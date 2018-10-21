<?php
class Discordauth extends SF_Controller {
	
	
	const IDENTIFY_SCOPES = array(
		'identify'
	);
	const MAIL_SCOPES = array(
		'esi-ui.open_window.v1'
	);
	
	public function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->library( 'LibOAuthState', array('key'=>'discord'), 'OAuth_state_discord' );
        $this->config->load( 'discord' );
        $this->load->library( 'LibOAuth2', $this->config->item('oauth_discord'), 'oauth_discord' );
		$this->load->model( 'Discord_model' );
	}// __construct()
	
	
	public function index()
	{
		$this->_ensure_logged_in();
		
		$UserID = $this->session->user_session['UserID'];
		
		$this->_ensure_one_of( 'CAN_LINK_DISCORD_ID', $this->should_have_discord_identity( $UserID ) );
		
		$auth_data = $this->Discord_model->get_auth_data( $UserID );
		if( $auth_data !== FALSE )
		{
			$this->session->set_flashdata( 'flash_message', 'Your Discord ID:'.$auth_data['DiscordID'].' was already linked to this Eve Character.' );
			redirect('portal', 'location');
		}
		// Else proceed to obtain refresh_token and DiscordID
		
		self::ensure_discord_token();
		
		$this->OAuth_state_discord->logout();
		
		redirect('portal', 'location');
	}// index()
	
	private function should_have_discord_identity( $UserID )
	{
		$roles = $this->User_model->get_roles_by_UserID( $UserID );
		$groups = $this->User_model->get_groups_by_UserID( $UserID );
		return Discord_model::should_have_identity( $roles->Rank, $roles->Editor, $roles->Admin, $groups );
	}// should_have_discord_identity()
	
	private function ensure_discord_token()
	{
		// Assumes $this->_ensure_logged_in() has been called
		$UserID = $this->session->user_session['UserID'];
		
		// Let's see what refresh tokens we have
		$refresh_token = $this->OAuth_state_discord->get_refresh_token();
		if( $refresh_token === NULL )
		{
			// Not one in the session, maybe in the db?
			$auth_data = $this->Discord_model->get_auth_data( $UserID );
			if( $auth_data === FALSE )
			{
				$this->ensure_new_refresh_token();
			}
			else
			{
				$discord_app = $this->config->item('discord_app');
				
				// Assume we need a new access_token
				$token_response = $this->oauth_discord->refresh_access_token( $auth_data['refresh_token'], $discord_app );
				// Test that the refresh token still works and is for the desired scopes
				if( $token_response === FALSE || !empty( array_diff( self::IDENTIFY_SCOPES, $token_response['scopes'] ) ) )
				{
					log_message( 'error', 'DiscordAuth controller: purging bad refresh token for UserID:'.$UserID );
					$this->Discord_model->delete_auth_data( $UserID );
					
					$this->ensure_new_refresh_token();
				}
				else
				{
					// Set up OAuth_state_discord with new refresh, access tokens, expires, application, etc? Refactor with refresh_access_token()?
					$this->OAuth_state_discord->set_tokens( $token_response, $discord_app );
					
					$_SESSION['oauth']['discord']['id'] = $auth_data['DiscordID'];
					
					return;
				}
			}
		}
		// Else, have refresh token in session
		
		if( isset($_SESSION['oauth']['discord']['store_refresh_token']) )
		{
			// We must have just acquired this refresh token, assume the access token and scopes are good
			$discord_user_data = $this->Discord_model->get_self_data( $this->OAuth_state_discord->get_auth_token() );
			if( $discord_user_data === FALSE )
			{
				//couldn't get /@me data using this token
			
				//unset( $_SESSION['oauth']['discord']['store_refresh_token'] );
				log_message( 'error', 'DiscordAuth controller: failure using new refresh token for UserID:'.$UserID );
				//$this->Discord_model->delete_auth_data( $UserID );
				
				$this->OAuth_state_discord->logout();
				
				$this->ensure_new_refresh_token();
			}
			// Else
			
			$DiscordID = $discord_user_data['id'];
			
			$_SESSION['oauth']['discord']['id'] = $DiscordID;
			/*
			$content = 'Eve Character '.$this->session->user_session['CharacterID'] .':"'. $this->session->user_session['Username'] .'" is on Discord as <@'.$DiscordID.'>, currently nicknamed "'.  $discord_user_data['username'] .'" (this can differ per server/guild).';
			$result = $this->Discord_model->tell_tech( $content );
			if( $result['response'] == FALSE )
			{
				log_message( 'error', "DiscordAuth controller: failure to tell_tech( $content )." );
			}
			*/
			log_message( 'error', 'DiscordAuth controller: acquired new refresh token for UserID:'.$UserID.', DiscordID:'.$DiscordID );
			$stored = $this->Discord_model->store_auth_data( $UserID, $DiscordID, $refresh_token );
			if( $stored === FALSE )
			{
				log_message( 'error', 'DiscordAuth controller: problem storing new refresh token for UserID:'.$UserID.', DiscordID:'.$DiscordID );
				$this->session->set_flashdata( 'flash_message', 'Your Discord ID:'.$_SESSION['oauth']['discord']['id'].' could not be stored. Please ensure you have not already associated this Discord account with a different user account on this website.' );
			}
			else
			{
				$this->session->set_flashdata( 'flash_message', 'Your Discord ID:'.$_SESSION['oauth']['discord']['id'].' has been linked to this Eve Character, thanks.' );
			}
			unset( $_SESSION['oauth']['discord']['store_refresh_token'] );
			
			return;
		}
	}// ensure_discord_token()
	
	private function ensure_new_refresh_token()
	{
		log_message( 'error', 'DiscordAuth controller: requesting new refresh token for UserID:'.$this->session->user_session['UserID'] );
		
		$_SESSION['oauth']['discord']['store_refresh_token'] = TRUE;
		
		$this->OAuth_state_discord->expect_login( 'discordauth/index', self::IDENTIFY_SCOPES, $this->config->item('discord_app') );
		redirect( 'discordauth/login', 'location' );
	}// ensure_new_refresh_token()
	
	
	public function login()		// Set up local state (for XSRF prevention) before calling external CCP URL
	{
		$this->_ensure_logged_in();
		
		$UserID = $this->session->user_session['UserID'];
		
		$this->_ensure_one_of( 'CAN_LINK_DISCORD_ID', $this->should_have_discord_identity( $UserID ) );
		
		if( !$this->OAuth_state_discord->expecting_login() )
		{
			$this->session->set_flashdata( 'flash_message', 'Invalid OAuth flow. Please ensure any bookmarks are still valid.' );
			log_message( 'error', 'DiscordAuth controller: Invalid OAuth flow. Not expecting login().' );
			redirect('portal', 'location');
		}
		
		$scopes = $this->OAuth_state_discord->get_requested_scopes();
		
		$state = $this->OAuth_state_discord->setup_login_state();
		
		$application = $this->OAuth_state_discord->get_application_details();
		
		$url = $this->oauth_discord->get_authentication_url( $scopes, $state, $application );
		
		redirect( $url, 'location' );
	}// login()
	
	public function callback()	// Registered callback URL for Discord
	{
		$this->_ensure_logged_in();
		
		$UserID = $this->session->user_session['UserID'];
		
		$this->_ensure_one_of( 'CAN_LINK_DISCORD_ID', $this->should_have_discord_identity( $UserID ) );
		
		$state = $this->input->get( 'state' );
		$code = $this->input->get( 'code' );
		
		$local_state = $this->OAuth_state_discord->get_login_state();
		if( $local_state === NULL )
		{
			$this->session->set_flashdata( 'flash_message', 'Expired OAuth state. Please avoid navigating Back during OAuth actions.' );
			log_message( 'error', 'DiscordAuth controller: Expired OAuth state. $state:'. $state . ', $code:' .$code );
			redirect( 'portal', 'location' );
		}
		
		if( $state == NULL || $state == '' || $code == NULL || $code == '' )
		{
			// Redirect to login?
			$this->session->set_flashdata( 'flash_message', 'Invalid OAuth flow. Please ensure any bookmarks are still valid.' );
			log_message( 'error', 'DiscordAuth controller: Invalid OAuth flow. $state:'. $state . ', $code:' .$code );
			redirect( 'portal', 'location' );
		}
		
		$application = $this->OAuth_state_discord->get_application_details();
		
		$response = $this->oauth_discord->handle_callback( $local_state, $state, $code, $application );
		if( $response === FALSE )
		{
			// Error on bad token verifying
			$this->session->set_flashdata( 'flash_message', 'OAuth login failed.' );
			log_message( 'error', 'DiscordAuth controller: failure to verify access token' );
			redirect('portal', 'location');
		}
		
		$location = $this->OAuth_state_discord->finish_login( $response );
		
		redirect( $location, 'location' );
		
	}// callback()
	
	
	public function manage()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_VIEW_DISCORD_DATA' );
		
		$this->load->helper( 'discord' );
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Manage Discord Integration' ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'discordauth/manage' );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer' );
		
	}// manage()
	
	public function list_roles()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_VIEW_DISCORD_DATA' );
		
		$data['roles'] = $this->Discord_model->get_roles();
		
		$this->load->helper( 'discord' );
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Discord Roles' ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'discordauth/roles', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer' );
		
	}// list_roles()
	
	public function refresh_roles()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_CONFIGURE_DISCORD_APPS' );
		
		// Prevent CSRF
		$this->form_validation->set_rules('confirm', 'confirm', 'required');	// Useless field to trigger CI CSRF checking
		
		if( $this->form_validation->run() == TRUE )
		{
			$roles = $this->Discord_model->list_roles();
			
			if( $roles !== FALSE && $this->Discord_model->update_roles( $roles ) ) {
				$this->session->set_flashdata( 'flash_message', "Discord roles were refreshed." );
				redirect('portal', 'location');
			}
			else
			{
				$this->session->set_flashdata( 'flash_message', "There was a problem refreshing the roles from Discord." );
				log_message( 'error', 'Discordauth controller: Problem refreshing the roles from Discord.' );
				redirect('portal', 'location');
			}
		}
		else
		{
			// Field validation failed. Reload page with errors.
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Refresh roles list' ) );
			$this->load->view( 'portal/portal_header' );
			$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
			$this->load->view( 'portal/portal_content' );
			$this->load->view( 'discordauth/refresh_roles' );
			$this->load->view( 'portal/portal_footer' );
			$this->load->view( 'common/footer' );
		}
		
	}// refresh_roles()
	
	public function list_channels()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_VIEW_DISCORD_DATA' );
		
		$data['channels'] = $this->Discord_model->list_channels();
		
		$this->load->helper( 'discord' );
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Discord Channels' ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'discordauth/channels', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer' );
		
	}// list_channels()
	
	public function refresh_members()
	{
		$this->_ensure_local_request_or( 'CAN_CONFIGURE_DISCORD_APPS' );
		
		$guild_members = $this->Discord_model->get_trusted_members_data( $this->config->item('discord_bot') );
		
		echo 'Refreshing members list: '. ( $guild_members === FALSE ? 'failed.' : count( $guild_members ) );
		
	}// refresh_members()
	
	public function list_members()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_VIEW_DISCORD_DATA' );
		
		$guild_members = $this->Discord_model->get_trusted_members_data( $this->config->item('discord_bot') );
		
		//log_message( 'error', 'Discordauth controller: guild members '. count( $guild_members ) );
		
		$data['guild_members'] = $guild_members;
		
		$data['roles_data'] = $this->Discord_model->get_roles();
		
		$this->load->helper( 'discord' );
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Discord Members' ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'discordauth/members', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer' );
	}// list_members()
	
	public function list_unidentified()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_VIEW_DISCORD_DATA' );
		
		$unidentified_members = $this->Discord_model->get_unidentified_trusted_discord_members();
		
		$filtered_members = array();
		foreach( $unidentified_members as &$member_data )
		{
			if( $member_data['role_ids'] )
			{
				$member_data['role_ids'] = array_diff( $member_data['role_ids'], Discord_model::IGNORED_ROLES );
				if( !empty( $member_data['role_ids'] ) )
				{
					$filtered_members[] = $member_data;
				}
			}
		}
		$data['unidentified_members'] = $filtered_members;
		
		$data['unidentified_users'] = $this->Discord_model->get_unidenfified_trusted_users();
		
		$data['rank_names'] = Command_model::RANK_NAMES();
		$data['role_names'] = Editor_model::ROLE_NAMES();
		$data['admin_names'] = User_model::ADMIN_NAMES();
		$data['roles_data'] = $this->Discord_model->get_roles();
		
		$this->load->helper( 'discord' );
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Unidentified Members' ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'discordauth/unidentified', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer' );
	}// list_unidentified()
	
	public function mention_unidentified()
	{
		$this->_ensure_local_request_or( 'CAN_CONFIGURE_DISCORD_APPS' );
		
		$unidentified_members = $this->Discord_model->get_unidentified_trusted_discord_members();
		
		$filtered_members = array();
		foreach( $unidentified_members as &$member_data )
		{
			if( $member_data['role_ids'] )
			{
				$member_data['role_ids'] = array_diff( $member_data['role_ids'], Discord_model::IGNORED_ROLES );
				if( !empty( $member_data['role_ids'] ) )
				{
					$filtered_members[] = $member_data;
				}
			}
		}
		
		$unidentified_users = $this->Discord_model->get_unidenfified_trusted_users();
		
		$content = '';
		if( !empty( $filtered_members ) )
		{
			$content .= 'The following '. count( $filtered_members ) ." members have trusted Discord roles but are Unidentified as Eve Characters on our website:\n";
			foreach( $filtered_members as $member_data )
			{
				$content .= '<@'. $member_data['DiscordID'] .">\n";
			}
			$content .= "\n";
		}
		if( !empty( $unidentified_users ) )
		{
			$content .= 'The following '. count( $unidentified_users ) ." Eve Characters on our website have trusted permissions or S.I.G. membership but are Unidentified as Discord accounts:\n";
			foreach( $unidentified_users as $user_data )
			{
				$content .= $user_data['CharacterName'] ."\n";
			}
			$content .= "\n";
		}
		
		if( $content !== '' )
		{
			$content = "\nAs per <https://spectrefleet.com/discordauth/list_unidentified>:\n" . $content;
			$result = $this->Discord_model->tell_directorate( $content );
			if( $result['response'] == FALSE )
			{
				log_message( 'error', "DiscordAuth controller: failure to tell_directorate( $content )." );
			}
		}
	}// mention_unidentified()
	
	
	public function prepare_evemail()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_EVEMAIL_USERS' );
		
		self::ensure_esi_refresh_token();
		
		$recipients = array();
		$unidentified_users = $this->Discord_model->get_unidenfified_trusted_users();
		foreach( $unidentified_users as $user_data )
		{
			$recipients[] = $user_data['CharacterID'];
		}
		if( empty( $recipients ) )
		{
			echo 'No unidentified trusted users.';
			return;
		}
		
		$subject = 'SpectreFleet.com Discord Identity integration';
		$body = 'Hello,
