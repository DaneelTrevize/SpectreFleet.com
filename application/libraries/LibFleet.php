<?php if( !defined('BASEPATH') ) exit ('No direct script access allowed');

/**
 * Live Fleet library.
 *
 * @author Daneel Trevize
 */

class LibFleet
{
	
	
	const SLOW_INVITES_THRESHOLD = 16;
	const SLOW_INVITES_PERIOD = 5;
	const UNEXPECTED_THRESHOLD = 3;
	
	const DATETIME_DB_FORMAT = 'Y-m-d H:i:se';
	const FORMUP_DURATION = 'PT30M';
	const WINGS_PER_FLEET = 25;
	const SQUADS_PER_WING = 25;
	const SQUADS_PER_FLEET = self::WINGS_PER_FLEET * self::SQUADS_PER_WING;
	const MEMBERS_PER_SQUAD = 256;
	
	const MOTD_BASE_COLOUR = 'ffffff';
	const MOTD_MUTED_COLOUR = 'b2b2b2';
	const FORMING_HIGHLIGHT_COLOUR = '00ff00';
	
	
	private $CI;
	
	public function __construct()
	{
		$this->CI =& get_instance();	// Assign the CodeIgniter object to a variable
		$this->CI->load->model('Fleets_model');
		$this->CI->load->model('Invites_model');
		$this->CI->config->load('ccp_api');
		$this->CI->load->library( 'LibOAuthState', array('key'=>'eve'), 'OAuth_model' );
        $this->CI->load->library( 'LibOAuth2', $this->CI->config->item('oauth_eve'), 'oauth_eve' );
		$this->CI->load->library( 'LibRestAPI', $this->CI->config->item('rest_esi'), 'rest_esi' );
	}// __construct()
	
	
	public function try_detect_fleet_ID( $CharacterID )
	{
		if( !$this->CI->OAuth_model->ensure_fresh_token( $this->CI->oauth_eve ) )
		{
			return FALSE;
		}
		
		$character_fleet = $this->CI->Fleets_model->get_character_fleet( $CharacterID, $this->CI->OAuth_model->get_auth_token() );
		if( $character_fleet === FALSE )
		{
			log_message( 'error', 'LibFleet: get_character_fleet() failure.' );
			return FALSE;
		}
		
		// Still need to verify we're boss of this fleet?!
		
		// We have a new fleet ID or some cached value (possibly NULL)
		
		$fleet_id = $character_fleet['fleet_id'];
		if( $fleet_id === NULL )
		{
			//log_message( 'error', "LibFleet: cached character_fleet details were not those of a fleet member." );
			return FALSE;
		}
		
		// Verify we're boss of this fleet, by reading more fleet data?
		// If it's bad, cache expires timer helps to avoid error spam
		// If it's good, it's no problem to have warmed fleet details cache.
		
		$fleet_response_decoded = $this->CI->Fleets_model->get_fleet( $fleet_id, $this->CI->OAuth_model->get_auth_token() );
		if( $fleet_response_decoded === FALSE )
		{
			// Problem using the Fleet URL
			self::log_error_user( 'Warming fleet cache from ESI failed.' );
			return FALSE;
		}
		
		// Persist working values
		self::set_fleet_id( $fleet_id );
		
		return TRUE;
	}// try_detect_fleet_ID()
	
	private function log_error_user( $message )
	{
		$user_session = $this->CI->session->user_session;
		log_message( 'error', 'LibFleet: ' . $message . ' For user:'.$user_session['UserID'].':'.$user_session['CharacterName'].'.' );
	}// log_error_user()
	
	
	public function set_fleet_id( $fleet_id )
	{
		if( $fleet_id == NULL )
		{
			unset( $_SESSION['fleet']['id'] );
			unset( $_SESSION['fleet']['URL'] );
		}
		else
		{
			$_SESSION['fleet']['id'] = $fleet_id;
			$ESI_ROOT = $this->CI->config->item( 'rest_esi' )['root'];
			$_SESSION['fleet']['URL'] = $ESI_ROOT.'/v1/fleets/'. $_SESSION['fleet']['id'] .'/';
		}
	}// set_fleet_id()
	
