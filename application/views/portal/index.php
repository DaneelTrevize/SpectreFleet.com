
			<?php if( isset($_SESSION['flash_message']) )
			{
				echo '<h2>Notifications</h2><p>' . $_SESSION['flash_message'] . '</p>';
			} ?>
			
			<?php 
			$Username = $_SESSION['user_session']['Username'];
			$UserID = $_SESSION['user_session']['UserID'];
			$CharacterID = $_SESSION['user_session']['CharacterID'];
			$CharacterName = $_SESSION['user_session']['CharacterName'];
			?>
			<h3>Welcome, <?php echo $Username; ?>.</h3>
			
			<div class="row">
				<div class="col-sm-4 col-xs-6 aligncenter">
					<?php
					echo 'Your Eve character for this account is:<br><br>';
					echo '<strong>'. $CharacterName .'</strong> (&nbsp;<a href="https://zkillboard.com/character/'.$CharacterID.'/">zKillboard&nbsp;<i class="fa fa-external-link" aria-hidden="true"></i></a>&nbsp;)';
					?>
				</div>
				<div class="col-md-3 col-sm-4 col-xs-6 aligncenter">
					<?php
					echo '<img src="https://imageserver.eveonline.com/Character/'. $CharacterID. '_128.jpg" class="img-rounded" alt="'.$CharacterName.'">'; ?>
				</div>
				<div class="col-md-5 col-sm-4 col-xs-12 aligncenter">
					<a href="https://community.eveonline.com/support/third-party-applications/"><i class="fa fa-lock" aria-hidden="true">&nbsp;</i>Manage your EVE&nbsp;Online SSO/ESI<br>websites and applications here&nbsp;<i class="fa fa-external-link" aria-hidden="true"></i></a>
				</div>
			</div>
			
			<div class="row">
				<div class="col-sm-12">
					<hr>
				</div>
			</div>
			
			<?php
			echo '<p>';
			if( $Rank == Command_model::RANK_MEMBER && $Editor == Editor_model::ROLE_MEMBER && $Admin == User_model::ADMIN_MEMBER )
			{
				echo '<br><span class="text-muted">You currently do not have any special FC, media or staff roles in the Spectre Fleet community.<br>You\'re welcome to apply, just ask in-game or on our Discord server.</span>';
			}
			if( $Rank != Command_model::RANK_MEMBER )
			{
				echo '<br>Your current FC rank is <strong>' . $RankName . '</strong>.';
				echo ' <span class="visible-sm-inline visible-xs-inline"><br></span><a href="/activity/FC/'.$UserID.'">View your public FC profile here&nbsp;<span class="fa fa-id-card-o fa-fw"></span></a>';
			}
			if( $Editor != Editor_model::ROLE_MEMBER )
			{
				echo '<br>Your current Editor role is <strong>' . $EditorRoleName . '</strong>.';
			}
			if( $Admin != User_model::ADMIN_MEMBER )
			{
				echo '<br>Your current Admin role is <strong>' . $AdminRoleName . '</strong>.';
			}
			if( !empty( $groups ) )
			{
				echo '<br><br>You are a member of the following (public/private&nbsp;<span class="fa fa-eye-slash fa-fw"></span>) Special Interest Groups:';
				foreach( $groups as $group )
				{
					echo ' <strong>' . $group['groupName'];
					echo $group['private'] === 't' ? '</strong>&nbsp;<span class="fa fa-eye-slash fa-fw"></span>;' : '</strong>;';
				}
			}
			else
			{
				echo '<br><br><span class="text-muted">You are not currently a member of any Special Interest Groups.</span>';
			}
			echo '</p>';
			?>
			
			<div class="row">
				<div class="col-sm-12">
					<hr>
				</div>
			</div>
			
			<?php
			if( $should_have_discord_identity )
			{
				echo $discord_html;
				echo "<br>\n";
			} ?>
			<p><img src="/media/image/misc/discord_logo_wordmark_white.png" height="82px" width="240px"> <span class="text-muted">Identity integration is now being used by FCs, Staff and our new Special Interest Groups members.</span></p>
