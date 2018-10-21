<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li><a href="/doctrine/fits">Doctrines</a></li>
			<li class="active">Fit</li>
		</ol>
	</div>
	
	<div class="row">

		<header class="page-header col-lg-10 col-lg-offset-1 col-md-12 col-md-offset-0 aligncenter">
			
			<?php
			if( $info['status'] == 'Official' )
			{ ?>
				<div class="col-sm-5">
					<h2 class="page-title full-page-title"><img src="/media/image/logo/brandmark_purple_512px_transparent.png" width="64px" height="64px">&nbsp;Official&nbsp;Fit:</h2>
				</div>
				<div class="col-sm-7 alignleft">
					<h2 class="page-title full-page-title">
						<?php echo $info['fitName']; ?>
					</h2>
				</div>
			<?php
			}
			elseif( $info['status'] == 'Retired' )
			{ ?>
				<div class="col-sm-5">
					<h2 class="page-title full-page-title">Retired&nbsp;Fit:</h2>
				</div>
				<div class="col-sm-7 alignleft">
					<h2 class="page-title full-page-title">
						<?php echo $info['fitName']; ?>
					</h2>
				</div>
			<?php
			}
			else
			{ ?>
				<h2 class="page-title full-page-title">
					<?php echo $info['fitName']; ?>
				</h2>
			<?php
			} ?>

		</header>
		
		<article class="entry col-lg-10 col-lg-offset-1 col-md-12 col-md-offset-0">
			
			<div class="col-sm-12">
				<h3>Description</h3>
				<?php
				if( $info['fitDescription'] == "<p>Description or Explanation of Fit</p>\r\n" )
				{
					echo '<p><i>No description provided.</i></p>';
				}
				else
				{
					echo $info['fitDescription'];
				} ?>
				<br>
			</div>
			
			<div class="col-sm-12">
				<div class="col-md-6">
					<div class="row">
						<table class="table table-bordered">
							<tbody>
								<tr>
									<th>Ship Name</th>
									<td><?php echo $info['shipName']; ?></td>
								</tr>
								<tr>
									<th>Role</th>
									<td><?php echo $info['fitRole']; ?></td>
								</tr>
								<tr>
									<th>Created by</th>
									<td><?php
									echo '<a href="/activity/FC/'.$info['userID'].'">';
									echo $info['username'] .'</a>';
									?></td>
								</tr>
								<tr>
									<th>Created on</th>
									<td><?php echo date("F jS, Y",strtotime($info['date'])); ?></td>
								</tr>
								<tr>
									<th>Last edited</th>
									<td><?php echo date("F jS, Y",strtotime($info['lastEdited'])); ?></td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="col-md-12 col-md-offset-0 col-sm-8 col-sm-offset-2">
						<button type="button" class="btn btn-primary btn-block" data-toggle="modal" data-target="#EFT">Export Fit</button>
						<br>
						<a href="/doctrine/fleets?ships_fitID=<?php echo $info['fitID']; ?>" class="btn btn-primary btn-block"><i class="fa fa-search fa-fw" aria-hidden="true"></i>&nbsp;View all Doctrines using this Fit</a>
					</div>
						<div hidden class="col-sm-12" id="fc_options">
							<h3>Spectre Fleet FC Options</h3>
						</div>
						<div hidden id="can_modify_fit">
							<div class="col-sm-6">
								<a href="/doctrine/edit_fits/<?php echo $info['fitID']; ?>" class="btn btn-info btn-block">Edit Fit</a>
								<br>
							</div>
							<div class="col-sm-6"><?php
								echo form_open('doctrine/retire_fit');
								echo form_hidden('fitID', $info['fitID']);
								?>
								<input type="submit" value="Retire Fit" name="Retire" class="btn btn-danger btn-block">
								</form>
								<br>
							</div>
						</div>
						
							<div hidden class="col-md-12 col-md-offset-0 col-sm-8 col-sm-offset-2" id="status_public"><?php
								echo form_open('doctrine/make_fit_official');
								echo form_hidden('fitID', $info['fitID']);
								?>
								<input type="submit" value="Make Fit Official" name="MakeOfficial" class="btn btn-warning btn-block">
								</form>
								<br>
							</div>
							
							<div hidden class="col-md-12 col-md-offset-0 col-sm-8 col-sm-offset-2" id="status_official"><?php
								echo form_open('doctrine/make_fit_public');
								echo form_hidden('fitID', $info['fitID']);
								?>
								<input type="submit" value="Revoke Official Status" name="RevokeOfficial" class="btn btn-warning btn-block">
								</form>
								<br>
							</div>
							
						<div hidden id="can_have_fits">
							<div class="col-sm-6">
								<a href="/doctrine/new_fit" class="btn btn-primary btn-block">Create New Fit</a>
							</div>
							<div class="col-sm-6">
								<a href="/doctrine/manage_fits" class="btn btn-primary btn-block">View all your Fits</a>
								<br>
							</div>
						</div>
				</div>
				
				<div class="col-md-6 col-sm-12">
					<div class="fitting_window">
						<img class="fitting_background" src="https://imageserver.eveonline.com/Render/<?php echo $info['shipID']; ?>_256.png">
						<img class="fitting_ring" src="/media/image/doctrine/fitting_ring.png">
						
						<?php
						
						foreach( $fit_items['slots'] as $slotID => $item )
						{
							if( $item['moduleID'] == NULL )
							{
								echo '<img class="fitting_slot fitting_slot_'.$slotID.'" src="/media/image/doctrine/fitting_module.png">';
							}
							else
							{
								echo '<img class="fitting_slot fitting_slot_'.$slotID.'" src="/media/image/doctrine/fitting_module.png">';
								
								echo '<img class="fitting_icon fitting_icon_'.$slotID.'" src="https://imageserver.eveonline.com/Type/'.$item['moduleID'].'_32.png" data-toggle="tooltip" data-placement="top" title="'.$item['moduleName'].'">';
							}
							if( array_key_exists( 'chargeID', $item ) && $item['chargeID'] !== NULL )
							{
								echo '<img class="fitting_slot fitting_charge_slot_'.$slotID.'" src="/media/image/doctrine/fitting_charge.png">';
								
								echo '<img class="fitting_charge_icon fitting_charge_'.$slotID.'" src="https://imageserver.eveonline.com/Type/'.$item['chargeID'].'_32.png" data-toggle="tooltip" data-placement="top" title="'.$item['chargeName'].'">';
							}
						} ?>
						
					</div>
				</div>
			</div>
			
			<div class="col-sm-12">
				<div class="col-sm-6">
					<h3>Drones</h3>
					<?php
					$drones = $fit_items['drones'];
					if( empty($drones) )
					{
						echo '<p><i>Empty drone bay.</i></p>';
					}
					else
					{ ?>
					<div class="table-responsive">
						<table class="table table-bordered">
							<tbody>
								<tr>
									<th class="aligncenter">Item</th>
									<th>Name</th>
								</tr>
								<?php
								foreach( $drones as $d )
								{
									$droneName = ( $d['droneName'] == NULL ) ? $d['droneID'] : $d['droneName'];
									echo '<tr>';
										echo '<td class="aligncenter" style="white-space:nowrap;"><img src="https://imageserver.eveonline.com/Type/'.$d['droneID'].'_32.png" data-toggle="tooltip" data-placement="top" title="'.$droneName.'"> x'.$d['droneCount'].'</td>';
										echo '<td style="vertical-align:middle;">'.$droneName.'</td>';
									echo '</tr>';
								} ?>
							</tbody>
						</table>
					</div><?php
					} ?>
					
					<?php
					$removed = $fit_items['removed'];
					if( !empty($removed) )
					{ ?>
						<h3>Legacy Fit</h3>
						<p>The following items were removed from their slots due to current ship rack sizes</p>
						<div class="table-responsive">
							<table class="table table-bordered">
								<tbody>
									<tr>
										<th class="aligncenter">Item</th>
										<th>Name</th>
									</tr>
									<?php
									foreach( $removed as $r )
									{
										$cargoName = ( $r['cargoName'] == NULL ) ? $r['cargoID'] : $r['cargoName'];
										echo '<tr>';
											echo '<td class="aligncenter" style="white-space:nowrap;"><img src="https://imageserver.eveonline.com/Type/'.$r['cargoID'].'_32.png" data-toggle="tooltip" data-placement="top" title="'.$cargoName.'"> x'.$r['cargoCount'].'</td>';
											echo '<td style="vertical-align:middle;">'.$cargoName.'</td>';
										echo '</tr>';
									} ?>
								</tbody>
							</table>
						</div><?php
					} ?>
				</div>
				<div class="col-sm-6">
					<h3>Cargo</h3>
					<?php
					$cargo = $fit_items['cargo'];
					if( empty($cargo) )
					{
						echo '<p><i>Empty cargo bay.</i></p>';
					}
					else
					{ ?>
					<div class="table-responsive">
						<table class="table table-bordered">
							<tbody>
								<tr>
									<th class="aligncenter">Item</th>
									<th>Name</th>
								</tr>
								<?php
								foreach( $cargo as $c )
								{
									$cargoName = ( $c['cargoName'] == NULL ) ? $c['cargoID'] : $c['cargoName'];
									echo '<tr>';
										echo '<td class="aligncenter" style="white-space:nowrap;"><img src="https://imageserver.eveonline.com/Type/'.$c['cargoID'].'_32.png" data-toggle="tooltip" data-placement="top" title="'.$cargoName.'"> x'.$c['cargoCount'].'</td>';
										echo '<td style="vertical-align:middle;">'.$cargoName.'</td>';
									echo '</tr>';
								} ?>
							</tbody>
						</table>
					</div><?php
					}
					/*
                    $size = 424;
                    $icon_slot = 36;
                    $icon_module = 32;
                    $icon_charge = 24;
                    $center = $size/2;
                    $radius_outer = 186;
                    $radius_inner = 150;
                    $spacing = 10;
                    $start = 305;
					
                    $position = $start;
                    
					echo '<style type="text/css">';
                    for( $n = 0; $n < 32; $n++ )
                    {
                        if( $n == 8 )    // FIRST_MID_SLOT
                        {
                            $position = 35;
                        }
                        elseif( $n == 16 )    // FIRST_HIGH_SLOT
                        {
                            $position = 55;
                        }
                        elseif( $n == 24 )    // FIRST_RIG_SLOT
                        {
                            $position = 145;
                        }
                        
                        $rotation = (90-$position);
                        echo '
.fitting_slot_'.$n;
						if( $n <= 23 )	// LAST_HIGH_SLOT
						{
							echo ', .fitting_charge_slot_'.$n;
						}
echo ' {
    transform: rotate('.$rotation.'deg);
}';

                        $left = ($center+$radius_outer*cos(deg2rad($position))-$icon_slot/2);
                        $top = ($center-$radius_outer*sin(deg2rad($position))-$icon_slot/2);
                        echo '
.fitting_slot_'.$n.' {
    left: '.$left.'px;
    top: '.$top.'px;
}';

                        $left = ($center+$radius_outer*cos(deg2rad($position))-$icon_module/2);
                        $top = ($center-$radius_outer*sin(deg2rad($position))-$icon_module/2);
                        echo '
.fitting_icon_'.$n.' {
    left: '.$left.'px;
    top: '.$top.'px;
}';

						if( $n <= 23 )	// LAST_HIGH_SLOT
						{
							$left = ($center+$radius_inner*cos(deg2rad($position))-$icon_slot/2);
							$top = ($center-$radius_inner*sin(deg2rad($position))-$icon_slot/2);
							echo '
.fitting_charge_slot_'.$n.' {
    left: '.$left.'px;
    top: '.$top.'px;
}';

							$left = ($center+$radius_inner*cos(deg2rad($position))-$icon_charge/2);
							$top = ($center-$radius_inner*sin(deg2rad($position))-$icon_charge/2);
							echo '
.fitting_charge_'.$n.' {
    left: '.$left.'px;
    top: '.$top.'px;
}';
						}

                        if( $n < 16 )    // FIRST_HIGH_SLOT, vertical top
                        {
                            $position -= $spacing;    // clockwise
                        }
                        else
                        {
                            $position += $spacing;    // anti-clockwise
                            // subsystems now want to go clockwise in 2018?
                        }
                    }
                    echo '</style>';
					*/
                    ?>
				</div>
			</div>
			
			<script type="text/javascript">fit_id = <?php echo $info['fitID']; ?>;</script>
			<script src="/js/fit_permissions.js"></script>

		</article>
		
	</div>

</div><!--/#content-->