<?php
if( !defined('BASEPATH') ) exit('No direct script access allowed');

class SIGs extends SF_Controller {	// SIGsment of FCs, not Editors or Admins/general users
	
	
	const SKILLS_SCOPES = array(
		'esi-skills.read_skills.v1'
	);
	
	public function __construct()
	{
		parent::__construct();
		$this->load->library( 'form_validation' );
		$this->load->model( 'User_model' );
		$this->load->model( 'Command_model' );
		$this->load->model( 'Discord_model' );
	}// __construct()
	
	
	public function change()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_CHANGE_OTHERS_GROUPS' );
		
		$this->form_validation->set_rules('Username', 'user name', 'required|max_length[100]|callback__check_Username');
		$this->form_validation->set_rules('GroupID', 'group', 'required|is_natural|callback__check_group');
		$this->form_validation->set_rules('comment', 'Reason', 'required|max_length[2000]');
		
		$groups = $this->User_model->get_SF_groups();
		$data['groups'] = $groups;
		
		if( $this->form_validation->run() == TRUE )
		{
			$Username = $this->input->post('Username');
			$user = $this->User_model->get_user_data_by_name( $Username );
			$TargetUserID = $user->UserID;
			
			$GroupID = $this->input->post('GroupID');
			$groupName = $groups[$GroupID]['groupName'];
			
			$comment =  htmlentities( $this->input->post('comment'), ENT_QUOTES);
			
			$EnactingUserID = $this->session->user_session['UserID'];
			
			if( isset( $_POST['Add'] ) && !isset( $_POST['Remove'] ) )
			{
				$action = 'Add';
			}
			elseif( isset( $_POST['Remove'] ) && !isset( $_POST['Add'] ) )
			{
				$action = 'Remove';
			}
			else
			{
				$action = NULL;
				// Malicious population of ApplicationID field?
				log_message( 'error', 'SIGs controller: Invalid group membership action by UserID:'.$EnactingUserID );
				$this->session->set_flashdata( 'flash_message', 'Invalid group membership action.' );
				redirect('portal', 'location');
			}
			
			if( $action == 'Add' )
			{
				if( $this->User_model->add_user_to_group( $TargetUserID, $GroupID, $EnactingUserID, $comment ) )
				{
					$content = $Username .' was added to group '. $groupName;
					$result = $this->Discord_model->tell_command( $content );
					if( $result['response'] == FALSE )
					{
						log_message( 'error', "SIGs controller: failure to tell_command( $content )." );
					}
					
					$this->session->set_flashdata( 'flash_message', $Username .' was added to group <strong>'. $groupName .'<strong>.' );
					redirect('SIGs/change', 'location');
				}
				else
				{
					$this->session->set_flashdata( 'flash_message', $Username .' was unable to be added to group <strong>'. $groupName .'<strong>.' );
					redirect('SIGs/change', 'location');
				}
			}
			elseif( $action == 'Remove' )
			{
				if( $this->User_model->remove_user_from_group( $TargetUserID, $GroupID, $EnactingUserID, $comment ) )
				{
					$content = $Username .' was removed from group '. $groupName;
					$result = $this->Discord_model->tell_command( $content );
					if( $result['response'] == FALSE )
					{
						log_message( 'error', "SIGs controller: failure to tell_command( $content )." );
					}
					
					$this->session->set_flashdata( 'flash_message', $Username .' was removed from group <strong>'. $groupName .'<strong>.' );
					redirect('SIGs/change', 'location');
				}
				else
				{
					$this->session->set_flashdata( 'flash_message', $Username .' was unable to be removed from group <strong>'. $groupName .'<strong>.' );
					redirect('SIGs/change', 'location');
				}
			}
			// Should never reach, because of checks of $action
			log_message( 'error', 'SIGs controller: Problem with group membership action by UserID:'.$EnactingUserID );
			redirect('portal', 'location');
		}
		else
		{
			// Field validation failed. Reload registration page with errors.
			
			if( isset( $_POST['Username'] ) )
			{
				$data['Username'] = $_POST['Username'];
			}
			if( isset( $_POST['GroupID'] ) )
			{
				$data['GroupID'] = $_POST['GroupID'];
			}
			if( isset( $_POST['comment'] ) )
			{
				$data['comment'] = $_POST['comment'];
			}
			
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Change User\'s Groups' ) );
			$this->load->view( 'portal/portal_header' );
			$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
			$this->load->view( 'portal/portal_content' );
			$this->load->view( 'SIGs/change_group', $data );
			$this->load->view( 'portal/portal_footer' );
			$this->load->view( 'common/footer' );
		}
		
	}// change()
	
	function _check_Username( $Username )
	{
		if( $Username !== '' && $this->User_model->get_user_data_by_name( $Username ) === FALSE )
		{
			$this->form_validation->set_message('_check_Username', 'The user name was not found.');
			return FALSE;
		}
		return TRUE;
	}// _check_Username()
	
	function _check_group( $groupID )
	{
		$groups = $this->User_model->get_SF_groups();
		if( !array_key_exists( $groupID, $groups ) )
		{
			$this->form_validation->set_message('_check_group', 'Invalid Group supplied.');
			return FALSE;
		}
		return TRUE;
	}// _check_group()
	
	
	public function manage()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_CHANGE_OTHERS_GROUPS' );
		
		$groups = $this->User_model->get_SF_groups();
		$data['groups'] = $groups;
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'List Users\' Groups' ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'SIGs/manage_groups', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer' );
	}// manage()
	
	public function group( $groupID = NULL )
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_CHANGE_OTHERS_GROUPS' );
		
		if( $groupID === NULL || !ctype_digit( $groupID ) )
		{
			// Malicious population of groupID field?!
			self::_not_found();
		}
		$groups = $this->User_model->get_SF_groups();
		if( !array_key_exists( $groupID, $groups ) )
		{
			self::_not_found();
		}
		
		$data['group'] = $groups[$groupID];
		$data['users'] = $this->User_model->get_users_by_groupID( $groupID );
		$data['rank_names'] = Command_model::RANK_NAMES();
		$data['role_names'] = Editor_model::ROLE_NAMES();
		$data['admin_names'] = User_model::ADMIN_NAMES();
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'S.I.G.: '. $groups[$groupID]['groupName'] ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'SIGs/view_group', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer' );
	}// group()
	
	// Dupe from Discordauth
	public function token()
	{
		$this->_ensure_logged_in();
		
		$UserID = $this->session->user_session['UserID'];
		
		$this->_ensure_one_of( 'CAN_SUBMIT_SKILLS_TOKEN', $this->should_have_skills_token( $UserID ) );
		
		$CharacterID = $this->session->user_session['CharacterID'];
		$auth_data = $this->User_model->get_refresh_token( $CharacterID );
		if( $auth_data !== FALSE )
		{
			$this->session->set_flashdata( 'flash_message', 'You have already supplied a skills token.' );
			redirect('portal', 'location');
		}
		// Else proceed to obtain refresh_token
		
		$this->load->library( 'LibOAuthState', array('key'=>'eve'), 'OAuth_model' );
		
		self::ensure_skills_token();
		
		$this->OAuth_model->logout();
		
		redirect('portal', 'location');
	}// token()
	
	private function should_have_skills_token( $UserID )
	{
		$roles = $this->User_model->get_roles_by_UserID( $UserID );
		$groups = $this->User_model->get_groups_by_UserID( $UserID );
		return User_model::should_have_skills_token( $roles->Rank, $roles->Editor, $roles->Admin, $groups );
	}// should_have_skills_token()
	
	private function ensure_skills_token()
	{
		// Assumes $this->_ensure_logged_in() has been called
		$CharacterID = $this->session->user_session['CharacterID'];
		
		// Let's see what refresh tokens we have
		$refresh_token = $this->OAuth_model->get_refresh_token();
		if( $refresh_token === NULL )
		{
			// Not one in the session, maybe in the db?
			$db_refresh_token = $this->User_model->get_refresh_token( $CharacterID );
			if( $db_refresh_token === FALSE )
			{
				$this->ensure_new_refresh_token();
			}
			else
			{
				$esi_app = $this->config->item('esi_params');
				
				// Assume we need a new access_token
				$token_response = $this->oauth_eve->refresh_access_token( $db_refresh_token, $esi_app );
				// Test that the refresh token still works and is for the desired scopes
				if( $token_response === FALSE || !empty( array_diff( self::SKILLS_SCOPES, $token_response['scopes'] ) ) )
				{
					log_message( 'error', 'SIGs controller: purging bad refresh token for CharacterID:'.$CharacterID );
					$this->User_model->remove_refresh_token( $CharacterID );
					
					$this->ensure_new_refresh_token();
				}
				else
				{
					// Set up OAuth_model with new refresh, access tokens, expires, application, etc? Refactor with refresh_access_token()?
					$this->OAuth_model->set_tokens( $token_response, $esi_app );
					
					return;
				}
			}
		}
		// Else, have refresh token in session
		
		if( isset($_SESSION['oauth']['eve']['store_refresh_token']) )
		{
			// We must have just acquired this refresh token, assume the access token and scopes are good
			
			$this->load->model( 'Skills_model' );
			
			$character_skills = $this->Skills_model->get_character_skills( $CharacterID, $this->OAuth_model->get_auth_token() );
			if( $character_skills === FALSE )
			{
				log_message( 'error', 'SIGs controller: failure using new refresh token for CharacterID:'.$CharacterID );
				
				$this->OAuth_model->logout();
				
				$this->ensure_new_refresh_token();
			}
			// Else
			
			log_message( 'error', 'SIGs controller: acquired new refresh token for CharacterID:'.$CharacterID );
			$stored = $this->User_model->store_refresh_token( $CharacterID, $refresh_token );
			if( $stored === FALSE )
			{
				log_message( 'error', 'SIGs controller: problem storing new refresh token for CharacterID:'.$CharacterID );
				$this->session->set_flashdata( 'flash_message', 'Your ESI token could not be stored.' );
			}
			else
			{
				$this->session->set_flashdata( 'flash_message', 'Your new ESI token has been stored.' );
			}
			unset( $_SESSION['oauth']['eve']['store_refresh_token'] );
			
			return;
		}
	}// ensure_skills_token()
	
	private function ensure_new_refresh_token()
	{
		log_message( 'error', 'SIGs controller: requesting new refresh token for CharacterID:'.$this->session->user_session['CharacterID'] );
		
		$_SESSION['oauth']['eve']['store_refresh_token'] = TRUE;
		
		$this->OAuth_model->expect_login( 'SIGs/token', self::SKILLS_SCOPES, $this->config->item('esi_params') );
		redirect( 'OAuth/login', 'location' );
	}// ensure_new_refresh_token()
	
	
	public function user( $MemberID = NULL )
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_CHANGE_OTHERS_GROUPS' );
		
		if( !self::_is_integer_string( $MemberID ) )
		{
			self::_not_found();
		}
		
		$user_data = $this->User_model->get_user_data_by_ID( $MemberID );
		if( $user_data === FALSE )
		{
			self::_not_found();
		}
		
		$CharacterID = $user_data->CharacterID;
		
		//$groups = $this->User_model->get_groups_by_UserID( $MemberID );
		if( !$this->should_have_skills_token( $MemberID ) )
		{
			log_message( 'error', 'SIGs controller: No skills token expected for MemberID:'.$MemberID.', requester:'.$this->session->user_session['UserID'] );
			$this->session->set_flashdata( 'flash_message', 'No data can be reported for that user as this time.' );
			redirect('portal', 'location');
		}
		
		$db_refresh_token = $this->User_model->get_refresh_token( $CharacterID );
		if( $db_refresh_token === FALSE )
		{
			log_message( 'error', 'SIGs controller: Problem accessing skills token for CharacterID:'.$CharacterID.', requester:'.$this->session->user_session['UserID'] );
			$this->session->set_flashdata( 'flash_message', 'There was a problem accessing that user\'s ESI token.' );
			redirect('portal', 'location');
		}
		
		$esi_app = $this->config->item('esi_params');
		// Assume we need a new access_token
		$token_response = $this->oauth_eve->refresh_access_token( $db_refresh_token, $esi_app );
		if( $token_response === FALSE || !empty( array_diff( self::SKILLS_SCOPES, $token_response['scopes'] ) ) )
		{
			log_message( 'error', 'SIGs controller: bad refresh token for CharacterID:'.$CharacterID );
			$this->session->set_flashdata( 'flash_message', 'There was a problem using that user\'s ESI token.' );
			redirect('portal', 'location');
		}
		
		$this->load->library( 'LibOAuthState', array('key'=>'eve'), 'OAuth_model' );
		
		$this->OAuth_model->set_tokens( $token_response, $esi_app );
		
		$this->load->model( 'Skills_model' );
		
		$character_skills = $this->Skills_model->get_character_skills( $CharacterID, $this->OAuth_model->get_auth_token() );
		
		$this->OAuth_model->logout();
		
		if( $character_skills === FALSE )
		{
			log_message( 'error', 'SIGs controller: failure using access token for CharacterID:'.$CharacterID );
			$this->session->set_flashdata( 'flash_message', 'There was a problem accessing that user\'s skills.' );
			redirect('portal', 'location');
		}
		
		$data['user_data'] = $user_data;
		$data['character_skills'] = $character_skills;
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'S.I.G. Member: '. $user_data->CharacterName ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'SIGs/view_user', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer' );
	}// user()
	
}// SIGs
?>