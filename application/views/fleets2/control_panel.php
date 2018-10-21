
		<script type="text/javascript">csrf_hash = '<?php echo $csrf_hash; ?>';</script>
		
		<div class="row" id="control_panel_cover">
			<div class="col-sm-12 aligncenter">
				Loading...&nbsp;<i class="fa fa-cog fa-spin fa-fw fa-2x"></i>
				
				<hr>
			</div>
		</div>
		<div hidden id="control_panel">
			
			<div class="row">
				<div class="ui-input col-sm-4">
					<button class="btn btn-warning btn-manager" id="forget_url"><i class="fa fa-link fa-fw" aria-hidden="true"></i>&nbsp;Forget Fleet URL</button>
				</div>
				
				<div hidden class="col-sm-4" id="choose_fleet">
					<a class="btn btn-success btn-manager" href="/fleets2/choose_fleet"><i class="fa fa-calendar fa-fw" aria-hidden="true"></i>&nbsp;Form A<span class="visible-lg-inline"> Scheduled</span> Fleet</a>
				</div>
				<div hidden class="ui-input col-sm-4">
					<button class="btn btn-warning btn-manager" id="forget_details"><i class="fa fa-calendar-o fa-fw" aria-hidden="true"></i>&nbsp;Forget<span class="visible-lg-inline"> Scheduled</span> Details</button>
				</div>
				<div class="ui-input col-sm-4">
					<button class="btn btn-success btn-manager" data-toggle="modal" data-target="#XUP_modal" id="reset_xup"><i class="fa fa-code fa-fw" aria-hidden="true"></i>&nbsp;Reset XUP<span class="visible-lg-inline"> MOTD</span></button>
				</div>
			</div>
			
			<div class="row">
				<div class="ui-input col-sm-4" id="auto_kick_area">
					<label class="checkbox">
						<input disabled type="checkbox" data-toggle="toggle" data-onstyle="danger" data-on="Auto Kicking Enabled" data-offstyle="default" data-off="Auto Kicking Disabled" data-width="100%" id="auto_kick">
					</label>
				</div>
				
				<div class="ui-input col-sm-4">
					<button class="btn btn-danger btn-manager" id="kick_banned"><i class="fa fa-ban fa-fw" aria-hidden="true"></i>&nbsp;Kick Banned<span class="visible-lg-inline"> Members</span></button>
				</div>
				
				<div hidden class="col-sm-4" id="manage_fleet">
					<a class="btn btn-info btn-manager" href="/fleets2/invite_manager"><i class="fa fa-envelope fa-fw" aria-hidden="true"></i>&nbsp;Manage Fleet Invites</a>
				</div>
				<div hidden class="col-sm-4" id="view_summary">
					<a class="btn btn-info btn-manager" href="/fleets2/summary"><i class="fa fa-sitemap fa-fw" aria-hidden="true"></i>&nbsp;View<span class="visible-lg-inline"> Fleet</span> Summary</a>
				</div>
			</div>
			
			<br>
			
			<div hidden class="row" id="third_row">
				<div class="ui-input col-sm-4">
					<button class="btn btn-success btn-manager" id="set_motd"><i class="fa fa-list-alt fa-fw" aria-hidden="true"></i>&nbsp;Set Fleet MOTD<span class="visible-lg-inline"> & Free-Move</span></button>
				</div>
				
				<div class="ui-input col-sm-4">
					<button class="btn btn-success btn-manager" data-toggle="modal" data-target="#XUP_modal" id="generate_xup"><i class="fa fa-code fa-fw" aria-hidden="true"></i>&nbsp;Generate XUP<span class="visible-lg-inline"> MOTD</span></button>
				</div>
				<div class="ui-input col-sm-4">
					<button class="btn btn-success btn-manager" id="ping_discord"><i class="fa fa-bell fa-fw" aria-hidden="true"></i>&nbsp;Ping Discord<span class="visible-lg-inline"> #Ops</span></button>
				</div>
				<div hidden class="ui-input col-sm-4">
					<button disabled class="btn btn-success btn-manager" id="have_pinged_discord"><i class="fa fa-bell-slash fa-fw" aria-hidden="true"></i>&nbsp;Already Pinged<span class="visible-lg-inline"> Discord</span></button>
				</div>
			</div>
			
			<div hidden class="row" id="forth_row">
				<div hidden class="ui-input col-sm-4">
					<button class="btn btn-success btn-manager" id="list_fits"><i class="fa fa-wrench fa-fw" aria-hidden="true"></i>&nbsp;List Fits In Fleet<span class="visible-lg-inline"> Chat</span></button>
				</div>
				<div class="ui-input col-sm-4">
					<button disabled class="btn btn-success btn-manager" id="no_fits"><i class="fa fa-wrench fa-fw" aria-hidden="true"></i>&nbsp;No Doctrine<span class="visible-lg-inline"> to List</span></button>
				</div>
				
				<div hidden class="ui-input col-sm-4">
					<button class="btn btn-success btn-manager" id="send_invites"><i class="fa fa-envelope fa-fw" aria-hidden="true"></i>&nbsp;<span class="visible-lg-inline">Manually </span>Send Invites</button>
				</div>
				
				<div hidden class="ui-input col-sm-4" id="auto_invites_area">
					<label class="checkbox">
						<input type="checkbox" data-toggle="toggle" data-onstyle="success" data-on="Auto Inviting Enabled" data-offstyle="default" data-off="Auto Inviting Disabled" data-width="100%" id="auto_invites">
					</label>
				</div>
			</div>
			
			<br>
			
			<div class="row">
				<div class="col-sm-3">
					<span class="text-muted"><i class="fa fa-refresh fa-fw fa-2x" aria-hidden="true" id="manual_update"></i><span id="update_speed">Page not updating</span></span>
				</div>
				<div class="col-sm-5">
					<span class="text-muted" id="queue"><i class="fa fa-cogs fa-fw fa-2x" aria-hidden="true"></i>&nbsp;Queue: </span>
					<span hidden id="pinging_Ops"><i class="fa fa-bell fa-fw fa-2x" aria-hidden="true"></i>&nbsp;</span>
					<span hidden id="setting_MOTD"><i class="fa fa-list-alt fa-fw fa-2x" aria-hidden="true"></i>&nbsp;</span>
					<span hidden id="listing_fits"><i class="fa fa-wrench fa-fw fa-2x" aria-hidden="true"></i>&nbsp;</span>
					<span hidden id="manually_updating"><i class="fa fa-refresh fa-fw fa-2x" aria-hidden="true"></i></span>
					<span hidden id="updating"><i class="fa fa-refresh fa-fw fa-2x" aria-hidden="true"></i></span>
					<span hidden id="sending_invites"><i class="fa fa-envelope fa-fw fa-2x" aria-hidden="true"></i>&nbsp;</span>
					<span hidden id="kicking_banned"><i class="fa fa-ban fa-fw fa-2x" aria-hidden="true"></i>&nbsp;</span>
					<span hidden id="auto_inviting"><i class="fa fa-repeat fa-fw fa-2x" aria-hidden="true"></i>&nbsp;</span>
					<span hidden id="auto_kicking"><i class="fa fa-ban fa-fw fa-2x" aria-hidden="true"></i>&nbsp;</span>
				</div>
				<div class="col-sm-4">
					<span class="pull-right" id="last_action"></span>
				</div>
			</div>
			
			<hr>
		</div>
		
		<div id="XUP_modal" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">XUP Channel MOTD</h4>
					</div>
					<div class="modal-body">
						<ol>
						<li>Open the in-game channel editor, and click inside the MOTD field.</li>
						<li>Ctrl+A to select all, Del to clear it to 0/4000 characters used.</li>
						<li>Click an MOTD below to select it, Ctrl+C to copy it to your clipboard.</li>
						<li>Ctrl+V to paste it into the in-game editor field, OK to save your changes.</li>
						</ol>
						<form name="myform">
							<div class="form-group">
								<label>Blank XUP MOTD:</label>
								<textarea onclick="this.select();" class="col-md-12" style="line-height:14px;" rows="10" readonly="readonly"><?php echo $XUP_blank; ?></textarea>
								
								<?php
								if( $fleet_scheduled_details )
								{ ?><br>&nbsp;<br>
									<label>Generated XUP MOTD:</label>
									<textarea onclick="this.select();" class="col-md-12" style="line-height:14px;" rows="10" readonly="readonly"><?php echo $XUP_generated; ?></textarea>
								<?php
								} ?>
							</div>
							&nbsp;<br>
							<div class="form-group aligncenter">
								<a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		
		<?php
		if( $fleet_scheduled_details )
		{ ?>
		<div class="row" id="scheduled_header">
			<div class="col-sm-12">
				<h4>Scheduled fleet details:&nbsp;<i class="fa fa-angle-double-down fa-fw" id="scheduled_toggle"></i><br></h4>
			</div>
		</div>
		
		<div hidden class="row" id="scheduled_details">
			<div class="col-sm-10 col-sm-offset-1">
				<br><?php
				echo '<div class="row">';
					echo '<div class="col-sm-6">';
					
					echo 'Type: <strong>'.$fleet_scheduled_details['type'].'</strong><br>';
					echo 'Date: <strong>'.$fleet_scheduled_details['pretty_date'].'</strong><br>';
					echo 'Time: <strong>'.$fleet_scheduled_details['time'].'</strong><br>';
					
				echo '</div>';
				echo '<div class="col-sm-6">';
					
					echo 'FC: <strong>';
					if( $fleet_scheduled_details['FC_ID'] !== FALSE )
					{
						$FC_CharacterName = $fleet_scheduled_details['FC_ID']['CharacterName'];
						$FC_UserID = $fleet_scheduled_details['FC_ID']['UserID'];
						echo '<a href="/activity/FC/'. $FC_UserID .'">'. $FC_CharacterName .'</a>';
					}
					else
					{
						echo $fleet_scheduled_details['FC'];
					}
					echo '</strong><br>';
					
					echo 'Form-up Location: <strong>';
					if( !$fleet_scheduled_details['location_exact'] ) echo 'Near ';
					//echo $fleet_scheduled_details['location'] . '</strong><br>';
					echo link_solar_system( $fleet_scheduled_details['location_name'] );
					echo '</strong><br>';
					
					echo 'Online Fits: ';
					if( $fleet_scheduled_details['doctrine'] != '' )
					{
						echo '<strong>';
						if( isset($fleet_scheduled_details['doctrine_name']) && $fleet_scheduled_details['doctrine_name'] !== FALSE )
						{
							echo '<a href="/doctrine/fleet/'.$fleet_scheduled_details['doctrine'].'">'.$fleet_scheduled_details['doctrine_name'].'</a>';
						}
						else
						{
							echo '<a href="/doctrine/fleet/'.$fleet_scheduled_details['doctrine'].'">fleetID:'.$fleet_scheduled_details['doctrine'].'</a><small> Missing?</small>';
						}
						echo '</strong>';
					}
					else
					{
						echo 'No Doctrine specified';
					}
					echo '<br>';
					echo '</div>';
				echo '</div>';
				
				echo 'Additional Details: ' . $fleet_scheduled_details['remaining_details'].'<br>';
				?>
			</div>
		</div>
		
		<hr><?php
		} ?>
		
		<div class="row">
			<div class="col-sm-3" >
				Fleet size:&nbsp;<strong><span id="member_count">-</span></strong>
			</div>
			<div class="col-sm-9" >
				Empty squad positions:&nbsp;<strong><span id="members_space">-</span></strong>
			</div>
		</div>