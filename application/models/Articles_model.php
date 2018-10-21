<?php
class Articles_model extends SF_Model {
	
	const ARTICLE_LIMIT = 7;
	const WHATS_NEW_LIMIT = 3;
	const FEATURED_LIMIT = 5;
	const HOT_LIMIT = 3;
	const IS_FEATURED = 1;
	
	const CONTENT_TAGS = '<p><strong><em><s><ol><ul><li><blockquote><h1><h2><h3><div><pre><hr><br><a>';
	
	
	public static function CATEGORIES()
	{
		return array(
			'Blog',
			'News',
			'Events',
			'Battlereports',
			'Podcast'
		);
	}// CATEGORIES()
	
	
	public function get_articles( $ArticleCategory = NULL, $Page = 0 )
	{
		if( $Page < 0 )
		{
			return FALSE;
		}
		
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'articles.*, users.CharacterName' );
		
		if( $ArticleCategory != NULL )
		{
			$this->db_ro->where( 'ArticleCategory', $ArticleCategory );
		}
		
		$this->db_ro->from( 'articles' );
		$this->db_ro->join( 'users', 'articles.UserID = users.UserID' );
		$this->db_ro->order_by( 'DatePublished', 'desc' );
		
		$query = $this->db_ro->get( '', self::ARTICLE_LIMIT, self::ARTICLE_LIMIT * $Page );
		return $query->result_array();
	}// get_articles()
	
	public function view_article( $ArticleID )
	{
		self::ensure_db_conn();
		
		if( $ArticleID == NULL )
		{
			return FALSE;
		}
		
		if( $this->db->platform() == 'mysqli' )
		{
			$this->db->query("SET SESSION sql_mode='ANSI_QUOTES'");
		}
		$this->db->set('"PageViews"', '"PageViews" + 1', FALSE);
		$this->db->where('ArticleID', $ArticleID);
		$this->db->update('articles');
		
		$this->db->join('users', 'articles.UserID = users.UserID');
		$query = $this->db->get_where('articles', array('ArticleID' => $ArticleID));
		return $query->row_array();
	}// view_article()
	
	public function get_whats_new()
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'articles.*, users.CharacterName' );
		$this->db_ro->from( 'articles' );
		$this->db_ro->join( 'users', 'articles.UserID = users.UserID' );
		
		$this->db_ro->order_by( 'DatePublished', 'desc' );
		$this->db_ro->limit( self::WHATS_NEW_LIMIT );
		
		$query = $this->db_ro->get();
		return $query->result_array();
	}// get_whats_new()
	
	public function get_featured()
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->select( 'articles.*, users.CharacterName' );
		$this->db_ro->from( 'articles' );
		$this->db_ro->join( 'users', 'articles.UserID = users.UserID' );
		
		$this->db_ro->where( 'Featured', self::IS_FEATURED );
		
		$this->db_ro->order_by( 'DatePublished', 'desc' );
		$this->db_ro->limit( self::FEATURED_LIMIT );
		
		$query = $this->db_ro->get();
		return $query->result_array();
	}// get_featured()
	
	public function get_hot()
	{
		self::ensure_db_ro_conn();
		
		$this->db_ro->from( 'articles' );
		
		$this->db_ro->order_by( 'PageViews', 'desc' );	// Should be smarter, normalise by article age?
		$this->db_ro->limit( self::HOT_LIMIT );
		
		$query = $this->db_ro->get();
		return $query->result_array();
	}// get_hot()
	
}// Articles_model
?>