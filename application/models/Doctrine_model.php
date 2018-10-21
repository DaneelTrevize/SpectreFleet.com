<?php
class Doctrine_model extends SF_Model {
	
	/*
	
		Current State	| Possible Action	| New State		| Permitted by
		------------------------------------------------------------------
						| Create			| Public		| (Rank/Role higher than Member)
		
		Public			| Retire			| Retired		| Owner, SFC
		Public			| Edit				| Public		| Owner, SFC
		Public			| Make Official		| Official		| SFC
		
		Retired			| 					| 				| 
		
		Official		| Revoke Official	| Public		| SFC
		
		
		Drafts being private to the owner.
		Retired being unlisted for creating/editing doctrines, but visible via activity history.
		
		For Fleets:
		Public			| Edit Ratios		| Public		| Owner
		
	*/
	public static function FIT_ROLES()
	{
		$pvp_fleet = array(
			'DPS',
			'Logistics',
			'Support',
			'Tackle',
			'Scout',
			'Entosis',
			'Hunter'
		);
		$pvp_solo = array(
			'Kiting',
			'Brawling'
		);
		$pve = array(
			'Mission',
			'Incursions',
			'Escalations',
			'Ratting',
			'Exploration',
			'Wormholes'
		);
		
		return array(
			'PvP (Fleet)' => $pvp_fleet,
			'PvP (Solo)' => $pvp_solo,
			'PvE' => $pve
		);
	}// FIT_ROLES()
	
	public static function is_fit_role( $role_candidate )		// We have categories of fit role
	{
		foreach( self::FIT_ROLES() as $category )
		{
			if( in_array( $role_candidate, $category ) )
			{
				return TRUE;
			}
		}
		return FALSE;
	}// is_fit_role()
	
	public static function FLEET_ROLES()
	{
		$pvp = array(
			'Strategic' => 'Strategic (Capitals)',
			'Long Range' => 'Long Range (100-250km)',
			'Mid Range' => 'Mid Range (25-100km)',
			'Close Range' => 'Close Range (0-25km)',
			'Skirmish' => 'Skirmish (No Logistics)',
			'Black Ops' => 'Black Ops',
			'Other' => 'Other'
		);
		return array(
			'PvP' => $pvp
		);
	}// FLEET_ROLES()
	
	public static function is_fleet_role( $role_candidate )		// We have categories of fleet roles
	{
		foreach( self::FLEET_ROLES() as $category )
		{
			foreach( $category as $role_value => $role_name )
			{
				if( $role_candidate == $role_value )
				{
					return TRUE;
				}
			}
		}
		return FALSE;
	}// is_fleet_role()
	
	public static function FLEET_SEARCH_FIELDS()
	{
		$fields = array(
			'createdBy' => 'string',
			'fleetDescription' => 'string',
			'fleetName' => 'string',
			'fleetType' => 'string',
			'shipName' => 'string',
			'full_shipName' => 'paired_boolean',
			//'moduleName' => 'string',
			'ships_fitID' => 'integer'
		);
		return $fields;
	}// FLEET_SEARCH_FIELDS()
	
	public static function FLEET_ORDERTYPES()
	{
		$orderTypes = array(
			'lastEdited' => 'Last Edited Date',
			'date' => 'Creation Date',
			'fleetType' => 'Fleet Type',
			'CharacterName' => 'Creator\'s Name',
			'fleetName' => 'Fleet Name'
		);
		return $orderTypes;
	}// FLEET_ORDERTYPES()
	
	public static function FLEET_PAGESIZES()
	{
		$pageSizes = array(
			10,
			20,
			50,
			100
		);
		return $pageSizes;
	}// FLEET_PAGESIZES()
	
	const MANAGE_DOCTRINES_PAGESIZE = 10;
	
	public static function FIT_SEARCH_FIELDS()
	{
		$fields = array(
			'createdBy' => 'string',
			'fitDescription' => 'string',
			'fitName' => 'string',
			'fitRole' => 'string',
			'shipName' => 'string',
			'full_shipName' => 'paired_boolean',
			'moduleName' => 'string',
			'full_moduleName' => 'paired_boolean',
			'onlyOfficial' => 'boolean',
			'alsoRetired' => 'boolean'
		);
		return $fields;
	}// FIT_SEARCH_FIELDS()
	
