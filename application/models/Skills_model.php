<?php
Class Skills_model extends SF_Model {
	
	
	public function __construct()
	{
		$this->config->load('ccp_api');
		$this->load->library( 'LibCachedAPI', $this->config->item('oauth_eve'), 'cached_esi' );
	}// __construct()
	
	
	public function get_character_skills( $characterID, $access_token )
	{
		if( $characterID === NULL )
		{
			throw new InvalidArgumentException( '$characterID should not be null' );
		}
		
		$ESI_ROOT = $this->config->item( 'rest_esi' )['root'];
		
		$params = array(
			'URL' => $ESI_ROOT.'/v4/characters/'.$characterID.'/skills/',
			'VERB' => 'GET',
			'PATH_MAP' => array( 'character_id' => $characterID ),
			//'RESPONSE_FILTER_FIELD' => 'skills',
			'RESPONSE_NAME_MAP' => array(
				'character_id' => 'character_id',
				'skill_id' => 'skill_id',
				'active_skill_level' => 'active_skill_level',
				'skillpoints_in_skill' => 'skillpoints_in_skill',
				'trained_skill_level' => 'trained_skill_level'/*,
				'total_sp' => 'total_sp',
				'unallocated_sp' => 'unallocated_sp'*/
			),
            'RESPONSE_FLATTEN_MAP' => array( 'skills' =>
                array(
                    'skill_id' => 'skill_id',
                    'active_skill_level' => 'active_skill_level',
                    'skillpoints_in_skill' => 'skillpoints_in_skill',
                    'trained_skill_level' => 'trained_skill_level'
                )
            ),
			'RESPONSE_KEY_FIELD' => 'character_id',
			'ALTERNATIVE_RESPONSE_KEY' => 'skill_id',
			'TABLE_NAME' => 'esi_characters_skills'
		);
		
		self::ensure_db_conn();
		
		$results = $this->cached_esi->get_data( $params, $this->db, $access_token );
		
		if( $results === FALSE )
		{
			log_message( 'error', 'Skills_model: Failed to call LibCachedAPI->get_data() for character skills.' );
			return FALSE;
		}
		else
		{
			$this->cached_esi->log_failures( $results['failures'] );
			
			return $results['api_results'];
		}
	}// get_character_skills()
	
}// Skills_model
?>