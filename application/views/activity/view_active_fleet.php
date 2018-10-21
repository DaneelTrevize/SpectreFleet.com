<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li><a href="/activity/recent_fleets">Fleets</a></li>
			<li class="active">Formed Fleet</li>
		</ol>
	</div>
	
	<div class="row">
		
		<article class="entry style-single">
			
			<div class="entry-content col-sm-10 col-sm-offset-1 aligncenter">
				
				<h1><?php
					echo $firstDetected_datetime->format( 'l F jS Y \a\t H:i' );
					?>
				</h1>
				
				<p>
					<?php
					if( isset($fleetTime) )
					{
						$fleetTime_datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', $fleetTime );
						
						echo 'Related: <a href="/activity/fleet/'. $fleetTime_datetime->format( 'Y-m-d/H:i' ) .'">';
						
						if( $fleetTime_datetime >= $latestDetected_datetime )
						{
							// Future fleet
							echo '<i class="fa fa-calendar fa-2x" aria-hidden="true"></i>&nbsp;Future scheduled fleet';
						}
						else
						{
							// Past fleet
							echo '<i class="fa fa-calendar-check-o fa-2x" aria-hidden="true"></i>&nbsp;Past scheduled fleet';
						}
						echo '</a>';
					}
					else
					{
						echo 'No related scheduled fleet detected.';
					}
					echo '<br>';
					
					if( $firstDetected == $latestDetected )
					{
						echo 'Just recently forming!';
					}
					elseif( $firstDetected == $lastDetected )
					{
						echo 'Duration: Roughly 15 minutes. Concluded fleet.';
					}
					else
					{
						$duration = $firstDetected_datetime->diff( $lastDetected_datetime );
						echo 'Duration: ' . $duration->format( '%r%h hours, %i minutes.' );
						
						if( $latestDetected == $lastDetected )
						{
							echo ' Ongoing fleet!';
						}
						else
						{
							echo ' Concluded fleet.';
						}
					} ?>
				</p>
				
			</div>
			
			<div class="col-md-10 col-md-offset-1">
				
				<div class="row">
					<div class="col-sm-4 col-sm-offset-1 aligncenter">
						<h3><i class="fa fa-user-circle-o fa-fw aria-hidden="true"></i>&nbsp;Fleet Commander</h3>
						<a href="/activity/FC/<?php echo $FC_ID; ?>">
						<span style="font-size: 22px;"><?php echo $CharacterName; ?></span><br>
						<?php
						echo '<img src="https://imageserver.eveonline.com/Character/';
						echo $CharacterID != NULL ? $CharacterID : '1';
						echo '_256.jpg" class="img-rounded" alt="'.$CharacterName.'">';
						?>
						</a><br>
					</div>
					<div class="col-sm-4 col-sm-offset-2 aligncenter">
						<h3><i class="fa fa-map-o fa-fw aria-hidden="true"></i>&nbsp;Form-up Location</h3>
						<br>
						<span style="font-size: 54px;">
						<?php
						if( !$locationExact ) echo 'Near ';
						if( isset( $solarSystemName ) )	// When would it not be??
						{
							echo link_solar_system( $solarSystemName );
						} ?>
						</span>
						
						<br>
						<br>
						<br>
						
						<h3>Activity Type</h3>
						<br>
						<div style="font-size: 18px;">
							<span class="fa-stack">
							<i class="fa fa-circle-thin fa-stack-2x"></i>
							<i class="fa fa-circle fa-stack-1x type_<?php echo strtolower( $type ); ?>"></i>
							</span>
							<span class="type_<?php echo strtolower( $type ); ?>">
								<?php echo $type; ?>
							</span>
						</div>
					</div>
				</div>
				
			</div>
			
			<?php
			if( $additionalDetails != '' )
			{ ?>
				<div class="col-sm-8 col-sm-offset-2 aligncenter" style="margin-top: 40px;">
					<h3>Additional Details</h3>
					<?php echo $additionalDetails; ?>
					
					&nbsp;<br>
					&nbsp;<br>
				</div>
			<?php
			} ?>
			
			<?php
			if( $kills_html != '' )
			{ ?>
				<div class="col-lg-10 col-lg-offset-1 col-md-12 col-sm-12" style="margin-top: 40px;">
					<h3><i class="fa fa-crosshairs fa-fw aria-hidden="true"></i>&nbsp;Highlighted Kills</h3>
					<?php echo $kills_html; ?>
					<p class="text-muted">Disclaimer: These kills were highlighted as being Spectre Fleet related and occured during this fleet's active time.<br>
					Potentially another Spectre Fleet was also active at these moments and was responsible for one or more of the kills.</p>
					
					&nbsp;<br>
					&nbsp;<br>
				</div>
			<?php
			} ?>
			
		</article>
		
	</div>
	
</div><!--/#content-->