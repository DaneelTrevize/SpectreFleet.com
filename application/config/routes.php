<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
#Order of Precedence is High to Low
$route['404_override'] = 'pages/view';
$route['default_controller'] = 'activity/fleets';	// The reserved routes must come before any wildcard or regular expression routes.
$route['articles/(:any)'] = 'articles/view/$1';
$route['articles'] = 'articles';
$route['polls/(:num)'] = 'polls/view/$1';
$route['polls'] = 'polls';
$route['social'] = 'social';
$route['doctrine'] = 'doctrine';
$route['f/f/(:num)'] = 'doctrine/fleet/$1';
$route['f/s/(:num)'] = 'doctrine/fit/$1';
$route['portal'] = 'portal';
$route['verify/SSO'] = 'authentication/SSO';
$route['login'] = 'authentication';
$route['logout'] = 'authentication/logout';
$route['authentication'] = 'authentication';
$route['invites'] = 'invites';
$route['fleets2'] = 'fleets2';
$route['fleets3'] = 'fleets3';
$route['motd'] = 'channel/motd';
$route['channel'] = 'channel';
$route['feedback/(:num)'] = 'feedback/index/$1';
$route['feedback'] = 'feedback/index';
$route['command_application'] = 'pages/view/join_command';	// Legacy redirect for those probably seeking /manage/apply
$route['discordauth'] = 'discordauth';
$route['rss'] = 'rss';
$route['l/(:any)'] = 'tool/l/$1';
$route['lscan'] = 'tool/lscan';
$route['d/(.+)'] = 'tool/d/$1';
$route['dscan'] = 'tool/dscan';
$route['dscan/(:num)'] = 'tool/dscan/$1';
$route['dscan/image/(:num)/(:num)'] = 'tool/image/$1/$2';
$route['(:any)'] = 'pages/view/$1';