You have been identified as a registered Eve Character trusted by Spectre Fleet, but you have not yet Identified a Discord account for this character, to assist our roles integration and for our new Special Interest Groups.

This is a simple process, taking just 1 click from <a href="https://spectrefleet.com/discordauth/">here</a> or 2 from the bottom section of <a href="https://spectrefleet.com/portal">Your Portal</a> page, assuming you are logged in to our site and Discord in your browser. You will see we only request the scope to determine your username and avatar, very similar to CCP\'s SSO feature. Please Identify your account ASAP, thanks.

This is an automated message, please contact @Tech on our Discord server if you have any questions.';
		$response = $this->User_model->prepare_evemail( $recipients, $subject, $body );
		if( $response === TRUE )
		{
			echo 'Open window request received by Eve.';
		}
		else
		{
			echo 'Unable to submit your Open window request to Eve.';
		}
		//For now, log them out either way?
		$this->OAuth_model->logout();
		
	}// prepare_evemail()
	
	private function ensure_esi_refresh_token()
	{
		// Assumes $this->_ensure_logged_in() has been called
		$characterID = $this->session->user_session['CharacterID'];
		
		$this->config->load('ccp_api');
		$this->load->library( 'LibOAuthState', array('key'=>'eve'), 'OAuth_model' );
        $this->load->library( 'LibOAuth2', $this->config->item('oauth_eve'), 'oauth_eve' );
		
		// Let's see what refresh tokens we have
		$refresh_token = $this->OAuth_model->get_refresh_token();
		if( $refresh_token === NULL )
		{
			// Not one in the session, and we're not using a db store
			
			$token_response = $this->oauth_eve->refresh_access_token( $refresh_token, $this->config->item('esi_params') );
			// Test that the refresh token still works and is for the desired scopes
			if( $token_response === FALSE || !empty( array_diff( self::MAIL_SCOPES, $token_response['scopes'] ) ) )
			{
				log_message( 'error', 'Discordauth controller: bad refresh token for characterID:'.$characterID );
				
				$this->OAuth_model->expect_login( 'discordauth/prepare_evemail', self::MAIL_SCOPES, $this->config->item('esi_params') );
				redirect( 'OAuth/login', 'location' );	// Put this URL in app config and merge these 2 lines into model/lib?
			}
			else
			{
				// Set up OAuth_model with new refresh, access tokens, expires, application, etc? Refactor with refresh_access_token()?
				$this->OAuth_model->set_tokens( $token_response, $this->config->item('esi_params') );
				
				return;
			}
		}
		// Else, have refresh token in session
		
	}// ensure_esi_refresh_token()
	
	
	public function list_identified()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_VIEW_DISCORD_DATA' );
		
		$identified = $this->Discord_model->get_identified_trusted_users( $this->config->item('discord_bot') );
		foreach( $identified as $userID => &$user_data )
		{
			$user_data['expected_roles'] = Discord_model::calculate_roles( $user_data['Rank'], $user_data['Editor'], $user_data['Admin'], $user_data['groupIDs'] );
		}
		$data['identified'] = $identified;
		
		$data['rank_names'] = Command_model::RANK_NAMES();
		$data['role_names'] = Editor_model::ROLE_NAMES();
		$data['admin_names'] = User_model::ADMIN_NAMES();
		$data['SF_groups'] = $this->User_model->get_SF_groups();
		$data['roles_data'] = $this->Discord_model->get_roles();
		
		$this->load->helper( 'discord' );
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Identified Users' ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'discordauth/identified', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer' );
	}// list_identified()
	
	public function mention_identified()
	{
		$this->_ensure_local_request_or( 'CAN_CONFIGURE_DISCORD_APPS' );
		
		$users_with_issues = array();
		$identified = $this->Discord_model->get_identified_trusted_users( $this->config->item('discord_bot') );
		foreach( $identified as $userID => &$user_data )	// Repeating logic as found in /view/discordauth/identified...
		{
			$user_data['expected_roles'] = Discord_model::calculate_roles( $user_data['Rank'], $user_data['Editor'], $user_data['Admin'], $user_data['groupIDs'] );
			
			$missing_roles = array_diff( $user_data['expected_roles'], $user_data['role_ids'] );
			$revoke_roles = array_diff( $user_data['role_ids'], $user_data['expected_roles'] );
			$revoke_roles = array_diff( $revoke_roles, Discord_model::IGNORED_ROLES );
			if( !empty( $missing_roles ) || !empty( $revoke_roles ) )
			{
				$user_data['missing_roles'] = $missing_roles;
				$user_data['revoke_roles'] = $revoke_roles;
				$users_with_issues[] = $user_data;
			}
		}
		
		$content = '';
		if( !empty( $users_with_issues ) )
		{
			$content .= 'The following '. count( $users_with_issues ) ." members have issues with their Discord roles compared to their website status:\n";
			foreach( $users_with_issues as $user_data )
			{
				$content .= '<@'. $user_data['DiscordID'] ."> should have the following role changes made:\n";
				if( !empty( $user_data['revoke_roles'] ) )
				{
					$content .= 'Remove: ';
					foreach( $user_data['revoke_roles'] as $role_id )
					{
						$content .= '<@&'. $role_id .'> ';
					}
					$content .= "\n";
				}
				if( !empty( $user_data['missing_roles'] ) )
				{
					$content .= 'Add: ';
					foreach( $user_data['missing_roles'] as $role_id )
					{
						$content .= '<@&'. $role_id .'> ';
					}
					$content .= "\n";
				}
			}
		}
		
		if( $content !== '' )
		{
			$content = "\nAs per <https://spectrefleet.com/discordauth/list_identified>:\n" . $content;
			$result = $this->Discord_model->tell_directorate( $content );
			if( $result['response'] == FALSE )
			{
				log_message( 'error', "DiscordAuth controller: failure to tell_directorate( $content )." );
			}
		}
	}// mention_identified()
	
	/*
	public function list_dan_dms()
	{
		
	}// list_dan_dms()
	*/
	public function websocket()	// Used once to enable send_messages
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_CONFIGURE_DISCORD_APPS' );
		
		//$data['token'] = $this->config->item('discord_bot')['BOT_TOKEN'];
		
		$this->load->view( 'discordauth/websocket', $data );
	
	}// websocket()
	
}// Discordauth
?>