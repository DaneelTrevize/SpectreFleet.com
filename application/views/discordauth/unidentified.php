		
		<p>Ignored Roles:</p>
		<?php
		echo discord_roles( Discord_model::IGNORED_ROLES, $roles_data, FALSE );
		?>
		
		<hr>
		
		<p>Unidentified Members with unignored roles: <?php echo count( $unidentified_members ); ?></p>
		<table class="table table-striped table_valign_m">
		<thead>
			<tr>
				<th class="aligncenter">ID</th>
				<th class="aligncenter">Username/Nickname</th>
				<th class="aligncenter">Roles</th>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach( $unidentified_members as $member_data )
		{
			echo '<tr>';
			echo '<td><code>&lt;@'. $member_data['DiscordID'] .'&gt;</code></td>';
			
			echo '<td>';
				echo ( $member_data['nickname'] !== NULL ) ? $member_data['nickname'] : $member_data['username'];
			echo '</td>';
			
			echo '<td class="aligncenter">'. discord_roles( $member_data['role_ids'], $roles_data ) .'</td>';
			echo '</tr>';
		} ?>
		</tbody>
		</table>
		
		<hr>
		
		<p>Unidentified Users with permissions (ignoring Submitters) or Special Interest Groups: <?php echo count( $unidentified_users ); ?></p>
		<table class="table table-striped table_valign_m">
		<thead>
			<tr>
				<th class="aligncenter">ID</th>
				<th class="aligncenter">CharacterName</th>
				<th class="aligncenter">Rank</th>
				<th class="aligncenter">Editor</th>
				<th class="aligncenter">Admin</th>
				<th class="aligncenter">Has S.I.G.s?</th>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach( $unidentified_users as $user_data )
		{
			echo '<tr>';
			echo '<td>'. $user_data['UserID'] .'</td>';
			echo '<td>'. $user_data['CharacterName'] .'</td>';
			echo '<td class="aligncenter">'. $rank_names[$user_data['Rank']] .'</td>';
			echo '<td class="aligncenter">'. $role_names[$user_data['Editor']] .'</td>';
			echo '<td class="aligncenter">'. $admin_names[$user_data['Admin']] .'</td>';
			echo '<td class="aligncenter">'. ($user_data['hasGroups'] === 't' ? 'Yes' : 'No') .'</td>';
			echo '</tr>';
		} ?>
		</tbody>
		</table>