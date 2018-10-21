<?php if( !defined('BASEPATH') ) exit ('No direct script access allowed');

/**
 * Spectre Fleet extended Model core class.
 *
 * @author Daneel Trevize
 */

class SF_Model extends CI_Model {
	
	
	const DATETIME_DB_FORMAT = 'Y-m-d H:i:se';
	const DATETIME_DB_FORMAT_NOMS = 'Y-m-d H:i:s';
	
	
	public function __construct()
	{
		$this->UTC_DTZ = new DateTimeZone( 'UTC' );
	}// __construct()
	
	
	protected function dtz_to_db_text( $datetime )
	{
		return $datetime->format( self::DATETIME_DB_FORMAT );
	}// dtz_to_db_text()
	
	protected function eve_now_dtz()
	{
		return new DateTime( 'now', $this->UTC_DTZ );
	}// eve_now_dtz()
	
	protected function eve_now_dtz_db_text()
	{
		return $this->dtz_to_db_text( $this->eve_now_dtz() );
	}// eve_now_dtz_db_text()
	
	protected function SF_now_dtz_db_text()
	{
		$datetime = $this->eve_now_dtz();
		return $datetime->format( self::DATETIME_DB_FORMAT_NOMS );
	}// SF_now_dtz_db_text()
	
	protected function ensure_db_conn()
	{
		$CI =& get_instance();
		if( !isset( $CI->db ) )
		{
			$CI->db = $this->load->database( 'default', TRUE );
		}
	}// ensure_db_conn()
	
	protected function ensure_db_ro_conn()
	{
		$CI =& get_instance();
		if( !isset( $CI->db_ro ) )
		{
			$CI->db_ro = $this->load->database( 'readonly_limited', TRUE );
		}
	}// ensure_db_ro_conn()

}// SF_Model
?>