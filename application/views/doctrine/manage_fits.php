
			<?php if( isset($_SESSION['flash_message']) )
			{
				echo '<h2>Notifications</h2><p>' . $_SESSION['flash_message'] . "</p>\n";
			} ?>

			<h2>Your Fits</h2>
			
			<div class="row">
				<div class="col-sm-6 col-sm-offset-3">
					<a href="/doctrine/new_fit" class="btn btn-primary btn-block">Create New Fit</a><br>
				</div>
			</div>
			
			<?php echo $pages_count_html; ?>
			<div class="row">
				<div class="col-sm-12 table-responsive">
					<table class="table table-hover table-bordered">
						<thead>
							<tr>
								<th colspan="2">Fit</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach( $fits as $fit )
							{ ?>
								<tr>
									<td style="vertical-align:middle;width:96px;">
										<?php
											echo '<img class="timg img-rounded" data-toggle="tooltip" data-placement="top" title="'.$fit['shipName'].'" src="https://imageserver.eveonline.com/Type/'.$fit['shipID'].'_64.png">';
										?>
									</td>
									<td>
										<a style="text-decoration:none;" href="/doctrine/fit/<?php echo $fit['fitID']; ?>">
											<div class="pull-right">FitID: <?php echo $fit['fitID']; ?></div>
											<div class="thtext"><?php echo $fit['fitName']; ?></div>
										</a>
										<div class="pull-right"><?php
											
											if( $fit['status'] == 'Official' )
											{
												echo '<img src="/media/image/logo/favicon_purple_32px.png" width="32px" height="32px" style="margin-bottom: 0px">&nbsp;Official Fit';
											}
											elseif( $fit['status'] == 'Retired' )
											{
												echo 'Retired';
											}
											else
											{
												echo form_open('doctrine/retire_fit');
												echo form_hidden('fitID', $fit['fitID']);
												echo '<a href="/doctrine/edit_fits/' . $fit['fitID'] . '" class="btn btn-info btn-sm">Edit Fit</a>';
												echo ' <input type="submit" value="Retire Fit" name="Retire" class="btn btn-danger btn-sm">';
												echo '</form>';
											} ?>
										</div>
										<div><?php echo 'Combat Role: '.$fit['fitRole']; ?></div>
										<div class="tftext">
											<?php echo 'Created by You on '.date("F jS, Y",strtotime($fit['date'])); ?>
											<span class="pull-right"><?php echo 'Last edited '.date("F jS, Y",strtotime($fit['lastEdited'])); ?></span>
										</div>
									</td>
								</tr><?php
							} ?>
						</tbody>
					</table>
				</div>
			</div>
			
			<?php echo $pages_arrows_html; ?>
