
			<h2>Manage Users' Special Interest Groups</h2>
			<a href="/SIGs/change">Change User's groups</a>.
			
			<h3>List Special Interest Groups</h3>
			Public / Private&nbsp;<span class="fa fa-eye-slash fa-fw"></span>
			<div class="col-sm-12 entry-content">
				<?php foreach( $groups as $group )
				{
					echo '<a href="/SIGs/group/'. $group['groupID'] .'">'. $group['groupName'] . '</a>';
					if( $group['private'] === 't' )
					{
						echo '&nbsp;<span class="fa fa-eye-slash fa-fw"></span>';
					}
					echo '<br>';
				} ?>
			</div>