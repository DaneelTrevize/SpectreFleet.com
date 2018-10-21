<?php
if( !defined('BASEPATH') ) exit('No direct script access allowed');

class Manage extends SF_Controller {	// Management of FCs, not Editors or Admins/general users
	
	
	public function __construct()
	{
		parent::__construct();
		$this->load->library( 'form_validation' );
		$this->load->model( 'User_model' );
		$this->load->model( 'Command_model' );
		$this->load->model( 'Discord_model' );
	}// __construct()
	
	
	public function apply()
	{
		$this->_ensure_logged_in();
		
		// All FCs are able to see the empty form, to help guide new applicants
		$this->_ensure_one_of( array('CAN_SUBMIT_FC_APPLICATION', 'CAN_HELP_FC_APPLICANTS') );
		
		// Form and Credential Validation.
		$this->form_validation->set_rules('SFexp', 'Spectre Fleet experience', 'required|max_length[2000]');
		$this->form_validation->set_rules('priorFC', 'FCing experience', 'required|max_length[2000]');
		$this->form_validation->set_rules('whySF', 'Why Spectre Fleet', 'required|max_length[2000]');
		$this->form_validation->set_rules('Timezone', 'fleet timezone', 'required|callback__valid_timezone');
		$this->form_validation->set_rules('fleetStyle', 'fleet style', 'required|max_length[2000]');
		$this->form_validation->set_rules('fleetSize', 'fleet size', 'required|max_length[2000]');
		
		if( $this->form_validation->run() == TRUE )
		{
			$SFexp = htmlentities( $this->input->post('SFexp'), ENT_QUOTES);
			$priorFC = htmlentities( $this->input->post('priorFC'), ENT_QUOTES);
			$whySF = htmlentities( $this->input->post('whySF'), ENT_QUOTES);
			$Timezone = htmlentities( $this->input->post('Timezone'), ENT_QUOTES);
			$fleetStyle = htmlentities( $this->input->post('fleetStyle'), ENT_QUOTES);
			$fleetSize = htmlentities( $this->input->post('fleetSize'), ENT_QUOTES);
			$UserID = $this->session->user_session['UserID'];
			
			$applicationID = $this->Command_model->add_fc_application( $UserID, $SFexp, $priorFC, $whySF, $Timezone, $fleetStyle, $fleetSize );
			
			if( $applicationID != FALSE )
			{
				// Go to the application's page
				redirect("manage/application/$applicationID", 'location');
			}
			else
			{
				$this->session->set_flashdata( 'flash_message', 'There was a problem adding the new FC application.' );
				log_message( 'error', 'Manage controller: failed for user:'.$UserID.':'.$this->session->user_session['Username'].' while adding new FC application.' );
				//Go to user applications page.
				redirect('manage/my_applications', 'location');
			}
		}
		else
		{
			// Field validation failed. Reload application form with errors.
			
			$data['fc_timezones'] = Command_model::FC_TIMEZONES();
			
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'FC Application' ) );
			$this->load->view('portal/portal_header' );
			$this->load->view('portal/portal_menu', $this->_get_permissions() );
			$this->load->view('portal/portal_content' );
			$this->load->view('manage/fc_application', $data );
			$this->load->view('portal/portal_footer' );
			$this->load->view('common/footer');
		}
		
	}// apply()
	
	function _valid_timezone( $Timezone ){
		if( array_key_exists( $Timezone, Command_model::FC_TIMEZONES() ) )
		{
			return TRUE;
		}
		else
		{
			$this->form_validation->set_message('_valid_timezone', 'The specified Timezone is not valid.');
			return FALSE;
		}
	}// _valid_timezone()
	
	
	public function application( $ApplicationID = NULL )
	{
		$this->_ensure_logged_in();
		
		if( $ApplicationID == NULL || !ctype_digit( $ApplicationID ) )
		{
			//	Malicious population of ApplicationID field?
			$this->session->set_flashdata( 'flash_message', 'Invalid Application ID supplied while viewing.' );
			log_message( 'error', 'Manage controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Application ID:'.$ApplicationID.' supplied while viewing.' );
			redirect('portal', 'location');
		}
		
		$is_owner = self::confirm_application_owner( $ApplicationID );
		$this->_ensure_one_of( 'CAN_PROCESS_FC_APPLICATIONS', $is_owner );
		
		$application = $this->Command_model->get_fc_application( $ApplicationID );
		if( $application != FALSE )
		{
			$data['application'] = $application;
			$data['fc_timezones'] = Command_model::FC_TIMEZONES();
			
			$data['applicant_history'] = $this->Command_model->get_fc_application_history( $application['UserID'] );
			
			$data['can_review'] = $this->_has_permission('CAN_PROCESS_FC_APPLICATIONS') && ($application['Status'] == 'Submitted');
			$data['is_owner'] = $is_owner;
			
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'FC Application '.$ApplicationID ) );
			$this->load->view('portal/portal_header' );
			$this->load->view('portal/portal_menu', $this->_get_permissions() );
			$this->load->view('portal/portal_content' );
			$this->load->view('manage/view_application', $data);
			$this->load->view('portal/portal_footer' );
			$this->load->view('common/footer');
		}
		else
		{
			$application = $this->Command_model->get_old_fc_application( $ApplicationID );
			if( $application != FALSE )
			{
				$data['application'] = $application;
				$data['fc_timezones'] = Command_model::FC_TIMEZONES();
				
				$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'FC Application '.$ApplicationID ) );
				$this->load->view('portal/portal_header' );
				$this->load->view('portal/portal_menu', $this->_get_permissions() );
				$this->load->view('portal/portal_content' );
				$this->load->view('manage/view_old_application', $data);
				$this->load->view('portal/portal_footer' );
				$this->load->view('common/footer');
			}
			else
			{
				//	No application found.	Malicious population of ApplicationID field? Or concurrent change of status?
				$this->session->set_flashdata( 'flash_message', 'Invalid Application ID supplied while viewing.' );
				log_message( 'error', 'Manage controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Application ID:'.$ApplicationID.' supplied while viewing.' );
				redirect('manage/my_applications', 'location');
			}
		}
	}// application()
	
	private function confirm_application_owner( $ApplicationID )
	{
		$UserID = $this->session->user_session['UserID']; 
		$application = $this->Command_model->get_fc_application( $ApplicationID );
		if( $application == FALSE )
		{
			return FALSE;
		}
		return ($application['UserID'] == $UserID);
	}// confirm_application_owner()
	
	
	public function edit_application( $ApplicationID = NULL )
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( array('CAN_SUBMIT_FC_APPLICATION', 'CAN_HELP_FC_APPLICANTS') );
		
		if( $ApplicationID == NULL || !ctype_digit( $ApplicationID ) )
		{
			//	Malicious population of ApplicationID field?
			$this->session->set_flashdata( 'flash_message', 'Invalid Application ID supplied while editing.' );
			log_message( 'error', 'Manage controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Application ID:'.$ApplicationID.' supplied while editing.' );
			redirect('portal', 'location');
		}
		
		$is_owner = self::confirm_application_owner( $ApplicationID );	// Also checks Application exists
		
		if( !$is_owner )
		{
			// If no permission, redirect to profile page.		Malicious population of ApplicationID field?!
			$this->session->set_flashdata( 'flash_message', 'Insufficient permission to edit Application ID: '.$ApplicationID.'.' );
			log_message( 'error', 'Manage controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Insufficient permission to edit Application ID:'.$ApplicationID.'.' );
			redirect('portal', 'location');
		}			
		
		// Form and Credential Validation.
		$this->form_validation->set_rules('SFexp', 'Spectre Fleet experience', 'required|max_length[2000]');
		$this->form_validation->set_rules('priorFC', 'FCing experience', 'required|max_length[2000]');
		$this->form_validation->set_rules('whySF', 'Why Spectre Fleet', 'required|max_length[2000]');
		$this->form_validation->set_rules('Timezone', 'fleet timezone', 'required|callback__valid_timezone');
		$this->form_validation->set_rules('fleetStyle', 'fleet style', 'required|max_length[2000]');
		$this->form_validation->set_rules('fleetSize', 'fleet size', 'required|max_length[2000]');
		
		if( $this->form_validation->run() == TRUE )
		{
			$SFexp = htmlentities( $this->input->post('SFexp'), ENT_QUOTES);
			$priorFC = htmlentities( $this->input->post('priorFC'), ENT_QUOTES);
			$whySF = htmlentities( $this->input->post('whySF'), ENT_QUOTES);
			$Timezone = htmlentities( $this->input->post('Timezone'), ENT_QUOTES);
			$fleetStyle = htmlentities( $this->input->post('fleetStyle'), ENT_QUOTES);
			$fleetSize = htmlentities( $this->input->post('fleetSize'), ENT_QUOTES);
			
			if( $this->Command_model->edit_fc_application( $ApplicationID, $SFexp, $priorFC, $whySF, $Timezone, $fleetStyle, $fleetSize ) )
			{
				// Go to the application's page
				redirect("manage/application/$ApplicationID", 'location');
			}
			else
			{
				$this->session->set_flashdata( 'flash_message', 'There was a problem editing FC application ID:'.$ApplicationID.'.' );
				log_message( 'error', 'Manage controller: failed for user:'.$UserID.':'.$this->session->user_session['Username'].' while editing FC application ID:'.$ApplicationID.'.' );
				//Go to user applications page.
				redirect('manage/my_applications', 'location');
			}
		}
		else
		{
			// Field validation failed. Reload application form with errors and form data.
			$data = $this->Command_model->get_fc_application( $ApplicationID );
			
			$data['fc_timezones'] = Command_model::FC_TIMEZONES();
			
			if( isset( $_POST['SFexp'] ) )
			{
				$data['SFexp'] = $_POST['SFexp'];
			}
			if( isset( $_POST['priorFC'] ) )
			{
				$data['priorFC'] = $_POST['priorFC'];
			}
			if( isset( $_POST['whySF'] ) )
			{
				$data['whySF'] = $_POST['whySF'];
			}
			if( isset( $_POST['Timezone'] ) )
			{
				$data['Timezone'] = $_POST['Timezone'];
			}
			if( isset( $_POST['fleetStyle'] ) )
			{
				$data['fleetStyle'] = $_POST['fleetStyle'];
			}
			if( isset( $_POST['fleetSize'] ) )
			{
				$data['fleetSize'] = $_POST['fleetSize'];
			}
				
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Edit FC Application' ) );
			$this->load->view('portal/portal_header' );
			$this->load->view('portal/portal_menu', $this->_get_permissions() );
			$this->load->view('portal/portal_content' );
			$this->load->view('manage/edit_application', $data );
			$this->load->view('portal/portal_footer' );
			$this->load->view('common/footer');
		}
		
	}// edit_application()
	
	public function my_applications()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( array('CAN_SUBMIT_FC_APPLICATION', 'CAN_HELP_FC_APPLICANTS') );
		
		$applications = $this->Command_model->get_fc_applications( $this->session->user_session['UserID'] );
		if( count($applications) > 0 )
		{
			$status = $applications[0]['Status'];
			if( $status == 'Draft' || $status == 'Submitted' )
			{
				$data['outstanding_application'] = $applications[0];
				array_shift( $applications );	// Pop the first, outstanding application off of the stack of previous ones
			}
		}
		$data['previous_applications'] = $applications;
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'My FC Applications') );
		$this->load->view('portal/portal_header' );
		$this->load->view('portal/portal_menu', $this->_get_permissions() );
		$this->load->view('portal/portal_content' );
		$this->load->view('manage/my_applications', $data);
		$this->load->view('portal/portal_footer' );
		$this->load->view('common/footer');
		
	}// my_applications()
	
	public function confirm_application( $ApplicationID = NULL )
	{
		$this->_ensure_logged_in();
		
		if( $ApplicationID == NULL || !ctype_digit( $ApplicationID ) )
		{
			//	Malicious population of ApplicationID field?
			$this->session->set_flashdata( 'flash_message', 'Invalid Application ID supplied while confirming.' );
			log_message( 'error', 'Manage controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Application ID:'.$ApplicationID.' supplied while confirming.' );
			redirect('portal', 'location');
		}
		
		$this->_ensure_one_of( array('CAN_SUBMIT_FC_APPLICATION', 'CAN_HELP_FC_APPLICANTS') );
		
		// Form and Credential Validation.
		$this->form_validation->set_rules('ApplicationID', 'application ID', 'required|is_natural');
		$this->form_validation->set_rules('Confirmed', 'confirmation', 'required|is_natural_no_zero');
		
		if( $this->form_validation->run() == TRUE )
		{
			$ApplicationID = $this->input->post('ApplicationID');
			
			if( $this->_has_permission('CAN_SUBMIT_FC_APPLICATION') && self::confirm_application_owner( $ApplicationID ) )
			{
				if( $this->Command_model->confirm_fc_application( $ApplicationID ) )
				{
					// Successfully applied
					$application = $this->Command_model->get_fc_application( $ApplicationID );
					$Timezone = Command_model::FC_TIMEZONES()[$application['Timezone']];
					self::report_application_to_directorate( $application['CharacterName'], $Timezone );
					
					$this->session->set_flashdata( 'flash_message', 'Application ID: '.$ApplicationID.' submitted.' );
					redirect('manage/my_applications', 'location');
				}
				else
				{
					$this->session->set_flashdata( 'flash_message', 'There was a problem confirming Application ID: '.$ApplicationID.'.' );
					log_message( 'error', 'Manage controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' while confirming ApplicationID: '.$ApplicationID.'.' );
					redirect('manage/my_applications', 'location');
				}
			}
			else
			{
				// If no permission, redirect to applications.		Malicious population of ApplicationID field? Or CAN_HELP_FC_APPLICANTS
				$this->session->set_flashdata( 'flash_message', 'You are unable to submit FC application ID: '.$ApplicationID.'. Perhaps you are already an FC?' );
				log_message( 'error', 'Manage controller: denied user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' while confirming ApplicationID: '.$ApplicationID.'.' );
				redirect('manage/my_applications', 'location');
			}
		}
		else
		{
			// Field validation failed. Reload application form with errors.
			
			$data['ApplicationID'] = $ApplicationID;
			
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'FC Application '.$ApplicationID ) );
			$this->load->view('portal/portal_header' );
			$this->load->view('portal/portal_menu', $this->_get_permissions() );
			$this->load->view('portal/portal_content' );
			$this->load->view('manage/confirm_application', $data);
			$this->load->view('portal/portal_footer' );
			$this->load->view('common/footer');
		}
		
	}// confirm_application()
	
	private function report_application_to_directorate( $applicant_CharacterName, $Timezone )
	{
		$content = $applicant_CharacterName . ' has applied to be a new FC in Timezone: ' . $Timezone;
		$result = $this->Discord_model->tell_directorate( $content );
		if( $result['response'] == FALSE )
		{
			log_message( 'error', "Manage controller: failure to tell_directorate( $content )." );
		}
	}// report_application_to_directorate()
	
	
	public function cancel_application()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( array('CAN_SUBMIT_FC_APPLICATION', 'CAN_HELP_FC_APPLICANTS') );
		
		// Form and Credential Validation.
		$this->form_validation->set_rules('ApplicationID', 'application ID', 'required|is_natural');
		
		if( $this->form_validation->run() == TRUE )
		{
			$ApplicationID = $this->input->post('ApplicationID');
			
			if( self::confirm_application_owner( $ApplicationID ) )
			{
				if( $this->Command_model->cancel_fc_application( $ApplicationID ) )
				{
					$this->session->set_flashdata( 'flash_message', 'Application ID: '.$ApplicationID.' cancelled.' );
					redirect('manage/my_applications', 'location');
				}
				else
				{
					$this->session->set_flashdata( 'flash_message', 'There was a problem cancelling Application ID: '.$ApplicationID.'.' );
					log_message( 'error', 'Manage controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' while cancelling ApplicationID: '.$ApplicationID.'.' );
					redirect('manage/my_applications', 'location');
				}
			}
			else
			{
				// If no permission, redirect to applications.		Malicious population of ApplicationID field?
				redirect('manage/my_applications', 'location');
			}
		}
		else
		{
			$this->session->set_flashdata( 'flash_message', 'Invalid Application ID supplied while cancelling.' );
			log_message( 'error', 'Manage controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Application ID supplied while cancelling.' );
			redirect('manage/my_applications', 'location');
		}
		
	}// cancel_application()
	
	public function review_applications()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_PROCESS_FC_APPLICATIONS' );
		
		$data['outstanding_applications'] = $this->Command_model->get_fc_applications();
		$data['recent_applications'] = $this->Command_model->get_recent_fc_applications();
		
		$data['fc_timezones'] = Command_model::FC_TIMEZONES();
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Review FC Applications' ) );
		$this->load->view('portal/portal_header' );
		$this->load->view('portal/portal_menu', $this->_get_permissions() );
		$this->load->view('portal/portal_content' );
		$this->load->view('manage/review_applications', $data);
		$this->load->view('portal/portal_footer' );
		$this->load->view('common/footer');
		
	}// review_applications()
	
	public function accept_application()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_PROCESS_FC_APPLICATIONS' );
		
		$UserID = $this->session->user_session['UserID'];
		
		// Form and Credential Validation.
		$this->form_validation->set_rules('ApplicationID', 'application ID', 'required|is_natural');
		
		if( $this->form_validation->run() == TRUE )
		{
			$ApplicationID = $this->input->post('ApplicationID');
			
			$applicant_CharacterName = $this->Command_model->accept_fc_application( $ApplicationID, $UserID );
			if( $applicant_CharacterName !== FALSE )
			{
				// Successfully accepted
				self::report_acceptance_to_command( $applicant_CharacterName );
				
				redirect('/manage/application_accepted/'.$ApplicationID, 'location');
			}
			else
			{
				$this->session->set_flashdata( 'flash_message', 'There was a problem accepting Application ID: '.$ApplicationID.'.' );
				log_message( 'error', 'Manage controller: failed for user:'.$UserID.':'.$this->session->user_session['Username'].' while accepting ApplicationID: '.$ApplicationID.'.' );
				redirect('manage/review_applications', 'location');
			}
		}
		else
		{
			$this->session->set_flashdata( 'flash_message', 'Invalid Application ID supplied while accepting.' );
			log_message( 'error', 'Manage controller: failed for user:'.$UserID.':'.$this->session->user_session['Username'].' Invalid Application ID supplied while accepting.' );
			redirect('manage/review_applications', 'location');
		}
		
	}// accept_application()
	
	private function report_acceptance_to_command( $applicant_CharacterName )
	{
		$content = '@here ' . $this->session->user_session['Username'] . ' has accepted ' . $applicant_CharacterName . ' as a new Junior FC!';
		$result = $this->Discord_model->tell_command( $content );
		if( $result['response'] == FALSE )
		{
			log_message( 'error', "Manage controller: failure to tell_command( $content )." );
		}
	}// report_acceptance_to_command()
	
	
	public function reject_application()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_PROCESS_FC_APPLICATIONS' );
		
		$UserID = $this->session->user_session['UserID'];
		
		// Form and Credential Validation.
		$this->form_validation->set_rules('ApplicationID', 'application ID', 'required|is_natural');
		
		if( $this->form_validation->run() == TRUE )
		{
			$ApplicationID = $this->input->post('ApplicationID');
			
			if( $this->Command_model->reject_fc_application( $ApplicationID, $UserID ) )
			{
				$this->session->set_flashdata( 'flash_message', 'Application ID: '.$ApplicationID.' rejected.' );
				redirect('manage/review_applications', 'location');
			}
			else
			{
				$this->session->set_flashdata( 'flash_message', 'There was a problem rejecting Application ID: '.$ApplicationID.'.' );
				log_message( 'error', 'Manage controller: failed for user:'.$UserID.':'.$this->session->user_session['Username'].' while rejecting ApplicationID: '.$ApplicationID.'.' );
				redirect('manage/review_applications', 'location');
			}
		}
		else
		{
			$this->session->set_flashdata( 'flash_message', 'Invalid Application ID supplied while rejecting.' );
			log_message( 'error', 'Manage controller: failed for user:'.$UserID.':'.$this->session->user_session['Username'].' Invalid Application ID supplied while rejecting.' );
			redirect('manage/review_applications', 'location');
		}
		
	}// reject_application()
	
	public function old_applications()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_PROCESS_FC_APPLICATIONS' );
		
		$data['applications'] = $this->Command_model->get_old_fc_applications();
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Old FC Applications' ) );
		$this->load->view('portal/portal_header' );
		$this->load->view('portal/portal_menu', $this->_get_permissions() );
		$this->load->view('portal/portal_content' );
		$this->load->view('manage/old_applications', $data);
		$this->load->view('portal/portal_footer' );
		$this->load->view('common/footer');
		
	}// old_applications()
	
	
	public function application_accepted( $ApplicationID = NULL )
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_PROCESS_FC_APPLICATIONS' );
		
		if( $ApplicationID != NULL )
		{
			$application = $this->Command_model->get_fc_application( $ApplicationID );	// Assumes join with users for username and rank
			if( $application == FALSE )
			{
				$this->session->set_flashdata( 'flash_message', "No Application found for ID: $ApplicationID." );
				redirect('/manage/review_applications', 'location');
			}
			
			$data['Username'] = $application['CharacterName'];
			$new_FC_Rank = $application['Rank'];
			$data['Rank'] = Command_model::RANK_NAMES()[$new_FC_Rank];	// Can be assumed to be Junior FC?
			$data['Accepter'] = $this->session->user_session['CharacterName'];
			$accepted_mail = $this->load->view( 'manage/accepted_mail', $data, TRUE );
			// Perhaps we should store the mail text, have applications also go through 'To Be Mailed' and 'Mailed' states/stages?
			$data['Accepted_mail'] = $accepted_mail;
			
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Accepted: FC Application '.$ApplicationID ) );
			$this->load->view('portal/portal_header' );
			$this->load->view('portal/portal_menu', $this->_get_permissions() );
			$this->load->view('portal/portal_content' );
			$this->load->view('manage/application_accepted', $data);
			$this->load->view('portal/portal_footer' );
			$this->load->view('common/footer');
		}
		else
		{
			// Malicious population of ApplicationID field?
			$this->session->set_flashdata( 'flash_message', 'Invalid Application ID supplied.' );
			log_message( 'error', 'Manage controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Application ID:'.$ApplicationID.' supplied.' );
			redirect('manage/applications', 'location');
		}
		
	}// application_accepted()
	
	
	public function change_rank()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_CHANGE_OTHERS_RANKS' );
		
		$this->form_validation->set_rules('UserID', 'user ID', 'callback__check_pairedUserID');
		$this->form_validation->set_rules('Username', 'user name', 'callback__check_pairedUsername');
		$this->form_validation->set_rules('Rank', 'rank', 'required|is_natural|callback__check_rank');
		
		$rank_names = Command_model::RANK_NAMES();
		$data['rank_names'] = $rank_names;
		
		if( $this->form_validation->run() == TRUE )
		{
			$UserID = $this->input->post('UserID');
			$Username = $this->input->post('Username');
			if( $UserID !== '' )
			{
				$user = $this->User_model->get_user_data_by_ID( $UserID );
				$Username = $user->Username;
			}
			else
			{	
				$user = $this->User_model->get_user_data_by_name( $Username );
				$UserID = $user->UserID;
			}
			
			$Rank = $this->input->post('Rank');
			
			$rankname = $rank_names[$Rank];
			
			if( $this->User_model->update_rank( $UserID, $Rank, $this->session->user_session['UserID'] ) )
			{
				$this->output->delete_cache( "activity/FC/$UserID" );
				
				$content = "$Username's rank was changed to '$rankname'.";
				$result = $this->Discord_model->tell_command( $content );
				if( $result['response'] == FALSE )
				{
					log_message( 'error', "Manage controller: failure to tell_command( $content )." );
				}
				
				$this->session->set_flashdata( 'flash_message', "$Username's rank was changed to '$rankname'." );
				redirect('manage/change_rank', 'location');
			}
			else
			{
				$this->session->set_flashdata( 'flash_message', "$Username's rank was unable to be changed." );
				redirect('manage/change_rank', 'location');
			}
		}
		else
		{
			// Field validation failed. Reload registration page with errors.
			
			if( isset( $_POST['UserID'] ) )
			{
				$data['UserID'] = $_POST['UserID'];
			}
			if( isset( $_POST['Username'] ) )
			{
				$data['Username'] = $_POST['Username'];
			}
			if( isset( $_POST['Rank'] ) )
			{
				$data['Rank'] = $_POST['Rank'];
			}
			
			$data['sorted_commanders'] = $this->Command_model->get_sorted_commanders();
			
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Change FC Rank' ) );
			$this->load->view( 'portal/portal_header' );
			$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
			$this->load->view( 'portal/portal_content' );
			$this->load->view( 'manage/change_rank', $data );
			$this->load->view( 'portal/portal_footer' );
			$this->load->view( 'common/footer', array( 'SELECT2' => TRUE ) );
		}
		
	}// change_rank()
	
	function _check_pairedUserID( $UserID )
	{
		if( $UserID === '' && $this->input->post('Username') === '' )
		{
			$this->form_validation->set_message('_check_pairedUserID', 'A User has not been selected while the user name field is also empty.');
			return FALSE;
		}
		if( $UserID !== '' && $this->input->post('Username') !== '' )
		{
			$this->form_validation->set_message('_check_pairedUserID', 'A User has been selected while the user name field is not empty.');
			return FALSE;
		}
		if( $UserID !== '' && !ctype_digit($UserID) )
		{
			$this->form_validation->set_message('_check_pairedUserID', 'The user ID field is invalid.');
			return FALSE;
		}
		return TRUE;
	}// _check_pairedUserID()
	
	function _check_pairedUsername( $Username )
	{
		if( $Username === '' && $this->input->post('UserID') === '' )
		{
			$this->form_validation->set_message('_check_pairedUsername', 'The user name field is empty while a User has also not been selected.');
			return FALSE;
		}
		if( $Username !== '' && $this->input->post('UserID') !== '' )
		{
			$this->form_validation->set_message('_check_pairedUsername', 'The user name field is not empty while a User has been selected.');
			return FALSE;
		}
		if( $Username !== '' && $this->User_model->get_user_data_by_name( $Username ) === FALSE )
		{
			$this->form_validation->set_message('_check_pairedUsername', 'The user name was not found.');
			return FALSE;
		}
		return TRUE;
	}// _check_pairedUsername()
	
	function _check_rank( $rank )
	{
		if( !array_key_exists( $rank, Command_model::RANK_NAMES() ) )	// Needs to include Member for demotions
		{
			$this->form_validation->set_message('_check_rank', 'Invalid FC rank supplied.');
			return FALSE;
		}
		return TRUE;
	}// _check_rank()
	
	
}// Manage
?>