<?php
if( !empty($fleets) )
{ ?>
	<table class="table table-striped table_valign_m">
		<thead>
		<tr>
			<th class="col-md-2 aligncenter" colspan="2">First recorded</th>
			<th class="col-md-2 aligncenter" colspan="2">Last recorded</th>
			<th class="aligncenter">Channel</th>
			<th>Type</th>
			<?php 
			if( !$single_FC )
			{
				echo' <th class="aligncenter">Commander</th>';
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
		echo '<tr>';
			$firstDetected_datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', $fleet->firstDetected );
			echo '<td class="alignright">' . $firstDetected_datetime->format( 'F jS' ) . '</td>';
			echo '<td class="alignright">' . $firstDetected_datetime->format( 'H:i' ) . '</td>';
			
			$lastDetected_datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', $fleet->lastDetected );
			echo '<td class="alignright">' . $lastDetected_datetime->format( 'F jS' ) . '</td>';
			echo '<td class="alignright">' . $lastDetected_datetime->format( 'H:i' ) . '</td>';
			
			echo '<td class="aligncenter">' .'XUP~'. $fleet->XUPNumber . '</td>';
			
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
				echo  '</td>';
			}
			
			echo '<td class="aligncenter">';
			if( !$fleet->locationExact ) echo 'Near ';
			if( property_exists( $fleet, 'solarSystemName' ) )
			{
				echo link_solar_system( $fleet->solarSystemName );
			}
			else
			{
				echo $fleet->locationID;
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
} ?>