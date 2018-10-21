<?php
if( !empty($recent_favoured_scheduled_locations) )
{
	echo '<table class="table table-striped"><thead><tr>';
		echo '<th class="col-xs-7">System</th>';
		echo '<th class="col-xs-1">Uses</th>';
		echo '<th class="col-xs-3">Latest<span class="hidden-md hidden-sm"> Date</span></th>';
		echo '<th class="col-xs-1 alignright">Time</th>';
	echo '</tr></thead><tbody>';
	foreach( $recent_favoured_scheduled_locations as $location )
	{
		echo '<tr>';
		
		echo '<td>';
		echo link_solar_system( $location->solarSystemName );
		echo '</td>';
		
		echo '<td>';
		echo $location->Uses;
		echo '</td>';
		
		echo '<td>';
		$datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', $location->LatestUse );
		$fleet_url = '<a href="/activity/fleet/'.$datetime->format( 'Y-m-d/H:i' ).'">';
		echo $fleet_url;
		echo $datetime->format('F').'&nbsp;'.$datetime->format('jS');
		
		echo '<td class="alignright">';
		echo $fleet_url;
		echo $datetime->format('H:i').'</a>';
		echo '</td>';
		
		echo '</tr>';
	}
	echo '</tbody></table>';
} ?>