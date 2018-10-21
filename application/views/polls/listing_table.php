<?php
if( !empty( $polls ) )
{ ?>
				<div class="table-responsive" style="min-width:330px;">
				<table class="table table-striped table_valign_m">
					<thead>
					<tr><?php
						if( $can_view_FC_polls )
						{ ?>
						<th class="col-md-1">Non-FCs</th>
						<th class="col-md-4">Title</th><?php
						}
						else
						{ ?>
						<th class="col-md-5">Title</th><?php
						} ?>
						<th class="col-md-1 aligncenter">Options</th>
						<th class="col-md-2 aligncenter">Max Votes Per User</th>
						<th class="col-md-2">Creator</th>
						<th class="col-md-2">Creation Date</th>
					</tr>
					</thead>
					<tbody><?php
					foreach( $polls as $poll )
					{ ?>
						<tr>
							<?php
							if( $can_view_FC_polls )
							{
								echo '<td>';
								switch( $poll['accessMode'] )
								{
									case Polls_model::ALL_READ_ALL_VOTE_MODE:
										echo '<i class="fa fa-check-square-o fa-fw" aria-hidden="true"></i>&nbsp;Can Vote';
										break;
									case Polls_model::ALL_READ_FC_VOTE_MODE:
										echo '<i class="fa fa-eye fa-fw" aria-hidden="true"></i>&nbsp;Can see';
										break;
									case Polls_model::FC_READ_FC_VOTE_MODE:
										echo '<i class="fa fa-eye-slash fa-fw" aria-hidden="true"></i>&nbsp;Can\'t see';
										break;
									default:
										break;
								}
								echo '</td>';
							} ?>
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
							<td>
								<?php
									echo substr( $poll['creationDate'], 0, 16 );
								?>
							</td>
						</tr><?php
					} ?>
					</tbody>
				</table>
			</div><?php
} ?>