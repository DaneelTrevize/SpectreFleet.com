		
		<div class="alignright">
			<p>Discord Members API Cache Last Updated: <?php
		echo $guild_members[Discord_model::SPECTREFLEET_BOT]['expires']; ?></p>
		</div>
		
		<p>Members with roles: <?php echo count( $guild_members ); ?></p>
		<table class="table table-striped table_valign_m">
		<thead>
			<tr>
				<th class="col-md-2 col-sm-2 aligncenter">ID</th>
				<th class="col-md-4 col-sm-5 aligncenter">Name</th>
				<th class="col-md-1 col-sm-1 aligncenter">Avatar</th>
				<th class="col-md-5 col-sm-4 aligncenter">Roles</th>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach( $guild_members as $DiscordID => $member_data )
		{
			echo '<tr>';
			echo '<td><code>&lt;@'. $DiscordID .'&gt;</code></td>';
			
			echo '<td>';
				echo 'Name: <strong>'. $member_data['username'] .'</strong><br>';
				
				if( array_key_exists( 'nick', $member_data ) && $member_data['nick'] != '' )
				{
					echo 'SF Nickname: <strong>'. $member_data['nick'] .'</strong>';
				}
			echo '</td>';
			
			echo '<td class="aligncenter">'. discord_avatar( $DiscordID, $member_data, 32 ) .'</td>';
			echo '<td class="aligncenter">'. discord_roles( $member_data['roles'], $roles_data, TRUE ) .'</td>';
			echo '</tr>';
		} ?>
		</tbody>
		</table>