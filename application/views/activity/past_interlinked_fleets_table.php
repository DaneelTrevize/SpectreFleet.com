
<div>
    <div class="text-muted">
		Form-up times of recent fleets. Click an entry to view the fleet details:<br>
	</div>
	<!--<div class="pull-right">
		<strong>Key: </strong>
		<div class="past_fleet past_key">
			<div>Scheduled</div>
		</div>
		<div class="past_fleet_unscheduled past_key">
			<div>Unscheduled</div>
		</div>
	</div>-->
	<table class="table table-bordered past_fleets">
		<thead>
			<tr>
				<th class="aligncenter">
					<i class="fa fa-chevron-down" aria-hidden="true"></i>&nbsp;Today
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$day_fleets_list = array();
			foreach( $fleets as $fleet )
			{
				$day_fleets_list[$fleet->Day][] = $fleet;
			}
			
			echo '<tr>';
			
			for( $day = 0; $day >= -7; $day-- )
			{
				echo '<td class="aligncenter';
				echo ($day <= -5) ? ' hidden-xs' : '';
				echo ($day <= -7) ? ' hidden-sm' : '';
				echo '" style="vertical-align: bottom;">';
				if( array_key_exists( $day, $day_fleets_list ) )
				{
					$day_fleets = $day_fleets_list[$day];
					$day_fleets = array_reverse( $day_fleets );
					foreach( $day_fleets as $fleet )
					{
						//echo '<pre>'. print_r( $fleet, TRUE ) .'</pre>';
						$scheduled = $fleet->fleetTime != NULL;
						$active = $fleet->firstDetected != NULL;
						echo '<div class="past_fleet';
							if( $scheduled )
							{
								echo '"><div>';
								$fleetTime_dtz = DateTime::createFromFormat( 'Y-m-d H:i:se', $fleet->fleetTime );
								echo '<a href="/activity/fleet/'. $fleetTime_dtz->format( 'Y-m-d/H:i' ) .'">';
								echo '<strong>' . $fleetTime_dtz->format( 'H:i' ) /*.'&nbsp;<i class="fa fa-calendar-check-o fa-fw" aria-hidden="true"></i>'*/. '</strong>';
								echo '</a>';
							}
							else
							{
								echo '_unscheduled"><div>';
								$fleetTime_dtz = DateTime::createFromFormat( 'Y-m-d H:i:se', $fleet->firstDetected );
								echo '<a href="/activity/active/'. $fleetTime_dtz->format( 'Y-m-d/H:i:s' ) .'/'. $fleet->XUPNumber .'">';
								echo '<strong>' . $fleetTime_dtz->format( 'H:i' ) /*.'&nbsp;<i class="fa fa-lightbulb-o fa-fw" aria-hidden="true"></i>'*/. '</strong>';
								echo '</a>';
							}
							echo '</div>';
						echo'</div>';
					}
				}
				echo '</td>';
			}
			
			echo "</tr>\n";
			?>
		</tbody>
		<tfoot>
			<tr>
			<?php
			$today_dt = new DateTimeImmutable( 'now', new DateTimeZone( 'UTC' ) );
			for( $day = 0; $day >= -7; $day-- )
			{
				$day_dt = $today_dt->sub( new DateInterval('P'.abs($day).'D') );
				echo '<td class="col-md-1 aligncenter';
				echo ($day <= -5) ? ' hidden-xs' : '';
				echo ($day <= -7) ? ' hidden-sm' : '';
				echo '">';
				echo $day_dt->format( 'l' ) . "<br>\n";
				echo $day_dt->format( 'jS F' );
				echo '</td>';
			} ?>
			</tr>
		</tfoot>
	</table>
</div>
