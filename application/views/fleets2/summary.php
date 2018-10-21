
			<h2>Fleet Summary</h2>
			
			<?php $this->load->view( 'fleets2/control_panel' ); ?>
			
			<br>
			<h4>Fleet composition:</h4>
			
			<div class="col-sm-12">
				<div class="col-sm-4 col-sm-offset-1" id="members_toggle_down">
					<i class="fa fa-angle-double-down fa-fw"></i>&nbsp;Show All Ship Types&nbsp;<i class="fa fa-angle-double-down fa-fw"></i>
				</div>
				<div class="col-sm-4 col-sm-offset-2" id="members_toggle_up">
					<i class="fa fa-angle-double-up fa-fw"></i>&nbsp;Hide All Ship Types&nbsp;<i class="fa fa-angle-double-up fa-fw"></i>
				</div>
			</div>
			<br>
			<br>
			
			<table class="table table-striped-members table_valign_m" id="fleet_table">
				<thead>
				<tr>
					<th>Wing</th>
					<th>Squad</th>
					<th class="col-md-2">Character</th>
					<th>Hierarchy Role</th>
					<th class="col-md-2 aligncenter">Solar system</th>
					<!--<th class="col-md-3 aligncenter">Docked location</th>-->
					<th class="aligncenter">Exempt from warp</th>
				</tr>
				</thead>
			</table>
			
			<br>
			<div class="text-muted">Fleet membership and role can update every 5 seconds.<br>
			Fleet member location, docked status and ship type may only update every 60 seconds.</div>
			
			<script src="/js/fleet_common.js"></script>
			<script src="/js/fleet_summary.js"></script>
