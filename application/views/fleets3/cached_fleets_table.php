<?php
if( !empty($recent_fleets) )
{ ?>
	<div class="alignright text-muted">
		<i class="fa fa-sort-amount-asc" aria-hidden="true"></i>&nbsp;Ordered by Date descending
	</div>
	<table class="table table-striped table_valign_m">
		<thead>
			</tr>
				<th>ID</th>
				<th>Date</th>
				<th>Time</th>
				<th>Size</th>
			</tr>
		</thead>
		<tbody>
	<?php
	foreach( $recent_fleets as $fleet )
	{
		//echo '<pre>'. print_r( $fleet, TRUE ) .'</pre>';
		
		echo '<tr>';
			$fleet_id = $fleet->fleet_id;
			$link_html = '<a href="/fleets3/fleet/'.$fleet_id.'">';
			echo '<td>'.$link_html . $fleet_id .'</a></td>';
			
			$datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', $fleet->last_detected );
			echo '<td>'.$link_html . '<span class="hidden-xs">'. $datetime->format( 'F' ) .'&nbsp</span>' . $datetime->format( 'jS' ) . '</a></td>';
			echo '<td>'.$link_html . $datetime->format( 'H:i' ) . '</a></td>';
			
			echo '<td>'.$link_html . $fleet->total.'</a></td>';
		echo "</tr>\n";
	}
	?>
		</tbody>
	</table>
	<?php
} ?>