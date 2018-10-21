<?php
if( !empty( $polls ) )
{ ?>
			<div class="table-responsive" style="min-width:330px;">
				<table class="table table-hover table-bordered table_valign_m">
					<thead>
					<tr>
						<th>ID</th>
						<th class="col-md-1 aligncenter">Non-FCs</th>
						<th class="col-md-3">Title</th>
						<th class="col-md-1 aligncenter">Options</th>
						<th class="col-md-1 aligncenter">Max Votes Per User</th>
						<th class="col-md-2">Creator</th>
						<th class="col-md-4 aligncenter">Actions</th>
					</tr>
					</thead>
					<tbody>
						<?php foreach( $polls as $poll )
						{ ?>
							<tr>
								<td>
									<?php
										echo $poll['pollID'];
									?>
								</td>
								<td class="aligncenter">
									<?php
									switch( $poll['accessMode'] )
									{
										case Polls_model::ALL_READ_ALL_VOTE_MODE:
											echo '<i class="fa fa-check-square-o fa-fw" aria-hidden="true"></i>';
											break;
										case Polls_model::ALL_READ_FC_VOTE_MODE:
											echo '<i class="fa fa-eye fa-fw" aria-hidden="true"></i>';
											break;
										case Polls_model::FC_READ_FC_VOTE_MODE:
											echo '<i class="fa fa-eye-slash fa-fw" aria-hidden="true"></i>';
											break;
										default:
											break;
									} ?>
								</td>
								<td>
									<a style="text-decoration:none;" href="/polls/<?php echo $poll['pollID']; ?>">
									<?php
										echo $poll['Title'];
									?>
									</a>
								</td>
								<td class="aligncenter">
									<?php
										echo $poll['OptionsCount'];
									?>
								</td>
								<td class="aligncenter">
									<?php
										echo $poll['maximumVotesPerUser'];
									?>
								</td>
								<td>
									<?php
										echo $poll['Username'];
									?>
								</td>
								<td class="text-center"><?php
									if( $poll['Status'] === 'Open' )
									{
										echo '<div class="col-md-4 col-md-offset-4">';
										echo form_open('polls/close');
										echo form_hidden('pollID', $poll['pollID']); ?>
										<input type="submit" value="Close" name="Close" class="btn btn-warning btn-xs"style="width:90px;">
										</form>
									</div><?php
									}
									else
									{
										echo '<div class="col-md-4">';
										echo form_open('polls/open');
										echo form_hidden('pollID', $poll['pollID']); ?>
										<input type="submit" value="Open" name="Open" class="btn btn-success btn-xs"style="width:90px;">
										</form>
									</div><?php
										
										if( $show_edit_button )
										{ ?>
									<div class="col-md-4">
										<a href="/polls/edit/<?php echo $poll['pollID']; ?>" class="btn btn-info btn-xs" style="width:90px;">Edit</a>
									</div>
									<div class="col-md-4"><?php
										}
										else
										{ ?>
									<div class="col-md-4 col-md-offset-4"><?php
										}
										echo form_open('polls/delete');
										echo form_hidden('pollID', $poll['pollID']); ?>
										<input type="submit" value="Delete" name="Delete" class="btn btn-danger btn-xs"style="width:90px;">
										</form>
									</div><?php
									} ?>
								</td>
							</tr><?php
						} ?>
					</tbody>
				</table>
			</div><?php
} ?>