
			<h2>Review article submissions</h2>
			
			<?php if( isset($_SESSION['flash_message']) )
			{
				echo '<h3>Notifications</h3><p>' . $_SESSION['flash_message'] . '</p><br>';
			} ?>
			
			<?php
			if( $CAN_SUBMIT_ARTICLES || $CAN_PUBLISH_ARTICLES )
			{
				if( $CAN_PUBLISH_ARTICLES )
				{ ?>
			<h4>Review for Publication</h4><?php
				}
				else
				{ ?>
			<h4>Submitted to Publishers</h4><?php
				} ?>
			<div class="table-responsive" style="overflow:hidden;">
				<table class="table table-hover table-bordered table_valign_m">
					<thead>
						<tr>
							<th>ID</th>
							<th>Name</th>
							<th class="col-md-1">Category</th>
							<th class="col-md-2">Author</th>
							<th class="text-center col-md-2">Status</th>
							<th class="text-center col-md-4">Available Actions</th>
						</tr>
					</thead>
					<tbody>
					<?php
					if( !empty($publishable_submissions) )
					{
						foreach($publishable_submissions as $submission)
						{
							echo '<tr>';
							echo '<td>'.$submission['SubmissionID'].'</td>';
							echo '<td>'.$submission['ArticleName'].'</td>';
							echo '<td>'.$submission['ArticleCategory'].'</td>';
							echo '<td>'.$submission['Username'].'</td>';
							echo '<td class="text-center">'.$submission['Status'].'</td>'; ?>
							<td class="text-center">
								<?php
								if( $CAN_PUBLISH_ARTICLES )
								{ ?>
								<div class="col-md-4"> <?php
									echo form_open('editor/publish_article');
									echo form_hidden('SubmissionID', $submission['SubmissionID']); ?>
									<input type="submit" name="submit" value="Publish" class="btn btn-danger btn-xs" style="width:90px;">
									</form>
								</div><?php
								} ?>
								<div class="col-md-4<?php if( !$CAN_PUBLISH_ARTICLES ) echo ' col-md-offset-4' ?>">
									<a href="/editor/preview_article/<?php echo $submission['SubmissionID']; ?>" class="btn btn-success btn-xs" style="width:90px;">Preview</a>
								</div><?php
								if( $CAN_PUBLISH_ARTICLES )
								{ ?>
								<div class="col-md-4">
									<?php
									echo form_open('editor/reject_submission');
									echo form_hidden('SubmissionID', $submission['SubmissionID']); ?>
									<input type="submit" name="submit" value="Reject" class="btn btn-warning btn-xs" style="width:90px;">
									</form>
								</div><?php
								} ?>
							</td>
							</tr> <?php
						}
					}
					?>
					</tbody>
				</table>
			</div> <?php
			}
			
			if( $CAN_SUBMIT_ARTICLES || $CAN_EDIT_OTHERS_SUBMISSIONS )
			{ ?>
			<h4>Submitted to Editors</h4>
			<div class="table-responsive" style="overflow:hidden;">
				<table class="table table-hover table-bordered table_valign_m">
					<thead>
						<tr>
							<th>ID</th>
							<th>Name</th>
							<th class="col-md-1">Category</th>
							<th class="col-md-2">Author</th>
							<th class="text-center col-md-2">Status</th>
							<th class="text-center col-md-4">Available Actions</th>
						</tr>
					</thead>
					<tbody>
					<?php
					if( !empty($submitted_submissions) )
					{
						foreach($submitted_submissions as $submission)
						{
							echo '<tr>';
							echo '<td>'.$submission['SubmissionID'].'</td>';
							echo '<td>'.$submission['ArticleName'].'</td>';
							echo '<td>'.$submission['ArticleCategory'].'</td>';
							echo '<td>'.$submission['Username'].'</td>';
							echo '<td class="text-center">'.$submission['Status'].'</td>'; ?>
							<td class="text-center">
								<div class="col-md-4"> <?php
									echo form_open('editor/promote_submission');
									echo form_hidden('SubmissionID', $submission['SubmissionID']); ?>
									<input type="submit" name="submit" value="Put to Review" class="btn btn-success btn-xs" style="width:90px;">
									</form>
								</div>
								<div class="col-md-4">
									<a href="/editor/edit_article/<?php echo $submission['SubmissionID']; ?>" class="btn btn-info btn-xs" style="width:90px;">Edit</a>
								</div>
									<?php
									if( $submission['can_retract'] )
									{ ?>
								<div class="col-md-4"> <?php
									echo form_open('editor/retract_submission');
									echo form_hidden('SubmissionID', $submission['SubmissionID']); ?>
									<input type="submit" name="submit" value="Retract" class="btn btn-warning btn-xs" style="width:90px;">
									</form>
								</div>
									<?php
									} ?>
							</td>
							</tr> <?php
						}
					}
					?>
					</tbody>
				</table>
			</div> <?php
			}

			if( $CAN_SUBMIT_ARTICLES )
			{ ?>
			<h4>Your draft submissions</h4>
			<div class="table-responsive" style="overflow:hidden;">
				<table class="table table-hover table-bordered table_valign_m">
					<thead>
						<tr>
							<th>ID</th>
							<th>Name</th>
							<th class="col-md-1">Category</th>
							<th class="col-md-2">Author</th>
							<th class="text-center col-md-2">Status</th>
							<th class="text-center col-md-4">Available Actions</th>
						</tr>
					</thead>
					<tbody>
					<?php
					if( !empty($draft_submissions) )
					{
						foreach($draft_submissions as $submission)
						{
							echo '<tr>';
							echo '<td>'.$submission['SubmissionID'].'</td>';
							echo '<td>'.$submission['ArticleName'].'</td>';
							echo '<td>'.$submission['ArticleCategory'].'</td>';
							echo '<td>'.$submission['Username'].'</td>';
							echo '<td class="text-center">'.$submission['Status'].'</td>'; ?>
							<td class="text-center">
								<div class="col-md-4"> <?php
									if( $submission['Status'] != 'Rejected' )
									{
									echo form_open('editor/submit_submission');
									echo form_hidden('SubmissionID', $submission['SubmissionID']); ?>
									<input type="submit" name="submit" value="Submit" class="btn btn-success btn-xs" style="width:90px;">
									</form>
									<?php
									} ?>
								</div>
								<div class="col-md-4">
									<a href="/editor/edit_article/<?php echo $submission['SubmissionID']; ?>" class="btn btn-info btn-xs" style="width:90px;">Edit</a>
								</div>
								<div class="col-md-4"> <?php
									echo form_open('editor/delete_submission');
									echo form_hidden('SubmissionID', $submission['SubmissionID']); ?>
									<input type="submit" name="submit" value="Delete" class="btn btn-danger btn-xs" style="width:90px;">
									</form>
								</div>
							</td>
							</tr> <?php
						}
					}
					?>
					</tbody>
				</table>
			</div> <?php
			}
			?>
