<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li><a href="/polls">Polls</a></li>
			<li class="active"><?php echo $Title; ?></li>
		</ol>
	</div>
	
	<div class="row">

		<header class="page-header col-md-10 col-md-offset-1">

			<h2 class="page-title"><!-- full-page-title-->
				<?php echo $Title; ?>
			</h2>

		</header>
		
		<article class="entry style-single style-single-full type-post col-lg-10 col-lg-offset-1 col-md-12 col-md-offset-0">
			
			<div class="entry-meta">
				<div>
					<span class="author">Created by <?php echo $Username; ?></span>,
					<span class="entry-date">on <?php echo substr($creationDate,0,10); ?></span>.
				</div>
			</div>

			<div class="entry-content">
				
				<div class="col-md-10 col-md-offset-1">
					<p><?php echo $Details; ?></p>
				</div>
				
				<div class="col-md-2">
					<br>
					<?php
					$all_can_view = TRUE;
					$only_FCs_can_vote = TRUE;
					switch( $accessMode )
					{
						case Polls_model::ALL_READ_ALL_VOTE_MODE:
							$only_FCs_can_vote = FALSE;
							break;
						/*case Polls_model::ALL_READ_FC_VOTE_MODE:
							break;*/
						case Polls_model::FC_READ_FC_VOTE_MODE:
							$all_can_view = FALSE;
							break;
						default:
							break;
					}
					echo ($all_can_view ? '<p>All can view this poll.</p>' : '<p>Only FCs can view this poll.</p>') ;
					?>
					<p>This Poll is <?php echo $Status; ?>.<?php
					if( !$is_logged_in )
					{ ?><br><a href="/login">Login or register</a> to participate in this poll.
					<?php
					} ?><br>
					<br>
					Total votes cast: <?php echo $total_votes; ?></p>
					<br>
				</div>
				
				<div class="col-md-8">
					<table class="table table-hover table-bordered">
						<thead>
						<tr>
							<th class="col-md-1 aligncenter">Votes</th>
							<th class="col-md-8">Option</th>
							<th class="col-md-3 aligncenter">Your Vote<?php if( $maximumVotesPerUser > 1) echo 's'; ?></th>
						</tr>
						</thead>
						<tbody>
						<?php
						for( $optionID = 0; $optionID < count($options); $optionID++ )
						{
							$option = $options[$optionID];
							$votes = $votes_per_option[$optionID]['Votes'];
							if( $total_votes == 0 )
							{
								$percentage = 0;
							}
							else
							{
								$percentage = ($votes / $total_votes) * 100;
							}
							?>
							<tr>
							<td class="aligncenter" style="vertical-align:middle;"><?php echo $votes; ?></td>
							<td><span class="poll_bg"><span class="poll_bar" style="width: <?php echo $percentage; ?>%"></span></span>
								<?php echo $option['Description']; ?><br><br>
							</td>
							<td class="aligncenter">
								<?php
								if( $is_logged_in )
								{
									if( $users_votes[$optionID]['UserID'] == NULL )
									{
										if( $can_vote_again )
										{
											echo form_open('polls/vote');
											echo form_hidden('pollID', $pollID);
											echo form_hidden('optionID', $option['optionID']); ?>
											<input type="submit" value="Vote" name="Vote" class="btn btn-vote">
											</form>
											<?php
										}
									}
									else
									{ ?>
										<!--<input type="submit" value="Vote Registered" class="btn btn-sm" style="width:120px;" disabled>-->
										<?php echo 'Cast '.substr($users_votes[$optionID]['timestamp'], 0, 16); ?>
									<?php
									}
								}
								else
								{ ?>
									<input type="submit" value="Login to Vote" class="btn btn-vote hidden-xs" disabled>
									<span class="visible-xs">Login to Vote</span>
								<?php
								} ?>
							</td>
							</tr>
						<?php
						} ?>
						</tbody>
					</table>
				</div>
				
				<div class="col-md-2">
					<br>
					<?php echo ( $only_FCs_can_vote ? '<p>Only FCs can vote in this poll.</p>' : '<p>All can vote in this poll.</p>' ); ?>
					<p>Users may each vote for <?php echo $maximumVotesPerUser; ?> option<?php if( $maximumVotesPerUser > 1) echo 's'; ?> in this poll.</p>
				</div>
				
			</div>
			
		</article>
		
	</div>
	
</div><!--/#content-->