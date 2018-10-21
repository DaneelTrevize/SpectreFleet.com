<?php
class Doctrine extends SF_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('Doctrine_model');
		$this->load->library('form_validation');
		//$this->load->library('dynamic_search');	// Already done by Doctrine_model
		$this->load->library( 'LibFit' );
		$this->load->model('Discord_model');
	}
	
	public function index()
	{
		self::fleets();
	}// index()
	
	public function manage_doctrines()
	{
		
		$this->_ensure_logged_in();
		
		$userID = $this->session->user_session['UserID'];
		
		$page = $this->dynamic_search->validate_page( $this->input->get('page') );
		
		$query = $this->Doctrine_model->get_user_fleets( $userID, $page );
		
		$data['fleets'] = $query;
		
		$search_string = '/doctrine/manage_doctrines?';
		$data['search_string'] = $search_string;
		$data['page'] = $page;
		$data['pageSize'] = Doctrine_model::MANAGE_DOCTRINES_PAGESIZE;
		
		$data['results_on_page'] = count($query);
		$data['pages_count_html'] = $this->load->view( 'common/pages_count', $data, TRUE );
		$data['pages_arrows_html'] = $this->load->view( 'common/pages_arrows', $data, TRUE );
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Your Doctrines' ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'doctrine/manage_doctrines', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer' );
		
	}// manage_doctrines()
	
	public function manage_fits()
	{
		$this->_ensure_logged_in();
		
		$userID = $this->session->user_session['UserID'];
		
		$page = $this->dynamic_search->validate_page( $this->input->get('page') );
		
		$query = $this->Doctrine_model->get_user_fits( $userID, FALSE, $page );
		$data['fits'] = $query;
		
		$search_string = '/doctrine/manage_fits?';
		$data['search_string'] = $search_string;
		$data['page'] = $page;
		$data['pageSize'] = Doctrine_model::MANAGE_FITS_PAGESIZE;
		
		$data['results_on_page'] = count($query);
		$data['pages_count_html'] = $this->load->view( 'common/pages_count', $data, TRUE );
		$data['pages_arrows_html'] = $this->load->view( 'common/pages_arrows', $data, TRUE );
		
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Your Fits' ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'doctrine/manage_fits', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer' );
		
	}// manage_fits()
	
	public function fleets()
	{
		// Find GET variables that fleet search accepts, validate them
		$FLEET_SEARCH_FIELDS = Doctrine_model::FLEET_SEARCH_FIELDS();
		$validated_search_fields = $this->dynamic_search->validate_search_fields( $_GET, $FLEET_SEARCH_FIELDS );
		
		$orderTypes = Doctrine_model::FLEET_ORDERTYPES();
		$orderType = $this->dynamic_search->validate_orderType( $this->input->get('orderType'), $orderTypes );
		
		$orderSort = $this->dynamic_search->validate_orderSort( $this->input->get('orderSort') );
		
		$page = $this->dynamic_search->validate_page( $this->input->get('page') );
		
		$pageSizes = Doctrine_model::FLEET_PAGESIZES();
		$pageSize = $this->dynamic_search->validate_page_size( $this->input->get('pageSize'), $pageSizes );
		
		$fleets = $this->Doctrine_model->get_all_fleets( $validated_search_fields, $orderType, $orderSort, $page, $pageSize );
		
		$query = []; // This is pulling the shipID list for each doctrine. May be worth moving entirely into the model as a join/view?
		foreach($fleets as $fleet)
		{
			$fitIDs = $this->Doctrine_model->get_fleet_fitIDs($fleet['fleetID']);
			$shipIDs = [];
			foreach($fitIDs as $fit)
			{
				$fitInfo = $this->Doctrine_model->get_fit_info($fit['fitID']);
				if( $fitInfo !== FALSE )
				{
					$shipIDs[] = $fitInfo['shipID'];
				}
			}
			$fleet['shipIDs'] = $shipIDs;
			$query[] = $fleet;
		}
		
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
		
		$data['fleets'] = $query;
		$data['roles'] = Doctrine_model::FLEET_ROLES();
		$data['orderTypes'] = $orderTypes;
		$data['pageSizes'] = $pageSizes;
		$data['results_on_page'] = count($query);
		$data['results_controls_html'] = $this->load->view( 'doctrine/results_controls', $data, TRUE );
		$data['pages_count_html'] = $this->load->view( 'common/pages_count', $data, TRUE );
		$data['pages_arrows_html'] = $this->load->view( 'common/pages_arrows', $data, TRUE );
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Search Doctrines' ) );
		$this->load->view( 'doctrine/search_doctrines', $data );
		$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE, 'TOGGLES' => TRUE ) );
	}// fleets()

	public function fits()
	{
		// Find GET variables that fit search accepts, validate them
		$FIT_SEARCH_FIELDS = Doctrine_model::FIT_SEARCH_FIELDS();
		$validated_search_fields = $this->dynamic_search->validate_search_fields( $_GET, $FIT_SEARCH_FIELDS );
		
		$orderTypes = Doctrine_model::FIT_ORDERTYPES();
		$orderType = $this->dynamic_search->validate_orderType( $this->input->get('orderType'), $orderTypes );
		
		$orderSort = $this->dynamic_search->validate_orderSort( $this->input->get('orderSort') );
		
		$page = $this->dynamic_search->validate_page( $this->input->get('page') );
		
		$pageSizes = Doctrine_model::FIT_PAGESIZES();
		$pageSize = $this->dynamic_search->validate_page_size( $this->input->get('pageSize'), $pageSizes );
		
		$query = $this->Doctrine_model->get_all_fits( $validated_search_fields, $orderType, $orderSort, $page, $pageSize );
		
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
		
		$data['fits'] = $query;
		$data['roles'] = Doctrine_model::FIT_ROLES();
		$data['orderTypes'] = $orderTypes;
		$data['pageSizes'] = $pageSizes;
		$data['results_on_page'] = count($query);
		$data['results_controls_html'] = $this->load->view( 'doctrine/results_controls', $data, TRUE );
		$data['pages_count_html'] = $this->load->view( 'common/pages_count', $data, TRUE );
		$data['pages_arrows_html'] = $this->load->view( 'common/pages_arrows', $data, TRUE );
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Search Fits' ) );
		$this->load->view('doctrine/search_fits', $data);
		$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE, 'TOGGLES' => TRUE ) );
	}// fits()
	
	public function new_fit()	// Handles the form for making new fits
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_MANAGE_OWN_FITS' );
		
		$this->form_validation->set_rules('fitRole', 'role', 'required|callback__is_fit_role');
		$this->form_validation->set_rules('fitDescription', 'description', 'required|max_length[10000]');
		$this->form_validation->set_rules('fitName', 'name', 'max_length[255]');	// Not required, get from EFT
		$this->form_validation->set_rules('EFT', 'EVE Fit format', 'required|max_length[2000]');
		
		if( $this->form_validation->run() == TRUE )
		{
			$fitRole = htmlentities( $this->input->post('fitRole'), ENT_QUOTES );	// Really should be a groupID
			$fitDescription = strip_tags( $this->input->post('fitDescription'), Doctrine_model::DESCRIPTION_TAGS );
			$userID = $this->session->user_session['UserID'];
			$FitText = $this->input->post('EFT');
			if( isset( $_POST['fitName'] ) )
			{
				$fitName = htmlentities( $this->input->post('fitName'), ENT_QUOTES );
			}
			else
			{
				$fitName = NULL;
			}
			
			$parsedFit = $this->libfit->parse_Fit( $FitText );
			
			$this->form_validation->reset_validation();
			$this->form_validation->set_data( array( 'parsedFit' => implode(".<br />\n", $parsedFit['issues']) ) );
			$this->form_validation->set_rules('parsedFit', 'EVE Fit format', 'callback__parsedOK');
			
			if( $this->form_validation->run() == TRUE )
			{
				$fitID = $this->Doctrine_model->add_fit( $fitRole, $fitDescription, $userID, $parsedFit, $fitName );
				
				if( $fitID != FALSE )
				{
					// Go to the fit's page
					redirect("doctrine/fit/$fitID", 'location');
				}
				else
				{
					$this->session->set_flashdata( 'flash_message', 'There was a problem adding the new fit.' );
					log_message( 'error', 'Doctrine controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' while adding fit.' );
					//Go to user fits page.
					redirect('doctrine/manage_fits', 'location');
				}
			}
			// Else fall through to form validation failure.
		}
		
		// Field validation failed. Reload new fit page with errors and form data.
		
		$info = array(
			'fitName' => '',
			'fitRole' => '',
			'fitDescription' => 'Description or Explanation of Fit',
			'EFT' => ''
		);
		if( isset( $_POST['fitRole'] ) )
		{
			$info['fitRole'] = $_POST['fitRole'];
		}
		if( isset( $_POST['fitDescription'] ) )
		{
			$info['fitDescription'] = $_POST['fitDescription'];
		}
		if( isset( $_POST['fitName'] ) )
		{
			$info['fitName'] = $_POST['fitName'];
		}
		if( isset( $_POST['EFT'] ) )
		{
			$info['EFT'] = $_POST['EFT'];
		}
		$data['info'] = $info;
		
		$data['roles'] = Doctrine_model::FIT_ROLES();
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Create New Fit' ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'doctrine/new_fit', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE, 'SELECT2' => TRUE, 'CKEDITOR' => TRUE ) );
	
	}// new_fit()
	
	function _is_fit_role( $fitRole ){
		if( $this->Doctrine_model->is_fit_role( $fitRole ) )
		{
			return TRUE;
		}
		else
		{
			$this->form_validation->set_message('_is_fit_role', 'The specified fit role is not valid.');
			return FALSE;
		}
	}// _is_fit_role()
	
	function _parsedOK( $issues )
	{
		if( $issues === '' )
		{
			return TRUE;
		}
		else
		{
			$this->form_validation->set_message( '_parsedOK', "EVE Fit format issues:<br />\n".$issues );
			return FALSE;
		}
	}// _parsedOK()
	
	
	public function edit_fit()		// Should take fitID as a GET parameter? Fail early if it's not a valid fit, not have to pass it on form reload?
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_MANAGE_OWN_FITS' );
		
		$this->form_validation->set_rules('fitID', 'fitID', 'required|is_natural');
		$this->form_validation->set_rules('fitRole', 'role', 'required|callback__is_fit_role');
		$this->form_validation->set_rules('fitDescription', 'description', 'required|max_length[10000]');
		$this->form_validation->set_rules('fitName', 'name', 'max_length[255]');
		$this->form_validation->set_rules('EFT', 'EVE Fit format', 'required|max_length[2000]');
		
		if( $this->form_validation->run() == TRUE )
		{
			$fitID = $this->input->post('fitID');
			$fitRole = htmlentities( $this->input->post('fitRole'), ENT_QUOTES );	// Really should be a groupID
			//$fitName = htmlentities( $this->input->post('fitName'), ENT_QUOTES);
			$fitDescription = strip_tags( $this->input->post('fitDescription'), Doctrine_model::DESCRIPTION_TAGS );
			$FitText = $this->input->post('EFT');
			if( isset( $_POST['fitName'] ) )
			{
				$fitName = htmlentities( $this->input->post('fitName'), ENT_QUOTES );
			}
			else
			{
				$fitName = NULL;
			}
			
			$parsedFit = $this->libfit->parse_Fit( $FitText );
			
			$this->form_validation->reset_validation();
			$this->form_validation->set_data( array( 'parsedFit' => implode(".<br />\n", $parsedFit['issues']) ) );
			$this->form_validation->set_rules('parsedFit', 'EVE Fit format', 'callback__parsedOK');
			
			if( $this->form_validation->run() == TRUE )
			{
				
				if( self::can_modify_fit( $this->session->user_session['UserID'], $fitID ) )
				{
					
					if( $this->Doctrine_model->edit_fit( $fitID, $fitRole, $fitDescription, $parsedFit, $fitName ) )
					{
						$this->reset_fit_output_cache( $fitID );
						// Go to the fit's page
						redirect("doctrine/fit/$fitID", 'location');
					}
					else
					{
						$this->session->set_flashdata( 'flash_message', 'There was a problem editing Fit ID: '.$fitID.'.' );
						log_message( 'error', 'Doctrine controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' while editing fit ID:'.$fitID.'.' );
						//Go to user fits page.
						redirect('doctrine/manage_fits', 'location');
					}
				
				}
				else
				{
					// If no permission, redirect to profile page.		Malicious population of fitID field?!
					$this->session->set_flashdata( 'flash_message', 'Insufficient permission to edit fit ID: '.$fitID.'.' );
					log_message( 'error', 'Doctrine controller: failed for user:'.$this->session->user_session['UserID'].':'.$this->session->user_session['Username'].' Insufficient permission to edit fit ID:'.$fitID.'.' );
					redirect('portal', 'location');
				}
			}
			// Else fall through to form validation failure.
		}
		
		// Field validation failed. Reload edit fit page with errors and form data.
		
		if( !isset( $_POST['fitID'] ) )
		{
			// Warn user to choose a fit to edit? Malicious removal of fitID field?!
			redirect('doctrine/manage_fits', 'location');
		}
		
		$fitID = $this->input->post('fitID');	// We shouldn't trust that it's a number, or a real fitID
		
		if( !self::_is_integer_string( $fitID ) )
		{
			self::_not_found();
		}
		
		$info = $this->Doctrine_model->get_fit_info( $fitID );
		
		if( $info === FALSE )
		{
			self::_not_found();
		}
		
		if( isset( $_POST['fitRole'] ) )
		{
			$info['fitRole'] = $_POST['fitRole'];
		}
		if( isset( $_POST['fitDescription'] ) )
		{
			$info['fitDescription'] = $_POST['fitDescription'];
		}
		if( isset( $_POST['fitName'] ) && $_POST['fitName'] != "" )
		{
			$info['fitName'] = $_POST['fitName'];
		}
		
		$data['info'] = $info;
		
		if( isset( $_POST['EFT'] ) )
		{
			$data['EFT'] = $_POST['EFT'];
		}
		else
		{
			$fit_items = $this->Doctrine_model->get_fit_items( $fitID, $info['shipID'], $info['isStrategicCruiser'] );
			$data['EFT'] = $this->libfit->generate_EFT( $info, $fit_items );
		}
		
		$data['roles'] = Doctrine_model::FIT_ROLES();
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Edit: '. $data['info']['fitName'] ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'doctrine/edit_fit', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE, 'SELECT2' => TRUE, 'CKEDITOR' => TRUE ) );
		
	}// edit_fit()
	
	
	public function retire_fit()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_MANAGE_OWN_FITS' );
		
		$userID = $this->session->user_session['UserID'];
		
		$this->form_validation->set_rules('fitID', 'fitID', 'required|is_natural');
		
		if( $this->form_validation->run() == TRUE )
		{
			$fitID = $this->input->post('fitID');
			
			if( self::can_modify_fit( $userID, $fitID ) )
			{
				if( $this->Doctrine_model->retire_fit( $fitID, $userID ) )
				{
					$this->reset_fit_output_cache( $fitID );
					$this->session->set_flashdata( 'flash_message', "Fit ID: $fitID has been retired." );
					redirect('doctrine/manage_fits', 'location');
				}
				else
				{
					$this->session->set_flashdata( 'flash_message', "There was a problem retiring Fit ID: $fitID." );
					log_message( 'error', 'Doctrine controller: failed for user:'.$userID.':'.$this->session->user_session['Username']." while retiring fitID:$fitID." );
					redirect('doctrine/manage_fits', 'location');
				}
			}
			else
			{
				// If no permission, redirect to profile page.		Malicious population of fitID field?!
				$this->session->set_flashdata( 'flash_message', 'Insufficient permission to retire fit ID: '.$fitID.'.' );
				log_message( 'error', 'Doctrine controller: failed for user:'.$userID.':'.$this->session->user_session['Username'].' Insufficient permission to retire fit ID:'.$fitID.'.' );
				redirect('portal', 'location');
			}
		}
		else
		{
			// Go to user fits page.
			redirect('doctrine/manage_fits', 'location');
		}
		
	}// retire_fit()
	
	public function make_fit_official()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_MAKE_FITS_OFFICIAL' );
		
		$userID = $this->session->user_session['UserID'];
		
		$this->form_validation->set_rules('fitID', 'fitID', 'required|is_natural');
		
		if( $this->form_validation->run() == TRUE )
		{
			$fitID = $this->input->post('fitID');
			
			$fit_info = $this->Doctrine_model->get_fit_info( $fitID );
			if( $fit_info != FALSE )
			{
				if( $this->Doctrine_model->make_fit_official( $fitID, $userID ) )
				{
					$this->reset_fit_output_cache( $fitID );
					//$this->session->set_flashdata( 'flash_message', "Fit ID: $fitID has been made Official." );
					$content = 'Fit https://spectrefleet.com/doctrine/fit/'.$fitID.', `'.html_entity_decode( $fit_info['fitName'], ENT_QUOTES | ENT_HTML5 ).'` was made Official.';
					$result = $this->Discord_model->tell_command( $content );
					if( $result['response'] == FALSE )
					{
						log_message( 'error', "Doctrine controller: failure to tell_command( $content )." );
					}
					// Go to the fit's page
					redirect("doctrine/fit/$fitID", 'location');
				}
				else
				{
					$this->session->set_flashdata( 'flash_message', "There was a problem making Official fit ID: $fitID." );
					log_message( 'error', 'Doctrine controller: failed for user:'.$userID.':'.$this->session->user_session['Username']." while making Official fitID:$fitID." );
					redirect('doctrine/manage_fits', 'location');
				}
			}
			else
			{
				// If no permission, redirect to profile page.		Malicious population of fitID field?!
				$this->session->set_flashdata( 'flash_message', 'Insufficient permission to make Official fit ID: '.$fitID.'.' );
				log_message( 'error', 'Doctrine controller: failed for user:'.$userID.':'.$this->session->user_session['Username'].' Insufficient permission to make Official fit ID:'.$fitID.'.' );
				redirect('portal', 'location');
			}
		}
		else
		{
			// Go to user fits page.
			redirect('doctrine/manage_fits', 'location');
		}
		
	}// make_fit_official()
	
	public function make_fit_public()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_MAKE_FITS_OFFICIAL' );
		
		$userID = $this->session->user_session['UserID'];
		
		$this->form_validation->set_rules('fitID', 'fitID', 'required|is_natural');
		
		if( $this->form_validation->run() == TRUE )
		{
			$fitID = $this->input->post('fitID');
			
			$fit_info = $this->Doctrine_model->get_fit_info( $fitID );
			if( $fit_info != FALSE )
			{
				if( $this->Doctrine_model->make_fit_public( $fitID, $userID ) )
				{
					$this->reset_fit_output_cache( $fitID );
					//$this->session->set_flashdata( 'flash_message', "Fit ID: $fitID has been made Public." );
					$content = 'Fit https://spectrefleet.com/doctrine/fit/'.$fitID.', `'.html_entity_decode( $fit_info['fitName'], ENT_QUOTES | ENT_HTML5 ).'` is no longer Official.';
					$result = $this->Discord_model->tell_command( $content );
					if( $result['response'] == FALSE )
					{
						log_message( 'error', "Doctrine controller: failure to tell_command( $content )." );
					}
					// Go to the fit's page
					redirect("doctrine/fit/$fitID", 'location');
				}
				else
				{
					$this->session->set_flashdata( 'flash_message', "There was a problem making Public fit ID: $fitID." );
					log_message( 'error', 'Doctrine controller: failed for user:'.$userID.':'.$this->session->user_session['Username']." while making Public fitID:$fitID." );
					redirect('doctrine/manage_fits', 'location');
				}
			}
			else
			{
				// If no permission, redirect to profile page.		Malicious population of fitID field?!
				$this->session->set_flashdata( 'flash_message', 'Insufficient permission to make Public fit ID: '.$fitID.'.' );
				log_message( 'error', 'Doctrine controller: failed for user:'.$userID.':'.$this->session->user_session['Username'].' Insufficient permission to make Public fit ID:'.$fitID.'.' );
				redirect('portal', 'location');
			}
		}
		else
		{
			// Go to user fits page.
			redirect('doctrine/manage_fits', 'location');
		}
		
	}// make_fit_public()
	
	
	public function new_fleet()	// Handles the form for making new fleets
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_MANAGE_OWN_FITS' );
		
		$userID = $this->session->user_session['UserID'];
		
		$this->form_validation->set_rules('fleetName', 'fleet name', 'required|max_length[255]');
		$this->form_validation->set_rules('fleetType', 'fleet type', 'required|callback__is_fleet_role');
		$this->form_validation->set_rules('fleetComposition[]', 'fleet composition', 'required');
		$this->form_validation->set_rules('fleetDescription', 'description', 'required|max_length[10000]');
		
		if( $this->form_validation->run() == TRUE )
		{
			$fleetName = htmlentities( $this->input->post('fleetName'), ENT_QUOTES);
			$fleetType = htmlentities( $this->input->post('fleetType'), ENT_QUOTES);
			$fleetDescription = strip_tags( $this->input->post('fleetDescription'), Doctrine_model::DESCRIPTION_TAGS );
			$ships = $this->input->post('fleetComposition');	// Should be validated as a list of owned fits!
			
			$fleetID = $this->Doctrine_model->add_fleet( $fleetName, $fleetType, $fleetDescription, $userID, $ships );
			
			if( $fleetID != FALSE )
			{
				// Go to the fleet's page
				redirect("doctrine/fleet/$fleetID", 'location');
			}
			else
			{
				$this->session->set_flashdata( 'flash_message', 'There was a problem adding the new fleet.' );
				log_message( 'error', 'Doctrine controller: failed for user:'.$userID.':'.$this->session->user_session['Username'].' while adding a new fleet.' );
				//Go to user doctrines page.
				redirect('doctrine/manage_doctrines', 'location');
			}
		}
		else
		{
			// Field validation failed. Reload new fleet page with errors and form data.
			
			$fleet_info = array(
				'fleetName' => '',
				'fleetType' => '',
				'fleetDescription' => 'Description or Explanation of Fleet'
			);
			$ships = array();
			
			if( isset( $_POST['fleetType'] ) )
			{
				$fleet_info['fleetType'] = $_POST['fleetType'];
			}
			if( isset( $_POST['fleetComposition'] ) )
			{
				$ships = $_POST['fleetComposition'];
			}
			// Can't seem to manage to reset an empty fleetDescription to the helper string...
			/*if( isset( $_POST['fleetDescription'] ) && !empty( $_POST['fleetDescription'] ) )
			{
				$fleet_info['fleetDescription'] = $_POST['fleetDescription'];
			}
			else
			{	
				$data['fleetDescription'] = 'Description or Explanation of Fleet';
			}*/
			
			$data['info'] = $fleet_info;
			$data['ships'] = $ships;
			
			$data['fits'] = $this->Doctrine_model->get_user_fits( $userID, TRUE );
			
			$data['roles'] = Doctrine_model::FLEET_ROLES();
			
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Create New Fleet' ) );
			$this->load->view( 'portal/portal_header' );
			$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
			$this->load->view( 'portal/portal_content' );
			$this->load->view( 'doctrine/new_fleet', $data );
			$this->load->view( 'portal/portal_footer' );
			$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE, 'SELECT2' => TRUE, 'CKEDITOR' => TRUE ) );
		}
		
	}// new_fleet()
	
	public function _is_fleet_role( $fleetRole ){
		if( $this->Doctrine_model->is_fleet_role( $fleetRole ) )
		{
			return TRUE;
		}
		else
		{
			$this->form_validation->set_message('_is_fleet_role', 'The specified fleet role is not valid.');
			return FALSE;
		}
	}// _is_fleet_role()
	
	public function edit_fleet()		// Let's use this to populate the form with partial edit values over the stored ones
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_MANAGE_OWN_FITS' );
		
		$userID = $this->session->user_session['UserID'];
		
		$this->form_validation->set_rules('fleetName', 'fleet name', 'required|max_length[255]');
		$this->form_validation->set_rules('fleetType', 'fleet type', 'required');
		$this->form_validation->set_rules('fleetComposition[]', 'fleet composition', 'required');
		$this->form_validation->set_rules('fleetDescription', 'description', 'required|max_length[10000]');
		$this->form_validation->set_rules('fleetID', 'fleetID', 'required|is_natural');
		
		if( $this->form_validation->run() == TRUE )
		{
			$fleetID = $this->input->post('fleetID');
			
			if( self::can_modify_fleet( $userID, $fleetID ) )
			{
				$fleetName = htmlentities( $this->input->post('fleetName'), ENT_QUOTES);
				$fleetType = htmlentities( $this->input->post('fleetType'), ENT_QUOTES);
				$ships = $this->input->post('fleetComposition');	// Should be validated as a list of owned fits!
				$fleetDescription = strip_tags( $this->input->post('fleetDescription'), Doctrine_model::DESCRIPTION_TAGS );
				
				if( $this->Doctrine_model->edit_fleet( $fleetType, $fleetName, $fleetDescription, $fleetID, $ships ) )
				{
					// Go to the fleet's page
					redirect("doctrine/fleet/$fleetID", 'location');
				}
				else
				{
					$this->session->set_flashdata( 'flash_message', "There was a problem editing fleet ID: $fleetID." );
					log_message( 'error', 'Doctrine controller: failed for user:'.$userID.':'.$this->session->user_session['Username'].' while editing fleetID:'.$fleetID.'.' );
					//Go to user doctrines page.
					redirect('doctrine/manage_doctrines', 'location');
				}
			}
			else
			{
				// If no permission, redirect to profile page.		Malicious population of fleetID field?!
				$this->session->set_flashdata( 'flash_message', 'Insufficient permission to edit fleet ID: '.$fleetID.'.' );
				log_message( 'error', 'Doctrine controller: failed for user:'.$userID.':'.$this->session->user_session['Username'].' Insufficient permission to edit fleet ID:'.$fleetID.'.' );
				redirect('portal', 'location');
			}
		}
		else
		{
			$fleetID = $this->input->post('fleetID');	// We shouldn't trust that it's set, or a number, or a real fleetID
			
			if( !self::_is_integer_string( $fleetID ) )
			{
				self::_not_found();
			}
			
			$fleet_ships = $this->Doctrine_model->get_fleet_fitIDs( $fleetID );
			$fleet_info = $this->Doctrine_model->get_fleet_info( $fleetID );
			
			if( $fleet_info === FALSE )
			{
				self::_not_found();
			}
			
			$ships = array();
			foreach($fleet_ships as $fleet_ship)
			{
				$ships[] = $fleet_ship['fitID'];
			}
			
			if( isset( $_POST['fleetType'] ) )
			{
				$fleet_info['fleetType'] = $_POST['fleetType'];		// Override displaying stored with latest submitted
			}
			if( isset( $_POST['fleetComposition'] ) )
			{
				$ships = $_POST['fleetComposition'];				// Override displaying stored with latest submitted
			}
			// Are we being consistent with info['fleetDescription']?
			
			$data['info'] = $fleet_info;
			$data['ships'] = $ships;
			
			$data['fits'] = $this->Doctrine_model->get_user_fits( $userID, TRUE );
			
			$data['roles'] = Doctrine_model::FLEET_ROLES();
			
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Edit: '. $data['info']['fleetName'] ) );
			$this->load->view( 'portal/portal_header' );
			$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
			$this->load->view( 'portal/portal_content' );
			$this->load->view( 'doctrine/edit_fleet', $data );
			$this->load->view( 'portal/portal_footer' );
			$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE, 'SELECT2' => TRUE, 'CKEDITOR' => TRUE ) );
		}
		
	}// edit_fleet()
	
	
	public function edit_fleet_ratios()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_MANAGE_OWN_FITS' );
		
		$userID = $this->session->user_session['UserID'];
		
		$this->form_validation->set_rules('fleetID', 'fleetID', 'required|is_natural');
		
		if( isset( $_POST['ratios'] ) && $this->form_validation->run() == TRUE )
		{
			$fleetID = $this->input->post('fleetID');
			
			// Form and Credential Validation.
			$this->form_validation->reset_validation();
			//$this->form_validation->set_data( $_POST );
			$this->form_validation->set_rules('ratios[]', 'ratios', 'callback__validate_ratios');
			
			if( $this->form_validation->run() == TRUE )
			{
				$ratios = array();
				foreach( $this->input->post( 'ratios' ) as $fitID => $ratio )
				{
					$ratios[$fitID] = $ratio;
				}
				
				if( self::can_modify_fleet( $userID, $fleetID ) )
				{
					if( $this->Doctrine_model->edit_fleet_ratios( $fleetID, $ratios ) )
					{
						// Go to the fleet's page
						redirect("doctrine/fleet/$fleetID", 'location');
					}
					else
					{
						$this->session->set_flashdata( 'flash_message', "There was a problem editing ratios for fleet ID: $fleetID." );
						log_message( 'error', 'Doctrine controller: failed for user:'.$userID.':'.$this->session->user_session['Username'].' while editing ratios for fleetID:'.$fleetID.'.' );
						//Go to user doctrines page.
						redirect('doctrine/manage_doctrines', 'location');
					}
				}
				else
				{
					// If no permission, redirect to profile page.		Malicious population of fleetID field?!
					$this->session->set_flashdata( 'flash_message', 'Insufficient permission to edit ratios for fleet ID: '.$fleetID.'.' );
					log_message( 'error', 'Doctrine controller: failed for user:'.$userID.':'.$this->session->user_session['Username'].' Insufficient permission to edit ratios for fleet ID:'.$fleetID.'.' );
					redirect('portal', 'location');
				}
			}
			// Else fall through to form validation failure.
		}
		
		// Field validation failed. Reload new fit page with errors and form data.
		
		$fleetID = $this->input->post('fleetID');	// We shouldn't trust that it's set, or a number, or a real fleetID
		
		if( !self::_is_integer_string( $fleetID ) )
		{
			self::_not_found();
		}
		
		$fleet_info = $this->Doctrine_model->get_fleet_info( $fleetID );
		
		if( $fleet_info === FALSE )
		{
			self::_not_found();
		}
		
		$fleet_ships = $this->Doctrine_model->get_fleet_fitIDs( $fleetID );
		
		$ships_info = array();
		foreach( $fleet_ships as $ship )
		{
			$fitID = $ship['fitID'];
			$ships_info[$fitID] = array(
				'info' => $this->Doctrine_model->get_fit_info( $fitID ),
				'ratio' => ($ship['ratio'] === NULL) ? 1 : $ship['ratio']	// Minimum ratio value is 1.
			);
		}
		
		$data['fleet_info'] = $fleet_info;
		$data['ships_info'] = $ships_info;
		
		$data['roles'] = Doctrine_model::FLEET_ROLES();
		
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Edit ratios: '. $data['fleet_info']['fleetName'] ) );
		$this->load->view( 'portal/portal_header' );
		$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
		$this->load->view( 'portal/portal_content' );
		$this->load->view( 'doctrine/edit_fleet_ratios', $data );
		$this->load->view( 'portal/portal_footer' );
		$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE ) );
		
	}// edit_fleet_ratios()
	
	function _validate_ratios()
	{
		$ratios  = $this->input->post('ratios');
		
		if( !is_array( $ratios ) )
		{
			$this->form_validation->set_message('_validate_ratios', 'The ratios field is not valid.');
			return FALSE;
		}
		else
		{
			if( empty( $ratios ) )
			{
				$this->form_validation->set_message('_validate_ratios', 'The ratios array is empty.');
				return FALSE;
			}
			else
			{
				foreach( $ratios as $ratio )
				{
					if( !self::_is_integer_string( $ratio ) )
					{
						$this->form_validation->set_message('_validate_ratios', 'The ratios array did not validate for value:'.$ratio.'.');
						return FALSE;
					}
				}
				return TRUE;
			}
		}
	}//_validate_ratios()
	
	
	public function retire_fleet()
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_MANAGE_OWN_FITS' );
		
		$userID = $this->session->user_session['UserID'];
		
		$this->form_validation->set_rules('fleetID', 'fleetID', 'required|is_natural');
		
		if( $this->form_validation->run() == TRUE )
		{
			$fleetID = $this->input->post('fleetID');
			
			if( self::can_modify_fleet( $userID, $fleetID ) )
			{
				if( $this->Doctrine_model->retire_fleet( $fleetID ) )
				{
					$this->session->set_flashdata( 'flash_message', "Fleet ID: $fleetID has been retired." );
					redirect('doctrine/manage_doctrines', 'location');
				}
				else
				{
					$this->session->set_flashdata( 'flash_message', "There was a problem retiring fleet ID: $fleetID." );
					log_message( 'error', 'Doctrine controller: failed for user:'.$userID.':'.$this->session->user_session['Username'].' while retiring fleetID:'.$fleetID.'.' );
					redirect('doctrine/manage_doctrines', 'location');
				}
			}
			else
			{
				// If no permission, redirect to profile page.		Malicious population of fleetID field?!
				$this->session->set_flashdata( 'flash_message', 'Insufficient permission to retire fleet ID: '.$fleetID.'.' );
				log_message( 'error', 'Doctrine controller: failed for user:'.$userID.':'.$this->session->user_session['Username'].' Insufficient permission to retire fleet ID:'.$fleetID.'.' );
				redirect('portal', 'location');
			}
		}
		else
		{
			// Go to user doctrines page.
			redirect('doctrine/manage_doctrines', 'location');
		}
		
	}// retire_fleet()
	
	
	public function fit( $fitID = NULL )
	{
		if( !self::_is_integer_string( $fitID ) )
		{
			redirect('doctrine/fits', 'location');
		}
		
		$info = $this->Doctrine_model->get_fit_info( $fitID );
		if( $info === FALSE )
		{
			self::_not_found();
		}
		
		$data['info'] = $info;
		
		$fit_items = $this->Doctrine_model->get_fit_items( $fitID, $info['shipID'], $info['isStrategicCruiser'] );
		$data['fit_items'] = $fit_items;
		$data['EFT'] = $this->libfit->generate_EFT( $info, $fit_items );
		
		$data = array_merge( $data, $this->fit_permissions( $info ) );
		
		$this->output->cache( 60 );		// Doctrine model should store fit details cache timer, in minutes?
		$this->load->view( 'common/header', array(
			'PAGE_TITLE' => $data['info']['fitName'],
			'PAGE_AUTHOR' => $data['info']['username'],
			'PAGE_DESC' => $data['info']['fitDescription'] )
		);
		$this->load->view( 'doctrine/display_fit', $data );
		$this->load->view( 'doctrine/hidden' ,$data ) ;
		$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE ) );
	}// fit()
	
	private function fit_permissions( $info )
	{
		$can_modify_fit = FALSE;
		$can_have_fits = FALSE;
		$can_modify_status = FALSE;
		$status = NULL;
		if( $info !== FALSE && $this->_is_logged_in() )
		{
			if( $this->_has_permission('CAN_MANAGE_OWN_FITS') )
			{
				if( self::can_modify_fit( $this->session->user_session['UserID'], $info['fitID'] ) )
				{
					$can_modify_fit = TRUE;
				}
				
				$can_have_fits = TRUE;
			}
			if( $this->_has_permission('CAN_MAKE_FITS_OFFICIAL') )
			{
				$can_modify_status = TRUE;
				$status = $info['status'];
			}
		}
		$data = array(
			'can_modify_fit' => $can_modify_fit,
			'can_have_fits' => $can_have_fits,
			'can_modify_status' => $can_modify_status,
			'status' => $status
		);
		return $data;
	}// fit_permissions()
	
	public function fit_json( $fitID = NULL )
	{
		$data['csrf_name'] = $this->security->get_csrf_token_name();
		$data['csrf_hash'] = $this->security->get_csrf_hash();
		
		if( !self::_is_integer_string( $fitID ) )
		{
			$data = array_merge( $data, $this->fit_permissions( FALSE ) );
			$this->output->set_content_type( 'application/json' );
			$this->output->set_status_header( 400 );
			$this->output->set_output( json_encode( $data ) );
			return;
		}
		
		$info = $this->Doctrine_model->get_fit_info( $fitID );
		if( $info === FALSE )
		{
			$data = array_merge( $data, $this->fit_permissions( FALSE ) );
			$this->output->set_content_type( 'application/json' );
			$this->output->set_status_header( 404 );
			$this->output->set_output( json_encode( $data ) );
			return;
		}
		
		$data = array_merge( $data, $this->fit_permissions( $info ) );
		$this->output->set_content_type( 'application/json' );
		$this->output->set_status_header( 200 );
		$this->output->set_output( json_encode( $data ) );
	}// fit_json()
	
	public function fleetID()
	{
		if( !isset( $_POST['fleetID'] ) )
		{
			redirect('doctrine/fleets', 'location');
		}
		
		$fleetID = $this->input->post('fleetID');
		if( !self::_is_integer_string( $fleetID ) )
		{
			redirect('doctrine/fleets', 'location');
		}
		/*
		$fleet_info = $this->Doctrine_model->get_fleet_info( $fleetID );
		if( $fleet_info === FALSE )
		{
			self::_not_found();
		}
		*/
		redirect('doctrine/fleet/'.$fleetID, 'location');
	}// fleetID()
	
	public function fleet( $fleetID = NULL )
	{
		if( !self::_is_integer_string( $fleetID ) )
		{
			redirect('doctrine/fleets', 'location');
		}
		
		$fleet_info = $this->Doctrine_model->get_fleet_info( $fleetID );
		if( $fleet_info === FALSE )
		{
			self::_not_found();
		}
		
		$ships = $this->Doctrine_model->get_fleet_fitIDs( $fleetID );
		
		$ships_info = array();
		$total_ratio = 0;
		$largest_ratio = 0;
		foreach($ships as $ship)
		{
			$ratio = ($ship['ratio'] === NULL) ? 1 : $ship['ratio'];	// Minimum ratio value is 1.
			$total_ratio += $ratio;
			$largest_ratio = ($ratio > $largest_ratio) ? $ratio : $largest_ratio;
			$ships_info[] = array(
				'info' => $this->Doctrine_model->get_fit_info( $ship['fitID'] ),
				'ratio' => $ratio
			);
		}
		
		$data['fleet_info'] = $fleet_info;
		$data['ships_info'] = $ships_info;
		$data['total_ratio'] = $total_ratio;
		$data['largest_ratio'] = $largest_ratio;
		
		$can_modify_fleet = FALSE;
		$can_have_fleets = FALSE;
		if( $this->_is_logged_in() )
		{
			if( $this->_has_permission('CAN_MANAGE_OWN_FITS') )
			{
				$userID = $this->session->user_session['UserID'];
				if( self::can_modify_fleet( $userID, $fleetID ) )
				{
					$can_modify_fleet = TRUE;
				}
				
				$can_have_fleets = TRUE;
			}
		}
		$data['can_modify_fleet'] = $can_modify_fleet;
		$data['can_have_fleets'] = $can_have_fleets;
		
		$this->load->view( 'common/header', array(
			'PAGE_TITLE' => $data['fleet_info']['fleetName'],
			'PAGE_AUTHOR' => $data['fleet_info']['Username'],
			'PAGE_DESC' => $data['fleet_info']['fleetDescription'] )
		);
		$this->load->view( 'doctrine/display_doctrine', $data );
		$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE ) );
	}// fleet()
	
	public function edit_fits( $fitID = NULL )	// We use this to populate the form with the stored values, no partial edit ones
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_MANAGE_OWN_FITS' );
		
		if( $fitID !== NULL && ctype_digit( $fitID ) )
		{
			$info = $this->Doctrine_model->get_fit_info($fitID);
			if( $info === FALSE )
			{
				self::_not_found();
			}
			$data['info'] = $info;
			
			$fit_items = $this->Doctrine_model->get_fit_items( $fitID, $info['shipID'], $info['isStrategicCruiser'] );
			$data['EFT'] = $this->libfit->generate_EFT( $info, $fit_items );
					
			$data['roles'] = Doctrine_model::FIT_ROLES();
			
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Edit: '. $data['info']['fitName'] ) );
			$this->load->view( 'portal/portal_header' );
			$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
			$this->load->view( 'portal/portal_content' );
			$this->load->view( 'doctrine/edit_fit',$data );
			$this->load->view( 'portal/portal_footer' );
			$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE, 'SELECT2' => TRUE, 'CKEDITOR' => TRUE ) );
		}
		else
		{
			// Manual removal of fitID field??
			redirect('portal', 'location');
		}
		
	}// edit_fits()
	
	public function edit_fleets( $fleetID = NULL )	// We use this to populate the form with the stored values, no partial edit ones
	{
		$this->_ensure_logged_in();
		
		$this->_ensure_one_of( 'CAN_MANAGE_OWN_FITS' );
			
		if( $fleetID !== NULL && ctype_digit( $fleetID ) )
		{
			$fleet_info = $this->Doctrine_model->get_fleet_info( $fleetID );
			if( $fleet_info === FALSE )
			{
				self::_not_found();
			}
			$fleet_ships = $this->Doctrine_model->get_fleet_fitIDs( $fleetID );
			
			$ships = array();
			foreach($fleet_ships as $fleet_ship)
			{
				$ships[] = $fleet_ship['fitID'];
			}
			
			$data['info'] = $fleet_info;
			$data['ships'] = $ships;
			
			$user_session = $this->session->user_session;
			$userID = $user_session['UserID'];
			
			$data['fits'] = $this->Doctrine_model->get_user_fits( $userID, TRUE );
		
			$data['roles'] = Doctrine_model::FLEET_ROLES();
			
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Edit: '. $data['info']['fleetName'] ) );
			$this->load->view( 'portal/portal_header' );
			$this->load->view( 'portal/portal_menu', $this->_get_permissions() );
			$this->load->view( 'portal/portal_content' );
			$this->load->view( 'doctrine/edit_fleet',$data );
			$this->load->view( 'portal/portal_footer' );
			$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE, 'SELECT2' => TRUE, 'CKEDITOR' => TRUE ) );
		}
		else
		{
			// Manual removal of fleetID field??
			redirect('portal', 'location');
		}
		
	}// edit_fleets()
	
	private function can_modify_fit( $userID, $fitID )
	{
		$fit_info = $this->Doctrine_model->get_fit_info( $fitID );
		if( $fit_info == FALSE )	// Check fit existance
		{
			return FALSE;
		}
		if( $fit_info['userID'] != $userID )	// Check fit ownership
		{
			return FALSE;
		}
		if( $fit_info['status'] != 'Public' )	// Check fit status
		{
			return FALSE;
		}
		return TRUE;
	}// can_modify_fit()
	
	private function can_modify_fleet( $userID, $fleetID )
	{
		$fleet_info = $this->Doctrine_model->get_fleet_info( $fleetID );
		if( $fleet_info == FALSE )	// Check fleet existance
		{
			return FALSE;
		}
		if( $fleet_info['userID'] != $userID )	// Check fleet ownership
		{
			return FALSE;
		}
		if( $fleet_info['status'] != 'Public' )	// Check fleet status
		{
			return FALSE;
		}
		return TRUE;
	}// can_modify_fleet()
	
	private function reset_fit_output_cache( $fitID )
	{
		$this->output->delete_cache( "doctrine/fit/$fitID" );
		$this->output->delete_cache( "f/s/$fitID" );
		/*
		$fleetIDs = get_fit_fleetIDs( $fitID );
		foreach( $fleetIDs as $fleetID )
		{
			$this->output->delete_cache( "doctrine/fleet/$fleetID" );
		}
		*/
		// Elsewhere only uses the shipID of fit_info, which can't change, or are non-cacheable user pages
		
	}// reset_fit_output_cache()
	
}// Doctrine
?>