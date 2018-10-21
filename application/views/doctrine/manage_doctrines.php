
			<?php if( isset($_SESSION['flash_message']) )
			{
				echo '<h2>Notifications</h2><p>' . $_SESSION['flash_message'] . "</p>\n";
			} ?>

			<h2>Your Doctrines</h2>
			
			<div class="row">
				<div class="col-sm-6 col-sm-offset-3">
					<a href="/doctrine/new_fleet" class="btn btn-primary btn-block">Create New Doctrine</a><br>
				</div>
			</div>
			
			<?php echo $pages_count_html; ?>
			<div class="row">
				<div class="col-sm-12 table-responsive">
					<table class="table table-hover table-bordered">
						<thead>
							<tr>
								<th>Doctrine</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach($fleets as $fleet)
							{ ?>
								<tr>
									<td>
										<a style="text-decoration:none;" href="/doctrine/fleet/<?php echo $fleet['fleetID']; ?>">
											<div class="pull-right">FleetID: <?php echo $fleet['fleetID']; ?></div>
											<div class="thtext"><?php echo $fleet['fleetName']; ?></div>
										</a>
										<div class="pull-right"><?php
											
											if( $fleet['status'] == 'Official' )
											{
												echo '<img src="/media/image/logo/favicon_purple_32px.png" width="32px" height="32px" style="margin-bottom: 0px">&nbsp;Official Doctrine';
											}
											elseif( $fleet['status'] == 'Retired' )
											{
												echo 'Retired';
											}
											else
											{
												echo form_open('doctrine/retire_fleet');
												echo form_hidden('fleetID', $fleet['fleetID']);
												echo '<a href="/doctrine/edit_fleets/' . $fleet['fleetID'] . '" class="btn btn-info btn-sm">Edit Doctrine</a>';
												echo ' <input type="submit" value="Retire Doctrine" name="Retire" class="btn btn-danger btn-sm">';
												echo '</form>';
											} ?>
										</div>
										<div><?php echo 'Combat Type: '.$fleet['fleetType']; ?></div>
										<div class="tftext">
											<?php echo 'Created by You on '.date("F jS, Y",strtotime($fleet['date'])); ?>
											<span class="pull-right"><?php echo 'Last edited '.date("F jS, Y",strtotime($fleet['lastEdited'])); ?></span>
										</div>
									</td>
								</tr><?php
							} ?>
						</tbody>
					</table>
				</div>
			</div>
			
			<?php echo $pages_arrows_html; ?>
