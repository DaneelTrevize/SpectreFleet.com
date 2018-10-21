<?php
if( !defined('BASEPATH') ) exit('No direct script access allowed');

class Authentication extends SF_Controller {
	
	const BCYRPT_MAX_LENGTH = 72;
	const PASSWORD_MIN_LENGTH = 8;
	
	
	const SSO_REGISTERING = 'register';
	const SSO_RETURNING = 'login';
	const SSO_RESETTING = 'reset';
	
	public function __construct()
	{
		parent::__construct();
		$this->load->library( 'form_validation' );
		$this->load->model( 'User_model' );
		$this->load->model( 'Command_model' );
		$this->load->model( 'CharacterID_model' );
		$this->load->model( 'Discord_model' );
		$this->config->load('ccp_api');
		$this->load->library( 'LibOAuth2', $this->config->item('oauth_eve'), 'oauth_eve' );
		$this->load->library( 'Authorization' );
	}
	
	
	public function index()	// Perform login against database, rather than SSO
	{
		
	}// index()
	
	function _password_limits( $password, $enforce_min_length )
	{
		
	}// _password_limits()
	
	private function login($username, $password)
	{
		
	}// login()
	
	
	public function set_SSO_state()		// Local redirect to first set SSO state, to prevent XSRF
	{
		
	}// set_SSO_state()
	
	public function _valid_SSO_action( $action )
	{
		
	}// _valid_SSO_action()
	
	
	public function SSO()	// All SSO callbacks from CCP start here
	{
		
	}// SSO()
	
	private function handle_sso_state( $state, $verify_decoded )
	{
		
	}// handle_sso_state()
	
	
	private function handle_sso_returning( $verify_decoded )
	{
		
	}// handle_sso_returning()
	
	
	private function handle_sso_registering( $verify_decoded )
	{
		
	}// handle_sso_registering()
	
	
	private function handle_sso_resetting( $verify_decoded )
	{
		
	}// handle_sso_resetting()
	
	
	private function after_verification( $user_data, $CharacterID=NULL, $CharacterOwnerHash=NULL )	// Either login method ends here on successful verification
	{
		
	}// after_verification()
	
	
	public function logout()	// Protected from CSRF by not being a simple GET URL
	{
		
	}// logout()
	
	/*
	public function register_spectre()
	{
		
	}// register_spectre()
	*/
	public function register_SSO()
	{
		
	}// register_SSO()
	
	function _available_username( $username )
	{
		
	}// _available_username()
	
	
	public function reset_password_SSO()
	{
		
	}// reset_password_SSO()
	
	
	public function change_password()
	{
		
	}// change_password()
	
	function _check_password( $oldpassword )
	{
		
	}// _check_password()
	
	
	public function clear_output_cache()
	{
		
	}// clear_output_cache()
	
	public function delete_all_sessions()
	{
		
	}// delete_all_sessions()
	
	public function reset_users_password()
	{
		
	}// reset_users_password()
	
	public function invite_new_user()
	{
		
	}// invite_new_user()
	
}// Authentication
?>