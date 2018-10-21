<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li><a href="/activity/FCs">Commanders</a></li>
			<li class="active">Profile</li>
		</ol>
	</div>
	
	<div class="row">
		
		<article class="entry style-single">
			
			<div class="entry-content col-xs-12 aligncenter">
			
				<h1><?php
				if( $ex_FC )
				{
					echo $CharacterName.' (Retired)';
				}
				else
				{
					echo $RankName.': '.$CharacterName;
				} ?></h1>
				
			</div>
			
			<div class="col-md-8">
				
				<div class="row">
				
					<div class="col-sm-6 aligncenter">
						<?php
						echo '<img src="https://imageserver.eveonline.com/Character/';
						echo $CharacterID != NULL ? $CharacterID : '1';
						echo '_256.jpg" class="img-rounded" alt="'.$CharacterName.'">'; ?>
						<br>
					</div>
					
					<div class="col-sm-6">
						<div class="row">
							<div class="col-xs-10 col-xs-offset-2">
								Registration date: <?php
								if( $DateRegistered == '1970-01-01 00:00:00' )
								{
									echo 'Unknown';
								}
								else
								{
									$registered_datetime = DateTime::createFromFormat( 'Y-m-d H:i:s', $DateRegistered );
									echo $registered_datetime->format( 'l F jS Y' );
								} ?><br>
								<br>
								Latest rank change: <?php
								if( $DateRankChange == NULL )
								{
									echo 'Unknown';
								}
								else
								{
									$rankchanged_datetime = DateTime::createFromFormat( 'Y-m-d H:i:s.ue', $DateRankChange );
									echo $rankchanged_datetime->format( 'l F jS Y' );
								} ?>
							</div>
						</div>
						<br>
						<br>
						<?php
						if( !$ex_FC )
						{ ?>
						<div class="row aligncenter">
							<a class="btn btn-primary" style="font-size: 18px; border-radius: 4px;" href="/feedback/<?php echo $FC_ID; ?>">Submit anonymous Feedback<br> regarding <?php echo $CharacterName; ?></a>
						</div>
						<br><?php
						} ?>
					</div>
				
				</div>
				<div class="row" style="margin-top: 10px; margin-bottom: 30px; font-size: 20px;">
					<div class="col-sm-6 aligncenter">
						<a href="https://zkillboard.com/character/<?php echo $CharacterID; ?>/">zKillboard&nbsp;<i class="fa fa-external-link" aria-hidden="true"></i></a>
					</div>
				</div>
			</div>
			
			<div class="col-md-4 col-md-offset-0 col-sm-10 col-sm-offset-1" style="margin-bottom: 20px;">
				<h3><i class="fa fa-heart fa-fw" aria-hidden="true"></i>&nbsp;Recent Most Favoured</h3>
				<div class="row">
					<div class="col-lg-12 col-md-12 col-sm-6">
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
					
					<div class="col-lg-12 col-md-12 col-sm-6">
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
			
			<div class="col-xs-12">
				<?php echo $recent_favoured_scheduled_timezones_html; ?>
			</div>
			
			<div class="col-xs-12">
				<hr>
			</div>
			
			<div class="col-lg-8">
				
				<div class="col-lg-10 col-lg-offset-1 col-md-8 col-md-offset-2" style="margin-top: 20px; margin-bottom: 10px;">
					<?php $this->load->view( 'common/fleet_type_key' ); ?>
				</div>
			
				<div class="entry-content col-xs-12">
					<h3 style="margin-top: 15px; margin-bottom: 5px;">The upcoming fleets for this FC</h3>
					
					<div>
						
						<?php
						if( $ex_FC )
						{ ?>
							This FC has retired.<?php
						}
						elseif( $future_fleets_html == '' )
						{ ?>
							This FC does not appear to have any future scheduled fleets.<?php
						}
						else
						{ ?>
						<h4>Outstanding future fleets</h4>
						<p>
							These are all the confirmed fleets this FC is planning to run, with their latest details.<br>
							Generally FCs won't list future fleets farther than 1 week out from the present (due to in-game channel constraints), even if they run them regularly.
						</p>
						
						<?php echo $future_fleets_html;

						} ?>
						
					</div>
					
					<br>
					
					<div>
						
						<?php
						if( $cancelled_future_fleets_html == '' )
						{ ?>
							This FC does not appear to have recently cancelled or rescheduled any of their future fleets.<?php
						}
						else
						{ ?>
						<h4>Cancelled or rescheduled future fleets</h4>
						<p>
							If you were hoping to attend a future fleet by this FC that appears to now be missing from the main schedule or at a different time, you should find it listed here as it was detailed prior to having been cancelled or rescheduled.<br>
							You will need to <a href="/invites/request">reconfirm any prior online Invite Request</a> for a fleet who's scheduled date or time have changed.
						</p>
						<?php echo $cancelled_future_fleets_html;

						} ?>
						
					</div>
					
				</div>
				<!--<div class="col-xs-12">
					<hr>
				</div>
				<div class="entry-content col-xs-12">
					<h3 style="margin-top: 15px; margin-bottom: 5px;">The fleets this FC has recently formed</h3>
					
					<div>
						
						<?php
						if( TRUE/*$active_fleets_html == ''*/ )
						{ ?>
							This FC does not appear to have recently formed any fleets.<?php
						}
						else
						{ ?>
						<h4>Recent Active Fleets</h4>
						<p>
							Some FCs are more spontaneous than others when it comes to forming fleets. With our wide variety of in-game as well as IRL FC backgrounds, some are better able to receive and react to situations such as sudden intel regarding High Value Targets, or a space in the schedule with be filled with some casual gate-camping.<br>
							All fleets formed by or for this FC should be listed here, whether they were scheduled or otherwise.
						</p>
						
						<?php echo $active_fleets_html;

						} ?>
					
					</div>
					
				</div>-->
				<div class="col-xs-12">
					<hr>
				</div>
				<div class="entry-content col-xs-12">
					<h3 style="margin-top: 15px; margin-bottom: 5px;">The fleets this FC has previously scheduled</h3>
					
					<div>
						
						<?php
						if( $recent_scheduled_fleets_html == '' )
						{ ?>
							This FC does not appear to have had any recently scheduled fleets.<?php
						}
						else
						{ ?>
						<h4>Recent historic scheduled fleets</h4>
						<p>
							This is a listing of all the fleets this FC has recently scheduled that should have occured as detailed.<br>
							This can help you determine this FC's favoured doctrines, form-up locations and timezone coverage.<br>
							Rarely this list will include fleets cancelled at the last minute, or taken out by another FC in their place.
						</p>
						
						<?php echo $recent_scheduled_fleets_html;

						} ?>
						
					</div>
					
					<br>
					
					<div>
						
						<?php
						if( $cancelled_scheduled_fleets_html == '' )
						{ ?>
							This FC does not appear to have recently cancelled or rescheduled any of their past fleets.<?php
						}
						else
						{ ?>
						<h4>Previously cancelled or rescheduled fleets</h4>
						<p>
							This can give you an idea how likely this FC is to cancel or reschedule their fleet.<br>
							Each change to the date or time will result in a separate entry.<br>
							Changes to the Form-up location, chosen doctrine details, or other additional details are not covered by this listing.
						</p>
						
						<?php echo $cancelled_scheduled_fleets_html;

						} ?>
						
					</div>
					
					&nbsp;<br>
				
				</div>
			
			</div>
			
			<div class="col-lg-4 col-lg-offset-0 col-md-10 col-md-offset-1">
				<h3><i class="fa fa-wrench fa-fw" aria-hidden="true"></i>&nbsp;Doctrine and Fit changes</h3>
				
				<?php
				if( $doctrines_html == '' )
				{ ?>
					This FC does not appear to have recently created or edited any doctrines or fits.<?php
				}
				else
				{
					echo $doctrines_html;
				} ?>
				
				&nbsp;<br>
			</div>
			
			&nbsp;<br>
			
		</article>
		
	</div>
	
</div><!--/#content-->