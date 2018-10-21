
			<h2>Manage Fleet Invites</h2>
			
			<?php $this->load->view( 'fleets2/control_panel' ); ?>
			
			<br>
			<div class="row">
				<div class="col-sm-3" >
					Total invite requests:&nbsp;<strong><span id="invite_requests_count">-</span></strong>
				</div>
				<div class="col-sm-3">
					Awaiting invites:&nbsp;<strong><span id="awaiting_invites_count">-</span></strong>
				</div>
				<div class="col-sm-6">
					Can currently fit all invitees?&nbsp;<strong><span id="can_fit_all_invites">-</span></strong>
				</div>
			</div>
			
			<br>
			<h4>Invitees:</h4>
			
			<div class="row">
				<div class="col-lg-10 col-lg-offset-1">
					<span hidden id="invites_none">There are no outstanding invitees at this time.<br><br></span>
					<table hidden class="table table_valign_m" id="invites_table">
						<thead>
							<tr>
								<th>Character Name</th>
								<th class="aligncenter">Total Invites Sent</th>
								<th class="aligncenter">Time Since Last Invited</th>
								<th>Recent API response</th>
							</tr>
						</thead>
						<tbody id="new_awaiting">
						</tbody>
						<tbody id="awaiting_invites">
						</tbody>
						<tbody id="sent_invites">
						</tbody>
						<tbody id="recent_cancels">
						</tbody>
					</table>
					
				</div>
			</div>
			
			<br>
			<div class="text-muted">New sets of invites can be sent every 5 seconds.<br>
			Repeat invites are only sent to any specific character once every 60 seconds.<br>
			After 15 minutes' worth of failed invites to a specific character, they are only invited every 5 minutes.</div>
			
			<script src="/js/fleet_common.js"></script>
			<script src="/js/fleet_invite_manager.js"></script>
