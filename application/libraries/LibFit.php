<?php if( !defined('BASEPATH') ) exit ('No direct script access allowed');

/**
 * Fit parsing library.
 *
 * @author Daneel Trevize
 */

class LibFit
{
	
	/*
	*  Relocated from Eve_SDE_model, old?:
	*  Identify ship type from first line
	*  Make model determine slot counts for non-T3C ships
	*  Make fit parser process each line to SDE-validate the type of item(s) in the line. Also support [Empty slots?
	*  Keep lists of items in order & split groups as per linebreaks?
	*  Identify, separate and use subsystem group if ship is T3C? To determine dynamic slot count
	*  Count off modules per rack until slot count, note any remainders. Additionally enforce rack/slot verification?
	*  Similarly identify, separate drones, put all in drone bay? Or count off drones until bay full?
	*  Move remainders to cargo, note that fit has been 'edited', presumably because of SDE changes.
	*/
	
	const RACK_MAX = 8;
	const RIGS_MAX = 3;
	const SUBSYSTEMS_MAX = 4;
	const FIRST_LOW_SLOT = 0;
	const LAST_LOW_SLOT = 7;
	const FIRST_MID_SLOT = 8;
	const LAST_MID_SLOT = 15;
	const FIRST_HIGH_SLOT = 16;
	const LAST_HIGH_SLOT = 23;
	const FIRST_RIG_SLOT = 24;
	const LAST_RIG_SLOT = 26;
	const FIRST_SUBSYSTEM_SLOT = 27;
	const LAST_SUBSYSTEM_SLOT = 30;
	
	private static function RACK_INDEXES()
	{
		return array(
			'L' => self::FIRST_LOW_SLOT,
			'M' => self::FIRST_MID_SLOT,
			'H' => self::FIRST_HIGH_SLOT,
			'R' => self::FIRST_RIG_SLOT,
			'S' => self::FIRST_SUBSYSTEM_SLOT
		);
	}// RACK_INDEXES()
	
	
	private $CI;
	
