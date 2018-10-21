
			<h2>FC Applications</h2>
			
			<?php if( isset($_SESSION['flash_message']) )
			{
				echo '<h3>Notifications</h3><p>' . $_SESSION['flash_message'] . '</p><br>';
			} ?>
			
			<h3>Outstanding FC Applications</h3>
			
			<div class="table-responsive">
				<table class="table table-hover table-bordered table_valign_m">
					<thead>
					<tr>
						<th class="col-md-1">ID</th>
						<th class="col-md-3">Applicant</th>
						<th class="col-md-2">Applicant's Timezone</th>
						<th class="col-md-2">Date Submitted</th>
						<th class="col-md-4 aligncenter">Actions</th>
					</tr>
					</thead>
					<tbody>
						<?php foreach( $outstanding_applications as $application )
						{ ?>
							<tr>
								<td>
									<a href="/manage/application/<?php echo $application['ApplicationID'] .'">'. $application['ApplicationID'];
									?></a>
								</td>
								<td>
									<img src="https://imageserver.eveonline.com/Character/<?php
									$CharacterID = $application['CharacterID'];
									$CharacterName = $application['CharacterName'];
									echo $CharacterID != NULL ? $CharacterID : '1';
									echo '_64.jpg" title="';
									echo $CharacterName;
									?>" class="img-rounded" style="height: 40px; width: 40px; margin-left: 0px; margin-bottom:0px">
									<?php
										echo $CharacterName;
									?>
								</td>
								<td>
									<?php $Timezone = $application['Timezone'];
									$tz_label = $fc_timezones[$Timezone];
									echo substr( $tz_label, 0, 13 );	// length"HH:MM - HH:MM" = 13 ?>
								</td>
								<td>
									<?php
									$DateSubmitted = $application['DateSubmitted'];
									if( $DateSubmitted != NULL )
									{
										echo date("F jS, Y",strtotime($application['DateSubmitted']));
									} ?>
								</td>
								<td class="text-center">
									<div class="col-md-4">
										<a href="/manage/application/<?php echo $application['ApplicationID']; ?>" class="btn btn-success btn-sm" style="width:70px;">Review</a>
									</div>
									<div class="col-md-4"><?php
										echo form_open('manage/accept_application');
										echo form_hidden('ApplicationID', $application['ApplicationID']); ?>
										<input type="submit" value="Accept" name="Accept" class="btn btn-warning btn-sm" style="width:70px;">
										</form>
									</div>
									<div class="col-md-4"><?php
										echo form_open('manage/reject_application');
										echo form_hidden('ApplicationID', $application['ApplicationID']); ?>
										<input type="submit" value="Reject" name="Reject" class="btn btn-danger btn-sm" style="width:70px;">
										</form>
									</div>
								</td>
							</tr><?php
						} ?>
					</tbody>
				</table>
			</div>
			
			<h3>Recent Previous FC Applications</h3>
			
			<div class="table-responsive">
				<table class="table table-hover table-bordered table_valign_m">
					<thead>
					<tr>
						<th class="col-md-1">ID</th>
						<th class="col-md-3">Applicant</th>
						<th class="col-md-2">Applicant's Timezone</th>
						<th class="col-md-1 aligncenter">Status</th>
						<th class="col-md-2">Date Enacted</th>
						<th class="col-md-3">Enacting User</th>
					</tr>
					</thead>
					<tbody>
						<?php foreach( $recent_applications as $application )
						{ ?>
							<tr>
								<td>
									<a href="/manage/application/<?php echo $application['ApplicationID'] .'">'. $application['ApplicationID'];
									?></a>
								</td>
								<td>
									<img src="https://imageserver.eveonline.com/Character/<?php
									$CharacterID = $application['ApplicantCharacterID'];
									$CharacterName = $application['ApplicantCharacterName'];
									echo $CharacterID != NULL ? $CharacterID : '1';
									echo '_64.jpg" title="';
									echo $CharacterName;
									?>" class="img-rounded" style="height: 40px; width: 40px; margin-left: 0px; margin-bottom:0px">
									<?php
										echo $CharacterName;
									?>
								</td>
								<td>
									<?php $Timezone = $application['Timezone'];
									$tz_label = $fc_timezones[$Timezone];
									echo substr( $tz_label, 0, 13 );	// length"HH:MM - HH:MM" = 13 ?>
								</td>
								<td class="text-center">
									<?php
										echo $application['Status'];
									?>
								</td>
								<td>
									<?php
									$DateEnacted = $application['DateEnacted'];
									if( $DateEnacted != NULL )
									{
										echo date("F jS, Y",strtotime($application['DateEnacted']));
									} ?>
								</td>
								<td>
									<img src="https://imageserver.eveonline.com/Character/<?php
									$CharacterID = $application['EnactingCharacterID'];
									$CharacterName = $application['EnactingCharacterName'];
									echo $CharacterID != NULL ? $CharacterID : '1';
									echo '_64.jpg" title="';
									echo $CharacterName;
									?>" class="img-rounded" style="height: 40px; width: 40px; margin-left: 0px; margin-bottom:0px">
									<?php
										echo $CharacterName;
									?>
								</td>
							</tr><?php
						} ?>
					</tbody>
				</table>
			</div>
			