	public function get_fleet_id()
	{
		return isset($_SESSION['fleet']['id']) ? $_SESSION['fleet']['id'] : FALSE;
	}// get_fleet_id()
	
	public function get_fleet_url()
	{
		return isset($_SESSION['fleet']['URL']) ? $_SESSION['fleet']['URL'] : FALSE;
	}// get_fleet_url()
	
	public function refresh_fleet_cache()
	{
		
		if( !$this->CI->OAuth_model->ensure_fresh_token( $this->CI->oauth_eve ) )
		{
			return array(
				'error' => 'Unable to refresh authentication tokens.'
			);
		}
		
		$fleet_url = self::get_fleet_url();
		if( !$fleet_url )
		{
			log_message( 'error', 'LibFleet: refresh_fleet_cache() was called without ensuring fleet_url exists.' );
			return array(
				'error' => 'No External Fleet Link provided.'
			);
		}
		
		$fleet_response_decoded = $this->CI->Fleets_model->get_fleet( self::get_fleet_id(), $this->CI->OAuth_model->get_auth_token() );
		if( $fleet_response_decoded === FALSE )
		{
			// Problem using the Fleet URL
			self::unset_fleet();
			self::unset_scheduledDetails();
			
			self::log_error_user( 'Refreshing fleet cache from ESI failed.' );
			
			return array(
				'error' => 'There was a problem refreshing the fleet cache.'
			);
		}
		
		// Get members
		$members_response_decoded = $this->CI->Fleets_model->get_fleet_members( self::get_fleet_id(), $this->CI->OAuth_model->get_auth_token() );
		if( $members_response_decoded === FALSE )
		{
			// Problem getting members details
			self::log_error_user( 'Listing the fleet members failed.' );
			return array(
				'error' => 'There was a problem with obtaining the fleet member details.'
			);
		}
		
		// Get wings and squads
		$fleet_wings_squads = $this->CI->Fleets_model->get_fleet_wings_squads( self::get_fleet_id(), $this->CI->OAuth_model->get_auth_token() );
		if( $fleet_wings_squads === FALSE )
		{
			// Problem getting members details
			self::log_error_user( 'Listing the fleet wings failed.' );
			return array(
				'error' => 'There was a problem with obtaining the fleet wing details.'
			);
		}
		
		$capacity = self::fleet_capacity_status( $fleet_wings_squads, $members_response_decoded );
		
		$fleet = array();
		$fleet['fleet_details'] = $fleet_response_decoded;
		$fleet['fleet_members'] = $members_response_decoded;
		$fleet['member_count'] = count( $members_response_decoded );
		$fleet = array_merge( $fleet, $capacity );
		
		$fleet['error'] = FALSE;
		
		return $fleet;
	}// refresh_fleet_cache()
	
	public function unset_fleet()
	{
		unset( $_SESSION['fleet'] );
	}// unset_fleet()
	
	
	public function set_scheduled_details( $fleet )
	{
		$fleet_datetime = new DateTime( $fleet->fleetTime );
		$scheduled_fleet = array(
			'type' => $fleet->type,
			'datetime' => $fleet->fleetTime,
			'pretty_date' => $fleet_datetime->format( 'l jS F' ),
			'remaining_details' => $fleet->additionalDetails,
			'time' => $fleet_datetime->format( 'H:i' ),
			'FC' => $fleet->CharacterName,
			'FC_ID' => Array
				(
					'UserID' => $fleet->FC_ID,
					'CharacterName' => $fleet->CharacterName,
					'CharacterID' => $fleet->CharacterID
				),
			'location_exact' => $fleet->locationExact,
			'location_ID' => $fleet->locationID,
			'location_name' => $fleet->solarSystemName,
			'doctrine' => $fleet->doctrineID,
			'doctrine_name' => $fleet->fleetName
		);
		
		$_SESSION['fleet']['scheduled_details'] = $scheduled_fleet;
		$_SESSION['fleet']['hasPinged'] = FALSE;	// Actually should check fc_action_log...
		$_SESSION['fleet']['hasDoctrine'] = ($scheduled_fleet['doctrine'] != '' && $scheduled_fleet['doctrine_name'] != FALSE);
	}// set_scheduled_details()
	
