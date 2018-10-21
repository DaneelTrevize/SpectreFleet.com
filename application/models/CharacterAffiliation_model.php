<?php
Class CharacterAffiliation_model extends SF_Model {
	
	
	public function __construct()
	{
		$this->config->load('ccp_api');
		$this->load->library( 'LibCachedAPI', $this->config->item('oauth_eve'), 'cached_esi' );
		$this->load->model('Eve_SDE_model');
	}// __construct()
	
	
	public function get_character_data( $IDs )
	{
		log_message( 'debug', 'CharacterAffiliation_model: time at start ' .microtime() );
		
		$ESI_ROOT = $this->config->item( 'rest_esi' )['root'];
		
		$params = array(
			'URL' => $ESI_ROOT.'/v1/characters/affiliation/',
			'VERB' => 'POST_JSON',
			'query_keys' => $IDs,
			'API_MAX_ITEMS' => 1000,
			'RESPONSE_NAME_MAP' => array(
				'character_id' => 'characterID',
				'corporation_id' => 'corporationID',
				'alliance_id' => 'allianceID',
				'faction_id' => 'factionID'
			),
			'RESPONSE_KEY_FIELD' => 'character_id',
			'TABLE_NAME' => 'esi_characters_affiliation'
		);
		
		self::ensure_db_conn();
		
		$affiliation_results = empty( $IDs ) ? array( 'api_results' => array() ) : $this->cached_esi->get_data( $params, $this->db, $this->config->item('public_esi_params') );
		
		$characters_count = 0;
		$corporation_ids_counts = array();
		$alliance_ids_counts = array();
		$corporation_alliance_map = array();
		$no_alliance_count = 0;
		$faction_ids_counts = array();
		$corporation_faction_map = array();
		$factions_names = array();
		
		if( $affiliation_results === FALSE )
		{
			log_message( 'error', 'CharacterAffiliation_model: Failed to call LibCachedAPI->get_data() for affiliations.' );
		}
		else
		{
			$this->cached_esi->log_failures( $affiliation_results['failures'], TRUE );
			
			foreach( $affiliation_results['api_results'] as $result )
			{
				$characters_count += 1;
				
				$corporation_id = $result['corporation_id'];
				$corporation_id_count = 1;
				if( array_key_exists( $corporation_id, $corporation_ids_counts ) )
				{
					$corporation_id_count += $corporation_ids_counts[$corporation_id];
				}
				$corporation_ids_counts[$corporation_id] = $corporation_id_count;
				
				if( array_key_exists( 'alliance_id', $result ) && $result['alliance_id'] !== NULL )
				{
					$alliance_id = $result['alliance_id'];
					$alliance_id_count = 1;
					if( array_key_exists( $alliance_id, $alliance_ids_counts ) )
					{
						$alliance_id_count += $alliance_ids_counts[$alliance_id];
					}
					$alliance_ids_counts[$alliance_id] = $alliance_id_count;
					
					$corporation_alliance_map[$corporation_id] = $alliance_id;
				}
				else
				{
					$no_alliance_count += 1;
				}
				
				if( array_key_exists( 'faction_id', $result ) && $result['faction_id'] !== NULL )
				{
					$faction_id = $result['faction_id'];
					$faction_id_count = 1;
					if( array_key_exists( $faction_id, $faction_ids_counts ) )
					{
						$faction_id_count += $faction_ids_counts[$faction_id];
					}
					$faction_ids_counts[$faction_id] = $faction_id_count;
					
					$corporation_faction_map[$corporation_id] = $faction_id;
				}
			}
		}
		//log_message( 'error', 'corporation_ids size:' .count($corporation_ids) . ' .Time: ' .microtime() );
		
		$corporations_names = $this->get_corporation_names( array_keys( $corporation_ids_counts ) );
		
		$alliances_names = $this->get_alliance_names( array_keys( $alliance_ids_counts ) );
		
		//log_message( 'error', 'faction_ids_counts:' .print_r($faction_ids_counts, TRUE) );
		$faction_names_data = $this->Eve_SDE_model->get_faction_data( array_keys( $faction_ids_counts ) );
		foreach( $faction_names_data as $row )
		{
			$factions_names[$row['factionID']] = $row;
		}
		
		//log_message( 'error', 'corporation_faction_map:' .print_r($corporation_faction_map, TRUE) );
		return array(
			'characters_count' => $characters_count,
			'corporation_ids_counts' => $corporation_ids_counts,
			'corporations_names' => $corporations_names,
			'alliance_ids_counts' => $alliance_ids_counts,
			'corporation_alliance_map' => $corporation_alliance_map,
			'alliances_names' => $alliances_names,
			'no_alliance_count' => $no_alliance_count,
			'faction_ids_counts' => $faction_ids_counts,
			'corporation_faction_map' => $corporation_faction_map,
			'factions_names' => $factions_names
		);
	}// get_character_data()
	
	public function get_corporation_names( $IDs )
	{
		log_message( 'debug', 'CharacterAffiliation_model: time at start ' .microtime() );
		
		if( empty( $IDs ) )
		{
			return array();
		}
		
		$ESI_ROOT = $this->config->item( 'rest_esi' )['root'];
		
		$params = array(
			'URL' => $ESI_ROOT.'/v2/universe/names/',
			'VERB' => 'POST_JSON',
			'query_keys' => $IDs,
			'API_MAX_ITEMS' => 1000,
			// verify each result 'category' == 'corporation'
			'RESPONSE_NAME_MAP' => array(
				'id' => 'corporationID',
				'name' => 'corporationName'
			),
			'RESPONSE_KEY_FIELD' => 'id',
			'TABLE_NAME' => 'esi_corporations_names'
		);
		
		self::ensure_db_conn();
		
		$results = $this->cached_esi->get_data( $params, $this->db, $this->config->item('public_esi_params') );
		
		if( $results === FALSE )
		{
			log_message( 'error', 'CharacterAffiliation_model: Failed to call LibCachedAPI->get_data() for corporation names.' );
			return FALSE;
		}
		else
		{
			$this->cached_esi->log_failures( $results['failures'] );
			
			return $results['api_results'];
		}
	}// get_corporation_names()
	
	public function get_alliance_names( $IDs )
	{
		log_message( 'debug', 'CharacterAffiliation_model: time at start ' .microtime() );
		
		if( empty( $IDs ) )
		{
			return array();
		}
		
		$ESI_ROOT = $this->config->item( 'rest_esi' )['root'];
		
		$params = array(
			'URL' => $ESI_ROOT.'/v2/universe/names/',
			'VERB' => 'POST_JSON',
			'query_keys' => $IDs,
			'API_MAX_ITEMS' => 1000,
			// verify each result 'category' == 'alliance'
			'RESPONSE_NAME_MAP' => array(
				'id' => 'allianceID',
				'name' => 'allianceName'
			),
			'RESPONSE_KEY_FIELD' => 'id',
			'TABLE_NAME' => 'esi_alliances_names'
		);
		
		self::ensure_db_conn();
		
		$results = $this->cached_esi->get_data( $params, $this->db, $this->config->item('public_esi_params') );
		
		if( $results === FALSE )
		{
			log_message( 'error', 'CharacterAffiliation_model: Failed to call LibCachedAPI->get_data() for alliance names.' );
			return FALSE;
		}
		else
		{
			$this->cached_esi->log_failures( $results['failures'] );
			
			return $results['api_results'];
		}
	}// get_alliance_names()
	
}// CharacterAffiliation_model
?>