	public function __construct()
	{
		$this->CI =& get_instance();	// Assign the CodeIgniter object to a variable
		$this->CI->load->model('Eve_SDE_model');
		
		$P_ITEM_NAME = '[[:alnum:] \'\-]{1,}';
		$P_OFFLINE_SUFFIX = '( /OFFLINE){0,1}+';
		
		$this->MODULE_WITHOUT_CHARGE_PATTERN_2G = '#^'. '('. $P_ITEM_NAME .')' .$P_OFFLINE_SUFFIX .'$#';
		$this->MODULE_WITH_CHARGE_PATTERN_3G = '#^'. '('. $P_ITEM_NAME .')' .',(?: ){0,1}'. '('. $P_ITEM_NAME .')' .$P_OFFLINE_SUFFIX .'$#';
		$this->EMPTY_SLOT = '#^'. '\[[Ee]mpty '. '(?:(?:[Ll]ow|[Mm]ed|[Hh]igh|[Rr]ig|[Ss]ubsystem) ){0,1}' .'slot\]' .'$#';
		//$this->RIG_PATTERN_1G = '#^'. '('. '(?:Small|Medium|Large|Capital) ' .$P_ITEM_NAME .')' .'$#';
		$this->SUBSYSTEM_PATTERN_1G = '#^'. '('. '(?:Legion|Loki|Proteus|Tengu) ' .'(?:Offensive|Propulsion|Core|Defensive) - '. $P_ITEM_NAME .')' .'$#';
		$this->DRONE_OR_CARGO_PATTERN_2G = '#^'. '('. $P_ITEM_NAME .')' .' x([[:digit:]]{1,6})' .'$#';	// Arbitrary 6digits quantity limit
	}// __construct()
	
	
	public function parse_Fit( $FitText )
	{
		/*
		*	According to EVE and PyFA, all empty lines are optional, slot order isn't always preserved,
		*	and overfilled racks don't spill into cargo but are instead silently discarded.
		*
		*	From the first line find the ship type & rack sizes, if Strat Cruiser assume max rack sizes.
		*
		*	Find which rack is the first the ship type has, track current rack.
		*	For each line:
		*		If truely empty, slide down to the next non-empty one. Don't need to increment rack because checking rack Effect
		*		If [Empty, use the current rack tracker to try use a slot there.
		*			If no space in rack, assume [Empty in next slot, find that, use a slot there, set new current rack.
		*		If module, with or without charges, or rig, or subsystem, check rack Effect, try use correct rack
		*			If no space in rack, Remove item (to cargo, add Issue, or just lose origin line number?)
		*		If drone/cargo format, add to correct bay unless no drone bay exists, else cargo. No need to subsequently fix drones?
		*	
		*	If Strat Cruiser, recalculate rack sizes, Remove excess items to cargo, add Issue?
		*
		*	Ignores rig sizes vs ship type, calibration costs.
		*/
		
		$issues = array();
		$fit_name = NULL;
		$shipID = NULL;
		$slots = array();
		$drones = array(
			'item' => array(),
			'quantity' => array()
		);
		$cargo = array(
			'item' => array(),
			'quantity' => array()
		);
		
		// Explode original fit string based on Windows line breaks
		$lines = explode( "\r\n", $FitText );
		$line_index = 0;
		$final_line_index = count($lines) - 1;
		
		if( $final_line_index < 2 )
		{
			$issues[] = 'Insufficient lines detected';
		}
		
		if( empty( $issues ) )
		{
			$PATTERN_TYPE_AND_NAME = '#'. '\['. '(.{1,}?)' .', '. '(.{1,})' .'\]' .'#';
			if( preg_match( $PATTERN_TYPE_AND_NAME, $lines[$line_index], $matches ) === 1 )
			{
				// htmlentities() because other rows should be checked against trusted db text
				$ship_typeName = htmlentities( $matches[1], ENT_QUOTES );
				$fit_name = htmlentities( $matches[2], ENT_QUOTES );
			}
			else
			{
				$issues[] = 'Invalid ship type and fit name line';
			}
		}
		
		if( empty( $issues ) )
		{
			$shipDetails = $this->CI->Eve_SDE_model->ship_typeName_to_details( $ship_typeName );
			if( count( $shipDetails ) != 1 )
			{
				$issues[] = 'Unmatched ship type: ' . $ship_typeName;
			}
			else
			{
				$shipID = $shipDetails[0]['typeID'];
				$isStrategicCruiser = $shipDetails[0]['isStrategicCruiser'];
				$rack_sizes = $this->get_rack_sizes( $shipID, $isStrategicCruiser );
				
				//log_message( 'error', print_r( $rack_sizes, TRUE ) );
			}
		}
		
		if( empty( $issues ) )
		{
			$module_racks = array(
				'L' => array(),	// Each are to be an associative array of slotID => itemID
				'M' => array(),
				'H' => array(),
				'R' => array(),
				'S' => array()
			);
			$charge_racks = array(
				'L' => array(),	// Each are to be an associative array of slotID => itemID
				'M' => array(),
				'H' => array()
			);
			$current_rack = self::next_rack( NULL, $rack_sizes, $module_racks );
			
			do {
				$line_index++;
				$line = $lines[$line_index];
				
				if( $line == '' )
				{
					// Slide down to the next non-empty line
					while( $line_index+1 <= $final_line_index && $lines[$line_index+1] == '' ) $line_index++;
				}
				elseif( preg_match( $this->EMPTY_SLOT, $line ) )
				{
					// [Empty slot
					// Use $current_rack to try use a slot there, else try one in next_rack()
					$slotID = self::current_slot( $current_rack, $rack_sizes, $module_racks );
					if( $slotID === FALSE )
					{
						$current_rack = self::next_rack( $current_rack, $rack_sizes, $module_racks );
						$slotID = self::current_slot( $current_rack, $rack_sizes, $module_racks );
					}
					if( $slotID !== FALSE )
					{
						$module_racks[$current_rack][$slotID] = NULL;	// Will use up a slot, doesn't have to be empty array.
					}
					else
					{
						$issues[] = 'Line '.($line_index+1).': Unable to find a non-empty rack to allocate an Empty slot in';
					}
				}
				elseif( preg_match( $this->SUBSYSTEM_PATTERN_1G, $line, $matches ) )
				{
					$subsystemName = $matches[1];
					$subsystemID = $this->CI->Eve_SDE_model->typeName_to_typeID( $subsystemName );
					if( $subsystemID == NULL )
					{
						$issues[] = 'Line '.($line_index+1).': Unmatched subsystem type: ' .$subsystemName;
					}
					else
					{
						$rack = $this->CI->Eve_SDE_model->typeID_to_rack( $subsystemID );
						if( $rack != Eve_SDE_model::SUBSYSTEM_SLOT_EFFECTID )
						{
							$issues[] = 'Line '.($line_index+1).': Wrong rack for module type: ' .$subsystemName;
							$subsystemID = NULL;
						}
					}
					if( $subsystemID != NULL )
					{
						$slotID = self::current_slot( 'S', $rack_sizes, $module_racks );
						if( $slotID !== FALSE )
						{
							$module_racks['S'][$slotID] = $subsystemID;
						}
						else
						{
							self::put_item_in_cargo( $cargo, $subsystemID );
							
							$issues[] = 'Line '.($line_index+1).': No empty slot for subsystem: ' .$subsystemName;
						}
					}
				}
				elseif( preg_match( $this->DRONE_OR_CARGO_PATTERN_2G, $line, $matches ) )
				{
					$cargoName = $matches[1];
					$cargoID = $this->CI->Eve_SDE_model->typeName_to_typeID( $cargoName );
					if( $cargoID == NULL )
					{
						$issues[] = 'Line '.($line_index+1).': Unmatched cargo/drone set: ' .$cargoName;
					}
					else
					{
						// Is this a Drone stack & do we have a drone bay for it?
						if( $rack_sizes['D'] && $this->CI->Eve_SDE_model->is_drone( $cargoID ) )
						{
							$drone_stack = count( $drones['item'] );
							$drones['item'][$drone_stack] = $cargoID;
							$drones['quantity'][$drone_stack] = $matches[2];
						}
						else
						{
							self::put_item_in_cargo( $cargo, $cargoID, $matches[2] );
						}
					}
				}
				else
				{
					$moduleID = NULL;
					$chargeID = NULL;
					
					if( preg_match( $this->MODULE_WITH_CHARGE_PATTERN_3G, $line, $matches ) )
					{
						$moduleName = $matches[1];
						$moduleID = $this->CI->Eve_SDE_model->typeName_to_typeID( $moduleName );
						
						$chargeName = $matches[2];
						$chargeID = $this->CI->Eve_SDE_model->typeName_to_typeID( $chargeName );
						if( $chargeID == NULL )
						{
							$issues[] = 'Line '.($line_index+1).': Unmatched charge type: ' .$chargeName;
						}
					}
					elseif( preg_match( $this->MODULE_WITHOUT_CHARGE_PATTERN_2G, $line, $matches ) )
					{
						$moduleName = $matches[1];
						$moduleID = $this->CI->Eve_SDE_model->typeName_to_typeID( $moduleName );
					}
					
					if( $moduleID == NULL )
					{
						$issues[] = 'Line '.($line_index+1).': No valid item was detected in: ' .$line;
					}
					else
					{
						$rack_effectID = $this->CI->Eve_SDE_model->typeID_to_rack( $moduleID );
						$rack_key = self::get_rack_from_effectID( $rack_effectID );
						
						//log_message( 'error', 'Considering '. $rack_key .':'. $moduleName );
						
						if( array_key_exists( $rack_key, $module_racks ) && array_key_exists( $rack_key, $rack_sizes ) &&count( $module_racks[$rack_key] ) < $rack_sizes[$rack_key] )
						{
							$slotID = self::current_slot( $rack_key, $rack_sizes, $module_racks );
							//log_message( 'error', 'Considering c'. $current_rack .'|r'. $rack_key .'|s'. $slotID .'|m'. $moduleName );
							if( $slotID !== FALSE )
							{
								$module_racks[$rack_key][$slotID] = $moduleID;
								$current_rack = $rack_key;	// Only assign this on successful rack usage, such as now?
								
								if( $chargeID != NULL )
								{
									// Test if rack should permit charges?
									
									$charge_racks[$rack_key][$slotID] = $chargeID;
								}
							}
							else
							{
								//log_message( 'error', 'rack:'. $rack_key .' moduleName:'. $moduleName );
								$issues[] = 'Line '.($line_index+1).': Unexpected failure to find empty slot in rack:'. $rack_key;
							}
						}
						else
						{
							self::put_item_in_cargo( $cargo, $moduleID );
							if( $chargeID != NULL )
							{
								self::put_item_in_cargo( $cargo, $chargeID );	// Puts 1 instead of module's capacity
							}
							
							$issues[] = 'Line '.($line_index+1).': No empty slot for module: ' .$moduleName;
						}
					}
				}
				
				//if( !empty( $issues ) ) break;	// Don't keep looping, we're probably out of sync with racks.
				
			} while( $line_index < $final_line_index );
		
			//log_message( 'error', print_r( $module_racks, TRUE ) );
		}
		
		// fix_strategic_cruiser_racks() needs ever possible slotID to be a valid slots index. Doctrine_model won't insert empty slots into DB.
		if( empty( $issues ) )
		{
			$slots = $this->get_empty_ship_slots( $shipID, $isStrategicCruiser );
			foreach( $module_racks as $rack => $m )
			{
				foreach( $m as $slotID => $moduleID )
				{
					$slots[$slotID]['moduleID'] = $moduleID;
					//$slots[$slotID]['moduleName'] = $moduleName;
				}
			}
			foreach( $charge_racks as $rack => $c )
			{
				foreach( $c as $slotID => $chargeID )
				{
					$slots[$slotID]['chargeID'] = $chargeID;
					//$slots[$slotID]['chargeName'] = $chargeName;
				}
			}
			//log_message( 'error', print_r( $slots, TRUE ) );
		}
		
		if( empty( $issues ) )
		{
			if( $isStrategicCruiser === 't' )	// Stupid SQL->PHP bool limitation
			{
				// Determine excess items and put them in cargo. Also check drone bay?
				$removed = self::fix_strategic_cruiser_racks( $slots, $issues );
				foreach( $removed as $cargo_stack )
				{
					self::put_item_in_cargo( $cargo, $cargo_stack['cargoID'], $cargo_stack['cargoCount'] );
				}
			}
		}
		
		return array(
			'issues' => $issues,
			'name' => $fit_name,
			'shipID' => $shipID,
			'slots' => $slots,
			'drones' => $drones,
			'cargo' => $cargo
		);
	}// parse_Fit()
	
