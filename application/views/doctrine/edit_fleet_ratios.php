
			<h2>Edit Fleet Ratios</h2>
			<div class="col-sm-12">
				<h3>Doctrine: <?php echo $fleet_info['fleetName']; ?></h3>
				<p>Return to <a href="/doctrine/fleet/<?php echo $fleet_info['fleetID']; ?>">view doctrine here</a>.</p>
			</div>
			
			<?php echo form_open('doctrine/edit_fleet_ratios'); ?>
			
			<div class="col-sm-12">
				<h3>Ships</h3>
				<div class="table-responsive">
					<table class="table table-hover table-bordered">
						<thead>
						<tr>
							<th class="aligncenter">Status</th>
							<th class="aligncenter" colspan="2">Ship</th>
							<th class="aligncenter col-sm-1">Ratio</th>
							<th class="aligncenter">Role</th>
							<th class="aligncenter">Name</th>
						</tr>
						</thead>
						<tbody>
							<?php foreach( $ships_info as $fitID => $ship )
							{
								$info = $ship['info'];
								$ratio = $ship['ratio'];
								echo '<tr>';
									if( $info['status'] == 'Official' )
									{
										echo '<td class="doctrine-fit">';
										echo '<a href="/doctrine/fit/'.$fitID.'"><img class="img-rounded" data-toggle="tooltip" data-placement="top" title="Official Fit" src="/media/image/logo/brandmark_purple_512px_transparent.png" width="64px" height="64px" style="margin-bottom:0px;"></a>';
									}
									elseif( $info['status'] == 'Retired' )
									{
									echo '<td class="doctrine-fit doctrine-fit-text"><a href="/doctrine/fit/'.$fitID.'">';
										echo 'Retired</a>';
									}
									else
									{
										echo '<td>';
									}
									echo '</td>';
									echo '<td class="doctrine-fit"><a href="/doctrine/fit/'.$fitID.'">';
										echo '<img class="img-rounded" data-toggle="tooltip" data-placement="top" title="'.$info['shipName'].'" src="https://imageserver.eveonline.com/Type/'.$info['shipID'].'_64.png" style="margin-bottom:0px;">';
									echo '</a></td>';
									echo '<td class="doctrine-fit doctrine-fit-text"><a href="/doctrine/fit/'.$fitID.'">';
										echo $info['shipName'];
									echo '</a></td>';
									echo '<td class="doctrine-fit doctrine-fit-text'.((form_error('ratios[]')!=NULL)?' has-error':'').'">';
										echo '<input type="number" min="1" step="1" name="ratios['.$fitID.']" class="form-control" style="margin-top:15px;" value="'.set_value( 'ratios['.$fitID.']', $ratio ).'">';
									echo '</td>';
									echo '<td class="doctrine-fit doctrine-fit-text"><a href="/doctrine/fit/'.$fitID.'">';
										echo $info['fitRole'];
									echo '</a></td>';
									echo '<td class="doctrine-fit doctrine-fit-text"><a href="/doctrine/fit/'.$fitID.'">';
										echo $info['fitName'];
									echo '</a></td>';
								echo '</tr>';
							} ?>
						</tbody>
					</table>
				</div>
			</div>
				
				&nbsp;<br>
				
				<div class="col-sm-6 col-sm-offset-3 ui-input">
					<?php
					echo form_hidden('fleetID', $fleet_info['fleetID']);
					?>
					<input type="submit" name="submit" value="Edit Fleet Ratios" class="btn btn-primary btn-block">
				</div>
			</form>
			
			&nbsp;<br>
			&nbsp;<br>
			
			<div class="col-sm-6 col-sm-offset-3 ui-input">
			<?php
				echo form_open('doctrine/edit_fleet_ratios');
				echo form_hidden('fleetID', $fleet_info['fleetID']);
				?>
				<input type="submit" value="Reset proposed changes" name="Reset proposed changes" class="btn btn-primary btn-block">
				</form>
			</div>
			
			<div class="col-sm-12">
				<?php
				if( validation_errors() != '' )
				{
					echo '<h3>Errors</h3>';
				}
				echo validation_errors(); ?>
			</div>
