<?php if( !defined('BASEPATH') ) exit ('No direct script access allowed');

/**
 * MOTD parsing library.
 *
 * @author Daneel Trevize
 */

class MOTD
{
	/*
	*	We assume the HTML for chat MOTDs doesn't produce nested or interleaved font tags
	*	We also assume every <font> tag contains size="ddd" and colour="#hhhhhhhh"
	*/
	
	const ACCEPTABLE_TAGS = '<a><font><b><br>';
	
	const P_ANYTHING_LAZY = '(.{0,}?)';		// The smallest matching group of anything, even nothing.
	
	const P_BEFORE_SYSTEM_NAME = '((?:</font>| ){0,})';
	const P_VISIBLE_SYSTEM_NAME = '([[:alnum:]-]{1,})';
	const P_AFTER_SYSTEM_NAME = '( |,|\.|<)';	// Can't be sure it's only <br|<font tags after if it could be an unlinked, formatted name
	const P_COLOR_ATTR_START = 'color="\#[[:xdigit:]]{2,2}';
	const P_FONT_TAG_START = '<font( size="[[:digit:]]{1,}") ' .self::P_COLOR_ATTR_START. '([[:xdigit:]]{6,6})">';
	
	const P_AFTER_VISIBLE_INTERESTING = '(,| |<br>|</font>)';
	
	const FLEETS_URI = '/doctrine/fleet/';
	const SYSTEM_URI = 'http://evemaps.dotlan.net/system/';
	const KILLMAIL_URI = 'https://zkillboard.com/kill/';
	
	const DEFAULT_COLOR = self::NULLSEC_COLOR;		// Default colour of a fleet line, Red aka Nullsec
	
	const HIGHSEC_COLOR = '00ff00';
	const LOWSEC_COLOR = 'ffff00';
	const NULLSEC_COLOR = 'ff0000';
	const SPECIAL_COLOR = '00ffff';
	const TRAINING_COLOR = 'ff00ff';
	
	// Unstyled versions of section headers, to ignore when looking for scheduled/active fleets
	const SCHEDULED_FLEETS_HEADER = 'Upcoming Fleets: (HIGH/LOW/NULL/TRAINING/OTHER)';
	const ACTIVE_FLEETS_HEADER = 'Active Fleets:';
	
	
	private $CI;
	
	public function __construct()
	{
		$this->CI =& get_instance();	// Assign the CodeIgniter object to a variable
		$this->CI->load->model('Eve_SDE_model');
		$this->CI->load->model('Command_model');
		$this->CI->load->model('Doctrine_model');
	}// __construct()
	
	
	public function spectre_to_data( $motd )
	{
		$errors = array();
		$motd = self::remove_undesired_tags( $motd, $errors );
		
		// We could strip out paired tags that only contain whitespace, keeping the whitespace
		
		$motd = self::remove_extra_spaces( $motd );
		
		$motdSections = self::extract_sections( $motd );
		
		$bulletins_html = self::section_to_html( self::remove_xml_styles( $motdSections['bulletins'] ) );
		
		// parse raw CCP xml sections
		$kills = self::detect_kills( self::remove_xml_styles( $motdSections['kills'] ) );
		$active = self::detect_active( self::remove_xml_styles_except_color( $motdSections['active'] ), $errors );
		$fleets = self::detect_fleets( self::remove_xml_styles_except_color( $motdSections['upcoming'] ), $errors );
		
		$motd_data = array(
			'bulletins_html' => $bulletins_html,
			'kills' => $kills,
			'active' => $active,
			'fleets' => $fleets,
			'errors' => $errors
		);
		return $motd_data;
	}// spectre_to_data()
	
	
	private static function remove_undesired_tags( $motd, &$errors )
	{
		$stripped_motd = strip_tags( $motd, self::ACCEPTABLE_TAGS );
		$motd_len = strlen( $motd );
		$stripped_len = strlen( $stripped_motd );
		if( $motd_len !== $stripped_len )		// The lengths must differ if strip_tags found anything unacceptable
		{
			$error = 'Unacceptable tags detected';
			
			// Reported position is 0-indexed, and after any $MOTD_COPY_PATTERN prefix removal
			
			$shorter_len = ( $motd_len < $stripped_len ? $motd_len : $stripped_len );
			$index = 0;
			for( ; $index < $shorter_len; $index++ )
			{
				if( $motd[$index] !== $stripped_motd[$index] )
				{
					$error .= ', starting at position: '. $index;
					break;
				}
			}
			if( $index >= $shorter_len )
			{
				$error .= ', starting at position: '. $index;
			}
			
			$errors[] = $error;
		}
		return $stripped_motd;
	}// remove_undesired_tags()
	
