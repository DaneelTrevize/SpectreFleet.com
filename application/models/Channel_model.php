<?php
class Channel_model extends SF_Model {
	
	/*
	*	For each channel, we can identify them by displayName or channelID.
	*	Each channel should have a characterID, refreshToken pair with which to query the API for details.
	*	Per channel, we should track the API time when we queried it, and the expires time.
	*
	*	Accounting for 1 minute variation between the local/UTC time and the API current time, If the current time
	*	is calculated to be greater than the expires time, we should query the API. Else, use the locally cached response.
	*
	*	We shouldn't update all channel caches for channels that we receive a response for, as a non-favoured
	*	authentication token may have different rights and result in undesired differences in the response data?
	*/
	
	const SPECTREFLEET_CHANNEL_NAMES = array( 'SF Spectre Fleet', 'XUP~1', 'XUP~2', 'XUP~3' );
	const DATETIME_DISPLAY_FORMAT = 'F jS l H:i';
	
	const ACCESSOR_TYPES = array(
		'character' => 0,
		'corporation' => 1,
		'alliance' => 2
	);
	
	
	public function add_spectre_MOTD( $parsedData, $EnactingUserID )
	{
		if( $parsedData == NULL )
		{
			throw new InvalidArgumentException( '$parsedData should not be null.' );
		}
		if( $EnactingUserID == NULL )
		{
			throw new InvalidArgumentException( '$EnactingUserID should not be null.' );
		}
		
		self::ensure_db_conn();
		
		$queried = self::SF_now_dtz_db_text();
		
		$parsedData_JSON = json_encode( $parsedData );
		
		$this->db->trans_start();
		
		$this->db->set( 'lastQueried', $queried );
		$this->db->set( 'parsedData', $parsedData_JSON );
		
		$inserted = ( $this->db->insert( 'MOTD_log' ) && $this->db->affected_rows() == 1 );
		
		$this->load->model('User_model');
		$logged = $this->User_model->log_staff_action( $EnactingUserID, 'MOTD updated', $queried );
		
		$this->db->trans_complete();
		
		if( $this->db->trans_status() === TRUE && $inserted && $logged )
		{
			return $queried;
		}
		//log_message( 'error', 'Channel model: problem recording MOTD '.$queried .' '. $parsedData_JSON );
		return FALSE;
	}// add_spectre_MOTD()
	