	public function get_scheduled_details()
	{
		return isset($_SESSION['fleet']['scheduled_details']) ? $_SESSION['fleet']['scheduled_details'] : FALSE;
	}// get_scheduled_details()
	
	public function get_scheduled_datetime()
	{
		return isset($_SESSION['fleet']['scheduled_details']) ? $_SESSION['fleet']['scheduled_details']['datetime'] : NULL;
	}// get_scheduled_datetime()
	
	public function unset_scheduledDetails()
	{
		unset( $_SESSION['fleet']['scheduled_details'] );
		unset( $_SESSION['fleet']['hasPinged'] );
		unset( $_SESSION['fleet']['hasDoctrine'] );
	}// unset_scheduledDetails()
	
	public function set_ping_result( $result )
	{
		$_SESSION['fleet']['hasPinged'] = $result;
	}// set_ping_result()
	
	public function get_has_pinged()
	{
		// Could also instead test for isset 'scheduled_details'?
		return isset($_SESSION['fleet']['hasPinged']) ? $_SESSION['fleet']['hasPinged'] : FALSE;
	}// get_has_pinged()
	
	public function get_has_doctrine()
	{
		// Could also instead test for isset 'scheduled_details'?
		return isset($_SESSION['fleet']['hasDoctrine']) ? $_SESSION['fleet']['hasDoctrine'] : FALSE;
	}// get_has_doctrine()
	
	public function refresh_fleet_invites_status()
	{
		
		$fleet = self::refresh_fleet_cache();
		if( $fleet['error'] != FALSE )
		{
			return array(
				'error' => $fleet['error']
			);
		}
		
		$scheduledDateTime = self::get_scheduled_datetime();
		if( $scheduledDateTime === NULL )
		{
			log_message( 'error', 'LibFleet: lack of get_scheduled_datetime.' );
			return array(
				'error' => 'lack of get_scheduled_datetime.'
			);
		}
		
		$invite_requests = $this->CI->Invites_model->get_invite_requests( $scheduledDateTime );
		if( $invite_requests === FALSE )
		{
			// Problem getting fleet's invite requests
			log_message( 'error', 'LibFleet: obtaining scheduledDateTime:'.$scheduledDateTime,'\'s invite requests failed.' );
			return array(
				'error' => 'There was a problem with the fleet\'s invite requests.'
			);
		}
		
		$fleet_members = $fleet['fleet_members'];
		
		$data = self::fleet_invites_status( $invite_requests, $fleet_members );
		$data['member_count'] = $fleet['member_count'];
		
		$detectedDateTimeString = self::datetime_string( self::getCurrentEveDateTime() );
		$data['detectedDateTimeString'] = $detectedDateTimeString;
		
		$already_inFleet = $data['already_inFleet'];
		foreach( $already_inFleet as $CharacterID => $value )
		{
			$detectedInFleet = $value['detectedInFleet'];
			if( $detectedInFleet == NULL )
			{
				$logged_inFleet = $this->CI->Invites_model->record_detectedInFleet( $scheduledDateTime, $CharacterID, $detectedDateTimeString );
				if( !$logged_inFleet ) {
					log_message( 'error', "LibFleet: failure to record detectedInFleet for $scheduledDateTime, $CharacterID, $detectedDateTimeString." );
				}
			}
		}
		
		$data['fleet_datetime'] = $scheduledDateTime;
		
		$data['fleet_squad_capacity'] = $fleet['fleet_squad_capacity'];
		$data['fleet_capacity'] = $fleet['fleet_capacity'];
		$data['non_squad_count'] = $fleet['non_squad_count'];
		$data['members_space'] = $fleet['members_space'];
		$data['fleet_id'] = self::get_fleet_id();
		
		$data['error'] = FALSE;
		
		return $data;
	}// refresh_fleet_invites_status()
	
