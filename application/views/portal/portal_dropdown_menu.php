<?php
if( isset($_SESSION['user_session']) )
{ ?>
	<li class="dropdown" id="portal_dropdown_menu">
		<a tabindex="0" data-toggle="dropdown" data-submenu="" aria-expanded="false">
		<span class="fa fa-caret-down fa-fw"></span>&nbsp;Portal&nbsp;
		<img src="https://imageserver.eveonline.com/Character/<?php
		$CharacterName = $_SESSION['user_session']['CharacterName'];
		echo $_SESSION['user_session']['CharacterID'] .'_64.jpg" title="';
		echo $CharacterName; ?>" class="img-rounded">
		</a>
		<ul class="dropdown-menu">
			<li class="dropdown-header"><?php echo $CharacterName; ?></li>
			<li><a tabindex="0" href="/portal">Your Portal</a></li>
			<!--<li><a tabindex="0" href="/invites/request">Request Fleet Invites&nbsp;<span class="fa fa-user-times fa-fw"></span></a></li>-->
			<li class="dropdown-submenu">
				<a tabindex="0">Guides&nbsp;<span class="fa fa-question-circle fa-fw"></span></a>
				<ul class="dropdown-menu">
					<li><a tabindex="0" href="/pages/view/policies">Policies</a></li>
					<li><a tabindex="0" href="/pages/view/invite_manager">Fleet &amp; Invite Manager</a></li>
					<li><a tabindex="0" href="/pages/view/motd_format">MOTD Format</a></li>
					<li><a tabindex="0" href="/pages/view/guide">FCing for Spectre Fleet</a></li>
				</ul>
			</li>
			<?php
			if( $CAN_MANAGE_ACTIVE_FLEETS || $CAN_VIEW_FEEDBACK )
			{
				echo '<li class="divider"></li>';
			}
			if( $CAN_MANAGE_ACTIVE_FLEETS )
			{
				echo '<li><a tabindex="0" href="/fleets2/">Fleet &amp; Invite Manager</a></li>';
			}
			if( $CAN_VIEW_FEEDBACK )
			{
				echo '<li><a tabindex="0" href="/feedback/search/?FleetFC='.rawurlencode($CharacterName).'">Your Feedback</a></li>';
			}
			
			if( $CAN_VIEW_DEBUGGING )
			{
				echo '<li class="divider"></li>';
				echo '<li><a tabindex="0" href="/portal/debugging">Debugging</a></li>';
			}
			
			if( $CAN_PROCESS_FC_APPLICATIONS )
			{
				echo '<li class="divider"></li>';
				echo '<li><a tabindex="0" href="/manage/review_applications">Review FC Applications</a></li>';
			}
			
			if( $CAN_SUBMIT_ARTICLES || $CAN_EDIT_OTHERS_SUBMISSIONS || $CAN_PUBLISH_ARTICLES || $CAN_CREATE_POLLS || $CAN_MANAGE_OTHERS_POLLS )
			{
				echo '<li class="divider"></li>';
				echo '<li class="dropdown-submenu">';
				echo '<a tabindex="0">Community&nbsp;<span class="fa fa-bar-chart fa-fw"></span></a>';
				echo '<ul class="dropdown-menu">';
					if( $CAN_SUBMIT_ARTICLES || $CAN_EDIT_OTHERS_SUBMISSIONS || $CAN_PUBLISH_ARTICLES )
					{
						echo '<li><a tabindex="0" href="/editor/review_submissions">Review Submissions</a></li>';
					}
					if( $CAN_CREATE_POLLS || $CAN_MANAGE_OTHERS_POLLS )
					{
						echo '<li><a tabindex="0" href="/polls/manage">Manage Polls</a></li>';
					}
				echo '</ul>';
				echo '</li>';
			} ?>
			<li class="divider"></li>
			<li><a tabindex="0" href="/logout"><span class="fa fa-sign-out fa-fw"></span>&nbsp;Logout</a></li>
		</ul>
	</li><?php
}
else
{ ?>
	<li><a href="/login"><span class="fa fa-sign-in fa-fw"></span>&nbsp;Login</a></li><?php
} ?>