<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if ( !function_exists('discord_avatar') )
{
	function discord_avatar( $DiscordID, array $member_data, $size=128 )
	{
		if( array_key_exists( 'avatar', $member_data ) && $member_data['avatar'] != NULL )
		{
			$avatar_url = 'avatars/'. $DiscordID .'/'. $member_data['avatar'];
		}
		elseif( array_key_exists( 'discriminator', $member_data ) && $member_data['discriminator'] != NULL )
		{
			$avatar_url = 'embed/avatars/'. ($member_data['discriminator'] % 5);
		}
		else
		{
			$avatar_url = 'embed/avatars/0';
		}
		return '<img class="discord_avatar discord_avatar_'.$size.'" src="https://cdn.discordapp.com/'. $avatar_url .'.png?size='.$size.'">';
	}
}

if ( !function_exists('discord_role') )
{
	function discord_role( $color_int, $name )
	{
		$role_color = dechex( $color_int );
		if( $role_color === '0' )
		{
			$role_color = 'ffffff';
		}
		if( strlen( $role_color ) === 5 )
		{
			$role_color = '0'.$role_color;
		}
		$output = '<div class="discord_role" style="border-color:#'.$role_color.';">';
		$output .= '<div class="discord_role_circle" style="background-color:#'.$role_color.';"></div>';
		$output .= '<div class="discord_role_name">'. $name .'</div>';
		$output .= '</div>';
		
		return $output;
	}
}

if ( !function_exists('discord_roles') )
{
	function discord_roles( $member_roles, array $roles_data, $decode=FALSE )
	{
		$output = '<div class="discord_roles">';
		
		if( $decode )
		{
			$member_roles = json_decode( $member_roles, FALSE, 512, JSON_BIGINT_AS_STRING );
		}
		
		if( $member_roles !== NULL && !empty( $member_roles ) )
		{
			$displayed = array();
			foreach( $roles_data as $role_data )
			{
				if( in_array( $role_data['role_id'], $member_roles ) )
				{
					$output .= discord_role( $role_data['color'], $role_data['name'] );
					$displayed[] = $role_data['role_id'];
				}
			}
			foreach( array_diff( $member_roles, $displayed ) as $role_id )
			{
				// Unknown role!
				$output .= discord_role( 'ffffff', $role_id );
			}
		}
		
		$output .= '</div>';
		
		return $output;
	}
}

?>
