<?php if( !defined('BASEPATH') ) exit ('No direct script access allowed');

/**
 * OAuth2 REST API Client library.
 *
 * @author Daneel Trevize
 */

class LibRestAPI
{
	
	private $user_agent;
	private $connect_timeout;
	private $call_timeout;
	private $url_handle_map;
	
	public function __construct( $config )	// Validate config keys!
	{
		$this->user_agent = $config['user_agent'];
		$this->connect_timeout = $config['connect_timeout'];
		$this->call_timeout = $config['call_timeout'];
		$this->url_handle_map = array();
	}// __construct()
	
	
	public static function build_params( array $fields )
	{
		$string = '';
		foreach( $fields as $field => $value )
		{
			$string .= $string == '' ? '' : '&';
			$string .= "$field=" . rawurlencode( $value );
		}
		return $string;
	}// build_params()
	
	public function do_call( $url, $application_or_authorization, array $fields = array(), $callType = 'GET' )
	{
		// Either an array containing the private application values, or an authorization token
		if( is_array( $application_or_authorization ) )
		{
			if( !array_key_exists( 'CLIENT_ID', $application_or_authorization ) || !array_key_exists( 'CLIENT_SECRET', $application_or_authorization ) )
			{
				log_message( 'error', 'LibRestAPI: bad application details.' );
				return FALSE;
			}
			if( array_key_exists( 'BOT_TOKEN', $application_or_authorization ) )
			{
				// Just for Discord atm
				$header = 'Authorization: Bot ' . $application_or_authorization['BOT_TOKEN'];
			}
			else
			{
				$header = 'Authorization: Basic ' . base64_encode( $application_or_authorization['CLIENT_ID'] . ':' . $application_or_authorization['CLIENT_SECRET'] );
			}
		}
		else
		{
			$header = 'Authorization: Bearer ' . $application_or_authorization;
		}
		$headers = [$header];
		
		// Always declare we accept JSON response?
		$headers[] = 'Accept: application/json';
		
		$common_url = self::parse_for_common_url( $url );
		if( $common_url === FALSE )
		{
			log_message( 'error', 'LibRestAPI: failed to parse url: ' .$url );
			return FALSE;
		}
		
		if( array_key_exists( $common_url, $this->url_handle_map ) )
		{
			log_message( 'debug', 'LibRestAPI: re-using handler for url: ' .$url );
			$ch = $this->url_handle_map[$common_url];
			curl_reset( $ch );
		}
		else
		{
			$ch = curl_init();
			if( $ch === FALSE )
			{
				// Log an error about cURL failing?
				log_message( 'error', 'LibRestAPI: cURL failed to init()');
				return FALSE;
			}
			
			$this->url_handle_map[$common_url] = $ch;
		}
		
		curl_setopt( $ch, CURLOPT_USERAGENT, $this->user_agent );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, TRUE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
		
		curl_setopt( $ch, CURLOPT_HEADER, TRUE );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $this->connect_timeout );
		curl_setopt( $ch, CURLOPT_TIMEOUT, $this->call_timeout );
		
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $callType );
		
		switch( $callType )
		{
			case 'GET':
				if( !empty($fields) )
				{
					$fieldsString = self::build_params( $fields );
					$url .= '?'. $fieldsString;
				}
				break;
			case 'POST':
				$headers[] = 'Content-Type: application/x-www-form-urlencoded';
				$fieldsString = self::build_params( $fields );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $fieldsString );
				break;
			case 'POST_JSON':	// Fake verb to indicate POSTing JSON rather than form fields
				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'POST' );
				$headers[] = 'Content-Type: application/json';
				if( empty($fields) )
				{
					$fieldsString = json_encode( (object) NULL );	// Force an empty Dict to be created, rather than List
					// Investigate JSON_FORCE_OBJECT?
				}
				else
				{
					$fieldsString = json_encode( $fields, JSON_UNESCAPED_SLASHES );
				}
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $fieldsString );
				break;
			case 'PUT':
			case 'DELETE':
				$headers[] = 'Content-Type: application/json';
				$fieldsString = json_encode( $fields, JSON_UNESCAPED_SLASHES );
				curl_setopt( $ch, CURLOPT_INFILESIZE, strlen($fieldsString) );	// Instead of headers[] = 'Content-Length: ' . strlen($fieldsString); ?
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $fieldsString );
				break;
			case 'OPTIONS':
				break;
			default:
				// Log an error about invalid callType?
				log_message( 'error', 'LibRestAPI: invalid callType:'. $callType );
				return FALSE;
		}
		curl_setopt( $ch, CURLOPT_URL, $url );
		log_message( 'debug', 'LibRestAPI: URL: ' . $url );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		//log_message( 'error', 'LibRestAPI: headers:'. print_r( $headers, TRUE) );
		//log_message( 'error', 'LibRestAPI: fieldsString:'. $fieldsString );
		
		$response = curl_exec( $ch );
		
		if( $response === FALSE )
		{
			log_message( 'error', 'LibRestAPI: exec failed: '. curl_errno( $ch ) .': '. curl_error( $ch ) .'. URL: '. $url );
			
			// Probably shouldn't try to reuse handlers that have failed so badly
			unset( $this->url_handle_map[$common_url] );
			curl_close( $ch );
			
			return FALSE;
		}
		
		$header_size = curl_getinfo( $ch, CURLINFO_HEADER_SIZE );
		$headers_section = substr( $response, 0, $header_size );
        $result['headers'] = explode( "\n", rtrim($headers_section) );	// Better parse header, split on first : per line?
        $result['body'] = substr( $response, $header_size );
        $result['response_code'] = curl_getinfo( $ch, CURLINFO_RESPONSE_CODE );
		
		foreach( $result['headers'] as $header )
		{
			if( substr( $header, 0, 8 ) === 'Warning:' )
			{
				log_message( 'error', 'LibRestAPI: Warning header in response to calling "'.$url.'": '. $header );
			}
		}
		
		return $result;
	}// do_call()
	
	private static function parse_for_common_url( $url )
	{
		$parsed_url = parse_url( $url );
		if( $parsed_url === FALSE )
		{
			return FALSE;
		}
		
		$scheme   = isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] . '://' : '';
		$host     = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';
		$port     = isset( $parsed_url['port'] ) ? ':' . $parsed_url['port'] : '';
		$user     = isset( $parsed_url['user'] ) ? $parsed_url['user'] : '';
		$pass     = isset( $parsed_url['pass'] ) ? ':' . $parsed_url['pass']  : '';
		$pass     = ($user || $pass) ? "$pass@" : '';
		$path     = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '';
		$query    = isset( $parsed_url['query'] ) ? '?' . $parsed_url['query'] : '';
		$fragment = isset( $parsed_url['fragment'] ) ? '#' . $parsed_url['fragment'] : '';
		
		return "$scheme$host$port";
	}// parse_for_common_url()
	
}// LibRestAPI
?>