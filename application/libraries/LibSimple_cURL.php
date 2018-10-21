<?php if( !defined('BASEPATH') ) exit ('No direct script access allowed');

/**
 * Simple cURL library.
 *
 * @author Daneel Trevize
 */

class LibSimple_cURL
{
	
	const CONNECT_TIMEOUT = 5;
	const CALL_TIMEOUT = 20;
	
	private $CI;
	
	private $user_agent;
	private $url_handle_map;
	
	public function __construct()
	{
		$this->CI =& get_instance();	// Assign the CodeIgniter object to a variable
		
		$this->user_agent = 'SpectreFleet';
		$this->url_handle_map = array();
	}// __construct()
	
	
	public function do_call( $url, $fields = array(), $RFC3986 = TRUE, $callType = 'GET' )
	{
		$ch = FALSE;
		
		$common_url = self::parse_for_common_url( $url );
		if( $common_url === FALSE )
		{
			log_message( 'error', 'LibSimple_cURL: failed to parse url: ' .$url );
			return FALSE;
		}
		
		if( array_key_exists( $common_url, $this->url_handle_map ) )
		{
			log_message( 'debug', 'LibSimple_cURL: re-using handler for url: ' .$url );
			$ch = $this->url_handle_map[$common_url];
			curl_reset( $ch );
		}
		else
		{
			$ch = curl_init();
			if( $ch === FALSE )
			{
				log_message( 'error', 'LibSimple_cURL: cURL failed to init()');
				return FALSE;
			}
			
			$this->url_handle_map[$common_url] = $ch;
		}
		
		curl_setopt( $ch, CURLOPT_USERAGENT, $this->user_agent );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, TRUE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
		
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT );
		curl_setopt( $ch, CURLOPT_TIMEOUT, self::CALL_TIMEOUT );
		
		$headers = array();
		
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $callType );
		
		switch( $callType )
		{
			case 'GET':
				if( !empty($fields) )
				{
					$fieldsString = self::build_params( $fields, $RFC3986 );
					$url .= '?'. $fieldsString;
				}
				break;
			case 'POST':
				$fieldsString = self::build_params( $fields, $RFC3986 );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $fieldsString );
				break;
			case 'POST_JSON':	// Fake verb to indicate POSTing JSON rather than form fields
				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'POST' );
				$headers[] = 'Content-Type: application/json';
				if( empty($fields) )
				{
					$fieldsString = json_encode( (object) NULL );	// Force an empty Dict to be created, rather than List
				}
				else
				{
					$fieldsString = json_encode( $fields, JSON_UNESCAPED_SLASHES );
				}
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $fieldsString );
				break;
			default:
				// Log an error about invalid callType?
				log_message( 'error', 'LibSimple_cURL: invalid callType:'. $callType );
				return FALSE;
		}
		
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		
		$result = curl_exec( $ch );
		
		if( $result === FALSE )
		{
			log_message( 'error', 'LibSimple_cURL: '. curl_error($ch) );
		}
		$return = curl_getinfo( $ch, CURLINFO_RESPONSE_CODE );
		if( $return < 200 || $return >= 300 )	// Outside of HTTP 2xx Success range
		{
			log_message( 'error', 'LibSimple_cURL: $url:' .$url. ' . Response: '.$return .':'. $result );
			$result = FALSE;
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
	
	private static function build_params( $fields, $RFC3986 )
	{
		$string = '';
		foreach( $fields as $field => $value )
		{
			$string .= $string == '' ? '' : '&';
			if( $RFC3986 )
			{
				$string .= "$field=" . rawurlencode( $value );
			}
			else
			{
				$string .= "$field=" . $value;
			}
		}
		return $string;
	}// build_params()
	
	
}// LibSimple_cURL
?>