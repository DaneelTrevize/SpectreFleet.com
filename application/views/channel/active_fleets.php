<?php
if( !empty($active_fleets) )
{ ?>
<div class="col-xl-10 col-xl-offset-1 col-lg-12 col-lg-offset-0">
	<h2>Active Fleets</h2>
</div>
	
<div class="col-xl-10 col-xl-offset-1 col-lg-12 col-lg-offset-0">
	<table class="table table-striped table_valign_m">
		<thead>
		<tr>
			<th class="hidden-xs">Type</th>
			<th class="aligncenter">Channel</th>
			<th class="col-md-2 aligncenter">Fleet Commander</th>
			<th class="aligncenter">Form-up Location</th>
			<th class="col-md-2 aligncenter">Online Fits</th>
			<th>Additional Details</th>
		</tr>
		</thead>
		<tbody>
		<?php
		//echo '<pre>'. print_r( $active_fleets, TRUE ) .'</pre>';
		foreach( $active_fleets as $active )
		{
			echo '<tr>';
				echo '<td class="hidden-xs">'. $active['pretty_type'] .'</td>';
				
				echo '<td class="aligncenter">' .'XUP~'. $active['XUPNumber'] . '</td>';
				
				echo '<td class="aligncenter">';
				if( $active['FC_ID'] !== FALSE )
				{
					$CharacterName = $active['FC_ID']['CharacterName'];
					$UserID = $active['FC_ID']['UserID'];
					echo '<a href="/activity/FC/'. $UserID .'">'. $CharacterName .'</a>';
				}
				else
				{
					echo $active['FC'];
				}
				echo  '</td>';
				
				echo '<td class="aligncenter">';
				if( !$active['location_exact'] ) echo 'Near ';
				echo $active['location'];
				/*if( $active['location_ID'] === FALSE && $active['location'] != 'Undecided' && $active['location'] != 'Undetermined' )
				{
					echo '<small> (Unresolved)</small>';
				}*/
				echo '</td>';
				
				echo '<td class="aligncenter">';
				if( $active['doctrine'] != '' )
				{
					if( isset($active['doctrine_name']) && $active['doctrine_name'] !== FALSE )
					{
						echo '<a href="/doctrine/fleet/'.$active['doctrine'].'">'.$active['doctrine_name'].'</a>';
					}
					else
					{
						echo '<a href="/doctrine/fleet/'.$active['doctrine'].'">fleetID:'.$active['doctrine'].'</a><small> Missing?</small>';
					}
				}
				echo '</td>';
				
				echo '<td>' . $active['remaining_details'] . '</td>';
			echo "</tr>\n";
		} ?>
		</tbody>
	</table>
	<br>
	<br>
</div>
<?php
}
?>