
	<?php
	if( empty($active_fleets) )
	{
		echo '<p>There don\'t appear to be any active fleets at this time. Please check in-game for the latest situation.</p>';
	}
	else
	{ ?>
	<table class="table table-striped table_valign_m current_active">
		<thead>
		<tr>
			<th>Type</th>
			<th class="aligncenter">Channel</th>
			<th class="col-md-2 aligncenter"><span class="visible-lg-inline">Fleet </span>Commander</th>
			<th class="aligncenter">Form-up<span class="visible-lg-inline"> Location</span></th>
			<th class="col-md-2 aligncenter"><span class="hidden-xs">Online </span>Fits</th>
			<th>Additional Details</th>
		</tr>
		</thead>
		<tbody>
		<?php
		//echo '<pre>'. print_r( $active_fleets, TRUE ) .'</pre>';
		foreach( $active_fleets as $active )
		{
			echo '<tr>';
				echo '<td>';
				echo '<span class="fa-stack">
				<i class="fa fa-circle-thin fa-stack-2x"></i>';
				
				$type_class = '';
				if( $active->type === 'Highsec' )
				{
					$type_class = ' type_highsec';
				}
				elseif( $active->type === 'Lowsec' )
				{
					$type_class = ' type_lowsec';
				}
				elseif( $active->type === 'Nullsec' )
				{
					$type_class = ' type_nullsec';
				}
				elseif( $active->type === 'Special' )
				{
					$type_class = ' type_special';
				}
				elseif( $active->type === 'Training' )
				{
					$type_class = ' type_training';
				}
				//echo $active->type;	// No need for htmlentities( , ENT_QUOTES ) ?
				echo '<i class="fa fa-circle fa-stack-1x' .$type_class. '"></i>';
				echo '</span>';
				echo '</td>';
				
				echo '<td class="aligncenter">' .'XUP~'. $active->XUPNumber . '</td>';
				
				echo '<td class="aligncenter">';
				$UserID = $active->FC_ID;
				echo '<a href="/activity/FC/'. $UserID .'">';
				if( property_exists( $active, 'CharacterName' ) )
				{
					echo $active->CharacterName;
				}
				else
				{
					echo 'FC:'.$UserID;
				}
				echo '</a>';
				echo '</td>';
				
				echo '<td class="aligncenter">';
				if( !$active->locationExact ) echo 'Near ';
				if( property_exists( $active, 'solarSystemName' ) && $active->solarSystemName != NULL )
				{
					echo link_solar_system( $active->solarSystemName );
				}
				else
				{
					if( $active->locationID != NULL )
					{
						echo $active->locationID;
					}/*
					else
					{
						echo 'Undecided';
					}*/
				}
				echo '</td>';
				
				echo '<td class="aligncenter">';
				$fleetID = $active->doctrineID;
				if( $fleetID != NULL )
				{
					echo '<a href="/doctrine/fleet/'.$fleetID.'">';
					echo '<span class="hidden-xs">';
					if( $active->fleetName != NULL )
					{
						echo $active->fleetName;
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
				
				echo '<td>' . $active->additionalDetails . '</td>';
			echo "</tr>\n";
		} ?>
		</tbody>
	</table>
	<?php
	} ?>
	