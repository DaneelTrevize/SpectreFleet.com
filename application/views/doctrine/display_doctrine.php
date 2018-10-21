<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li><a href="/doctrine/fleets">Doctrines</a></li>
			<li class="active">Fleet</li>
		</ol>
	</div>
	
	<div class="row">

		<header class="page-header col-md-10 col-md-offset-1 aligncenter">
			
			<?php
			if( $fleet_info['status'] == 'Official' )
			{ ?>
				<div class="col-sm-5">
					<h2 class="page-title full-page-title"><img src="/media/image/logo/brandmark_purple_512px_transparent.png" width="64px" height="64px">&nbsp;Official&nbsp;Doctrine:</h2>
				</div>
				<div class="col-sm-7 alignleft">
					<h2 class="page-title full-page-title">
						<?php echo $fleet_info['fleetName']; ?>
					</h2>
				</div>
			<?php
			}
			elseif( $fleet_info['status'] == 'Retired' )
			{ ?>
				<div class="col-sm-5">
					<h2 class="page-title full-page-title">Retired&nbsp;Doctrine:</h2>
				</div>
				<div class="col-sm-7 alignleft">
					<h2 class="page-title full-page-title">
						<?php echo $fleet_info['fleetName']; ?>
					</h2>
				</div>
			<?php
			}
			else
			{ ?>
				<h2 class="page-title full-page-title">
					<?php echo $fleet_info['fleetName']; ?>
				</h2>
			<?php
			} ?>

		</header>
		
		<article class="entry col-md-10 col-md-offset-1">
			
			<div class="col-sm-12">
				<h3>Description</h3>
				<?php
				if( $fleet_info['fleetDescription'] == "<p>Description or Explanation of Fleet</p>\r\n" )
				{
					echo '<p><i>No description provided.</i></p>';
				}
				else
				{
					echo $fleet_info['fleetDescription'];
				} ?>
				<br>
			</div>
			
			<div class="col-sm-12">
				<h3>Ships</h3>
				<div class="table-responsive">
					<table class="table table-hover table-bordered">
						<thead>
						<tr>
							<th class="aligncenter">Status</th>
							<th class="aligncenter" colspan="2">Ship</th>
							<?php
							if( $total_ratio > count($ships_info) )
							{
								echo '<th class="aligncenter">Ratio</th>';
							} ?>
							<th class="aligncenter">Role</th>
							<th class="aligncenter">Name</th>
						</tr>
						</thead>
						<tbody>
							<?php foreach( $ships_info as $ship )
							{
								$info = $ship['info'];
								$ratio = $ship['ratio'];
								echo '<tr>';
									if( $info['status'] == 'Official' )
									{
										echo '<td class="doctrine-fit">';
										echo '<a href="/doctrine/fit/'.$info['fitID'].'"><img class="img-rounded" data-toggle="tooltip" data-placement="top" title="Official Fit" src="/media/image/logo/brandmark_purple_512px_transparent.png" width="64px" height="64px"></a>';
									}
									elseif( $info['status'] == 'Retired' )
									{
									echo '<td class="doctrine-fit doctrine-fit-text"><a href="/doctrine/fit/'.$info['fitID'].'">';
										echo 'Retired</a>';
									}
									else
									{
										echo '<td>';
									}
									echo '</td>';
									echo '<td class="doctrine-fit"><a href="/doctrine/fit/'.$info['fitID'].'">';
										echo '<img class="img-rounded" data-toggle="tooltip" data-placement="top" title="'.$info['shipName'].'" src="https://imageserver.eveonline.com/Type/'.$info['shipID'].'_64.png">';
									echo '</a></td>';
									echo '<td class="doctrine-fit doctrine-fit-text"><a href="/doctrine/fit/'.$info['fitID'].'">';
										echo $info['shipName'];
									echo '</a></td>';
									if( $total_ratio > count($ships_info) )
									{
										$percentage = ($ratio / $largest_ratio) * 100;
										echo '<td class="doctrine-fit"><a href="/doctrine/fit/'.$info['fitID'].'"><span class="ratio_bg" data-toggle="tooltip" data-placement="top" title="'.$ratio.':'.$total_ratio.'"><span class="ratio_bar" style="width: '.$percentage.'%"></span></span></a></td>';
									}
									echo '<td class="doctrine-fit doctrine-fit-text"><a href="/doctrine/fit/'.$info['fitID'].'">';
										echo $info['fitRole'];
									echo '</a></td>';
									echo '<td class="doctrine-fit doctrine-fit-text"><a href="/doctrine/fit/'.$info['fitID'].'">';
										echo $info['fitName'];
									echo '</a></td>';
								echo '</tr>';
							} ?>
						</tbody>
					</table>
				</div>
			</div>
		
			<div class="col-sm-6">
				<h3>Details</h3>
				<div class="table-responsive">
					<table class="table table-bordered">
						<tbody>
							<tr>
								<th>Combat Type</th>
								<td><?php echo $fleet_info['fleetType']; ?></td>
							</tr>
							<tr>
								<th>Created by</th>
								<td><?php
								echo '<a href="/activity/FC/'.$fleet_info['userID'].'">';
								echo $fleet_info['Username'] .'</a>';
								?></td>
							</tr>
							<tr>
								<th>Created on</th>
								<td><?php echo date("F jS, Y",strtotime($fleet_info['date'])); ?></td>
							</tr>
							<tr>
								<th>Last edited</th>
								<td><?php echo date("F jS, Y",strtotime($fleet_info['lastEdited'])); ?></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<?php
			if( $can_modify_fleet || $can_have_fleets )
			{ ?>
			<div class="col-sm-6">
				<h3>Spectre Fleet FC Options</h3>
				<?php
				if( $can_modify_fleet )
				{ ?>
					<div class="col-sm-4">
						<a href="/doctrine/edit_fleets/<?php echo $fleet_info['fleetID']; ?>" class="btn btn-info btn-block">Edit Doctrine</a>
						<br>
					</div>
					<div class="col-sm-4"><?php
						echo form_open('doctrine/edit_fleet_ratios');
						echo form_hidden('fleetID', $fleet_info['fleetID']);
						?>
						<input type="submit" value="Edit Ratios" name="Retire" class="btn btn-info btn-block">
						</form>
						<br>
					</div>
					<div class="col-sm-4"><?php
						echo form_open('doctrine/retire_fleet');
						echo form_hidden('fleetID', $fleet_info['fleetID']);
						?>
						<input type="submit" value="Retire Doctrine" name="Retire" class="btn btn-danger btn-block">
						</form>
						<br>
					</div><?php
				}
				if( $can_have_fleets )
				{ ?>
					<div class="col-sm-6">
						<a href="/doctrine/new_fleet" class="btn btn-primary btn-block">Create New Doctrine</a>
					</div>
					<div class="col-sm-6">
						<a href="/doctrine/manage_doctrines" class="btn btn-primary btn-block">View all your Doctrines</a>
					</div>
				<?php
				} ?>
			</div>
			<?php
			} ?>

		</article>

	</div>
		
</div><!--/#content-->