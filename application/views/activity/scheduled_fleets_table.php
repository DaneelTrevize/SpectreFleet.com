<?php
if( !empty($fleets) )
{ ?>
	<div class="alignright text-muted">
		<i class="fa fa-sort-amount-asc" aria-hidden="true"></i>&nbsp;Ordered by Date descending
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
			<th class="aligncenter"><span class="hidden-xs">Online </span>Fits</th>
			<th>Additional Details</th>
		</tr>
		</thead>
		<tbody>
	<?php
	foreach( $fleets as $fleet )
	{
		//echo '<pre>'. print_r( $fleet, TRUE ) .'</pre>';
		
		if( isset($HIGHLIGHT_NEXT_FLEET) )
		{
			$datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', $fleet->fleetTime );
			// Instead of this heuristic, should use channel active fleets data?
			if( $datetime < $currentEVEtime )
			{
				// Assuming ascending order by fleetTime
				continue;
			}
		}
		
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
			
			$datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', $fleet->fleetTime );
			$fleet_url = '/activity/fleet/'. $datetime->format( 'Y-m-d/H:i' );
			echo '<td><a href="'.$fleet_url.'">';
			echo '<span class="hidden-xs">'. $datetime->format( 'F' ) .'&nbsp</span>' . $datetime->format( 'jS' ) . '</a></td>';
			echo '<td class="alignright"><a href="'.$fleet_url.'">' . $datetime->format( 'H:i' );
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
				echo '<span class="hidden-xs">';
				if( $fleet->fleetName != NULL )
				{
					echo $fleet->fleetName;
				}
				else
				{
					echo 'FleetID:'.$fleetID;
				}
				echo '</span>';
				echo '<span class="visible-xs-inline">';
				echo '<i class="fa fa-wrench fa-fw" aria-hidden="true"></i>';
				echo '</span>';
				echo '</a>';
			}
			echo '</td>';
			
			echo '<td>' . $fleet->additionalDetails . '</td>';
		echo "</tr>\n";
	}
	?>
		</tbody>
	</table>
	<?php
	if( isset($HIGHLIGHT_NEXT_FLEET) )
	{ ?>
		<div class="aligncenter">
			<i class="fa fa-chevron-up" aria-hidden="true"></i>&nbsp;Next fleet scheduled to form&nbsp;<i class="fa fa-chevron-up" aria-hidden="true"></i><br>
			<br>
		</div>
	<?php
	}
	
} ?>