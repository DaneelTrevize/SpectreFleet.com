<?php
Class Polls_model extends SF_Model {
	
	/*
	
		Current State	| Possible Action	| New State		| Permitted by
		------------------------------------------------------------------
						| Create			| Draft			| (Rank/Role higher than Member)
		Draft			| Edit				| Draft			| Owner
		Draft			| Delete			| Deleted		| Owner
		Draft			| Open				| Open			| Owner
		
		Open			| Close				| Closed		| Owner, Tech?
		
		Closed			| Open				| Open			| Owner, Tech?
		Closed			| Edit				| Closed		| Owner, Tech?
		Closed			| Delete			| Deleted		| Owner, Tech?
		
		Deleted			| 					| 				| 
		
		
		Drafts being private to the owner.
		We can still keep the contents of Deleted polls, and just hide such entries from usual results.
		
	*/
	
	const ALL_READ_ALL_VOTE_MODE = 0;
	const ALL_READ_FC_VOTE_MODE = 1;
	const FC_READ_FC_VOTE_MODE = 2;
	const MAX_ACCESS_MODE = self::FC_READ_FC_VOTE_MODE;
	
	const MAX_OPTIONS_PER_POLL = 10;
	
	const DETAILS_TAGS = '<p><strong><em><s><ol><ul><li><blockquote><h1><h2><h3><div><pre><hr><br><a>';
	
	
	public function get_draft_polls( $UserID )
	{
		if( $UserID === NULL )
		{
			throw new InvalidArgumentException( '$UserID should not be null' );
		}
		
		self::ensure_db_ro_conn();
		
		if( $this->db_ro->platform() == 'mysqli' )
		{
			$this->db_ro->query("SET SESSION sql_mode='ANSI_QUOTES'");
		}
		$this->db_ro->select( 'polls.pollID, Title, OwnerID, Username, Status, creationDate, maximumVotesPerUser, COUNT( poll_options."optionID" ) AS "OptionsCount", accessMode' );
		$this->db_ro->from( 'polls' );
		$this->db_ro->join( 'users', 'polls.OwnerID = users.UserID', 'left' );
		$this->db_ro->join( 'poll_options', 'polls.pollID = poll_options.pollID', 'left' );
		
		$this->db_ro->where( 'OwnerID', $UserID );
		$this->db_ro->where( 'Status', 'Draft' );
		
		$this->db_ro->group_by( array('poll_options.pollID', 'polls.pollID', 'Username') );
		
		$this->db_ro->order_by( 'creationDate', 'desc' );
		$query = $this->db_ro->get();
		return $query->result_array();
	}// get_draft_polls()
	
	public function get_open_polls( $only_all_read = TRUE, $UserID = NULL )
	{
		self::ensure_db_ro_conn();
		
		if( $this->db_ro->platform() == 'mysqli' )
		{
			$this->db_ro->query("SET SESSION sql_mode='ANSI_QUOTES'");
		}
		$this->db_ro->select( 'polls.pollID, Title, OwnerID, Username, Status, creationDate, maximumVotesPerUser, COUNT( poll_options."optionID" ) AS "OptionsCount", accessMode' );
		$this->db_ro->from( 'polls' );
		$this->db_ro->join( 'users', 'polls.OwnerID = users.UserID', 'left' );
		$this->db_ro->join( 'poll_options', 'polls.pollID = poll_options.pollID', 'left' );
		
		if( $only_all_read )
		{
			$this->db_ro->where_in( 'accessMode', array( self::ALL_READ_ALL_VOTE_MODE, self::ALL_READ_FC_VOTE_MODE ) );
		}
		if( $UserID != NULL )
		{
			$this->db_ro->where( 'OwnerID', $UserID );
		}
		
		$this->db_ro->where( 'Status', 'Open' );
		
		$this->db_ro->group_by( array('poll_options.pollID', 'polls.pollID', 'Username') );
		
		$this->db_ro->order_by( 'creationDate', 'desc' );
		$query = $this->db_ro->get();
		return $query->result_array();
	}// get_open_polls()
	
	public function get_closed_polls( $only_all_read = TRUE, $UserID = NULL, $cutoffDate = NULL )
	{
		self::ensure_db_ro_conn();
		
		if( $this->db_ro->platform() == 'mysqli' )
		{
			$this->db_ro->query("SET SESSION sql_mode='ANSI_QUOTES'");
		}
		$this->db_ro->select( 'polls.pollID, Title, OwnerID, Username, Status, creationDate, maximumVotesPerUser, COUNT( poll_options."optionID" ) AS "OptionsCount", accessMode' );
		$this->db_ro->from( 'polls' );
		$this->db_ro->join( 'users', 'polls.OwnerID = users.UserID', 'left' );
		$this->db_ro->join( 'poll_options', 'polls.pollID = poll_options.pollID', 'left' );
		
		if( $only_all_read )
		{
			$this->db_ro->where_in( 'accessMode', array( self::ALL_READ_ALL_VOTE_MODE, self::ALL_READ_FC_VOTE_MODE ) );
		}
		if( $UserID != NULL )
		{
			$this->db_ro->where( 'OwnerID', $UserID );
		}
		
		if( $cutoffDate != NULL )
		{
			$this->db_ro->where( 'creationDate >=', $this->dtz_to_db_text( $cutoffDate ) );
		}
		
		$this->db_ro->where( 'Status', 'Closed' );
		
		$this->db_ro->group_by( array('poll_options.pollID', 'polls.pollID', 'Username') );
		
		$this->db_ro->order_by( 'creationDate', 'desc' );
		$query = $this->db_ro->get();
		return $query->result_array();
	}// get_closed_polls()
	
	public function get_poll( $pollID )
	{
		self::ensure_db_ro_conn();
		
		if( $pollID === NULL )
		{
			throw new InvalidArgumentException( '$pollID should not be null' );
		}
		
		$this->db_ro->select( 'pollID, Title, OwnerID, Username, Status, creationDate, maximumVotesPerUser, accessMode' );
		$this->db_ro->from( 'polls' );
		$this->db_ro->join( 'users', 'polls.OwnerID = users.UserID' );
		
		$this->db_ro->where( 'pollID', $pollID );
		
		$query = $this->db_ro->get();
		
		if( $query->num_rows() == 1 )
		{
			return $query->row_array();
		}
		else
		{
			return FALSE;
		}
	}// get_poll()
	
	public function get_poll_with_options( $pollID )
	{
		self::ensure_db_ro_conn();
		
		if( $pollID === NULL )
		{
			throw new InvalidArgumentException( '$pollID should not be null' );
		}
		
		if( $this->db_ro->platform() == 'mysqli' )
		{
			$this->db_ro->query("SET SESSION sql_mode='ANSI_QUOTES'");
		}
		$this->db_ro->select( 'polls.pollID, Title, OwnerID, Username, Status, creationDate, maximumVotesPerUser, optionID, Description, Details, accessMode' );
		$this->db_ro->from( 'polls' );
		$this->db_ro->join( 'users', 'polls.OwnerID = users.UserID' );
		$this->db_ro->join( 'poll_options', 'polls.pollID = poll_options.pollID', 'left' );
		
		$this->db_ro->where( 'polls.pollID', $pollID );
		
		$this->db_ro->order_by( 'optionID', 'asc' );
		$query = $this->db_ro->get();
		
		if( $query->num_rows() >= 1 )
		{
			return $query->result_array();
		}
		else
		{
			return FALSE;
		}
	}// get_poll_with_options()
	
	public function get_poll_votes( $pollID )
	{
		self::ensure_db_ro_conn();
		
		if( $pollID === NULL )
		{
			throw new InvalidArgumentException( '$pollID should not be null' );
		}
		
		$this->db_ro->trans_start();
		
		if( $this->db_ro->platform() == 'mysqli' )
		{
			$this->db_ro->query("SET SESSION sql_mode='ANSI_QUOTES'");
		}
		$this->db_ro->select( 'pollID, COUNT("pollID") AS "Votes"' );
		$this->db_ro->from( 'poll_votes' );
		$this->db_ro->where( 'pollID', $pollID );
		$this->db_ro->group_by( array('pollID') );
		$query = $this->db_ro->get();
		
		if( $query->num_rows() > 0 )
		{
			$poll_votes['total_votes'] = $query->row()->Votes;
		}
		else
		{
			$poll_votes['total_votes'] = 0;
		}
		
		$this->db_ro->select( 'poll_options.optionID, Description, COUNT("UserID") AS "Votes"' );
		$this->db_ro->from( 'poll_options' );
		$this->db_ro->join( 'poll_votes', '"poll_options"."pollID" = "poll_votes"."pollID" AND "poll_options"."optionID" = "poll_votes"."optionID"', 'left' );
		$this->db_ro->where( '"poll_options"."pollID"', $pollID );
		$this->db_ro->group_by( array('"poll_options"."optionID"', 'Description') );
		$this->db_ro->order_by( 'optionID', 'asc' );
		$query = $this->db_ro->get();
		
		$poll_votes['votes_per_option'] = $query->result_array();
		
		$this->db_ro->trans_complete();
		
		if( $this->db_ro->trans_status() === TRUE )
		{
			return $poll_votes;
		}
		else
		{
			return FALSE;
		}
	}// get_poll_votes()
	
	public function get_users_votes( $pollID, $UserID )
	{
		if( $pollID === NULL )
		{
			throw new InvalidArgumentException( '$pollID should not be null' );
		}
		if( $UserID === NULL )
		{
			throw new InvalidArgumentException( '$UserID should not be null' );
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'pollID, optionID, UserID, timestamp');
		$this->db_ro->from( 'poll_votes' );
		$this->db_ro->where( 'pollID', $pollID );
		$this->db_ro->where( 'UserID', $UserID );
		$this->db_ro->order_by( 'optionID', 'asc' );
		$subquery = $this->db_ro->get_compiled_select();
		
		$this->db_ro->select( 'poll_options.optionID, Description, UserID, timestamp' );
		$this->db_ro->from( 'poll_options' );
		$this->db_ro->join( '('.$subquery.') AS "Votes"', '"poll_options"."pollID" = "Votes"."pollID" AND "poll_options"."optionID" = "Votes"."optionID"', 'left', NULL );
		$this->db_ro->where( '"poll_options"."pollID"', $pollID );
		$this->db_ro->order_by( 'optionID', 'asc' );
		$query = $this->db_ro->get();
		
		return $query->result_array();
	}// get_users_votes()
	
	public function create_poll( $Title, $UserID, $maximumVotesPerUser, $verified_options, $Details, $accessMode )
	{
		if( count( $verified_options ) > self::MAX_OPTIONS_PER_POLL )
		{
			throw new InvalidArgumentException( '$verified_options is too large.' );
		}
		
		self::ensure_db_conn();
		
		$this->db->trans_start();
		
		$this->db->set( 'Title', $Title );
		$this->db->set( 'OwnerID', $UserID );
		$this->db->set( 'Status', 'Draft' );
		$this->db->set( 'maximumVotesPerUser', $maximumVotesPerUser );
		$this->db->set( 'Details', $Details );
		$this->db->set( 'accessMode', intval($accessMode) );
		$insert_success = $this->db->insert( 'polls' );
		
		if( $insert_success )
		{
			$pollID = $this->db->insert_id();
			
			$insert = array();
			foreach( $verified_options as $optionID => $option )
			{
				$insert[] = array(
					'pollID' => $pollID,
					'optionID' => $optionID,
					'Description' => $option
				);
			}
			$this->db->insert_batch( 'poll_options', $insert );
		}
		
		$this->db->trans_complete();
		
		if( $this->db->trans_status() === TRUE )
		{
			return $pollID;
		}
		else
		{
			return FALSE;
		}
	}// create_poll()
	
	public function edit_draft_poll( $pollID, $Title, $maximumVotesPerUser, $verified_options, $Details, $accessMode )
	{
		if( count( $verified_options ) > self::MAX_OPTIONS_PER_POLL )
		{
			throw new InvalidArgumentException( '$verified_options is too large.' );
		}
		
		self::ensure_db_conn();
		
		$this->db->trans_start();
		
		$this->db->set( 'Title', $Title );
		$this->db->set( 'maximumVotesPerUser', $maximumVotesPerUser );
		$this->db->set( 'Details', $Details );
		$this->db->set( 'accessMode', intval($accessMode) );
		$this->db->where( 'pollID', $pollID );
		$this->db->where( 'Status', 'Draft' );
		$this->db->update( 'polls' );
		
		$this->db->where( 'pollID', $pollID );
		$this->db->delete( 'poll_options' );
		
		$insert = array();
		foreach( $verified_options as $optionID => $option )
		{
			$insert[] = array(
				'pollID' => $pollID,
				'optionID' => $optionID,
				'Description' => $option
			);
		}
		$this->db->insert_batch( 'poll_options', $insert );
		
		$this->db->trans_complete();
		
		return $this->db->trans_status();
	}// edit_draft_poll()
	
	public function add_vote( $pollID, $optionID, $UserID )
	{
		self::ensure_db_conn();
		
		$this->db->set( 'pollID', $pollID );
		$this->db->set( 'optionID', $optionID );
		$this->db->set( 'UserID', $UserID );
		return ( $this->db->insert( 'poll_votes' ) && $this->db->affected_rows() == 1 );
	}// add_vote()
	
	public function open_poll( $pollID )
	{
		self::ensure_db_conn();
		
		$this->db->set( 'Status', 'Open' );
		$this->db->where( 'pollID', $pollID );
		$valid_open_status = array( 'Draft', 'Closed' );
		$this->db->where_in( 'Status', $valid_open_status );
		return ($this->db->update( 'polls' ) && $this->db->affected_rows() == 1);
	}// open_poll()
	
	public function close_poll( $pollID )
	{
		self::ensure_db_conn();
		
		$this->db->set( 'Status', 'Closed' );
		$this->db->where( 'pollID', $pollID );
		$valid_close_status = array( 'Open' );
		$this->db->where_in( 'Status', $valid_close_status );
		return ($this->db->update( 'polls' ) && $this->db->affected_rows() == 1);
	}// close_poll()
	
	public function delete_poll( $pollID )
	{
		self::ensure_db_conn();
		
		$this->db->set( 'Status', 'Deleted' );
		$this->db->where( 'pollID', $pollID );
		$valid_delete_status = array( 'Draft', 'Closed' );
		$this->db->where_in( 'Status', $valid_delete_status );
		return ($this->db->update( 'polls' ) && $this->db->affected_rows() == 1);
	}// delete_poll()
	
	
}// Polls_model
?>