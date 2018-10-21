
			<h2>View Special Interest Group: <?php echo $group['groupName']; ?></h2>
			
			<div class="col-sm-12 entry-content">
				Members count: <?php echo count( $users ); ?>
				<br>
				<br>
				
				<table class="table table-striped table_valign_m">
				<thead>
					<tr>
						<th class="aligncenter">ID</th>
						<th class="aligncenter">Character</th>
						<th class="aligncenter">Rank</th>
						<th class="aligncenter">Editor</th>
						<th class="aligncenter">Admin</th>
						<th class="aligncenter">Discord ID</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach( $users as $user_data )
					{
						echo '<tr>';
						echo '<td>'. $user_data['UserID'] .'</td>';
						
						$CharacterID = $user_data['CharacterID'];
						$CharacterName = $user_data['CharacterName'];
						echo '<td><a href="https://zkillboard.com/character/'.$CharacterID.'/"><img class="img-rounded" src="https://imageserver.eveonline.com/Character/'.$CharacterID.'_32.jpg" alt="'.$CharacterName.'">&nbsp;'.$CharacterName.'</a></td>';
						
						echo '<td class="aligncenter">'. $rank_names[$user_data['Rank']] .'</td>';
						echo '<td class="aligncenter">'. $role_names[$user_data['Editor']] .'</td>';
						echo '<td class="aligncenter">'. $admin_names[$user_data['Admin']] .'</td>';
						
						echo '<td>';
						if( $user_data['DiscordID'] === NULL )
						{
							echo 'Unknown!';
						}
						else
						{
							echo '<code>&lt;@'. $user_data['DiscordID'] .'&gt;</code>';
						}
						echo '</td>';
						
						echo '</tr>';
					}
					?>
				</tbody>
				</table>
			</div>