<?php if( !defined('BASEPATH') ) exit ('No direct script access allowed');

/**
 * OAuth2 library. Partially redacted
 *
 * @author Daneel Trevize
 */

class LibOAuth2
{
	
	private $base_url;
	private $authorize_suffix;
	private $token_suffix;
	private $verify_suffix;
	private $scopes_behind_verify;
	private $scopes_field;
	
	private $LibRestAPI;
	
	public function __construct( $config )	// Validate config keys!
	{
		$this->base_url = $config['base_url'];
		$this->authorize_suffix = $config['authorize_suffix'];
		$this->token_suffix = $config['token_suffix'];
		$this->verify_suffix = array_key_exists( 'verify_suffix', $config ) ? $config['verify_suffix'] : '';
		$this->scopes_behind_verify = array_key_exists( 'scopes_behind_verify', $config ) ? $config['scopes_behind_verify'] : FALSE;
		$this->scopes_field = $config['scopes_field'];
		
		$api_name = $config['rest_api_name'];
        $CI =& get_instance();
		$CI->load->library( 'LibRestAPI', $CI->config->item($api_name), $api_name );
		$this->LibRestAPI =& $CI->$api_name;
	}// __construct()
	
	
	public function get_authentication_url( array $scopes, $state, array $params )
	{
		if( !array_key_exists( 'CLIENT_ID', $params ) || !array_key_exists( 'REDIRECT_URI', $params ) )
		{
			log_message( 'error', 'LibOAuth2: bad application details.' );
			return FALSE;
		}
		/*
		*	We assume the application state has been verified as to not be in the middle of
		*	a previous authentication flow, and that $state is sufficiently random.
		*/
		$fields = [
			"response_type" => "code", 
			"client_id" => $params['CLIENT_ID'],
			"redirect_uri" => $params['REDIRECT_URI'], 
			"scope" => implode( ' ', $scopes ),
			"state" => $state
		];
		$params = $this->LibRestAPI->build_params( $fields );

		$url = $this->get_authorize_url() .'?'. $params;
		
		return $url;
	}// get_authentication_url()
	
	private function get_authorize_url()
	{
		return $this->base_url . $this->authorize_suffix;
	}// get_authorize_url()
	
	private function get_token_url()
	{
		return $this->base_url . $this->token_suffix;
	}// get_token_url()
	
	private function get_verify_url()
	{
		return $this->base_url . $this->verify_suffix;
	}// get_verify_url()
	
	
	public function handle_callback( $local_state, $state, $code, $application )
	{
		if( $local_state != $state )
		{
			// Log an error about invalid state?
			log_message( 'error', 'LibOAuth2: invalid state.' );
			return FALSE;
		}

		$fields = array(
			'grant_type' => 'authorization_code',
			'code' => $code
		);
		if( array_key_exists( 'REPEAT_REDIRECT_URI', $application ) && $application['REPEAT_REDIRECT_URI'] === TRUE )
		{
			$fields['redirect_uri'] = $application['REDIRECT_URI'];
		}
		
		$access_response = $this->LibRestAPI->do_call( $this->get_token_url(), $application, $fields, 'POST' );
		$handled_access_response = $this->handle_access_response( $access_response );
		if( $handled_access_response === FALSE )
		{
			// Log an error about failure to acquire an access token?
			log_message( 'error', 'LibOAuth2: failure to acquire an access token during callback.' );
			return FALSE;
		}
		return $handled_access_response;
	}// handle_callback()
	
	private function handle_access_response( $access_response )
	{
		if( $access_response === FALSE )
		{
			return FALSE;
		}
		
		$access_decoded = json_decode( $access_response['body'], TRUE );
		if( !is_array($access_decoded) || !isset($access_decoded['access_token']) )
		{
			log_message( 'error', 'LibOAuth2: '. print_r( $access_response, TRUE) );
			return FALSE;
		}
		$access_token = $access_decoded['access_token'];
		$refresh_token = $access_decoded['refresh_token'];
		
		// Elevate scopes field, possibly involving elevating it from a verify token, renaming, and forming into an array
		if( $this->scopes_behind_verify )
		{
			$verify_decoded = $this->verify_access_token( $access_token );
			if( $verify_decoded === FALSE )
			{
				// Unexpected response, not a valid verification response.
				log_message( 'error', 'LibOAuth2: failure to verify token.' );
				return FALSE;
			}
			
			if( array_key_exists( $this->scopes_field, $verify_decoded ) )
			{
				$scopes = self::reformat_scopes( $verify_decoded[$this->scopes_field] );
				unset( $verify_decoded[$this->scopes_field] );
			}
		}
		else
		{
			$scopes = self::reformat_scopes( $access_decoded[$this->scopes_field] );
			unset( $access_decoded[$this->scopes_field] );
			
			$verify_decoded = array();
		}
		
		return array(
			'access_token' => $access_token,
			'expires_in' => ( isset($access_decoded['expires_in']) ? $access_decoded['expires_in'] : 20 * 60 ),	// 20 minutes default
			'refresh_token' => $refresh_token,
			'scopes' => $scopes,
			'verify_decoded' => $verify_decoded
		);
	}// handle_access_response()
	
	private function verify_access_token( $access_token )
	{
		$verify_response = $this->LibRestAPI->do_call( $this->get_verify_url(), $access_token );
		if( $verify_response === FALSE )
		{
			return FALSE;
		}
		
		$verify_decoded = json_decode( $verify_response['body'], TRUE );
		if( !is_array($verify_decoded) )
		{
			return FALSE;
		}
		
		return $verify_decoded;
	}// verify_access_token()
	
	private static function reformat_scopes( $scopes_string )
	{
		$scopes = array();
		if( $scopes_string !== '' )
		{
			$scopes = explode( ' ', $scopes_string );
		}
		return $scopes;
	}// reformat_scopes()
	
	
	public static function encrypt_token( $characterID, $token )
	{
		// Redacted
	}// encrypt_token()
	
	public static function decrypt_token( $characterID, $encrypted_token )
	{
		// Redacted
	}// decrypt_token()
	
	public function refresh_access_token( $refresh_token, $application )
	{
		$fields = array(
			'grant_type' => 'refresh_token',
			'refresh_token' => $refresh_token
		);
		
		$access_response = $this->LibRestAPI->do_call( $this->get_token_url(), $application, $fields, 'POST' );
		$handled_access_response = $this->handle_access_response( $access_response );
		if( $handled_access_response === FALSE )
		{
			// Log an error about failure to acquire an access token?
			log_message( 'error', 'LibOAuth2: failure to acquire an access token during refresh.' );
			return FALSE;
		}
		
		return $handled_access_response;
	}// refresh_access_token()
	
}// LibOAuth2
?>