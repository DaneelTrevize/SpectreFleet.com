<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li><a href="/activity/recent_fleets">Fleets</a></li>
			<li class="active">Request Fleet Invites</li>
		</ol>
	</div>
	
	<div class="row">
		
		<article class="entry style-single col-md-12">
			
			<div class="entry-content">
			
			<h2>Request Invites to Upcoming Fleets</h2>
			
			<?php $this->load->view( 'common/temp_notice' ); ?>
			
			<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<?php $this->load->view( 'common/fleet_type_key' ); ?>
			</div>
			</div>
			
			<div class="row">
			<div class="col-lg-12">
				<div class="pull-right text-muted">
					<i class="fa fa-sort-amount-desc" aria-hidden="true"></i>&nbsp;Ordered by Date ascending
				</div>
				<table class="table table-striped table_valign_m request_invites">
					<thead>
					<tr>
						<th>Type</th>
						<th class="col-md-2 aligncenter"><span class="hidden-sm hidden-xs">Day & </span>Date</th>
						<th class="aligncenter">Time</th>
						<th class="col-md-2 aligncenter"><span class="visible-lg-inline">Fleet </span>Commander</th>
						<th class="aligncenter">Form-up</th>
						<th class="col-md-2 aligncenter"><span class="hidden-xs">Online </span>Fits</th>
						<th><span class="hidden-xs">Additional </span>Details</th>
						<th class="aligncenter">Request<span class="visible-lg-inline"> Invite</span></th>
					</tr>
					</thead>
					<tbody>
					<?php
					$rows_html = '';
					$first_fleet_before_now = NULL;
					foreach( $fleets as $fleet )
					{
						//echo '<pre>'. print_r( $fleet, TRUE ) .'</pre>';
						
						// Assuming descending order by fleetTime
						$datetime = DateTime::createFromFormat( 'Y-m-d H:i:se', $fleet->fleetTime );
						// Instead of this heuristic, should use channel active fleets data?
						if( $datetime < $currentEVEtime )
						{
							if( $first_fleet_before_now !== NULL )
							{
								break;	// End displaying older fleets, already displayed one older than now
							}
							else
							{
								$first_fleet_before_now = $fleet;
							}
						}
						
						$row_html = '<tr>';
							$row_html .= '<td>';
							$row_html .= '<span class="fa-stack">
							<i class="fa fa-circle-thin fa-stack-2x"></i>';
							
							$type_class = '';
							if( $fleet->type === 'Highsec' )
							{
								$type_class = ' type_highsec';
							}
							elseif( $fleet->type === 'Lowsec' )
							{
								$type_class = ' type_lowsec';
							}
							elseif( $fleet->type === 'Nullsec' )
							{
								$type_class = ' type_nullsec';
							}
							elseif( $fleet->type === 'Special' )
							{
								$type_class = ' type_special';
							}
							elseif( $fleet->type === 'Training' )
							{
								$type_class = ' type_training';
							}
							//$row_html .= $fleet->type;	// No need for htmlentities( , ENT_QUOTES ) ?
							$row_html .= '<i class="fa fa-circle fa-stack-1x' .$type_class. '"></i>';
							$row_html .= '</span>';
							$row_html .= '</td>';
							
							$fleet_url = '/activity/fleet/'. $datetime->format( 'Y-m-d/H:i' );
							$row_html .= '<td><a href="'.$fleet_url.'">';
							$row_html .= '<span class="hidden-sm hidden-xs">'. $datetime->format( 'l' ) .'&nbsp</span>';
							$row_html .= '<span class="hidden-xs">'. $datetime->format( 'F' ) .'&nbsp</span>' . $datetime->format( 'jS' ) . '</a></td>';
							$row_html .= '<td class="alignright"><a href="'.$fleet_url.'">' . $datetime->format( 'H:i' );
							$row_html .= '</a></td>';
							
							if( !$single_FC )
							{
								$row_html .= '<td class="aligncenter">';
								$UserID = $fleet->FC_ID;
								$row_html .= '<a href="/activity/FC/'. $UserID .'">';
								if( property_exists( $fleet, 'CharacterName' ) )
								{
									$row_html .= $fleet->CharacterName;
								}
								else
								{
									$row_html .= 'FC:'.$UserID;
								}
								$row_html .= '</a>';
								$row_html .= '</td>';
							}
							
							$row_html .= '<td class="aligncenter">';
							if( !$fleet->locationExact ) $row_html .= 'Near ';
							if( property_exists( $fleet, 'solarSystemName' ) && $fleet->solarSystemName != NULL )
							{
								$row_html .= link_solar_system( $fleet->solarSystemName );
							}
							else
							{
								if( $fleet->locationID != NULL )
								{
									$row_html .= $fleet->locationID;
								}/*
								else
								{
									$row_html .= 'Undecided';
								}*/
							}
							$row_html .= '</td>';
							
							$row_html .= '<td class="aligncenter">';
							$fleetID = $fleet->doctrineID;
							if( $fleetID != NULL )
							{
								$row_html .= '<a href="/doctrine/fleet/'.$fleetID.'">';
								$row_html .= '<span class="hidden-xs">';
								if( $fleet->fleetName != NULL )
								{
									$row_html .= $fleet->fleetName;
								}
								else
								{
									$row_html .= 'FleetID:'.$fleetID;
								}
								$row_html .= '</span>';
								$row_html .= '<span class="visible-xs-inline">';
								$row_html .= '<i class="fa fa-wrench fa-fw" aria-hidden="true"></i>';
								$row_html .= '</span>';
								$row_html .= '</a>';
							}
							$row_html .= '</td>';
							
							$row_html .= '<td>' . $fleet->additionalDetails . '</td>';
							
							$row_html .= '<td class="aligncenter">';
							$scheduledDateTime = $fleet->fleetTime;
							if( !array_key_exists( $scheduledDateTime, $invite_requests ) )
							{
								$row_html .= form_open('invites/request');
								$row_html .= form_hidden('scheduledDateTime', $scheduledDateTime);
								$row_html .= '<input type="submit" value="X-UP" name="X-UP" class="btn btn-xup"></form>';
							}
							else
							{
								$row_html .= form_open('invites/cancel');
								$row_html .= form_hidden('scheduledDateTime', $scheduledDateTime);
								$row_html .= '<input type="submit" value="Cancel" name="Cancel" class="btn btn-cancel"></form>';
							}
							$row_html .= "</td>\n";
							
						$row_html .= "</tr>\n";
						
						$rows_html = $row_html . $rows_html;	// Reverse row order
					}
					echo $rows_html; ?>
					</tbody>
				</table>
			</div>
			</div>
			
			&nbsp;<br>
			
			<div class="row">
				<div class="col-lg-6 col-lg-offset-1">
					<h3>What is this?</h3>
					This is the users' portion of Spectre Fleet's 'Fleet & Invite Manager' tool for our FCs.<br>
					You can <a href="/invite_manager">read this Guide</a> if you'd like to know more about how it helps all our pilots.<br>
					Contact @Tech on <a href="/discord">our Discord</a> if you have feedback to help improve it.<br>
					Or contact @Staff or use the <a href="/feedback">anonymous Feedback Form</a> if you wish to give feedback on an FC<br>
					(e.g. if perhaps they did not invite you to fleet after you had requested such).</p>
				</div>
				<div class="col-lg-4">
					<h3>Disclaimer</h3>
					<!--All our FCs have access to use this tool to auto-invite pilots to their active fleet, but not all are familiar with it yet, thus you may still need to X-Up in the traditional ingame channel-based manner.--><i class="fa fa-exclamation-triangle fa-fw" aria-hidden="true"></i>&nbsp;While CCP has not yet fixed their chat channel data issue, this schedule listing may not be accurate, and FCs cannot reliably honour invite requests.<br>
					We hope to relaunch an improved version of this feature once CCP improve the situation on their end. Thank you for your patience.</p>
				</div>
			</div>
			
			</div>
			
		</article>
		
	</div>
	
</div><!--/#content-->