		<nav class="col-sm-3 sidenav" id="portal_menu">
		
			<ul class="nav">
			
				<?php
				echo '<li class="sidenav_header">General Features</li>';
				echo '<li><a href="/portal">Your Portal</a></li>';
				echo '<li><a href="/invites/request">Request Fleet Invites</a></li>';
				echo '<li class="sidenav_spacer">&nbsp;</li>';
				echo '<li><a href="/portal/command_team">Command Team</a></li>';
				if( $CAN_SUBMIT_FC_APPLICATION || $CAN_HELP_FC_APPLICANTS )
				{
					echo '<li><a href="/manage/my_applications">';
					echo ( $CAN_SUBMIT_FC_APPLICATION ) ? 'Apply to FC' : 'Help others Apply to FC' ;
					echo '</a></li>';
				}
				echo '<li class="sidenav_spacer">&nbsp;</li>';
				echo '<li><a href="/authentication/change_password">Change Password</a></li>';
				echo '<li><a href="/logout">Logout</a></li>';
				
				if( $CAN_VIEW_DEBUGGING )
				{
					
					echo '</ul>';
					echo '<ul class="nav">';
					echo '<li class="sidenav_header">Tech</li>';
					echo '<li><a href="/portal/debugging">Debugging</a></li>';
					echo '<li><a href="/portal/testing">Testing</a></li>';
				}
				
				if( $CAN_MANAGE_ACTIVE_FLEETS || $CAN_UPDATE_CHANNEL_DATA || $CAN_VIEW_FEEDBACK || $CAN_MANAGE_OWN_FITS || $CAN_ACCESS_COMMAND_MEETINGS )
				{
					echo '</ul>';
					echo '<ul class="nav">';
					echo '<li class="sidenav_header">FC Tools</li>';
				}
				if( $CAN_MANAGE_ACTIVE_FLEETS )
				{
					echo '<li><a href="/invites/recent">Recent Invites Summary</a></li>';
					echo '<li><a href="/fleets2/">Fleet &amp; Invite Manager</a></li>';
				}
				if( $CAN_UPDATE_CHANNEL_DATA )
				{
					echo '<li><a href="/channel/refresh_spectre">Update site with MOTD</a></li>';
				}
				if( $CAN_VIEW_FEEDBACK )
				{
					echo '<li><a href="/feedback/search/?FleetFC='.rawurlencode($_SESSION['user_session']['CharacterName']).'">Your Feedback</a></li>';
				}
				if( $CAN_MANAGE_OWN_FITS )
				{
					echo '<li><a href="/doctrine/manage_doctrines">Your Fleet Doctrines</a></li>';
					echo '<li><a href="/doctrine/manage_fits">Your Ship Fits</a></li>';
				}
				if( $CAN_ACCESS_COMMAND_MEETINGS )
				{
					echo '<li><a href="/portal/meetings">Command Meeting Records</a></li>';
				}
				
				
				echo '</ul>';
				echo '<ul class="nav">';
				// Should be conditional on not being member? Or advertised to all users?
				echo '<li class="sidenav_header">Guides</li>';
				echo '<li><a href="/pages/view/policies">Policies</a></li>';
				echo '<li><a href="/pages/view/invite_manager">Fleet &amp; Invite Manager</a></li>';
				echo '<li><a href="/pages/view/motd_format">MOTD Format</a></li>';
				echo '<li><a href="/pages/view/guide">FCing for Spectre Fleet</a></li>';

				if( $CAN_CHANGE_OTHERS_RANKS || $CAN_PROCESS_FC_APPLICATIONS || $CAN_INVITE_NEW_USERS || $CAN_CHANGE_OTHERS_GROUPS || $CAN_VIEW_DISCORD_DATA )
				{
					
					echo '</ul>';
					echo '<ul class="nav">';
					echo '<li class="sidenav_header">Staff</li>';
					
					if( $CAN_CHANGE_OTHERS_RANKS )
					{
						echo '<li><a href="/channel/permissions">Check Channel Operators</a></li>';
					}
					
					if( $CAN_PROCESS_FC_APPLICATIONS )
					{
						echo '<li><a href="/manage/review_applications">Review FC Applications</a></li>';
					}
					if( $CAN_INVITE_NEW_USERS )
					{
						echo '<li><a href="/authentication/invite_new_user">Invite New User</a></li>';
					}
					if( $CAN_CHANGE_OTHERS_RANKS )
					{
						echo '<li><a href="/manage/change_rank">Change FC Rank</a></li>';
					}
					if( $CAN_CHANGE_OTHERS_GROUPS )
					{
						echo '<li><a href="/SIGs/manage">Manage Users\' Groups</a></li>';
					}
					
					if( $CAN_VIEW_DISCORD_DATA )
					{
						echo '<li><a href="/discordauth/manage">Manage Discord Integration</a></li>';
					}
				}
				if( $CAN_SUBMIT_ARTICLES || $CAN_EDIT_OTHERS_SUBMISSIONS || $CAN_PUBLISH_ARTICLES || $CAN_UPLOAD || $CAN_CHANGE_OTHERS_EDITOR_ROLES )
				{
					
					echo '</ul>';
					echo '<ul class="nav">';
					echo '<li class="sidenav_header">Articles &amp; Polls</li>';
					
					if( $CAN_SUBMIT_ARTICLES )
					{
						echo '<li><a href="/editor/create_article">Create Article</a></li>';
					}
					if( $CAN_SUBMIT_ARTICLES || $CAN_EDIT_OTHERS_SUBMISSIONS || $CAN_PUBLISH_ARTICLES )
					{
						echo '<li><a href="/editor/review_submissions">Review Submissions</a></li>';
					}
					if( $CAN_UPLOAD )
					{
						echo '<li><a href="/upload/index">Upload Media</a></li>';
					}
					if( $CAN_CHANGE_OTHERS_EDITOR_ROLES )
					{
						echo '<li><a href="/editor/change_role">Change Editorial Role</a></li>';
					}
				}
				if( $CAN_CREATE_POLLS || $CAN_MANAGE_OTHERS_POLLS )
				{
					echo '<li class="sidenav_spacer">&nbsp;</li>';
					echo '<li><a href="/polls/manage">Manage Polls</a></li>';
				}
				?>
				
			</ul>
			
		</nav>
