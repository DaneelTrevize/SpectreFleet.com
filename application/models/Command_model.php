<?php
Class Command_model extends SF_Model {
	
	const RANK_SFC = 1;
	const RANK_FC = 2;
	const RANK_JFC = 3;
	const RANK_MEMBER = 4;
	const RANK_TFC = 5;
	const DEFAULT_FC_RANK = self::RANK_MEMBER;
	
	const RECENT_FC_APPS_LIMIT = 10;
	
	public static function RANK_NAMES()
	{
		return array(
			self::RANK_SFC => 'Senior Fleet Commander',
			self::RANK_FC => 'Fleet Commander',
			self::RANK_JFC => 'Junior Fleet Commander',
			self::RANK_MEMBER => 'Member',
			self::RANK_TFC => 'Trial Fleet Commander'
		);
	}// RANK_NAMES()
	
	public static function FC_RANKS()
	{
		return array( self::RANK_SFC, self::RANK_FC, self::RANK_JFC, self::RANK_TFC );
	}// FC_RANKS()
	
	const TZ_EU = 'EU';		// EMEA
	const TZ_US = 'US';		// AMER
	const TZ_AU = 'AU';		// APAC
	
	public static function FC_TIMEZONES()
	{
		return array(
			self::TZ_EU => '12:00 - 20:00 (Europe, Middle East, Africa)',
			self::TZ_US => '20:00 - 04:00 (Americas)',
			self::TZ_AU => '04:00 - 12:00 (Asia-Pacific)'
		);
	}// FC_TIMEZONES()
	
	
	
	public function get_sorted_commanders()
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select('UserID, CharacterName, Rank, CharacterID');
		$this->db_ro->from('users');
		$this->db_ro->where_in('Rank', self::FC_RANKS() );
		$this->db_ro->order_by('Rank', 'ASC');		// Assumption that ascending numerical value is descending rank significance!
		$this->db_ro->order_by('CharacterName', 'ASC');
		$query = $this->db_ro->get();
		
		return $query->result_array();
	}// get_sorted_commanders()
	
	public function is_commander( $UserID )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select('UserID, CharacterName, Rank');
		$this->db_ro->from('users');
		$this->db_ro->where_in('Rank', self::FC_RANKS() );
		$this->db_ro->where('UserID', $UserID);
		$query = $this->db_ro->get();
		
		return ( $query->num_rows() == 1 );
	}// is_commander()
	
	public function commander_by_name( $CharacterName )	// Performs search of FCs by vague prefix of actual recorded CharacterName
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select('UserID, CharacterName, Rank, CharacterID');
		$this->db_ro->from('users');
		$this->db_ro->where_in('Rank', self::FC_RANKS() );
		$this->db_ro->like( 'LOWER("CharacterName")', strtolower($CharacterName), 'after' );	//  Case-insensitive matching
		$query = $this->db_ro->get();
		
		if( $query->num_rows() == 1 )
		{
			return $query->row_array();
		}
		else
		{
			return FALSE;
		}
	}// commander_by_name()
	
	
	public function get_old_fc_applications()
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select();
		$this->db_ro->from( 'SF_applications_old' );
		$this->db_ro->order_by( 'ApplicationID', 'DESC' );
		$query = $this->db_ro->get();
		
		return $query->result_array();
	}// get_old_fc_applications()
	
	public function get_old_fc_application( $ApplicationID )
	{
		if( $ApplicationID === NULL )
		{
			throw new InvalidArgumentException( '$ApplicationID should not be null' );
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->from( 'SF_applications_old' );
		$this->db_ro->where( 'ApplicationID', $ApplicationID );
		$query = $this->db_ro->get();
		
		if( $query->num_rows() == 1 )
		{
			return $query->row_array();
		}
		else
		{
			return FALSE;
		}
	}// get_old_fc_application()
	
	/*
	
		Current State	| Possible Action	| New State		| Permitted by
		------------------------------------------------------------------
						| Apply				| Draft			| Applicant (Non-FCs)
		Draft			| Edit				| Draft			| Owner
		Draft			| Submit			| Submitted		| Owner
		Draft			| Cancel			| Cancelled		| Owner
		
		Submitted		| Accept			| Accepted		| SFCs
		Submitted		| Reject			| Rejected		| SFCs
		Submitted		| Cancel			| Cancelled		| Owner
		
	*/
	public function add_fc_application( $UserID, $SFexp, $priorFC, $whySF, $Timezone, $fleetStyle, $fleetSize )
	{
		self::ensure_db_conn();
		
		$this->db->set( 'UserID', $UserID );
		$this->db->set( 'SFexp', $SFexp );
		$this->db->set( 'priorFC', $priorFC );
		$this->db->set( 'whySF', $whySF );
		$this->db->set( 'Timezone', $Timezone );
		$this->db->set( 'fleetStyle', $fleetStyle );
		$this->db->set( 'fleetSize', $fleetSize );
		$insert_success = $this->db->insert( 'SF_applications_new' );
		
		if( $insert_success )
		{
			return $this->db->insert_id();
		}
		else
		{
			return FALSE;
		}
	}// add_fc_application()
	
	public function edit_fc_application( $ApplicationID, $SFexp, $priorFC, $whySF, $Timezone, $fleetStyle, $fleetSize )
	{
		self::ensure_db_conn();
		
		$application = array(
			'lastEdited' => self::SF_now_dtz_db_text(),
			'SFexp' => $SFexp,
			'priorFC' => $priorFC,
			'whySF' => $whySF,
			'Timezone' => $Timezone,
			'fleetStyle' => $fleetStyle,
			'fleetSize' => $fleetSize
		);
		
		$this->db->where( 'ApplicationID', $ApplicationID );
		$this->db->where( 'Status', 'Draft' );
		return ($this->db->update( 'SF_applications_new', $application ) && $this->db->affected_rows() == 1);
	}// edit_fc_application()
	
	public function get_fc_application( $ApplicationID )
	{
		if( $ApplicationID === NULL )
		{
			throw new InvalidArgumentException( '$ApplicationID should not be null' );
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select('SF_applications_new.*, CharacterName, Rank, DateRegistered, CharacterID');
		$this->db_ro->from( 'SF_applications_new' );
		$this->db_ro->join( 'users', 'SF_applications_new.UserID = users.UserID', 'left' );
		$this->db_ro->where( 'ApplicationID', $ApplicationID );
		$query = $this->db_ro->get();
		
		if( $query->num_rows() == 1 )
		{
			return $query->row_array();
		}
		else
		{
			return FALSE;
		}
	}// get_fc_application()
	
	public function get_fc_applications( $UserID = NULL )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select('SF_applications_new.*, CharacterID, CharacterName');
		$this->db_ro->from( 'SF_applications_new' );
		$this->db_ro->join( 'users', 'SF_applications_new.UserID = users.UserID', 'left' );
		
		if( $UserID != NULL )
		{
			$this->db_ro->where( 'SF_applications_new.UserID', $UserID );
		}
		else
		{
			$this->db_ro->where( 'Status', 'Submitted' );
			$this->db_ro->where( 'Rank', self::RANK_MEMBER );	// Hide non-Member apps that were bypassed via Change Rank
		}
		
		$this->db_ro->order_by( 'ApplicationID', 'DESC' );
		$query = $this->db_ro->get();
		return $query->result_array();
	}// get_fc_applications()
	
	public function get_fc_application_history( $UserID )
	{
		if( $UserID === NULL )
		{
			throw new InvalidArgumentException( '$UserID should not be null' );
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( '"ApplicationID", "Status", "DateSubmitted", "UserID", \'Current\' AS "Type"', FALSE );
		$this->db_ro->from( 'SF_applications_new' );
		$this->db_ro->where_in( 'Status', array( 'Submitted', 'Accepted', 'Rejected' ) );
		$this->db_ro->where( 'UserID', $UserID );
		
		$new_apps_query = $this->db_ro->get_compiled_select();
		
		$this->db_ro->select( '"ApplicationID", "Status", "DateSubmitted", "UserID", \'Legacy\' AS "Type"', FALSE );
		$this->db_ro->from( 'SF_applications_old' );
		$this->db_ro->join( 'users', '"SF_applications_old"."CharacterName" = users."CharacterName"', 'left' );
		$this->db_ro->where( 'UserID', $UserID );
		
		$old_apps_query = $this->db_ro->get_compiled_select();
		
		$sql = '('. $new_apps_query .') UNION ('. $old_apps_query .') ORDER BY "ApplicationID" DESC';
		
		$query = $this->db_ro->query( $sql );
		return $query->result_array();
	}// get_fc_application_history()
	
	public function get_recent_fc_applications()
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select('SF_applications_new.*, app.CharacterID AS ApplicantCharacterID, app.CharacterName AS ApplicantCharacterName, en.CharacterID AS EnactingCharacterID, en.CharacterName AS EnactingCharacterName');
		$this->db_ro->from( 'SF_applications_new' );
		$this->db_ro->join( 'users AS app', 'SF_applications_new.UserID = app.UserID', 'left' );
		$this->db_ro->join( 'users AS en', 'SF_applications_new.EnactingUserID = en.UserID', 'left' );
		
		$this->db_ro->where_in( 'Status', array( 'Accepted', 'Rejected' ) );
		$this->db_ro->or_group_start();
		$this->db_ro->where( 'Status', 'Submitted' );
		$this->db_ro->where( 'app.Rank !=', self::RANK_MEMBER );	// Show non-Member apps that were bypassed via Change Rank
		$this->db_ro->group_end();
		
		$this->db_ro->order_by( 'ApplicationID', 'DESC' );
		$this->db_ro->limit( self::RECENT_FC_APPS_LIMIT );
		$query = $this->db_ro->get();
		return $query->result_array();
	}// get_recent_fc_applications()
	
	public function confirm_fc_application( $ApplicationID )
	{
		if( $ApplicationID === NULL )
		{
			throw new InvalidArgumentException( '$ApplicationID should not be null' );
		}
		
		self::ensure_db_conn();
		
		$Status = array(
			'Status' => 'Submitted',
			'DateSubmitted' => self::SF_now_dtz_db_text()
		);
		$this->db->where( 'ApplicationID', $ApplicationID );
		$this->db->where( 'Status', 'Draft' );
		return ($this->db->update( 'SF_applications_new', $Status ) && $this->db->affected_rows() == 1);
	}// confirm_fc_application()
	
	public function cancel_fc_application( $ApplicationID )
	{
		if( $ApplicationID === NULL )
		{
			throw new InvalidArgumentException( '$ApplicationID should not be null' );
		}
		
		self::ensure_db_conn();
		
		$this->db->set( '"EnactingUserID"', '"UserID"', FALSE );	// Use the column value, rather than the string UserID
		$Status = array(
			'Status' => 'Cancelled',
			'DateEnacted' => self::SF_now_dtz_db_text()
		);
		$this->db->where( 'ApplicationID', $ApplicationID );
		$this->db->where_in( 'Status', array( 'Draft', 'Submitted' ) );
		return ($this->db->update( 'SF_applications_new', $Status ) && $this->db->affected_rows() == 1);
	}// cancel_fc_application()
	
	public function accept_fc_application( $ApplicationID, $EnactingUserID )	// Should return applicant's CharacterName
	{
		if( $ApplicationID === NULL )
		{
			throw new InvalidArgumentException( '$ApplicationID should not be null' );
		}
		if( $EnactingUserID === NULL )
		{
			throw new InvalidArgumentException( '$EnactingUserID should not be null' );
		}
		
		self::ensure_db_conn();
		
		// Get Applicant's UserID, Rank and CharacterName
		$this->db->select( 'SF_applications_new.UserID, Rank, CharacterName' );
		$this->db->from( 'SF_applications_new' );
		$this->db->join( 'users', 'SF_applications_new.UserID = users.UserID', 'left' );
		$this->db->where( 'ApplicationID', $ApplicationID );
		$query = $this->db->get();
		
		if( $query->num_rows() != 1 )
		{
			return FALSE;
		}
		
		$TargetUserID = $query->row()->UserID;
		$CharacterName = $query->row()->CharacterName;
		$Current_Rank = $query->row()->Rank;
		$Target_Rank = self::RANK_JFC;	// Can no longer assume this, might be TFC?!
		
		if( $Current_Rank != self::RANK_MEMBER )	// Only Members can apply to be FCs
		{
			return FALSE;
		}
		
		$this->db->trans_start();
		
		$Status = array(
			'Status' => 'Accepted',
			'EnactingUserID' => $EnactingUserID,
			'DateEnacted' => self::SF_now_dtz_db_text()
		);
		$this->db->where( 'ApplicationID', $ApplicationID );
		$this->db->where( 'Status', 'Submitted' );
		if( !$this->db->update( 'SF_applications_new', $Status ) || !$this->db->affected_rows() == 1 )
		{
			$this->db->trans_complete();
			
			return FALSE;
		}
		
		$this->load->model('User_model');
		$updated = $this->User_model->update_rank( $TargetUserID, $Target_Rank, $EnactingUserID, TRUE );	// Avoid nested transaction start
		
		$this->db->trans_complete();
		
		if( $this->db->trans_status() === TRUE && $updated )
		{
			return $CharacterName;
		}
		else
		{
			return FALSE;
		}
	}// accept_fc_application()
	
	public function reject_fc_application( $ApplicationID, $EnactingUserID )
	{
		if( $ApplicationID === NULL )
		{
			throw new InvalidArgumentException( '$ApplicationID should not be null' );
		}
		if( $EnactingUserID === NULL )
		{
			throw new InvalidArgumentException( '$EnactingUserID should not be null' );
		}
		
		self::ensure_db_conn();
		
		$Status = array(
			'Status' => 'Rejected',
			'EnactingUserID' => $EnactingUserID,
			'DateEnacted' => self::SF_now_dtz_db_text()
		);
		$this->db->where( 'ApplicationID', $ApplicationID );
		$this->db->where( 'Status', 'Submitted' );
		return ($this->db->update( 'SF_applications_new', $Status ) && $this->db->affected_rows() == 1);
	}// reject_fc_application()
	
}// Command_model
?>