	public function set_fleet_motd( $fleet_scheduled_details )
	{
		if( !$this->CI->OAuth_model->ensure_fresh_token( $this->CI->oauth_eve ) )
		{
			return array(
				'error' => 'Unable to refresh authentication tokens.'
			);
		}
		
		$motd = self::generate_fleet_motd( $fleet_scheduled_details );
		
		$put_array = array(
			'is_free_move' => TRUE,
			'motd' => $motd
		);
		
		$response = $this->CI->rest_esi->do_call( self::get_fleet_url(), $this->CI->OAuth_model->get_auth_token(), $put_array, 'PUT' );
		if( $response !== FALSE )
		{
			$response = $response['response_code'];
		}
		return $response;	// Should log error?
	}// set_fleet_motd()
	
	private function generate_fleet_motd( $fleet_scheduled_details )
	{
		$formup_time = $fleet_scheduled_details['time'];
		$f_datetime = new DateTime( $fleet_scheduled_details['datetime'] );
		$d_datetime = $f_datetime->add( new DateInterval( self::FORMUP_DURATION ) );
		$departure_time = $d_datetime->format( 'H:i' );
		
		$FC_ingame_link = $fleet_scheduled_details['FC'];
		$FC_feedback_link = '<loc><a href="https://spectrefleet.com/feedback/">Feedback Form</a></loc>';
		$FC_ID = $fleet_scheduled_details['FC_ID'];
		if( $FC_ID !== FALSE )
		{
			$FC_ingame_link = '<loc><a href="showinfo:1377//' . $FC_ID['CharacterID'] . '">' . $FC_ID['CharacterName'] . '</a></loc>';
			
			$FC_feedback_link = '<loc><a href="https://spectrefleet.com/feedback/' . $FC_ID['UserID'] . '">Feedback Form</a></loc>';
		}
		
		$Staging_link = $fleet_scheduled_details['location_name'];
		if( $fleet_scheduled_details['location_ID'] !== FALSE )
		{
			$Staging_link = '<loc><a href="showinfo:5//' . $fleet_scheduled_details['location_ID'] . '">' . $fleet_scheduled_details['location_name'] . '</a></loc>';	// Detect w-space/Thera and offer Eve-scout?
			if( !$fleet_scheduled_details['location_exact'] )
			{
				$Staging_link = 'Near ' . $Staging_link;
			}
		}
		
		$doctrine_link = '';
		if( $fleet_scheduled_details['doctrine'] != '' )
		{
			$doctrine_link = '<loc><a href="https://spectrefleet.com/doctrine/fleet/' . $fleet_scheduled_details['doctrine'] . '">' . $fleet_scheduled_details['doctrine_name'] . '</a></loc>';
		}
		
		$mumble_link = '<loc><a href="https://spectrefleet.com/mumble">Mumble</a></loc>';
		
		$remaining_details = trim( $fleet_scheduled_details['remaining_details'] );
		
		$motd = '<font color="#ff'.self::MOTD_BASE_COLOUR.'"><br><br>Comms: '.$mumble_link.' | Channel: ---<br>FC: ' .$FC_ingame_link. ' | ' .$FC_feedback_link. '<br>Form-up Time: ' .$formup_time. '<br>Departure Time: ' .$departure_time. '<br>Staging: ' .$Staging_link. '<br>Doctrine: ' .$doctrine_link. '<br>Details: ' . $remaining_details . '</font><font color="#ff'.self::MOTD_BASE_COLOUR.'"><br>Status: Planned // </font><font color="#ff'.self::FORMING_HIGHLIGHT_COLOUR.'">Forming</font><font color="#ff'.self::MOTD_BASE_COLOUR.'"> // Departed // Closed<br></font>';
		
		$motd = html_entity_decode( $motd, ENT_QUOTES | ENT_HTML5 );
		
		return $motd;
	}// generate_fleet_motd()
	
