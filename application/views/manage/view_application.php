
			<div class="entry-content col-xs-12 aligncenter">
				<h2>Spectre Fleet Command Application</h2>
				
				<h3>Character Name: <?php
					$CharacterName = $application['CharacterName'];
					echo $CharacterName; ?>
				</h3>
			</div>
			
			<div class="row">
			
				<div class="col-sm-6 aligncenter" style="margin-bottom: 30px;">
					<?php
					echo '<img src="https://imageserver.eveonline.com/Character/';
					$CharacterID = $application['CharacterID'];
					echo $CharacterID != NULL ? $CharacterID : '1';
					echo '_256.jpg" class="img-rounded" alt="'.$CharacterName.'">'; ?>
				</div>
				
				<div class="col-sm-6">
					<div class="row">
						<div class="col-xs-10 col-xs-offset-1">
							<br>Registration date: <?php
							$DateRegistered = $application['DateRegistered'];
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
							<!--Latest rank change: --><br><?php
							/*if( $DateRankChange == NULL )
							{
								echo 'Unknown';
							}
							else
							{
								$rankchanged_datetime = DateTime::createFromFormat( 'Y-m-d H:i:s.ue', $DateRankChange );
								echo $rankchanged_datetime->format( 'l F jS Y' );
							}*/ ?><br>
							<br>
							<a style="font-size: 20px;" href="https://zkillboard.com/character/<?php echo $CharacterID; ?>/">zKillboard&nbsp;<i class="fa fa-external-link" aria-hidden="true"></i></a>
						</div>
					</div>
					<br>
				</div>
			
			</div>
			
			<div class="col-sm-5 col-sm-offset-1">
				<label>Application status:</label>
				<p><?php
				$status = $application['Status'];
				echo $status; ?></p>
			</div>
			<div class="col-sm-5 col-sm-offset-1">
				<label>Date submitted:</label>
				<p><?php
				$DateSubmitted = $application['DateSubmitted'];
				if( $DateSubmitted != NULL )
				{
					echo date("F jS, Y",strtotime($DateSubmitted));
				}
				else
				{
					echo 'Not yet submitted.';
				}?></p>
			</div>
			
			&nbsp;<hr>

			<div class="row">
				<div class="col-sm-10 col-sm-offset-1">
				
				<h3>Other applications by this applicant:</h3>
				
				<?php
				if( $applicant_history !== FALSE && count($applicant_history) != 1 )
				{
					// Other applications
					?>
					<table class="table table-hover table-bordered table_valign_m">
						<thead>
						<tr>
							<th class="col-md-2 aligncenter">ID</th>
							<th class="col-md-4 aligncenter">Status</th>
							<th class="col-md-4">Date Submitted</th>
							<th class="col-md-2 aligncenter">Type</th>
						</tr>
						</thead>
					<tbody><?php
					foreach( $applicant_history as $history )
					{
						$historyID = $history['ApplicationID'];
						if( $historyID !== $application['ApplicationID'] )
						{
							echo '<tr>';
								echo '<td class="text-center"><a href="/manage/application/'.$historyID.'">'.$historyID.'</td>';
								echo '<td class="text-center">'.$history['Status'].'</td>';
								echo '<td>'.date("F jS, Y",strtotime($history['DateSubmitted'])).'</td>';
								echo '<td class="text-center">'.$history['Type'].'</td>';
							echo '</tr>';
						}
					}
					echo '</tbody></table>';
				}
				else
				{
					echo '<p>No other applications found.</p>';
				}?>
				</div>
			</div>
			
			&nbsp;<hr>
			
			<div class="col-sm-12">
				<label>Tell us about your Spectre Fleet experience:</label>
				<p><?php echo $application['SFexp']; ?></p>
			</div>
			
			&nbsp;<br>
			
			<div class="col-sm-12">
				<label>Do you have prior FC experience?</label>
				<p><?php echo $application['priorFC']; ?></p>
			</div>
			
			&nbsp;<br>
			
			<div class="col-sm-12">
				<label>Why do you want to FC for Spectre Fleet?</label>
				<p><?php echo $application['whySF']; ?></p>
			</div>
			
			&nbsp;<br>
			
			<div class="col-sm-12">
				<label>When would you most be likely to run fleets? (In Eve-Time)</label>
				<p><?php $Timezone = $application['Timezone'];
					echo $fc_timezones[$Timezone]; ?></p>
			</div>
			
			&nbsp;<br>
			
			<div class="col-sm-12">
				<label>What style of fleets would you like to run?</label>
				<p><?php echo $application['fleetStyle']; ?></p>
			</div>
			
			&nbsp;<br>
			
			<div class="col-sm-12">
				<label>What size of fleets are you comfortable with being responsible for?</label>
				<p><?php echo $application['fleetSize']; ?></p>
			</div>
			
			<?php
			// If SFC and app is Submitted, offer Accept/Reject
			// If owner, offer Confirm/Edit/Cancel
			if( $can_review )
			{ ?>
				&nbsp;<hr>
				
				<div class="row text-center">
					<p>Do you wish to Accept or Reject this Application?</p>
					<div class="col-md-6"><?php
						echo form_open('manage/accept_application');
						echo form_hidden('ApplicationID', $application['ApplicationID']); ?>
						<input type="submit" value="Accept" name="Accept" class="btn btn-warning" style="width:70px;">
						</form>
					</div>
					<div class="col-md-6"><?php
						echo form_open('manage/reject_application');
						echo form_hidden('ApplicationID', $application['ApplicationID']); ?>
						<input type="submit" value="Reject" name="Reject" class="btn btn-danger" style="width:70px;">
						</form>
					</div>
				</div>
				
				<hr><?php
			}
			else if( $is_owner )
			{ ?>
				&nbsp;<hr>
				
				<div class="row text-center">
					<p>You can Edit your application prior to submission. You can Cancel your application at any time.</p>
					<div class="col-md-4">
					<?php
					if( $status == 'Draft' )
					{ ?>
						<a href="/manage/confirm_application/<?php echo $application['ApplicationID']; ?>" class="btn btn-success btn-sm" style="width:70px;">Submit</a><?php
					} ?>
					</div>
					<div class="col-md-4">
					<?php
					if( $status == 'Draft' )
					{ ?>
						<a href="/manage/edit_application/<?php echo $application['ApplicationID']; ?>" class="btn btn-info btn-sm" style="width:70px;">Edit</a><?php
					} ?>
					</div>
					<div class="col-md-4"><?php
						if( $status == 'Draft' || $status == 'Submitted' )
						{
							echo form_open('manage/cancel_application');
							echo form_hidden('ApplicationID', $application['ApplicationID']); ?>
							<input type="submit" value="Cancel" name="Cancel" class="btn btn-warning btn-sm" style="width:70px;">
							</form><?php
						} ?>
					</div>
				</div>
				
				<hr><?php
			}?>
