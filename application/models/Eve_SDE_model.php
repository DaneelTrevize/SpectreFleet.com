<?php
class Eve_SDE_model extends SF_Model {
	
	/*
	SELECT "categoryID"
	FROM "invCategories"
	WHERE "published" = TRUE
	AND "categoryName" = 'Ship'
	*/
	const SHIP_CATEGORYID = 6;
	/*
	SELECT "categoryID"
	FROM "invCategories"
	WHERE "published" = TRUE
	AND "categoryName" = 'Drone'
	*/
	const DRONE_CATEGORYID = 18;
	
	//const SHIP_ATTRIBUTE_CATEGORYID_SET = array( 1, 7, 10, 40 );	// Fitting, Miscellaneous, Drones, Hangars & Bays.
	const SHIP_ATTRIBUTEID_SET = array( 12, 13, 14, 283, 422, 1132, 1137, 1271, 1367, 1547 ); // 101, 102, 1154, 1366
	
	const LOW_SLOT_EFFECTID = 11;
	const HIGH_SLOT_EFFECTID = 12;
	const MID_SLOT_EFFECTID = 13;
	const RIG_SLOT_EFFECTID = 2663;
	const SUBSYSTEM_SLOT_EFFECTID = 3772;
	const SLOT_EFFECTID_SET = array( self::LOW_SLOT_EFFECTID, self::HIGH_SLOT_EFFECTID, self::MID_SLOT_EFFECTID, self::RIG_SLOT_EFFECTID, self::SUBSYSTEM_SLOT_EFFECTID );
	
	const STRATEGIC_CRUISER_GROUPID = 963;
	const HIGH_SLOT_MODIFIER_ATTRIBUTEID = 1374;
	const MID_SLOT_MODIFIER_ATTRIBUTEID = 1375;
	const LOW_SLOT_MODIFIER_ATTRIBUTEID = 1376;
	const SLOT_MODIFIER_ATTRIBUTEID_SET = array( self::HIGH_SLOT_MODIFIER_ATTRIBUTEID, self::MID_SLOT_MODIFIER_ATTRIBUTEID, self::LOW_SLOT_MODIFIER_ATTRIBUTEID );
	
	const NAMED_CELESTIALS_TYPEIDS = array( 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 2014, 2015, 2016, 2017, 2063, 3796, 3797, 3798, 3799, 3800, 3801, 3802, 3803, 17774, 30889, 34331, 45030, 45031, 45032, 45033, 45034, 45035, 45036, 45037, 45038, 45039, 45040, 45041, 45042, 45046, 45047 );
	
	
	public function get_types_names( array $typeIDs )
	{
		if( count($typeIDs) == 0 )
		{
			return array();
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'typeID, typeName' );
		$this->db_ro->from( 'invTypes' );
		$this->db_ro->where_in( 'typeID', $typeIDs );
		$query = $this->db_ro->get();
		
		return $query->result_array();
	}// get_types_names()
	
	public function typeName_to_typeID( $typeName, $case_sensitive = TRUE, $full_typeName = TRUE )
	{
		if( $typeName == NULL || $typeName == '' )
		{
			return NULL;
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'typeID' );
		if( $case_sensitive )	// Always also full_typeName
		{
			$this->db_ro->where( 'typeName', $typeName );
		}
		else
		{
			if( $full_typeName )
			{
				$this->db_ro->where( 'LOWER("typeName")', strtolower($typeName) );
			}
			else
			{
				$this->db_ro->like( 'LOWER("typeName")', strtolower($typeName) );
			}
		}
		
		$this->db_ro->where( 'published', TRUE );
		$this->db_ro->order_by( 'typeID', 'ASC' );
		$query = $this->db_ro->get( 'invTypes' );
		
		if( $full_typeName )
		{
			if( $query->num_rows() == 1 )
			{
				return $query->row()->typeID;
			}
			else
			{
				return NULL;
			}
		}
		else
		{
			return array_column( $query->result_array(), 'typeID' );
		}
	}// typeName_to_typeID()
	
	// Refactor into generic category_typeName_to_typeID(), remove hardcoded categoryID?
	public function ship_typeName_to_details( $typeName, $full_typeName = TRUE )	// Case-insensitive
	{
		if( $typeName == NULL || $typeName == '' )
		{
			return NULL;
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'typeID, typeName, ("invTypes"."groupID" = '.self::STRATEGIC_CRUISER_GROUPID.') as "isStrategicCruiser"' );
		$this->db_ro->join( 'invGroups', 'invTypes.groupID = invGroups.groupID' );
		if( $full_typeName )
		{
			$this->db_ro->where( 'LOWER("typeName")', strtolower($typeName) );
		}
		else
		{
			$this->db_ro->like( 'LOWER("typeName")', strtolower($typeName) );
		}
		//$this->db_ro->where( 'invTypes.published', TRUE );
		//$this->db_ro->where( 'invGroups.published', TRUE );
		$this->db_ro->where( 'invGroups.categoryID', self::SHIP_CATEGORYID );
		$this->db_ro->order_by( 'typeID', 'ASC' );
		$query = $this->db_ro->get( 'invTypes' );
		
		return $query->result_array();
	}// ship_typeName_to_details()
	
