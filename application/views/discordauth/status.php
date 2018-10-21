			<?php
			if( $DiscordID === NULL )
			{
				echo '<div class="col-sm-12 aligncenter"><i class="fa fa-exclamation-triangle fa-fw fa-2x" aria-hidden="true"></i>&nbsp;We are unaware of your Discord identity, please <a href="/discordauth/index">click here to enable account association&nbsp;<i class="fa fa-comments fa-fw" aria-hidden="true"></i></a>.</div>';
			}
			elseif( $member_data === FALSE || !is_array( $member_data ) )
			{
				echo '<div class="col-sm-12 aligncenter"><i class="fa fa-exclamation-triangle fa-fw fa-2x" aria-hidden="true"></i>&nbsp;We are unable to confirm your Discord identity at this time, please <a href="/discordauth/index">click here to enable account association&nbsp;<i class="fa fa-comments fa-fw" aria-hidden="true"></i></a>.</div>';
			}
			else
			{ ?>
				<div class="row">
					<div class="col-sm-4 col-xs-6 aligncenter">
						<h4>Discord Identity</h4>
						<?php
						echo 'ID: <code>&lt;@'.$DiscordID.'&gt;</code><br><br>';
						echo 'Name: <strong>'. $member_data['username'] .'</strong><br><br>';
						
						if( array_key_exists( 'nick', $member_data ) && $member_data['nick'] != '' )
						{
							echo 'SF Nickname: <strong>'. $member_data['nick'] .'</strong>';
						}
						else
						{
							echo '<i>No SpectreFleet-specific nickname detected.</i>';
						} ?>
					</div>
					<div class="col-md-3 col-sm-4 col-xs-6 aligncenter">
						<h4>Avatar</h4>
						<?php echo discord_avatar( $DiscordID, $member_data ); ?>
					</div>
					<div class="col-md-5 col-sm-4 col-xs-12 aligncenter">
						<h4>Roles</h4>
						<?php echo discord_roles( $member_data['roles'], $roles_data, TRUE ); ?>
					</div>
				</div>
			<?php
			} ?>