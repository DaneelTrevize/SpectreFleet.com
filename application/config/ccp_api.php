<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['oauth_eve'] = array(
	'base_url' => 'https://login.eveonline.com/',
	'authorize_suffix' => 'oauth/authorize',
	'token_suffix' => 'oauth/token',
	'verify_suffix' => 'oauth/verify',
	'scopes_behind_verify' => TRUE,
	'scopes_field' => 'Scopes',
	'rest_api_name' => 'rest_esi'
);

$config['rest_esi'] = array(
	'user_agent' => 'SpectreFleet',
	'connect_timeout' => 4,
	'call_timeout' => 10,
	'root' => 'https://esi.evetech.net'
);

$config['sso_params'] = array(
	'CLIENT_ID' => '',
	'CLIENT_SECRET' => '',
	'REDIRECT_URI' => 'https://spectrefleet.com/verify/SSO'
);

$config['esi_params'] = array(
	'CLIENT_ID' => '',
	'CLIENT_SECRET' => '',
	'REDIRECT_URI' => 'https://spectrefleet.com/OAuth/verify'
);

$config['public_esi_params'] = array(
	'CLIENT_ID' => '',
	'CLIENT_SECRET' => '',
	'REDIRECT_URI' => 'https://spectrefleet.com/OAuth/verify'
);

$config['critical_esi_params'] = array(
	'CLIENT_ID' => '',
	'CLIENT_SECRET' => '',
	'REDIRECT_URI' => 'https://spectrefleet.com/OAuth/verify'
);
