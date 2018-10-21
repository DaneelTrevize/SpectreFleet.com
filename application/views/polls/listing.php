<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li class="active"><a href="/polls">Polls</a></li>
		</ol>
	</div>
	
	<div class="row">

		<header class="page-header col-md-10 col-md-offset-1">

			<h2 class="page-title full-page-title">
				Community Polls
			</h2>

		</header>
		
		<article class="entry style-single style-single-full type-post col-md-10 col-md-offset-1">
			<?php
			if( $open_polls_html != '' )
			{
				echo $open_polls_html;
			}
			else
			{ ?>
			<div>There are no polls open at this time.</div><?php
			} ?>
		</article>
		
		<article class="entry style-single style-single-full type-post col-md-10 col-md-offset-1">
			
			<h2>Recently Closed Polls</h2>
			
			<?php
			if( $closed_polls_html != '' )
			{
				echo $closed_polls_html;
			}
			else
			{ ?>
			<div>There are no recently closed polls.</div><?php
			} ?>
			
		</article>
		
	</div>
	
</div><!--/#content-->