<?php
class OAuth extends SF_Controller {
	
	
	public function __construct()
	{
		parent::__construct();
		$this->config->load('ccp_api');
		$this->load->library( 'LibOAuthState', array('key'=>'eve'), 'OAuth_model' );
		$this->load->library( 'LibOAuth2', $this->config->item('oauth_eve'), 'oauth_eve' );
	}// __construct()
	
	
	public function login()		// Set up local state (for XSRF prevention) before calling external CCP URL
	{
		if( !$this->OAuth_model->expecting_login() )
		{
			$this->session->set_flashdata( 'flash_message', 'Invalid OAuth flow. Please ensure any bookmarks are still valid.' );
			log_message( 'error', 'OAuth controller: Invalid OAuth flow. Not expecting login().' );
			redirect('portal', 'location');
		}
		
		$scopes = $this->OAuth_model->get_requested_scopes();
		
		$state = $this->OAuth_model->setup_login_state();
		
		$application = $this->OAuth_model->get_application_details();
		
		$url = $this->oauth_eve->get_authentication_url( $scopes, $state, $application );
		
		redirect( $url, 'location' );
	}// login()
	
	public function verify()	// Registered callback URL for ESI
	{
		$state = $this->input->get( 'state' );
		$code = $this->input->get( 'code' );
		
		$local_state = $this->OAuth_model->get_login_state();
		if( $local_state === NULL )
		{
			$this->session->set_flashdata( 'flash_message', 'Expired OAuth state. Please avoid navigating Back during OAuth actions.' );
			log_message( 'error', 'OAuth controller: Expired OAuth state. $state:'. $state . ', $code:' .$code );
			redirect( 'portal', 'location' );
		}
		
		if( $state == NULL || $state == '' || $code == NULL || $code == '' )
		{
			// Redirect to login?
			$this->session->set_flashdata( 'flash_message', 'Invalid OAuth flow. Please ensure any bookmarks are still valid.' );
			log_message( 'error', 'OAuth controller: Invalid OAuth flow. $state:'. $state . ', $code:' .$code );
			redirect( 'portal', 'location' );
		}
		
		$application = $this->OAuth_model->get_application_details();
		
		$response = $this->oauth_eve->handle_callback( $local_state, $state, $code, $application );
		if( $response === FALSE )
		{
			// Error on bad token verifying
			$this->session->set_flashdata( 'flash_message', 'OAuth login failed.' );
			log_message( 'error', 'OAuth controller: failure to verify access token' );
			redirect('portal', 'location');
		}
		
		$location = $this->OAuth_model->finish_login( $response );
		
		redirect( $location, 'location' );
		
	}// verify()
	
}// OAuth
?>