	private static function remove_extra_spaces( $motd )
	{
		$FONT_SPACES_PATTERN = '#'. '( {1,})(</font>)' .'#';
		$motd = preg_replace( $FONT_SPACES_PATTERN, '$2$1', $motd );// Move inner suffix spaces outside of font tags
		
		$LINEBREAK_SPACES_PATTERN = '#'. '( {1,})(<br>)' .'#';
		$motd = preg_replace( $LINEBREAK_SPACES_PATTERN, '$2', $motd );	// Remove inner suffix spaces before linebreak tags
		
		return $motd;
	}// remove_extra_spaces()
	
	private static function extract_sections( $motd )
	{
		$sections = array(
			'upcoming' => '',
			'bulletins' => '',
			'kills' => '',
			'active' => ''
		);
		
		// Removes all the MOTD prior to "Upcoming Fleets:"
		// But capturing all formatting on the line it starts on, to try not break paired tags for later
		// Similarly, each subsequent optional header and section capture all formatting on the line they start on
		
		$lines = explode( "<br>", $motd );
		$section = "prior";
		foreach( $lines as $line )
		{
			if( strpos( $line, "Upcoming Fleets:" ) !== FALSE )
			{
				$section = "upcoming";
			}
			elseif( strpos( $line, "Special Bulletins:" ) !== FALSE )
			{
				$section = "bulletins";
			}
			elseif( strpos( $line, "Kills of the Day:" ) !== FALSE )
			{
				$section = "kills";
			}
			elseif( strpos( $line, "Active Fleets:" ) !== FALSE )
			{
				$section = "active";
			}
			
			if( $section != "prior" )
			{
				$sections[$section] .= "<br>" . $line;
			}
		}
		return $sections;
	}// extract_sections()
	
	private static function section_to_html( $motd )
	{
		$motd = self::replace_channellink( $motd );
		
		$motd = self::replace_httplink( $motd );
		
		$motd = self::replace_locationlink( $motd, FALSE );
		
		$motd = self::replace_itemlink( $motd, FALSE );
		
		$motd = self::replace_fleetlink( $motd );
		
		$motd = self::replace_system( $motd );
		
		$motd = self::replace_fc( $motd );
		
		//$motd = self::replace_bold( $motd );
		
		$motd = self::replace_font( $motd );
		
		// We should probably still check for and remove unpaired tags
		
		$motd = self::restyle_header( $motd );
		
		return $motd;
	}// section_to_html()
	
	private static function replace_channellink( $motd )
	{
		$CHANNEL_PATTERN = '#'. '<a href="joinChannel:player_' .self::P_ANYTHING_LAZY. '">' .self::P_ANYTHING_LAZY. '</a>' .'#';
		
		$replaced_motd = preg_replace( $CHANNEL_PATTERN, '[InGameChannel:"$2"]', $motd );
		return $replaced_motd;
	}// replace_channellink()
	
	private static function replace_httplink( $motd )
	{
		// We don't go searching for any http:(s) strings, they need to be linkified via the ingame editor atm.
		// Only http and https permitted? We don't want to htmlentities()/rawurlencode() this?
		$HTTPLINK_PATTERN = '#'. '<a href="(http[s]{0,1}://)'.self::P_ANYTHING_LAZY.'">' .self::P_ANYTHING_LAZY. '</a>' .'#';
		
		$replaced_motd = preg_replace( $HTTPLINK_PATTERN, '<a href="$1$2">$3</a>', $motd );
		return $replaced_motd;
	}// replace_httplink()
	
	private static function replace_locationlink( $motd, $discard=TRUE )	// Loc tags now only appear around external links?
	{
		$LOCATIONLINK_PATTERN = '#'. '<loc><a href="showinfo:' .self::P_ANYTHING_LAZY. '">' .self::P_ANYTHING_LAZY. '</a></loc>' .'#';
		
		if( $discard )
		{
			$replaced_motd = preg_replace( $LOCATIONLINK_PATTERN, '$2', $motd );	// Just strip the tags
		}
		else
		{
			$replaced_motd = preg_replace( $LOCATIONLINK_PATTERN, '[InGameLocation:"$2"]', $motd );
		}
		return $replaced_motd;
	}// replace_locationlink()
	
