<?php
Class User_model extends SF_Model {
	
	
	const ADMIN_MEMBER = 0;
	const ADMIN_TECH = 1;
	const ADMIN_STAFF = 2;
	const ADMIN_RECRUITER = 3;
	const DEFAULT_ADMIN_ROLE = self::ADMIN_MEMBER;
	
	const SIG_CARRIER = 1;
	const SIG_DREAD = 2;
	const SIG_FAX = 3;
	const SIG_CAP_FC = 4;
	const SIG_PROBER = 5;
	const SIG_BUBBLER = 6;
	const SIG_SCOUT = 7;
	const SIG_LOGI_FC = 8;
	const SIG_BOOSHER = 9;
	const SIG_LOGISTICS = 10;
	const SIG_INCURSION_FC = 11;
	
	public static function ADMIN_NAMES()
	{
		return array(
			self::ADMIN_MEMBER => 'Member',
			self::ADMIN_TECH => 'Tech',
			self::ADMIN_STAFF => 'Staff',
			self::ADMIN_RECRUITER => 'Recruiter'
		);
	}// ADMIN_NAMES()
	
	public function get_SF_groups()
	{
		$this->ensure_db_ro_conn();
		
		$query = $this->db_ro->get( 'SF_user_groups' );
		$groups = array();
		foreach( $query->result_array() as $group )
		{
			$groups[$group['groupID']] = $group;
		}
		return $groups;
	}// get_SF_groups()
	
	public static function generate_random_password()
	{
		return base64_encode( openssl_random_pseudo_bytes(54) );	//	54 bytes of entropy, 72 chars, avoid risk of early null byte fed into bcrypt
	}// generate_random_password()
	
	
	public function get_user_data_by_ID( $UserID )	// Unlike ..._by_name(), this returns no "Password", "CharacterOwnerHash"
	{
		if( $UserID === NULL )
		{
			throw new InvalidArgumentException( '$UserID should not be null' );
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'UserID, Username, CharacterName, Rank, Editor, DateRegistered, Admin, CharacterID' );
		$this->db_ro->from( 'users' );
		$this->db_ro->where( 'UserID', $UserID );
		
		$query = $this->db_ro->get();
		
		if( $query->num_rows() == 1 )
		{
			return $query->row();
		}
		else
		{
			return FALSE;
		}
	}// get_user_data_by_ID()
	
	public function get_user_data_by_name( $Username )
	{
		if( $Username === NULL )
		{
			throw new InvalidArgumentException( '$Username should not be null' );
		}
		
		self::ensure_db_conn();
		
		$this->db->select( 'UserID, Username, CharacterName, Password, Rank, Editor, DateRegistered, Admin, CharacterID, CharacterOwnerHash' );
		$this->db->from( 'users' );
		$this->db->where( 'Username', $Username );
		
		$query = $this->db->get();
		
		if( $query->num_rows() == 1 )
		{
			return $query->row();
		}
		else
		{
			return FALSE;
		}
	}// get_user_data_by_name()
	
	public function get_roles_by_UserID( $UserID )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'Rank, Editor, Admin' );
		$this->db_ro->where( 'UserID', $UserID );
		$query = $this->db_ro->get( 'users' );
		
		if( $query->num_rows() == 1 )
		{
			return $query->row();
		}
		else
		{
			return FALSE;
		}
	}// get_roles_by_UserID()
	
	public function get_groups_by_UserID( $UserID )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'userID, ugs.groupID, groupName, private' );
		$this->db_ro->from( 'SF_users_groups AS ugs' );
		$this->db_ro->join( 'SF_user_groups AS gs', 'ugs.groupID = gs.groupID', 'left' );
		
		$this->db_ro->where( 'userID', $UserID );
		
		$this->db_ro->order_by( 'ugs.groupID', 'ASC' );
		
		$query = $this->db_ro->get();
		return $query->result_array();
	}// get_groups_by_UserID()
	
	public function get_users_by_groupID( $groupID )
	{
		self::ensure_db_ro_conn();

		$this->db_ro->select( 'ugs.userID as UserID, Username, CharacterName, Rank, Editor, DateRegistered, Admin, CharacterID, DiscordID' );
		$this->db_ro->from( 'SF_users_groups AS ugs' );
		$this->db_ro->join( 'users AS u', 'ugs.userID = u.UserID', 'left' );
		$this->db_ro->join( 'discord_user_id AS did', 'u.UserID = did.UserID', 'left' );
		
		$this->db_ro->where( 'groupID', $groupID );
		
		$this->db_ro->order_by( 'userID', 'ASC' );
		
		$query = $this->db_ro->get();
		return $query->result_array();
	}// get_users_by_groupID()
	
	public function add_user_to_group( $TargetUserID, $groupID, $EnactingUserID, $comment )
	{
		if( $TargetUserID === NULL )
		{
			throw new InvalidArgumentException( '$TargetUserID should not be null' );
		}
		if( $groupID === NULL )
		{
			throw new InvalidArgumentException( '$groupID should not be null' );
		}
		if( $EnactingUserID === NULL )
		{
			throw new InvalidArgumentException( '$EnactingUserID should not be null' );
		}
		if( $comment === NULL )
		{
			throw new InvalidArgumentException( '$comment should not be null' );
		}
		
		self::ensure_db_conn();
		
		$timestamp = self::SF_now_dtz_db_text();
		
		$this->db->trans_start();
		
		$this->db->select( 'userID, groupID' );
		$this->db->from( 'SF_users_groups' );
		$this->db->where( 'userID', $TargetUserID );
		$this->db->where( 'groupID', $groupID );
		$query = $this->db->get();
		
		if( $query->num_rows() !== 0 )
		{
			$this->db->trans_complete();
			return FALSE;
		}
		
		$this->db->set( 'userID', $TargetUserID );
		$this->db->set( 'groupID', $groupID );
		
		$inserted = ( $this->db->insert( 'SF_users_groups' ) && $this->db->affected_rows() == 1 );
		
		$logged = self::log_group_action( $TargetUserID, $groupID, $EnactingUserID, $timestamp, TRUE, $comment );
		
		$this->db->trans_complete();
		
		return ( $this->db->trans_status() === TRUE && $inserted && $logged );
	}// add_user_to_group()
	
	private function log_group_action( $TargetUserID, $groupID, $EnactingUserID, $timestamp, $AddNotRemove, $comment )
	{
		self::ensure_db_conn();
		
		$this->db->set( 'TargetUserID', $TargetUserID );
		$this->db->set( 'groupID', $groupID );
		$this->db->set( 'EnactingUserID', $EnactingUserID );
		$this->db->set( 'timestamp', $timestamp );
		$this->db->set( 'AddNotRemove', $AddNotRemove );
		$this->db->set( 'comment', $comment );
		return ( $this->db->insert( 'users_groups_log' ) && $this->db->affected_rows() == 1 );
	}// log_group_action()
	
	public function remove_user_from_group( $TargetUserID, $groupID, $EnactingUserID, $comment )
	{
		if( $TargetUserID === NULL )
		{
			throw new InvalidArgumentException( '$TargetUserID should not be null' );
		}
		if( $groupID === NULL )
		{
			throw new InvalidArgumentException( '$groupID should not be null' );
		}
		if( $EnactingUserID === NULL )
		{
			throw new InvalidArgumentException( '$EnactingUserID should not be null' );
		}
		if( $comment === NULL )
		{
			throw new InvalidArgumentException( '$comment should not be null' );
		}
		
		self::ensure_db_conn();
		
		$timestamp = self::SF_now_dtz_db_text();
		
		$this->db->trans_start();
		
		$this->db->select( 'userID, groupID' );
		$this->db->from( 'SF_users_groups' );
		$this->db->where( 'userID', $TargetUserID );
		$this->db->where( 'groupID', $groupID );
		$query = $this->db->get();
		
		if( $query->num_rows() !== 1 )
		{
			$this->db->trans_complete();
			return FALSE;
		}
		
		$this->db->where( 'userID', $TargetUserID );
		$this->db->where( 'groupID', $groupID );
		
		$deleted = ( $this->db->delete( 'SF_users_groups' ) && $this->db->affected_rows() == 1 );
		
		$logged = self::log_group_action( $TargetUserID, $groupID, $EnactingUserID, $timestamp, FALSE, $comment );
		
		$this->db->trans_complete();
		
		return ( $this->db->trans_status() === TRUE && $deleted && $logged );
	}// remove_user_from_group()
	
	
	public function update_password( $UserID, $password )
	{
		if( $UserID === NULL )
		{
			throw new InvalidArgumentException( '$UserID should not be null' );
		}
		if( $password === NULL )
		{
			throw new InvalidArgumentException( '$password should not be null' );
		}
		
		$newpassword = array( 'Password' => password_hash($password, PASSWORD_DEFAULT) );
		$newpassword['LastPasswordChange'] = self::SF_now_dtz_db_text();
		
		self::ensure_db_conn();
		
		$this->db->where('UserID', $UserID);
		return ( $this->db->update( 'users', $newpassword ) && $this->db->affected_rows() == 1 );
	}// update_password()
	
	public function update_login( $UserID )
	{
		$LastLogin = array( 'LastLogin' => self::SF_now_dtz_db_text() );
		
		self::ensure_db_conn();
		
		$this->db->where('UserID', $UserID);
		return ( $this->db->update( 'users', $LastLogin ) && $this->db->affected_rows() == 1 );
	}// update_login()
	
	public function update_EveID( $UserID, $CharacterID, $CharacterOwnerHash )
	{
		$EveID = array(
			'CharacterID' => $CharacterID,
			'CharacterOwnerHash' => $CharacterOwnerHash
		);
		
		self::ensure_db_conn();
		
		$this->db->where('UserID', $UserID);
		return ( $this->db->update( 'users', $EveID ) && $this->db->affected_rows() == 1 );
	}// update_EveID()
	
	public function randomise_user_password( $UserID, $EnactingUserID )
	{
		if( $UserID === NULL )
		{
			throw new InvalidArgumentException( '$UserID should not be null' );
		}
		if( $EnactingUserID === NULL )
		{
			throw new InvalidArgumentException( '$EnactingUserID should not be null' );
		}
		
		$random_password = self::generate_random_password();
		$timestamp = self::SF_now_dtz_db_text();
		
		$newpassword = array(
			'Password' => password_hash($random_password, PASSWORD_DEFAULT),
			'LastPasswordChange' => $timestamp
		);
		
		self::ensure_db_conn();
		
		$this->db->trans_start();
		
		$this->db->where('UserID', $UserID);
		$updated = ( $this->db->update( 'users', $newpassword ) && $this->db->affected_rows() == 1 );
		
		$inserted = self::log_staff_action( $EnactingUserID, 'Reset Password for UserID:'.$UserID, $timestamp );
		
		$this->db->trans_complete();
		
		return ( $this->db->trans_status() === TRUE && $updated && $inserted );
	}// randomise_user_password()
	
	public function available_username( $username )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'UserID' );
		$this->db_ro->where( 'LOWER("Username")', strtolower($username) );	//  Case-insensitive matching
		$this->db_ro->from('users');
		$query = $this->db_ro->get();
		
		return ($query->num_rows() == 0);
	}// available_username()
	
	public function register_user( $username, $password )
	{
		$this->load->model( 'Command_model' );
		$this->load->model( 'Editor_model' );
		
		self::ensure_db_conn();
		
		$this->db->set( 'Username', $username );
		$this->db->set( 'CharacterName', $username );
		$this->db->set( 'Rank', Command_model::DEFAULT_FC_RANK );
		$this->db->set( 'Editor', Editor_model::DEFAULT_EDITOR_ROLE );
		$date = self::SF_now_dtz_db_text();
		$this->db->set( 'DateRegistered', $date );
		$this->db->set( 'LastLogin', $date );
		$this->db->set( 'LastPasswordChange', $date );
		$this->db->set( 'Password', password_hash( $password, PASSWORD_DEFAULT ) );
		
		if ( $this->db->insert( 'users' ) && $this->db->affected_rows() == 1 )
		{
			return $this->db->insert_id();
		}
		else
		{
			return FALSE;
		}
	}// register_user()
	
	public function update_rank( $TargetUserID, $Rank, $EnactingUserID, $external_transaction=FALSE )
	{
		self::ensure_db_conn();
		
		if( !$external_transaction )
		{
			$this->db->trans_start();
		}
		
		$roles = array(
			'Rank' => $Rank,
		);
		$this->db->where( 'UserID', $TargetUserID );
		$this->db->where( 'Rank !=', $Rank );		// Ensure we are changing the value
		$updated = ($this->db->update( 'users', $roles ) && $this->db->affected_rows() == 1 );
		
		$inserted = self::log_role_change( $TargetUserID, $Rank, 'FC', $EnactingUserID );
		
		if( !$external_transaction )
		{
			$this->db->trans_complete();
			
			return ( $this->db->trans_status() === TRUE && $updated && $inserted );
		}
		else
		{
			return $updated && $inserted;
		}
	}// update_rank()
	
	public function update_editor_role( $TargetUserID, $Role, $EnactingUserID )
	{
		self::ensure_db_conn();
		
		$this->db->trans_start();
		
		$roles = array(
			'Editor' => $Role,
		);
		$this->db->where( 'UserID', $TargetUserID );
		$this->db->where( 'Editor !=', $Role );		// Ensure we are changing the value
		$updated = ( $this->db->update( 'users', $roles ) && $this->db->affected_rows() == 1 );
		
		$inserted = self::log_role_change( $TargetUserID, $Role, 'Editor', $EnactingUserID );
		
		$this->db->trans_complete();
		
		return ( $this->db->trans_status() === TRUE && $updated && $inserted );
	}// update_editor_role()
	
	private function log_role_change( $TargetUserID, $Role, $RoleType, $EnactingUserID )
	{
		self::ensure_db_conn();
		
		$this->db->set( 'TargetUserID', $TargetUserID );
		$this->db->set( 'NewRole', $Role );
		$this->db->set( 'RoleType', $RoleType );
		$this->db->set( 'EnactingUserID', $EnactingUserID );
		return ( $this->db->insert( 'users_role_log' ) && $this->db->affected_rows() == 1 );
	}// log_role_change()
	
	
	public function log_staff_action( $UserID, $action, $timestamp = NULL )
	{
		self::ensure_db_conn();
		
		$this->db->set( 'UserID', $UserID );
		if( $timestamp !== NULL )
		{
			$this->db->set( 'actionTime', $timestamp );
		}
		$this->db->set( 'action', $action );
		
		return ( $this->db->insert( 'staff_action_log' ) && $this->db->affected_rows() == 1 );
	}// log_staff_action()
	
	public function get_latest_rank_change( $TargetUserID )
	{
		if( $TargetUserID === NULL )
		{
			throw new InvalidArgumentException( '$TargetUserID should not be null' );
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->from( 'users_role_log' );
		$this->db_ro->where( 'TargetUserID', $TargetUserID );
		$this->db_ro->where( 'RoleType', 'FC' );
		$this->db_ro->order_by( 'timestamp', 'DESC' );
		$this->db_ro->limit( 1 );
		$query = $this->db_ro->get();
		
		if( $query->num_rows() == 1 )
		{
			return $query->row()->timestamp;
		}
		else
		{
			return FALSE;
		}
	}// get_latest_rank_change()
	
	public function get_recent_public_rank_changes()	// Doesn't find demotions to Member
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'users_role_log.NewRole, users_role_log.timestamp, TargetUserID, users.CharacterName' );
		$this->db_ro->from( 'users_role_log' );
		$this->db_ro->join( 'users', 'users_role_log.TargetUserID = users.UserID', 'left' );
		
		$this->db_ro->where( 'RoleType', 'FC' );
		$this->db_ro->where_in( 'NewRole', Command_model::FC_RANKS() );
		$this->db_ro->where( 'timestamp >= (current_date - interval \'7 day\')' );
		$this->db_ro->order_by( 'timestamp', 'DESC' );
		$this->db_ro->limit( 5 );
		
		$query = $this->db_ro->get();
		return $query->result();
	}// get_recent_public_rank_changes()
	
	public function was_user_an_FC( $UserID )
	{
		if( $UserID === NULL )
		{
			throw new InvalidArgumentException( '$UserID should not be null' );
		}
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'timestamp' );
		$this->db_ro->from( 'users_role_log' );
		
		$this->db_ro->where( 'RoleType', 'FC' );
		$this->db_ro->where( 'TargetUserID', $UserID );
		$this->db_ro->limit( 1 );
		
		$query = $this->db_ro->get();
		
		if( $query->num_rows() == 1 )
		{
			return TRUE;
		}
		else
		{
			// No record of their rank change, but possibly grandfathered in and currently non-member
			
			$this->db_ro->select( 'UserID' );
			$this->db_ro->from( 'users' );
			$this->db_ro->where( 'UserID', $UserID );
			$this->db_ro->where( 'Rank !=', Command_model::RANK_MEMBER );
			$query = $this->db_ro->get();
			
			return ( $query->num_rows() == 1 );
		}
	}// was_user_an_FC()
	
	public function get_staff()
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'UserID, CharacterName, Admin, CharacterID' );
		$this->db_ro->from( 'users' );
		$this->db_ro->where( 'Admin !=', self::ADMIN_MEMBER );
		$this->db_ro->order_by( 'CharacterName', 'ASC' );
		$query = $this->db_ro->get();
		
		return $query->result_array();
	}// get_staff()
	
	public function prepare_evemail( array $recipients, $subject, $body )
	{
		if( empty( $recipients ) )
		{
			throw new InvalidArgumentException( '$recipients should not be empty' );
		}
		if( $subject === NULL )
		{
			throw new InvalidArgumentException( '$subject should not be null' );
		}
		if( $body === NULL )
		{
			throw new InvalidArgumentException( '$body should not be null' );
		}
		
		$this->config->load('ccp_api');
		$this->load->library( 'LibOAuthState', array('key'=>'eve'), 'OAuth_model' );
        $this->load->library( 'LibOAuth2', $this->config->item('oauth_eve'), 'oauth_eve' );
		$this->load->library( 'LibRestAPI', $this->config->item('rest_esi'), 'rest_esi' );
		
		if( !$this->OAuth_model->ensure_fresh_token( $this->oauth_eve ) )
		{
			return array(
				'error' => 'Unable to refresh authentication tokens.'
			);
		}
		
		$post_array = array(
			'body' => $body,
			'recipients' => $recipients,
			'subject' => $subject
		);
		$mail_url = $this->config->item( 'rest_esi' )['root'] .'/v1/ui/openwindow/newmail/';
		
		$response = $this->rest_esi->do_call( $mail_url, $this->OAuth_model->get_auth_token(), $post_array, 'POST_JSON' );
		if( $response !== FALSE )
		{
			return $response['response_code'] === 204;
		}
		return FALSE;
	}// prepare_evemail()
	
	// Dupe from Fleets_model
	public static function should_have_skills_token( $Rank, $Editor, $Admin, array $groups )
	{
		foreach( $groups as $group )
		{
			switch( $group['groupID'] )
			{
				case self::SIG_CARRIER:
				case self::SIG_DREAD:
				case self::SIG_FAX:
				case self::SIG_CAP_FC:
					return true;
					break;
				default:
					break;
			}
		}
		return false;
	}// should_have_skills_token()
	
	public function get_refresh_token( $characterID	)
	{
		if( $characterID === NULL )
		{
			throw new InvalidArgumentException( '$characterID should not be null' );
		}
		
		$this->config->load('ccp_api');
        $this->load->library( 'LibOAuth2', $this->config->item('oauth_eve'), 'oauth_eve' );
		
		self::ensure_db_conn();
		
		$this->db->select( 'characterID, refresh_token' );
		$this->db->from( 'skills_refresh_tokens' );
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
		
		$this->config->load('ccp_api');
        $this->load->library( 'LibOAuth2', $this->config->item('oauth_eve'), 'oauth_eve' );
		
		self::ensure_db_conn();
		
		// Check whether a token already existed
		$this->db->select( 'characterID, refresh_token' );
		$this->db->from( 'skills_refresh_tokens' );
		$this->db->where( 'characterID', $characterID );
		$query = $this->db->get();
		
		$refresh_token = $this->oauth_eve->encrypt_token( $characterID, $refresh_token );
		
		if( $query->num_rows() == 1 )
		{
			$this->db->set( 'refresh_token', $refresh_token );
			$this->db->where( 'characterID', $characterID );
			return ( $this->db->update( 'skills_refresh_tokens' ) && $this->db->affected_rows() == 1 );
		}
		else
		{
			$this->db->set( 'characterID', $characterID );
			$this->db->set( 'refresh_token', $refresh_token );
			return ( $this->db->insert( 'skills_refresh_tokens' ) && $this->db->affected_rows() == 1 );
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
		$this->db->delete( 'skills_refresh_tokens' );
		return ( $this->db->affected_rows() == 1 );
	}// remove_refresh_token()
	
	
}// User_model
?>