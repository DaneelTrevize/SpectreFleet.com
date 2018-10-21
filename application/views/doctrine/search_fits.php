<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li>Doctrines</li>
			<li class="active">Search Fits</li>
		</ol>
	</div>
	
	<div class="row">

		<header class="page-header col-md-10 col-md-offset-1">

			<h2 class="page-title full-page-title">
				Ship Fits
			</h2>

		</header>
		
		<article class="col-md-10 col-md-offset-1">

			<div>
				<p>These search filters are case-insensitive, and by default search for the supplied text anywhere within the target field, rather than fully matching the value.</p>
				<h3>Filters</h3>
				<form method="get" accept-charset="utf-8" action="/doctrine/fits">
				<div class="row">
					<div class="col-md-6">
						<div class="ui-input">
							<span>Created By</span>
							<input type="text" name="createdBy" placeholder="FC Name" value="<?php if( isset($createdBy) ) echo $createdBy; ?>" class="form-control">
						</div>
						<div class="ui-input">
							<span>Fit Name</span>
							<input type="text" name="fitName" placeholder="Fit Name" value="<?php if( isset($fitName) ) echo $fitName; ?>" class="form-control">
						</div>
					</div>
					<div class="col-md-6">
						<div class="ui-input">
							<span>Description Search</span>
							<input type="text" name="fitDescription" placeholder="Text Segment" value="<?php if( isset($fitDescription) ) echo $fitDescription; ?>" class="form-control">
						</div>
						<div class="ui-input">
							<span>Combat Role</span>
							<select name="fitRole" class="form-control">
								<option value="">Fit Role</option>
								<?php
								foreach( $roles as $category_name => $category )
								{
									echo '<optgroup label="'.$category_name."\">\n";
									foreach( $category as $role )
									{
										echo '<option value="'.$role.'"';
										if( isset($fitRole) && $fitRole == $role) echo ' selected';
										echo '>'.$role."</option>\n";
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
				<div class="row">
					<div class="col-sm-6 ui-input">
						<span>Module</span>
						<input type="text" name="moduleName" placeholder="Module Name" value="<?php if( isset($moduleName) ) echo $moduleName; ?>" class="form-control">
					</div>
					<div class="col-sm-6 ui-input">
						<span>Module Name contains value, or fully matches value?</span>
						<div class="col-lg-8 col-lg-offset-2 col-sm-10 col-sm-offset-1">
							<input type="checkbox" name="full_moduleName" value="on"<?php if( isset($full_moduleName) ) echo ' checked'; ?> data-toggle="toggle" data-onstyle="primary" data-on="Full Name matching On" data-offstyle="default" data-off="Full Name matching Off" data-width="100%">
						</div>
					</div>
				</div>
				<br>
				<div class="row">
					<div class="col-sm-6 ui-input">
						<span>Only search Official fits?</span>
						<div class="col-lg-8 col-lg-offset-2 col-sm-10 col-sm-offset-1">
							<input type="checkbox" name="onlyOfficial" value="on"<?php if( isset($onlyOfficial) ) echo ' checked'; ?> data-toggle="toggle" data-onstyle="primary" data-on="Only searching within Official fits" data-offstyle="default" data-off="Searching within all current fits" data-width="100%">
						</div>
					</div>
					<div class="col-sm-6 ui-input">
						<span>Also search Retired fits?</span>
						<div class="col-lg-8 col-lg-offset-2 col-sm-10 col-sm-offset-1">
							<input type="checkbox" name="alsoRetired" value="on"<?php if( isset($alsoRetired) ) echo ' checked'; ?> data-toggle="toggle" data-onstyle="primary" data-on="Searching includes Retired fits" data-offstyle="default" data-off="Searching within current fits" data-width="100%">
						</div>
					</div>
				</div>
				<?php echo $results_controls_html; ?>
				<div class="row">
					<div class="ui-input col-sm-6 col-sm-offset-3 col-xs-10 col-xs-offset-1">
						<button type="submit" class="btn btn-primary btn-block"><i class="fa fa-search fa-fw" aria-hidden="true"></i>&nbsp;Search Fits</button>
					</div>
					<div class="ui-input col-md-2 col-md-offset-1 col-sm-3 col-sm-offset-0 col-xs-10 col-xs-offset-1">
						<a class="btn btn-primary btn-block" href="/doctrine/fits">Reset Filters</a>
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
								<?php foreach($fits as $fit)
								{ ?>
									<tr>
										<td style="vertical-align:middle;width:96px;">
											<a style="text-decoration:none;" href="/doctrine/fit/<?php echo $fit['fitID']; ?>">
											<?php
												echo '<img class="timg img-rounded" data-toggle="tooltip" data-placement="top" title="'.$fit['shipName'].'" src="https://imageserver.eveonline.com/Type/'.$fit['shipID'].'_64.png">';
											?>
											</a>
										</td>
										<td>
											<a style="text-decoration:none;" href="/doctrine/fit/<?php echo $fit['fitID']; ?>">
												<div class="pull-right">FitID: <?php echo $fit['fitID']; ?></div>
												<div class="thtext"><?php echo $fit['fitName']; ?></div>
												<div class="pull-right"><?php
												if( $fit['status'] == 'Official' )
												{
													echo '<img src="/media/image/logo/favicon_purple_32px.png" width="32px" height="32px" style="margin-bottom: 0px">&nbsp;Official Fit';
												}
												elseif( $fit['status'] == 'Retired' )
												{
													echo 'Retired';
												} ?>
												</div>
												<div><?php echo 'Combat Role: '.$fit['fitRole']; ?></div>
											</a>
											<div class="tftext">
												<?php echo 'Created by '.$fit['CharacterName'].' on '.date("F jS, Y",strtotime($fit['date'])); ?>
												<span class="pull-right"><?php echo 'Last edited '.date("F jS, Y",strtotime($fit['lastEdited'])); ?></span>
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