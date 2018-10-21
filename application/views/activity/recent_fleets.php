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
					<div class="col-xs-12">
						<h2>Fleets' History</h2>
					</div>
				</div>
				
				<div>
					<div class="col-xs-12">
						<div class="col-xs-10 col-xs-offset-1">
							<p>
								Below is a summary of the recent history of our fleets & schedule. This is intended to help inform you of the popularity of specific doctrines, form-up locations and timezone coverage.<br>
								It also offers some insight into the frequency of spontaneous fleets formed outside of the listed schedule. Typically these unscheduled fleets are in response to intel of High Value Targets (e.g. tackled Supers), or are casual gate-camps during quieter times.
							</p>
						</div>
					</div>
					
					<div class="col-xs-12" style="font-size: 16px; line-height: 32px;">
						<div class="col-lg-5 col-lg-offset-1 col-md-6 col-md-offset-0 col-sm-8 col-sm-offset-2">
							<a href="/activity/future_fleets"><i class="fa fa-calendar fa-2x" aria-hidden="true"></i>&nbsp;View all future scheduled fleets that are outstanding</a><br>
							<a href="/activity/cancelled_future_fleets" class="text-muted"><i class="fa fa-calendar-o fa-2x" aria-hidden="true"></i>&nbsp;View all future scheduled fleets that have been cancelled</a>
						</div>
						<div class="col-lg-5 col-lg-offset-0 col-md-6 col-md-offset-0 col-sm-8 col-sm-offset-2">
							<a href="/activity/recent_scheduled_fleets"><i class="fa fa-calendar-check-o fa-2x" aria-hidden="true"></i>&nbsp;View recent historic scheduled fleets that occured</a><br>
							<a href="/activity/cancelled_scheduled_fleets" class="text-muted"><i class="fa fa-calendar-times-o fa-2x" aria-hidden="true"></i>&nbsp;View recent historic scheduled fleets that were cancelled</a>
						</div>
					</div>
					
					&nbsp;<br>
					
					<div class="col-lg-10 col-lg-offset-1 col-md-12 col-md-offset-0" style="margin-top: 20px; margin-bottom: 40px;">
						<?php echo $recent_favoured_scheduled_timezones_html; ?>
					</div>
					
					<div class="col-xs-10 col-xs-offset-1">
						<h3><i class="fa fa-heart fa-fw" aria-hidden="true"></i>&nbsp;Recent Most Favoured</h3>
					</div>
					<div class="col-lg-10 col-lg-offset-1 col-md-12 col-md-offset-0">
						<div class="row">
							
							<div class="col-lg-5 col-lg-offset-0 col-md-4 col-md-offset-0 col-sm-8 col-sm-offset-2">
								<h4 style="margin-bottom: 10px;"><i class="fa fa-map-o fa-fw" aria-hidden="true"></i>&nbsp;Form-up Locations</h4>
								<?php
								if( !empty($recent_favoured_scheduled_locations_html) )
								{
									echo $recent_favoured_scheduled_locations_html;
								}
								else
								{
									echo '<span class="text-muted">No recent favourite found</span>';
								} ?>
							</div>
							
							<div class="col-lg-7 col-md-8 col-sm-12">
								<h4 style="margin-bottom: 10px;"><i class="fa fa-users fa-fw" aria-hidden="true"></i>&nbsp;Doctrines</h4>
								<?php
								if( !empty($recent_favoured_scheduled_doctrines_html) )
								{
									echo $recent_favoured_scheduled_doctrines_html;
								}
								else
								{
									echo '<span class="text-muted">No recent favourite found</span>';
								} ?>
							</div>
							
						</div>
					</div>
					
					<div class="col-lg-10 col-lg-offset-1 col-md-12">
						<h3><i class="fa fa-rocket fa-fw" aria-hidden="true"></i>&nbsp;Fleets from the past few days</h3>
						<?php echo $past_interlinked_html; ?>
					</div>
					
				</div>
				
				&nbsp;<br>
				
			</div>
			
		</article>
		
	</div>
	
</div><!--/#content-->