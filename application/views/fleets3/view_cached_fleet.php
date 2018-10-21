
			<h2>Recent Fleet</h2>
			
			<p>Most recent update: <?php
			$most_recent_member = $fleet_members[0];
			$datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', $most_recent_member->expires );
			echo $datetime->format( 'F jS H:i:s' ); ?></p>
			<p>Total participants across duration: <?php echo count($fleet_members); ?></p>
			
			<div class="row">
			<?php
			foreach( $members_categories_tables as $sf_class_name => $fleet_class_html )
			{
				echo $fleet_class_html;
			} ?>
			</div>
			
			<br>
			<p>Members hierarchy and locations:</p>
			
			<table class="table table-striped table_valign_m">
				<thead>
					<tr>
						<th>wing_id</th>
						<th>squad_id</th>
						<th>character_id</th>
						<th hidden>role</th>
						<th>role_name</th>
						<th>ship_type_id</th>
						<th>solarSystemName</th>
						<th>takes_fleet_warp</th>
					</tr>
				</thead>
				<tbody>
			<?php
			foreach( $fleet_members as $fleet )
			{
				//echo '<pre>'. print_r( $fleet, TRUE ) .'</pre>';
				
				echo '<tr>';
					
					$wing_name = $fleet_wings_squads['wing_names'][$fleet->wing_id];
					echo '<td>'.$wing_name.'</td>';
					$squad_name = $fleet_wings_squads['squad_names'][$fleet->squad_id];
					echo '<td>'.$squad_name.'</td>';
					
					$character_id = $fleet->character_id;
					echo '<td data-eve-character-id="'.$character_id.'"><img src="https://imageserver.eveonline.com/Character/'.$character_id.'_64.jpg" class="img-rounded img40px"></td>';
					
					echo '<td hidden>'.$fleet->role.'</td>';
					echo '<td>'.$fleet->role_name.'</td>';
					
					$ship_type_id = $fleet->ship_type_id;
					echo '<td data-eve-type-id="'.$ship_type_id.'"><img src="https://imageserver.eveonline.com/Type/'. $ship_type_id .'_64.png" title="'. $ship_type_id .'" class="img-rounded img40px"></td>';
					
					echo '<td data-eve-solar-system-id="'.$fleet->solar_system_id.'">'.$fleet->solarSystemName.'</td>';
					
					echo '<td>'.($fleet->takes_fleet_warp ? 'Yes' : 'No').'</td>';
				echo "</tr>\n";
			}
			?>
				</tbody>
			</table>
