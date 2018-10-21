<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li class="active">Fleets</li>
		</ol>
	</div>
	
	<div class="row">
		
		<article class="entry style-single">
			
			<div class="entry-content">
				
				<div>
					<div class="col-xs-12 aligncenter">
						<h2><i class="fa fa-clock-o fa-fw" aria-hidden="true"></i>&nbsp;Fleet Activity as of <?php echo $currentEVEtime; ?> (EVE Time)</h2>
					</div>
				</div>
				
				<div>
					<div class="col-md-9">
						<div class="col-xs-12">
							<?php $this->load->view( 'common/temp_notice' ); ?>
						</div>
						
						<?php $this->load->view( 'common/fleet_type_key' ); ?>
						
						<div>
							<span class="pull-right"><a href="/activity/future_fleets">View all future scheduled fleets</a></span>
							<h3 style="margin-bottom: 0px;">Fleets scheduled for the coming week</h3>
							<?php echo $future_html; ?>
						</div>
						
						<!--<div class="col-xs-12">
							<div class="col-lg-8 col-lg-offset-0 col-sm-12 col-xs-12">
								<p>You can optionally Request Invites online now for any of our future scheduled&nbsp;fleets, or&nbsp;X-Up in-game at the start time.</p>
							</div>
							<div class="col-lg-4 col-lg-offset-0 col-sm-12 aligncenter" style="padding-top: 8px;">
								<a class="btn btn-request" href="/invites/request"><i class="fa fa-user-times" aria-hidden="true"></i>&nbsp;Request Invites online</a>
							</div>
						</div>-->
						
						&nbsp;<br>
						
						<?php
						if( FALSE/*$channel_api_problem*/ )
						{ ?>
						<div>
							<h3><i class="fa fa-exclamation-triangle fa-fw fa-2x" aria-hidden="true"></i>&nbsp;CCP API Problem</h3>
							<p>There appears to be a problem with our connection to CCP, the data on this page may be more than 20 minutes old, which will impact the accuracy of our fleet schedule & activity tracking.</p>
						</div>
						
						&nbsp;<br>
						<?php
						} ?>
						
						<!--<div>
							<h3>Currently Active Fleets</h3>
							<?php echo $active_html; ?>
						</div>
						
						&nbsp;<br>-->
						
						<?php
						if( $bulletins_html != '' )
						{ ?>
						<div style="font-size: 16px;">
							<?php echo $bulletins_html; ?>
						</div>
						
						&nbsp;<br>
						<?php
						} ?>
						
						<div>
							<span class="pull-right"><a href="/activity/recent_fleets">View all recently past fleets</a></span>
							<h3>Fleets from the past few days</h3>
							<?php echo $past_interlinked_html; ?>
						</div>
						
						&nbsp;<br>
						
					</div>
					
					<div class="col-md-3 col-md-offset-0 col-sm-10 col-sm-offset-1">
						
						<div class="aligncenter">
							<h4><a href="/activity/recent_kills">View all recent highlighted kills&nbsp;<i class="fa fa-crosshairs fa-fw" aria-hidden="true"></i></a></h4>
						</div>
						
						<div>
							<h3>Top ISK Kills of the Day</h3>
							<?php echo $day_kills_html; ?>
						</div>
						
						<div>
							<h3>Other Top Kills of the Week</h3>
							<?php echo $week_kills_html; ?>
						</div>
						
						<div>
							<h3>Other Top Kills of the Month</h3>
							<?php echo $month_kills_html; ?>
						</div>
						
						&nbsp;<br>
					</div>
				</div>
				
				&nbsp;<br>
				
			</div>
			
		</article>
		
	</div>
	
</div><!--/#content-->