	private static function replace_itemlink( $motd, $discard=TRUE )
	{
		// More generic that locationlink, will also match those if called first, and won't strip the <loc></loc> tags
		$ITEMLINK_PATTERN = '#'. '<a href="showinfo:' .self::P_ANYTHING_LAZY. '">' .self::P_ANYTHING_LAZY. '</a>' .'#';
		
		if( $discard )
		{
			$replaced_motd = preg_replace( $ITEMLINK_PATTERN, '$2', $motd );	// Just strip the tags
		}
		else
		{
			$replaced_motd = preg_replace( $ITEMLINK_PATTERN, '[InGameItem:"$2"]', $motd );
		}
		return $replaced_motd;
	}// replace_itemlink()
	
	private static function replace_fleetlink( $motd )
	{
		// Support replacing "fleetID(case-insensitive): numbers" with URI to fleet doctrine view page
		$FLEETLINK_PATTERN = '#'. '(?i:fleetID):[ ]{0,1}' .'([\d]{1,9})' .'#';	// 9 digits max to avoid integer max
		
		$replaced_motd = preg_replace( $FLEETLINK_PATTERN, ' [<a href="'.self::FLEETS_URI.'$1">online fleet fits</a>]$2', $motd );
		return $replaced_motd;
	}// replace_fleetlink()
	
	private static function replace_system( $motd )
	{
		// We depend on there being no formatting between the space and the @ or ~ at the start of these patterns
		// We need to in order to nicely format before the , in the replacement
		
		$replaced_motd = $motd;	// Do this here so we don't accidentally use $motd later, should we re-order things in this function...
		
		$AT_LINKED_SYSTEM_PATTERN = '#'. ' (?:@|(?i)at )' .self::P_BEFORE_SYSTEM_NAME .'(\[InGame(?:Location|Item):")' .self::P_VISIBLE_SYSTEM_NAME .'("\])' .'#';
		$replaced_motd = preg_replace( $AT_LINKED_SYSTEM_PATTERN, ', in $1$2<a href="'.self::SYSTEM_URI.'$3">$3</a>$4', $replaced_motd );
		// $2 $4 would be the before and after [InGame...:" "] tags.
		
		$AT_UNLINKED_SYSTEM_PATTERN = '#'. ' (?:@|(?i)at )' .self::P_BEFORE_SYSTEM_NAME .self::P_VISIBLE_SYSTEM_NAME .self::P_AFTER_SYSTEM_NAME .'#';
		$replaced_motd = preg_replace( $AT_UNLINKED_SYSTEM_PATTERN, ', in $1<a href="'.self::SYSTEM_URI.'$2">$2</a>$3', $replaced_motd );
		
		
		$NEAR_LINKED_SYSTEM_PATTERN = '#'. ' (?:~|(?i)near )' .self::P_BEFORE_SYSTEM_NAME .'(\[InGame(?:Location|Item):")' .self::P_VISIBLE_SYSTEM_NAME .'("\])' .'#';
		$replaced_motd = preg_replace( $NEAR_LINKED_SYSTEM_PATTERN, ', near $1$2<a href="'.self::SYSTEM_URI.'$3">$3</a>$4', $replaced_motd );
		// $2 $4 would be the before and after [InGame...:" "] tags.
		
		$NEAR_UNLINKED_SYSTEM_PATTERN = '#'. ' (?:~|(?i)near )' .self::P_BEFORE_SYSTEM_NAME .self::P_VISIBLE_SYSTEM_NAME .self::P_AFTER_SYSTEM_NAME .'#';
		$replaced_motd = preg_replace( $NEAR_UNLINKED_SYSTEM_PATTERN, ', near $1<a href="'.self::SYSTEM_URI.'$2">$2</a>$3', $replaced_motd );
		
		
		$ATVAGUE_PATTERN = '#'. ' (?:@|(?i)at )' .self::P_BEFORE_SYSTEM_NAME .'(?:\?{1,})'. self::P_AFTER_VISIBLE_INTERESTING .'#';
		$replaced_motd = preg_replace( $ATVAGUE_PATTERN, '$1, location undecided$2', $replaced_motd );
		
		return $replaced_motd;
	}// replace_system()
	
	private static function replace_fc( $motd )
	{
		$replaced_motd = str_ireplace(' w/',' with',$motd);					// Replaces shorthand version of with
		return $replaced_motd;
	}// replace_fc()
	/*
	private static function replace_bold( $motd )
	{
		$BOLD_PATTERN = '#'. '<b>' .self::P_ANYTHING_LAZY. '</b>' .'#';
		
		$replaced_motd = preg_replace( $BOLD_PATTERN, '$1', $motd );		// Just strip the tags
		return $replaced_motd;
	}// replace_bold()
	*/
	
