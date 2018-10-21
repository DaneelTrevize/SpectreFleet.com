<?php
Class Tool_model extends SF_Model {
	
	
	const DSCAN_FILTER_NONE = 0;
	const DSCAN_FILTER_ONGRID = 1;
	const DSCAN_FILTER_OFFGRID = 2;
	
	
	public function get_SF_classes()
	{
		self::ensure_db_ro_conn();
		
		$query = $this->db_ro->get( 'SF_classes' );
		$classes = array();
		foreach( $query->result_array() as $class )
		{
			$classes[$class['classID']] = $class['className'];
		}
		return $classes;
	}// get_SF_classes()
	
	public function add_local_scan( $system, array $characterIDs )
	{
		self::ensure_db_conn();
		
		$this->db->trans_start();
		$inserted_count = 0;
		
		$this->db->set( 'system', $system );
		$insert_success = $this->db->insert( 'local_scan_info' );
		if( $insert_success )
		{
			$scanID = $this->db->insert_id();
			
			if( !empty($characterIDs) )
			{
				$insert = array();
				foreach( $characterIDs as $characterID )
				{
					$insert[] = array(
						'scanID' => $scanID,
						'characterID' => $characterID
					);
				}
				$inserted_count = $this->db->insert_batch( 'local_scan_details', $insert );
			}
		}
		
		$this->db->trans_complete();
		
		if( $this->db->trans_status() === TRUE && $inserted_count === count($characterIDs) )
		{
			return $scanID;
		}
		else
		{
			return FALSE;
		}
	}// add_local_scan()
	
	public function get_local_scan_info( $scanID )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'ID, datetime, system' );
		$this->db_ro->where( 'ID', intval($scanID) );
		$query = $this->db_ro->get( 'local_scan_info' );
		if( $query->num_rows() == 1 )
		{
			return $query->row_array();
		}
		else
		{
			return FALSE;
		}
	}// get_local_scan_info()
	
	public function get_local_scan_details( $scanID )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'scanID, characterID' );
		$this->db_ro->where( 'scanID', intval($scanID) );
		$query = $this->db_ro->get( 'local_scan_details' );
		return $query->result_array();
	}// get_local_scan_details()
	
	
	public function add_dscan( $offgrid_types_count, $ongrid_types_summary, $system = NULL )
	{
		self::ensure_db_conn();
		
		$this->db->trans_start();
		$offgrid_inserted_count = 0;
		$ongrid_inserted_count = 0;
		
		$this->db->set( 'system', $system );
		$insert_success = $this->db->insert( 'tools_dscan_info' );
		if( $insert_success )
		{
			$scanID = $this->db->insert_id();
			
			if( !empty($offgrid_types_count) )
			{
				$insert = array();
				foreach( $offgrid_types_count as $typeID => $type_count )
				{
					$insert[] = array(
						'scanID' => $scanID,
						'typeID' => $typeID,
						'count' => $type_count
					);
				}
				$offgrid_inserted_count = $this->db->insert_batch( 'tools_dscan_offgrid', $insert );
			}
			
			if( !empty($ongrid_types_summary) )
			{
				$insert = array();
				foreach( $ongrid_types_summary as $typeID => $summary )
				{
					$insert[] = array(
						'scanID' => $scanID,
						'typeID' => $typeID,
						'count' => $summary['count'],
						'closest' => $summary['closest'],
						'median' => $summary['median'],
						'furthest' => $summary['furthest']
					);
				}
				$ongrid_inserted_count = $this->db->insert_batch( 'tools_dscan_ongrid', $insert );
			}
		}
		
		$this->db->trans_complete();
		
		if( $this->db->trans_status() === TRUE && $offgrid_inserted_count === count($offgrid_types_count) && $ongrid_inserted_count === count($ongrid_types_summary) )
		{
			return $scanID;
		}
		else
		{
			return FALSE;
		}
	}// add_dscan()
	
	public function get_dscan_info( $scanID )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'scanID, datetime, solarSystemName' );
		$this->db_ro->join( 'mapSolarSystems', 'system = solarSystemID', 'left' );
		$this->db_ro->where( 'scanID', intval($scanID) );
		$query = $this->db_ro->get( 'tools_dscan_info' );
		if( $query->num_rows() == 1 )
		{
			return $query->row_array();
		}
		else
		{
			return FALSE;
		}
	}// get_dscan_info()
	
	public function get_dscan_offgrid( $scanID )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->where( 'scanID', $scanID );
		
		$query = $this->db_ro->get( 'view_dscan_offgrid' );
		return $query->result_array();
	}// get_dscan_offgrid()
	
	public function get_dscan_ongrid( $scanID )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->where( 'scanID', $scanID );
		
		$query = $this->db_ro->get( 'view_dscan_ongrid' );
		return $query->result_array();
	}// get_dscan_ongrid()
	
	
	public function get_old_dscan( $scanID )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->where( 'DScan_ID', intval($scanID) );
		$query = $this->db_ro->get( 'tools_old_dscan' );
		return $query->row_array();
	}// get_old_dscan()
	
}// Tool_model
?>