<?php if( !defined('BASEPATH') ) exit ('No direct script access allowed');

/**
 * OAuth state model-turned-library.
 *
 * @author Daneel Trevize
 */

class LibOAuthState
{
	
	private $key;
	
	public function __construct( $params )
	{
		$this->key = $params['key'];
	}// __construct()
	
	
	public function expect_login( $redirect, array $requested_scopes, $application )
	{
		if( $redirect == NULL || $application == NULL )
		{
			throw new InvalidArgumentException( 'Arguments should not be null.' );
		}
		
		$_SESSION['oauth'][$this->key]['redirect'] = $redirect;
		
		$_SESSION['oauth'][$this->key]['requested_scopes'] = $requested_scopes;
		
		$_SESSION['oauth'][$this->key]['application'] = $application;
		
	}// expect_login()
	
	
	public function expecting_login()
	{
		return isset( $_SESSION['oauth'][$this->key]['redirect'] );
	}// expecting_login()
	
	public function get_requested_scopes()
	{
		return isset( $_SESSION['oauth'][$this->key]['requested_scopes'] ) ? $_SESSION['oauth'][$this->key]['requested_scopes'] : array();
	}// get_requested_scopes()
	
	public function get_application_details()
	{
		return isset( $_SESSION['oauth'][$this->key]['application'] ) ? $_SESSION['oauth'][$this->key]['application'] : NULL;
	}// get_application_details()
	
	public function setup_login_state()
	{
		$state = bin2hex( openssl_random_pseudo_bytes(32) );	//	32 bytes of entropy, 64 hex chars, avoid risk of early null byte
		
		$_SESSION['oauth'][$this->key]['auth_state'] = $state;
		
		return $state;
	}// setup_login_state()
	
	public function get_login_state()
	{
		return isset( $_SESSION['oauth'][$this->key]['auth_state'] ) ? $_SESSION['oauth'][$this->key]['auth_state'] : NULL;
	}// get_login_state()
	
	public function finish_login( $response )
	{
		self::set_tokens( $response );
		
		unset( $_SESSION['oauth'][$this->key]['requested_scopes'] );
		unset( $_SESSION['oauth'][$this->key]['auth_state'] );
		$location = $_SESSION['oauth'][$this->key]['redirect'];
		unset( $_SESSION['oauth'][$this->key]['redirect'] );
		return $location;
	}// finish_login()
	
	public function set_tokens( $response, $application = NULL )
	{
		$_SESSION['oauth'][$this->key]['refresh_token'] = $response['refresh_token'];
		$_SESSION['oauth'][$this->key]['auth_token'] = $response['access_token'];
		$_SESSION['oauth'][$this->key]['scopes'] = $response['scopes'];
		$_SESSION['oauth'][$this->key]['expiry'] = time() + $response['expires_in'];
		if( $application !== NULL )
		{
			$_SESSION['oauth'][$this->key]['application'] = $application;
		}
	}// set_tokens()
	
	public function get_refresh_token()
	{
		return isset( $_SESSION['oauth'][$this->key]['refresh_token'] ) ? $_SESSION['oauth'][$this->key]['refresh_token'] : NULL;
	}// get_refresh_token()
	
	public function get_auth_token()
	{
		return isset( $_SESSION['oauth'][$this->key]['auth_token'] ) ? $_SESSION['oauth'][$this->key]['auth_token'] : NULL;
	}// get_auth_token()
	
	public function get_scopes()
	{
		return isset( $_SESSION['oauth'][$this->key]['scopes'] ) ? $_SESSION['oauth'][$this->key]['scopes'] : array();
	}// get_auth_token()
	
	public function ensure_fresh_token( $LibOAuth2 )
	{
		if( $this->get_auth_token() === NULL )
		{
			return FALSE;
		}
		if( time() >= $_SESSION['oauth'][$this->key]['expiry'] )
		{
			$response = $LibOAuth2->refresh_access_token( self::get_refresh_token(), self::get_application_details() );
			if( $response === FALSE )
			{
				// Error on bad access token refreshing
				log_message( 'error', 'LibOAuthState: failure to refresh access token.' );
				return FALSE;
			}
			
			// Check Scopes match requested_scopes?
			
			self::set_tokens( $response );
		}
		return TRUE;
	}// ensure_fresh_token()
	
	public function logout()
	{
		unset( $_SESSION['oauth'][$this->key] );
	}// logout()
	
}// LibOAuthState
?>