	private static function replace_font( $motd )
	{
		$FONT_PATTERN = '#'. self::P_FONT_TAG_START .self::P_ANYTHING_LAZY. '</font>' .'#';
		
		$replaced_motd = preg_replace( $FONT_PATTERN, '<font color="$2">$3</font>', $motd );
		return $replaced_motd;
	}// replace_font()
	
	private static function restyle_header( $motd )
	{
		$motd = str_ireplace(array('<b>', '</b>'), array('', ''),$motd);	// Removes bold tags
		
		$HEADER_PATTERN = '#'. '(?:<br>){0,1}' .self::P_ANYTHING_LAZY. '(Upcoming Fleets|Special Bulletins|Kills of the Day|Active Fleets):' .self::P_ANYTHING_LAZY. '(?:<br>)' .'#';
		$motd = preg_replace( $HEADER_PATTERN, '$1<h2>$2</h2>$3'."\n", $motd );
		
		return $motd;
	}// restyle_header()
	
	
	private static function detect_kills( $kills_xml )
	{
		$kills = array();
		
		$kills_lines = explode( '<br>', $kills_xml );
		
		foreach( $kills_lines as $line )
		{
			$kill = self::extract_kill( $line );
			if( $kill != NULL )
			{
				$kills[] = $kill;
			}
		}
		
		return $kills;
	}// detect_kills()
	
	private static function extract_kill( $line )
	{
		// Assumes maximum of 1 kill per line
		
		$kill = NULL;
		
		$KILLREPORT_PATTERN = '#'. '<a href="killReport:' . '([[:digit:]]{1,}?)' . ':' . '([[:xdigit:]]{1,}?)' . '">'.self::P_ANYTHING_LAZY.': ' .self::P_ANYTHING_LAZY. '</a>' .'#';
		
		if( preg_match( $KILLREPORT_PATTERN, $line, $matches ) === 1 )
		{
			$kill = array(
				'ID' => $matches[1],
				'hash' => $matches[2],
				'text' => $matches[4]	// We skip the localised version of "Kill":
			);
		}
		return $kill;
	}// extract_kill()
	
	private function detect_active( $active_xml, &$errors )
	{
		$actives = array();
		
		$active_lines = explode( '<br>', $active_xml );
		
		$ACTIVE_LINE_PATTERN = '#'. '.{0,}'. '<a href="joinChannel:player_'. '([[:xdigit:]]{1,})' .'(?://None//None)">XUP~'. '([[:digit:]]{1,1})' .'</a>'. '(.{0,}?)-' .'(.{0,})' .'#';
		
		$current_color = self::DEFAULT_COLOR;
		
		// Assume that each fleet line only changes colour for type right after the channel link
		foreach( $active_lines as $line )
		{
			if( preg_match( $ACTIVE_LINE_PATTERN, $line, $matches ) !== 1 )
			{
				// Not an active fleet line, don't add to $actives[], skip to next loop cycle
				if( !self::should_skip_active_line( $line ) )
				{
					$errors[] = 'No active fleet found in: "' . $line . '"';
				}
				continue;
			}
			//$active['matches'] = print_r( $matches, TRUE );
			
			$active['channelID'] = $matches[1];
			$active['XUPNumber'] = $matches[2];
			
			// Determine the colour from the font tag after the channel link, which has CCP's chosen colour
			$current_color = self::determine_fleet_color( $matches[3], $current_color );
			
			$type_data = self::fleet_color_to_type( $current_color );
			$active['type'] = $type_data['type'];
			$active['pretty_type'] = $type_data['pretty_type'];
			
			// Now we've used the xml color, we should remove it
			$altered_line = self::remove_xml_color( $matches[4] );
			
			//log_message( 'error', print_r( $altered_line, TRUE ) );
			if( trim( $altered_line, ' ' ) === '' )
			{
				// No actual fleet details after the -
				continue;
			}
			
			// And remove ingame links, and other formatting?
			$altered_line = self::replace_channellink( $altered_line );
			$altered_line = self::replace_httplink( $altered_line );
			
			$altered_line = self::replace_locationlink( $altered_line );
			$altered_line = self::replace_itemlink( $altered_line );
			
			$active['remaining_details'] = $altered_line;
			
			$actives[] = $active;
		}
		
		// Loop back over simpler identified fleet lines, to determine location, FC, other details
		foreach( $actives as &$active )
		{
			self::parse_fleet_remaining_details( $active, $errors, TRUE );
		}
		
		return $actives;
	}// detect_active()
	
