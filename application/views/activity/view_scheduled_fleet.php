<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li><a href="/activity/recent_fleets">Fleets</a></li>
			<li class="active">Scheduled Fleet</li>
		</ol>
	</div>
	
	<div class="row">
		
		<article class="entry style-single">
			
			<div class="entry-content col-sm-10 col-sm-offset-1 aligncenter">
			
				<h1><?php
					$fleetTime_datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', $fleetTime );
					echo $fleetTime_datetime->format( 'l F jS Y \a\t H:i' ); ?>
				</h1>
				
				<p>
					<?php
					$API_ENDED_datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', Activity_model::API_15MIN_LOGGING_END_DATE );
					$latestDetected_datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', $latestDetected );
					
					$lastDetected_datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', $lastDetected );
					if( $lastDetected_datetime > $API_ENDED_datetime )
					{
						// Was after regular API era
						
						// Assume no cancellations
						
						if( $fleetTime_datetime >= $currentEVEtime )
						{
							// Future fleet
							echo '<i class="fa fa-calendar fa-2x" aria-hidden="true"></i>&nbsp;Scheduled future fleet';
						}
						else
						{
							// Past fleet
							echo '<i class="fa fa-calendar-check-o fa-2x" aria-hidden="true"></i>&nbsp;Past fleet';
							echo '<br>';
							echo '<span class="text-muted">Duration: Unknown due to removed ESI Channel API.</span>';
						}
					}
					else
					{
						// Was before/during regular API era
						
						if( $fleetTime_datetime >= $latestDetected_datetime )
						{
							// Future fleet
							if( $lastDetected_datetime >= $latestDetected_datetime )
							{
								echo '<i class="fa fa-calendar fa-2x" aria-hidden="true"></i>&nbsp;Scheduled future fleet';
								
								// Could try list associated forming fleet if within 1.5hour window?
								
							}
							else
							{
								echo '<i class="fa fa-calendar-o fa-2x" aria-hidden="true"></i>&nbsp;Cancelled future fleet';
							}
						}
						else
						{
							// Past fleet
							if( $lastDetected_datetime >= $fleetTime_datetime->sub( new DateInterval( Activity_model::QUERY_PERIOD_WITH_WIGGLE_ROOM ) ) )
							{
								echo '<i class="fa fa-calendar-check-o fa-2x" aria-hidden="true"></i>&nbsp;Past fleet';
								
								echo '<br>';
								if( isset($af_firstDetected) )
								{
									/*
									echo 'Related formed fleet: ';
									echo '<a href="/activity/active/'. $af_firstDetected_datetime->format( 'Y-m-d/H:i:s' ) .'/'. $XUPNumber .'">';
									echo $af_firstDetected_datetime->format( 'l F jS Y \a\t H:i' );
									echo '</a>';
									*/
									if( $af_firstDetected == $af_lastDetected )
									{
										echo 'Duration: Roughly 15 minutes. Concluded fleet.';
									}
									else
									{
										$af_firstDetected_datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', $af_firstDetected );
										$af_lastDetected_datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', $af_lastDetected );
										$duration = $af_firstDetected_datetime->diff( $af_lastDetected_datetime );
										echo 'Duration: ' . $duration->format( '%r%h hours, %i minutes.' );
									}
								}
								else
								{
									echo 'No related formed fleet detected.<br><span class="text-muted">Possibly the FC gave notice to cancel/reschedule within ~15minutes of the form-up time, or there was a problem with CCP\'s API.</span>';
								}
							}
							else
							{
								echo '<i class="fa fa-calendar-times-o fa-2x" aria-hidden="true"></i>&nbsp;Cancelled past fleet';
							}
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
			
			<?php if( isset($fleet) )
			{ ?>
				<div class="col-sm-10 col-sm-offset-1">
					<h3 class="aligncenter">Doctrine</h3>
					<a style="text-decoration:none;" href="/doctrine/fleet/<?php echo $fleet['fleetID']; ?>">
						<div class="pull-right">FleetID: <?php echo $fleet['fleetID']; ?></div>
						<div class="thtext"><?php echo $fleet['fleetName']; ?></div>
						<div class="pull-right hidden-xs">
							<?php
							foreach($fleet['shipIDs'] as $shipID)
							{
							echo '<img class="small-doctrine-img img-rounded" src="https://imageserver.eveonline.com/Type/'.$shipID.'_32.png">';
							} ?>
						</div>
						<div><?php echo 'Type: '.$fleet['fleetType']; ?></div>
					</a>
					<div class="tftext">
						<?php echo 'Created by '.$fleet['Username'].' on '.date("F jS, Y",strtotime($fleet['date'])); ?>
						<span class="pull-right"><?php echo 'Last edited '.date("F jS, Y",strtotime($fleet['lastEdited'])); ?></span>
					</div>
					
					&nbsp;<br>
					&nbsp;<br>
					
				</div>
			<?php
			} ?>
			
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