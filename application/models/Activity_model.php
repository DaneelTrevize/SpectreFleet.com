<?php
Class Activity_model extends SF_Model {
	
	
	const API_15MIN_LOGGING_START_DATE = '2017-03-07 22:55:37+00';
	const API_15MIN_LOGGING_END_DATE = '2018-03-20 11:00:00+00';
	const QUERY_PERIOD_WITH_WIGGLE_ROOM = 'PT16M';
	const CACHE_MINUTES = 5;
	
	
	public function __construct()
	{
		$this->load->model('Channel_model');
	}// __construct()
	
	
	public function get_latest_MOTD_merge()
	{
		/*
		*  This whole system generally assumes that the current set of scheduled fleets is not empty,
		*  that the latest set have the lastDetected time of the lastQueried AND PARSED logged MOTD.
		*/
		self::ensure_db_ro_conn();
		
		$this->db_ro->from( 'SF_scheduled_fleets' );
		$this->db_ro->order_by( 'lastDetected', 'DESC' );
		$this->db_ro->limit( 1 );
		$query = $this->db_ro->get();
		
		if( $query->num_rows() == 1 )
		{
			return $query->row()->lastDetected;
		}
		else
		{
			return FALSE;
		}
	}// get_latest_MOTD_merge()
		
	private function get_active_fleets_before( $cutoffDate = NULL )
	{
		if( $cutoffDate == NULL )
		{
			throw new InvalidArgumentException( '$cutoffDate should not be null.' );
		}
		
		// We want the single most recent previous row per channel. Group-wise max values is a PITA in SQL.
		// Using PostgreSQL-specific DISTINCT ON, could just do UNION of LIMIT 1 for each specific hard-coded XUPNumber subquery.
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'DISTINCT ON ("XUPNumber") "firstDetected", "lastDetected", "XUPNumber", "type", "FC_ID", "locationID", "locationExact", "doctrineID", "additionalDetails"', FALSE );
		$this->db_ro->from( 'SF_active_fleets' );
		
		$this->db_ro->where( 'firstDetected <', $cutoffDate );
		
		$this->db_ro->order_by( 'XUPNumber', 'ASC' );
		$this->db_ro->order_by( 'firstDetected', 'DESC' );
		$query = $this->db_ro->get();
		
		return $query->result();
		
	}// get_active_fleets_before()
	
	public function reparse_MOTDs( $PARSE_SCHEDULED, $PARSE_ACTIVES, $PARSE_KILLS, $cutoffDate = NULL )
	{
		if( $cutoffDate == NULL )
		{
			$cutoffDate = self::API_15MIN_LOGGING_START_DATE;	// Don't default back into old style data logs
		}
		
		//$latestQueried = $this->Channel_model->get_latest_MOTD()->lastQueried;	// Check for failure?
		
		$motds = $this->Channel_model->get_new_MOTDs( $cutoffDate );	// Assumed order of oldest to latest
		
		if( $PARSE_ACTIVES )
		{
			$channels_to_previous_fleets = array();	// The prior set of active fleets, with dynamical channel keys
			$active_fleets_before = self::get_active_fleets_before( $cutoffDate );
			//echo '<pre>'. print_r( $active_fleets_before, TRUE ) .'</pre><br>';
			foreach( $active_fleets_before as $active_fleet )
			{
				// Convert table row to an object matching the MOTD XML-to-JSON parsing
				$previous_fleet = new stdClass;
				$previous_fleet->firstDetected = $active_fleet->firstDetected;
				$previous_fleet->lastDetected = $active_fleet->lastDetected;
				$previous_fleet->XUPNumber = $active_fleet->XUPNumber;
				$previous_fleet->type = $active_fleet->type;
				
				// Un-flatten the row into enough of a fleet object for the later comparisons
				$FC_ID_obj = new stdClass;
				$FC_ID_obj->UserID = $active_fleet->FC_ID;
				$previous_fleet->FC_ID = $FC_ID_obj;
				
				$previous_fleet->location_ID = $active_fleet->locationID;
				$previous_fleet->location_exact = $active_fleet->locationExact;
				$previous_fleet->doctrine = $active_fleet->doctrineID;
				$previous_fleet->remaining_details = $active_fleet->additionalDetails;
				
				// Set up the previous fleet for the channel
				$channel_key = 'a'.$active_fleet->XUPNumber;
				$channels_to_previous_fleets[$channel_key] = $previous_fleet;
			}
			//echo '<pre>'. print_r( $channels_to_previous_fleets, TRUE ) .'</pre><br>';
		}
		
		$distinct_scheduled_fleets = array();	// The distinct fleetTime scheduled fleets from this set of MOTDs
		$distinct_active_fleets = array();		// The distinct firstDetected, XUPNumber(, FC_ID?) active fleets from this set of MOTDs
		$distinct_kills = array();				// The distinct killID kills from this set of MOTDs
		
		foreach( $motds as $motd )
		{
			$parsedData = json_decode( $motd->parsedData );
			if( $parsedData != NULL ) {
				
				if( $PARSE_SCHEDULED )
				{
					foreach( $parsedData->fleets as $scheduled_fleet )
					{
						self::merge_newer_scheduled_fleet( $scheduled_fleet, $distinct_scheduled_fleets, $motd->lastQueried );
					}
				}
				
				if( $PARSE_ACTIVES )
				{
					self::merge_newer_active_fleets( $parsedData->active, $channels_to_previous_fleets, $distinct_active_fleets, $motd->lastQueried );
				}
				
				if( $PARSE_KILLS )
				{
					foreach( $parsedData->kills as $kill )
					{
						$distinct_kills[$kill->ID] = $kill;
					}
				}
				
			}
		}
		
		foreach( $distinct_scheduled_fleets as $scheduled_fleet )
		{
			self::store_scheduled_fleet( $scheduled_fleet );
		}
		
		foreach( $distinct_active_fleets as $XUPNumber => $active_fleets )
		{
			foreach( $active_fleets as $firstDetected => $active_fleet )
			{
				self::store_active_fleet( $active_fleet );
			}
		}
		
		$this->load->model('Killmails_model');
		
		foreach( $distinct_kills as $kill )
		{
			$this->Killmails_model->store_kill( $kill->ID, $kill->hash );
		}
		
		return array(
			'distinct_scheduled_fleets' => $distinct_scheduled_fleets,
			'distinct_active_fleets' => $distinct_active_fleets,
			'distinct_kills' => $distinct_kills
		);
		
	}// reparse_MOTDs()
	
	public function fix_past_scheduled_fleets( $lastQueried )	// We assume the last (fake) API query time matches the latest of all detected scheduled fleets times
	{
		self::ensure_db_conn();
		/*
		UPDATE "SF_scheduled_fleets"
		SET "lastDetected" = "fleetTime"
		WHERE "fleetTime" > '2018-03-20 00:00:00+00'
		AND "fleetTime" > "lastDetected"
		AND "fleetTime" < (
		SELECT "lastDetected"
		FROM "SF_scheduled_fleets"
		ORDER BY "lastDetected" DESC
		LIMIT 1
		)
		*/
		$this->db->set( '"lastDetected"', '"fleetTime"', FALSE );	// Modify fleet to seem to have been scheduled until the time it was due to start
		$this->db->where( 'fleetTime >', self::API_15MIN_LOGGING_END_DATE );	// Only appy to post-API-end data
		$this->db->where( '"fleetTime" > "lastDetected"', NULL, FALSE );		// Only apply to fleets that were yet to pass
		$this->db->where( 'fleetTime <', $lastQueried );		// Only apply to fleets due to occur before this recent update
		
		return $this->db->update( 'SF_scheduled_fleets' );
	}// fix_past_scheduled_fleets()
	
	
	private function merge_newer_scheduled_fleet( $latest_fleet, &$distinct_scheduled_fleets, $lastQueried )
	{
		if( $latest_fleet->FC_ID == NULL )
		{
			log_message( 'error', 'Activity model: For the MOTD logged at: ' .$lastQueried. '. Lacking an identified FC for scheduled fleet->datetime:' . $latest_fleet->datetime );
			return;
		}
		
		$datetime = $latest_fleet->datetime;
		if( array_key_exists($datetime, $distinct_scheduled_fleets) )
		{
			//firstDetected should be carried over
			$distinct_fleet = $distinct_scheduled_fleets[$datetime];
			$latest_fleet->firstDetected = $distinct_fleet->firstDetected;
		}
		else
		{
			$latest_fleet->firstDetected = $lastQueried;
		}
		$latest_fleet->lastDetected = $lastQueried;
		$distinct_scheduled_fleets[$datetime] = $latest_fleet;
	}// merge_newer_scheduled_fleet()
	
	private function store_scheduled_fleet( $scheduled_fleet )
	{
		
		// Should this reject updating details for historic (datetime > 30mins ago?) fleets? At least without a reparsing-historic flag?
		
		if( self::exists_scheduled_fleet( $scheduled_fleet->datetime ) )
		{
			// Should update existing fleets, as the get_new_MOTDs()/get_latest_MOTD() ordering ensures the latest details apply last
			if( !self::update_scheduled_fleet( $scheduled_fleet ) )
			{
				log_message( 'error', 'Activity model: problem updating scheduled fleet '.$scheduled_fleet->datetime );
			}
		}
		else
		{
			if( !self::add_scheduled_fleet( $scheduled_fleet ) )
			{
				log_message( 'error', 'Activity model: problem adding scheduled fleet '.$scheduled_fleet->datetime );
			}
		}
		
	}// store_scheduled_fleet()
	
	private function add_scheduled_fleet( $scheduled_fleet )
	{
		self::ensure_db_conn();
		
		$this->db->trans_start();
		
		$additional_details = array(
			'locationID' => $scheduled_fleet->location_ID == FALSE ? NULL : $scheduled_fleet->location_ID,
			'locationExact' => $scheduled_fleet->location_exact,
			'doctrineID' => $scheduled_fleet->doctrine == FALSE ? NULL : $scheduled_fleet->doctrine,
			'additionalDetails' => $scheduled_fleet->remaining_details
		);
		
		$this->db->from( 'SF_scheduled_fleets' );
		$this->db->where( 'fleetTime', $scheduled_fleet->datetime );
		$query = $this->db->get();
		
		if( $query->num_rows() === 0 )
		{
			$this->db->set( 'fleetTime', $scheduled_fleet->datetime );
			$this->db->set( 'firstDetected', $scheduled_fleet->firstDetected );
			$this->db->set( 'lastDetected', $scheduled_fleet->lastDetected );
			$this->db->set( 'type', $scheduled_fleet->type );
			$this->db->set( 'FC_ID', $scheduled_fleet->FC_ID->UserID );
			
			$inserted = ( $this->db->insert( 'SF_scheduled_fleets', $additional_details ) && $this->db->affected_rows() == 1 );
			
			$this->db->trans_complete();
			
			return ( $this->db->trans_status() === TRUE && $inserted );
		}
		
		$this->db->trans_complete();
		
		return FALSE;
	}// add_scheduled_fleet()
	
	private function update_scheduled_fleet( $scheduled_fleet )
	{
		self::ensure_db_conn();
		
		$this->db->trans_start();
		
		$additional_details = array(
			'locationID' => $scheduled_fleet->location_ID == FALSE ? NULL : $scheduled_fleet->location_ID,
			'locationExact' => $scheduled_fleet->location_exact,
			'doctrineID' => $scheduled_fleet->doctrine == FALSE ? NULL : $scheduled_fleet->doctrine,
			'additionalDetails' => $scheduled_fleet->remaining_details
		);
		
		$this->db->from( 'SF_scheduled_fleets' );
		$this->db->where( 'fleetTime', $scheduled_fleet->datetime );
		$query = $this->db->get();
		
		if( $query->num_rows() === 1 )
		{
			//$this->db->set( 'firstDetected', $scheduled_fleet->firstDetected );	// We might be import data that doesn't go back to firstDetected
			$this->db->set( 'lastDetected', $scheduled_fleet->lastDetected );	// We assume we're importing newer data
			$this->db->set( 'type', $scheduled_fleet->type );
			$this->db->set( 'FC_ID', $scheduled_fleet->FC_ID->UserID );
			
			$this->db->where( 'fleetTime', $scheduled_fleet->datetime );
			
			$updated = ( $this->db->update( 'SF_scheduled_fleets', $additional_details ) && $this->db->affected_rows() == 1 );
			
			$this->db->trans_complete();
			
			return ( $this->db->trans_status() === TRUE && $updated );
		}
		
		$this->db->trans_complete();
		
		return FALSE;
	}// update_scheduled_fleet()
	
	
	private function merge_newer_active_fleets( $latest_fleets, &$channels_to_previous_fleets, &$distinct_active_fleets, $lastQueried )
	{
		// Set up key => value mapping of latest fleets
		$latest_channel_fleets = array();
		foreach( $latest_fleets as $latest_fleet )
		{
			if( !property_exists( $latest_fleet, 'XUPNumber' ) || !property_exists( $latest_fleet, 'FC_ID' ) )
			{
				// Skip handling bad older records?
				continue;
			}
			if( $latest_fleet->FC_ID === FALSE )
			{
				log_message( 'error', 'Activity model: For the MOTD logged at: ' .$lastQueried. '. Lacking an identified FC for active fleet->XUPNumber:' . $latest_fleet->XUPNumber );
				continue;
			}
			
			$channel_key = 'a'.$latest_fleet->XUPNumber;	// Because PHP hates numeric string array keys...
			
			// Assume only one listing per XUP channel per MOTD to be merged
			$latest_channel_fleets[$channel_key] = $latest_fleet;
			
			// Ensure each channel ever referenced has a previous fleet key => value
			if( !array_key_exists($channel_key, $channels_to_previous_fleets) )
			{
				$channels_to_previous_fleets[$channel_key] = NULL;
			}
		}
		
		foreach( $channels_to_previous_fleets as $channel_key => $previous_fleet )
		{
			$XUPNumber = substr( $channel_key, 1 );	// skip the 'a' prefix
			$current_channel_fleet = NULL;
			// Does this channel currently have an active fleet?
			if( array_key_exists($channel_key, $latest_channel_fleets ) )
			{
				$current_channel_fleet = $latest_channel_fleets[$channel_key];
				
				// Check the FC hasn't changed, that it's not a different fleet promptly using the same channel
				if( $previous_fleet === NULL || ($previous_fleet->FC_ID->UserID !== $current_channel_fleet->FC_ID->UserID) )
				{
					// Add the new active fleet to the channel's array
					$current_channel_fleet->firstDetected = $lastQueried;
					$current_channel_fleet->lastDetected = $lastQueried;
					$distinct_active_fleets[$XUPNumber][$current_channel_fleet->firstDetected] = $current_channel_fleet;
				}
				else
				{
					// Same fleet, carry over the firstDetected time, and update that entry in the channel's array
					$current_channel_fleet->firstDetected = $previous_fleet->firstDetected;
					$current_channel_fleet->lastDetected = $lastQueried;
					$distinct_active_fleets[$XUPNumber][$previous_fleet->firstDetected] = $current_channel_fleet;
				}
				
			}/*
			else
			{
				// There isn't currently an active fleet for this channel
				if( $previous_fleet !== NULL )
				{
					// The fleet is over, regardless of whether it was in the most recent previous MOTD log or a prior one.
					log_message( 'error', 'Previous fleet in channel:' .$XUPNumber. ' at ' .$lastQueried. ' was first detected at ' .$previous_fleet->firstDetected. ' and last detected at ' .$previous_fleet->lastDetected );
				}
			}*/
			
			// Reset the previous_fleet to the current one
			$channels_to_previous_fleets[$channel_key] = $current_channel_fleet;
		}
		
	}// merge_newer_active_fleets()
	
	
	private function store_active_fleet( $active_fleet )
	{
		if( self::exists_active_fleet( $active_fleet->firstDetected, $active_fleet->XUPNumber ) )
		{
			// Should update existing fleets, as the get_new_MOTDs()/get_latest_MOTD() ordering ensures the latest details apply last
			if( !self::update_active_fleet( $active_fleet ) )
			{
				log_message( 'error', 'Activity model: problem updating active fleet '.$active_fleet->XUPNumber );
			}
		}
		else
		{
			if( !self::add_active_fleet( $active_fleet ) )
			{
				log_message( 'error', 'Activity model: problem adding active fleet '.$active_fleet->XUPNumber );
			}
		}
		
	}// store_active_fleet()
	
	private function add_active_fleet( $active_fleet )
	{
		self::ensure_db_conn();
		
		$this->db->trans_start();
		
		$additional_details = array(
			'locationID' => $active_fleet->location_ID == FALSE ? NULL : $active_fleet->location_ID,
			'locationExact' => $active_fleet->location_exact,
			'doctrineID' => $active_fleet->doctrine == FALSE ? NULL : $active_fleet->doctrine,
			'additionalDetails' => $active_fleet->remaining_details
		);
		
		$this->db->from( 'SF_active_fleets' );
		$this->db->where( 'firstDetected', $active_fleet->firstDetected );
		$this->db->where( 'XUPNumber', $active_fleet->XUPNumber );
		$query = $this->db->get();
		
		if( $query->num_rows() === 0 )
		{
			$this->db->set( 'firstDetected', $active_fleet->firstDetected );
			$this->db->set( 'lastDetected', $active_fleet->lastDetected );
			$this->db->set( 'XUPNumber', $active_fleet->XUPNumber );
			$this->db->set( 'type', $active_fleet->type );
			$this->db->set( 'FC_ID', $active_fleet->FC_ID->UserID );
			
			$inserted = ( $this->db->insert( 'SF_active_fleets', $additional_details ) && $this->db->affected_rows() == 1 );
			
			$this->db->trans_complete();
			
			return ( $this->db->trans_status() === TRUE && $inserted );
		}
		
		$this->db->trans_complete();
		
		return FALSE;
	}// add_active_fleet()
	
	private function update_active_fleet( $active_fleet )
	{
		self::ensure_db_conn();
		
		$this->db->trans_start();
		
		$additional_details = array(
			'locationID' => $active_fleet->location_ID == FALSE ? NULL : $active_fleet->location_ID,
			'locationExact' => $active_fleet->location_exact,
			'doctrineID' => $active_fleet->doctrine == FALSE ? NULL : $active_fleet->doctrine,
			'additionalDetails' => $active_fleet->remaining_details
		);
		
		$this->db->from( 'SF_active_fleets' );
		$this->db->where( 'firstDetected', $active_fleet->firstDetected );
		$this->db->where( 'XUPNumber', $active_fleet->XUPNumber );
		$query = $this->db->get();
		
		if( $query->num_rows() === 1 )
		{
			$this->db->set( 'lastDetected', $active_fleet->lastDetected );	// We assume we're importing newer data
			$this->db->set( 'type', $active_fleet->type );
			$this->db->set( 'FC_ID', $active_fleet->FC_ID->UserID );
			
			$this->db->where( 'firstDetected', $active_fleet->firstDetected );
			$this->db->where( 'XUPNumber', $active_fleet->XUPNumber );
			
			$updated = ( $this->db->update( 'SF_active_fleets', $additional_details ) && $this->db->affected_rows() == 1 );
			
			$this->db->trans_complete();
			
			return ( $this->db->trans_status() === TRUE && $updated );
		}
		
		$this->db->trans_complete();
		
		return FALSE;
	}// update_active_fleet()
	
	
	private function exists_scheduled_fleet( $fleetTime )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->from( 'SF_scheduled_fleets' );
		$this->db_ro->where( 'fleetTime', $fleetTime );
		
		$query = $this->db_ro->get();
		
		return( $query->num_rows() == 1 );
	}// exists_scheduled_fleet()
	
	private function exists_active_fleet( $firstDetected, $XUPNumber )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->from( 'SF_active_fleets' );
		$this->db_ro->where( 'firstDetected', $firstDetected );
		$this->db_ro->where( 'XUPNumber', $XUPNumber );
		
		$query = $this->db_ro->get();
		
		return( $query->num_rows() == 1 );
	}// exists_scheduled_fleet()
	
	
	public function get_active_fleet( $firstDetected, $XUPNumber )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( '"af".*, "j"."fleetTime", "mapSolarSystems"."solarSystemName", users."CharacterName", users."CharacterID", ABS(EXTRACT(EPOCH FROM ("fleetTime" - "af"."firstDetected"))) AS "i"', FALSE );
		
		$this->db_ro->from( 'SF_active_fleets AS af' );
		$this->db_ro->join( 'view_interlinked_recent_fleets AS j', 'af.firstDetected = j.firstDetected AND af.FC_ID = j.af_FC_ID', 'left' );
		$this->db_ro->join( 'mapSolarSystems', 'af.locationID = mapSolarSystems.solarSystemID', 'left' );
		$this->db_ro->join( 'users', 'af.FC_ID = users.UserID', 'left' );
		$this->db_ro->where( 'af.firstDetected', $firstDetected );
		$this->db_ro->where( 'af.XUPNumber', $XUPNumber );
		
		$this->db_ro->order_by( 'i', 'ASC' );
		$this->db_ro->limit( 1 );
		
		$query = $this->db_ro->get();
		
		if( $query->num_rows() == 1 )
		{
			return $query->row_array();
		}
		else
		{
			return FALSE;
		}
	}// get_active_fleet()
	
	public function get_latest_active_fleets()
	{
		$latestDetected = self::get_latest_MOTD_merge();
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'SF_active_fleets.*, mapSolarSystems.solarSystemName, users.CharacterName, fleet_info.fleetName' );
		$this->db_ro->from( 'SF_active_fleets' );
		$this->db_ro->join( 'mapSolarSystems', 'SF_active_fleets.locationID = mapSolarSystems.solarSystemID', 'left' );
		$this->db_ro->join( 'users', 'SF_active_fleets.FC_ID = users.UserID', 'left' );
		$this->db_ro->join( 'fleet_info', 'SF_active_fleets.doctrineID = fleet_info.fleetID', 'left' );
		
		$this->db_ro->where( 'lastDetected', $latestDetected );
		
		$this->db_ro->order_by( 'XUPNumber', 'ASC' );
		
		$query = $this->db_ro->get();
		return $query->result();
	}// get_latest_active_fleets()
	
	public function get_recent_active_fleets( $FC_ID = NULL )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'SF_active_fleets.*, mapSolarSystems.solarSystemName, users.CharacterName, fleet_info.fleetName' );
		$this->db_ro->from( 'SF_active_fleets' );
		$this->db_ro->join( 'mapSolarSystems', 'SF_active_fleets.locationID = mapSolarSystems.solarSystemID', 'left' );
		$this->db_ro->join( 'users', 'SF_active_fleets.FC_ID = users.UserID', 'left' );
		$this->db_ro->join( 'fleet_info', 'SF_active_fleets.doctrineID = fleet_info.fleetID', 'left' );
		
		
		if( $FC_ID !== NULL )
		{
			$this->db_ro->where( 'FC_ID', $FC_ID );
			$this->db_ro->where( 'lastDetected >= current_date - interval \'30 days\'' );
		}
		else
		{
			$this->db_ro->where( 'lastDetected >= current_date - interval \'7 days\'' );
		}
		
		$this->db_ro->order_by( 'lastDetected', 'DESC' );
		
		$query = $this->db_ro->get();
		return $query->result();
	}// get_recent_active_fleets()
	
	
	public function get_past_interlinked_fleets()
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( '*, case when "fleetTime" is null then -(current_date - date ("firstDetected")) else -(current_date - date ("fleetTime")) end AS "Day"', FALSE );
		$this->db_ro->from( 'view_interlinked_recent_fleets' );
		
		$query = $this->db_ro->get();
		return $query->result();
	}// get_past_interlinked_fleets()
	
	
	public function get_scheduled_fleet( $fleetTime )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( '"SF_scheduled_fleets".*, "j"."firstDetected" AS "af_firstDetected", "j"."XUPNumber", "mapSolarSystems"."solarSystemName", "users"."CharacterName", "users"."CharacterID", ABS(EXTRACT(EPOCH FROM ("SF_scheduled_fleets"."fleetTime" - "j"."firstDetected"))) AS "i"', FALSE );
		
		$this->db_ro->from( 'SF_scheduled_fleets' );
		$this->db_ro->join( 'view_interlinked_recent_fleets AS j', 'SF_scheduled_fleets.fleetTime = j.fleetTime', 'left' );
		$this->db_ro->join( 'mapSolarSystems', 'SF_scheduled_fleets.locationID = mapSolarSystems.solarSystemID', 'left' );
		$this->db_ro->join( 'users', 'SF_scheduled_fleets.FC_ID = users.UserID', 'left' );
		
		$this->db_ro->where( 'SF_scheduled_fleets.fleetTime', $fleetTime );
		
		$this->db_ro->order_by( 'i', 'ASC' );
		$this->db_ro->limit( 1 );
		
		$query = $this->db_ro->get();
		
		if( $query->num_rows() == 1 )
		{
			return $query->row_array();
		}
		else
		{
			return FALSE;
		}
	}// get_scheduled_fleet()
	
	public function get_recent_scheduled_fleets( $FC_ID = NULL )
	{
		$latestDetected = self::get_latest_MOTD_merge();
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->from( 'SF_scheduled_fleets' );
		$this->db_ro->join( 'mapSolarSystems', 'SF_scheduled_fleets.locationID = mapSolarSystems.solarSystemID', 'left' );
		$this->db_ro->join( 'fleet_info', 'SF_scheduled_fleets.doctrineID = fleet_info.fleetID', 'left' );
		
		$this->db_ro->where( '"lastDetected" >= ("fleetTime" - interval \'15 minutes\')', NULL, FALSE );
		$this->db_ro->where( 'fleetTime <', $latestDetected );
		
		$this->db_ro->where( 'fleetTime >= (current_date - interval \'28 day\')' );
		
		if( $FC_ID !== NULL )
		{
			$this->db_ro->select( 'SF_scheduled_fleets.*, mapSolarSystems.solarSystemName, fleet_info.fleetName' );
			$this->db_ro->where( 'FC_ID', $FC_ID );
		}
		else
		{
			$this->db_ro->select( 'SF_scheduled_fleets.*, mapSolarSystems.solarSystemName, users.CharacterName, fleet_info.fleetName' );
			$this->db_ro->join( 'users', 'SF_scheduled_fleets.FC_ID = users.UserID', 'left' );
		}
		
		$this->db_ro->order_by( 'fleetTime', 'DESC' );
		
		$query = $this->db_ro->get();
		return $query->result();
	}// get_recent_scheduled_fleets()
		
	public function get_recent_favoured_scheduled_doctrines( $FC_ID = NULL, $limit = NULL )
	{
		$latestDetected = self::get_latest_MOTD_merge();
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( '"doctrineID", COUNT("doctrineID") AS "Uses", MAX("fleetTime") AS "LatestUse"', FALSE );
		//$this->db_ro->from( 'SF_scheduled_fleets' );
		$this->db_ro->group_start();
		$this->db_ro->where( '"lastDetected" >= ("fleetTime" - interval \'15 minutes\')', NULL, FALSE );	// Remained listed
		$this->db_ro->or_where( 'fleetTime >=', $latestDetected );	// Is still listed
		$this->db_ro->group_end();
		$this->db_ro->where( 'fleetTime >= (current_date - interval \'28 day\')' );
		$this->db_ro->where( 'doctrineID IS NOT NULL' );
		
		if( $FC_ID !== NULL )
		{
			$this->db_ro->where( 'FC_ID', $FC_ID );
		}
		
		$this->db_ro->group_by( 'doctrineID' );
		$this->db_ro->order_by( 'MAX("fleetTime")', 'DESC', FALSE );
		
		$counts_sql = $this->db_ro->get_compiled_select( 'SF_scheduled_fleets' );
		
		
		$this->db_ro->select( 'counts.doctrineID, Uses, LatestUse, fleetName' );
		
		$this->db_ro->from( '('.$counts_sql.') as counts' );
		$this->db_ro->join( 'fleet_info', 'counts.doctrineID = fleet_info.fleetID', 'left' );
		
		$this->db_ro->order_by( 'Uses', 'DESC' );
		$this->db_ro->order_by( 'LatestUse', 'DESC' );
		if( $limit != NULL )
		{
			$this->db_ro->limit( $limit );
		}
		
		$query = $this->db_ro->get();
		return $query->result();
	}// get_recent_favoured_scheduled_doctrines()
		
	public function get_recent_favoured_scheduled_locations( $FC_ID = NULL, $limit = NULL )
	{
		$latestDetected = self::get_latest_MOTD_merge();
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( '"locationID", COUNT("locationID") AS "Uses", MAX("fleetTime") AS "LatestUse"', FALSE );
		//$this->db_ro->from( 'SF_scheduled_fleets' );
		$this->db_ro->group_start();
		$this->db_ro->where( '"lastDetected" >= ("fleetTime" - interval \'15 minutes\')', NULL, FALSE );	// Remained listed
		$this->db_ro->or_where( 'fleetTime >=', $latestDetected );	// Is still listed
		$this->db_ro->group_end();
		$this->db_ro->where( 'fleetTime >= (current_date - interval \'28 day\')' );
		$this->db_ro->where( 'locationID IS NOT NULL' );
		
		if( $FC_ID !== NULL )
		{
			$this->db_ro->where( 'FC_ID', $FC_ID );
		}
		
		$this->db_ro->group_by( 'locationID' );
		$this->db_ro->order_by( 'MAX("fleetTime")', 'DESC', FALSE );
		
		$counts_sql = $this->db_ro->get_compiled_select( 'SF_scheduled_fleets' );
		
		
		$this->db_ro->select( 'counts.locationID, Uses, LatestUse, solarSystemName' );
		
		$this->db_ro->from( '('.$counts_sql.') as counts' );
		$this->db_ro->join( 'mapSolarSystems', 'counts.locationID = mapSolarSystems.solarSystemID', 'left' );
		
		$this->db_ro->order_by( 'Uses', 'DESC' );
		$this->db_ro->order_by( 'LatestUse', 'DESC' );
		if( $limit != NULL )
		{
			$this->db_ro->limit( $limit );
		}
		
		$query = $this->db_ro->get();
		return $query->result();
	}// get_recent_favoured_scheduled_locations()
		
	public function get_recent_favoured_scheduled_timezones( $FC_ID = NULL )
	{
		$latestDetected = self::get_latest_MOTD_merge();
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'CASE
WHEN ("fleetTime"::time < \'4:00\'::time) THEN \'AMER\'
	WHEN ("fleetTime"::time >= \'4:00\'::time AND "fleetTime"::time < \'12:00\'::time) THEN \'APAC\'
	WHEN ("fleetTime"::time >= \'12:00\'::time AND "fleetTime"::time < \'20:00\'::time) THEN \'EMEA\'
	WHEN ("fleetTime"::time >= \'20:00\'::time) THEN \'AMER\'
END AS "TZ", "fleetTime", "FC_ID"', FALSE );
		//$this->db_ro->from( 'SF_scheduled_fleets' );
		$this->db_ro->group_start();
		$this->db_ro->where( '"lastDetected" >= ("fleetTime" - interval \'15 minutes\')', NULL, FALSE );	// Remained listed
		$this->db_ro->or_where( 'fleetTime >=', $latestDetected );	// Is still listed
		$this->db_ro->group_end();
		$this->db_ro->where( 'fleetTime >= (current_date - interval \'28 day\')' );
		
		if( $FC_ID !== NULL )
		{
			$this->db_ro->where( 'FC_ID', $FC_ID );
		}
		
		$this->db_ro->order_by( '"fleetTime"::time', 'ASC', FALSE );
		
		$counts_sql = $this->db_ro->get_compiled_select( 'SF_scheduled_fleets' );
		
		
		$this->db_ro->select( '"TZ", count("TZ")', FALSE );
		
		$this->db_ro->from( '('.$counts_sql.') as TZs' );
		
		$this->db_ro->group_by( 'TZ' );
		$this->db_ro->order_by( 'count("TZ")', 'DESC', FALSE );
		
		$query = $this->db_ro->get();
		return $query->result();
	}// get_recent_favoured_scheduled_timezones()
	
		
	public function get_cancelled_scheduled_fleets( $FC_ID = NULL )
	{
		$latestDetected = self::get_latest_MOTD_merge();
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->from( 'SF_scheduled_fleets' );
		$this->db_ro->join( 'mapSolarSystems', 'SF_scheduled_fleets.locationID = mapSolarSystems.solarSystemID', 'left' );
		$this->db_ro->join( 'fleet_info', 'SF_scheduled_fleets.doctrineID = fleet_info.fleetID', 'left' );
		
		$this->db_ro->where( '"lastDetected" < ("fleetTime" - interval \'15 minutes\')', NULL, FALSE );
		$this->db_ro->where( 'fleetTime <', $latestDetected );
		
		$this->db_ro->where( 'fleetTime >= (current_date - interval \'28 day\')' );
		
		if( $FC_ID !== NULL )
		{
			$this->db_ro->select( 'SF_scheduled_fleets.*, mapSolarSystems.solarSystemName, fleet_info.fleetName' );
			$this->db_ro->where( 'FC_ID', $FC_ID );
		}
		else
		{
			$this->db_ro->select( 'SF_scheduled_fleets.*, mapSolarSystems.solarSystemName, users.CharacterName, fleet_info.fleetName' );
			$this->db_ro->join( 'users', 'SF_scheduled_fleets.FC_ID = users.UserID', 'left' );
		}
		
		
		$this->db_ro->order_by( 'fleetTime', 'DESC' );
		
		$query = $this->db_ro->get();
		return $query->result();
	}// get_cancelled_scheduled_fleets()
		
	public function get_future_fleets( $FC_ID = NULL, $focused_on_present = FALSE )
	{
		$latestDetected = self::get_latest_MOTD_merge();
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->from( 'SF_scheduled_fleets' );
		$this->db_ro->join( 'mapSolarSystems', 'SF_scheduled_fleets.locationID = mapSolarSystems.solarSystemID', 'left' );
		$this->db_ro->join( 'fleet_info', 'SF_scheduled_fleets.doctrineID = fleet_info.fleetID', 'left' );
		
		$this->db_ro->where( 'lastDetected >=', $latestDetected );
		if( $focused_on_present )
		{
			// Recently past hours and upcoming few days
			$this->db_ro->where( '"fleetTime" >= (now() - interval \'12 hours\')', NULL, FALSE );
			$this->db_ro->where( '"fleetTime" < (now() + interval \'7 days\')', NULL, FALSE );
		}
		else
		{
			// All fleets that haven't yet occured, rolling barrier of now(), rather than $latestDetected
			$this->db_ro->where( '"fleetTime" >= now()', NULL, FALSE );
		}
		
		if( $FC_ID !== NULL )
		{
			$this->db_ro->select( 'SF_scheduled_fleets.*, mapSolarSystems.solarSystemName, fleet_info.fleetName' );
			$this->db_ro->where( 'FC_ID', $FC_ID );
		}
		else
		{
			$this->db_ro->select( 'SF_scheduled_fleets.*, mapSolarSystems.solarSystemName, users.CharacterName, users.CharacterID, fleet_info.fleetName' );
			$this->db_ro->join( 'users', 'SF_scheduled_fleets.FC_ID = users.UserID', 'left' );
		}
		
		$this->db_ro->order_by( 'fleetTime', 'DESC' );
		
		$query = $this->db_ro->get();
		return $query->result();
	}// get_future_fleets()
		
	public function get_cancelled_future_fleets( $FC_ID = NULL )
	{
		$latestDetected = self::get_latest_MOTD_merge();
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->from( 'SF_scheduled_fleets' );
		$this->db_ro->join( 'mapSolarSystems', 'SF_scheduled_fleets.locationID = mapSolarSystems.solarSystemID', 'left' );
		$this->db_ro->join( 'fleet_info', 'SF_scheduled_fleets.doctrineID = fleet_info.fleetID', 'left' );
		
		$this->db_ro->where( 'lastDetected <', $latestDetected );
		$this->db_ro->where( 'fleetTime >=', $latestDetected );
		
		if( $FC_ID !== NULL )
		{
			$this->db_ro->select( 'SF_scheduled_fleets.*, mapSolarSystems.solarSystemName, fleet_info.fleetName' );
			$this->db_ro->where( 'FC_ID', $FC_ID );
		}
		else
		{
			$this->db_ro->select( 'SF_scheduled_fleets.*, mapSolarSystems.solarSystemName, users.CharacterName, fleet_info.fleetName' );
			$this->db_ro->join( 'users', 'SF_scheduled_fleets.FC_ID = users.UserID', 'left' );
		}
		
		$this->db_ro->order_by( 'fleetTime', 'DESC' );
		
		$query = $this->db_ro->get();
		return $query->result();
	}// get_cancelled_future_fleets()
	
}// Activity_model
?>