	private function should_skip_active_line( $line )
	{
		if( $line === '' )
		{
			return TRUE;
		}
		$unstyled_full_line = self::remove_xml_color( $line );
		if( $unstyled_full_line === '' || $unstyled_full_line == self::ACTIVE_FLEETS_HEADER )
		{
			return TRUE;
		}
		return FALSE;
	}// should_skip_active_line()
	
	
	private function detect_fleets( $fleet_xml, &$errors )
	{
		$fleets = array();
		
		$fleet_lines = explode( '<br>', $fleet_xml );
		
		$FLEET_LINE_PATTERN = '#'. '([\d]{2,2}/[\d]{2,2} - [\d]{2,2}:[\d]{2,2}) -' .'.{0,}'. ' (?:(?:[wW]/[ ]{0,1})|(?i)with )' .'.{1,}?'. ' (?:(?i)at |@[ ]{0,1}|(?i)near |~)' .'.{1,}' .'#';
		
		$current_color = self::DEFAULT_COLOR;
		
		// Assume that each fleet line only changes colour (for type) at the start of the line
		foreach( $fleet_lines as $line )
		{
			if( preg_match( $FLEET_LINE_PATTERN, $line, $matches ) !== 1 )
			{
				// Not a fleet line, don't add to $fleets[], skip to next loop cycle
				if( !self::should_skip_fleet_line( $line ) )
				{
					$errors[] = 'No scheduled fleet found in: "' . $line . '"';
				}
				continue;
			}
			//print_r( $matches );
			
			$current_color = self::determine_fleet_color( $line, $current_color );
			
			$type_data = self::fleet_color_to_type( $current_color );
			$fleet['type'] = $type_data['type'];
			$fleet['pretty_type'] = $type_data['pretty_type'];
			
			// Now we've used the xml color, we should remove it
			$altered_line = self::remove_xml_color( $line );
			// And remove ingame links, and other formatting?
			$altered_line = self::replace_channellink( $altered_line );
			$altered_line = self::replace_httplink( $altered_line );
			
			$altered_line = self::replace_locationlink( $altered_line );
			$altered_line = self::replace_itemlink( $altered_line );
			
			//$fleet['altered_line'] = $altered_line;
			
			$fleet_datetime = self::determine_fleet_datetime( $matches[1] );	// Get full Day string
			if( $fleet_datetime != NULL )
			{
				$fleet['datetime'] = $fleet_datetime['datetime'];
				$fleet['pretty_date'] = $fleet_datetime['pretty_date'];
				$line_after_time = substr( $altered_line, $fleet_datetime['time_offset'] + 7 );	// "hh:mm -" = length7
				$fleet['remaining_details'] = $line_after_time;
				$fleet['time'] = $fleet_datetime['time'];
			}
			
			$fleets[] = $fleet;
		}
		
		// Loop back over simpler identified fleet lines, to determine location, FC, other details
		foreach( $fleets as &$fleet )
		{
			self::parse_fleet_remaining_details( $fleet, $errors, FALSE );
		}
		
		return $fleets;
	}// detect_fleets()
	
	private function should_skip_fleet_line( $line )
	{
		if( $line === '' )
		{
			return TRUE;
		}
		$unstyled_full_line = self::remove_xml_color( $line );
		if( $unstyled_full_line === '' || $unstyled_full_line == self::SCHEDULED_FLEETS_HEADER )
		{
			return TRUE;
		}
		return FALSE;
	}// should_skip_fleet_line()
	
	
	private function determine_fleet_color( $line, $current_color )
	{
		$COLOR_AT_LINE_START_PATTERN = '#'. '^</font>'.self::P_FONT_TAG_START .'#';
		
		if( preg_match( $COLOR_AT_LINE_START_PATTERN, $line, $color_matches ) === 1 )
		{
			return $color_matches[2];
		}
		return $current_color;
	}// determine_fleet_color()
	