	public function generate_xup_motd( $fleet_scheduled_details )
	{
		if( $fleet_scheduled_details == FALSE )
		{
			return '';	// generate_blank_xup_motd()?
		}
		
		$formup_time = $fleet_scheduled_details['time'];
		$f_datetime = new DateTime( $fleet_scheduled_details['datetime'] );
		
		$FC_ingame_link = $fleet_scheduled_details['FC'];
		$FC_ID = $fleet_scheduled_details['FC_ID'];
		if( $FC_ID !== FALSE )
		{
			$FC_ingame_link = '<loc><a href="showinfo:1377//' . $FC_ID['CharacterID'] . '">' . $FC_ID['CharacterName'] . '</a></loc>';
		}
		
		$Staging_link = $fleet_scheduled_details['location_name'];
		if( $fleet_scheduled_details['location_ID'] !== FALSE )
		{
			$Staging_link = '<loc><a href="showinfo:5//' . $fleet_scheduled_details['location_ID'] . '">' . $fleet_scheduled_details['location_name'] . '</a></loc>';	// Detect w-space/Thera and offer Eve-scout?
			if( !$fleet_scheduled_details['location_exact'] )
			{
				$Staging_link = 'Near ' . $Staging_link;
			}
		}
		
		$doctrine_link = '';
		if( $fleet_scheduled_details['doctrine'] != '' )
		{
			$doctrine_link = '<loc><a href="https://spectrefleet.com/doctrine/fleet/' . $fleet_scheduled_details['doctrine'] . '">' . $fleet_scheduled_details['doctrine_name'] . '</a></loc>';
		}
		
		$motd = '<font color="#ff'.self::MOTD_BASE_COLOUR.'"><br><a href="joinChannel:player_086148705b9811e883ab9abe94f5a39b//None//None">SF Spectre Fleet</a><br><br>FC: ' .$FC_ingame_link. '<br>TIME: ' .$formup_time. '<br>STAGING: ' .$Staging_link. '<br>DOCTRINE: ' .$doctrine_link. '<br><br>Status: Planned // </font><font color="#ff'.self::FORMING_HIGHLIGHT_COLOUR.'">Forming</font><font color="#ff'.self::MOTD_BASE_COLOUR.'"> // Departed // Closed<br><br><font size="10">You can <loc><a href="http://spectrefleet.com/invites/request>X-Up Online</a></loc> and avoid needing to x in this channel at form-up time.</font></font><br><br><font color="#ff007fff">Want to FC for Spectre?</font><br><a href="https://spectrefleet.com/join_command>Join Command</a>';
		
		return $motd;
	}// generate_xup_motd()
	
	public function generate_blank_xup_motd()
	{
		return '<br><font size="12" color="#fff7931e"><a href="joinChannel:player_086148705b9811e883ab9abe94f5a39b//None//None">SF Spectre Fleet</a><br><br></font><font size="12" color="#ffffffff">FC:<br>TIME: <br>STAGING:<br>DOCTRINE: <br><br>Status: Planned // Forming // Departed // Closed<br><br></font><font size="10" color="#ffffffff">You can </font><font size="12" color="#ffffa600"><loc><a href="http://spectrefleet.com/invites/request">X-Up Online</a></loc></font><font size="12" color="#ffffffff"> and avoid needing to x in this channel at form-up time.<br><br></font><font size="12" color="#ff007fff">Want to FC for Spectre?<br></font><font size="12" color="#ffffa600"><a href="https://spectrefleet.com/join_command">Join Command</a></font>';
	}// generate_blank_xup_motd()
	