	private function get_rack_sizes( $shipID, $isStrategicCruiser )
	{
		$attributes = $this->CI->Eve_SDE_model->ship_typeID_to_attributes( $shipID );
		
		// If Strategic Cruiser, lax slots limits, else use attributes
		if( $isStrategicCruiser === 't' )	// Stupid SQL->PHP bool limitation
		{
			$rack_sizes = array(
				'L' => self::RACK_MAX,	// True values only known after adding subsystems
				'M' => self::RACK_MAX,
				'H' => self::RACK_MAX,
				'R' => self::RIGS_MAX,
				'S' => self::SUBSYSTEMS_MAX,	// 4, but CCP's SDE still has maxSubSystems == 5, which is no longer achievable
				'D' => TRUE
			);
			/*
			foreach( $attributes as $a )
			{
				switch( $a['attributeName'] )
				{
					case 'rigSlots':
						$rack_sizes['R'] = $a['value'];
						break;
					case 'maxSubSystems':
						$rack_sizes['S'] = $a['value'];
						break 2;	// Stop the foreach, because attributes were sorted and this is the last we care about
					default:
						break;	// Nothing, next foreach
				}
			}*/
		}
		else
		{
			$rack_sizes = array(
				'L' => 0,
				'M' => 0,
				'H' => 0,
				'R' => 0,	// Rookie ships lack any dgmAttributeTypes->rigSlots association in dgmTypeAttributes
				'S' => 0,
				'D' => FALSE
			);
			foreach( $attributes as $a )
			{
				switch( $a['attributeName'] )
				{
					case 'lowSlots':
						$rack_sizes['L'] = $a['value'];
						break;
					case 'medSlots':
						$rack_sizes['M'] = $a['value'];
						break;
					case 'hiSlots':
						$rack_sizes['H'] = $a['value'];
						break;
					case 'droneCapacity':
						$rack_sizes['D'] = ($a['value'] > 0);
						break;
					case 'rigSlots':
						$rack_sizes['R'] = $a['value'];
						break 2;	// Stop the foreach, because attributes were sorted and this is the last we care about
					default:
						break;	// Nothing, next foreach
				}
			}
		}
		
		return $rack_sizes;
	}// get_rack_sizes()
	