	public static function FIT_ORDERTYPES()
	{
		$orderTypes = array(
			'lastEdited' => 'Last Edited Date',
			'date' => 'Creation Date',
			'shipName' => 'Ship Name',
			'fitRole' => 'Fit Group',
			'CharacterName' => 'Creator\'s Name',
			'fitName' => 'Fit Name'
		);
		return $orderTypes;
	}// FIT_ORDERTYPES()
	
	public static function FIT_PAGESIZES()
	{
		$pageSizes = array(
			10,
			20,
			50,
			100
		);
		return $pageSizes;
	}// FIT_PAGESIZES()
	
	const MANAGE_FITS_PAGESIZE = 10;
	const DESCRIPTION_TAGS = '<p><strong><em><s><ol><ul><li><blockquote><h1><h2><h3><div><pre><hr><br>';
	
	
	public function __construct()
	{
		$this->load->model('Eve_SDE_model');
		$this->load->library('dynamic_search');
	}// __construct()
	
	
	public function add_fit( $fitRole, $fitDescription, $userID, $parseFit, $fitName )
	{
		$shipID = $parseFit['shipID'];
		if( $shipID == NULL )
		{
			return FALSE;
		}
		
		$date = self::SF_now_dtz_db_text();
		
		self::ensure_db_conn();
		
		$this->db->trans_start();
		
		if( $fitName != NULL )
		{
			$this->db->set( 'fitName', $fitName );
		}
		else
		{
			$this->db->set( 'fitName', $parseFit['name'] );
		}
		$this->db->set( 'fitRole', $fitRole );
		$this->db->set( 'fitDescription',$fitDescription );
		$this->db->set( 'shipID', $shipID );
		$this->db->set( 'userID', $userID );
		$this->db->set( 'date', $date );
		$this->db->set( 'lastEdited', $date );
		$insert_success = $this->db->insert( 'fit_info' );
		
		if( $insert_success )
		{
			$fitID = $this->db->insert_id();
			
			self::add_modules( $fitID, $parseFit['slots'] );
			self::add_charges( $fitID, $parseFit['slots'] );
			self::add_drones( $fitID, $parseFit['drones'] );
			self::add_cargo( $fitID, $parseFit['cargo'] );
		}
		
		$this->db->trans_complete();
		
		if( $this->db->trans_status() === TRUE )
		{
			return $fitID;
		}
		else
		{
			return FALSE;
		}
	}// add_fit()
	
	private function add_modules( $fitID, $slots )
	{
		$insert = array();
		foreach( $slots as $slotID => $item )
		{
			$moduleID = $item['moduleID'];
			if( $moduleID !== NULL )
			{
				$insert[] = array(
					'fitID' => $fitID,
					'slotID' => $slotID,
					'moduleID' => $moduleID
				);
			}
		}
		if( !empty($insert) )
		{
			self::ensure_db_conn();
			
			$this->db->insert_batch( 'fit_modules', $insert );
		}
	}// add_modules()
	
	private function add_charges( $fitID, $slots )
	{
		$insert = array();
		foreach( $slots as $slotID => $item )
		{
			if( array_key_exists( 'chargeID', $item ) )
			{
				$chargeID = $item['chargeID'];
				if( $chargeID !== NULL )
				{
					$insert[] = array(
						'fitID' => $fitID,
						'slotID' => $slotID,
						'chargeID' => $chargeID
					);
				}
			}
		}
		if( !empty($insert) )
		{
			self::ensure_db_conn();
			
			$this->db->insert_batch( 'fit_charges', $insert );
		}
	}// add_charges()
	
	private function add_drones( $fitID, $parsed_drones )
	{
		// Ensure fit_drones has PK defined as fitID, droneID? Or permit duplicate stacks, even of the same count?
		
		if( !empty($parsed_drones['item']) )
		{
			$insert = array();
			foreach( $parsed_drones['item'] as $drone_stack => $droneID )
			{
				$insert[] = array(
					'fitID' => $fitID,
					'droneID' => $droneID,
					'droneCount' => $parsed_drones['quantity'][$drone_stack]
				);
			}
			
			self::ensure_db_conn();
			
			$this->db->insert_batch( 'fit_drones', $insert );
		}
	}// add_drones()
	
