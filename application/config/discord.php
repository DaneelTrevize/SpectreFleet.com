<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['discord_webhooks']['channel_list'] = array(
	'tech' => array(
		'id' => '',
		'token' => ''
	),
	'command' => array(
		'id' => '',
		'token' => ''
	),
	'directorate' => array(
		'id' => '',
		'token' => ''
	),
	'ops' => array(
		'id' => '',
		'token' => ''
	)
);
$config['discord_webhooks']['username'] = 'SpectreFleet.com';

$config['oauth_discord'] = array(
	'base_url' => 'https://discordapp.com/api/v6/',
	'authorize_suffix' => 'oauth2/authorize',
	'token_suffix' => 'oauth2/token',
	'scopes_field' => 'scope',
	'rest_api_name' => 'rest_discord'
);

$config['rest_discord'] = array(
	'user_agent' => 'SpectreFleet (https://spectrefleet.com, 0.1)',
	'connect_timeout' => 8,
	'call_timeout' => 20,
	'root' => 'https://discordapp.com/api/v6/'
);

$config['discord_app'] = array(
	'CLIENT_ID' => '',
	'CLIENT_SECRET' => '',
	'REDIRECT_URI' => 'https://spectrefleet.com/discordauth/callback',
	'REPEAT_REDIRECT_URI' => TRUE
);

$config['discord_bot'] = array(
	'CLIENT_ID' => '',
	'CLIENT_SECRET' => '',
	'REDIRECT_URI' => '',
	'BOT_TOKEN' => ''
);