	private static function next_rack( $current_rack, $rack_sizes, $module_racks )
	{
		// Find which rack we're in and, if we're not at the start of a non-size-0 one, move up to the next non-size-0 one
		
		$next_rack = $current_rack;
		do {
			switch( $next_rack )
			{
				case NULL:
					$next_rack = 'L';
					break;
				case 'L':
					$next_rack = 'M';
					break;
				case 'M':
					$next_rack = 'H';
					break;
				case 'H':
					$next_rack = 'R';
					break;
				case 'R':
					$next_rack = 'S';
					break;
				case 'S':
				default:
					$next_rack = FALSE;
					break;
			}
			
			// If there are less modules in this rack than it can fit, return this rack
			if( count( $module_racks[$next_rack] ) < $rack_sizes[$next_rack] ) return $next_rack;
			
		} while( $next_rack != FALSE );
		
		return $next_rack;
	}// next_rack()
	
	private static function current_slot( $current_rack, $rack_sizes, $module_racks )
	{
		// Check there's another empty slot in the current rack, else FALSE
		
		$modules_count = count( $module_racks[$current_rack] );
		if( $modules_count < $rack_sizes[$current_rack] )
		{
			return (self::RACK_INDEXES()[$current_rack]) + $modules_count;	// Start + count = unused slotID
		}
		else
		{
			return FALSE;
		}
	}// current_slot()
	
