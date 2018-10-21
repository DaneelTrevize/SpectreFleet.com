
			<h2>Discord Roles</h2>
			<p>All Roles:</p>
			<div class="discord_roles">
			<?php
			foreach( $roles as $role )
			{
				if( $role['name'] === '@everyone' || $role['name'] === 'SpectreFleet.com' )
				{
					continue;	// Skip and do other roles
				}
				
				echo discord_role( $role['color'], $role['name'] );
			}
			?>
			</div>
		
			<hr>
			<p>Roles which are Ignored when synchronising permissions, rank/role and group membership with this site:</p>
			<?php
			echo discord_roles( Discord_model::IGNORED_ROLES, $roles, FALSE );
			?>
