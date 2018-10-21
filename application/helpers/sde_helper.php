<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( !function_exists('link_solar_system') )
{
	function link_solar_system( $systemName, $markdown=FALSE )
	{
		if( $systemName == 'Thera' )	// Linking Thera on Dotlan isn't useful, routes are more easily found from eve-scout
		{
			if( $markdown )
			{
				return '[Thera](https://www.eve-scout.com/)';
			}
			else
			{
				return '<a href="https://www.eve-scout.com/">Thera</a>';
			}
		}
		else
		{
			if( $markdown )
			{
				return '['.$systemName.'](http://evemaps.dotlan.net/system/'.$systemName.')';
			}
			else
			{
				return '<a href="http://evemaps.dotlan.net/system/'.$systemName.'">'.$systemName.'</a>';
			}
		}
	}
}

?>
