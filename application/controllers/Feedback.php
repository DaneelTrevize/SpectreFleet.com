<?php
class Feedback extends SF_Controller {
	
	const SCORE_MINIMUM = 1;
	const SCORE_MAXIMUM = 10;

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Command_model');
		$this->load->model('Feedback_model');
		$this->load->model('User_model');
	}// __construct()
	
	public function index( $preselectedFC = NULL )	// Cacheable submit form
	{
		$data['rank_names'] = Command_model::RANK_NAMES();
		$data['sorted_commanders'] = $this->Command_model->get_sorted_commanders();
		
		if( $preselectedFC != NULL )
		{
			$data['UserID'] = $preselectedFC;
		}
		
		$this->output->cache( 60 );		// Command model should store FC list cache timer, in minutes?
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Feedback' ) );
		$this->load->view( 'feedback/submit', $data );
		$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE, 'SELECT2' => TRUE ) );
	}// index()
	
	public function submit( $preselectedFC = NULL )		// Handle the submit form
	{
		// Note: Doesn't even require a session, let alone a registered Member.
		
		// Form and Credential Validation.
		$this->load->library('form_validation');
		
		$UserID = $this->input->post('UserID');
		$Feedback = htmlentities( $this->input->post('Feedback'), ENT_QUOTES);
		$Score = $this->input->post('Score');
		
		$this->form_validation->set_rules('UserID', 'FC', 'required|is_natural|callback__is_commander');
		$this->form_validation->set_rules('Feedback', 'Feedback', 'required|max_length[2000]');
		$this->form_validation->set_rules('Score', 'Score', 'required|is_natural|callback__in_score_range');
			
		if( $this->form_validation->run() == TRUE )
		{
			$error = NULL;
			$Date = date( 'Y-m-d H:i:s' );
			
			$FC_data = $this->User_model->get_user_data_by_ID( $UserID );
			if( $FC_data === FALSE )
			{
				// Database error
				log_message( 'error', 'Feedback controller: failed to look up FC ID: '. $UserID .', feedback: ' .print_r( $feedback_data, TRUE ) );
				$error = 'There was a problem storing the feedback.';
			}
			else
			{
				$feedback_data = array(
					'CharacterID' => $FC_data->CharacterID,
					'UserID' => $UserID,
					'Rank' => $FC_data->Rank,
					'CharacterName' => $FC_data->CharacterName,
					'Date' => $Date,
					'Feedback' => $Feedback,
					'Score' => $Score
				);
				
				if( $this->Feedback_model->record_feedback( $UserID, $Feedback, $Score, $Date ) === FALSE )
				{
					// Database error
					log_message( 'error', 'Feedback controller: failed to store feedback: ' .print_r( $feedback_data, TRUE ) );
					$error = 'There was a problem storing the feedback.';
				}
				else
				{
					$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Feedback' ) );
					$this->load->view( 'feedback/submitted', array( 'feedback_data' => $feedback_data ) );
					$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE ) );
					return;
				}
			}
			$data['error'] = $error;
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Feedback Error' ) );
			$this->load->view( 'feedback/error', $data );
			$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE ) );
			return;
		}
		else
		{
			// Field validation failed. Reload feedback form with errors.
			$data['rank_names'] = Command_model::RANK_NAMES();
			$data['sorted_commanders'] = $this->Command_model->get_sorted_commanders();
			
			$data['UserID'] = $UserID;
			$data['Feedback'] = $Feedback;
			$data['Score'] = $Score;
			
			if( $preselectedFC != NULL )
			{
				$data['UserID'] = $preselectedFC;
			}
			
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Feedback' ) );
			$this->load->view( 'feedback/submit', $data );
			$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE, 'SELECT2' => TRUE ) );
		}
	}// submit()
	
	function _is_commander( $UserID ){
		if( $this->Command_model->is_commander( $UserID ) )
		{
			return TRUE;
		}
		else
		{
			$this->form_validation->set_message('_is_commander', 'The specified user is not a Fleet Commander.');
			return FALSE;
		}
	}// _is_commander()
	
	public function _in_score_range( $Score ){
		if( $Score >= self::SCORE_MINIMUM && $Score <= self::SCORE_MAXIMUM )
		{
			return TRUE;
		}
		else
		{
			$this->form_validation->set_message('_in_score_range', 'The score is not within the permitted range.');
			return FALSE;
		}
	}// _in_score_range()
	
	
	public function search()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_VIEW_FEEDBACK' );
		
		// Find GET variables that feedback search accepts, validate them
		$FEEDBACK_SEARCH_FIELDS = Feedback_model::FEEDBACK_SEARCH_FIELDS();
		$validated_search_fields = $this->dynamic_search->validate_search_fields( $_GET, $FEEDBACK_SEARCH_FIELDS );
		
		$orderTypes = Feedback_model::FEEDBACK_ORDERTYPES();
		$orderType = $this->dynamic_search->validate_orderType( $this->input->get('orderType'), $orderTypes );
		
		$orderSort = $this->dynamic_search->validate_orderSort( $this->input->get('orderSort') );
		
		$page = $this->dynamic_search->validate_page( $this->input->get('page') );
		
		$pageSizes = Feedback_model::FEEDBACK_PAGESIZES();
		$pageSize = $this->dynamic_search->validate_page_size( $this->input->get('pageSize'), $pageSizes );
		
		$feedback = $this->Feedback_model->get_all_feedback( $validated_search_fields, $orderType, $orderSort, $page, $pageSize );
		
		// Repopulate form data with valid values
		foreach( $validated_search_fields as $field_name => $value_type )
		{
			$data[$field_name] = $value_type['value'];
		}
		
		$search_string = $this->dynamic_search->regenerate_search_string( $_GET, $validated_search_fields, $orderType, $orderSort, $pageSize );
		$data['search_string'] = $search_string;
		$data['orderType'] = $orderType;
		$data['orderSort'] = $orderSort;
		$data['page'] = $page;
		$data['pageSize'] = $pageSize;
		
		$data['UserID'] = $this->session->user_session['UserID'];
		$data['CharacterName'] = $this->session->user_session['CharacterName'];
		
		$data['feedback'] = $feedback;
		$data['orderTypes'] = $orderTypes;
		$data['pageSizes'] = $pageSizes;
		
		$data['results_on_page'] = count($feedback);
		$data['pages_count_html'] = $this->load->view( 'common/pages_count', $data, TRUE );
		$data['pages_arrows_html'] = $this->load->view( 'common/pages_arrows', $data, TRUE );
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Search Feedback' ) );
		$this->load->view('portal/portal_header' );
		$this->load->view('portal/portal_menu', $this->_get_permissions() );
		$this->load->view('portal/portal_content' );
		$this->load->view('feedback/search_feedback', $data);
		$this->load->view('portal/portal_footer' );
		$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE, 'SELECT2' => TRUE ) );
		
	}// search()

	
}// Feedback
?>