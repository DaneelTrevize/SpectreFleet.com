<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li class="active">Directional Scan Results</li>
		</ol>
	</div>
	
	<div class="row">
		
		<div class="col-lg-12">
			<div class="row">
				<div class="col-lg-4 col-lg-offset-1 col-md-4 col-md-offset-1 col-sm-6 col-sm-offset-3 col-xs-10 col-xs-offset-1 aligncenter">
					<a href="/dscan" class="btn btn-primary btn-block btn-lg">Prepare a new Directional Scan&nbsp;<i class="fa fa-wifi fa-fw" aria-hidden="true"></i></a>
					<span class="visible-sm-inline visible-xs-inline"><br></span>
				</div>
				<div class="col-lg-4 col-lg-offset-2 col-md-6 col-md-offset-1 col-sm-10 col-sm-offset-1">
					<div class="row">
						<div hidden class="col-sm-6 col-sm-offset-0 col-xs-10 col-xs-offset-1" id="dscan_grid_toggle">
							<a class="btn btn-primary btn-block" href="#content"><i class="fa fa-window-restore fa-fw" aria-hidden="true"></i>&nbsp;On & Off-Grid views</a>
						</div>
						<div class="col-sm-6 col-sm-offset-0 col-xs-10 col-xs-offset-1" id="dscan_combined_toggle">
							<a class="btn btn-primary btn-block" href="#content"><i class="fa fa-table fa-fw" aria-hidden="true"></i>&nbsp;Combined Grid view</a>
						</div>
						<div class="col-sm-6 col-sm-offset-0 col-xs-10 col-xs-offset-1" id="dscan_simplified_toggle">
							<a class="btn btn-primary btn-block" href="#content"><i class="fa fa-columns fa-fw" aria-hidden="true"></i>&nbsp;Simplified view</a>
						</div>
						<div class="col-sm-12 col-sm-offset-0 col-xs-10 col-xs-offset-1 dscan_images_toggle aligncenter">
							<div id="dscan_images_hide">
								<i class="fa fa-eye-slash fa-fw" aria-hidden="true"></i>&nbsp;<a href="#content">Hide Group Counts &amp; Type Images</a>
							</div>
							<div hidden id="dscan_images_show">
								<i class="fa fa-eye fa-fw" aria-hidden="true"></i>&nbsp;<a href="#content">Show Group Counts &amp; Type Images</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<header class="page-header thinner-headers col-sm-12">
			
			<div class="row">
				<div class="col-md-6 col-sm-5 aligncenter">
					<h3>
						<?php
						$systemName = $info['solarSystemName'];
						echo '<i class="fa fa-map-o fa-fw" aria-hidden="true"></i>&nbsp;Location: ';
						if( $systemName == NULL )
						{
							echo 'Unknown system';
						}
						else
						{
							echo link_solar_system( $systemName );
						}
						?>
					</h3>
				</div>
				<div class="col-md-6 col-sm-7 aligncenter">
					<h3>
						<?php
						$dscan_datetime = DateTime::createFromFormat( 'Y-m-d H:i:s.ue', $info['datetime'] );
						echo '<i class="fa fa-clock-o fa-fw" aria-hidden="true"></i>&nbsp;Scanned: '.$dscan_datetime->format( 'F jS \a\t H:i:s' );
						?>
					</h3>
				</div>
			</div>
		</header>
		
		<article class="col-lg-12">
		
			<div id="ongrid">
			
			<!-- Screens between 1200px and 1440px wide will have issues with this layout -->
			
			<div class="row">
				<div class="col-sm-4">
					<div class="row pull-left">
						<h2>On-Grid:&nbsp;Σ&nbsp;<span id="ongrid_ships_count">&nbsp;</span> ships</h2>
					</div>
				</div>
				<div class="col-sm-4 aligncenter" id="dscan_offgrid_skip">
					<i class="fa fa-angle-double-down fa-fw" aria-hidden="true"></i>&nbsp;<a href="#offgrid">Skip to Off-Grid results</a>&nbsp;<i class="fa fa-angle-double-down fa-fw" aria-hidden="true"></i>
				</div>
			</div>
			
			<div class="row">
				<?php
				$column_for_clearfix = 1;
				
				$ongrid_prefix_classes = array( 'DPS', 'Logistics', 'Tackle', 'EWar & Support', 'Supers', 'Capitals', 'Drones and Fighters' );
				// Industrial, Non-combat
				$ongrid_suffix_classes = array( 'Celestial', 'Deployable', 'NPC', 'Other' );
				
				foreach( $ongrid_prefix_classes as $sf_class_name )
				{
					if( array_key_exists( $sf_class_name, $ongrid ) )
					{
						echo $ongrid[$sf_class_name];
						unset( $ongrid[$sf_class_name] );
						/*
						if( $column_for_clearfix % 3 == 0 )
						{
							echo '<div class="clearfix visible-lg"></div>';
						}
						if( $column_for_clearfix % 2 == 0 )
						{
							echo '<div class="clearfix visible-md"></div>';
						}*/
						$column_for_clearfix++;
					}
				}
				
				foreach( $ongrid as $sf_class_name => $ongrid_class_html )
				{
					if( !in_array( $sf_class_name, $ongrid_suffix_classes ) )
					{
						echo $ongrid_class_html;
						/*
						if( $column_for_clearfix % 3 == 0 )
						{
							echo '<div class="clearfix visible-lg"></div>';
						}
						if( $column_for_clearfix % 2 == 0 )
						{
							echo '<div class="clearfix visible-md"></div>';
						}*/
						$column_for_clearfix++;
					}
				}
				if( $column_for_clearfix == 1 ) {
					// No tables output
					echo '<div class="col-sm-offset-1"><p>No results found for combat ship categories.</p></div>';
				}
				?>
			</div>
			
			<div class="row">
				<div class="col-xs-8 col-xs-offset-2">
					<hr>
					<br>
				</div>
			</div>
			
			<div class="row">
				<?php
				$column_for_clearfix = 1;
				foreach( $ongrid_suffix_classes as $sf_class_name )
				{
					if( array_key_exists( $sf_class_name, $ongrid ) )
					{
						echo $ongrid[$sf_class_name];
						/*
						if( $column_for_clearfix % 3 == 0 )
						{
							echo '<div class="clearfix visible-lg"></div>';
						}
						if( $column_for_clearfix % 2 == 0 )
						{
							echo '<div class="clearfix visible-md"></div>';
						}*/
						$column_for_clearfix++;
					}
				} ?>
			</div>
			
			</div>
			<div id="offgrid">
			
			<hr>
			
			<div class="row">
				<div class="col-sm-4 col-sm-offset-4 col-xs-8 col-xs-offset-2 aligncenter">
					<i class="fa fa-angle-double-up fa-fw" aria-hidden="true"></i>&nbsp;<a href="#content">Skip to On-Grid results</a>&nbsp;<i class="fa fa-angle-double-up fa-fw" aria-hidden="true"></i>
				</div>
			</div>
			
			<div class="row">
				<div class="pull-left">
					<h2>Off-Grid:&nbsp;Σ&nbsp;<span id="offgrid_ships_count">&nbsp;</span> ships</h2>
				</div>
			</div>
			
			<div class="row">
				<?php
				$column_for_clearfix = 1;
				
				$offgrid_prefix_classes = array( 'DPS', 'Logistics', 'Tackle', 'EWar & Support', 'Supers', 'Capitals', 'Drones and Fighters' );
				// Industrial, Non-combat
				$offgrid_suffix_classes = array( 'Celestial', 'Deployable', 'NPC', 'Other' );
				
				foreach( $offgrid_prefix_classes as $sf_class_name )
				{
					if( array_key_exists( $sf_class_name, $offgrid ) )
					{
						echo $offgrid[$sf_class_name];
						unset( $offgrid[$sf_class_name] );
						$column_for_clearfix++;
					}
				}
				
				foreach( $offgrid as $sf_class_name => $offgrid_class_html )
				{
					if( !in_array( $sf_class_name, $offgrid_suffix_classes ) )
					{
						echo $offgrid_class_html;
						$column_for_clearfix++;
					}
				}
				if( $column_for_clearfix == 1 ) {
					// No tables output
					echo '<div class="col-sm-offset-1"><p>No results found for combat ship categories.</p></div>';
				} ?>
			</div>
			
			<div class="row">
				<div class="col-xs-8 col-xs-offset-2">
					<hr>
					<br>
				</div>
			</div>
			
			<div class="row">
				<?php
				$column_for_clearfix = 1;
				foreach( $offgrid_suffix_classes as $sf_class_name )
				{
					if( array_key_exists( $sf_class_name, $offgrid ) )
					{
						echo $offgrid[$sf_class_name];
						$column_for_clearfix++;
					}
				} ?>
			</div>
			
			</div>
			<div hidden id="combined">
			
			<div class="row">
				<div class="pull-left">
					<h2>Combined&nbsp;results:&nbsp;Σ&nbsp;<span id="combined_ships_count">&nbsp;</span> ships</h2>
				</div>
			</div>
			
			</div>
			<div hidden id="simplified">
			
			<div class="row">
				<div class="pull-left">
					<h2>Simplified&nbsp;results:&nbsp;Σ&nbsp;<span id="simplified_ships_count">&nbsp;</span> ships</h2>
				</div>
			</div>
			
			</div>
			
			<div class="row">
				<div class="col-sm-10 col-sm-offset-1 aligncenter">
					<p>CCP's EVE Online &trade; Overview icons <a href="https://www.flickr.com/photos/12832008@N04/17919565904/">reproduced by Rixx Javix</a></p>
				</div>
			</div>
			
		</article>
		
		<script src="/js/tools_dscan.js"></script>
		
	</div>

</div><!--/#content-->