	private static function fleet_color_to_type( $current_color )
	{
		$type_data['type'] = 'Undetermined';
		$type_data['pretty_type'] = 'Undetermined';
		
		if( $current_color === self::HIGHSEC_COLOR )
		{
			$type_data['type'] = 'Highsec';
			$type_data['pretty_type'] = '<font color="'.self::HIGHSEC_COLOR.'">Highsec</font>';
		}
		elseif( $current_color === self::LOWSEC_COLOR )
		{
			$type_data['type'] = 'Lowsec';
			$type_data['pretty_type'] = '<font color="'.self::LOWSEC_COLOR.'">Lowsec</font>';
		}
		elseif( $current_color === self::NULLSEC_COLOR )
		{
			$type_data['type'] = 'Nullsec';
			$type_data['pretty_type'] = '<font color="'.self::NULLSEC_COLOR.'">Nullsec</font>';
		}
		elseif( $current_color === self::SPECIAL_COLOR )
		{
			$type_data['type'] = 'Special';
			$type_data['pretty_type'] = '<font color="'.self::SPECIAL_COLOR.'">Special</font>';
		}
		elseif( $current_color === self::TRAINING_COLOR )
		{
			$type_data['type'] = 'Training';
			$type_data['pretty_type'] = '<font color="'.self::TRAINING_COLOR.'">Training</font>';
		}
		
		return $type_data;
	}// fleet_color_to_type()
	
	private static function determine_fleet_datetime( $line )
	{
		$UTC_DTZ = new DateTimeZone( 'UTC' );
		$currentTime = new DateTime( 'now', $UTC_DTZ );
		$current_year = $currentTime->format( 'Y' );	// Extract the 4digit year
		
		$DATE_MONTH_PATTERN = '#'. '([\d]{2,2})/([\d]{2,2}) - ([\d?]{2,2}:[\d?]{2,2})' .'#';	// Don't need to match start of line formatting?
		if( preg_match( $DATE_MONTH_PATTERN, $line, $matches, PREG_OFFSET_CAPTURE ) === 1 )
		{
			$fleet_date = $matches[1][0];
			$date_offset = $matches[1][1];
			$fleet_month = $matches[2][0];
			$fleet_time = $matches[3][0];
			$time_offset = $matches[3][1];
			
			// Handle being in December but having January listings
			if( $fleet_month == '01' )
			{
				$current_month = $currentTime->format( 'm' );
				if( $current_month == '12' )
				{
					$current_year = 1 + (integer) $current_year;
				}
			}
			
			$fleet_datetime_string = $current_year .'/'. $fleet_month .'/'. $fleet_date .' '. $fleet_time ;	// Concat format depended upon below
			$fleet_datetime = DateTime::createFromFormat( 'Y/m/d H:i', $fleet_datetime_string, $UTC_DTZ );
			return array(
				'datetime' => $fleet_datetime->format( 'Y-m-d H:i:s' ) . '+00',	// UTC TZ, in format not supported by date()
				'pretty_date' => $fleet_datetime->format( 'l jS F' ),
				'date_offset' => $date_offset,
				'time' => $fleet_time,
				'time_offset' => $time_offset
			);
		}
		else
		{
			return NULL;
		}
	}// determine_fleet_datetime()
	
	private function parse_fleet_remaining_details( &$fleet, &$errors, $ACTIVE_NOT_SCHEDULED )
	{
		$remaining_details = $fleet['remaining_details'];
		
		$extracted_fc = self::determine_fc( $remaining_details, $errors, $ACTIVE_NOT_SCHEDULED );
		$fleet['FC'] = $extracted_fc['FC'];
		$fleet['FC_ID'] = $extracted_fc['FC_ID'];
		$remaining_details = $extracted_fc['remaining_details'];
		
		$extracted_fleet_location = self::determine_fleet_location( $remaining_details, $errors, $ACTIVE_NOT_SCHEDULED );
		$fleet['location'] = $extracted_fleet_location['location'];
		$fleet['location_exact'] = $extracted_fleet_location['location_exact'];
		$fleet['location_ID'] = $extracted_fleet_location['location_ID'];
		$fleet['location_name'] = $extracted_fleet_location['location_name'];
		$remaining_details = $extracted_fleet_location['remaining_details'];
		
		$extracted_fleet_doctrine = self::determine_fleet_doctrine( $remaining_details );
		$fleet['doctrine'] = $extracted_fleet_doctrine['doctrine'];
		$fleet['doctrine_name'] = $extracted_fleet_doctrine['doctrine_name'];
		$remaining_details = $extracted_fleet_doctrine['remaining_details'];
		
		// Finish reformatting xml and Spectre syntax to HTML
		$remaining_details = self::replace_channellink( $remaining_details );
		
		// Remove redundant details text if it's just the doctrine's name
		$remaining_details = trim( $remaining_details );
		if( $remaining_details === html_entity_decode( $fleet['doctrine_name'], ENT_QUOTES | ENT_HTML5 ) )
		{
			$remaining_details = '';
		}
		
		$fleet['remaining_details'] = $remaining_details;
		// Trim affixed spaces, commas, periods, brackets, etc?
	}// parse_fleet_remaining_details()
	
