<?php
Class Editor_model extends SF_Model {
	
	/*
	
		Current State	| Possible Action	| New State		| Permitted by
		------------------------------------------------------------------
						| Create			| Draft			| Submitter
		Draft			| Edit				| Draft			| Owner
		Draft			| Delete			| Deleted		| Owner
		Deleted			| 					| 				| 
		Draft			| Submit			| Submitted		| Owner
		
		Submitted		| Edit				| Submitted		| Owner, Editors
		Submitted		| Retract			| Draft			| Owner
		Submitted		| Put to Review		| In Review		| Owner, Editors
		
		In Review		| Published			| Published		| Publisher
		Published		| Edit?				| Published?	| Publisher?
		Published		| Withdraw?			| Withdrawn?	| Publisher?
		In Review		| Reject			| Rejected		| Publisher
		
		Rejected		| Edit				| Draft			| Owner
		Rejected		| Delete			| Deleted		| Owner
		
		
		Drafts and Rejects being private to the submitting editor.
		In Review being read-only w.r.t. article content, category, etc.
		We can still keep the contents of Deleted submissions, and just hide such entries from usual results.
		
	*/
	
	const ROLE_PUBLISHER = 1;
	const ROLE_EDITOR = 2;
	const ROLE_SUBMITTER = 3;
	const ROLE_MEMBER = 4;
	const DEFAULT_EDITOR_ROLE = self::ROLE_MEMBER;
	
	public static function ROLE_NAMES()
	{
		return array(
			self::ROLE_PUBLISHER => 'Publisher',
			self::ROLE_EDITOR => 'Editor',
			self::ROLE_SUBMITTER => 'Submitter',
			self::ROLE_MEMBER => 'Member'
		);
	}// ROLE_NAMES()
	
	
	public function get_sorted_editors()
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select('UserID, CharacterName, Editor, CharacterID');
		$this->db_ro->from('users');
		$this->db_ro->where_in('Editor', array( self::ROLE_PUBLISHER, self::ROLE_EDITOR, self::ROLE_SUBMITTER ) );
		$this->db_ro->order_by('Editor', 'asc');		// Assumption that ascending numerical value is descending role significance.
		$this->db_ro->order_by('CharacterName', 'asc');
		$query = $this->db_ro->get();
		
		return $query->result_array();
	}// get_sorted_editors()
	
	public function get_draft_submissions( $UserID )
	{
		if( $UserID === NULL )
		{
			throw new InvalidArgumentException( '$UserID should not be null' );
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'SubmissionID, ArticleName, ArticleDescription, ArticleCategory, ArticleContent, ArticlePhoto, submissions.UserID, Username, Status' );
		$this->db_ro->from( 'submissions' );
		$this->db_ro->join( 'users', 'submissions.UserID = users.UserID' );
		
		$this->db_ro->group_start();
		$this->db_ro->where( 'submissions.UserID', $UserID );
		$this->db_ro->group_start();
		$this->db_ro->where( 'Status', 'Draft' );
		$this->db_ro->or_where( 'Status', 'Rejected' );
		$this->db_ro->group_end();
		$this->db_ro->group_end();
		
		$this->db_ro->order_by( 'SubmissionID', 'desc' );
		$query = $this->db_ro->get();
		return $query->result_array();
	}// get_draft_submissions()
	
	public function get_submitted_submissions( $UserID = NULL )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'SubmissionID, ArticleName, ArticleDescription, ArticleCategory, ArticleContent, ArticlePhoto, submissions.UserID, Username, Status' );
		$this->db_ro->from( 'submissions' );
		$this->db_ro->join( 'users', 'submissions.UserID = users.UserID' );
		
		$this->db_ro->where( 'Status', 'Submitted' );
		
		if( $UserID != NULL )
		{
			$this->db_ro->where( 'submissions.UserID', $UserID );
		}
		
		$this->db_ro->order_by( 'SubmissionID', 'desc' );
		$query = $this->db_ro->get();
		return $query->result_array();
	}// get_submitted_submissions()
	
	public function get_publishable_submissions( $UserID = NULL )
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'SubmissionID, ArticleName, ArticleDescription, ArticleCategory, ArticleContent, ArticlePhoto, submissions.UserID, Username, Status' );
		$this->db_ro->from( 'submissions' );
		$this->db_ro->join( 'users', 'submissions.UserID = users.UserID' );
		
		$this->db_ro->where( 'Status', 'Ready for Review' );
		
		if( $UserID != NULL )
		{
			$this->db_ro->where( 'submissions.UserID', $UserID );
		}
		
		$this->db_ro->order_by( 'SubmissionID', 'desc' );
		$query = $this->db_ro->get();
		return $query->result_array();
	}// get_publishable_submissions()
	
	public function get_submission( $SubmissionID )
	{
		if( $SubmissionID === NULL )
		{
			throw new InvalidArgumentException( '$SubmissionID should not be null' );
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select('SubmissionID, ArticleName, ArticleDescription, ArticleCategory, ArticleContent, ArticlePhoto, UserID, Status');
		$this->db_ro->from( 'submissions' );
		$this->db_ro->where( 'SubmissionID', $SubmissionID );
		$query = $this->db_ro->get();
		
		if( $query->num_rows() == 1 )
		{
			return $query->row_array();
		}
		else
		{
			return FALSE;
		}
	}// get_submission()
	
	
	public function create_article( $ArticleName, $ArticleDescription, $ArticleCategory, $ArticleContent, $ArticlePhoto, $UserID )
	{
		self::ensure_db_conn();
		
		$this->db->set( 'ArticleName', $ArticleName );
		$this->db->set( 'ArticleDescription', $ArticleDescription );
		$this->db->set( 'ArticleCategory', $ArticleCategory );
		$this->db->set( 'ArticleContent', $ArticleContent );
		$this->db->set( 'ArticlePhoto', $ArticlePhoto );
		$this->db->set( 'UserID', $UserID );
		$this->db->set( 'Status', 'Draft' );
		if( $this->db->insert( 'submissions' ) && $this->db->affected_rows() == 1 )
		{
			return TRUE;//$this->db->insert_id( 'submissions_SubmissionID_seq' );
		}
		else
		{
			return FALSE;
		}
	}// create_article()
	
	public function edit_article( $SubmissionID, $ArticleName, $ArticleDescription, $ArticleCategory, $ArticleContent, $ArticlePhoto )
	{
		$Article = array(
			'ArticleName' => $ArticleName,
			'ArticleDescription' => $ArticleDescription,
			'ArticleCategory' => $ArticleCategory,
			'ArticleContent' => $ArticleContent,
			'ArticlePhoto' => $ArticlePhoto
		);
		
		self::ensure_db_conn();
		
		$this->db->where( 'SubmissionID', $SubmissionID );
		$valid_edit_status = array( 'Draft', 'Submitted', 'Rejected' );
		$this->db->where_in( 'Status', $valid_edit_status );
		return ($this->db->update( 'submissions', $Article ) && $this->db->affected_rows() == 1);
	}// edit_article()
	
	public function submit_submission( $SubmissionID )
	{
		if( $SubmissionID === NULL )
		{
			throw new InvalidArgumentException( '$SubmissionID should not be null' );
		}
		
		self::ensure_db_conn();
		
		$status = array( 'Status' => 'Submitted' );
		$this->db->where( 'SubmissionID', $SubmissionID );
		$this->db->where( 'Status', 'Draft' );
		return ($this->db->update( 'submissions', $status ) && $this->db->affected_rows() == 1);
	}// submit_submission()
	
	public function retract_submission( $SubmissionID )
	{
		if( $SubmissionID === NULL )
		{
			throw new InvalidArgumentException( '$SubmissionID should not be null' );
		}
		
		self::ensure_db_conn();
		
		$status = array( 'Status' => 'Draft' );
		$this->db->where( 'SubmissionID', $SubmissionID );
		$this->db->where( 'Status', 'Submitted' );
		return ($this->db->update( 'submissions', $status ) && $this->db->affected_rows() == 1);
	}// retract_submission()
	
	public function promote_submission( $SubmissionID )
	{
		if( $SubmissionID === NULL )
		{
			throw new InvalidArgumentException( '$SubmissionID should not be null' );
		}
		
		self::ensure_db_conn();
		
		$status = array( 'Status' => 'Ready for Review' );
		$this->db->where( 'SubmissionID', $SubmissionID );
		$this->db->where( 'Status', 'Submitted' );
		return ($this->db->update( 'submissions', $status ) && $this->db->affected_rows() == 1);
	}// promote_submission()
	
	public function publish_submission( $SubmissionID )
	{
		if( $SubmissionID === NULL )
		{
			throw new InvalidArgumentException( '$SubmissionID should not be null' );
		}
		
		self::ensure_db_conn();
		
		/*
		$this->db->trans_start();
		
		$this->db->select( 'ArticleName, ArticleDescription, ArticleCategory, ArticleContent, ArticlePhoto' );
		$this->db->from( 'submissions' );
		$this->db->where( 'SubmissionID', $SubmissionID );
		$this->db->where( 'Status', 'Ready for Review' );
		$query = $this->db->get();
		$NewArticle = $query->row_array();
		
		$this->db->set( $NewArticle );
		$this->db->set( 'PageViews', 0 );
		$this->db->set( 'DatePublished', self::SF_now_dtz_db_text() );
		$this->db->insert( 'articles' );	// We need to reconcile the duplication between Articles and Submissions
		// We should probably log who published the article, and/or who makes any changes to the status or content of any submissions/articles
		
		$this->db->set( 'Status', 'Published' );
		$this->db->where( 'SubmissionID', $SubmissionID );
		$this->db->where( 'Status', 'Ready for Review' );	// Avoids concurrent (rejection leading to = edits) and publishing
		$this->db->update( 'submissions' );
		
		$this->db->trans_complete();
		
		return $this->db->trans_status();
		*/
		
		// This doesn't use a transaction...
		$NewArticle = $this->get_submission( $SubmissionID );
		if( $NewArticle != FALSE && $NewArticle['Status'] === 'Ready for Review' )
		{
			unset($NewArticle['SubmissionID']);
			unset($NewArticle['Username']);
			unset($NewArticle['Status']);
			
			$this->db->set( $NewArticle );
			$this->db->set( 'PageViews', 0 );
			$this->db->set( 'DatePublished', self::SF_now_dtz_db_text() );
			// We need to reconcile the duplication between Articles and Submissions
			if( $this->db->insert( 'articles' ) && $this->db->affected_rows() == 1 )
			{
				//$articleID = $this->db->insert_id( 'articles_ArticleID_seq' );
				
				// We should probably log who published the article, and/or who makes any changes to the status or content of any submissions/articles
				
				$status = array( 'Status' => 'Published' );
				$this->db->where( 'SubmissionID', $SubmissionID );
				if( $this->db->update( 'submissions', $status ) && $this->db->affected_rows() == 1 )
				{
					return TRUE;//$articleID;
				}
				else
				{
					return FALSE;
				}
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}// publish_submission()
	
	public function reject_submission( $SubmissionID )
	{
		if( $SubmissionID === NULL )
		{
			throw new InvalidArgumentException( '$SubmissionID should not be null' );
		}
		
		self::ensure_db_conn();
		
		$status = array( 'Status' => 'Rejected' );
		$this->db->where( 'SubmissionID', $SubmissionID );
		$this->db->where( 'Status', 'Ready for Review' );
		return ($this->db->update( 'submissions', $status ) && $this->db->affected_rows() == 1);
	}// reject_submission()
	
	public function delete_submission( $SubmissionID )
	{
		if( $SubmissionID === NULL )
		{
			throw new InvalidArgumentException( '$SubmissionID should not be null' );
		}
		
		self::ensure_db_conn();
		
		$status = array( 'Status' => 'Deleted' );
		$this->db->where( 'SubmissionID', $SubmissionID );
		$valid_edit_status = array( 'Draft', 'Rejected' );
		$this->db->where_in( 'Status', $valid_edit_status );
		return ($this->db->update( 'submissions', $status ) && $this->db->affected_rows() == 1);
	}// delete_submission()
	
}// Editor_model
?>