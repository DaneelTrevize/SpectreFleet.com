<?php
Class Discord_model extends SF_Model {
	
	
	/*
	$guilds_url = $DISCORD_ROOT . 'users/@me/guilds';
	$guilds_response = $this->rest_discord->do_call( $guilds_url, $this->config->item('discord_bot') );
	if( $guilds_response === FALSE || $guilds_response['response_code'] !== 200 )
	{
		log_message( 'error', 'Discord_model: list_roles() failed with: ' . print_r( $guilds_response, TRUE ) );
		return FALSE;
	}
	*/
	const SPECTREFLEET_GUILD = '108746777263415296';
	const SPECTREFLEET_BOT = '461912156279930880';
	const IGNORED_ROLES = array(
		'108757816533073920',	// "Admin"
		'447781411118907392',	// "AFK LEADERSHIP"
		'194961551462105088',	// "MEDIA"
		'260686856222408704',	// "Tournament"
		'455416279059136523',	// "Friend Next Door"
		'481243216268492800'	// "High Command"
	);
	
	public function __construct()
	{
		$this->config->load( 'discord' );
		$this->load->library( 'libDiscord', $this->config->item('discord_webhooks') );
        $this->load->library( 'LibOAuth2', $this->config->item('oauth_discord'), 'oauth_discord' );
		$this->load->library( 'LibRestAPI', $this->config->item('rest_discord'), 'rest_discord' );
		$this->load->library( 'LibCachedAPI', $this->config->item('oauth_discord'), 'cached_discord' );
	}// __construct()
	
	
	public function ping_ops( $UserID, $datetime, $content )
	{
		$response = $this->libdiscord->exec_webhook_content( 'ops', $content, 'true' );
		$result['response'] = ($response !== FALSE);
		
		$result['logged'] = self::log_ops_pings( $UserID, $datetime, $content, $this->config->item('discord_webhooks')['username'], $response );
		if( !$result['logged'] ) {
			log_message( 'error', "Discord_model: failure to log ping_ops( $UserID, $datetime, $content )." );
		}
		
		return $result;
	}// ping_ops()
	
	private function log_ops_pings( $UserID, $datetime, $content, $name, $response )
	{
		self::ensure_db_conn();
		
		$this->db->set( 'UserID', $UserID );
		$this->db->set( 'fleetTime', $datetime );
		$this->db->set( 'content', $content );
		$this->db->set( 'name', $name );
		$this->db->set( 'response', $response );
		return ( $this->db->insert( 'discord_ops_log' ) && $this->db->affected_rows() == 1 );
	}// log_ops_pings()
	
	
	public function tell_tech( $content )
	{
		$response = $this->libdiscord->exec_webhook_content( 'tech', $content, 'true' );
		$result['response'] = ($response !== FALSE);
		
		return $result;
	}// tell_tech()
	
	public function tell_command( $content )
	{
		$response = $this->libdiscord->exec_webhook_content( 'command', $content, 'true' );
		$result['response'] = ($response !== FALSE);
		
		return $result;
	}// tell_command()
	
	public function tell_directorate( $content )
	{
		$response = $this->libdiscord->exec_webhook_content( 'directorate', $content, 'true' );
		$result['response'] = ($response !== FALSE);
		
		return $result;
	}// tell_directorate()
	
	
	public function store_auth_data( $UserID, $DiscordID, $refresh_token )
	{
		if( $UserID === NULL )
		{
			throw new InvalidArgumentException( '$UserID should not be null' );
		}
		if( $DiscordID === NULL )
		{
			throw new InvalidArgumentException( '$DiscordID should not be null' );
		}
		if( $refresh_token === NULL )
		{
			throw new InvalidArgumentException( '$refresh_token should not be null' );
		}
		
		self::ensure_db_conn();
		
		// Check whether a token already existed
		$this->db->select( 'UserID, refresh_token' );
		$this->db->from( 'discord_user_id' );
		$this->db->where( 'UserID', $UserID );
		$query = $this->db->get();
		
		// Check whether DiscordID is already associated with a different user
		$this->db->select( 'UserID' );
		$this->db->from( 'discord_user_id' );
		$this->db->where( 'UserID !=', $UserID );
		$this->db->where( 'DiscordID', $DiscordID );
		$query = $this->db->get();
		if( $query->num_rows() == 1 )
		{
			return FALSE;
		}
		
		$refresh_token = $this->oauth_discord->encrypt_token( $UserID, $refresh_token );
		
		if( $query->num_rows() == 1 )
		{
			$this->db->set( 'refresh_token', $refresh_token );
			$this->db->where( 'UserID', $UserID );
			return ( $this->db->update( 'discord_user_id' ) && $this->db->affected_rows() == 1 );
		}
		else
		{
			$this->db->set( 'UserID', $UserID );
			$this->db->set( 'DiscordID', $DiscordID );
			$this->db->set( 'refresh_token', $refresh_token );
			return ( $this->db->insert( 'discord_user_id' ) && $this->db->affected_rows() == 1 );
		}
	}// store_auth_data()
	
	public function delete_auth_data( $UserID )
	{
		if( $UserID === NULL )
		{
			throw new InvalidArgumentException( '$UserID should not be null' );
		}
		
		self::ensure_db_conn();
		
		$this->db->where( 'UserID', $UserID );
		$this->db->delete( 'discord_user_id' );
		return ( $this->db->affected_rows() == 1 );
	}// delete_auth_data()
	
	public function get_auth_data( $UserID )
	{
		if( $UserID === NULL )
		{
			throw new InvalidArgumentException( '$UserID should not be null' );
		}
		
		self::ensure_db_conn();
		
		$this->db->select( 'UserID, DiscordID, refresh_token' );
		$this->db->from( 'discord_user_id' );
		$this->db->where( 'UserID', $UserID );
		$query = $this->db->get();
		
		if( $query->num_rows() == 1 )
		{
			$result = $query->row_array();
			$result['refresh_token'] = $this->oauth_discord->decrypt_token( $UserID, $result['refresh_token'] );
			return $result;
		}
		else
		{
			return FALSE;
		}
	}// get_auth_data()

	public function get_self_data( $access_token )
	{
		if( $access_token === NULL )
		{
			throw new InvalidArgumentException( '$access_token should not be null' );
		}
		
		$me_url = $this->config->item( 'rest_discord' )['root'] . 'users/@me';
		$me_response = $this->rest_discord->do_call( $me_url, $access_token );
		if( $me_response === FALSE || $me_response['response_code'] !== 200 )
		{
			log_message( 'error', 'Discord_model: get_self_data() failed with: ' . print_r( $me_response, TRUE ) );
			return FALSE;
		}
		
		$me_decoded = json_decode( $me_response['body'], TRUE, 512, JSON_BIGINT_AS_STRING );
		if( $me_decoded === NULL )
		{
			log_message( 'error', 'Discord_model: get_self_data() decoding failed for: ' . print_r( $me_response['body'], TRUE ) );
			return FALSE;
		}
		
		return $me_decoded;
	}// get_self_data()
	/*
	public function get_user_data( $DiscordID, $access_token )
	{
		if( $DiscordID === NULL )
		{
			throw new InvalidArgumentException( '$DiscordID should not be null' );
		}
		if( $access_token === NULL )
		{
			throw new InvalidArgumentException( '$access_token should not be null' );
		}
		
		$DISCORD_ROOT = $this->config->item( 'rest_discord' )['root'];
		
		$params = array(
			'URL' => $DISCORD_ROOT.'users/'.$DiscordID,
			'VERB' => 'GET',
			'PATH_MAP' => array( 'id' => $DiscordID ),
			'API_MAX_ITEMS' => 1,
			'RESPONSE_NAME_MAP' => array(
				'id' => 'DiscordID',
				'username' => 'username',
				'discriminator' => 'discriminator',
				'avatar' => 'avatar'
			),
			'RESPONSE_KEY_FIELD' => 'id',
			'TABLE_NAME' => 'discord_users_cache'
		);
		
		self::ensure_db_conn();
		
		$results = $this->cached_discord->get_data( $params, $this->db, $access_token );
		
		if( $results === FALSE )
		{
			log_message( 'error', 'Discord_model: Failed to call LibCachedAPI->get_data() for character names.' );
			return FALSE;
		}
		else
		{
			$this->cached_discord->log_failures( $results['failures'] );
			
			return $results['api_results'];
		}
	}// get_user_data()
	*/
	public function get_guild_member_data( $DiscordID, $access_token )
	{
		if( $DiscordID === NULL )
		{
			throw new InvalidArgumentException( '$DiscordID should not be null' );
		}
		if( $access_token === NULL )
		{
			throw new InvalidArgumentException( '$access_token should not be null' );
		}
		
		$DISCORD_ROOT = $this->config->item( 'rest_discord' )['root'];
		
		$params = array(
			'URL' => $DISCORD_ROOT.'guilds/'.self::SPECTREFLEET_GUILD.'/members/'.$DiscordID,
			'VERB' => 'GET',
			'PATH_MAP' => array( 'id' => $DiscordID ),
			'API_MAX_ITEMS' => 1,
			'EXPIRES_PERIOD_OVERRIDE' => 'PT12M',
			'RESPONSE_NAME_MAP' => array(
				'id' => 'DiscordID',
				'username' => 'username',
				'discriminator' => 'discriminator',
				'avatar' => 'avatar',
				'nick' => 'nickname',
				'roles' => 'role_ids'/*,
				'joined_at' => 'joined_at'*/
			),
			'RESPONSE_FLATTEN_MAP' => array( 'user' =>
				array(
					'id' => 'id',
					'username' => 'username',
					'discriminator' => 'discriminator',
					'avatar' => 'avatar'
				)
			),
			'RESPONSE_REENCODE_FIELDS' => array( 'roles' ),
			'RESPONSE_KEY_FIELD' => 'id',
			/*'ALTERNATIVE_RESPONSE_KEY' => 'roles',*/
			'TABLE_NAME' => 'discord_members_cache'
		);
		
		self::ensure_db_conn();
		
		$results = $this->cached_discord->get_data( $params, $this->db, $access_token );
		
		if( $results === FALSE || empty( $results['api_results'] ) )
		{
			log_message( 'error', 'Discord_model: Failed to call LibCachedAPI->get_data() for guild member:'.$DiscordID.'.' );
			return FALSE;
		}
		else
		{
			$this->cached_discord->log_failures( $results['failures'] );
			
			return $results['api_results'][$DiscordID];
		}
	}// get_guild_member_data()
	
	private function get_guild_members_data( $access_token, $DiscordID=NULL )
	{
		if( $access_token === NULL )
		{
			throw new InvalidArgumentException( '$access_token should not be null' );
		}
		
		$DISCORD_ROOT = $this->config->item( 'rest_discord' )['root'];
		
		$fields = array(
			'limit' => 1000
		);
		if( $DiscordID !== NULL )
		{
			$fields['after'] = $DiscordID;
		}
		$params = $this->rest_discord->build_params( $fields );
		$url = $DISCORD_ROOT.'guilds/'.self::SPECTREFLEET_GUILD.'/members' .'?'. $params;
		
		$params = array(
			'URL' => $url,
			'VERB' => 'GET',
			'PATH_MAP' => array( 'guild_id' => self::SPECTREFLEET_GUILD ),
			'API_MAX_ITEMS' => 1,
			'EXPIRES_PERIOD_OVERRIDE' => 'PT12M',
			'RESPONSE_NAME_MAP' => array(
				'id' => 'DiscordID',
				'username' => 'username',
				'discriminator' => 'discriminator',
				'avatar' => 'avatar',
				'nick' => 'nickname',
				'roles' => 'role_ids'/*,
				'joined_at' => 'joined_at'*/
			),
			'RESPONSE_FLATTEN_MAP' => array( 'user' =>
				array(
					'id' => 'id',
					'username' => 'username',
					'discriminator' => 'discriminator',
					'avatar' => 'avatar'
				)
			),
			'RESPONSE_REENCODE_FIELDS' => array( 'roles' ),
			'RESPONSE_KEY_FIELD' => 'id',
			'RESPONSE_LACKS_QUERY_KEY' => TRUE,
			'TABLE_NAME' => 'discord_members_cache'
		);
		
		self::ensure_db_conn();
		
		$results = $this->cached_discord->get_data( $params, $this->db, $access_token );
		
		if( $results === FALSE || empty( $results['api_results'] ) )
		{
			log_message( 'error', 'Discord_model: Failed to call LibCachedAPI->get_data() for guild members.' );
			return FALSE;
		}
		else
		{
			$this->cached_discord->log_failures( $results['failures'] );
			
			return $results['api_results'];
		}
	}// get_guild_members_data()
	
	public function get_trusted_members_data( $access_token )
	{
		$trusted_members = array();
		
		$afterID = NULL;
		do {
			$members_chunk = $this->get_guild_members_data( $access_token, $afterID );
			//log_message( 'error', 'Discord_model: members_chunk '. count( $members_chunk ) );
			if( $members_chunk === FALSE )
			{
				return FALSE;	// Try salvage any previous loop's members?
			}
			
			foreach( $members_chunk as $DiscordID => $member_data )
			{
				if( $member_data['roles'] !== '[]' )
				{
					$trusted_members[$DiscordID] = $member_data;
				}
			}
			
			if( count( $members_chunk ) >= 999 )	// We hit the (bugged?) limit in this call
			{
				end( $members_chunk );
				$afterID = key( $members_chunk );	// Get the last DiscordID, more efficient than testing each?
				//reset( $members_chunk );
			}
			else
			{
				$afterID = NULL;
			}
		} while( $afterID !== NULL );	// Also have a guard counter to cap it to x(5?) loops?
		
		return $trusted_members;
	}// get_trusted_members_data()
	
	public function get_unidentified_trusted_discord_members()
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'dmc.DiscordID, username, nickname, role_ids' );
		$this->db_ro->from( 'discord_members_cache AS dmc' );
		$this->db_ro->join( 'discord_user_id AS dui', 'dmc.DiscordID = dui.DiscordID', 'left' );
		
        $this->db_ro->where( 'dmc.DiscordID !=', self::SPECTREFLEET_BOT );
		$this->db_ro->where( 'role_ids !=', '[]' );
		$this->db_ro->where( 'UserID', NULL );
		
		$this->db_ro->order_by( 'dmc.DiscordID', 'ASC' );
		$query = $this->db_ro->get();
		
		$results = array();
		foreach( $query->result_array() as &$row )
		{
			$roles = json_decode( $row['role_ids'], FALSE, 512, JSON_BIGINT_AS_STRING );
			$row['role_ids'] = ( $roles === NULL ) ? array() : $roles;
			$results[] = $row;
		}
		return $results;
	}// get_unidentified_trusted_discord_members()
	
	public function get_unidenfified_trusted_users()
	{
		$this->load->model( 'User_model' );
		$this->load->model( 'Command_model' );
		$this->load->model( 'Editor_model' );
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'users."UserID", "CharacterID", "CharacterName", "Rank", "Editor", "Admin",
CASE WHEN "groupID" IS NOT NULL THEN TRUE ELSE FALSE END as "hasGroups"', FALSE );
		$this->db_ro->distinct();
		$this->db_ro->from( 'users' );
		$this->db_ro->join( 'SF_users_groups AS ugs', 'users.UserID = ugs.userID', 'left' );
		$this->db_ro->join( 'discord_user_id AS dui', 'users.UserID = dui.UserID', 'left' );
		
		$this->db_ro->group_start();
		$this->db_ro->where( 'Rank !=', Command_model::RANK_MEMBER );
		$this->db_ro->or_where_not_in( 'Editor', array( Editor_model::ROLE_MEMBER, Editor_model::ROLE_SUBMITTER ) );
		$this->db_ro->or_where( 'Admin !=', User_model::ADMIN_MEMBER );
		$this->db_ro->or_where( 'ugs."groupID" IS NOT NULL', NULL, FALSE );
		$this->db_ro->group_end();
		$this->db_ro->where( 'DiscordID', NULL );
		
		$this->db_ro->order_by( 'users.UserID', 'ASC' );
		$query = $this->db_ro->get();
		
		return $query->result_array();
	}// get_unidenfified_trusted_users()
	
	public function get_identified_trusted_users()
	{
		$this->load->model( 'User_model' );
		$this->load->model( 'Command_model' );
		$this->load->model( 'Editor_model' );
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'users.UserID, CharacterName, Rank, Editor, Admin, CharacterID, dmc.DiscordID, username, nickname, role_ids, groupID' );
		$this->db_ro->from( 'users' );
		$this->db_ro->join( 'discord_user_id AS dui', 'users.UserID = dui.UserID', 'left' );
		$this->db_ro->join( 'discord_members_cache AS dmc', 'dui.DiscordID = dmc.DiscordID', 'left' );
		$this->db_ro->join( 'SF_users_groups AS ugs', 'users.UserID = ugs.userID', 'left' );
		
		$this->db_ro->group_start();
		$this->db_ro->where( 'Rank !=', Command_model::RANK_MEMBER );
		$this->db_ro->or_where_not_in( 'Editor', array( Editor_model::ROLE_MEMBER, Editor_model::ROLE_SUBMITTER ) );
		$this->db_ro->or_where( 'Admin !=', User_model::ADMIN_MEMBER );
		$this->db_ro->group_end();
		$this->db_ro->where( 'dui.DiscordID IS NOT NULL' );
		
		$this->db_ro->order_by( 'users.UserID', 'ASC' );
		$query = $this->db_ro->get();
		
		$results = array();
		foreach( $query->result_array() as &$row )
		{
			$userID = $row['UserID'];
			if( !array_key_exists( $userID, $results ) )
			{
				$roles = json_decode( $row['role_ids'], FALSE, 512, JSON_BIGINT_AS_STRING );
				$row['role_ids'] = ( $roles === NULL ) ? array() : $roles;
				$row['groupIDs'] = ( $row['groupID'] === NULL ) ? array() : array( $row['groupID'] );
				unset( $row['groupID'] );
				$results[$userID] = $row;
			}
			else
			{
				// Roles already decoded, just need to merge user groups
				$results[$userID]['groupIDs'][] = $row['groupID'];
			}
		}
		return $results;
	}// get_identified_trusted_users()
	
	
	public static function should_have_identity( $Rank, $Editor, $Admin, array $groups )
	{
		// Mustn't use exact !== comparisons
		return ( $Rank != Command_model::RANK_MEMBER ||
			$Editor != Editor_model::ROLE_MEMBER ||
			$Admin != User_model::ADMIN_MEMBER ||
			!empty( $groups )
		);
	}// should_have_identity()
	
	public static function calculate_roles( $Rank, $Editor, $Admin, $groupIDs=array() )
	{
		$roles = array();
		
		switch( $Rank )
		{
			case Command_model::RANK_SFC:
				$roles[] = '372050389694021652';
				$roles[] = '110600609643646976';
				break;
			case Command_model::RANK_FC:
				$roles[] = '110600609643646976';
				break;
			case Command_model::RANK_JFC:
				$roles[] = '448159408854269952';
				break;
			case Command_model::RANK_TFC:
				$roles[] = '448159662966308874';
				break;
			default:
				break;
		}
		/*
		switch( $Editor )
		{
			case Editor_model::ROLE_PUBLISHER:
				$roles[] = '194961551462105088';
				break;
			case Editor_model::ROLE_EDITOR:
				$roles[] = '194961551462105088';
				break;
			default:
				break;
		}
		*/
		switch( $Admin )
		{
			case User_model::ADMIN_TECH:
				$roles[] = '166963784429207552';
				$roles[] = '230041129117024258';
				break;
			case User_model::ADMIN_STAFF:
				$roles[] = '230041129117024258';
				break;
			case User_model::ADMIN_RECRUITER:
				$roles[] = '468588816194732053';
				break;
			default:
				break;
		}
		
		//$roles = array_merge( $roles, $groupIDs );
		foreach( $groupIDs as $groupID )
		{
			switch( $groupID )
			{
				case User_model::SIG_CARRIER:
					$roles[] = '458323380056096798';
					break;
				case User_model::SIG_DREAD:
					$roles[] = '458323654200000547';
					break;
				case User_model::SIG_FAX:
					$roles[] = '458323771837644800';
					break;
				case User_model::SIG_CAP_FC:
					$roles[] = '458323877588893784';
					break;
				case User_model::SIG_PROBER:
					$roles[] = '458308849657446420';
					break;
				case User_model::SIG_BUBBLER:
					$roles[] = '458309064129249281';
					break;
				case User_model::SIG_SCOUT:
					$roles[] = '458309180735094804';
					break;
				case User_model::SIG_LOGI_FC:
					$roles[] = '458309570591195147';
					break;
				case User_model::SIG_BOOSHER:
					$roles[] = '458310133160607758';
					break;
				case User_model::SIG_LOGISTICS:
					$roles[] = '315600189027254273';
					break;
				case User_model::SIG_INCURSION_FC:
					$roles[] = '476452027262828544';
					break;
				default:
					break;
			}
		}
		
		return array_unique( $roles );
	}// calculate_roles()
	
	
	public function list_roles()
	{
		$DISCORD_ROOT = $this->config->item( 'rest_discord' )['root'];
		
		$roles_url = $DISCORD_ROOT . 'guilds/'.self::SPECTREFLEET_GUILD.'/roles';
		$roles_response = $this->rest_discord->do_call( $roles_url, $this->config->item('discord_bot') );
		if( $roles_response === FALSE || $roles_response['response_code'] !== 200 )
		{
			log_message( 'error', 'Discord_model: list_roles() failed with: ' . print_r( $roles_response, TRUE ) );
			return FALSE;
		}
		$roles_decoded = json_decode( $roles_response['body'], TRUE, 512, JSON_BIGINT_AS_STRING );
		if( $roles_decoded === NULL )
		{
			log_message( 'error', 'Discord_model: list_roles() failed decoding: ' . print_r( $roles_response['body'], TRUE ) );
			return FALSE;
		}
		return $roles_decoded;
	}// list_roles()
	
	public function update_roles( array $roles )
	{
		if( $roles === NULL )
		{
			throw new InvalidArgumentException( '$roles should not be null' );
		}
		
		self::ensure_db_conn();
		
		$this->db->trans_start();
		
		$this->db->truncate( 'discord_roles' );
		
		$insert = array();
		foreach( $roles as $role )
		{
			$insert[] = array(
				'role_id' =>$role['id'],
				'name' =>$role['name'],
				'color' =>$role['color'],
				'hoist' =>$role['hoist'],
				'position' =>$role['position'],
				'permissions' =>$role['permissions'],
				'managed' =>$role['managed'],
				'mentionable' =>$role['mentionable']
			);
		}
		if( !empty($insert) )
		{
			$this->db->insert_batch( 'discord_roles', $insert );
		}
		
		$this->db->trans_complete();
		
		return $this->db->trans_status();
	}// update_roles()
	
	public function get_roles()
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->from( 'discord_roles' );
		$this->db_ro->order_by( 'position', 'DESC' );
		$query = $this->db_ro->get();
		
		return $query->result_array();
	}// get_roles()
	
	public function list_channels()
	{
		$DISCORD_ROOT = $this->config->item( 'rest_discord' )['root'];
		
		$channels_url = $DISCORD_ROOT . 'guilds/'.self::SPECTREFLEET_GUILD.'/channels';
		$channels_response = $this->rest_discord->do_call( $channels_url, $this->config->item('discord_bot') );
		if( $channels_response === FALSE || $channels_response['response_code'] !== 200 )
		{
			log_message( 'error', 'Discord_model: list_channels() failed with: ' . print_r( $channels_response, TRUE ) );
			return FALSE;
		}
		$channels_decoded = json_decode( $channels_response['body'], TRUE, 512, JSON_BIGINT_AS_STRING );
		if( $channels_decoded === NULL )
		{
			log_message( 'error', 'Discord_model: list_channels() failed decoding: ' . print_r( $channels_response['body'], TRUE ) );
			return FALSE;
		}
		return $channels_decoded;
	}// list_channels()
	
	public function send_message( $channel_id, $content )
	{
		if( $channel_id === NULL )
		{
			throw new InvalidArgumentException( '$channel_id should not be null' );
		}
		if( $content === NULL )
		{
			throw new InvalidArgumentException( '$content should not be null' );
		}
		
		$DISCORD_ROOT = $this->config->item( 'rest_discord' )['root'];
		
		$fields = array( 'content' => $content );
		
		$messages_url = $DISCORD_ROOT . 'channels/'.$channel_id.'/messages';
		$messages_response = $this->rest_discord->do_call( $messages_url, $this->config->item('discord_bot'), $fields, 'POST_JSON' );
		if( $messages_response === FALSE || $messages_response['response_code'] !== 200 )
		{
			log_message( 'error', 'Discord_model: send_message() failed with: ' . print_r( $messages_response, TRUE ) );
			return FALSE;
		}
		$messages_decoded = json_decode( $messages_response['body'], TRUE, 512, JSON_BIGINT_AS_STRING );
		if( $messages_decoded === NULL )
		{
			log_message( 'error', 'Discord_model: send_message() failed decoding: ' . print_r( $messages_response['body'], TRUE ) );
			return FALSE;
		}
		
		//print_r( $messages_response['body'] );
		
	}// send_message()
	/*
	public function list_dms()
	{
		$DISCORD_ROOT = $this->config->item( 'rest_discord' )['root'];
		
		$dms_url = $DISCORD_ROOT . 'users/@me/channels';
		$dms_response = $this->rest_discord->do_call( $dms_url, $this->config->item('discord_bot') );
		if( $dms_response === FALSE || $dms_response['response_code'] !== 200 )
		{
			log_message( 'error', 'Discord_model: list_dms() failed with: ' . print_r( $dms_response, TRUE ) );
			return FALSE;
		}
		print_r( $dms_response );
		// Bots just can't know their DM channels https://github.com/discordapp/discord-api-docs/issues/184
		
	}// list_dms()
	*/
	public function start_dm( $recipient_id )	// Start/resume DM channel
	{
		if( $recipient_id === NULL )
		{
			throw new InvalidArgumentException( '$recipient_id should not be null' );
		}
		
		$DISCORD_ROOT = $this->config->item( 'rest_discord' )['root'];
		
		$fields = array( 'recipient_id' => $recipient_id );
		
		$DMs_url = $DISCORD_ROOT . 'users/@me/channels';
		$DMs_response = $this->rest_discord->do_call( $DMs_url, $this->config->item('discord_bot'), $fields, 'POST_JSON' );
		if( $DMs_response === FALSE || $DMs_response['response_code'] !== 200 )
		{
			log_message( 'error', 'Discord_model: start_dm() failed with: ' . print_r( $DMs_response, TRUE ) );
			return FALSE;
		}
		$DMs_decoded = json_decode( $DMs_response['body'], TRUE, 512, JSON_BIGINT_AS_STRING );
		if( $DMs_decoded === NULL )
		{
			log_message( 'error', 'Discord_model: start_dm() failed decoding: ' . print_r( $DMs_response['body'], TRUE ) );
			return FALSE;
		}
		
		//print_r( $DMs_response['body'] );
		
	}// open_convo()
	
	public function send_dm( $dm_channel_id, $content )
	{
		if( $dm_channel_id === NULL )
		{
			throw new InvalidArgumentException( '$dm_channel_id should not be null' );
		}
		if( $content === NULL )
		{
			throw new InvalidArgumentException( '$content should not be null' );
		}
		
		$DISCORD_ROOT = $this->config->item( 'rest_discord' )['root'];
		
		$fields = array( 'content' => $content );
		
		$messages_url = $DISCORD_ROOT . 'channels/'.$dm_channel_id.'/messages';
		$messages_response = $this->rest_discord->do_call( $messages_url, $this->config->item('discord_bot'), $fields, 'POST_JSON' );
		if( $messages_response === FALSE || $messages_response['response_code'] !== 200 )
		{
			log_message( 'error', 'Discord_model: send_dm() failed with: ' . print_r( $messages_response, TRUE ) );
			return FALSE;
		}
		$messages_decoded = json_decode( $messages_response['body'], TRUE, 512, JSON_BIGINT_AS_STRING );
		if( $messages_decoded === NULL )
		{
			log_message( 'error', 'Discord_model: send_dm() failed decoding: ' . print_r( $messages_response['body'], TRUE ) );
			return FALSE;
		}
		
		//print_r( $messages_response['body'] );
		
	}// send_dm()
	
}// Discord_model
?>