	public function inform_fleet( $message )
	{
		$errors = array();
		
		$fleet = self::refresh_fleet_cache();
		if( $fleet['error'] != FALSE )
		{
			$errors[] = $fleet['error'];
			return $errors;
		}
		
		$fleet_url = self::get_fleet_url();
		
		$prior_motd = $fleet['fleet_details']['motd'];
		
		$put_array = array(
			'motd' => $message	// Check message length, perhaps needs splitting, but intelligently?
		);
		$response = $this->CI->rest_esi->do_call( $fleet_url, $this->CI->OAuth_model->get_auth_token(), $put_array, 'PUT' );
		if( $response === FALSE || $response['response_code'] != 204 )
		{
			self::log_error_user( 'Informing fleet failed with the unexpected response:'. print_r( $response, TRUE ) );
			$errors[] = 'There was a problem informing fleet.';
		}
		
		$put_array = array(
			'motd' => $prior_motd
		);
		$response = $this->CI->rest_esi->do_call( $fleet_url, $this->CI->OAuth_model->get_auth_token(), $put_array, 'PUT' );
		if( $response === FALSE || $response['response_code'] != 204 )
		{
			self::log_error_user( 'Restoring prior MOTD failed with the unexpected response:'. print_r( $response, TRUE ) );
			$errors[] = 'There was a problem restoring prior MOTD.';
		}
		
		return $errors;
	}// inform_fleet()
	
	public function kick_members( $kick_list )
	{
		$fleet = self::refresh_fleet_cache();
		if( $fleet['error'] != FALSE )
		{
			return FALSE;
		}
		
		$kick_members = array();
		foreach( $fleet['fleet_members'] as $character_id => $fleet_member )
		{
			if( array_key_exists( $character_id, $kick_list ) )
			{
				$kick_members[] = $character_id;
			}
		}
		
		$fleet_url = self::get_fleet_url();
		
		$kicked_members = array();
		$failed_members = array();
		foreach( $kick_members as $memberID )
		{
			$member_url = $fleet_url.'members/'.$memberID.'/';
			$response = $this->CI->rest_esi->do_call( $member_url, $this->CI->OAuth_model->get_auth_token(), array(), 'DELETE' );
			if( $response === FALSE || $response['response_code'] != 204 )
			{
				self::log_error_user( 'Kicking member ID:'.$memberID.' failed with the unexpected response: '. print_r( $response, TRUE ) );
				
				$failed_members[] = $memberID;
				
				continue;
			}
			else
			{
				$kicked_members[] = $memberID;
			}
		}
		
		return array(
			'kicked' => $kicked_members,
			'failed' => $failed_members
		);
	}// kick_members()
	
	
	public function send_invites( $data )	// Why take data when we could just invoke refresh_fleet_invites_status() ourselves?
	{
		$scheduledDateTime = $data['fleet_datetime'];
		$inviteDateTimeString = self::datetime_string( self::getCurrentEveDateTime() );
		
		$members_space = $data['members_space'];
		$awaiting_invites = $data['awaiting_invites'];
		$awaiting_count = count($awaiting_invites);
		
		if( $awaiting_count === 0 )
		{
			return 0;
		}
		if( $awaiting_count > $members_space )
		{
			// Fleet needs expanding first, check if we can, and try do so?
			
			$increase_by_target = $awaiting_count - $members_space;
			
			return -$increase_by_target;
		}
		
		$fleet_id = $data['fleet_id'];
		
		$invite_results = 0;
		$datetime_pending_invites_cleared_by = new DateTime( '-1 minute', new DateTimeZone( 'UTC' ) );
		
		$unexpected_responses_count = 0;
		
		foreach( $awaiting_invites as $CharacterID => $awaiting_invite )
		{
			$invitesSent = $awaiting_invite['invitesSent'];
			if( self::should_send_invite( $invitesSent, $awaiting_invite['lastInviteSent'], $datetime_pending_invites_cleared_by ) ) {
				
				$response = self::invite_verified_character( $fleet_id, $CharacterID );
				$response_code = ($response === FALSE) ? FALSE : $response['response_code'];
				$logged_invite = $this->CI->Invites_model->record_invite_sent( $scheduledDateTime, $CharacterID, $inviteDateTimeString, $response_code );
				if( !$logged_invite ) {
					log_message( 'error', "LibFleet: failure to record invite_sent for $scheduledDateTime, $CharacterID, $inviteDateTimeString, $response_code." );
				}
				
				if( $response_code != 204 && $response_code != 520 )	// && $response_code != 422
				{
					// 400: "Cannot find an position for new member!"
					// 420: "{'error_label': 'FleetCandidateOffline', 'error_dict': {'invitee': (2, 1637604641)}}" Still?
					// 520: "error":"{'error_label': 'FleetCandidateOffline', 'error_dict': {'invitee': (2, 93652455)}}"
					self::log_error_user( 'Sending an invite failed with the unexpected response:'. print_r( $response, TRUE ) );
					$unexpected_responses_count++;
				}
				/*
				$invite_results[$CharacterID] = array(
					'CharacterName' => $awaiting_invite['CharacterName'],
					'lastInviteSent' => $inviteDateTimeString,
					'response' => $response,
					'logged' => $logged_invite
				);*/
				$invite_results += 1;
				
				if( $unexpected_responses_count >= self::UNEXPECTED_THRESHOLD )
				{
					self::log_error_user( 'Halting invite batch early to minimise potential ESI errors.' );
					break;
				}
			}
			else
			{
				if( $invitesSent >= self::SLOW_INVITES_THRESHOLD )
				{
					log_message( 'debug', "LibFleet: skipping sending an invite for $scheduledDateTime, $CharacterID, $inviteDateTimeString." );
				}
			}
		}
		
		return $invite_results;
	}// send_invites()
	
