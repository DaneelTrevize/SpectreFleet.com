<?php
if( !defined('BASEPATH') ) exit('No direct script access allowed');

class Polls extends SF_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library( 'form_validation' );
		$this->load->model( 'Polls_model' );
	}// __construct()
	
	public function index()
	{
		self::listing();
	}// index()
	
	public function create()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_CREATE_POLLS' );
		
		$UserID = $this->session->user_session['UserID'];
		
		// Form and Credential Validation.
		$this->form_validation->set_rules('Title', 'poll title', 'required|max_length[255]');
		$this->form_validation->set_rules('accessMode', 'access mode', 'required|is_natural|less_than_equal_to['.Polls_model::MAX_ACCESS_MODE.']');
		$this->form_validation->set_rules('Details', 'poll details', 'required|max_length[65535]');
		$this->form_validation->set_rules('maximumVotesPerUser', 'permit multiple votes', 'required|is_natural|less_than_equal_to['.Polls_model::MAX_OPTIONS_PER_POLL.']');
		$this->form_validation->set_rules('options[]', 'options', 'callback__validate_options');
		
		if( $this->form_validation->run() == TRUE )
		{
			$Title = htmlentities( $this->input->post('Title'), ENT_QUOTES);
			$maximumVotesPerUser = $this->input->post('maximumVotesPerUser');
			$verified_options = self::verify_options( $this->input->post('options') );
			$Details = strip_tags( $this->input->post('Details'), Polls_model::DETAILS_TAGS );
			$accessMode = $this->input->post('accessMode');
			
			$pollID = $this->Polls_model->create_poll( $Title, $UserID, $maximumVotesPerUser, $verified_options, $Details, $accessMode );
			if( $pollID != FALSE )
			{
				$this->session->set_flashdata( 'flash_message', "Poll created, ID:".$pollID.'.' );
				redirect('polls/manage', 'location');
			}
			else
			{
				$this->session->set_flashdata( 'flash_message', 'There was a problem creating the poll.' );
				log_message( 'error', 'Polls controller: failed for user:'.$UserID.':'.$this->session->user_session['Username'].' while creating a poll.' );
				redirect('polls/manage', 'location');
			}
		}
		else
		{
			// Form validation failed, reload with content.
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Create Poll' ) );
			$this->load->view( 'portal/portal_header' );
			$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
			$this->load->view( 'portal/portal_content' );
			$this->load->view( 'polls/create' );
			$this->load->view( 'portal/portal_footer' );
			$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE, 'CKEDITOR' => TRUE ) );
		}
	}// create()
	
	function _validate_options()
	{
		$options  = $this->input->post('options');
		
		if( !is_array( $options ) )
		{
			$this->form_validation->set_message('_validate_options', 'The options field is not valid.');
			return FALSE;
		}
		else
		{
			if( empty( $options ) )
			{
				$this->form_validation->set_message('_validate_options', 'The options array is empty.');
				return FALSE;
			}
			else
			{
				$non_empty_options_count = 0;
				foreach( $options as $option )
				{
					if( $option != '' )
					{
						$non_empty_options_count++;
					}
					if( $non_empty_options_count >=2 )
					{
						return TRUE;
					}
				}
				$this->form_validation->set_message('_validate_options', 'The options array did not contain at least 2 non-empty options.');
				return FALSE;
			}
		}
	}//_validate_options()
	
	private static function verify_options( $options )
	{
		$verified_options = array();
		foreach( $options as $option )
		{
			if( $option != '' )
			{
				$verified_options[] = htmlentities( $option, ENT_QUOTES);
			}
		}
		return $verified_options;
	}// verify_options()
	
	
	public function edit( $pollID = NULL )
	{
		$this->_ensure_logged_in();
		
		if( $pollID !== NULL && ctype_digit( $pollID ) )
		{
			
			$this->_ensure_one_of( 'CAN_MANAGE_OTHERS_POLLS', self::confirm_poll_owner( $pollID ) );
			
			// Reload edit draft page
			$poll_with_options = $this->Polls_model->get_poll_with_options( $pollID );
			
			$options = array();
			foreach( $poll_with_options as $option )
			{
				$options[] = $option['Description'];
			}
			$data = array(
				'pollID' => $pollID,
				'Title' => $poll_with_options[0]['Title'],
				'accessMode' => $poll_with_options[0]['accessMode'],
				'maximumVotesPerUser' => $poll_with_options[0]['maximumVotesPerUser'],
				'Details' => $poll_with_options[0]['Details'],
				'options' => $options
			);
			//print_r( $data );
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Edit Poll' ) );
			$this->load->view( 'portal/portal_header' );
			$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
			$this->load->view( 'portal/portal_content' );
			$this->load->view( 'polls/edit_draft', $data );
			$this->load->view( 'portal/portal_footer' );
			$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE, 'CKEDITOR' => TRUE ) );
			
		}
		else
		{
			// Malicious population of pollID field?
			$this->session->set_flashdata( 'flash_message', 'Invalid Poll ID supplied while editing.' );
			log_message( 'error', 'Polls controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Poll ID:'.$pollID.' supplied while editing.' );
			redirect('polls/manage', 'location');
		}
		
	}// edit()
	
	public function edit_draft()
	{
		$this->_ensure_logged_in();
		
		$this->form_validation->set_rules('pollID', 'poll ID', 'required|is_natural');
		
		if( $this->form_validation->run() == TRUE )
		{
			$pollID = $this->input->post('pollID');
			
			$this->_ensure_one_of( 'CAN_MANAGE_OTHERS_POLLS', self::confirm_poll_owner( $pollID ) );
			
			// Form and Credential Validation.
			$this->form_validation->reset_validation();
			//$this->form_validation->set_data( $_POST );
			$this->form_validation->set_rules('Title', 'poll title', 'required|max_length[255]');
			$this->form_validation->set_rules('accessMode', 'access mode', 'required|is_natural|less_than_equal_to['.Polls_model::MAX_ACCESS_MODE.']');
			$this->form_validation->set_rules('Details', 'poll details', 'required|max_length[65535]');
			$this->form_validation->set_rules('maximumVotesPerUser', 'permit multiple votes', 'required|is_natural|less_than_equal_to['.Polls_model::MAX_OPTIONS_PER_POLL.']');
			$this->form_validation->set_rules('options[]', 'options', 'callback__validate_options');
			
			if( $this->form_validation->run() == TRUE )
			{
				$UserID = $this->session->user_session['UserID'];
				
				$Title = htmlentities( $this->input->post('Title'), ENT_QUOTES);
				$maximumVotesPerUser = $this->input->post('maximumVotesPerUser');
				$verified_options = self::verify_options( $this->input->post('options') );
				$Details = strip_tags( $this->input->post('Details'), Polls_model::DETAILS_TAGS );
				$accessMode = $this->input->post('accessMode');
				
				$success = $this->Polls_model->edit_draft_poll( $pollID, $Title, $maximumVotesPerUser, $verified_options, $Details, $accessMode );
				if( $success )
				{
					$this->session->set_flashdata( 'flash_message', 'Poll ID: '.$pollID.' edited.' );
					redirect('polls/manage', 'location');
				}
				else
				{
					$this->session->set_flashdata( 'flash_message', "There was a problem editing draft Poll ID:$pollID." );
					log_message( 'error', 'Polls controller: failed for user:'.$UserID.':'.$this->session->user_session['Username'].' while editing pollID:'.$pollID.'.' );
					redirect('polls/manage', 'location');
				}
			}
			else
			{
				// Field validation failed. Reload edit draft page with errors and form data.
				
				$poll_with_options = $this->Polls_model->get_poll_with_options( $pollID );
				
				$options = array();
				foreach( $poll_with_options as $option )
				{
					$options[] = $option['Description'];
				}
				$data = array(
					'pollID' => $pollID,
					'Title' => $poll_with_options[0]['Title'],
					'accessMode' => $poll_with_options[0]['accessMode'],
					'maximumVotesPerUser' => $poll_with_options[0]['maximumVotesPerUser'],
					'Details' => $poll_with_options[0]['Details'],
					'options' => $options
				);
				
				if( isset( $_POST['Title'] ) )
				{
					$data['Title'] = $_POST['Title'];	// Override displaying default with latest submitted
				}
				if( isset( $_POST['accessMode'] ) )
				{
					$data['accessMode'] = $_POST['accessMode'];	// Override displaying default with latest submitted
				}
				if( isset( $_POST['maximumVotesPerUser'] ) )
				{
					$data['maximumVotesPerUser'] = $_POST['maximumVotesPerUser'];	// Override displaying default with latest submitted
				}
				if( isset( $_POST['Details'] ) )
				{
					$data['Details'] = $_POST['Details'];	// Override displaying default with latest submitted
				}
				if( isset( $_POST['options[]'] ) )
				{
					$data['options'] = $_POST['options[]'];
				}
				//print_r( $data );
				$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Edit Poll' ) );
				$this->load->view( 'portal/portal_header' );
				$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
				$this->load->view( 'portal/portal_content' );
				$this->load->view( 'polls/edit_draft', $data );
				$this->load->view( 'portal/portal_footer' );
				$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE, 'CKEDITOR' => TRUE ) );
				return;
			}
			
		}
		else
		{
			// Malicious population of pollID field?
			$this->session->set_flashdata( 'flash_message', 'Invalid Poll ID supplied while editing draft.' );
			log_message( 'error', 'Polls controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Poll ID:'.$pollID.' supplied while editing draft.' );
			redirect('polls/manage', 'location');
		}
		
	}// edit_draft()
	
	public function open()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( array('CAN_CREATE_POLLS', 'CAN_MANAGE_OTHERS_POLLS') );
		
		// Form and Credential Validation.
		$this->form_validation->set_rules('pollID', 'poll ID', 'required|is_natural');
		
		if( $this->form_validation->run() == TRUE )
		{
			$pollID = $this->input->post('pollID');
			
			$this->_ensure_one_of( 'CAN_MANAGE_OTHERS_POLLS', self::confirm_poll_owner( $pollID ) );
			
			if( $this->Polls_model->open_poll( $pollID ) )
			{
				$this->session->set_flashdata( 'flash_message', 'Poll ID: '.$pollID.' opened.' );
				redirect('polls/manage', 'location');
			}
			else
			{
				$this->session->set_flashdata( 'flash_message', 'There was a problem opening Poll ID: '.$pollID.'.' );
				log_message( 'error', 'Polls controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' while opening pollID:'.$pollID.'.' );
				redirect('polls/manage', 'location');
			}
			
		}
		else
		{
			//	Malicious population of pollID field?
			$this->session->set_flashdata( 'flash_message', 'Invalid Poll ID supplied while opening.' );
			log_message( 'error', 'Polls controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Poll ID:'.$pollID.' supplied while closing.' );
			redirect('polls/manage', 'location');
		}
		
	}// open()
	
	public function close()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( array('CAN_CREATE_POLLS', 'CAN_MANAGE_OTHERS_POLLS') );
		
		// Form and Credential Validation.
		$this->form_validation->set_rules('pollID', 'poll ID', 'required|is_natural');
		
		if( $this->form_validation->run() == TRUE )
		{
			$pollID = $this->input->post('pollID');
			
			$this->_ensure_one_of( 'CAN_MANAGE_OTHERS_POLLS', self::confirm_poll_owner( $pollID ) );
			
			if( $this->Polls_model->close_poll( $pollID ) )
			{
				$this->session->set_flashdata( 'flash_message', 'Poll ID: '.$pollID.' closed.' );
				redirect('polls/manage', 'location');
			}
			else
			{
				$this->session->set_flashdata( 'flash_message', 'There was a problem closing Poll ID: '.$pollID.'.' );
				log_message( 'error', 'Polls controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' while closing pollID:'.$pollID.'.' );
				redirect('polls/manage', 'location');
			}
			
		}
		else
		{
			//	Malicious population of pollID field?
			$this->session->set_flashdata( 'flash_message', 'Invalid Poll ID supplied while closing.' );
			log_message( 'error', 'Polls controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Poll ID:'.$pollID.' supplied while closing.' );
			redirect('polls/manage', 'location');
		}
		
	}// close()
	
	public function delete()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( array('CAN_CREATE_POLLS', 'CAN_MANAGE_OTHERS_POLLS') );
		
		// Form and Credential Validation.
		$this->form_validation->set_rules('pollID', 'poll ID', 'required|is_natural');
		
		if( $this->form_validation->run() == TRUE )
		{
			$pollID = $this->input->post('pollID');
			
			$this->_ensure_one_of( 'CAN_MANAGE_OTHERS_POLLS', self::confirm_poll_owner( $pollID ) );
			
			if( $this->Polls_model->delete_poll( $pollID ) )
			{
				$this->session->set_flashdata( 'flash_message', 'Poll ID: '.$pollID.' deleted.' );
				redirect('polls/manage', 'location');
			}
			else
			{
				$this->session->set_flashdata( 'flash_message', 'There was a problem deleting Poll ID: '.$pollID.'.' );
				log_message( 'error', 'Polls controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' while deleting pollID:'.$pollID.'.' );
				redirect('polls/manage', 'location');
			}
			
		}
		else
		{
			//	Malicious population of pollID field?
			$this->session->set_flashdata( 'flash_message', 'Invalid Poll ID supplied while deleting.' );
			log_message( 'error', 'Polls controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Invalid Poll ID:'.$pollID.' supplied while deleting.' );
			redirect('polls/manage', 'location');
		}
		
	}// delete()
	
	private function confirm_poll_owner( $pollID )
	{
		$userID = $this->session->user_session['UserID']; 
		$poll = $this->Polls_model->get_poll( $pollID );
		if( $poll == FALSE )
		{
			return FALSE;
		}
		return ($poll['OwnerID'] === $userID);
	}// confirm_poll_owner()
	
	
	public function manage()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( array('CAN_CREATE_POLLS', 'CAN_MANAGE_OTHERS_POLLS') );
		
		$UserID = $this->session->user_session['UserID'];
		
		$draft_polls_data['polls'] = $this->Polls_model->get_draft_polls( $UserID );
		$draft_polls_data['show_edit_button'] = TRUE;
		$data['draft_polls_html'] = $this->load->view( 'polls/manage_table', $draft_polls_data, TRUE );
		
		$open_polls_data['show_edit_button'] = FALSE;
		$closed_polls_data['show_edit_button'] = FALSE;
		if( $this->_has_permission('CAN_MANAGE_OTHERS_POLLS') )
		{
			// List all Open and Closed polls
			$open_polls_data['polls'] = $this->Polls_model->get_open_polls( FALSE );
			$data['open_polls_html'] = $this->load->view( 'polls/manage_table', $open_polls_data, TRUE );
			$closed_polls_data['polls'] = $this->Polls_model->get_closed_polls( FALSE );
			$data['closed_polls_html'] = $this->load->view( 'polls/manage_table', $closed_polls_data, TRUE );
		}
		else
		{
			// List just the user's Open and Closed polls
			$open_polls_data['polls'] = $this->Polls_model->get_open_polls( FALSE, $UserID );
			$data['open_polls_html'] = $this->load->view( 'polls/manage_table', $open_polls_data, TRUE );
			$closed_polls_data['polls'] = $this->Polls_model->get_closed_polls( FALSE, $UserID );
			$data['closed_polls_html'] = $this->load->view( 'polls/manage_table', $closed_polls_data, TRUE );
		}
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Manage Polls' ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'polls/manage', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE ) );
		
	}// manage()
	
	public function listing()
	{
		$lastMonth = new DateTime( '-30 day', $this->UTC_DTZ );
		
		$can_view_FC_polls = ( $this->_is_logged_in() && $this->_has_permission('CAN_VIEW_FC_POLLS') );
		$only_all_read = !$can_view_FC_polls;	// Assumption of only 3 modes atm
		$open_polls_data['can_view_FC_polls'] = $can_view_FC_polls;
		$open_polls_data['polls'] = $this->Polls_model->get_open_polls( $only_all_read );
		$data['open_polls_html'] = $this->load->view( 'polls/listing_table', $open_polls_data, TRUE );
		
		$closed_polls_data['can_view_FC_polls'] = $can_view_FC_polls;
		$closed_polls_data['polls'] = $this->Polls_model->get_closed_polls( $only_all_read, NULL, $lastMonth );
		$data['closed_polls_html'] = $this->load->view( 'polls/listing_table', $closed_polls_data, TRUE );
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Polls' ) );
		$this->load->view( 'polls/listing', $data );
		$this->load->view( 'common/footer' );
	}// listing()
	
	public function view( $pollID )
	{
		if( $pollID === NULL )
		{
			self::poll_error( 'No Poll ID found.' );
			return;
		}
		
		$poll_with_options = $this->Polls_model->get_poll_with_options( $pollID );
		if( $poll_with_options == FALSE )
		{
			self::poll_error( "No Poll found for ID:$pollID." );
			return;
		}
		
		$first_row = $poll_with_options[0];
		$Status =  $first_row['Status'];
		$accessMode = $first_row['accessMode'];
		$maximumVotesPerUser = $first_row['maximumVotesPerUser'];
		$data['pollID'] = $pollID;
		$data['Title'] = $first_row['Title'];
		$data['Username'] = $first_row['Username'];
		$data['Status'] = $Status;
		$data['creationDate'] = $first_row['creationDate'];
		$data['maximumVotesPerUser'] = $maximumVotesPerUser;
		$data['Details'] = $first_row['Details'];
		$data['accessMode'] = $accessMode;
		
		// Check if the user is logged in
		$is_logged_in = $this->_is_logged_in();
		$data['is_logged_in'] = $is_logged_in;
		$UserID = $is_logged_in ? $this->session->user_session['UserID'] : NULL;
		
		if( !$this->can_view_poll( $UserID, $first_row['OwnerID'], $accessMode, $Status ) )
		{
			self::poll_error( 'This poll is not accessible to you.' );
			return;
		}
		
		$data['options'] = $poll_with_options;
		
		// Percentages should be calculated within 1 transaction for consistency of votecounting?
		$poll_votes = $this->Polls_model->get_poll_votes( $pollID );
		if( $poll_votes == FALSE )
		{
			self::poll_error( "Unable to retrieve votes for Poll ID:$pollID." );
			return;
		}
		$data['total_votes'] = $poll_votes['total_votes'];
		$data['votes_per_option'] = $poll_votes['votes_per_option'];
		
		// Try display personal votes if the user is logged in
		if( $is_logged_in )
		{
			$users_votes = $this->Polls_model->get_users_votes( $pollID, $UserID );
			if( $users_votes == FALSE )
			{
				$this->session->set_flashdata( 'flash_message', "Unable to retrieve your votes for Poll ID::$pollID." );
				redirect('portal', 'location');
			}
			$data['users_votes'] = $users_votes;
			
			$could_have_voted = FALSE;
			// User must be FC if poll is FC-only
			switch( $accessMode )
			{
				case Polls_model::ALL_READ_ALL_VOTE_MODE:
					$could_have_voted = TRUE;
					break;
				case Polls_model::ALL_READ_FC_VOTE_MODE:
				case Polls_model::FC_READ_FC_VOTE_MODE:
					$could_have_voted = $this->_has_permission( 'CAN_VOTE_FC_POLLS' );
					break;
				default:
					$this->session->set_flashdata( 'flash_message', 'There was a problem with accessing the poll.' );
					log_message( 'error', 'Polls controller: failed for user:'.$UserID.':'.$this->session->user_session['Username'].' attempted to view pollID:'.$pollID.', unexpected poll access mode:'.$accessMode.'.' );
					redirect('portal', 'location');
					break;
			}
			//$data['could_have_voted'] = $could_have_voted;
			
			$can_vote_again = FALSE;
			if( $could_have_voted && $Status === 'Open' )
			{
				$user_vote_total = 0;
				foreach( $users_votes as $option )
				{
					if( $option['UserID'] !== NULL )
					{
						$user_vote_total++;
					}
				}
				$can_vote_again = ($user_vote_total < $maximumVotesPerUser);
			}
			$data['can_vote_again'] = $can_vote_again;
		}
		
		$this->load->view( 'common/header', array(
			'PAGE_TITLE' => 'Poll '.$pollID,
			'PAGE_AUTHOR' => $data['Username'],
			'PAGE_DESC' => $data['Title'])
		);
		$this->load->view( 'polls/view', $data );
		$this->load->view( 'common/footer' );
	}// view()
	
	private function poll_error( $message )
	{
		$data['error'] = $message;
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Polls' ) );
		$this->load->view( 'polls/error', $data );
		$this->load->view( 'common/footer' );
	}// poll_error()
	
	
	public function vote()
	{
		/*
		*	User must be logged in
		*	Poll must exist
		*	Poll must be open
		*	User must be FC if poll is FC-only
		*	User can't have voted too many times for this poll
		*	User can't have voted for this option already
		*	This option must exist for this poll
		*/
		
		$this->_ensure_logged_in();
		
		$UserID = $this->session->user_session['UserID'];
		
		$pollID = $this->input->post('pollID');
		if( !isset($pollID) || !ctype_digit($pollID) || $pollID < 0 )
		{
			log_message( 'error', 'Polls controller: failed for user:'.$UserID.':'.$this->session->user_session['Username'].' submitted invalid pollID:'.htmlentities($pollID, ENT_QUOTES) );
			redirect('polls/listing', 'location');
		}
		
		$chosen_optionID = $this->input->post('optionID');
		if( !isset($chosen_optionID) || !ctype_digit($chosen_optionID) || $chosen_optionID < 0 )
		{
			log_message( 'error', 'Polls controller: failed for user:'.$UserID.':'.$this->session->user_session['Username'].' submitted invalid chosen_optionID:'.htmlentities($chosen_optionID, ENT_QUOTES) );
			redirect('polls/listing', 'location');
		}
		$chosen_optionID = intval($chosen_optionID);
		
		$poll_with_options = $this->Polls_model->get_poll_with_options( $pollID );
		if( $poll_with_options == FALSE )
		{
			log_message( 'error', 'Polls controller: failed for user:'.$UserID.':'.$this->session->user_session['Username'].' submitted missing pollID:'.htmlentities($pollID, ENT_QUOTES) );
			$this->session->set_flashdata( 'flash_message', "No Poll found for ID:$pollID." );
			redirect('portal', 'location');
		}
		
		$first_row = $poll_with_options[0];
		$Status = $first_row['Status'];
		$maximumVotesPerUser = $first_row['maximumVotesPerUser'];
		
		if( $Status !== 'Open' )
		{
			$this->session->set_flashdata( 'flash_message', 'You cannot vote in a closed poll.' );
			redirect('portal', 'location');
		}
		
		// User must be FC if poll is FC-only
		$accessMode = $first_row['accessMode'];
		switch( $accessMode )
		{
			case Polls_model::ALL_READ_ALL_VOTE_MODE:
				break;
			case Polls_model::ALL_READ_FC_VOTE_MODE:
			case Polls_model::FC_READ_FC_VOTE_MODE:
				//$this->_ensure_one_of( 'CAN_VOTE_FC_POLLS' );
				if( !$this->_has_permission('CAN_VOTE_FC_POLLS') )
				{
					$this->session->set_flashdata( 'flash_message', 'You do not have permission to vote in Poll ID:'.$pollID );
					log_message( 'error', 'Polls controller: failed for user:'.$UserID.':'.$this->session->user_session['Username'].' while voting in pollID:'.$pollID.'.' );
					redirect('portal', 'location');
				}
				break;
			default:
				$this->session->set_flashdata( 'flash_message', 'There was a problem with accessing the poll.' );
				log_message( 'error', 'Polls controller: failed for user:'.$UserID.':'.$this->session->user_session['Username'].' attempted to vote in pollID:'.$pollID.', unexpected poll access mode:'.$accessMode.'.' );
				redirect('portal', 'location');
				break;
		}
		
		$users_votes = $this->Polls_model->get_users_votes( $pollID, $UserID );
		//log_message( 'error', print_r( $users_votes, TRUE ) );
		$can_vote_again = FALSE;
		$valid_chosen_optionID = FALSE;
		$user_vote_total = 0;
		for( $optionID = 0; $optionID < count($users_votes); $optionID++ )
		{
			$option = $users_votes[$optionID];
			
			if( $option['UserID'] !== NULL )
			{
				$user_vote_total++;
			}
			if( $optionID === $chosen_optionID )
			{
				if( $option['UserID'] !== NULL )
				{
					$this->session->set_flashdata( 'flash_message', 'You cannot vote for the same option twice.' );
					log_message( 'error', 'Polls controller: failed for user:'.$UserID.':'.$this->session->user_session['Username'].' attempted to vote twice in pollID:'.$pollID.' for chosen_optionID:'.$chosen_optionID.'.' );
					redirect('portal', 'location');
				}
				else
				{
					$valid_chosen_optionID = TRUE;
					// Don't break early, still need to count qubsequent options' votes
				}
			}
		}
		$can_vote_again = ($user_vote_total < $maximumVotesPerUser);
		
		//log_message( 'error', print_r( $valid_chosen_optionID, TRUE ) .':'. print_r( $can_vote_again, TRUE ) );
		if( !$valid_chosen_optionID || !$can_vote_again )
		{
			$this->session->set_flashdata( 'flash_message', 'There was a problem voting for that option.' );
			log_message( 'error', 'Polls controller: failed for user:'.$UserID.':'.$this->session->user_session['Username'].' while attempting to vote in pollID:'.$pollID.' for chosen_optionID:'.$chosen_optionID.'.' );
			redirect('portal', 'location');
		}
		
		$voted = $this->Polls_model->add_vote( $pollID, $chosen_optionID, $UserID );
		if( $voted )
		{
			redirect('polls/'.$pollID, 'location');
		}
		else
		{
			$this->session->set_flashdata( 'flash_message', 'There was a problem registering your vote.' );
			log_message( 'error', 'Polls controller: failed for user:'.$UserID.':'.$this->session->user_session['Username'].' while voting in pollID:'.$pollID.' for $chosen_optionID:'.$chosen_optionID.'.' );
			redirect('portal', 'location');
		}
		
	}// vote()
	
	private function can_view_poll( $UserID, $OwnerID, $accessMode, $Status )
	{
		// $OwnerID can't be NULL
		if( $UserID === $OwnerID )
		{
			return TRUE;
		}
		
		switch( $accessMode )
		{
			case Polls_model::ALL_READ_ALL_VOTE_MODE:
			case Polls_model::ALL_READ_FC_VOTE_MODE:
				// Check if in generally-visible states
				return ($Status === 'Open' || $Status === 'Closed');
				//break;	implicit
			case Polls_model::FC_READ_FC_VOTE_MODE:
				// Check if the user is logged in
				if( $UserID === NULL )
				{
					return FALSE;
				}
				// Check if the user has permission for this type of poll
				if( !$this->_has_permission('CAN_VIEW_FC_POLLS') )
				{
					return FALSE;
				}
				// Check if in generally-visible states
				return ($Status === 'Open' || $Status === 'Closed');
				//break;	implicit
			default:
				return FALSE;
		}
	}// can_view_poll()
	
}// Polls
?>