	public function get_latest_MOTD()
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'lastQueried, parsedData' );
		$this->db_ro->from( 'MOTD_log' );
		$this->db_ro->order_by( 'lastQueried', 'DESC' );
		$this->db_ro->limit( 1 );
		$query = $this->db_ro->get();
		
		if( $query->num_rows() == 1 )
		{
			$row = $query->row();
			return array(
				'data' => json_decode( $row->parsedData, TRUE ),
				'lastQueried' => $row->lastQueried
			);
		}
		else
		{
			return FALSE;
		}
	}// get_latest_MOTD()
	
	public function get_new_MOTDs( $cutoffDate = NULL )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'lastQueried, parsedData' );
		$this->db_ro->from( 'MOTD_log' );
		
		if( $cutoffDate == NULL )
		{
			$yesterday = new DateTime( '-1 day', $this->UTC_DTZ );
			$cutoffDate = $this->dtz_to_db_text( $yesterday );
		}
		$this->db_ro->where( 'lastQueried >=', $cutoffDate );
		
		$this->db_ro->order_by( 'lastQueried', 'ASC' );
		$query = $this->db_ro->get();
		return $query->result();
	}// get_new_MOTDs()
	
	
	public function get_blocked_cache( array $filter = array() )
	{
		if( !empty( $filter ) )
		{
			$diffs = array_diff( $filter, array_keys( self::ACCESSOR_TYPES ) );
			if( !empty( $diffs ) )
			{
				log_message( 'error', 'Channel_model: get_blocked_cache(): unexpected accessor_type(s):'. print_r( $diffs, TRUE ) );
				return FALSE;
			}
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->from( 'SF_blocked_cache' );
		if( !empty( $filter ) )
		{
			$map = self::ACCESSOR_TYPES;
			$filterIDs = array_map( function($name) use ($map){ return $map[$name]; }, $filter );
			//log_message( 'error', 'Channel_model: get_blocked_cache(): filterIDs:'. print_r( $filterIDs, TRUE ) );
			$this->db_ro->where_in( 'accessorType', $filterIDs );
		}
		$this->db_ro->order_by( 'accessorID ASC, accessorType ASC' );
		
		$query = $this->db_ro->get();
		
		return $query->result_array();
	}// get_blocked_cache()
	
	public function get_accessors( $ChannelName = 'SF Spectre Fleet' )
	{
		return FALSE;	// API endpoint doesn't exist
		
		$cached_data = self::get_cached_channel_data( $ChannelName );
		if( $cached_data == FALSE )
		{
			//Failed to get data
			log_message( 'error', 'Channel_model: get_accessors(): problem getting data for channel: '.$ChannelName );
			return FALSE;
		}
		
		$formatted_data['lastQueried'] = $cached_data['queried'];
		
		$rawData = json_decode( $cached_data['rawData'] );	// , FALSE, 512, JSON_BIGINT_AS_STRING );
		
		// Should pool all char/corp/alliance IDs across all accessors, deduplicate, resolve names via 3 ESI calls, relist?
		
		//$allowed = self::reformat_accessor_list( $rawData->allowed );
		$blocked = self::reformat_accessor_list( $rawData->blocked );
		//$muted = self::reformat_accessor_list( $rawData->muted );
		$operators = self::reformat_accessor_list( $rawData->operators );
		
		$this->load->model('CharacterID_model');
		$named_blocked_characters = $this->CharacterID_model->get_character_names( array_keys( $blocked ) );
		//log_message( 'error', 'Channel_model: named_blocked_characters:'. print_r( $named_blocked_characters, TRUE ) );
		foreach( $named_blocked_characters as $named_blocked_character )
		{
			$blocked[$named_blocked_character['character_id']]['accessorName'] = $named_blocked_character['name'];
		}
		$named_ops_characters = $this->CharacterID_model->get_character_names( array_keys( $operators ) );
		//log_message( 'error', 'Channel_model: named_ops_characters:'. print_r( $named_ops_characters, TRUE ) );
		foreach( $named_ops_characters as $named_ops_character )
		{
			$operators[$named_ops_character['character_id']]['accessorName'] = $named_ops_character['name'];
		}
		
		//$formatted_data['allowed'] = $allowed;
		$formatted_data['blocked'] = $blocked;
		//$formatted_data['muted'] = $muted;
		$formatted_data['operators'] = $operators;
		return $formatted_data;
	}// get_accessors()
	
	private function get_cached_channel_data( $displayName )
	{
		if( $displayName === NULL )
		{
			throw new InvalidArgumentException( '$displayName should not be null' );
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'channelID, queried, rawData, parsedData' );
		$this->db_ro->from( 'esi_channel_cache' );
		$this->db_ro->where( 'displayName', $displayName );
		
		$query = $this->db_ro->get();
		
		if( $query->num_rows() !== 1 )
		{
			return FALSE;
		}
		
		$cache_data = $query->row_array();
		
		$lastQueried_datetime = DateTime::createFromFormat( self::DATETIME_DB_FORMAT, $cache_data['queried'] );
		$cached_data['queried'] = $lastQueried_datetime->format( self::DATETIME_DISPLAY_FORMAT );
		
		$cached_data['rawData'] = $cache_data['rawData'];
		
		$parsedData = json_decode( $cache_data['parsedData'], TRUE );
		$cached_data['parsedData'] = $parsedData;
		// Ensure at least empty arrays with expected map keys are stored, even if JSON didn't decode
		if( $parsedData === NULL || !is_array($parsedData) || !isset( $parsedData['fleets'] ) )
		{
			$cached_data['parsedData'] = array(
				'bulletins_html' => '',
				'kills' => array(),
				'active' => array(),
				'fleets' => array(),
				'errors' => array()
			);
		}
		
		return $cached_data;
	}// get_cached_channel_data()
	
	private function reformat_accessor_list( $accessors )
	{
		$output = array();
		foreach( $accessors as $accessor )
		{
			$output[$accessor->accessor_id] = array(
				'accessorType' => $accessor->accessor_type,
				'accessorName' => NULL
			);
		}
		return $output;
	}// reformat_accessor_list()
	
}// Channel_model
?>