<?php
Class CharacterID_model extends SF_Model {
	
	
	public function __construct()
	{
		$this->config->load('ccp_api');
		$this->load->library( 'LibCachedAPI', $this->config->item('oauth_eve'), 'cached_esi' );
	}// __construct()
	
	
	public static function is_valid_character_name( $character_name )
	{
		$NAME_PATTERN = '#^'. '([[:alnum:] \-\']){3,37}' .'$#';
		return ( preg_match( $NAME_PATTERN, $character_name, $matches ) === 1 );
	}// is_valid_character_name()
	
	public function get_character_data( $names )
	{
		log_message( 'debug', 'CharacterID_model: time at start ' .microtime() );
		
		if( empty( $names ) )
		{
			return array();
		}
		
		$ESI_ROOT = $this->config->item( 'rest_esi' )['root'];
		
		$params = array(
			'URL' => $ESI_ROOT.'/v1/universe/ids/',
			'VERB' => 'POST_JSON',
			'query_keys' => $names,
			'API_MAX_ITEMS' => 250,		// 1000
			'RESPONSE_FILTER_FIELD' => 'characters',
			'RESPONSE_NAME_MAP' => array(
				'id' => 'characterID',
				'name' => 'characterName'
			),
			'RESPONSE_KEY_FIELD' => 'name',
			'TABLE_NAME' => 'esi_characters_names'
		);
		
		self::ensure_db_conn();
		
		$results = $this->cached_esi->get_data( $params, $this->db, $this->config->item('public_esi_params') );
		
		if( $results === FALSE )
		{
			log_message( 'error', 'CharacterID_model: Failed to call LibCachedAPI->get_data() for character IDs.' );
			return FALSE;
		}
		else
		{
			$this->cached_esi->log_failures( $results['failures'], TRUE );
			
			return $results['api_results'];
		}
	}// get_character_data()
	
	public function get_character_names( $IDs )
	{
		log_message( 'debug', 'CharacterID_model: time at start ' .microtime() );
		
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
			// verify each result 'category' == 'character'
			'RESPONSE_NAME_MAP' => array(
				'id' => 'characterID',
				'name' => 'characterName'
			),
			'RESPONSE_KEY_FIELD' => 'id',
			'TABLE_NAME' => 'esi_characters_names'
		);
		
		self::ensure_db_conn();
		
		$results = $this->cached_esi->get_data( $params, $this->db, $this->config->item('public_esi_params') );
		
		if( $results === FALSE )
		{
			log_message( 'error', 'CharacterID_model: Failed to call LibCachedAPI->get_data() for character names.' );
			return FALSE;
		}
		else
		{
			$this->cached_esi->log_failures( $results['failures'] );
			
			return $results['api_results'];
		}
	}// get_character_names()
	
}// CharacterID_model
?>