	private function determine_fc( $line, &$errors, $ACTIVE_NOT_SCHEDULED )
	{
		$fc = 'Undetermined';
		$ID = FALSE;
		$remaining_details = $line;
		
		$P_START_ANYTHING = '^(.{0,})';
		
		$P_WITH_VARIATIONS = ' (?:(?:[wW]/[ ]{0,1})|(?i)with )';
		
		$P_LOCATION_VARIATIONS = '( (?:(?i)at |@[ ]{0,1}|(?i)near |~))';
		$P_END_SOMETHING = '(.{1,}$)';
		
		// FC name must be followed by either location pattern, or is assumed to be at the end of the line
		$FC_LOCATION_PATTERN = '#'. $P_START_ANYTHING. $P_WITH_VARIATIONS .'(.{1,}?)'. $P_LOCATION_VARIATIONS .$P_END_SOMETHING .'#';
		$FC_END_PATTERN = '#'. $P_START_ANYTHING. $P_WITH_VARIATIONS .'(.{1,}$)' .'#';
		
		if( preg_match( $FC_LOCATION_PATTERN, $line, $matches ) === 1 )
		{
			$fc = $matches[2];
			$ID = self::get_FCID( $fc );
			$remaining_details = $matches[1] . $matches[3] . $matches[4];
		}
		else if( preg_match( $FC_END_PATTERN, $line, $matches ) === 1 )
		{
			$fc = $matches[2];
			$ID = self::get_FCID( $fc );
			$remaining_details = $matches[1];
		}
		else
		{
			$error = 'Parsing for ' . ($ACTIVE_NOT_SCHEDULED ? 'Active' : 'Scheduled') . ' fleet';
			$error .= '. No FC found prior to a location or end-of-line in: "' . $line . '"';
			$errors[] = $error;
		}
		
		if( $fc != 'Undetermined' && $ID === FALSE )	// Technically assumes no 2 FCs start with "Undetermined"...
		{
			$error = 'Parsing for ' . ($ACTIVE_NOT_SCHEDULED ? 'Active' : 'Scheduled') . ' fleet';
			$error .= '. Unable to determine 1 specific FC from the name "'.$fc.'"';
			$errors[] = $error;
		}
		
		return array(
			'FC' => $fc,
			'FC_ID' => $ID,
			'remaining_details' => $remaining_details
		);
	}// determine_fc()
	
	private function get_FCID( $fc )
	{
		return $this->CI->Command_model->commander_by_name( $fc );
	}// get_FCID()
	
	private function determine_fleet_location( $line, &$errors, $ACTIVE_NOT_SCHEDULED )
	{
		$location = 'Undetermined';
		$exact = TRUE;
		$ID = FALSE;
		$systemName = '';
		$remaining_details = $line;
		
		$P_START_ANYTHING = '^(.{0,})';
		$P_END_ANYTHING = '(.{0,})$';
		
		$AT_LOCATION_PATTERN = '#'. $P_START_ANYTHING. ' (?:(?i)at |@)[ ]{0,1}'. self::P_VISIBLE_SYSTEM_NAME .$P_END_ANYTHING .'#';
		$NEAR_LOCATION_PATTERN = '#'. $P_START_ANYTHING. ' (?:(?i)near |~)[ ]{0,1}'. self::P_VISIBLE_SYSTEM_NAME .$P_END_ANYTHING .'#';
		$VAGUE_PATTERN = '#'. $P_START_ANYTHING. ' (?:(?i)at |@)[ ]{0,1}'. '(?:\?{1,})' .$P_END_ANYTHING .'#';
		
		
		if( preg_match( $AT_LOCATION_PATTERN, $line, $matches ) === 1 )
		{
			$solarSystem = self::get_solarSystem( $matches[2] );
			if( $solarSystem !== FALSE )
			{
				$systemName = $solarSystem['solarSystemName'];
				$ID = $solarSystem['solarSystemID'];
				$location = self::link_systemName( $systemName, $ID );
				$remaining_details = $matches[1] . $matches[3];
			}
			else
			{
				$error = 'Parsing for ' . ($ACTIVE_NOT_SCHEDULED ? 'Active' : 'Scheduled') . ' fleet';
				$error .= '. Location "'.$matches[2].'" could not be resolved';
				$errors[] = $error;
			}
		}
		elseif( preg_match( $NEAR_LOCATION_PATTERN, $line, $matches ) === 1 )
		{
			$solarSystem = self::get_solarSystem( $matches[2] );
			if( $solarSystem !== FALSE )
			{
				$systemName = $solarSystem['solarSystemName'];
				$ID = $solarSystem['solarSystemID'];
				$location = self::link_systemName( $systemName, $ID );
				$exact = FALSE;
				$remaining_details = $matches[1] . $matches[3];
			}
			else
			{
				$error = 'Parsing for ' . ($ACTIVE_NOT_SCHEDULED ? 'Active' : 'Scheduled') . ' fleet';
				$error .= '. Location "'.$matches[2].'" could not be resolved';
				$errors[] = $error;
			}
		}
		elseif( preg_match( $VAGUE_PATTERN, $line, $matches ) === 1 )
		{
			$location = 'Undecided';
			$remaining_details = $matches[1] . $matches[2];
		}
		else
		{
			$error = 'Parsing for ' . ($ACTIVE_NOT_SCHEDULED ? 'Active' : 'Scheduled') . ' fleet';
			$error .= '. No fleet location found in: "' . $line . '"';
			$errors[] = $error;
		}
		
		return array(
			'location' => $location,
			'location_exact' => $exact,
			'location_ID' => $ID,
			'location_name' => $systemName,
			'remaining_details' => $remaining_details
		);
	}// determine_fleet_location()
	
