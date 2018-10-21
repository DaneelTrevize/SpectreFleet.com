<?php
if( !defined('BASEPATH') ) exit('No direct script access allowed');

class Social extends SF_Controller {
	
	public function __construct()
	{
		parent::__construct();
		$this->load->library( 'LibSimple_cURL' );
	}// __construct()
	
	
	public function index()
	{
		$is_streaming = FALSE;
		
		//https://api.twitch.tv/kraken/users?client_id=hmisgesmik65rcup76anwegnlwmhzr&login=spectrefleet&api_version=5
		/*
		$url = 'https://api.twitch.tv/kraken/streams/94855092?client_id=hmisgesmik65rcup76anwegnlwmhzr&api_version=5';
		$twitch_response = $this->libsimple_curl->do_call( $url );
		
		if( $twitch_response !== FALSE )
		{
			$stream_info = json_decode( $twitch_response );
			if( $stream_info != NULL && $stream_info->stream != NULL )
			{
				$is_streaming = TRUE;
			}
		}
		else
		{
			log_message( 'error', 'Social controller: Twitch API is being flakey, again...' );
		}
		*/
		if( $is_streaming )
		{
			//$this->load->view('social/twitch');
			redirect( 'https://player.twitch.tv/?channel=spectrefleet&volume=0.00', location );
		}
		else
		{
			$this->load->view('social/discord');
		}
		
	}// index()
	
	
}// Social
?>