	private static function get_rack_from_effectID( $rack_effectID )
	{
		switch( $rack_effectID )
		{
			case Eve_SDE_model::LOW_SLOT_EFFECTID:
				return 'L';
			case Eve_SDE_model::HIGH_SLOT_EFFECTID:
				return 'H';
			case Eve_SDE_model::MID_SLOT_EFFECTID:
				return 'M';
			case Eve_SDE_model::RIG_SLOT_EFFECTID:
				return 'R';
			case Eve_SDE_model::SUBSYSTEM_SLOT_EFFECTID:
				return 'S';
			default:
				return FALSE;
		}
	}// get_rack_from_effectID()
	
	private function put_item_in_cargo( &$cargo, $itemID, $quantity = 1 )
	{
		$cargo_stack = count( $cargo['item'] );
		$cargo['item'][$cargo_stack] = $itemID;
		$cargo['quantity'][$cargo_stack] = $quantity;
	}// put_item_in_cargo()
	
	
	public function get_empty_ship_slots( $shipID, $isStrategicCruiser )
	{
		$rack_sizes = $this->get_rack_sizes( $shipID, $isStrategicCruiser );
		
		$slotID = 0;
		$slots = array();
		foreach( self::RACK_INDEXES() as $rack_index => $rack_start )
		{
			switch( $rack_index )
			{
				case 'L':
				case 'M':
				case 'H':
					for( $rack_slot = 0; $rack_slot < $rack_sizes[$rack_index] ; $rack_slot++ )
					{
						$slotID = $rack_start + $rack_slot;
						$slots[$slotID] = array(
							'moduleID' => NULL,
							'moduleName' => NULL,
							'chargeID' => NULL,
							'chargeName' => NULL
						);
					}
					break;
				default:
					for( $rack_slot = 0; $rack_slot < $rack_sizes[$rack_index] ; $rack_slot++ )
					{
						$slotID = $rack_start + $rack_slot;
						$slots[$slotID] = array(
							'moduleID' => NULL,
							'moduleName' => NULL
						);
					}
					break;
			}
		}
		//print_r( $slots );
		return $slots;
	}// get_empty_ship_slots()
	
