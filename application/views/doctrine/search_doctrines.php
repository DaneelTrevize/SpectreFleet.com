<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li>Doctrines</li>
			<li class="active">Search Doctrines</li>
		</ol>
	</div>
	
	<div class="row">

		<header class="page-header col-md-10 col-md-offset-1">

			<h2 class="page-title full-page-title">
				Fleet Doctrines
			</h2>

		</header>
		
		<article class="col-md-10 col-md-offset-1">

			<div>
				<p>Search simply by FleetID, or use any combination of the filters below:</p>
				<?php echo form_open('doctrine/fleetID'); ?>
				<div class="row">
					<div class="col-md-4 col-md-offset-1 col-sm-6 col-sm-offset-0 ui-input">
						<input type="number" min="1" step="1" name="fleetID" placeholder="FleetID" value="" class="form-control">
					</div>
					<div class="col-md-4 col-md-offset-2 col-sm-6 col-sm-offset-0 ui-input">
						<button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search fa-fw" aria-hidden="true"></i>&nbsp;Search FleetIDs</button>
					</div>
				</div>
				</form>
			</div>
			<hr>
			<div>
				<p>These search filters are case-insensitive, and by default search for the supplied text anywhere within the target field, rather than fully matching the value.</p>
				<h3>Filters</h3>
				<form method="get" accept-charset="utf-8" action="/doctrine/fleets">
				<div class="row">
					<div class="col-md-6">
						<div class="ui-input">
							<span>Created By</span>
							<input type="text" name="createdBy" placeholder="FC Name" value="<?php if( isset($createdBy) ) echo $createdBy; ?>" class="form-control">
						</div>
						<div class="ui-input">
							<span>Doctrine Name</span>
							<input type="text" name="fleetName" placeholder="Doctrine Name" value="<?php if( isset($fleetName) ) echo $fleetName; ?>" class="form-control">
						</div>
					</div>
					<div class="col-md-6">
						<div class="ui-input">
							<span>Description Search</span>
							<input type="text" name="fleetDescription" placeholder="Text Segment" value="<?php if( isset($fleetDescription) ) echo $fleetDescription; ?>" class="form-control">
						</div>
						<div class="ui-input">
							<span>Combat Type</span>
							<select name="fleetType" class="form-control">
								<option value="">Fleet Type</option>
								<?php
								foreach( $roles as $category_name => $category )
								{
									echo '<optgroup label="'.$category_name."\">\n";
									foreach( $category as $category_value => $category_name )
									{
										echo '<option value="'.$category_value.'"';
										if( isset($fleetType) && $fleetType == $category_value) echo ' selected';
										echo '>'.$category_name."</option>\n";
									}
									echo "</optgroup>\n";
								}
								?>
							</select>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-6 ui-input">
						<span>Ship Hull</span>
						<input type="text" name="shipName" placeholder="Ship Hull Name" value="<?php if( isset($shipName) ) echo $shipName; ?>" class="form-control">
					</div>
					<div class="col-sm-6 ui-input">
						<span>Ship Hull Name contains value, or fully matches value?</span>
						<div class="col-lg-8 col-lg-offset-2 col-sm-10 col-sm-offset-1">
							<input type="checkbox" name="full_shipName" value="on"<?php if( isset($full_shipName) ) echo ' checked'; ?> data-toggle="toggle" data-onstyle="primary" data-on="Full Name matching On" data-offstyle="default" data-off="Full Name matching Off" data-width="100%">
						</div>
					</div>
				</div>
				<?php echo $results_controls_html; ?>
				<div class="row">
					<div class="ui-input col-sm-6 col-sm-offset-3 col-xs-10 col-xs-offset-1">
						<button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search fa-fw" aria-hidden="true"></i>&nbsp;Search Doctrines</button>
					</div>
					<div class="ui-input col-md-2 col-md-offset-1 col-sm-3 col-sm-offset-0 col-xs-10 col-xs-offset-1">
						<a class="btn btn-primary btn-block" href="/doctrine/fleets">Reset Filters</a>
					</div>
				</div>
				</form>
				<br>
				
				<?php
				echo $pages_count_html;
				
				if( $results_on_page > 0 )
				{ ?>
				<div class="col-sm-12">
					<div class="table-responsive" style="min-width:330px;">
						<table class="table table-hover table-bordered">
							<tbody>
								<?php foreach($fleets as $fleet)
								{ ?>
									<tr>
										<td>
											<a style="text-decoration:none;" href="/doctrine/fleet/<?php echo $fleet['fleetID']; ?>">
												<div class="pull-right">FleetID: <?php echo $fleet['fleetID']; ?></div>
												<div class="thtext"><?php echo $fleet['fleetName']; ?></div>
												<div class="pull-right hidden-xs">
													<?php
													foreach($fleet['shipIDs'] as $shipID)
													{
													echo '<img class="small-doctrine-img img-rounded" src="https://imageserver.eveonline.com/Type/'.$shipID.'_32.png">';
													} ?>
												</div>
												<div><?php echo 'Type: '.$fleet['fleetType']; ?></div>
											</a>
											<div class="tftext">
												<?php echo 'Created by '.$fleet['CharacterName'].' on '.date("F jS, Y",strtotime($fleet['date'])); ?>
												<span class="pull-right"><?php echo 'Last edited '.date("F jS, Y",strtotime($fleet['lastEdited'])); ?></span>
											</div>
										</td>
									</tr><?php
								} ?>
							</tbody>
						</table>
					</div>
				</div><?php
				}
				
				echo $pages_arrows_html;
				?>
				
			</div>

		</article>

	</div>

</div><!--/#content-->