	public function ship_typeID_to_attributes( $typeID )
	{
		if( $typeID == NULL || !ctype_digit($typeID) )
		{
			return FALSE;
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'dta.typeID, dta.attributeID, COALESCE( dta."valueInt", dta."valueFloat" ) AS value, categoryID, dat.attributeName' );
		$this->db_ro->join( 'dgmAttributeTypes AS dat', 'dta.attributeID = dat.attributeID' );
		
		$this->db_ro->where_in( 'dat.attributeID', self::SHIP_ATTRIBUTEID_SET );	// Uses dat's PK index
		$this->db_ro->where( 'typeID', $typeID );
		$this->db_ro->order_by( 'dat.attributeID', 'ASC' );
		$query = $this->db_ro->get( 'dgmTypeAttributes AS dta' );
		
		return $query->result_array();
	}// ship_typeID_to_attributes()
	
	public function typeID_to_rack( $typeID )
	{
		if( $typeID == NULL || !ctype_digit($typeID) )
		{
			return FALSE;
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'typeID, de.effectID' );
		$this->db_ro->join( 'dgmEffects AS de', 'dte.effectID = de.effectID' );
		
		$this->db_ro->where_in( 'de.effectID', self::SLOT_EFFECTID_SET );
		$this->db_ro->where( 'typeID', $typeID );
		$query = $this->db_ro->get( 'dgmTypeEffects AS dte' );
		
		if( $query->num_rows() == 1 )
		{
				return $query->row()->effectID;
		}
		else
		{
			return FALSE;
		}
	}// typeID_to_rack()
	
	public function subsystems_slot_modifiers( $subsystemIDs )
	{
		if( $subsystemIDs == NULL || !is_array($subsystemIDs) )
		{
			return FALSE;
		}
		if( count($subsystemIDs) == 0 )
		{
			return FALSE;
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'dat.attributeID, COALESCE( dta."valueInt", dta."valueFloat" ) AS value' );
		$this->db_ro->join( 'dgmAttributeTypes AS dat', 'dta.attributeID = dat.attributeID', 'left' );
		
		$this->db_ro->where_in( 'dat.attributeID', self::SLOT_MODIFIER_ATTRIBUTEID_SET );
		$this->db_ro->where_in( 'typeID', $subsystemIDs );
		$query = $this->db_ro->get( 'dgmTypeAttributes AS dta' );
		
		return $query->result_array();
	}// subsystems_slot_modifiers()
	
	public function is_drone( $typeID )	// Refactor to return drone size, for fitting in bay?
	{
		if( $typeID == NULL || !ctype_digit($typeID) )
		{
			return FALSE;
		}
		
		$this->db_ro->select( 'invTypes.groupID' );
		$this->db_ro->join( 'invGroups', 'invTypes.groupID = invGroups.groupID' );
		$this->db_ro->where( 'invTypes.published', TRUE );
		$this->db_ro->where( 'invGroups.published', TRUE );
		$this->db_ro->where( 'invGroups.categoryID', self::DRONE_CATEGORYID );
		$this->db_ro->where( 'invTypes.typeID', $typeID );
		$query = $this->db_ro->get( 'invTypes' );
		return ( $query->num_rows() == 1 );
	}// is_drone()
	
	public function get_solarSystem_by_name( $solarSystemName )
	{
		if( $solarSystemName == NULL || $solarSystemName == '' )
		{
			throw new InvalidArgumentException( '$solarSystemName should not be null or empty.' );
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'solarSystemID, solarSystemName' );
		$this->db_ro->where( 'LOWER("solarSystemName")', strtolower($solarSystemName) );
		$query = $this->db_ro->get( 'mapSolarSystems' );
		if( $query->num_rows() == 1 )
		{
			return $query->row_array();
		}
		else
		{
			return FALSE;
		}
	}// get_solarSystem_by_name()
	
	public function get_solarSystem_names( array $solarSystemIDs )
	{
		if( count($solarSystemIDs) == 0 )
		{
			return array();
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'solarSystemID, solarSystemName' );
		$this->db_ro->from( 'mapSolarSystems' );
		$this->db_ro->where_in( 'solarSystemID', $solarSystemIDs );
		$query = $this->db_ro->get();
		
		return $query->result_array();
	}// get_solarSystem_names()
	
	public function get_faction_data( array $factionIDs )
	{
		if( count($factionIDs) == 0 )
		{
			return array();
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->from( 'chrFactions' );
		$this->db_ro->where_in( 'factionID', $factionIDs );
		$query = $this->db_ro->get();
		
		return $query->result_array();
	}// get_faction_data()
	
}// Eve_SDE_model
?>