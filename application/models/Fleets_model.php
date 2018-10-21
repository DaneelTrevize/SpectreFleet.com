<?php
Class Fleets_model extends SF_Model {
	
	
	public function __construct()
	{
		$this->config->load('ccp_api');
		$this->load->library( 'LibOAuth2', $this->config->item('oauth_eve'), 'oauth_eve' );
		$this->load->library( 'LibCachedAPI', $this->config->item('oauth_eve'), 'cached_esi' );
	}// __construct()
	
	
	public function store_refresh_token( $characterID, $refresh_token )
	{
		if( $characterID === NULL )
		{
			throw new InvalidArgumentException( '$characterID should not be null' );
		}
		if( $refresh_token === NULL )
		{
			throw new InvalidArgumentException( '$refresh_token should not be null' );
		}
		
		self::ensure_db_conn();
		
		// Check whether a token already existed
		$this->db->select( 'characterID, refresh_token' );
		$this->db->from( 'esi_refresh_tokens' );
		$this->db->where( 'characterID', $characterID );
		$query = $this->db->get();
		
		$refresh_token = $this->oauth_eve->encrypt_token( $characterID, $refresh_token );
		
		if( $query->num_rows() == 1 )
		{
			$this->db->set( 'refresh_token', $refresh_token );
			$this->db->where( 'characterID', $characterID );
			return ( $this->db->update( 'esi_refresh_tokens' ) && $this->db->affected_rows() == 1 );
		}
		else
		{
			$this->db->set( 'characterID', $characterID );
			$this->db->set( 'refresh_token', $refresh_token );
			return ( $this->db->insert( 'esi_refresh_tokens' ) && $this->db->affected_rows() == 1 );
		}
	}// store_refresh_token()
	
	public function remove_refresh_token( $characterID )
	{
		if( $characterID === NULL )
		{
			throw new InvalidArgumentException( '$characterID should not be null' );
		}
		
		self::ensure_db_conn();
		
		$this->db->where( 'characterID', $characterID );
		$this->db->delete( 'esi_refresh_tokens' );
		return ( $this->db->affected_rows() == 1 );
	}// remove_refresh_token()
	
	public function get_refresh_token( $characterID	)
	{
		if( $characterID === NULL )
		{
			throw new InvalidArgumentException( '$characterID should not be null' );
		}
		
		self::ensure_db_conn();
		
		$this->db->select( 'characterID, refresh_token' );
		$this->db->from( 'esi_refresh_tokens' );
		$this->db->where( 'characterID', $characterID );
		$query = $this->db->get();
		
		if( $query->num_rows() == 1 )
		{
			$result = $query->row_array();
			return $this->oauth_eve->decrypt_token( $characterID, $result['refresh_token'] );
		}
		else
		{
			return FALSE;
		}
	}// get_refresh_token()
	
	public function log_fc_action( $UserID, $scheduledDateTime, $action )
	{
		self::ensure_db_conn();
		
		$this->db->set( 'FC', $UserID );
		$this->db->set( 'fleetTime', $scheduledDateTime );
		$this->db->set( 'action', $action );
		return ( $this->db->insert( 'fc_action_log' ) && $this->db->affected_rows() == 1 );
	}// log_fc_action()
	
	public function get_character_fleet( $characterID, $access_token )
	{
		if( $characterID === NULL )
		{
			throw new InvalidArgumentException( '$characterID should not be null' );
		}
		
		$ESI_ROOT = $this->config->item( 'rest_esi' )['root'];
		
		$params = array(
			'URL' => $ESI_ROOT.'/v1/characters/'.$characterID.'/fleet/',
			'VERB' => 'GET',
			'PATH_MAP' => array( 'character_id' => $characterID ),
			'CACHEABLE_RESPONSE_CODES' => array( 200, 404 ),
			'EXPIRES_PERIOD_OVERRIDE' => 'PT1M',
			'RESPONSE_NAME_MAP' => array(
				'character_id' => 'character_id',
				'fleet_id' => 'fleet_id',
				'wing_id' => 'wing_id',
				'squad_id' => 'squad_id',
				'role' => 'role'
			),
			'RESPONSE_KEY_FIELD' => 'character_id',
			'TABLE_NAME' => 'esi_characters_fleet'
		);
		
		self::ensure_db_conn();
		
		$results = $this->cached_esi->get_data( $params, $this->db, $access_token );
		
		if( $results === FALSE )
		{
			return FALSE;
		}
		
		$this->cached_esi->log_failures( $results['failures'] );
		
		return $results['api_results'][$characterID];
	}// get_character_fleet()
	
	public function get_fleet( $fleetID, $access_token )
	{
		if( $fleetID === NULL )
		{
			throw new InvalidArgumentException( '$fleetID should not be null' );
		}
		
		$ESI_ROOT = $this->config->item( 'rest_esi' )['root'];
		
		$params = array(
			'URL' => $ESI_ROOT.'/v1/fleets/'.$fleetID.'/',
			'VERB' => 'GET',
			'PATH_MAP' => array( 'fleet_id' => $fleetID ),
			'CACHEABLE_RESPONSE_CODES' => array( 200, 404 ),
			'EXPIRES_PERIOD_OVERRIDE' => 'PT5S',	// Wanted for if any FC resumes being boss of a fleet previously 404d
			'RESPONSE_NAME_MAP' => array(
				'fleet_id' => 'fleet_id',
				'is_free_move' => 'is_free_move',
				'is_registered' => 'is_registered',
				'is_voice_enabled' => 'is_voice_enabled',
				'motd' => 'motd'
			),
			'RESPONSE_KEY_FIELD' => 'fleet_id',
			'TABLE_NAME' => 'esi_fleets'
		);
		
		self::ensure_db_conn();
		
		$results = $this->cached_esi->get_data( $params, $this->db, $access_token );
		
		if( $results === FALSE )
		{
			return FALSE;
		}
		
		$this->cached_esi->log_failures( $results['failures'] );
		
		foreach( $results['failures'] as $failure )
		{
			if( array_key_exists( 'response', $failure ) && $failure['response'] !== FALSE )
			{
				if( $failure['response']['response_code'] == 520 )
				{
					return FALSE;	// This probably won't end well, to try and get members right after
				}
			}
		}
		
		if( count( $results['api_results'] ) === 0 )
		{
			return FALSE;	// Fleet must have some useful details
		}
		
		$fleet = $results['api_results'][$fleetID];
		if( $fleet['is_free_move'] === NULL )	// We pulled a 404 no fleet found from the api/db?
		{
			return FALSE;
		}
		
		return $fleet;
	}// get_fleet()
	
	public function get_fleet_members( $fleetID, $access_token )
	{
		if( $fleetID === NULL )
		{
			throw new InvalidArgumentException( '$fleetID should not be null' );
		}
		
		$ESI_ROOT = $this->config->item( 'rest_esi' )['root'];
		
		$params = array(
			'URL' => $ESI_ROOT.'/v1/fleets/'.$fleetID.'/members/',
			'VERB' => 'GET',
			'PATH_MAP' => array( 'fleet_id' => $fleetID ),
			'RESPONSE_NAME_MAP' => array(
				'fleet_id' => 'fleet_id',
				'character_id' => 'character_id',
				'join_time' => 'join_time',
				'role' => 'role',
				'role_name' => 'role_name',
				'ship_type_id' => 'ship_type_id',
				'solar_system_id' => 'solar_system_id',
				'squad_id' => 'squad_id',
				'station_id' => 'station_id',
				'wing_id' => 'wing_id',
				'takes_fleet_warp' => 'takes_fleet_warp'
			),
			'RESPONSE_KEY_FIELD' => 'fleet_id',
			'ALTERNATIVE_RESPONSE_KEY' => 'character_id',
			'TABLE_NAME' => 'esi_fleet_members'
		);
		
		self::ensure_db_conn();
		
		$results = $this->cached_esi->get_data( $params, $this->db, $access_token );
		
		if( $results === FALSE )
		{
			return FALSE;
		}
		
		$this->cached_esi->log_failures( $results['failures'] );
		
		if( count( $results['api_results'] ) === 0 )
		{
			return FALSE;	// Fleet must have some members
		}
		
		return $results['api_results'];
	}// get_fleet_members()
	
	public function get_fleet_wings_squads( $fleetID, $access_token )
	{
		if( $fleetID === NULL )
		{
			throw new InvalidArgumentException( '$fleetID should not be null' );
		}
		
		$ESI_ROOT = $this->config->item( 'rest_esi' )['root'];
		
		$params = array(
			'URL' => $ESI_ROOT.'/v1/fleets/'.$fleetID.'/wings/',
			'VERB' => 'GET',
			'PATH_MAP' => array( 'fleet_id' => $fleetID ),
			'RESPONSE_NAME_MAP' => array(
				'fleet_id' => 'fleet_id',
				'id' => 'wing_id',
				'name' => 'wing_name',
				'squad_id' => 'squad_id',
				'squad_name' => 'squad_name'
			),
			'RESPONSE_FLATTEN_MAP' => array( 'squads' =>
				array(
					'id' => 'squad_id',
					'name' => 'squad_name'
				)
			),
			'RESPONSE_KEY_FIELD' => 'fleet_id',
			'ALTERNATIVE_RESPONSE_KEY' => 'squad_id',
			'TABLE_NAME' => 'esi_fleet_wings_squads'
		);
		
		self::ensure_db_conn();
		
		$results = $this->cached_esi->get_data( $params, $this->db, $access_token );
		
		if( $results === FALSE )
		{
			return FALSE;
		}
		
		$this->cached_esi->log_failures( $results['failures'] );
		
		// Fleet may have no wings or squads
		
		return self::map_wings_squads_names( $results['api_results'] );
	}// get_fleet_wings_squads()
	
	private static function map_wings_squads_names( $api_results )
	{
		// Map wing & squad IDs to names
		$wing_names = array( '-1' => '' );		// name of FC's position's "squad"
		$squad_names = array( '-1' => '' );		// name of FC's position's "wing"
		foreach( $api_results as $squad )
		{
			$wing_id = $squad['id'];
			if( !array_key_exists( $wing_id, $wing_names ) )
			{
				$wing_names[$wing_id] = $squad['name'];
			}
			$squad_names[$squad['squad_id']] = $squad['squad_name'];
		}
		return array(
			'wing_names' => $wing_names,
			'squad_names' => $squad_names
		);
	}// map_wings_squads_names()
	
	
	public function get_recent_fleets()
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'fleet_id, MAX(expires) AS last_detected, COUNT( character_id ) AS total' );
		$this->db_ro->from( 'esi_fleet_members' );
		$this->db_ro->where( '"expires" >= (now() - interval \'7 days\')', NULL, FALSE );
		
		$this->db_ro->group_by( 'fleet_id' );
		
		$this->db_ro->order_by( 'MAX(expires)', 'DESC' );
		$this->db_ro->order_by( 'fleet_id', 'DESC' );
		
		$query = $this->db_ro->get();
		return $query->result();
	}// get_recent_fleets()
	
	public function get_cached_fleet_members( $fleet_id )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'esi_fleet_members.*, mapSolarSystems.solarSystemName' );
		$this->db_ro->from( 'esi_fleet_members' );
		$this->db_ro->where( 'fleet_id', $fleet_id );
		$this->db_ro->join( 'mapSolarSystems', 'esi_fleet_members.solar_system_id = mapSolarSystems.solarSystemID', 'left' );
		
		$this->db_ro->order_by( 'expires', 'DESC' );
		
		$query = $this->db_ro->get();
		return $query->result();
	}// get_cached_fleet_members()
	
	public function get_cached_fleet_wings_squads( $fleet_id )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'fleet_id, wing_id AS id, wing_name AS name, squad_id, squad_name, expires ' );
		$this->db_ro->from( 'esi_fleet_wings_squads' );
		$this->db_ro->where( 'fleet_id', $fleet_id );
		
		$this->db_ro->order_by( 'expires', 'DESC' );
		
		$query = $this->db_ro->get();
		
		return self::map_wings_squads_names( $query->result_array() );
	}// get_cached_fleet_wings_squads()
	
	public function get_cached_fleet_members_categories( $fleet_id )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->where( 'fleet_id', $fleet_id );
		
		$query = $this->db_ro->get( 'view_fleet_members' );
		return $query->result_array();
	}// get_cached_fleet_members_categories()
	
}// Fleets_model
?>