	private function invite_verified_character( $fleet_id, $CharacterID )
	{
		$fleet_members_url = self::get_fleet_url().'members/';
		
		$fields = array(
			'character_id' => $CharacterID,
			'role' => 'squad_member'
		);
		
		return $this->CI->rest_esi->do_call( $fleet_members_url, $this->CI->OAuth_model->get_auth_token(), $fields, 'POST_JSON' );
	}// invite_verified_character()
	
	
	public static function generate_ping_content( $fleet_scheduled_details, $current_Username )
	{
		$FC_ID = $fleet_scheduled_details['FC_ID'];
		$FC_name = $current_Username;
		if( $FC_ID !== FALSE )
		{
			$FC_name = '['.$FC_ID['CharacterName'] .'](https://spectrefleet.com/feedback/'.$FC_ID['UserID'].')';
		}
		
		$fleet_datetime = new DateTime( $fleet_scheduled_details['datetime'] );
		$time = '['. $fleet_scheduled_details['time'] .' fleet](https://spectrefleet.com/activity/fleet/'. $fleet_datetime->format( 'Y-m-d/H:i' ) .')';
		
		$location_string = ' in ';
		if( $fleet_scheduled_details['location_ID'] !== FALSE )
		{
			if( !$fleet_scheduled_details['location_exact'] )
			{
				$location_string = ' near ';
			}
			$location_string .= link_solar_system( $fleet_scheduled_details['location_name'], TRUE );
		}
		else
		{
			$location_string = ', location ' . $fleet_scheduled_details['location_name'];
		}
		
		$doctrine_string = '';
		if( $fleet_scheduled_details['doctrine'] != '' )
		{
			if( $fleet_scheduled_details['doctrine_name'] !== FALSE )
			{
				$doctrine_string = '. Doctrine: [' . $fleet_scheduled_details['doctrine_name'] .'](https://spectrefleet.com/doctrine/fleet/'. $fleet_scheduled_details['doctrine'] .')';
			}
			else
			{
				$doctrine_string = '. Doctrine: [FleetID:' . $fleet_scheduled_details['doctrine'].'](https://spectrefleet.com/doctrine/fleet/'. $fleet_scheduled_details['doctrine'] .')';
			}
		}
		
		$content = '@here ' .$FC_name. '\'s ' .$time. ' is forming' .$location_string . $doctrine_string .'.';
		
		$remaining_details = $fleet_scheduled_details['remaining_details'];
		if( $remaining_details != ''  )
		{
			$content .= ' Additional details: ' .$remaining_details;
		}
		
		$content = html_entity_decode( $content, ENT_QUOTES | ENT_HTML5 );
		
		return $content;
	}// generate_ping_content()
	
	
	private static function getCurrentEveDateTime()
	{
		return new DateTime( 'now', new DateTimeZone( 'UTC' ) );
	}// getCurrentEveDateTime()
	
