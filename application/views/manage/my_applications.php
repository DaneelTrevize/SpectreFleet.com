
			<h2>Your FC Applications</h2>
			
			<?php if( isset($_SESSION['flash_message']) )
			{
				echo '<h3>Notifications</h3><p>' . $_SESSION['flash_message'] . '</p><br>';
			} ?>
			
			<?php
			if( !isset( $outstanding_application ) )
			{ ?>
				You have no outstanding FC application.<br>
				<br>
				<div class="row">
					<div class="col-sm-6 col-sm-offset-3">
						<a href="/manage/apply" class="btn btn-primary btn-block">Apply to FC</a>
					</div>
				</div>
			<?php
			}
			else
			{ ?>
			
			<h3>Your Latest FC Application</h3>
			
			<div class="table-responsive">
				<table class="table table-hover table-bordered table_valign_m">
					<thead>
					<tr>
						<th class="col-md-1">ID</th>
						<th class="col-md-3">Last Edited</th>
						<th class="col-md-3">Date Submitted</th>
						<th class="col-md-1 aligncenter">Status</th>
						<th class="col-md-4 aligncenter">Actions</th>
					</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<a href="/manage/application/<?php echo $outstanding_application['ApplicationID'] .'">'. $outstanding_application['ApplicationID'];
								?></a>
							</td>
							<td>
								<?php
									echo date("F jS, Y",strtotime($outstanding_application['lastEdited']));
								?>
							</td>
							<td>
								<?php
								$DateSubmitted = $outstanding_application['DateSubmitted'];
								if( $DateSubmitted != NULL )
								{
									echo date("F jS, Y",strtotime($outstanding_application['DateSubmitted']));
								} ?>
							</td>
							<td class="text-center">
								<?php
									$status = $outstanding_application['Status'];
									echo $status;
								?>
							</td>
							<td class="text-center">
								<div class="col-md-4">
								<?php
								if( $status == 'Draft' )
								{ ?>
									<a href="/manage/confirm_application/<?php echo $outstanding_application['ApplicationID']; ?>" class="btn btn-success btn-sm" style="width:70px;">Submit</a><?php
								} ?>
								</div>
								<div class="col-md-4">
								<?php
								if( $status == 'Draft' )
								{ ?>
									<a href="/manage/edit_application/<?php echo $outstanding_application['ApplicationID']; ?>" class="btn btn-info btn-sm" style="width:70px;">Edit</a><?php
								} ?>
								</div>
								<div class="col-md-4"><?php
									echo form_open('manage/cancel_application');
									echo form_hidden('ApplicationID', $outstanding_application['ApplicationID']); ?>
									<input type="submit" value="Cancel" name="Cancel" class="btn btn-warning btn-sm" style="width:70px;">
									</form>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div><?php
			} 
			
			if( count( $previous_applications ) > 0 )
			{ ?>
			<h3>Your Previous FC Applications</h3>
			
			<div class="table-responsive">
				<table class="table table-hover table-bordered table_valign_m">
					<thead>
					<tr>
						<th class="col-md-1">ID</th>
						<th class="col-md-3">Last Edited</th>
						<th class="col-md-3">Date Submitted</th>
						<th class="col-md-1 aligncenter">Status</th>
						<th class="col-md-4 aligncenter">Date Enacted</th>
					</tr>
					</thead>
					<tbody>
						<?php foreach( $previous_applications as $application )
						{ ?>
							<tr>
								<td>
									<a href="/manage/application/<?php echo $application['ApplicationID'] .'">'. $application['ApplicationID'];
									?></a>
								</td>
								<td>
									<?php
										echo date("F jS, Y",strtotime($application['lastEdited']));
									?>
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
									<?php
										echo $application['Status'];
									?>
								</td>
								<td class="text-center">
									<?php
									$DateEnacted = $application['DateEnacted'];
									if( $DateEnacted != NULL )
									{
										echo date("F jS, Y",strtotime($application['DateEnacted']));
									} ?>
								</td>
							</tr><?php
						} ?>
					</tbody>
				</table>
			</div><?php
			}
			else
			{
				echo '<br>You have no previous FC applications.';
			}?>
