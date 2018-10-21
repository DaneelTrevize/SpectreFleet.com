<?php
Class Killmails_model extends SF_Model {
	
	
	const KILLS_OF_THE_MONTH_LIMIT = 5;
	const KILLS_OF_THE_WEEK_LIMIT = 5;
	const KILLS_OF_THE_DAY_LIMIT = 5;
	
	
	public function __construct()
	{
		$this->load->library( 'LibKillmails' );
	}// __construct()
	
	
	public function store_kill( $killmail_id, $killmail_hash )
	{
		$retry_ESI = TRUE;
		$retry_zKb = TRUE;
		
		$db_record = self::get_killmail( $killmail_id );
		if( $db_record !== FALSE )
		{
			// Record exists but has incomplete resolving
			$retry_ESI = $db_record->try_resolve_after != NULL;
			$retry_zKb = $db_record->totalValue == NULL;
		}
		
		$additional_details = $this->libkillmails->resolve_killmail( $killmail_id, $killmail_hash, $retry_ESI, $retry_zKb );
		if( !empty($additional_details) )
		{
			if( $db_record === FALSE )
			{
				if( !self::add_killmail( $killmail_id, $killmail_hash, $additional_details ) )
				{
					log_message( 'error', 'Killmails_model: Failure to add killmail $killmail_id:'.$killmail_id.' $killmail_hash:'.$killmail_hash.' $additional_details:'. print_r($additional_details, TRUE) );
				}
			}
			else
			{
				if( !self::update_killmail( $killmail_id, $additional_details ) )
				{
					log_message( 'error', 'Killmails_model: Failure to update killmail $killmail_id:'.$killmail_id.' $killmail_hash:'.$killmail_hash.' $additional_details:'. print_r($additional_details, TRUE) );
				}
			}
		}
	}// store_kill()
	
	private function add_killmail( $ID, $hash, $additional_details = array() )
	{
		self::ensure_db_conn();
		
		$this->db->trans_start();
		
		$this->db->from( 'SF_killmails' );
		$this->db->where( 'ID', $ID );
		$query = $this->db->get();
		
		if( $query->num_rows() === 0 )
		{
			$this->db->set( 'ID', $ID );
			$this->db->set( 'hash', $hash );
			
			$inserted = ( $this->db->insert( 'SF_killmails', $additional_details ) && $this->db->affected_rows() == 1 );
			
			$this->db->trans_complete();
			
			return ( $this->db->trans_status() === TRUE && $inserted );
		}
		
		$this->db->trans_complete();
		
		return FALSE;
	}// add_killmail()
	
	private function update_killmail( $ID, $additional_details = array() )
	{
		self::ensure_db_conn();
		
		$this->db->trans_start();
		
		$this->db->from( 'SF_killmails' );
		$this->db->where( 'ID', $ID );
		$query = $this->db->get();
		
		if( $query->num_rows() === 1 )
		{
			$this->db->where( 'ID', $ID );
			
			$updated = ( $this->db->update( 'SF_killmails', $additional_details ) && $this->db->affected_rows() == 1 );
			
			$this->db->trans_complete();
			
			return ( $this->db->trans_status() === TRUE && $updated );
		}
		
		$this->db->trans_complete();
		
		return FALSE;
	}// update_killmail()
	
	
	public function get_killmail( $ID )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->from( 'SF_killmails' );
		$this->db_ro->where( 'ID', $ID );
		
		$query = $this->db_ro->get();
		
		if( $query->num_rows() == 1 )
		{
			return $query->row();
		}
		else
		{
			return FALSE;
		}
	}// get_killmail()
	
	public function get_recent_killmails()
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->from( 'SF_killmails' );
		
		$lastWeek = new DateTime( '-7 day', $this->UTC_DTZ );
		$cutoffDate = $this->dtz_to_db_text( $lastWeek );
		$this->db_ro->where( 'time >=', $cutoffDate );

		$this->db_ro->where( 'try_resolve_after', NULL );
		
		$this->db_ro->order_by( 'time', 'DESC' );
		
		$query = $this->db_ro->get();
		return $query->result();
	}// get_recent_killmails()
	
	public function get_top_isk_killmails()
	{
		$top_kills = array();
		$exclude = array();
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->trans_start();
		
		$this->db_ro->from( 'SF_killmails' );
		$this->db_ro->where( 'time >= (current_date - interval \'1 day\')' );
		$this->db_ro->where( 'totalValue IS NOT NULL' );
		$this->db_ro->order_by( 'totalValue', 'DESC' );
		$this->db_ro->order_by( 'time', 'DESC' );
		$this->db_ro->limit( self::KILLS_OF_THE_DAY_LIMIT );
		$query = $this->db_ro->get();
		
		$day_kills = $query->result();
		$top_kills['day'] = $day_kills;
		foreach( $day_kills as $kill )
		{
			$exclude[] = $kill->ID;
		}
		
		$this->db_ro->from( 'SF_killmails' );
		$this->db_ro->where( 'time >= (current_date - interval \'7 day\')' );
		$this->db_ro->where( 'totalValue IS NOT NULL' );
		if( count($exclude) > 0 )
		{
			$this->db_ro->where_not_in( 'ID', $exclude );
		}
		$this->db_ro->order_by( 'totalValue', 'DESC' );
		$this->db_ro->order_by( 'time', 'DESC' );
		$this->db_ro->limit( self::KILLS_OF_THE_WEEK_LIMIT );
		$query = $this->db_ro->get();
		
		$week_kills = $query->result();
		$top_kills['week'] = $week_kills;
		foreach( $week_kills as $kill )
		{
			$exclude[] = $kill->ID;
		}
		
		$this->db_ro->from( 'SF_killmails' );
		$this->db_ro->where( 'time >= (current_date - interval \'28 day\')' );
		$this->db_ro->where( 'totalValue IS NOT NULL' );
		if( count($exclude) > 0 )
		{
			$this->db_ro->where_not_in( 'ID', $exclude );
		}
		$this->db_ro->order_by( 'totalValue', 'DESC' );
		$this->db_ro->order_by( 'time', 'DESC' );
		$this->db_ro->limit( self::KILLS_OF_THE_MONTH_LIMIT );
		$query = $this->db_ro->get();
		
		$month_kills = $query->result();
		$top_kills['month'] = $month_kills;
		
		$this->db_ro->trans_complete();
		
		return $top_kills;
	}// get_top_isk_killmails()
	
	public function get_killmails_for_fleet( $firstDetected, $XUPNumber )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'af.lastDetected, k.*' );
		
		$this->db_ro->from( 'SF_active_fleets AS af' );
		$this->db_ro->join( '"SF_killmails" AS k', 'k.time > af."firstDetected" - interval \'16 minutes\' AND k.time <= af."lastDetected"', 'left', FALSE );
		
		$this->db_ro->where( 'firstDetected', $firstDetected );
		$this->db_ro->where( 'XUPNumber', $XUPNumber );
		
		$this->db_ro->order_by( 'k.time', 'DESC' );
		$this->db_ro->order_by( 'firstDetected', 'DESC' );
		
		$query = $this->db_ro->get();
		
		if( $query->num_rows() >= 1 )
		{
			return $query->result();
		}
		else
		{
			return FALSE;
		}
	}// get_killmails_for_fleet()
	
}// Killmails_model