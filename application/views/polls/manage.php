
			<h2>Manage Polls</h2>
			
			<?php if( isset($_SESSION['flash_message']) )
			{
				echo '<h3>Notifications</h3><p>' . $_SESSION['flash_message'] . '</p><br>';
			} ?>
			
			<div class="row">
				<div class="col-sm-6 col-sm-offset-3">
					<a href="/polls/create" class="btn btn-primary btn-block">Create New Poll</a>
				</div>
			</div>
			
			<h3>Your Draft Polls</h3>
			<?php
			if( $draft_polls_html != '' )
			{
				echo $draft_polls_html;
			}
			else
			{ ?>
			<div>There are no recently closed polls.</div><?php
			} ?>
			
			<h3>Open Polls</h3>
			<?php
			if( $open_polls_html != '' )
			{
				echo $open_polls_html;
			}
			else
			{ ?>
			<div>There are no recently closed polls.</div><?php
			} ?>
			
			<h3>Closed Polls</h3>
			
			<?php
			if( $closed_polls_html != '' )
			{
				echo $closed_polls_html;
			}
			else
			{ ?>
			<div>There are no recently closed polls.</div><?php
			} ?>
