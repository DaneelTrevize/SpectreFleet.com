<?php
class Channel extends SF_Controller {
	
	
	public function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->model('Channel_model');
		$this->load->library( 'LibOAuthState', array('key'=>'eve'), 'OAuth_model' );
        $this->config->load( 'ccp_api' );
        $this->load->library( 'LibOAuth2', $this->config->item('oauth_eve'), 'oauth_eve' );
		$this->load->model('Discord_model');
		$this->load->model('Activity_model');
		$this->load->model('Command_model');
		$this->load->model('CharacterID_model');
	}// __construct()
	
	
	private function get_current_EVEtime()
	{
		return $this->_eve_now_dtz()->format( Channel_model::DATETIME_DISPLAY_FORMAT );
	}// get_current_EVEtime()
	
	
	public function set_refresh_tokens()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_SET_CHANNEL_TOKENS' );
		
		// Prevent CSRF
		$this->form_validation->set_rules('confirm', 'confirm', 'required');	// Useless field to trigger CI CSRF checking
		
		if( $this->form_validation->run() == TRUE )
		{
			
			$characterID = $this->session->user_session['CharacterID'];
			
			self::report_to_tech( 'characterID:'.$characterID.' is attempting to set a new ESI chat channel token.' );
			
			$this->config->load( 'ccp_api' );
			
			$this->ensure_scopes( array( 'esi-characters.read_chat_channels.v1' ), 'channel/set_refresh_tokens', $this->config->item('critical_esi_params') );
			
			if( !$this->OAuth_model->ensure_fresh_token( $this->oauth_eve ) )
			{
				self::report_to_tech( 'characterID:'.$characterID.' failed to refresh access token for new ESI chat channel token.' );
				
				$this->session->set_flashdata( 'flash_message', 'Failure to refresh access token.' );
				redirect('portal', 'location');
			}
			
			$refresh_token = $this->OAuth_model->get_refresh_token();
			/*if( $this->Channel_model->try_set_channels_token( $characterID, $refresh_token ) )
			{
				self::report_to_tech( 'characterID:'.$characterID.' set new ESI chat channel token(s).' );
				
				//$this->_reset_channel_output_cache();
				
				$this->session->set_flashdata( 'flash_message', 'Channel token updated.' );
				redirect('portal', 'location');
			}
			else
			{
				self::report_to_tech( 'characterID:'.$characterID.' failed to set new ESI chat channel token(s).' );
				
				// Forget tokens so we'll ask for fresh ones next time
				$this->OAuth_model->logout();
				
				$this->session->set_flashdata( 'flash_message', 'Channel token failed to update.' );
				redirect('portal', 'location');
			}*/
			self::report_to_tech( 'THIS FEATURE IS CURRENTLY DISABLED!' );
		}
		else
		{
			// Field validation failed. Reload page with errors.
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Set Refresh Tokens' ) );
			$this->load->view( 'portal/portal_header' );
			$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
			$this->load->view( 'portal/portal_content' );
			$this->load->view( 'channel/set_refresh_tokens' );
			$this->load->view( 'portal/portal_footer' );
			$this->load->view( 'common/footer' );
		}
	}// set_refresh_tokens()
	
	private function report_to_tech( $content )
	{
		$result = $this->Discord_model->tell_tech( $content );
		if( $result['response'] == FALSE )
		{
			log_message( 'error', "Channel controller: failure to tell_tech( $content )." );
		}
		else
		{
			log_message( 'error', 'Channel controller: '. $content );
		}
	}// report_to_tech()
	
	private function ensure_scopes( array $requested_scopes, $resume_URL, $application )
	{
		// Assumes any current tokens are for the same application...
		
		$need_to_reauthorize = FALSE;
		if( $this->OAuth_model->get_auth_token() === NULL )
		{
			$need_to_reauthorize = TRUE;
		}
		else
		{
			$scopes = $this->OAuth_model->get_scopes();
			if( empty($scopes) || !empty( array_diff( $requested_scopes, $scopes ) ) )
			{
				$need_to_reauthorize = TRUE;
			}
		}
		if( $need_to_reauthorize )
		{
			$characterID = $this->session->user_session['CharacterID'];
			$CharacterName = $this->session->user_session['CharacterName'];
			log_message( 'error', 'Channel controller: user:'.$characterID.':'.$CharacterName.' needs to first authenticate with CCP.' );
			
			$this->OAuth_model->expect_login( $resume_URL, $requested_scopes, $application );
			redirect( 'OAuth/login', 'location' );
		}
	}// ensure_scopes()
	
	
	public function refresh_spectre()
	{
		$this->_ensure_logged_in();
		
        $this->_ensure_one_of( 'CAN_UPDATE_CHANNEL_DATA' );
		
		$this->load->library( 'MOTD' );
		
		// Form and Credential Validation.
		$this->form_validation->set_rules('rawMOTD', 'MOTD', 'required|max_length[65535]');
		
		if( $this->form_validation->run() == TRUE )
		{
			$rawMOTD = $this->input->post('rawMOTD');
			
			//log_message( 'error', print_r( $rawMOTD, TRUE ) );
			
			$MOTD_COPY_PATTERN = '#'. '^\[[[:digit:]]{2,2}:[[:digit:]]{2,2}:[[:digit:]]{2,2}\] EVE System > Channel MOTD: '. '(.{1,})' .'\r\n$' .'#';	// [HH:MM:SS] EVE System > Channel MOTD: ... \r\n
			if( preg_match( $MOTD_COPY_PATTERN, $rawMOTD, $matches ) === 1 )
			{
				$trimmedData = $matches[1];
			}
			else
			{
				$trimmedData = $rawMOTD;
			}
			
			$parsedData = $this->motd->spectre_to_data( $trimmedData );
			
			$errors = $parsedData['errors'];
			$issues = '';
			if( count( $errors ) > 0 )
			{
				foreach( $errors as $error )
				{
					$issues .=  htmlentities( $error, ENT_QUOTES ) .'<br>'."\n";
				}
			}
			
			$fleets = $parsedData['fleets'];
			if( $issues === '' && count( $fleets ) <= 0 )
			{
				$issues = 'No fleets detected.';
			}
			
			$this->form_validation->reset_validation();
			$this->form_validation->set_data( array( 'parsedData' => $issues ) );
			$this->form_validation->set_rules('parsedData', 'MOTD format', 'callback__parsedOK');
			
			if( $this->form_validation->run() == TRUE )
			{
				$UserID = $this->session->user_session['UserID'];
				$updated = $this->Channel_model->add_spectre_MOTD( $parsedData, $UserID );
				if( $updated === FALSE )
				{
					$this->session->set_flashdata( 'flash_message', 'Problem adding MOTD' );
					log_message( 'error', 'Channel controller: problem adding MOTD for UserID:'.$UserID );
					//self::report_to_tech( 'Problem adding MOTD.' );
					redirect('portal', 'location');
				}
				
				$result = $this->Activity_model->reparse_MOTDs( TRUE, FALSE, TRUE, $updated );	// Not Actives section
				
				// Fix activity chart
				// $updated !== FALSE, ensured above
				$this->Activity_model->fix_past_scheduled_fleets( $updated );
				
				$this->reset_activity_output_cache();
				
				$details = 'MOTD updated.<br>';
				$details .= 'Scheduled fleets detected: '. count( $result['distinct_scheduled_fleets'] );
				$details .= "<br>\n";
				/*foreach( $result['distinct_active_fleets'] as $XUPNumber => $active_fleets )
				{
					echo ' XUP~' .$XUPNumber. ': ' .count( $active_fleets );
				}
				echo '$distinct_active_fleets:<br><pre>' . print_r( $result['distinct_active_fleets'], TRUE ) . '</pre>';
				echo "<br>\n";*/
				$details .= 'Kills detected: '. count( $result['distinct_kills'] );
				
				$this->session->set_flashdata( 'flash_message', $details );
				redirect('portal', 'location');
			}
			// Else fall through to form validation failure.
		}
		// Form validation failed, reload with content.
		
		$data = array();
		if( isset( $_POST['rawMOTD'] ) )
		{
			$data['rawMOTD'] = $_POST['rawMOTD'];
		}
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Update MOTD' ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'channel/add_spectre_motd', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE ) );
	}// refresh_spectre()
	
	function _parsedOK( $issues )
	{
		if( $issues === '' )
		{
			return TRUE;
		}
		else
		{
			$this->form_validation->set_message( '_parsedOK', "MOTD Errors:<br />\n".$issues );
			return FALSE;
		}
	}// _parsedOK()
	
	private function reset_activity_output_cache()
	{
		$this->output->delete_cache( '/motd' );
		$this->output->delete_cache( '/channel/motd' );
		$this->output->delete_cache( '/' );
		$this->output->delete_cache( '/activity/fleets' );
		$this->output->delete_cache( '/activity/recent_fleets' );
		$this->output->delete_cache( '/activity/future_fleets' );
		$this->output->delete_cache( '/activity/cancelled_future_fleets' );
		$this->output->delete_cache( '/activity/recent_scheduled_fleets' );
		$this->output->delete_cache( '/activity/cancelled_scheduled_fleets' );
		$this->output->delete_cache( '/activity/recent_kills' );
	}// reset_activity_output_cache()
	
	
	public function motd()
	{
		$motd = $this->Channel_model->get_latest_MOTD();
		if( $motd === FALSE )
		{
			log_message( 'error', 'Channel controller: problem calling Channel_model->get_latest_MOTD().' );
			return;
		}
		$motd_data = $motd['data'];
		
		$active_fleets_data = array( 'active_fleets' => $motd_data['active'] );
		$active_html = $this->load->view( 'channel/active_fleets', $active_fleets_data, TRUE );
		$motd_data['active_html'] = $active_html;
		
		$kills_data = self::enhance_kills( $motd_data['kills'] );
		$kills_html = $this->load->view( 'channel/kills', $kills_data, TRUE );
		$motd_data['kills_html'] = $kills_html;
		
		$data['scheduled_html'] = $this->load->view( 'channel/scheduled_fleets', $motd_data, TRUE );
		
		$data['currentEVEtime'] = self::get_current_EVEtime();
		
		$this->output->cache( Activity_model::CACHE_MINUTES );
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'MOTD' ) );
		$this->load->view( 'channel/motd_tables', $data );
		$this->load->view( 'common/footer' );
	}// motd()
	
	private function enhance_kills( $kills )
	{
		$simple_kills = array();
		$enhanced_kills = array();
		
		$this->load->model('Killmails_model');
		
		foreach( $kills as $kill )
		{
			$killID = $kill['ID'];
			$enhanced_kill = $this->Killmails_model->get_killmail( $killID );
			if( $enhanced_kill != FALSE && ($enhanced_kill->try_resolve_after == NULL) )
			{
				$enhanced_kills[] = $enhanced_kill;
			}
			else
			{
				$simple_kills[] = $kill;
			}
		}
		
		return array(
			'kills' => $simple_kills,
			'enhanced_kills' => $enhanced_kills
		);
	}// enhance_kills()
	
	
	public function permissions()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_CHANGE_OTHERS_RANKS' );
		
		$data = $this->Channel_model->get_accessors( 'SF Spectre Fleet' );
		
		if( $data === FALSE )
		{
			$data = array(
				'operators' => array()
			);
		}
		
		$data['currentEVEtime'] = self::get_current_EVEtime();
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Check Channel Permissions' ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'channel/permissions', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer' );
		
	}// permissions()
	
	public function permission_results()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_CHANGE_OTHERS_RANKS' );
		
		$FCs = array();
		$commanders = $this->Command_model->get_sorted_commanders();
		foreach( $commanders as $commander )
		{
			$FCs[$commander['CharacterID']] = $commander;
		}
		
		$operators = trim( $this->input->post( 'operators' ) );
		$operators = explode( "\r\n", $operators );
		// Validate that all rows are only of characters permitted in Eve character names
		foreach( $operators as $character_name )
		{
			if( !$this->CharacterID_model->is_valid_character_name( $character_name ) )
			{
				$this->session->set_flashdata( 'flash_message', 'Invalid character name supplied:'. htmlentities( $character_name, ENT_QUOTES ) );
				redirect('portal', 'location');
			}
		}
		$operator_list = $this->CharacterID_model->get_character_data( $operators );
		if( $operator_list === FALSE )
		{
			log_message( 'error', 'Channel controller: problem resolving operators\' IDs' );
			$operators = array();
		}
		else
		{
			$operators = array();
			foreach( $operator_list as $operator )
			{
				$operators[$operator['id']] = $operator['name'];
			}
		}
		$data['rank_names'] = Command_model::RANK_NAMES();
		$data['FCs'] = $FCs;
		$data['operators'] = $operators;
		
		//$data['blocked_list'] = $this->Channel_model->get_blocked_cache( array( 'character' ) );
		$data['Spectre_Fleet_accessors'] = $this->Channel_model->get_accessors( 'SF Spectre Fleet' );
		$data['XUP1_accessors'] = $this->Channel_model->get_accessors( 'XUP~1' );
		$data['XUP2_accessors'] = $this->Channel_model->get_accessors( 'XUP~2' );
		$data['XUP3_accessors'] = $this->Channel_model->get_accessors( 'XUP~3' );
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Channel Permissions Results' ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'channel/permission_results', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer' );
		
	}// permission_results()
	
}// Channel
?>