	private function add_cargo( $fitID, $parsed_cargo )
	{
		// Ensure fit_cargo has PK defined as fitID, cargoID? Or permit duplicate stacks, even of the same count?
		
		if( !empty($parsed_cargo['item']) )
		{
			$insert = array();
			foreach( $parsed_cargo['item'] as $cargo_stack => $cargoID )
			{
				$insert[] = array(
					'fitID' => $fitID,
					'cargoID' => $cargoID,
					'cargoCount' => $parsed_cargo['quantity'][$cargo_stack]
				);
			}
			
			self::ensure_db_conn();
			
			$this->db->insert_batch( 'fit_cargo', $insert );
		}
	}// add_cargo()
	
	
	public function edit_fit( $fitID, $fitRole, $fitDescription, $parseFit, $fitName )
	{
		
		$shipID = $parseFit['shipID'];
		if( $shipID == NULL )
		{
			return FALSE;
		}
		
		self::ensure_db_conn();
		
		$this->db->select( 'shipID' );		
		$this->db->from( 'fit_info' );
		$this->db->where( 'fitID', $fitID );
		$this->db->where( 'status', 'Public' );
		$query = $this->db->get();
		
		//$existing_shipID = $query->result_array();
		if( ($query->num_rows() !== 1) || ($query->row()->shipID !== $shipID) )
		{
			return FALSE;	// Don't permit the fit to change shipType
		}
		
		$this->db->trans_start();
		
		// Purge the old fit details
		$this->db->delete('fit_modules', array('fitID'=>$fitID));
		$this->db->delete('fit_charges', array('fitID'=>$fitID));
		$this->db->delete('fit_drones', array('fitID'=>$fitID));
		$this->db->delete('fit_cargo', array('fitID'=>$fitID));
		// Leave fleet_ships relations?
		
		if( $fitName != NULL )
		{
			$this->db->set( 'fitName', $fitName );
		}
		else
		{
			$this->db->set( 'fitName', $parseFit['name'] );
		}
		$this->db->set( 'fitRole', $fitRole );
		$this->db->set( 'fitDescription', $fitDescription );
		$this->db->set( 'lastEdited', self::SF_now_dtz_db_text() );
		$this->db->where( 'fitID', $fitID );
		$this->db->update( 'fit_info' );
		
		self::add_modules( $fitID, $parseFit['slots'] );
		self::add_charges( $fitID, $parseFit['slots'] );
		self::add_drones( $fitID, $parseFit['drones'] );
		self::add_cargo( $fitID, $parseFit['cargo'] );
		
		$this->db->trans_complete();
		
		return $this->db->trans_status();
	}// edit_fit()
	
	public function make_fit_official( $fitID, $EnactingUserID )
	{
		self::ensure_db_conn();
		
		$this->db->trans_start();
		
		$this->db->set( 'status', 'Official' );
		$this->db->where( 'fitID', $fitID );
		$this->db->where( 'status', 'Public' );
		$updated = ( $this->db->update( 'fit_info' ) && $this->db->affected_rows() == 1 );
		
		$inserted = self::log_status_change( $fitID, TRUE, 'Official', $EnactingUserID );
		
		$this->db->trans_complete();
		
		return ( $this->db->trans_status() === TRUE && $updated && $inserted );
	}// make_fit_official()
	
	public function make_fit_public( $fitID, $EnactingUserID )
	{
		self::ensure_db_conn();
		
		$this->db->trans_start();
		
		$this->db->set( 'status', 'Public' );
		$this->db->where( 'fitID', $fitID );
		$this->db->where( 'status', 'Official' );
		$updated = ( $this->db->update( 'fit_info' ) && $this->db->affected_rows() == 1 );
		
		$inserted = self::log_status_change( $fitID, TRUE, 'Public', $EnactingUserID );
		
		$this->db->trans_complete();
		
		return ( $this->db->trans_status() === TRUE && $updated && $inserted );
	}// make_fit_public()
	
	public function retire_fit( $fitID, $EnactingUserID )
	{
		self::ensure_db_conn();
		
		$this->db->trans_start();
		
		$this->db->set( 'status', 'Retired' );
		$this->db->where( 'fitID', $fitID );
		$this->db->where( 'status', 'Public' );
		$updated = ( $this->db->update( 'fit_info' ) && $this->db->affected_rows() == 1 );
		
		$inserted = self::log_status_change( $fitID, TRUE, 'Retired', $EnactingUserID );
		
		$this->db->trans_complete();
		
		return ( $this->db->trans_status() === TRUE && $updated && $inserted );
	}// retire_fit()
	
