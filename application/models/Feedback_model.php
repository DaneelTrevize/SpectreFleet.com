<?php
Class Feedback_model extends SF_Model {
	
	public static function FEEDBACK_SEARCH_FIELDS()
	{
		$fields = array(
			'FeedbackID' => 'integer',
			'FleetFC' => 'string',
			'UserID' => 'integer',
			'Date' => 'date',
			'Details' => 'string'//,
			//'Score' => 'integer'	// Need filter/cutoff low and high scores, not just one?
		);
		return $fields;
	}// FEEDBACK_SEARCH_FIELDS()
	
	public static function FEEDBACK_ORDERTYPES()
	{
		$orderTypes = array(
			//'FeedbackID' => 'Feedback ID', // Redundant w.r.t. datetime stamp?
			'Date' => 'Fleet Date',
			'FleetFC' => 'FC\'s Name',
			'Score' => 'Score'
		);
		return $orderTypes;
	}// FEEDBACK_ORDERTYPES()
	
	public static function FEEDBACK_PAGESIZES()
	{
		$pageSizes = array(
			10,
			20,
			50,
			100
		);
		return $pageSizes;
	}// FEEDBACK_PAGESIZES()
	
	
	public function __construct()
	{
		$this->load->library('dynamic_search');
	}// __construct()
	
	
	public function record_feedback( $UserID, $Feedback, $Score, $Date )
	{
		self::ensure_db_conn();
		
		$this->db->set( 'UserID', $UserID );
		$this->db->set( 'Feedback', $Feedback );
		$this->db->set( 'Score', $Score );
		$this->db->set( 'Date', $Date );
		if ( $this->db->insert( 'feedback' ) && $this->db->affected_rows() == 1 )
		{
			return $this->db->insert_id();
		}
		else
		{
			return FALSE;
		}
	}// record_feedback()
	
	public function get_all_feedback( $validated_search_fields, $orderType = 'Date', $orderSort = 'DESC', $page=0, $pageSize=10 )
	{
		// Need to convert UserID back into disambiguous feedback.UserID field.
		if( array_key_exists( 'UserID', $validated_search_fields ) )
		{
			$validated_search_fields['feedback.UserID'] = $validated_search_fields['UserID'];
			unset( $validated_search_fields['UserID'] );
		}
		
		// Another need to alias a field for use in the WHERE clause rather than just the SELECT
		if( array_key_exists( 'Details', $validated_search_fields ) )
		{
			$validated_search_fields['Feedback'] = $validated_search_fields['Details'];
			unset( $validated_search_fields['Details'] );
		}
		
		// Another need to alias a field for use in the WHERE clause rather than just the SELECT
		if( array_key_exists( 'FleetFC', $validated_search_fields ) )
		{
			$validated_search_fields['CharacterName'] = $validated_search_fields['FleetFC'];
			unset( $validated_search_fields['FleetFC'] );
		}
		if( $orderType == 'FleetFC' )
		{
			$orderType = 'CharacterName';
		}
		
		self::ensure_db_ro_conn();
		
		$this->dynamic_search->build_search_conditions( $this->db_ro, $validated_search_fields );
		
		$this->db_ro->select( 'feedback.*, users.CharacterName, users.CharacterID' );
		$this->db_ro->from( 'feedback' );
		$this->db_ro->join( 'users', 'feedback.UserID = users.UserID', 'left' );	// Make doing join conditional on FC Name being in search or order fields?
		$this->db_ro->order_by( $orderType, $orderSort );
		$this->db_ro->order_by( 'FeedbackID', 'DESC' );		// For consistent ordering
		$query = $this->db_ro->get( '', $pageSize, $pageSize * $page );
		// If we LIMIT to 1 row extra, we can determine and indicate if there's a next page or not...
		
		return $query->result_array();
	}// get_all_feedback()
	
}// Feedback_model
?>