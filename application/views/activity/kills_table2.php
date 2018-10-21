<?php
if( !empty( $kills ) )
{ ?>
	<table class="table table-striped kills">
		<thead>
		<tr>
			<th class="alignright">Date & Time</th>
			<th class="col-md-1 aligncenter">Location</th>
			<th class="col-md-1 aligncenter">Involved</th>
			<th class="aligncenter">Ship</th>
			<th class="alignright">Value</th>
			<th class="aligncenter">Victim</th>
			<th><span class="hidden-sm hidden-xs">Full </span>Killmail</th>
		</tr>
		</thead>
		<tbody>
	<?php
	foreach( $kills as $kill )
	{
		//echo '<pre>'. print_r( $kill, TRUE ) .'</pre>';
		echo '<tr>';
		
			$kill_datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', $kill->time );
			$kill_datetime_formatted = $kill_datetime->format( 'F&\nb\sp;jS l H:i' );
			echo '<td class="alignright">' . $kill_datetime_formatted . '</td>';
			
			echo '<td class="aligncenter">';
			echo $kill->solar_system_name;
			echo '</td>';
			
			echo '<td class="aligncenter">' . $kill->attackers_count . '</td>';
			
			echo '<td>';
			echo '<img src="https://imageserver.eveonline.com/Type/'.$kill->victim_ship_type_ID.'_64.png" class="img-rounded img40px" alt="'.$kill->victim_ship_type_name.'">';
			echo '&nbsp;'. $kill->victim_ship_type_name;
			echo '</td>';
			
			echo '<td class="alignright text-nowrap">';
			if( property_exists( $kill, 'totalValue_text' ) )
			{
				echo $kill->totalValue_text;
			}
			else
			{
				echo '???';
			}
			echo '</td>';
			
			echo '<td>';
			$victim_character_ID = ($kill->victim_character_ID != NULL) ? $kill->victim_character_ID : '1';
			echo '<img src="https://imageserver.eveonline.com/Character/'.$victim_character_ID.'_64.jpg" class="img-rounded img40px" title="'.$kill->victim_name.'">';
			echo '&nbsp;' . $kill->victim_name;
			echo '</td>';
			
			echo '<td class="text-nowrap"><a href="https://zkillboard.com/kill/'. $kill->ID;
			echo '"><span class="hidden-sm hidden-xs">zKillboard&nbsp;</span>';
			echo '<i class="fa fa-external-link" aria-hidden="true"></i>';
			echo '</a></td>';
		echo "</tr>\n";
	}
	?>
		</tbody>
	</table>
<?php
} ?>