<?php
class Invites extends SF_Controller {
	
	
	public function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->model('Activity_model');
		$this->load->model('Invites_model');
	}// __construct()
	
	
	public function recent()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_MANAGE_ACTIVE_FLEETS' );
		
		$fleets_data['latestDetected'] = $this->Activity_model->get_latest_MOTD_merge();
		
		$fleets_data['fleets'] = $this->Invites_model->get_fleets_invites();
		$fleets_data['single_FC'] = FALSE;
		$fleets_data['currentEVEtime'] = $this->_eve_now_dtz();
		
		$fleets_html = $this->load->view( 'invites/scheduled_fleets_table', $fleets_data, TRUE );
		$data['fleets_html'] = $fleets_html;
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Recent Invites' ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'invites/view_scheduled_fleets', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer' );
	}// recent()
	
	public function check_latest()
	{
		$this->_ensure_local_request_or( 'CAN_VIEW_DEBUGGING' );
		
		$result = $this->Invites_model->latest_requests_not_honoured();
		
		if( count( $result ) > 0 )
		{
			$content = "The following new fleets have not yet honoured invite requests:\n";
			foreach( $result as $fleet )
			{
				$fleet_dtz = DateTime::createFromFormat( 'Y-m-d H:i:se', $fleet->fleetTime );
				$content .= 'Fleet Time: '. $fleet_dtz->format( 'l jS F Y \a\t H:i' ) ."; FC: $fleet->CharacterName;";
				$content .= " Requests: $fleet->not_cancelled; Invitees found in fleet: $fleet->detected; Invites Sent: $fleet->invitesSent.\n";
			}
			
			$this->load->model('Discord_model');
			
			$result = $this->Discord_model->tell_command( $content );
			if( $result['response'] == FALSE )
			{
				log_message( 'error', "Invites controller: failure to tell_command( $content )." );
			}
		}
	}// check_latest()
	
	public function request()
	{
		/*
		*	User must be logged in
		*	CharacterID must be known
		*	Scheduled fleet must exist
		*	User can't have requested an invite for this fleet already
		*/
		
		$this->_ensure_logged_in();
		
		$CharacterID = $this->session->user_session['CharacterID'];
		if( $CharacterID == '' )
		{
			// Missing CharacterID
			$this->session->set_flashdata( 'flash_message', 'Please relog once using SSO in order for SpectreFleet to verify your CharacterID.' );
			redirect('portal', 'location');
		}
		
		$data['fleets'] = $this->Activity_model->get_future_fleets( NULL, TRUE );
		$data['currentEVEtime'] = $this->_eve_now_dtz();
		$data['single_FC'] = FALSE;
		
		$outstanding_invite_requests = $this->Invites_model->get_outstanding_invite_requests( $CharacterID );
		if( $outstanding_invite_requests === FALSE )
		{
			// Problem getting user's existing invites
			$this->session->set_flashdata( 'flash_message', 'There was a problem with obtaining your existing invites.' );
			log_message( 'error', 'Invites controller: obtaining CharacterID:'.$CharacterID,'\'s existing invites failed.' );
			redirect('portal', 'location');
		}
		$invite_requests = array();
		foreach( $outstanding_invite_requests as $invite_request )
		{
			$invite_requests[$invite_request['fleetScheduled']] = $invite_request;	// Value-as-Key mapping to ease the check for existing request
		}
		$data['invite_requests'] = $invite_requests;
		
		$this->form_validation->set_rules('scheduledDateTime', 'scheduledDateTime', 'required');	// Validate datetime format?
		
		if( $this->form_validation->run() == TRUE )
		{
			$scheduledDateTime = $this->input->post('scheduledDateTime');
			
			// Ensure scheduled fleet exists at this datetime?
			$valid_fleet_datetime = FALSE;
			foreach( $data['fleets'] as $fleet )
			{
				if( $fleet->fleetTime == $scheduledDateTime )
				{
					$valid_fleet_datetime = TRUE;
					break;
				}
			}
			if( $valid_fleet_datetime === FALSE )
			{
				// No fleet matched that schedule datetime
				$this->session->set_flashdata( 'flash_message', 'There was no fleet found matching the given datetime.' );
				log_message( 'debug', 'Invites controller: no fleet found matching scheduledDateTime:'.$scheduledDateTime,'.' );
				redirect('portal', 'location');
			}
			
			// Ensure user hasn't requested an invite for this fleet already
			
			if( $this->Invites_model->add_invite_request( $scheduledDateTime, $CharacterID ) )
			{
				// Success registering invite
				redirect('invites/request', 'location');
			}
			else
			{
				// Problem registering invite
				$this->session->set_flashdata( 'flash_message', 'There was a problem with registering your fleet invite request.' );
				log_message( 'error', "Invites controller: failed to register invite request for CharacterID:$CharacterID, fleet time:$scheduledDateTime." );
				redirect('portal', 'location');
			}
		}
		else
		{
			// Missing or invalid datetime
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Request Invites' ) );
			$this->load->view( 'invites/request_invite', $data );
			$this->load->view( 'common/footer' );
		}
		
	}// request()
	
	public function cancel()
	{
		/*
		*	User must be logged in
		*	CharacterID must be known
		*	Scheduled fleet must exist
		*	User must have requested an invite for this fleet already
		*/
		
		$this->_ensure_logged_in();
		
		$CharacterID = $this->session->user_session['CharacterID'];
		if( $CharacterID == '' )
		{
			// Missing CharacterID
			$this->session->set_flashdata( 'flash_message', 'Please relog once using SSO in order for SpectreFleet to verify your CharacterID.' );
			redirect('portal', 'location');
		}
		
		$data['fleets'] = $this->Activity_model->get_future_fleets( NULL, TRUE );
		$data['currentEVEtime'] = $this->_eve_now_dtz();
		$data['single_FC'] = FALSE;
		
		$outstanding_invite_requests = $this->Invites_model->get_outstanding_invite_requests( $CharacterID );
		if( $outstanding_invite_requests === FALSE )
		{
			// Problem getting user's existing invites
			$this->session->set_flashdata( 'flash_message', 'There was a problem with obtaining your existing invites.' );
			log_message( 'error', 'Invites controller: obtaining CharacterID:'.$CharacterID,'\'s existing invites failed.' );
			redirect('portal', 'location');
		}
		$invite_requests = array();
		foreach( $outstanding_invite_requests as $invite_request )
		{
			$invite_requests[$invite_request['fleetScheduled']] = $invite_request;	// Value-as-Key mapping to ease the check for existing request
		}
		$data['invite_requests'] = $invite_requests;
		
		$this->form_validation->set_rules('scheduledDateTime', 'scheduledDateTime', 'required');	// Validate datetime format?
		
		if( $this->form_validation->run() == TRUE )
		{
			$scheduledDateTime = $this->input->post('scheduledDateTime');
			
			// Ensure scheduled fleet exists at this datetime?
			$valid_fleet_datetime = FALSE;
			foreach( $data['fleets'] as $fleet )
			{
				if( $fleet->fleetTime == $scheduledDateTime )
				{
					$valid_fleet_datetime = TRUE;
					break;
				}
			}
			if( $valid_fleet_datetime === FALSE )
			{
				// No fleet matched that schedule datetime
				$this->session->set_flashdata( 'flash_message', 'There was no fleet found matching the given datetime.' );
				log_message( 'debug', 'Invites controller: no fleet found matching scheduledDateTime:'.$scheduledDateTime,'.' );
				redirect('portal', 'location');
			}
			
			// Ensure user has requested an invite for this fleet already
			
			if( $this->Invites_model->cancel_invite_request( $scheduledDateTime, $CharacterID ) )
			{
				// Success cancelling invite
				redirect('invites/request', 'location');
			}
			else
			{
				// Problem cancelling invite
				$this->session->set_flashdata( 'flash_message', 'There was a problem with cancelling your fleet invite request.' );
				log_message( 'error', "Invites controller: failed to cancel invite request for CharacterID:$CharacterID, fleet time:$scheduledDateTime." );
				redirect('portal', 'location');
			}
		}
		else
		{
			// Missing or invalid datetime
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Request Invites' ) );
			$this->load->view( 'invites/request_invite', $data );
			$this->load->view( 'common/footer' );
		}
		
	}// cancel()
	
}// Invites
?>