	private static function datetime_string( $datetime )
	{
		return $datetime->format(self::DATETIME_DB_FORMAT) . '+00';	// UTC TZ, in format not supported by date();
	}// datetime_string()
	
	private static function fleet_capacity_status( $fleet_wings_squads, $fleet_members )
	{
		$wing_names = $fleet_wings_squads['wing_names'];
		$squad_names = $fleet_wings_squads['squad_names'];
		
		// Calculate capacity
		
		$squad_capacity = min(256, (count( $squad_names ) - 1) * self::MEMBERS_PER_SQUAD );	// - 1 for '-1' name of FC's position's "squad"
		$wingcommander_capacity = (count( $wing_names ) - 1);								// - 1 for '-1' name of FC's position's "wing"
		$fleet_capacity = min(256, $squad_capacity + $wingcommander_capacity + 1 );			// + 1 for FC position
		
		// Count fleet/wing commanders vs squad commanders/members
		$member_count = 0;
		$non_squad_count = 0;
		foreach( $fleet_members as $fleet_member )
		{
			$member_count += 1;
			if( $fleet_member['squad_id'] === -1 )
			{
				$non_squad_count += 1;
			}
		}
		
		return array(
			'wing_names' => $wing_names,
			'squad_names' => $squad_names,
			'fleet_squad_capacity' => $squad_capacity,
			'fleet_capacity' => $fleet_capacity,
			'non_squad_count' => $non_squad_count,
			'members_space' => max( 0, $squad_capacity - ( $member_count - $non_squad_count ) )
		);
	}// fleet_capacity_status()
	
	private static function fleet_invites_status( $invite_requests, $fleet_members )
	{
		$data['invite_requests'] = $invite_requests;
		
		$requesting_characters = array();
		foreach( $invite_requests as $invite_request )
		{
			$requesting_characters[$invite_request['CharacterID']] = $invite_request;
		}
		
		$data['fleet_members'] = $fleet_members;
		
		$fleet_characters = array();
		foreach( $fleet_members as $character_id => $fleet_member )
		{
			$fleet_characters[$character_id] = NULL;
		}
		
		$already_inFleet = array_intersect_key( $requesting_characters, $fleet_characters );
		$data['already_inFleet'] = $already_inFleet;
		
		$awaiting_invites = array_diff_key( $requesting_characters, $fleet_characters );
		$data['awaiting_invites'] = $awaiting_invites;
		
		return $data;
	}// fleet_invites_status()
	
	private static function should_send_invite( $invitesSent, $lastInviteSent, $datetime_pending_invites_cleared_by )
	{
		if( $invitesSent == 0 )
		{
			return TRUE;
		}
		$datetime_lastInviteSent = DateTime::createFromFormat( self::DATETIME_DB_FORMAT, $lastInviteSent );
		if( $datetime_lastInviteSent < $datetime_pending_invites_cleared_by )
		{
			// We've tried inviting them before but they're not currently in fleet
			if( $invitesSent < self::SLOW_INVITES_THRESHOLD )
			{
				return TRUE;
			}
			else
			{
				// We've tried every minute for 15 total minutes, 16 invites sent in total.
				$minute_int_of_1min_ago = intval( $datetime_pending_invites_cleared_by->format( 'i' ) );
				// Send an invite only ~every 5minutes
				if( $minute_int_of_1min_ago % self::SLOW_INVITES_PERIOD == (self::SLOW_INVITES_PERIOD - 1) )
				{
					return TRUE;
				}
				else
				{
					return FALSE;
				}
			}
		}
	}// should_send_invite()
	
}// LibFleet
?>