	public function fix_strategic_cruiser_racks( &$slots, &$issues = NULL )	// Needs to have return tested for failure!
	{
		if( $slots == NULL || !is_array($slots) )
		{
			return FALSE;
		}
		
		$subsystemIDs = array();
		for( $slotID = self::FIRST_SUBSYSTEM_SLOT; $slotID <= self::LAST_SUBSYSTEM_SLOT; $slotID++ )
		{
			$subsystemIDs[] = $slots[$slotID]['moduleID'];
		}
		if( count($subsystemIDs) == 0 )
		{
			return FALSE;
		}
		
		$slot_modifiers = $this->CI->Eve_SDE_model->subsystems_slot_modifiers( $subsystemIDs );
		//log_message( 'error', print_r($slot_modifiers,TRUE) );
		
		$rack_sizes = self::calculate_true_rack_sizes( $subsystemIDs );
		//log_message( 'error', print_r($rack_sizes,TRUE) );
		
		$rack_indexes = array(
			'L' => self::FIRST_LOW_SLOT,
			'M' => self::FIRST_MID_SLOT,
			'H' => self::FIRST_HIGH_SLOT
		);
		
		$grouped_removed = array();
		
		foreach( $rack_indexes as $rack_index => $rack_start )
		{
			// Remove all modules or [Empty slots per rack that are beyond the true rack max index
			// We assume Strategic Cruisers have at least 1 slot in all 3 chargable racks, even if [Empty
			// So we don't touch it, to ensure we generate at least 1 row per rack in "EFT" format
			for( $rack_slot = 1; $rack_slot < self::RACK_MAX; $rack_slot++ )
			{
				if( $rack_slot >= $rack_sizes[$rack_index] )
				{
					$slotID = $rack_start + $rack_slot;
					
					if( $slots[$slotID]['moduleID'] != NULL )	// Instead should test if $slotID key exists?!
					{
						// Excess item detected
						$item = $slots[$slotID];
						$itemID = $item['moduleID'];	// typeID
						
						// Merge the item(s) into stacks for going in cargo
						if( array_key_exists( $itemID, $grouped_removed ) )
						{
							$grouped_removed[$itemID]['cargoCount'] += 1;
						}
						else
						{
							$grouped_removed[$itemID] = array(
								'cargoID' => $itemID,
								'cargoCount' => 1
							);
							if( array_key_exists( 'moduleName', $item ) )
							{
								$grouped_removed[$itemID]['cargoName'] = $item['moduleName'];
							}
						}
						
						// Item was a module that had an associated charge
						if( $item['chargeID'] != NULL )
						{
							$itemID = $item['chargeID'];
							if( array_key_exists( $itemID, $grouped_removed ) )
							{
								$grouped_removed[$itemID]['cargoCount'] += 1;
							}
							else
							{
								$grouped_removed[$itemID] = array(
									'cargoID' => $itemID,
									'cargoCount' => 1
								);
								if( array_key_exists( 'chargeName', $item ) )
								{
									$grouped_removed[$itemID]['cargoName'] = $item['chargeName'];
								}
							}
						}
						
						// Report the excess item if desired
						if( $issues !== NULL )
						{
							// During parse_Fit(), when the itemName hasn't been kept (or the line index)
							$issues[] = 'Invalid Strategic Cruiser slot '.($slotID+1). ' (rack:'.$rack_index. ' subslot:' .($rack_slot+1). '), discarding module typeID: ' .$itemID;
						}
					}
					
					// Remove the item(s) from their slot
					unset( $slots[$slotID] );
				}
			}
		}
		
		return $grouped_removed;
	}// fix_strategic_cruiser_racks()
	
