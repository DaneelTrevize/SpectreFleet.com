<?php if( !defined('BASEPATH') ) exit ('No direct script access allowed');

/**
 * RBAC authorization library. Redacted
 *
 * @author Daneel Trevize
 */

class Authorization
{
	
	private $CI;
	
	public function __construct()
	{
		$this->CI =& get_instance();	// Assign the CodeIgniter object to a variable
		$this->CI->load->model('User_model');
		$this->CI->load->model('Command_model');
		$this->CI->load->model('Editor_model');
	}// __construct()
	
	public function get_user_permissions( $userID )
	{
		
	}// get_user_permissions()
	
	private function &calculate_permissions( $fleetRole, $editorRole, $adminRole )
	{
		
	}// calculate_permissions()
	
	private function evaluate_fleet_role( &$permissions, $fleetRole )
	{
		
	}// evaluate_fleet_role()
	
	private function evaluate_editor_role( &$permissions, $editorRole )
	{
		
	}// evaluate_editor_role()
	
	private function evaluate_admin_role( &$permissions, $adminRole )
	{
		
	}// evaluate_admin_role()
	
}// Authorization
?>