<?php if( !defined('BASEPATH') ) exit ('No direct script access allowed');

/**
 * Killmail resolving library.
 *
 * @author Daneel Trevize
 */

class LibKillmails
{
	
	public function __construct()
	{
		$this->CI =& get_instance();	// Assign the CodeIgniter object to a variable
		$this->CI->config->load('ccp_api');
		$this->CI->load->library( 'LibRestAPI', $this->CI->config->item('rest_esi'), 'rest_esi' );
		$this->CI->load->library( 'LibSimple_cURL' );
		$this->CI->load->model( 'Eve_SDE_model' );
		$this->CI->load->model( 'CharacterID_model' );
		$this->CI->load->model( 'CharacterAffiliation_model' );
	}// __construct()
	
	public function resolve_killmail( $killmail_id, $killmail_hash, $retry_ESI = TRUE, $retry_zKb = TRUE )
	{
		$additional_details = array();
		
		if( $retry_ESI )
		{
			$ESI_unresolved = FALSE;
			
			$ESI_ROOT = $this->CI->config->item( 'rest_esi' )['root'];
			
			$killmail_url = "$ESI_ROOT/v1/killmails/$killmail_id/$killmail_hash/";
			
			$killmail_result = $this->CI->rest_esi->do_call( $killmail_url, $this->CI->config->item('public_esi_params') );
			
			if( $killmail_result === FALSE )
			{
				log_message( 'error', 'LibKillmails: resolve_killmail() failure in ESI for URL:'.$killmail_url );
				$ESI_unresolved = TRUE;
			}
			elseif( $killmail_result['response_code'] == 200 )
			{
				$this->resolve_additional_details( $ESI_ROOT, $killmail_result['body'], $additional_details, $ESI_unresolved );
			}
			else
			{
				log_message( 'error', 'LibKillmails: ESI failed with: ' . print_r( $killmail_result, TRUE ) );
				$ESI_unresolved = TRUE;
			}
			
			if( !$ESI_unresolved )
			{
				$additional_details['try_resolve_after'] = NULL;
			}
		}
		
		if( $retry_zKb )
		{
			$zKillURL = "https://zkillboard.com/api/kills/killID/$killmail_id/zkbOnly/";
			$zKill_response = $this->CI->libsimple_curl->do_call( $zKillURL );
			if( $zKill_response !== FALSE )
			{
				if( $zKill_response == '[]' )
				{
					log_message( 'error', 'LibKillmails: zKillboard is not aware of kill:'.$killmail_id );
				}
				else
				{
					$zKill_stats = json_decode( $zKill_response, TRUE );
					//log_message( 'error', print_r( $zKill_stats, TRUE ) );
					$totalValue = $zKill_stats['0']['zkb']['totalValue'];
					//log_message( 'error', $totalValue );
					$pos = strpos( $totalValue, '.' );
					if( $pos === FALSE )	// As happened for kill 61821668...
					{
						$intValue = $totalValue;
					}
					else
					{
						$intValue = substr( $totalValue, 0, $pos );	// Remove the .xx(xx?) partial ISK. Still a string because PHP & 32bit ints...
					}
					$additional_details['totalValue'] = $intValue;
					$additional_details['totalValue_text'] = self::shorten_value( $intValue );
				}
			}
			else
			{
				log_message( 'error', 'LibKillmails: zKillboard response: ' . print_r( $zKill_response, TRUE ) );
			}
		}
		
		return $additional_details;
	}// resolve_killmail()
	
	private function resolve_additional_details( $ESI_ROOT, $esi_killmail, &$additional_details, &$ESI_unresolved )
	{
		$killmail = json_decode( $esi_killmail, TRUE, 512, JSON_BIGINT_AS_STRING );
		
		$victim = $killmail['victim'];
		$victim_character_ID = array_key_exists( 'character_id', $victim ) ? $victim['character_id'] : NULL;
		
		$additional_details['time'] = $killmail['killmail_time'];
		$additional_details['solar_system_ID'] = $killmail['solar_system_id'];
		$additional_details['victim_character_ID'] = $victim_character_ID;
		$additional_details['victim_ship_type_ID'] = $victim['ship_type_id'];
		$additional_details['attackers_count'] = count( $killmail['attackers'] );
		
		// Resolve Solar system name
		$named_solar_systems = $this->CI->Eve_SDE_model->get_solarSystem_names( array( $killmail['solar_system_id'] ) );
		if( count($named_solar_systems) !== 1 )
		{
			$ESI_unresolved = TRUE;
		}
		else
		{
			$additional_details['solar_system_name'] = $named_solar_systems[0]['solarSystemName'];
		}
		
		// Resolve victim character/corporation name
		if( $victim_character_ID != NULL )
		{
			$named_characters = $this->CI->CharacterID_model->get_character_names( array( $victim_character_ID ) );
			if( $named_characters === FALSE )
			{
				$ESI_unresolved = TRUE;
			}
			else
			{
				$additional_details['victim_name'] = $named_characters[$victim_character_ID]['name'];
			}
		}
		else
		{
			$victim_corporation_ID = $victim['corporation_id'];
			
			$named_corporations = $this->CI->CharacterAffiliation_model->get_corporation_names( array( $victim_corporation_ID ) );
			if( $named_corporations === FALSE )
			{
				$ESI_unresolved = TRUE;
			}
			else
			{
				$additional_details['victim_name'] = $named_corporations[$victim_corporation_ID]['name'];
			}
		}
		
		
		$type_url = $ESI_ROOT.'/v3/universe/types/' . $victim['ship_type_id'] . '/';	// Could use SDE, but this works when it's not yet updated
		$type_result = $this->CI->rest_esi->do_call( $type_url, $this->CI->config->item('esi_params') );
		
		if( $type_result === FALSE || $type_result['response_code'] !== 200 )
		{
			$ESI_unresolved = TRUE;
		}
		else
		{
			$type = json_decode( $type_result['body'], TRUE, 512, JSON_BIGINT_AS_STRING );
			
			$additional_details['victim_ship_type_name'] = $type['name'];
		}
		
	}// resolve_additional_details()
	
	private static function shorten_value( $intValue )
	{
		// Reverse string, add thousands .s, remove end ., reverse result back.
		$reversed_chunked = chunk_split( strrev($intValue), 3, '.' );
		$thousands = strrev( substr( $reversed_chunked, 0, -1 ) );
		
		$units = '';
		$digits = strlen( $intValue );
		if( $digits > 15 )
		{
			$units = '?';
		} else if( $digits > 12 )
		{
			$units = 'T';
		} else if( $digits > 9 )
		{
			$units = 'B';
		} else if( $digits > 6 )
		{
			$units = 'M';
		} else if( $digits > 3 )
		{
			$units = 'K';
		}
		
		$first_three_length = ( $digits % 3 == 0 ) ? 3 : 4;
		$first_three = substr( $thousands, 0, $first_three_length );
		
		return $first_three .' '. $units;
	}// shorten_value()
	
}// LibKillmails
?>