	private function calculate_true_rack_sizes( $subsystemIDs )
	{
		$slot_modifiers = $this->CI->Eve_SDE_model->subsystems_slot_modifiers( $subsystemIDs );
		//log_message( 'error', print_r($slot_modifiers,TRUE) );
		
		$rack_sizes = array(
			'L' => 0,
			'M' => 0,
			'H' => 0
		);
		// Total modifiers to produce rack sizes
		foreach( $slot_modifiers as $modifier )
		{
			switch( $modifier['attributeID'] )
			{
				case Eve_SDE_model::HIGH_SLOT_MODIFIER_ATTRIBUTEID:
					$rack_sizes['H'] = $rack_sizes['H'] + $modifier['value'];
					break;
				case Eve_SDE_model::MID_SLOT_MODIFIER_ATTRIBUTEID:
					$rack_sizes['M'] = $rack_sizes['M'] + $modifier['value'];
					break;
				case Eve_SDE_model::LOW_SLOT_MODIFIER_ATTRIBUTEID:
					$rack_sizes['L'] = $rack_sizes['L'] + $modifier['value'];
					break;
				default:	// Consider handling enabling drone bay?
					break;
			}
		}
		//log_message( 'error', print_r($rack_sizes,TRUE) );
		return $rack_sizes;
	}// calculate_true_rack_sizes()
	
	
	public function generate_EFT( $info, $fit_items )
	{
		if( $info == NULL || !is_array($info) )
		{
			return FALSE;
		}
		if( $fit_items == NULL || !is_array($fit_items) )
		{
			return FALSE;
		}
		
		$fit_items['cargo'] = array_merge( $fit_items['removed'], $fit_items['cargo'] );
		
		$EFT = '['.$info['shipName'].', '.$info['fitName'].']';
		
		foreach( $fit_items['slots'] as $slotID => $item )
		{
			if( $slotID == self::FIRST_MID_SLOT || $slotID == self::FIRST_HIGH_SLOT || $slotID == self::FIRST_RIG_SLOT || $slotID == self::FIRST_SUBSYSTEM_SLOT )
			{
				$EFT .= "\r\n";
			}
			
			if( $item['moduleID'] !== NULL )
			{
				if( array_key_exists( 'chargeName', $item ) && $item['chargeName'] != NULL )
				{
					$EFT .= "\r\n" . $item['moduleName'].', '.$item['chargeName'];
				}
				else
				{
					$EFT .= "\r\n" . $item['moduleName'];
				}
			}
			else
			{
				$EFT .= "\r\n[Empty slot]";
			}
		}
		
		$drones = $fit_items['drones'];
		if( !empty( $drones ) )
		{
			$EFT .= "\r\n\r\n";
			foreach( $drones as $drone_stack )
			{
				$EFT .= "\r\n" . $drone_stack['droneName'].' x'.$drone_stack['droneCount'];
			}
		}
		
		$cargo = $fit_items['cargo'];
		if( !empty( $cargo ) )
		{
			if( empty( $drones ) )
			{
				$EFT .= "\r\n";
			}
			$EFT .= "\r\n";
			foreach( $cargo as $cargo_stack )
			{
				$EFT .= "\r\n" . $cargo_stack['cargoName'].' x'.$cargo_stack['cargoCount'];
			}
		}
		
		return $EFT;
	}// generate_EFT()
	
	public function generate_DNA( $info, $fit_items )
	{
		if( $info == NULL || !is_array($info) )
		{
			return FALSE;
		}
		if( $fit_items == NULL || !is_array($fit_items) )
		{
			return FALSE;
		}
		
		$fit_items['cargo'] = array_merge( $fit_items['removed'], $fit_items['cargo'] );
		
		$DNA = "";
		 
		foreach( $fit_items['slots'] as $slotID => $item )
		{
			if( $slotID < self::FIRST_RIG_SLOT )	// Prefix new item
			{
				if( $item['moduleID'] !== NULL )
				{
					$DNA = $item['moduleID'] . ';1:' . $DNA;
				}
			}
			elseif( $slotID < self::FIRST_SUBSYSTEM_SLOT )	// Suffix new item (just rigs and subsystems?)
			{
				if( $item['moduleID'] !== NULL )
				{
					$DNA .= $item['moduleID'] . ';1:';
				}
			}
			else
			{
				if( $item['moduleID'] !== NULL )	// Prefix new item
				{
					$DNA = $item['moduleID'] . ';1:' . $DNA;
				}
			}
		}
		
		$drones = $fit_items['drones'];
		if( !empty( $drones ) )
		{
			foreach( $drones as $drone_stack )
			{
				$DNA .= $drone_stack['droneID'] . ';' . $drone_stack['droneCount'] . ':';
			}
		}
		
		$cargo = $fit_items['cargo'];
		if( !empty( $cargo ) )
		{
			foreach( $cargo as $cargo_stack )
			{
				$DNA .= $cargo_stack['cargoID'] . ';' . $cargo_stack['cargoCount'] . ':';
			}
		}
		
		$DNA = $info['shipID'] . ':' . $DNA . ':';
		
		return $DNA;
	}// generate_DNA()
	
}// LibFit
?>