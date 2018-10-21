			
			<div class="col-lg-6 col-lg-offset-1 col-md-7 col-md-offset-0 col-sm-10 col-sm-offset-1">
				<?php echo $bulletins_html; ?>
			</div>
			<div class="col-lg-4 col-lg-offset-0 col-md-5 col-md-offset-0 col-sm-10 col-sm-offset-1 alignright">
				<?php echo $kills_html; ?>
			</div>
			
			<?php echo $active_html; ?>
			
			<div class="col-xl-10 col-xl-offset-1 col-lg-12 col-lg-offset-0">
				<h2>Scheduled Fleets</h2>
			</div>
				
			<div class="col-xl-10 col-xl-offset-1 col-lg-12 col-lg-offset-0">
				<table class="table table-striped table_valign_m">
					<thead>
					<tr>
						<th class="hidden-sm hidden-xs">Type</th>
						<th class="col-md-2 alignright">Day & Date</th>
						<th class="alignright">Time</th>
						<th class="col-md-2 aligncenter">Fleet Commander</th>
						<th class="aligncenter">Form-up Location</th>
						<th class="col-md-2 aligncenter">Online Fits</th>
						<th>Additional Details</th>
					</tr>
					</thead>
					<tbody>
				<?php
				//echo '<pre>'. print_r( $fleets, TRUE ) .'</pre>';
				foreach( $fleets as $fleet )
				{
					echo '<tr>';
						echo '<td class="hidden-sm hidden-xs">'. $fleet['pretty_type'] .'</td>';
						
						echo '<td class="alignright">'. $fleet['pretty_date'] .'</td>';	// No need for htmlentities( , ENT_QUOTES ) ?
						echo '<td class="alignright">' . $fleet['time'] . '</td>';
						
						echo '<td class="aligncenter">';
						if( $fleet['FC_ID'] !== FALSE )
						{
							$CharacterName = $fleet['FC_ID']['CharacterName'];
							$UserID = $fleet['FC_ID']['UserID'];
							echo '<a href="/activity/FC/'. $UserID .'">'. $CharacterName .'</a>';
						}
						else
						{
							echo $fleet['FC'];
						}
						echo  '</td>';
						
						echo '<td class="aligncenter">';
						if( !$fleet['location_exact'] ) echo 'Near ';
						echo $fleet['location'];
						/*if( $fleet['location_ID'] === FALSE && $fleet['location'] != 'Undecided' && $fleet['location'] != 'Undetermined' )
						{
							echo '<small> (Unresolved)</small>';
						}*/
						echo '</td>';
						
						echo '<td class="aligncenter">';
						if( $fleet['doctrine'] != '' )
						{
							if( isset($fleet['doctrine_name']) && $fleet['doctrine_name'] !== FALSE )
							{
								echo '<a href="/doctrine/fleet/'.$fleet['doctrine'].'">'.$fleet['doctrine_name'].'</a>';
							}
							else
							{
								echo '<a href="/doctrine/fleet/'.$fleet['doctrine'].'">fleetID:'.$fleet['doctrine'].'</a><small> Missing?</small>';
							}
						}
						echo '</td>';
						
						echo '<td>' . $fleet['remaining_details'] . '</td>';
					echo "</tr>\n";
				}
				?>
					</tbody>
				</table>
			</div>