	private function get_solarSystem( $solarSystemName )
	{
		$solarSystem = $this->CI->Eve_SDE_model->get_solarSystem_by_name( $solarSystemName );
		if( $solarSystem !== FALSE )
		{
			return $solarSystem;
		}
		else
		{
			return FALSE;	// We could try LIKE prefix searching?
		}
	}// get_solarSystem()
	
	private function link_systemName( $systemName, $ID )
	{
		if( $ID !== FALSE )
		{
			$location = link_solar_system( $systemName );
		}
		else
		{
			$location = $systemName;
		}
		return $location;
	}// link_systemName()
	
	private function determine_fleet_doctrine( $line )
	{
		$doctrine = '';
		$doctrine_name = FALSE;
		$remaining_details = $line;
		
		$P_START_ANYTHING = '^(.{0,})';
		$P_END_ANYTHING = '(.{0,})$';
		
		// Support replacing "fleetID(case-insensitive): numbers" with URI to fleet doctrine view page
		$P_FLEETID = '(?i:fleetID):[ ]{0,1}' .'([\d]{1,9})';	// 9 digits max to avoid integer max
		
		$FLEETID_PATTERN = '#'. $P_START_ANYTHING. $P_FLEETID .$P_END_ANYTHING .'#';
		
		if( preg_match( $FLEETID_PATTERN, $line, $matches ) === 1 )
		{
			$doctrine = $matches[2];
			$fleet_info = $this->CI->Doctrine_model->get_fleet_info( $doctrine );
			if( $fleet_info !== FALSE )
			{
				$doctrine_name = $fleet_info['fleetName'];
			}
			
			$remaining_details = $matches[1] . $matches[3];
		}
			
		return array(
			'doctrine' => $doctrine,
			'doctrine_name' => $doctrine_name,
			'remaining_details' => $remaining_details
		);
	}// determine_fleet_doctrine()
	
	
	private static function remove_xml_styles( $line )
	{
		// Handles font and bold formatting
		// We can't assume there will be pairs of tags on a single line
		$STYLE_PATTERN = '#'. '(<b>|</b>|'.self::P_FONT_TAG_START.'|</font>)' .'#';
		$altered_line = preg_replace( $STYLE_PATTERN, '', $line );
		return $altered_line;
	}// remove_xml_styles()
	
	private static function remove_xml_styles_except_color( $line )
	{
		// Handles size and bold formatting
		// We can't assume there will be pairs of tags on a single line
		$STYLE_PATTERN = '#'. '(<b>|</b>| size="[[:digit:]]{1,}">)' .'#';
		$altered_line = preg_replace( $STYLE_PATTERN, '', $line );
		return $altered_line;
	}// remove_xml_styles_except_color()
	
	private static function remove_xml_color( $line )
	{
		$STYLE_PATTERN = '#'. '('.self::P_FONT_TAG_START.'|</font>)' .'#';
		$altered_line = preg_replace( $STYLE_PATTERN, '', $line );
		return $altered_line;
	}// remove_xml_color()
	
}// MOTD
?>