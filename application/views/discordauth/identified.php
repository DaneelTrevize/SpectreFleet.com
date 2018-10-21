
		<p>Ignored Roles:</p>
		<?php
		echo discord_roles( Discord_model::IGNORED_ROLES, $roles_data, FALSE );
		?>
		
		<hr>
		
		<p>Identified Users with permissions (ignoring Submitters): <?php echo count( $identified ); ?></p>
		
		<div class="col-sm-12">
			<div class="col-sm-4 col-sm-offset-1 aligncenter" id="show_all_users">
				Show All Users
			</div>
			<div class="col-sm-4 col-sm-offset-2 aligncenter" id="show_only_issues">
				Show Only Issues
			</div>
		</div>
		<br>
		<br>
		
		<table class="table table-striped table_valign_m">
		<thead>
			<tr>
				<th colspan="7" class="aligncenter show_right">Identity and Permissions, Groups</th>
				<th colspan="5" class="aligncenter">Roles</th>
			</tr>
			<tr>
				<th class="aligncenter">ID</th>
				<th class="aligncenter">CharacterName</th>
				<th class="aligncenter">Rank</th>
				<th class="aligncenter">Editor</th>
				<th class="aligncenter">Admin</th>
				<th class="aligncenter">Groups</th>
				<th class="aligncenter show_right">Discord ID</th>
				<!--<th class="aligncenter">Discord name</th>-->
				<th class="aligncenter">Current</th>
				<th class="aligncenter">Ignored</th>
				<th class="aligncenter">Expected</th>
				<th class="aligncenter">Add</th>
				<th class="aligncenter">Remove</th>
			</tr>
		</thead>
		<tbody class="show_right">
		<?php
		foreach( $identified as $userID => $user_data )
		{
			$missing_roles = array_diff( $user_data['expected_roles'], $user_data['role_ids'] );
			$revoke_roles = array_diff( $user_data['role_ids'], $user_data['expected_roles'] );
			$revoke_roles = array_diff( $revoke_roles, Discord_model::IGNORED_ROLES );
			
			echo '<tr class="identified_user'. ((!empty( $missing_roles ) || !empty( $revoke_roles )) ? ' role_issues' : '') .'">';
			echo '<td>'. $user_data['UserID'] .'</td>';
			echo '<td>'. $user_data['CharacterName'] .'</td>';
			echo '<td class="aligncenter">'. $rank_names[$user_data['Rank']] .'</td>';
			echo '<td class="aligncenter">'. $role_names[$user_data['Editor']] .'</td>';
			echo '<td class="aligncenter">'. $admin_names[$user_data['Admin']] .'</td>';
			
			echo '<td>';
			if( !empty( $user_data['groupIDs'] ) )
			{
				foreach( $user_data['groupIDs'] as $groupID )
				{
					$group = $SF_groups[$groupID];
					echo $group['groupName'];
					echo $group['private'] === 't' ? '&nbsp;<span class="fa fa-eye-slash fa-fw"></span>; ' : '; ';
				}
			}
			echo '</td>';
			
			echo '<td><code>&lt;@'. $user_data['DiscordID'] .'&gt;</code></td>';
			/*
			echo '<td>';
				echo ( $user_data['nickname'] !== NULL ) ? $user_data['nickname'] : $user_data['username'];
			echo '</td>';
			*/
			echo '<td class="aligncenter">'. discord_roles( $user_data['role_ids'], $roles_data ) .'</td>';
			
			$ignored_roles = array_intersect( Discord_model::IGNORED_ROLES, $user_data['role_ids'] );
			echo '<td class="aligncenter">'. discord_roles( $ignored_roles, $roles_data ) .'</td>';
			
			echo '<td class="aligncenter">'. discord_roles( $user_data['expected_roles'], $roles_data ) .'</td>';
			
			echo '<td class="aligncenter">'. discord_roles( $missing_roles, $roles_data ) .'</td>';
			
			echo '<td class="aligncenter">'. discord_roles( $revoke_roles, $roles_data ) .'</td>';
			echo '</tr>';
		} ?>
		</tbody>
		</table>
		
		<script src="/js/discord_role_issues.js"></script>