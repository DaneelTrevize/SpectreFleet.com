<?php
class Fleets2 extends SF_Controller {
	
	
	const FLEET_SCOPES = array(
		'esi-fleets.read_fleet.v1',
		'esi-fleets.write_fleet.v1'
	);
	
	public function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->library( 'LibFleet' );
		$this->load->library( 'LibOAuthState', array('key'=>'eve'), 'OAuth_model' );
        $this->config->load( 'ccp_api' );
        $this->load->library( 'LibOAuth2', $this->config->item('oauth_eve'), 'oauth_eve' );
		$this->load->model('CharacterID_model');
		$this->load->model('Channel_model');
		$this->load->model('Eve_SDE_model');
		$this->load->model('Fleets_model');
		$this->load->model('Activity_model');
		$this->load->model('Discord_model');
		$this->load->model('Doctrine_model');
	}// __construct()
	
	
	public function index()
	{
		self::summary();
	}// index()
	
	public function get_fleet_url()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_MANAGE_ACTIVE_FLEETS' );
		
		self::ensure_refresh_token();
		
		self::log_fc_action( 'Attempting to get fleet URL/ID', NULL );
		
		$this->form_validation->set_rules('url', 'url', 'required');
		
		if( $this->form_validation->run() == TRUE )
		{
			
			self::log_fc_action( 'Get fleet URL', NULL );
			
			$url = $this->input->post('url');
			if( preg_match( '#^https://esi.tech.ccp.is/v1/fleets/(\d+)/\?datasource=tranquility$#', $url, $matches ) )
			{
				$this->libfleet->set_fleet_id( $matches[1] );
				
				redirect('fleets2', 'location');
			}
		}
		
		$data['csrf_hash'] = $this->security->get_csrf_hash();
		
		//Elses
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'External Fleet Link' ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'fleets2/get_fleet_url', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer', array( 'TOGGLES' => TRUE ) );
		
	}// get_fleet_url()
	
	private function ensure_refresh_token()
	{
		// Assumes $this->_ensure_logged_in() has been called
		$characterID = $this->session->user_session['CharacterID'];
		
		// Let's see what refresh tokens we have
		$refresh_token = $this->OAuth_model->get_refresh_token();
		if( $refresh_token === NULL )
		{
			// Not one in the session, maybe in the db?
			$refresh_token = $this->Fleets_model->get_refresh_token( $characterID );
			if( $refresh_token === FALSE )
			{
				$this->ensure_new_refresh_token();
			}
			else
			{
				// Assume we need a new access_token, but maybe we could store one in the cache table too?
				$token_response = $this->oauth_eve->refresh_access_token( $refresh_token, $this->config->item('esi_params') );
				// Test that the refresh token still works and is for the desired scopes
				if( $token_response === FALSE || !empty( array_diff( self::FLEET_SCOPES, $token_response['scopes'] ) ) )
				{
					log_message( 'error', 'Fleets2 controller: purging bad refresh token for characterID:'.$characterID );
					$this->Fleets_model->remove_refresh_token( $characterID );
					
					$this->ensure_new_refresh_token();
				}
				else
				{
					// Set up OAuth_model with new refresh, access tokens, expires, application, etc? Refactor with refresh_access_token()?
					$this->OAuth_model->set_tokens( $token_response, $this->config->item('esi_params') );
					
					return;
				}
			}
		}
		// Else, have refresh token in session
		
		if( isset($_SESSION['fleet']['store_refresh_token']) )
		{
			// We must have just acquired this refresh token, assume the access token and scopes are good
			
			log_message( 'error', 'Fleets2 controller: acquired new refresh token for characterID:'.$characterID );
			$this->Fleets_model->store_refresh_token( $characterID, $refresh_token );	// Don't test return?!
			unset( $_SESSION['fleet']['store_refresh_token'] );
		}
	}// ensure_refresh_token()
	
	private function ensure_new_refresh_token()
	{
		log_message( 'error', 'Fleets2 controller: requesting new refresh token for characterID:'.$this->session->user_session['CharacterID'] );
		
		$_SESSION['fleet']['store_refresh_token'] = TRUE;
		
		$this->OAuth_model->expect_login( 'fleets2/summary', self::FLEET_SCOPES, $this->config->item('esi_params') );
		redirect( 'OAuth/login', 'location' );
	}// ensure_new_refresh_token()
	
	private function log_fc_action( $message, $scheduledDateTime )
	{
		$UserID = $this->session->user_session['UserID'];
		$logged_action = $this->Fleets_model->log_fc_action( $UserID, $scheduledDateTime, $message );
		if( !$logged_action ) {
			$currentEveTimeString = $this->_eve_now_dtz()->format('Y-m-d H:i:s') . '+00';	// UTC TZ, in format not supported by date()
			log_message( 'error', "Fleets2 controller: failure to log FC action for $UserID, $currentEveTimeString, $scheduledDateTime, $message." );
		}
	}// log_fc_action()
	
	
	public function get_fleet_url_json()
	{
		$this->_ensure_json_permission('CAN_MANAGE_ACTIVE_FLEETS');
		
		$fleet_id = $this->libfleet->try_detect_fleet_ID( $this->session->user_session['CharacterID'] );
		$data['fleet_id'] = $fleet_id;
		
		if( $fleet_id )
		{
			self::log_fc_action( 'Detected fleet ID', NULL );
			$this->output->set_status_header( 200 );
		}
		else
		{
			if( !$this->OAuth_model->ensure_fresh_token( $this->oauth_eve ) )
			{
				$this->session->set_flashdata( 'flash_message', 'There was a problem with your access token.' );
				log_message( 'error', 'Fleets2 controller: get_fleet_url_json(): ensure_fresh_token() failed.' );
				$this->output->set_status_header( 403 );
				$this->OAuth_model->logout();
				return;
			}
			else
			{
				$this->output->set_status_header( 200 );
			}
		}
		
		$data['csrf_hash'] = $this->security->get_csrf_hash();
		
		$this->output->set_content_type( 'application/json' );
		$this->output->set_output( json_encode( $data ) );
		
	}// get_fleet_url_json()
	
	
	public function summary()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_MANAGE_ACTIVE_FLEETS' );
		
		self::ensure_refresh_token();
		
		self::ensure_fleet_url();
		
		$data['fleet_scheduled_details'] = $this->libfleet->get_scheduled_details();
		$data['XUP_generated'] = $this->libfleet->generate_xup_motd( $data['fleet_scheduled_details'] );
		$data['XUP_blank'] = $this->libfleet->generate_blank_xup_motd();
		
		$data['csrf_hash'] = $this->security->get_csrf_hash();
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Fleet Summary' ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'fleets2/summary', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer', array( 'TOGGLES' => TRUE ) );
		
	}// summary()
	
	private function ensure_fleet_url()
	{
		if( !$this->libfleet->get_fleet_url() )
		{
			redirect( 'fleets2/get_fleet_url', 'location' );
		}
	}//	ensure_fleet_url()
	
	
	public function summary_json()
	{
		$this->_ensure_json_permission('CAN_MANAGE_ACTIVE_FLEETS');
		
		if( !$this->libfleet->get_fleet_url() )
		{
			// Need a valid fleet_url to manage a fleet
			//redirect('fleets2/get_fleet_url', 'location');
			$this->session->set_flashdata( 'flash_message', 'There was a problem with the fleet URL.' );
			$this->output->set_status_header( 403 );
			$this->OAuth_model->logout();
			return;
		}
		
		// Could remember last refresh time, and/or output JSON's checksum, and return a short value if no changes?
		
		$fleet = $this->libfleet->refresh_fleet_cache();
		if( $fleet['error'] != FALSE )
		{
			$this->session->set_flashdata( 'flash_message', 'There was a problem refreshing the fleet data.' );
			log_message( 'error', 'Fleets2 controller: summary_json(): ' . $fleet['error'] );
			$this->output->set_status_header( 403 );
			$this->OAuth_model->logout();
			return;
		}
		
		$keys = array_flip( array(
			'members_space',
			'member_count',
			'wing_names',
			'squad_names'
		) );
		$data = array_intersect_key( $fleet, $keys );
		
		$ships_to_members = array();
		$characterIDs = array();
		$solarSystemIDs = array();
		foreach( $fleet['fleet_members'] as $character_id => $member )
		{
			$ship_id = $member['ship_type_id'];
			if( array_key_exists( $ship_id, $ships_to_members ) )
			{
				$ships_to_members[$ship_id][] = $member;
			}
			else
			{
				$ships_to_members[$ship_id] = array( $member );
			}
			
			if( !in_array( $character_id, $characterIDs ) )
			{
				$characterIDs[] = $character_id;
			}
			
			$solar_system_id = $member['solar_system_id'];
			if( !in_array( $solar_system_id, $solarSystemIDs ) )
			{
				$solarSystemIDs[] = $solar_system_id;
			}
		}
		$data['ships_to_members'] = $ships_to_members;
		
		$named_ships = $this->Eve_SDE_model->get_types_names( array_keys( $ships_to_members ) );
		if( $named_ships == FALSE )
		{
			// Don't fail, make JS more resilient?
			$this->session->set_flashdata( 'flash_message', 'There was a problem refreshing the fleet data.' );
			log_message( 'error', 'Fleets2 controller: unable to resolve ship names for fleet summary.' );
			$this->output->set_status_header( 403 );
			$this->OAuth_model->logout();
			return;
		}
		foreach( $named_ships as $named_ship )
		{
			$ship_names[$named_ship['typeID']] = $named_ship['typeName'];
		}
		$data['ship_names'] = $ship_names;
		
		$named_characters = $this->CharacterID_model->get_character_names( $characterIDs );
		if( $named_characters == FALSE )
		{
			// Don't fail, make JS more resilient?
			$this->session->set_flashdata( 'flash_message', 'There was a problem refreshing the fleet data.' );
			log_message( 'error', 'Fleets2 controller: unable to resolve character names for fleet summary.' );
			$this->output->set_status_header( 403 );
			$this->OAuth_model->logout();
			return;
		}
		foreach( $named_characters as $named_character )
		{
			$character_names[$named_character['id']] = $named_character['name'];
		}
		$data['character_names'] = $character_names;
		
		$named_systems = $this->Eve_SDE_model->get_solarSystem_names( $solarSystemIDs );
		if( $named_systems == FALSE )
		{
			// Don't fail, make JS more resilient?
			$this->session->set_flashdata( 'flash_message', 'There was a problem refreshing the fleet data.' );
			log_message( 'error', 'Fleets2 controller: unable to resolve system names for fleet summary.' );
			$this->output->set_status_header( 403 );
			$this->OAuth_model->logout();
			return;
		}
		foreach( $named_systems as $named_system )
		{
			$system_names[$named_system['solarSystemID']] = $named_system['solarSystemName'];
		}
		$data['system_names'] = $system_names;
		
		$data['hasScheduleDetails'] = ( $this->libfleet->get_scheduled_datetime() !== NULL );
		
		$data['hasPinged'] = $this->libfleet->get_has_pinged();
		
		$data['hasDoctrine'] = $this->libfleet->get_has_doctrine();
		
		$data['csrf_hash'] = $this->security->get_csrf_hash();
		
		$this->output->set_content_type( 'application/json' );
		$this->output->set_status_header( 200 );
		$this->output->set_output( json_encode( $data ) );
		
	}// summary_json()
	/*
	public function get_blocked_list_json()
	{
		$this->_ensure_json_permission('CAN_MANAGE_ACTIVE_FLEETS');
		
		$blocked_list = $this->Channel_model->get_blocked_cache( array( 'character' ) );
		if( $blocked_list === FALSE )
		{
			log_message( 'error', 'Fleets2 controller: unable to get blocked list' );
			$this->output->set_status_header( 403 );
			$this->OAuth_model->logout();
			return;
		}
		
		$data['blocked_list'] = $blocked_list;
		
		$data['csrf_hash'] = $this->security->get_csrf_hash();
		
		$this->output->set_content_type( 'application/json' );
		$this->output->set_status_header( 200 );
		$this->output->set_output( json_encode( $data ) );
	}// get_blocked_list_json()
	*/
	public function forget_fleet_json()
	{
		$this->_ensure_json_permission('CAN_MANAGE_ACTIVE_FLEETS');
		
		$scheduledDateTime = $this->libfleet->get_scheduled_datetime();
		
		self::log_fc_action( 'Forgot fleet', $scheduledDateTime );
		
		//$this->libfleet->unset_scheduledDetails();
		$this->libfleet->unset_fleet();
		
		$this->OAuth_model->logout();
		
		$this->session->set_flashdata( 'flash_message', 'Thank you for using the Fleet & Invite Manager.' );
		
		$data['csrf_hash'] = $this->security->get_csrf_hash();
		
		$this->output->set_content_type( 'application/json' );
		$this->output->set_status_header( 200 );
		$this->output->set_output( json_encode( $data ) );
		
	}// forget_fleet_json()
	
	public function kick_banned_json()
	{
		$this->_ensure_json_permission('CAN_MANAGE_ACTIVE_FLEETS');
		
		$scheduledDateTime = $this->libfleet->get_scheduled_datetime();
		
		self::log_fc_action( 'Kicking banned members', $scheduledDateTime );
		
		$blocked_list = $this->Channel_model->get_blocked_cache( array( 'character' ) );
		if( $blocked_list === FALSE )
		{
			$this->session->set_flashdata( 'flash_message', 'There was a problem with the blocked list.' );	// Should instead send csrf_hash and be able to continue?
			log_message( 'error', 'Fleets2 controller: unable to get blocked list' );
			$this->output->set_status_header( 403 );
			$this->OAuth_model->logout();
			return;
		}
		
		$kick_results = $this->libfleet->kick_members( array_column( $blocked_list, 'accessorID' ) );
		if( $kick_results === FALSE )
		{
			log_message( 'error', 'Fleets2 controller: problem kicking members' );
			$this->output->set_content_type( 'application/json' );
			$this->output->set_status_header( 403 );
			$output['csrf_hash'] = $this->security->get_csrf_hash();
			$this->output->set_output( json_encode( $output ) );
			return;
		}
		
		$data['kick_results'] = $kick_results;
		
		$data['csrf_hash'] = $this->security->get_csrf_hash();
		
		$this->output->set_content_type( 'application/json' );
		$this->output->set_status_header( 200 );
		$this->output->set_output( json_encode( $data ) );
	}// kick_banned_json()
	
	
	public function choose_fleet()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_MANAGE_ACTIVE_FLEETS' );
		
		self::ensure_refresh_token();
		
		self::ensure_fleet_url();
		
		$data['fleets'] = $this->Activity_model->get_future_fleets( NULL, TRUE );
		$data['currentEVEtime'] = $this->_eve_now_dtz();
		$data['single_FC'] = FALSE;
		
		$this->form_validation->set_rules('scheduledDateTime', 'scheduledDateTime', 'required');	// Validate datetime format?
		
		if( $this->form_validation->run() == TRUE )
		{
			$scheduledDateTime = $this->input->post('scheduledDateTime');
			
			self::log_fc_action( 'Chose fleet time', $scheduledDateTime );
			
			// Ensure scheduled fleet exists at this datetime?
			$valid_fleet_datetime = FALSE;
			foreach( $data['fleets'] as $fleet )
			{
				if( $fleet->fleetTime == $scheduledDateTime )
				{
					$valid_fleet_datetime = TRUE;
					$this->libfleet->set_scheduled_details( $fleet );
					break;
				}
			}
			if( $valid_fleet_datetime === FALSE )
			{
				// No fleet matched that schedule datetime
				$this->session->set_flashdata( 'flash_message', 'There was no fleet found matching the given datetime.' );
				log_message( 'debug', 'Fleets2 controller: no fleet found matching scheduledDateTime:'.$scheduledDateTime,'.' );
				redirect('fleets2', 'location');
			}
			
			// Now go where the FC can use the fleet_url and fleet_scheduled_details
			//redirect('fleets2/invite_manager', 'location');
			redirect('fleets2', 'location');
		}
		else
		{
			// Missing or invalid datetime
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Choose a fleet to form' ) );
			$this->load->view( 'portal/portal_header' );
			$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
			$this->load->view( 'portal/portal_content' );
			$this->load->view( 'fleets2/choose_fleet', $data );
			$this->load->view( 'portal/portal_footer' );
			$this->load->view( 'common/footer');
		}
		
	}// choose_fleet()
	
	public function forget_scheduledDetails_json()
	{
		$this->_ensure_json_permission('CAN_MANAGE_ACTIVE_FLEETS');
		
		$scheduledDateTime = $this->libfleet->get_scheduled_datetime();
		
		self::log_fc_action( 'Forgot fleet scheduled details', $scheduledDateTime );
		
		$this->libfleet->unset_scheduledDetails();
		
		$data['csrf_hash'] = $this->security->get_csrf_hash();
		
		$this->output->set_content_type( 'application/json' );
		$this->output->set_status_header( 200 );
		$this->output->set_output( json_encode( $data ) );
		
	}// forget_scheduledDetails_json()
	
	public function set_fleet_motd_json()
	{
		$this->_ensure_json_permission('CAN_MANAGE_ACTIVE_FLEETS');
		
		$scheduled_details = $this->libfleet->get_scheduled_details();
		
		self::log_fc_action( 'Set MOTD & Free-move', $scheduled_details['datetime'] );
		
		$response = $this->libfleet->set_fleet_motd( $scheduled_details );
		if( $response != 204 )
		{
			log_message( 'error', "Fleets2 controller: Setting the fleet MOTD failed with the unexpected response code:$response. For user:".$this->session->user_session['UserID'].':'.$this->session->user_session['CharacterName'].'.' );
			
			//$this->session->set_flashdata( 'flash_message', 'There was a problem setting the fleet MOTD.' );
			$this->output->set_content_type( 'application/json' );
			$this->output->set_status_header( 403 );
			$output['csrf_hash'] = $this->security->get_csrf_hash();
			$this->output->set_output( json_encode( $output ) );
			return;
		}
		
		$data['csrf_hash'] = $this->security->get_csrf_hash();
		
		$this->output->set_content_type( 'application/json' );
		$this->output->set_status_header( 200 );
		$this->output->set_output( json_encode( $data ) );
		
	}// set_fleet_motd_json()
	
	public function ping_discord_json()
	{
		$this->_ensure_json_permission('CAN_MANAGE_ACTIVE_FLEETS');
		
		$scheduled_details = $this->libfleet->get_scheduled_details();
		$current_Username = $this->session->user_session['CharacterName'];
		
		$content = $this->libfleet->generate_ping_content( $scheduled_details, $current_Username );
		
		$UserID = $this->session->user_session['UserID'];
		
		$result = $this->Discord_model->ping_ops( $UserID, $scheduled_details['datetime'], $content );
		
		$this->libfleet->set_ping_result( $result );
		
		$data['csrf_hash'] = $this->security->get_csrf_hash();
		
		$this->output->set_content_type( 'application/json' );
		$this->output->set_status_header( $result['response'] ? 200 : 502 );
		$this->output->set_output( json_encode( $data ) );
		
	}// ping_discord_json()
	
	public function list_fits_json()
	{
		$this->_ensure_json_permission('CAN_MANAGE_ACTIVE_FLEETS');
		
		$scheduled_details = $this->libfleet->get_scheduled_details();
		
		if( $this->libfleet->get_has_doctrine() == FALSE )
		{
			log_message( 'error', 'Fleets2 controller: Scheduled fleet has no resolved doctrine ID, can\'t list fits' );
			$this->output->set_content_type( 'application/json' );
			$this->output->set_status_header( 403 );
			$output['csrf_hash'] = $this->security->get_csrf_hash();
			$this->output->set_output( json_encode( $output ) );
			return;
		}
		
		self::log_fc_action( 'List fits', $scheduled_details['datetime'] );
		
		$fleetID = $scheduled_details['doctrine'];
		$fleet_info = $this->Doctrine_model->get_fleet_info( $fleetID );
		if( $fleet_info === FALSE )
		{
			log_message( 'error', 'Fleets2 controller: Doctrine ID:'.$fleetID.' no longer valid' );
			$this->output->set_content_type( 'application/json' );
			$this->output->set_status_header( 403 );
			$output['csrf_hash'] = $this->security->get_csrf_hash();
			$this->output->set_output( json_encode( $output ) );
			return;
		}
		
		$ships = $this->Doctrine_model->get_fleet_fitIDs( $fleetID );
		
		$this->load->library( 'LibFit' );
		
		$DNAs = array();
		foreach( $ships as $ship )
		{
			$fitID = $ship['fitID'];
			$info = $this->Doctrine_model->get_fit_info( $fitID );
			if( $info !== FALSE )
			{
				$fit_items = $this->Doctrine_model->get_fit_items( $fitID, $info['shipID'], $info['isStrategicCruiser'] );
				$DNAs[$info['fitName']] = $this->libfit->generate_DNA( $info, $fit_items );
			}
		}
		
		$message = '<br><color=0xff'.libfleet::MOTD_BASE_COLOUR.'>"'. html_entity_decode( $fleet_info['fleetName'], ENT_QUOTES | ENT_HTML5 ) .'" fits:';
		
		foreach( $DNAs as $name => $DNA )
		{
			// Check resulting message length before combining, or LibFleet should deal with it?
			$message .= '<br><url=fitting:'.$DNA.'>'. html_entity_decode( $name, ENT_QUOTES | ENT_HTML5 ) .'</url>';
		}
		$message .= '</color>';
		
		$errors = $this->libfleet->inform_fleet( $message );
		if( !empty( $errors ) )
		{
			log_message( 'error', 'Fleets2 controller: list_fits_json(): ' . implode( "<br>\n", $errors ) );
			$this->output->set_content_type( 'application/json' );
			$this->output->set_status_header( 403 );
			$output['csrf_hash'] = $this->security->get_csrf_hash();
			$this->output->set_output( json_encode( $output ) );
			return;
		}
		
		$data['csrf_hash'] = $this->security->get_csrf_hash();
		
		$this->output->set_content_type( 'application/json' );
		$this->output->set_status_header( 200 );
		$this->output->set_output( json_encode( $data ) );
		
	}// list_fits_json()
	
	
	public function invite_manager()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_MANAGE_ACTIVE_FLEETS' );
		
		self::ensure_refresh_token();
		
		self::ensure_fleet_url();
		
		$data['fleet_scheduled_details'] = $this->libfleet->get_scheduled_details();
		
		$data['XUP_generated'] = $this->libfleet->generate_xup_motd( $data['fleet_scheduled_details'] );
		$data['XUP_blank'] = $this->libfleet->generate_blank_xup_motd();
		
		$data['hasPinged'] = $this->libfleet->get_has_pinged();
		
		$data['csrf_hash'] = $this->security->get_csrf_hash();
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Manage Invites' ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'fleets2/invite_manager', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer', array( 'TOGGLES' => TRUE ) );
		
	}// invite_manager()
	
	public function invite_manager_json()
	{
		$this->_ensure_json_permission('CAN_MANAGE_ACTIVE_FLEETS');
				
		$invites = $this->libfleet->refresh_fleet_invites_status();
		if( $invites['error'] != FALSE )
		{
			$this->session->set_flashdata( 'flash_message', 'There was a problem refreshing the fleet data.' );
			log_message( 'error', 'Fleets2 controller: invite_manager_json(): ' . $invites['error'] );
			$this->output->set_status_header( 403 );
			$this->OAuth_model->logout();
			return;
		}
		$keys = array_flip( array(
			'awaiting_invites',
			'invite_results',
			'member_count',
			'fleet_squad_capacity',
			'fleet_capacity',
			'non_squad_count',
			'members_space'
		) );
		$data = array_intersect_key( $invites, $keys );
		
		$data['invite_requests_count'] = count( $invites['invite_requests'] );
		
		$currentEveTime = $this->_eve_now_dtz();
		$data['currentEveTime'] = $currentEveTime->format( DateTime::ATOM );	// Because DateTime::ISO8601 isn't actually ISO-8601
		foreach( $data['awaiting_invites'] as &$awaiting_invites )
		{
			$lastInviteSent = $awaiting_invites['lastInviteSent'];
			if( $lastInviteSent != NULL )
			{
				$lastInviteSent = DateTime::createFromFormat( 'Y-m-d H:i:se', $lastInviteSent );
				$timeSince = $lastInviteSent->diff( $currentEveTime );
				$format = ( $timeSince->h == 0 ) ? '%r%i minutes' : '%r%h hours %i minutes';	// Ignore potential of >24 hours?
				$awaiting_invites['sinceLastInviteSent'] = $timeSince->format( $format );
			}
			else
			{
				$awaiting_invites['sinceLastInviteSent'] = '';
			}
		}
		
		$data['hasPinged'] = $this->libfleet->get_has_pinged();
		
		$data['hasDoctrine'] = $this->libfleet->get_has_doctrine();
		
		$data['csrf_hash'] = $this->security->get_csrf_hash();
		
		$this->output->set_content_type( 'application/json' );
		$this->output->set_status_header( 200 );
		$this->output->set_output( json_encode( $data ) );
		
	}// invite_manager_json()
	
	public function send_invites_json()
	{
		$this->_ensure_json_permission('CAN_MANAGE_ACTIVE_FLEETS');
		
		// Perform the inviting
		
		$data = $this->libfleet->refresh_fleet_invites_status();
		if( $data['error'] != FALSE )
		{
			log_message( 'error', 'Fleets2 controller: send_invites_json(): ' . $data['error'] );
			$this->output->set_content_type( 'application/json' );
			$this->output->set_status_header( 403 );
			$output['csrf_hash'] = $this->security->get_csrf_hash();
			$this->output->set_output( json_encode( $output ) );
			return;
		}
		
		$scheduledDateTime = $data['fleet_datetime'];
		
		self::log_fc_action( 'Sent invites', $scheduledDateTime );
		
		$result = $this->libfleet->send_invites( $data );
		
		$new_data['csrf_hash'] = $this->security->get_csrf_hash();
		
		$this->output->set_content_type( 'application/json' );
		$this->output->set_status_header( ( $result >= 0 ) ? 200 : 409 );	// 409 Conflict, no space or no outstanding invitees
		$this->output->set_output( json_encode( $new_data ) );
		
	}// send_invites_json()
	
}// Fleets2
?>