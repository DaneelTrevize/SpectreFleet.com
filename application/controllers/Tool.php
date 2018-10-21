<?php
class Tool extends SF_Controller {
	
	
	const OLD_DSCAN_COLUMNS = 3;
	const NEW_DSCAN_COLUMNS = 4;
	
	const P_VISIBLE_SYSTEM_NAME = '([[:alnum:]-]{1,})';
	const SYSTEM_XML_PATTERN = '#'. '^' . '<url=showinfo:5//(?:[[:digit:]]{8,8}) alt=\'Current Solar System\'>' .self::P_VISIBLE_SYSTEM_NAME. '</url>' . '(?:.{0,})$' .'#';
	
	
	public function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->model('Tool_model');
		$this->load->model('CharacterID_model');
		$this->load->model('CharacterAffiliation_model');
		$this->load->model('Discord_model');
	}// __construct()
	

	public function lscan()
	{
		/*
		*	A local scan tool should:
		*	Take a list of local-sourced character names, one per line.
		*	Optionally take a list of fleet-sourced character names, one per line.	Fleet Boss could use ESI?
		*
		*	(Both lists should be sorted in ascending alphabetical order ignoring case, without duplicates.)
		*
		*	If a fleet name list is supplied, remove any matching entries from the local name list before proceeding.
		*
		*	Each local character name should be between 3 and 37 characters long, can contain at most 2 spaces,
		*	"May contain the characters A-Z, a-z, 0-9, hyphen-minus and single quotation.
		*	(Corporation names may also include the dot character.) Space, hyphen-minus and 
		*	single quotation characters are not allowed as the first or last character in a name."
		*
		*	Valid character names should be resolved against a caching call to the CharacterID API.
		*
		*	Valid CharacterIDs should be resolved against a caching call to the CharacterAffiliation API.
		*
		*/
		
		$this->form_validation->set_rules('Local', 'Local', 'required');	// Validate format as being character names list
		//$this->form_validation->set_rules('Fleet', 'Fleet', 'required');
		
		if( $this->form_validation->run() == TRUE )
		{
			
			$local = $this->input->post('Local');
			$fleet = $this->input->post('Fleet');
			$system = htmlentities( $this->input->post('system'), ENT_QUOTES );
			
			if( strlen($local) == 0 )
			{
				redirect('/lscan', 'location');
			}
			
			if( preg_match( self::SYSTEM_XML_PATTERN, $this->input->post('system'), $matches ) === 1 )
			{
				$system = htmlentities( $matches[1], ENT_QUOTES );	// Encoded because we store the string (for now...)
			}
			
			$local = explode( "\r\n", $local );		// I bet the Mac and Linux Eve clients via Wine also use Windows \r\n
			
			if( strlen($fleet) != 0 )
			{
				$fleet = explode( "\r\n", $fleet );	
				$local = array_diff( $local, $fleet );
			}
			
			//log_message( 'debug', 'Tool controller: local size: '.count($local) );
			
			// Validate that all rows are only of characters permitted in Eve character names
			foreach( $local as $character_name )
			{
				if( !$this->CharacterID_model->is_valid_character_name( $character_name ) )
				{
					// Bad input, abort
					redirect('/lscan', 'location');
				}
			}
			
			$characters = $this->CharacterID_model->get_character_data( $local );
			if( $characters === FALSE )
			{
				redirect('/lscan', 'location');
			}
			
			$characterIDs = array_column( $characters, 'id' );
			
			//log_message( 'debug', 'Tool controller: characterIDs size: '.count($characterIDs) );
			
			$scanID = $this->Tool_model->add_local_scan( $system, $characterIDs );
			
			if( $scanID === FALSE )
			{
				redirect('/lscan', 'location');
			}
			
			redirect('l/'. base_convert($scanID, 10, 36), 'location');
		}
		else
		{
			// Field validation failed. Reload new scan submission page.
			
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Local Scan' ) );
			$this->load->view( 'tools/local_submit' );
			$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE ) );
		}
	}// lscan()
	
	public function l( $ID = NULL )
	{
		if( $ID === NULL )
		{
			redirect('lscan', 'location');
		}
		
		$scanID = base_convert($ID, 36, 10);
		
		$info = $this->Tool_model->get_local_scan_info( $scanID );
		
		if( $info === FALSE )
		{
			self::_not_found();
		}
		
		$characterIDs_array = $this->Tool_model->get_local_scan_details( $scanID );
		if( $characterIDs_array === FALSE || empty($characterIDs_array) )
		{
			self::_not_found();
		}
		
		$characterIDs = array_column( $characterIDs_array, 'characterID' );
		$affiliations = $this->CharacterAffiliation_model->get_character_data( $characterIDs );
		
		$data['info'] = $info;
		//$data['affiliations'] = $this->CharacterAffiliation_model->get_grouped_characteraffiliation_data( $characterIDs );
		$data['affiliations'] = $affiliations;
		
		$this->output->cache( 5 );		// Arbitrary 5 minute cache timer
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Local Scan '.$scanID ) );
		$this->load->view( 'tools/local_result', $data );
		$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE, 'TABLESORTER' => TRUE ) );
		
	}// l()
	
	
	public function dscan()
	{
		$this->form_validation->set_rules('results', 'Results', 'required');
		
		if( $this->form_validation->run() == TRUE )
		{
			
			$scan = htmlentities( $this->input->post('results'), ENT_QUOTES );
			$system = $this->input->post('system');		// Raw
			
			$this->load->model('Eve_SDE_model');
			
			if( strlen($scan) == 0 )
			{
				show_error( 'No scan data supplied.', 400 );
			}
			if( $system != '' )
			{
				// Check if it's the in-game XML copy-paste format
				if( preg_match( self::SYSTEM_XML_PATTERN, $system, $matches ) === 1 )
				{
					$system = $matches[1];
				}
				
				$valid_system = $this->Eve_SDE_model->get_solarSystem_by_name( $system );
				if( !$valid_system )
				{
					show_error( 'Invalid system name supplied.', 400 );
				}
				$system = $valid_system['solarSystemID'];
			}
			else
			{
				$system = NULL;
			}
			
			$lines = explode( "\r\n", $scan );
			
			$offgrid_types_count = array();
			$ongrid_types_distances = array();
			
			$lines_count = count( $lines );
			for( $l = 0; $l < $lines_count; $l++ )
			{
				$item = explode( "\t", $lines[$l] );
				if( count($item) != self::NEW_DSCAN_COLUMNS )
				{
					show_error( 'Invalid scan data format supplied on line:'.($l+1), 400 );
				}
				
				$item_typeID = $item[0];
				$item_instanceName = $item[1];
				//$item_typeName = $item[2];
				$item_distance_string = $item[3];
				
				if( !ctype_digit($item_typeID) )
				{
					show_error( 'Invalid typeID in the supplied scan data on line:'.($l+1), 400 );
				}
				
				/*
				*	If no system determined yet
				*		if typeID in (sun, planet, moon, belt, station)
				*			try resolve first word of name via SDE?
				*/
				if( $system == NULL & in_array( $item_typeID, Eve_SDE_model::NAMED_CELESTIALS_TYPEIDS ) )
				{
					$candidate_name = explode( " ", $item_instanceName );
					if( count( $candidate_name ) < 2 )
					{
						log_message( 'error', 'Tool controller: Invalid system name format: '.$item_instanceName );
						show_error( 'Invalid system name format for celestial on line:'.($l+1), 400 );
					}
					$valid_system = $this->Eve_SDE_model->get_solarSystem_by_name( $candidate_name[0] );
					if( !$valid_system )
					{
						log_message( 'error', 'Tool controller: Invalid system name: '.$candidate_name[0] );
						show_error( 'Invalid system name for celestial on line:'.($l+1), 400 );
					}
					$system = $valid_system['solarSystemID'];
				}
				
				// Don't care about Tool_model::DSCAN_FILTER_ for now?
				
				if( $item_distance_string == "-" )
				{
					// Off-grid counting
					$this->add_offgrid( $offgrid_types_count, $item_typeID );
				}
				else
				{
					// Need to normalise $item_distance into km or offgrid
					$dist_len = strlen($item_distance_string);
					if( $dist_len < 3 )	// Value, space, unit
					{
						show_error( 'Invalid distance in the supplied scan data on line:'.($l+1), 400 );
					}
					$dist_unit_hint = $item_distance_string[$dist_len-2];	// One from the end
					$normalised_dist = NULL;
					switch( $dist_unit_hint )
					{
						case ' ':
							// Assume m
							$item_distance = substr( $item_distance_string, 0, $dist_len-2 );
							$item_distance = str_replace( array(',', '.'), '', $item_distance );
							if( !ctype_digit($item_distance) )
							{
								show_error( 'Invalid distance in the supplied scan data on line:'.($l+1), 400 );
							}
							$normalised_dist = floor( intval($item_distance) / 1000 );
							$ongrid_types_distances[$item_typeID][] = $normalised_dist;
							break;
						case 'k':
							// Assume km
							$item_distance = substr( $item_distance_string, 0, $dist_len-3 );
							$item_distance = str_replace( array(',', '.'), '', $item_distance );
							if( !ctype_digit($item_distance) )
							{
								show_error( 'Invalid distance in the supplied scan data on line:'.($l+1), 400 );
							}
							if( strlen($item_distance) > 6 )
							{
								// Risking 32bit int overflow
								$this->add_offgrid( $offgrid_types_count, $item_typeID );
								continue 2;	// do next foreach
							}
							else
							{
								$normalised_dist = intval($item_distance);
								$ongrid_types_distances[$item_typeID][] = $normalised_dist;
								break;
							}
						case 'A':
							// Assume AU
							$this->add_offgrid( $offgrid_types_count, $item_typeID );
							continue 2;	// do next foreach
						default:
							show_error( 'Invalid distance in the supplied scan data on line:'.($l+1), 400 );
					}
					// End casing distance hint
				}
				// End normalising distance
			}
			// End for each line
			
			$ongrid_types_summary = array();
			foreach( $ongrid_types_distances as $item_typeID => $distances )
			{
				// Count distances, sort, capture first, median, last.
				$t_count = count( $distances );
				sort( $distances );	// regular, should match SORT_NUMERIC 
				//log_message( 'error', 'd:'.print_r($distances, TRUE) .'|a:'. print_r($ascending_distances, TRUE) );
				
				$t_closest = $distances[0];
				$t_furthest = $distances[$t_count-1];
				$t_median = $distances[ ceil(($t_count)/2)-1 ];	// Use intdiv( $t_count, 2 ) in PHP7
				
				$ongrid_types_summary[$item_typeID] = array(
					'count' => $t_count,
					'closest' => $t_closest,
					'furthest' => $t_furthest,
					'median' => $t_median
				);
			}
			
			arsort( $offgrid_types_count, SORT_NUMERIC );
			
			function sort_ongrid_count( $a_summary, $b_summary )
			{
				$a = $a_summary['count'];
				$b = $b_summary['count'];
				if( $a == $b )
				{
					return 0;
				}
				return ($a > $b) ? -1 : 1;	// Descending order
			}// sort_ongrid_count()
			uasort( $ongrid_types_summary, 'sort_ongrid_count' );
			
			
			$scanID = $this->Tool_model->add_dscan( $offgrid_types_count, $ongrid_types_summary, $system );
			if( $scanID === FALSE )
			{
				show_error( 'Problem storing scan data.', 400 );
			}
			
			$scanID = base_convert($scanID, 10, 36);
			redirect( 'd/'.$scanID, 'location' );
		}
		else
		{
			// Field validation failed. Reload new scan submission page.
			
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Directional Scan' ) );
			$this->load->view( 'tools/dscan_submit' );
			$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE ) );
		}
	}// dscan()
	
	private function add_offgrid( &$offgrid_types_count, $item_typeID )
	{
		if( array_key_exists( $item_typeID, $offgrid_types_count ) )
		{
			$offgrid_types_count[$item_typeID] +=1;
		}
		else
		{
			$offgrid_types_count[$item_typeID] = 1;
		}
	}// add_offgrid()
	
	
	public function d( $scanID = NULL )
	{
		if( $scanID === NULL )
		{
			redirect( 'dscan', 'location' );
		}
		
		$scanID = base_convert($scanID, 36, 10);
		
		$info = $this->Tool_model->get_dscan_info( $scanID );
		
		if( empty($info) )
		{
			$query = $this->Tool_model->get_old_dscan( $scanID );
			
			if( empty($query) )
			{
				self::_not_found();
			}
			
			$data['scan'] = unserialize( $query['Scan'] );
			$data['filter'] = $query['Filter'];
			$data['date'] = DateTime::createFromFormat( 'Y-m-d H:i:s.ue', $query['Date'] );
			
			$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Legacy Directional Scan' ) );
			$this->load->view( 'tools/old_dscan_result', $data );
			$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE ) );
			return;
		}
		
		// New style DScan result
		$sf_classes = $this->Tool_model->get_SF_classes();
		/*$sf_named_classes = array();
		foreach( $sf_classes as $id => $name )
		{
			$sf_named_classes[$name] = $id;
		}*/
		
		$ongrid = $this->Tool_model->get_dscan_ongrid( $scanID );
		$offgrid = $this->Tool_model->get_dscan_offgrid( $scanID );
		
		$ongrid_by_class = self::split_by_classes( $ongrid );
		$offgrid_by_class = self::split_by_classes( $offgrid );
		
		$ongrid_class_htmls = array();
		foreach( $ongrid_by_class as $sf_class => $ongrid_current_class )
		{
			$sf_class_name = $sf_classes[$sf_class];
			$ongrid_class_htmls[$sf_class_name] = $this->load->view( 'tools/display_ongrid_class', array(
				'sf_class' => $sf_class,
				'sf_class_name' => $sf_class_name,
				'ongrid_class' => $ongrid_current_class
			), TRUE );
		}
		
		$offgrid_class_htmls = array();
		foreach( $offgrid_by_class as $sf_class => $offgrid_current_class )
		{
			$sf_class_name = $sf_classes[$sf_class];
			$offgrid_class_htmls[$sf_class_name] = $this->load->view( 'tools/display_offgrid_class', array(
				'sf_class' => $sf_class,
				'sf_class_name' => $sf_class_name,
				'offgrid_class' => $offgrid_current_class
			), TRUE );
		}
		
		//$data['sf_named_classes'] = $sf_named_classes;
		$data['info'] = $info;
		$data['ongrid'] = $ongrid_class_htmls;
		$data['offgrid'] = $offgrid_class_htmls;
		
		$this->output->cache( 5 );		// Arbitrary 5 minute cache timer
		$this->load->view( 'common/header', array( 'PAGE_TITLE' => 'Directional Scan '.$scanID ) );
		$this->load->view( 'tools/display_dscan', $data );
		$this->load->view( 'common/footer', array( 'HIDE_LINKS' => TRUE, 'TABLESORTER' => TRUE ) );
		
	}// d()
	
	private static function split_by_classes( $dscan_data )
	{
		$by_class = array();
		if( empty($dscan_data) ) {
			return $by_class;
		}
		
		$sf_class = NULL;
		$current_class = array();
		foreach( $dscan_data as $summary )
		{
			if( $sf_class === NULL )
			{
				// First class
				$sf_class = $summary['sf_class'];
				if( $sf_class == NULL )
				{
					$sf_class = 4;	// 'Other'
				}
			}
			
			if( $summary['sf_class'] === $sf_class || ( $summary['sf_class'] == NULL && $sf_class == 4 ) )
			{
				// Continue class
				$current_class[] = $summary;
			}
			else
			{
				// End old class and start a new one
				$by_class[$sf_class] = $current_class;
				
				$sf_class = $summary['sf_class'];
				if( $sf_class == NULL )
				{
					$sf_class = 4;	// 'Other'
					if( array_key_exists( $sf_class, $by_class ) ) {
						$current_class = $by_class[$sf_class];
						$current_class[] = $summary;
					}
					else
					{
						$current_class = array( $summary );
					}
				}
				else 
				{
					$current_class = array( $summary );
				}
			}
		}
		// End last class
		$by_class[$sf_class] = $current_class;
		
		return $by_class;
	}// split_by_classes()
	
	
	public function image( $sf_class = '', $groupID = '' ) {
		$png_path = './media/image/dscan/'.$groupID.'.png';
		if( !file_exists( $png_path ) ) {
			
			$content = 'No image found for DScan groupID:' .$groupID;
			$result = $this->Discord_model->tell_tech( $content );
			if( $result['response'] == FALSE )
			{
				log_message( 'error', "Tool controller: failure to tell_tech( $content )." );
			}
			
			switch( $sf_class ) {
				case 2:		// "DPS"
				case 6:		// "Industrial"
				case 7:		// "Logistics"
				case 8:		// "Tackle"
				case 10:	// "Non-combat"
				case 11:	// "Supers"
				case 12:	// "Capitals"
				case 13:	// "EWar & Support"
					$png_path = './media/image/dscan/ship.png';
				break;
				case 3:		// "Deployable"
					$png_path = './media/image/dscan/structure.png';
				break;
				default:	// "Celestial", "Other", "NPC", "Drones & Fighters"
					$png_path = './media/image/dscan/entity.png';
				break;
			}
		}
		
		$this->output->cache( 525600 );		// 365days in minutes
		$this->output->set_content_type('png');
        $this->output->set_output( file_get_contents( $png_path ) );
	}// image()
	
}// Tool
?>