	private function log_status_change( $TargetID, $FitNotFleet, $status, $EnactingUserID )
	{
		self::ensure_db_conn();
		
		$this->db->set( 'TargetID', $TargetID );
		$this->db->set( 'FitNotFleet', $FitNotFleet );
		$this->db->set( 'NewStatus', $status );
		$this->db->set( 'EnactingUserID', $EnactingUserID );
		return ( $this->db->insert( 'doctrine_status_log' ) && $this->db->affected_rows() == 1 );
	}// log_status_change()
	
	
	public function get_fit_info( $fitID )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'fitID, fitRole, fitName, fitDescription, shipID, invTypes.typeName as shipName, ("groupID" = '.Eve_SDE_model::STRATEGIC_CRUISER_GROUPID.') as "isStrategicCruiser", fit_info.userID, Username as username, date, status, lastEdited' );		// Why Username as username though?
		$this->db_ro->from( 'fit_info' );
		$this->db_ro->join( 'invTypes', 'fit_info.shipID = invTypes.typeID', 'left' );
		$this->db_ro->join( 'users', 'fit_info.userID = users.UserID' );
		$this->db_ro->where( 'fit_info.fitID', $fitID );
		$query = $this->db_ro->get();
		
		if( $query->num_rows() == 1 )
		{
			return $query->row_array();
		}
		else
		{
			return FALSE;
		}
	}// get_fit_info()
	
	public function get_fit_items( $fitID, $shipID, $isStrategicCruiser )
	{
		$slots = $this->libfit->get_empty_ship_slots( $shipID, $isStrategicCruiser );
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'slotID, moduleID, typeName as moduleName' );		
		$this->db_ro->from( 'fit_modules' );
		$this->db_ro->join( 'invTypes', 'fit_modules.moduleID = invTypes.typeID', 'left' );
		$this->db_ro->where( 'fit_modules.fitID', $fitID );
		$query = $this->db_ro->get();
		$modules = $query->result_array();
		
		foreach( $modules as $m )
		{
			$s = $m['slotID'];
			$slots[$s]['moduleID'] = $m['moduleID'];
			$slots[$s]['moduleName'] = $m['moduleName'];
		}
		
		$this->db_ro->select( 'slotID, chargeID, typeName as chargeName' );		
		$this->db_ro->from( 'fit_charges' );
		$this->db_ro->join( 'invTypes', 'fit_charges.chargeID = invTypes.typeID', 'left' );
		$this->db_ro->where( 'fit_charges.fitID', $fitID );
		$query = $this->db_ro->get();
		$charges = $query->result_array();	// Can be empty
		
		foreach( $charges as $c )
		{
			$s = $c['slotID'];
			$slots[$s]['chargeID'] = $c['chargeID'];
			$slots[$s]['chargeName'] = $c['chargeName'];
		}
		
		$removed = array();
		if( $isStrategicCruiser === 't' )	// Stupid SQL->PHP bool limitation
		{
			$removed = $this->libfit->fix_strategic_cruiser_racks( $slots );
		}
		
		$this->db_ro->select( 'droneID, typeName as droneName, droneCount' );
		$this->db_ro->from( 'fit_drones' );
		$this->db_ro->join( 'invTypes', 'fit_drones.droneID = invTypes.typeID', 'left' );
		$this->db_ro->where( 'fit_drones.fitID', $fitID );
		$query = $this->db_ro->get();
		$drones = $query->result_array();	// Can be empty
		
		$this->db_ro->select( 'cargoID, typeName as cargoName, cargoCount' );
		$this->db_ro->from( 'fit_cargo' );
		$this->db_ro->join( 'invTypes', 'fit_cargo.cargoID = invTypes.typeID', 'left' );
		$this->db_ro->where( 'fit_cargo.fitID', $fitID );
		$query = $this->db_ro->get();
		$cargo = $query->result_array();	// Can be empty
		
		return array(
			'slots' => $slots,
			'removed' => $removed,
			'drones' => $drones,
			'cargo' => $cargo
		);
	}// get_fit_items()
	
	
	public function add_fleet( $fleetName, $fleetType, $fitDescription, $userID, $ships )
	{
		self::ensure_db_conn();
		
		$this->db->trans_start();
		
		$date = self::SF_now_dtz_db_text();
		
		$this->db->set( 'fleetName', $fleetName );
		$this->db->set( 'fleetType', $fleetType );
		$this->db->set( 'fleetDescription', $fitDescription );
		$this->db->set( 'userID', $userID );
		$this->db->set( 'date', $date );
		$this->db->set( 'lastEdited', $date );
		$insert_success = $this->db->insert( 'fleet_info' );
		
		if( $insert_success )
		{
			$fleetID = $this->db->insert_id();
			
			$fleetComposition = array();
			foreach($ships as $fitID)
			{
				$fleetComposition[] = array(	// Auto-incrementing key in fleetComposition[], not ideal.
					'fleetID' => $fleetID,
					'fitID' => $fitID
				);
			}
			$this->db->insert_batch( 'fleet_ships', $fleetComposition );
		}
		
		$this->db->trans_complete();
		
		if( $this->db->trans_status() === TRUE )
		{
			return $fleetID;
		}
		else
		{
			return FALSE;
		}
	}// add_fleet()
	
	public function edit_fleet( $fleetType, $fleetName, $fleetDescription, $fleetID, $ships )
	{
		self::ensure_db_conn();
		
		$this->db->from( 'fleet_info' );
		$this->db->where( 'fleetID', $fleetID );
		$this->db->where( 'status', 'Public' );
		$query = $this->db->get();
		
		if( $query->num_rows() !== 1 )
		{
			return FALSE;
		}
		
		$this->db->trans_start();
		
		$data = array(
			'fleetType' => $fleetType,
			'fleetName' => $fleetName,
			'fleetDescription' => $fleetDescription,
			'lastEdited' => self::SF_now_dtz_db_text()
		);
		$this->db->where( 'fleetID', $fleetID );
		$this->db->update( 'fleet_info', $data );
		
		$this->db->where('fleetID',$fleetID);
		$this->db->delete('fleet_ships');
		
		// Orphaned fleet_ships_ratio values shouldn't be a problem. Keep in case of accidental fit removal from doctrine?
		
		$fleetComposition = array();
		foreach( $ships as $fitID )
		{
			$fleetComposition[] = array(
				'fleetID' => $fleetID,
				'fitID' => $fitID
			);
		}
		$this->db->insert_batch( 'fleet_ships', $fleetComposition );
		
		$this->db->trans_complete();
		
		return $this->db->trans_status();
	}// edit_fleet()
	
	public function edit_fleet_ratios( $fleetID, $ratios )
	{
		self::ensure_db_conn();
		
		$this->db->from( 'fleet_info' );
		$this->db->where( 'fleetID', $fleetID );
		$this->db->where( 'status', 'Public' );
		$query = $this->db->get();
		
		if( $query->num_rows() !== 1 )
		{
			return FALSE;
		}
		
		$this->db->trans_start();
		
		$data = array(
			'lastEdited'=> self::SF_now_dtz_db_text()
		);
		$this->db->where( 'fleetID', $fleetID );
		$this->db->update( 'fleet_info', $data );
		
		$this->db->where( 'fleetID', $fleetID );
		$this->db->delete( 'fleet_ships_ratio' );
		
		//log_message( 'error', print_r( $ratios, TRUE ) );
		
		$fleetRatios = array();
		$first_ratio = NULL;	// Check not all ratios are the same value. If so, don't store any.
		$ratios_differ = FALSE;
		foreach( $ratios as $fitID => $ratio )
		{
			$fleetRatios[] = array(
				'fleetID' => $fleetID,
				'fitID' => $fitID,
				'ratio' => $ratio
			);
			if( $first_ratio === NULL )	// First loop
			{
				$first_ratio = $ratio;
			}
			else
			{
				$ratios_differ |= ($ratio !== $first_ratio);	// OR to carry on any difference in the middle ratios
			}
		}
		//log_message( 'error', print_r( $fleetRatios, TRUE ) );
		if( $ratios_differ )
		{
			$this->db->insert_batch( 'fleet_ships_ratio', $fleetRatios );
		}
		
		$this->db->trans_complete();
		
		return $this->db->trans_status();
		
	}// edit_fleet_ratios()
	
	public function make_fleet_official( $fleetID )
	{
		self::ensure_db_conn();
		
		$this->db->set( 'status', 'Official' );
		$this->db->where( 'fleetID', $fleetID );
		$this->db->where( 'status', 'Public' );
		return( $this->db->update( 'fleet_info' ) && $this->db->affected_rows() == 1 );
	}// make_fleet_official()
	
	public function make_fleet_public( $fleetID )
	{
		self::ensure_db_conn();
		
		$this->db->set( 'status', 'Public' );
		$this->db->where( 'fleetID', $fleetID );
		$this->db->where( 'status', 'Official' );
		return( $this->db->update( 'fleet_info' ) && $this->db->affected_rows() == 1 );
	}// make_fleet_public()
	
	public function retire_fleet( $fleetID )
	{
		self::ensure_db_conn();
		
		$this->db->set( 'status', 'Retired' );
		$this->db->where( 'fleetID', $fleetID );
		$this->db->where( 'status', 'Public' );
		return( $this->db->update( 'fleet_info' ) && $this->db->affected_rows() == 1 );
	}// retire_fleet()
	
	public function get_fleet_info( $fleetID )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select('fleet_info.*, users.Username');
		$this->db_ro->where('fleetID',$fleetID);
		$this->db_ro->join('users','fleet_info.userID = users.UserID');
		$query = $this->db_ro->get('fleet_info');
		
		if( $query->num_rows() == 1 )
		{
			return $query->row_array();
		}
		else
		{
			return FALSE;
		}
	}// get_fleet_info()
	
	public function get_fleet_fitIDs( $fleetID )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select('fleet_ships.fitID, ratio');
		$this->db_ro->from('fleet_ships');
		$this->db_ro->join( 'fit_info', 'fleet_ships.fitID = fit_info.fitID', 'left' );
		$this->db_ro->join( 'fleet_ships_ratio', 'fleet_ships.fleetID = fleet_ships_ratio.fleetID AND fleet_ships.fitID = fleet_ships_ratio.fitID', 'left' );
		$this->db_ro->where( 'fleet_ships.fleetID', $fleetID );
		
		$this->db_ro->order_by( '(ratio * -1) ASC, fitRole ASC, date DESC, fleet_ships.fitID DESC' );	// Can't handle DESC NULLS LAST?
		// For consistent ordering (outside of ratios), should multi-import result in the same date/time
		
		$query = $this->db_ro->get();
		
		return $query->result_array();
	}// get_fleet_fitIDs()
	/*
	public function get_fit_fleetIDs( $fitID )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'fleetID' );
		$this->db_ro->from( 'fleet_ships' );
		$this->db_ro->where( 'fitID', $fitID );
		
		$query = $this->db_ro->get();
		
		return $query->result_array();
	}// get_fit_fleetIDs()
	*/
	public function get_all_fits( $validated_search_fields, $orderType = 'date', $orderSort = 'DESC', $page=0, $pageSize=10 )
	{
		// Don't start building the main query until we've run the sub ones against the Eve_SDE_model, else they get merged.
		if( $this->dynamic_search->subquery_typeName_to_typeID( 'Ship', 'shipName', 'shipID', $validated_search_fields ) === FALSE )
		{
			return array();
		}
		if( $this->dynamic_search->subquery_typeName_to_typeID( 'Module', 'moduleName', 'moduleID', $validated_search_fields) === FALSE )
		{
			return array();
		}
		
		// Another need to alias a field for use in the WHERE clause rather than just the SELECT
		if( array_key_exists( 'createdBy', $validated_search_fields ) )
		{
			$validated_search_fields['CharacterName'] = $validated_search_fields['createdBy'];
			unset( $validated_search_fields['createdBy'] );
		}
		/*
		// Special handling of description text, to support some HTML syntax via strip_tags()
		if( array_key_exists( 'fitDescription', $validated_search_fields ) )
		{
			$validated_search_fields['fitDescription']['value'] = html_entity_decode( $validated_search_fields['fleetDescription']['value'], ENT_QUOTES | ENT_HTML5 );
		}*/
		
		$valid_status = array( 'Public', 'Official' );
		if( array_key_exists( 'alsoRetired', $validated_search_fields ) )
		{
			$valid_status[] = 'Retired';
			unset( $validated_search_fields['alsoRetired'] );
		}
		if( array_key_exists( 'onlyOfficial', $validated_search_fields ) )
		{
			$valid_status = array( 'Official' );
			unset( $validated_search_fields['onlyOfficial'] );
		}
		
		self::ensure_db_ro_conn();
		
		$this->dynamic_search->build_search_conditions( $this->db_ro, $validated_search_fields );
		
		$this->db_ro->where_in( 'status', $valid_status );
		
		//$this->db_ro->distinct();	// Use GROUP BY instead
		$this->db_ro->select( 'fit_info.*, users.CharacterName, invTypes.typeName as shipName' );
		$this->db_ro->from( 'fit_info' );
		$this->db_ro->join( 'fit_modules' ,'fit_info.fitID = fit_modules.fitID', 'left' );
		$this->db_ro->join( 'invTypes', 'fit_info.shipID = invTypes.typeID', 'left' );
		$this->db_ro->join( 'users', 'fit_info.userID = users.UserID', 'left' );
		$this->db_ro->group_by( array('fit_info.fitID', 'users.CharacterName', 'shipName') );
		$this->db_ro->order_by( $orderType, $orderSort );
		$this->db_ro->order_by( 'fitID', 'DESC' );		// For consistent ordering
		$query = $this->db_ro->get( '', $pageSize, $pageSize * $page );
		// If we LIMIT to 1 row extra, we can determine and indicate if there's a next page or not...
		
		return $query->result_array();
	}// get_all_fits()
	
	public function get_all_fleets( $validated_search_fields, $orderType = 'date', $orderSort = 'DESC', $page=0, $pageSize=10 )
	{
		// Don't start building the main query until we've run the sub ones against the Eve_SDE_model, else they get merged.
		if( $this->dynamic_search->subquery_typeName_to_typeID( 'Ship', 'shipName', 'shipID', $validated_search_fields ) === FALSE )
		{
			return array();
		}
		
		// Need to convert ships_fitID back into disambiguous fitID field.
		if( array_key_exists( 'ships_fitID', $validated_search_fields ) )
		{
			$validated_search_fields['fleet_ships.fitID'] = $validated_search_fields['ships_fitID'];
			unset( $validated_search_fields['ships_fitID'] );
		}
		// Another need to alias a field for use in the WHERE clause rather than just the SELECT
		if( array_key_exists( 'createdBy', $validated_search_fields ) )
		{
			$validated_search_fields['CharacterName'] = $validated_search_fields['createdBy'];
			unset( $validated_search_fields['createdBy'] );
		}
		/*
		// Special handling of description text, to support some HTML syntax via strip_tags()
		if( array_key_exists( 'fleetDescription', $validated_search_fields ) )
		{
			$validated_search_fields['fleetDescription']['value'] = html_entity_decode( $validated_search_fields['fleetDescription']['value'], ENT_QUOTES | ENT_HTML5 );
		}*/
		
		self::ensure_db_ro_conn();
		
		$this->dynamic_search->build_search_conditions( $this->db_ro, $validated_search_fields );
		
		//$this->db_ro->distinct();	// Use GROUP BY instead
		$this->db_ro->select( 'fleet_info.fleetID, fleet_info.fleetType, fleet_info.fleetName, fleet_info.userID, fleet_info.date, fleet_info.status, fleet_info.lastEdited, users.CharacterName' );
		$this->db_ro->from( 'fleet_info ');
		$this->db_ro->join( 'fleet_ships', 'fleet_info.fleetID = fleet_ships.fleetID', 'left' );
		$this->db_ro->join( 'fit_info', 'fleet_ships.fitID = fit_info.fitID', 'left' );
		$this->db_ro->join( 'users', 'fleet_info.userID = users.UserID', 'left' );
		$valid_status = array( 'Public', 'Official' );
		$this->db_ro->where_in( 'fleet_info.status', $valid_status );
		$this->db_ro->group_by( array('fleet_info.fleetID', 'users.CharacterName') );
		$this->db_ro->order_by( $orderType, $orderSort );
		$this->db_ro->order_by( 'fleetID', 'DESC' );		// For consistent ordering
		$query = $this->db_ro->get( '', $pageSize, $pageSize * $page );
		// If we LIMIT to 1 row extra, we can determine and indicate if there's a next page or not...
		
		return $query->result_array();
	}// get_all_fleets()
	
	public function get_user_fits( $userID, $alsoOfficial = FALSE, $page = NULL )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'fit_info.*, invTypes.typeName as shipName' );
		$this->db_ro->from( 'fit_info' );
		
		$this->db_ro->group_start();
			$this->db_ro->where( 'userID', $userID );
			$valid_status = array( 'Public', 'Official' );
			$this->db_ro->where_in( 'status', $valid_status );
		$this->db_ro->group_end();
		if( $alsoOfficial )
		{
			$this->db_ro->or_group_start();
				$this->db_ro->where( 'status', 'Official' );
			$this->db_ro->group_end();
		}
		
		$this->db_ro->join( 'invTypes', 'fit_info.shipID = invTypes.typeID', 'left' );
		$this->db_ro->order_by( 'fitRole', 'ASC' );		// For ease of use, and efficient grouping within the views
		$this->db_ro->order_by( 'date', 'DESC' );
		$this->db_ro->order_by( 'fitID', 'DESC' );		// For consistent ordering, should multi-import result in the same date/time
		if( $page !== NULL ) {
			$this->db_ro->limit( self::MANAGE_FITS_PAGESIZE, self::MANAGE_FITS_PAGESIZE * $page );
		}
		$query = $this->db_ro->get();
		
		return $query->result_array();
	}// get_user_fits()
	
	public function get_user_fleets( $userID, $page = NULL )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->from( 'fleet_info' );
		$this->db_ro->where( 'userID', $userID );
		$valid_status = array( 'Public', 'Official' );
		$this->db_ro->where_in( 'status', $valid_status );
		$this->db_ro->order_by( 'date', 'DESC' );
		$this->db_ro->order_by( 'fleetID', 'DESC' );	// For consistent ordering, should multi-import result in the same date/time
		if( $page !== NULL ) {
			$this->db_ro->limit( self::MANAGE_DOCTRINES_PAGESIZE, self::MANAGE_DOCTRINES_PAGESIZE * $page );
		}
		$query = $this->db_ro->get();
		
		return $query->result_array();
	}// get_user_fleets()
	
	public function get_recent_public_changes( $FC_ID = NULL )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'TRUE as "FitNotFleet", fit_info."fitID" as "ID", fit_info."fitName" as "F_Name", fit_info."lastEdited", fit_info."userID", users."CharacterName"', FALSE );
		$this->db_ro->from( 'fit_info' );
		$this->db_ro->join( 'users', 'fit_info.userID = users.UserID', 'left' );
		
		if( $FC_ID !== NULL )
		{
			$this->db_ro->where( 'fit_info.userID', $FC_ID );
			$this->db_ro->where( 'lastEdited >= (current_date - interval \'28 day\')' );
		}
		else
		{
			$this->db_ro->where( 'lastEdited >= (current_date - interval \'7 day\')' );
		}
		
		$this->db_ro->order_by( 'lastEdited', 'DESC' );
		$this->db_ro->order_by( 'fitID', 'DESC' );
		$this->db_ro->limit( 5 );
		
		$fits_query = $this->db_ro->get_compiled_select();
		
		$this->db_ro->select( 'FALSE as "FitNotFleet", fleet_info."fleetID" as "ID", fleet_info."fleetName" as "F_Name", fleet_info."lastEdited", fleet_info."userID", users."CharacterName"', FALSE );
		$this->db_ro->from( 'fleet_info' );
		$this->db_ro->join( 'users', 'fleet_info.userID = users.UserID', 'left' );
		
		if( $FC_ID !== NULL )
		{
			$this->db_ro->where( 'fleet_info.userID', $FC_ID );
			$this->db_ro->where( 'lastEdited >= (current_date - interval \'28 day\')' );
		}
		else
		{
			$this->db_ro->where( 'lastEdited >= (current_date - interval \'7 day\')' );
		}
		
		$this->db_ro->order_by( 'lastEdited', 'DESC' );
		$this->db_ro->order_by( 'fleetID', 'DESC' );
		$this->db_ro->limit( 5 );
		
		$fleets_query = $this->db_ro->get_compiled_select();
		
		$sql = '('. $fits_query .') UNION ('. $fleets_query .') ORDER BY "lastEdited" DESC';
		
		$query = $this->db_ro->query( $sql );
		return $query->result();
	}// get_recent_public_changes()
	
}// Doctrine_model
?>