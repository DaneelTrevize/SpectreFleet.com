
			<h2>Feedback</h2>
			
			<p><?php echo $CharacterName; ?>, <a href="<?php echo '/feedback/'.$UserID; ?>">this is your custom feedback link/URL <i class="fa fa-external-link" aria-hidden="true"></i></a>.<br>
			It helps users to easily leave feedback specifically for you.</p>
			
			<h3>Search Feedback</h3>
			
			<form method="get" accept-charset="utf-8" action="/feedback/search">
			<div class="row">
				<div class="col-md-6">
					<div class="ui-input">
						<span>Fleet Commander</span>
						<input type="text" name="FleetFC" placeholder="FC's Name" value="<?php if( isset($FleetFC) ) echo $FleetFC; ?>" class="form-control">
					</div>
					<div class="ui-input">
						<span>Fleet Date</span>
						<input id="datepicker" type="text" name="Date" placeholder="YYYY-MM-DD" value="<?php if( isset($Date) ) echo $Date; ?>" class="form-control">
						<script type="text/javascript">
						$( function() {
							$( "#datepicker" ).datepicker( { dateFormat: "yy-mm-dd"} );
						} );
						</script>
					</div>
				</div>
				<div class="col-md-6">
					<div class="ui-input">
						<span>Feedback Text</span>
						<input type="text" name="Details" placeholder="Keyword" value="<?php if( isset($Details) ) echo $Details; ?>" class="form-control">
					</div>
					<div class="row ui-input">
						<div class="col-md-4">
							<span>Order By</span><br>
							<select name="orderType" class="form-control" style="display:inline;">
								<?php
								foreach( $orderTypes as $orderType_value => $orderType_name )
								{
									echo '<option value="'.$orderType_value.'"';
									if( isset($orderType) && $orderType == $orderType_value) echo ' selected';
									echo '>'.$orderType_name."</option>\n";
								}
								?>
							</select>
						</div>
						<div class="col-md-4">
							<span>Sort direction</span><br>
							<select name="orderSort" class="form-control" style="display:inline;">
								<option value="DESC"<?php if($orderSort=='DESC') echo ' selected'; ?>>Descending</option>
								<option value="ASC"<?php if($orderSort=='ASC') echo ' selected'; ?>>Ascending</option>
							</select>
						</div>
						<div class="col-md-4">
							<span>Page Size</span><br>
							<select name="pageSize" class="form-control" style="display:inline;">
								<?php
								foreach( $pageSizes as $possible_pageSize )
								{
									echo '<option value="'.$possible_pageSize.'"';
									if( isset($pageSize) && $pageSize == $possible_pageSize) echo ' selected';
									echo '>'.$possible_pageSize."</option>\n";
								}
								?>
							</select>
						</div>
					</div>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="ui-input col-sm-6 col-sm-offset-3 col-xs-10 col-xs-offset-1">
					<button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search fa-fw" aria-hidden="true"></i>&nbsp;Search Feedback</button><br>
				</div>
				<div class="ui-input col-md-2 col-md-offset-1 col-sm-3 col-sm-offset-0 col-xs-10 col-xs-offset-1">
					<a class="btn btn-primary btn-block" href="/feedback/search">Reset Filters</a><br>
				</div>
			</div>
			</form>
			
			<div class="container-fluid">
				
				<?php echo $pages_count_html;
				
				if( $results_on_page > 0 )
				{ ?>
				<div class="row">
					<table class="table table-striped" id="feedback_results">
						<thead>
							<tr>
								<th class="col-sm-2">Fleet Commander</th>
								<th class="col-sm-9 aligncenter">Feedback Details</th>
								<th class="col-sm-1 aligncenter">Score</th>
							</tr>
						</thead>
						<tbody>
						<?php if( !empty($feedback) )
						{
							foreach($feedback as $row)
							{ ?>
								<tr>
									<td>
										<div class="feedback-tdfc">
										<?php
										echo '<a href="/activity/FC/' . $row['UserID'] . '"><p>';
										if( $row['UserID'] !== $UserID )
										{
											echo $row['CharacterName'];
										}
										else
										{
											echo '<i>You</i>';
										}
										$CharacterID = $row['CharacterID'] == NULL ? 1 : $row['CharacterID'];
										echo '<br><img class="img-rounded" src="https://imageserver.eveonline.com/Character/' . $CharacterID . '_64.jpg">';
										?></p></a>
										</div>
									</td>
									<td>
										<div class="feedback-tdheader">Left on <?php
										$date = DateTime::createFromFormat( 'Y-m-d H:i:s', $row['Date'] );
										echo $date->format( 'F jS Y \a\t H:i' ) ?>
											<div class="pull-right"><a href="/feedback/search?FeedbackID=<?php echo $row['FeedbackID']; ?>">Feedback ID: <?php echo $row['FeedbackID']; ?></a></div>
										</div>
										<div class="feedback-tdtext"><p><?php echo $row['Feedback']; ?></p></div>
									</td>
									<td class="feedback-score aligncenter"><?php
									$score = $row['Score'];
									switch ( $score )
									{
										case 2:
											echo '<span class="sf_rate_2">-</span>';
											break;
										case 4:
											echo '<span class="sf_rate_4">-</span>';
											break;
										case 6:
											echo '<span class="sf_rate_6">=</span>';
											break;
										case 8:
											echo '<span class="sf_rate_8">+</span>';
											break;
										case 10:
											echo '<span class="sf_rate_10">+</span>';
											break;
										default:
											echo $score;
											break;
									} ?></td>
								</tr><?php
							}
						} ?>
						</tbody>
					</table>
				</div><?php
				}
				
				echo $pages_arrows_html; ?>
				
			</div>
