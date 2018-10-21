<?php
	if( !empty($fleets) )
	{ ?>
	<div class="text-muted">
		<i class="fa fa-sort-amount-desc" aria-hidden="true"></i>&nbsp;Ordered by Date ascending
	</div>
	<table class="table table-striped table_valign_m<?php if( isset($HIGHLIGHT_NEXT_FLEET) ) echo ' future_fleets'; ?>">
		<thead>
		<tr>
			<th>Type</th>
			<th class="aligncenter">Date</th>
			<th class="alignright">Time</th>
			<?php 
			if( !$single_FC )
			{
				echo' <th class="col-md-2 aligncenter"><span class="visible-lg-inline">Fleet </span>Commander</th>';
			} ?>
			<th class="aligncenter">Form-up</th>
			<th class="aligncenter"><span class="hidden-xs hidden-sm">Online </span>Fits</th>
			<th class="aligncenter">Fleet Status</th>
			<th class="write_v aligncenter"><span class="hidden-xs">Invites </span>Requested</th>
			<th class="write_v aligncenter"><span class="hidden-xs">Invites </span>Cancelled</th>
			<th class="write_v aligncenter"><span class="hidden-xs">Invitees </span>In Fleet</th>
			<th class="write_v aligncenter"><span class="hidden-xs">Invites </span>Sent</th>
		</tr>
		</thead>
		<tbody>
	<?php
	$API_ENDED_datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', Activity_model::API_15MIN_LOGGING_END_DATE );
	$latestDetected_datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', $latestDetected );
	
	foreach( $fleets as $fleet )
	{
		//echo '<pre>'. print_r( $fleet, TRUE ) .'</pre>';
		echo '<tr>';
			echo '<td>';
			echo '<span class="fa-stack">
			<i class="fa fa-circle-thin fa-stack-2x"></i>';
			
			$type_class = '';
			if( $fleet->type === 'Highsec' )
			{
				$type_class = ' type_highsec';
			}
			elseif( $fleet->type === 'Lowsec' )
			{
				$type_class = ' type_lowsec';
			}
			elseif( $fleet->type === 'Nullsec' )
			{
				$type_class = ' type_nullsec';
			}
			elseif( $fleet->type === 'Special' )
			{
				$type_class = ' type_special';
			}
			elseif( $fleet->type === 'Training' )
			{
				$type_class = ' type_training';
			}
			//echo $fleet->type;	// No need for htmlentities( , ENT_QUOTES ) ?
			echo '<i class="fa fa-circle fa-stack-1x' .$type_class. '"></i>';
			echo '</span>';
			echo '</td>';
			
			$fleetTime_datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', $fleet->fleetTime );
			$fleet_url = '/activity/fleet/'. $fleetTime_datetime->format( 'Y-m-d/H:i' );
			echo '<td><a href="'.$fleet_url.'">';
			echo '<span class="hidden-xs">'. $fleetTime_datetime->format( 'F' ) .'&nbsp</span>' . $fleetTime_datetime->format( 'jS' ) . '</a></td>';
			echo '<td class="alignright"><a href="'.$fleet_url.'">' . $fleetTime_datetime->format( 'H:i' );
			echo '</a></td>';
			
			if( !$single_FC )
			{
				echo '<td class="aligncenter">';
				$UserID = $fleet->FC_ID;
				echo '<a href="/activity/FC/'. $UserID .'">';
				if( property_exists( $fleet, 'CharacterName' ) )
				{
					echo $fleet->CharacterName;
				}
				else
				{
					echo 'FC:'.$UserID;
				}
				echo '</a>';
				echo '</td>';
			}
			
			echo '<td class="aligncenter">';
			if( !$fleet->locationExact ) echo 'Near ';
			if( property_exists( $fleet, 'solarSystemName' ) && $fleet->solarSystemName != NULL )
			{
				echo link_solar_system( $fleet->solarSystemName );
			}
			else
			{
				if( $fleet->locationID != NULL )
				{
					echo $fleet->locationID;
				}/*
				else
				{
					echo 'Undecided';
				}*/
			}
			echo '</td>';
			
			echo '<td class="aligncenter">';
			$fleetID = $fleet->doctrineID;
			if( $fleetID != NULL )
			{
				echo '<a href="/doctrine/fleet/'.$fleetID.'">';
				echo '<span class="hidden-xs hidden-sm">';
				if( $fleet->fleetName != NULL )
				{
					echo $fleet->fleetName;
				}
				else
				{
					echo 'FleetID:'.$fleetID;
				}
				echo '</span>';
				echo '<span class="visible-xs-inline visible-sm-inline">';
				echo '<i class="fa fa-wrench fa-fw" aria-hidden="true"></i>';
				echo '</span>';
				echo '</a>';
			}
			echo '</td>';
			
			echo '<td class="aligncenter">';
			$lastDetected_datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', $fleet->lastDetected );
			if( $lastDetected_datetime > $API_ENDED_datetime )
			{
				// Was after regular API era
				
				// We can't assume that there was any update reasonably soon after the fleet start that it should have still been listed to confirm it wasn't cancelled?
				//if( $latestDetected_datetime > $fleetTime_datetime && $lastDetected_datetime < $fleetTime_datetime )
				
				// Assume no cancellations
				
				if( $fleetTime_datetime >= $currentEVEtime )
				{
					// Future fleet
					echo '<i class="fa fa-calendar fa-2x" aria-hidden="true"></i>';
				}
				else
				{
					// Past fleet
					echo '<i class="fa fa-calendar-check-o fa-2x" aria-hidden="true"></i>';
				}
			}
			else
			{
				// Was before/during regular API era
				if( $fleetTime_datetime >= $latestDetected_datetime )
				{
					// Future fleet
					if( $lastDetected_datetime >= $latestDetected_datetime )
					{
						echo '<i class="fa fa-calendar fa-2x" aria-hidden="true"></i>';
					}
					else
					{
						echo '<i class="fa fa-calendar-o fa-2x text-muted" aria-hidden="true"></i>';
					}
				}
				else
				{
					// Past fleet
					if( $lastDetected_datetime >= $fleetTime_datetime->sub( new DateInterval( Activity_model::QUERY_PERIOD_WITH_WIGGLE_ROOM ) ) )
					{
						// Was still listed close to start time
						echo '<i class="fa fa-calendar-check-o fa-2x" aria-hidden="true"></i>';
					}
					else
					{
						// Was cancelled before start time
						echo '<i class="fa fa-calendar-times-o fa-2x text-muted" aria-hidden="true"></i>';
					}
				}
			}
			echo '</td>';
			
			echo '<td class="aligncenter">';
				echo $fleet->fleetScheduled == NULL ? 0 : $fleet->requestCount;
			echo '</td>';
			echo '<td class="aligncenter">';
				echo $fleet->fleetScheduled == NULL ? '' : $fleet->cancelled;
			echo '</td>';
			echo '<td class="aligncenter">';
				echo $fleet->fleetScheduled == NULL ? '' : $fleet->detected;
			echo '</td>';
			echo '<td class="aligncenter">';
				echo $fleet->fleetScheduled == NULL ? '' : $fleet->invitesSent;
			echo '</td>';
		echo "</tr>\n";
	}
	?>
		</tbody>
	</table><?php
	} ?>