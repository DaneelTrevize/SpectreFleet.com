<?php
Class Invites_model extends SF_Model {
		
	
	public function get_invite_requests( $scheduledDateTime )
	{
		if( $scheduledDateTime === NULL )
		{
			throw new InvalidArgumentException( '$scheduledDateTime should not be null' );
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'fleetScheduled, ingame_fleet_invites.CharacterID, dateRequested, detectedInFleet, lastInviteSent, invitesSent, response, CharacterName' );
		$this->db_ro->from( 'ingame_fleet_invites' );
		$this->db_ro->join( 'users', 'ingame_fleet_invites.CharacterID = users.CharacterID', 'left' );
		$this->db_ro->join( 'SF_blocked_cache', 'ingame_fleet_invites.CharacterID = SF_blocked_cache.accessorID', 'left' );
		
		$this->db_ro->where( 'fleetScheduled', $scheduledDateTime );
		$this->db_ro->where( '"inviteCancelled" IS NULL', NULL, FALSE );	// Or within the past 2 minutes?
		$this->db_ro->where( '"accessorID" IS NULL', NULL, FALSE );	// ( ... OR accessorType <> ACCESSOR_TYPES['character'] )
		
		$this->db_ro->order_by( 'fleetScheduled', 'ASC' );
		$this->db_ro->order_by( 'CharacterID', 'ASC' );
		
		$query = $this->db_ro->get();
		
		return $query->result_array();
	}// get_invite_requests()
	
	public function get_outstanding_invite_requests( $CharacterID )
	{
		if( $CharacterID === NULL )
		{
			throw new InvalidArgumentException( '$CharacterID should not be null' );
		}
		
		self::ensure_db_ro_conn();
		
		$lastDay = new DateTime( '-1 day', $this->UTC_DTZ );
		$cutoffDate = $this->dtz_to_db_text( $lastDay );	// We don't need all historic requests, just outstanding and recently past
		
		$this->db_ro->select( 'fleetScheduled, ingame_fleet_invites.CharacterID, dateRequested, detectedInFleet, lastInviteSent, CharacterName' );
		$this->db_ro->from( 'ingame_fleet_invites' );
		$this->db_ro->join( 'users', 'ingame_fleet_invites.CharacterID = users.CharacterID', 'left' );
		
		$this->db_ro->where( 'fleetScheduled >=', $cutoffDate );
		$this->db_ro->where( 'ingame_fleet_invites.CharacterID', $CharacterID );
		$this->db_ro->where( '"inviteCancelled" IS NULL', NULL, FALSE );
		
		$this->db_ro->order_by( 'fleetScheduled', 'ASC' );
		$this->db_ro->order_by( 'CharacterID', 'ASC' );
		
		$query = $this->db_ro->get();
		
		return $query->result_array();
	}// get_outstanding_invite_requests()
	/*
	public function get_invite_request( $scheduledDateTime, $CharacterID )
	{
		if( $scheduledDateTime === NULL )
		{
			throw new InvalidArgumentException( '$scheduledDateTime should not be null' );
		}
		if( $CharacterID === NULL )
		{
			throw new InvalidArgumentException( '$CharacterID should not be null' );
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'fleetScheduled, CharacterID, dateRequested, detectedInFleet, lastInviteSent' );
		$this->db_ro->from( 'ingame_fleet_invites' );
		
		$this->db_ro->where( 'fleetScheduled', $scheduledDateTime );
		$this->db_ro->where( 'CharacterID', $CharacterID );
		
		$this->db_ro->order_by( 'fleetScheduled', 'ASC' );
		$this->db_ro->order_by( 'CharacterID', 'ASC' );
		
		$query = $this->db_ro->get();
		
		return $query->row_array();
	}// get_invite_request()
	*/
	
	public function get_fleets_invites( $FC_ID = NULL )
	{
		self::ensure_db_ro_conn();
		
		//$latestDetected = self::get_latest_MOTD_merge();
		
		$this->db_ro->from( 'SF_scheduled_fleets' );
		$this->db_ro->join( 'view_summarised_invite_requests', 'SF_scheduled_fleets.fleetTime = view_summarised_invite_requests.fleetScheduled', 'left' );
		$this->db_ro->join( 'mapSolarSystems', 'SF_scheduled_fleets.locationID = mapSolarSystems.solarSystemID', 'left' );
		$this->db_ro->join( 'fleet_info', 'SF_scheduled_fleets.doctrineID = fleet_info.fleetID', 'left' );
		
		//$this->db_ro->where( 'lastDetected >=', $latestDetected );
		//$this->db_ro->where( 'fleetTime >=', $latestDetected );
		
		$this->db_ro->where( '"fleetTime" >= current_date - interval \'13 hours\'', NULL, FALSE );
		
		if( $FC_ID !== NULL )
		{
			$this->db_ro->select( 'SF_scheduled_fleets.*, mapSolarSystems.solarSystemName, fleet_info.fleetName, view_summarised_invite_requests.*' );
			$this->db_ro->where( 'FC_ID', $FC_ID );
		}
		else
		{
			$this->db_ro->select( 'SF_scheduled_fleets.*, mapSolarSystems.solarSystemName, users.CharacterName, fleet_info.fleetName, view_summarised_invite_requests.*' );
			$this->db_ro->join( 'users', 'SF_scheduled_fleets.FC_ID = users.UserID', 'left' );
		}
		
		$this->db_ro->order_by( 'fleetTime', 'ASC' );
		
		$query = $this->db_ro->get();
		return $query->result();
	}// get_fleets_invites()
	
	public function latest_requests_not_honoured()
	{
		self::ensure_db_ro_conn();
		
		//$latestDetected = self::get_latest_MOTD_merge();
		
		$this->db_ro->select( 'SF_scheduled_fleets.*, users.CharacterName, view_summarised_invite_requests.*' );
		$this->db_ro->from( 'SF_scheduled_fleets' );
		$this->db_ro->join( 'users', 'SF_scheduled_fleets.FC_ID = users.UserID', 'left' );
		$this->db_ro->join( 'view_summarised_invite_requests', 'SF_scheduled_fleets.fleetTime = view_summarised_invite_requests.fleetScheduled', 'left' );
		
		//$this->db_ro->where( 'lastDetected >=', $latestDetected );
		//$this->db_ro->where( 'fleetTime >=', $latestDetected );
		
		$this->db_ro->where( '"fleetTime" >= now() - interval \'16 minutes\'', NULL, FALSE );
		$this->db_ro->where( 'fleetTime < now()' );
		$this->db_ro->where( 'lastDetected >= fleetTime' );
		
		$this->db_ro->where( 'not_cancelled > 0' );
		$this->db_ro->where( 'detected < not_cancelled' );
		$this->db_ro->where( 'invitesSent = 0' );
		
		$this->db_ro->order_by( 'fleetTime', 'ASC' );
		
		$query = $this->db_ro->get();
		return $query->result();
	}// latest_requests_not_honoured()
	
	public function add_invite_request( $scheduledDateTime, $CharacterID )
	{
		self::ensure_db_conn();
		
		// Check whether an invite row already existed and was cancelled
		$this->db->select( 'fleetScheduled, CharacterID' );
		$this->db->from( 'ingame_fleet_invites' );
		
		$this->db->where( 'fleetScheduled ', $scheduledDateTime );
		$this->db->where( 'CharacterID', $CharacterID );
		$query = $this->db->get();
		
		if( $query->num_rows() == 1 )
		{
			$this->db->set( 'inviteCancelled', NULL );
			$this->db->where( 'fleetScheduled', $scheduledDateTime );
			$this->db->where( 'CharacterID', $CharacterID );
			$this->db->where( '"inviteCancelled" IS NOT NULL', NULL, FALSE );
			return ( $this->db->update( 'ingame_fleet_invites' ) && $this->db->affected_rows() == 1 );
		}
		else
		{
			$this->db->set( 'fleetScheduled', $scheduledDateTime );
			$this->db->set( 'CharacterID', $CharacterID );
			// dateRequested = now(), lastInviteSent = NULL
			return ( $this->db->insert( 'ingame_fleet_invites' ) && $this->db->affected_rows() == 1 );
		}
	}// add_invite_request()
	
	public function cancel_invite_request( $scheduledDateTime, $CharacterID )
	{
		self::ensure_db_conn();
		
		$this->db->set( 'inviteCancelled', 'now()' );
		$this->db->where( 'fleetScheduled', $scheduledDateTime );
		$this->db->where( 'CharacterID', $CharacterID );
		$this->db->where( '"inviteCancelled" IS NULL', NULL, FALSE );
		return ( $this->db->update( 'ingame_fleet_invites' ) && $this->db->affected_rows() == 1 );
	}// cancel_invite_request()
	
	public function record_detectedInFleet( $fleetScheduled, $CharacterID, $detectedInFleet )
	{
		self::ensure_db_conn();
		
		$this->db->set( 'detectedInFleet', $detectedInFleet );
		$this->db->where( 'fleetScheduled', $fleetScheduled );
		$this->db->where( 'CharacterID', $CharacterID );
		return ($this->db->update( 'ingame_fleet_invites' ) && $this->db->affected_rows() == 1);
	}// record_detectedInFleet()
	
	public function record_invite_sent( $fleetScheduled, $CharacterID, $lastInviteSent, $response )
	{
		self::ensure_db_conn();
		
		$this->db->set( 'lastInviteSent', $lastInviteSent );
		$this->db->set( 'response', $response );
		$this->db->set( '"invitesSent"', '"invitesSent" + 1', FALSE );
		$this->db->where( 'fleetScheduled', $fleetScheduled );
		$this->db->where( 'CharacterID', $CharacterID );
		return ($this->db->update( 'ingame_fleet_invites' ) && $this->db->affected_rows() == 1);
	}// record_invite_sent()
	
}// Invites_model
?>