<?php if( !defined('BASEPATH') ) exit ('No direct script access allowed');

/**
 * Discord library.
 *
 * @author Daneel Trevize
 */

class LibDiscord
{
	
	private $CI;
	private $CHANNEL_LIST;
	
	public function __construct( $details )
	{
		$this->CI =& get_instance();	// Assign the CodeIgniter object to a variable
		$this->CI->load->library( 'LibSimple_cURL' );
		$this->CHANNEL_LIST = $details['channel_list'];
		$this->USERNAME = $details['username'];
	}// __construct()
	
	
	public function get_webhook( $channel )
	{
		if( $channel === NULL || !is_string($channel) )
		{
			throw new InvalidArgumentException( '$channel should be a string' );
		}
		if( !array_key_exists( $channel, $this->CHANNEL_LIST ) )
		{
			throw new InvalidArgumentException( '$channel is not found in the configured channel list' );
		}
		
		$webhook = $this->CHANNEL_LIST[$channel];
		$webhook_id = $webhook['id'];
		$webhook_token = $webhook['token'];
		
		
		$response = $this->CI->libsimple_curl->do_call( 'https://discordapp.com/api/webhooks/'.$webhook_id.'/'.$webhook_token );
		
		return $response;
	}// get_webhook()
	
	public function exec_webhook_content( $channel, $content, $wait='false', $username=NULL )
	{
		if( $channel === NULL || !is_string($channel) )
		{
			throw new InvalidArgumentException( '$channel should be a string' );
		}
		if( !array_key_exists( $channel, $this->CHANNEL_LIST ) )
		{
			throw new InvalidArgumentException( '$channel is not found in the configured channel list' );
		}
		if( $content === NULL || !is_string($content) )
		{
			throw new InvalidArgumentException( '$content should be a string' );
		}
		if( $wait !== 'true' && $wait !== 'false' )
		{
			throw new InvalidArgumentException( '$wait should be true or false' );
		}
		if( $username !== NULL && (!is_string($username) || $username === '') )
		{
			throw new InvalidArgumentException( '$username should be a string or NULL' );
		}
		if( $username == NULL )
		{
			$username = $this->USERNAME;
		}
		
		$webhook = $this->CHANNEL_LIST[$channel];
		$webhook_id = $webhook['id'];
		$webhook_token = $webhook['token'];
		
		$fields['content'] = $content;
		if( $username !== NULL )
		{
			$fields['username'] = $username;
		}
		
		$response = $this->CI->libsimple_curl->do_call( 'https://discordapp.com/api/webhooks/'.$webhook_id.'/'.$webhook_token.'?wait='.$wait, $fields, TRUE, 'POST_JSON' );
		
		return $response;
	}// exec_webhook_content()
	
	
}// LibDiscord
?>