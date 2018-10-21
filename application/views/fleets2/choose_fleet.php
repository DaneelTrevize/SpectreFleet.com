
			<div class="col-sm-12 entry-content">
				
				<div class="row">
					<h2>Choose which scheduled fleet you are forming:</h2>
				</div>
				
				<div class="row">
					<table class="table table-striped table_valign_m">
						<thead>
						<tr>
							<th>Type</th>
							<th class="col-md-2 alignright">Day & Date</th>
							<th class="aligncenter">Time</th>
							<th class="col-md-2 aligncenter">Fleet Commander</th>
							<th class="aligncenter">Form-up</th>
							<th class="col-md-2 aligncenter">Online Fits</th>
							<th>Additional Details</th>
							<th class="aligncenter">Form Fleet</th>
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
								$row_html .=  form_open('fleets2/choose_fleet');
								$row_html .=  form_hidden('scheduledDateTime', $scheduledDateTime);
								$row_html .= '<input type="submit" value="Form Fleet" name="Form Fleet" class="btn btn-block" style="font-size: 16px; width:120px;"></form>';
								$row_html .= "</td>\n";
								
							$row_html .= "</tr>\n";
							
							$rows_html = $row_html . $rows_html;	// Reverse row order
						}
						echo $rows_html; ?>
						</tbody>
					</table>
				</div>
				
				<div class="row">
					<?php echo validation_errors(); ?>
				</div>
			</div>
