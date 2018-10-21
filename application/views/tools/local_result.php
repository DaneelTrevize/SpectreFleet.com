<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li class="active">Local Scan Results</li>
		</ol>
	</div>
	
	<div class="row">
		
		<div class="col-lg-12">
			<div class="row">
				<div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3 col-xs-10 col-xs-offset-1">
					<a href="/lscan" class="btn btn-primary btn-block">Prepare a new Local Scan&nbsp;<i class="fa fa-list fa-fw" aria-hidden="true"></i></a>
				</div>
			</div>
		</div>
		
		<header class="page-header col-sm-12">
			
			<div class="row">
				<div class="col-md-6 col-sm-5 aligncenter">
					<h3>
						<?php
						$systemName = $info['system'];
						echo '<i class="fa fa-map-o fa-fw" aria-hidden="true"></i>&nbsp;Location: ';
						if( $systemName == NULL || $systemName == '' )
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
		
		<article class="col-md-10 col-md-offset-1">
			
			<div class="col-xs-6 aligncenter">
			 <h3>
				Pilots: <?php echo $affiliations['characters_count']; ?>
			 </h3>
			</div>
			<div class="col-xs-6">
				
				<h3>
					Factions: <?php echo count( $affiliations['faction_ids_counts'] ); ?>
				</h3>
				
				<table class="table table-striped table_valign_m lscan" id="lscan_factions">
					<thead>
						<tr>
							<th class="aligncenter">Pilots</th>
							<th class="aligncenter">Name</th>
							<th class="aligncenter">Corps</th>
						</tr>
					</thead>
					<tbody>
						<?php
						//echo '<pre>'. print_r( $affiliations['faction_names'], TRUE ) .'</pre>';
						foreach( $affiliations['faction_ids_counts'] as $faction_id => $count )
						{
							echo '<tr data-eve-faction-id="'.$faction_id.'">';
							
							echo '<td class="aligncenter">'.$count.'</td>';
							echo '<td>';
							if( array_key_exists( $faction_id, $affiliations['factions_names'] ) )
							{
								echo $affiliations['factions_names'][$faction_id]['factionName'];
							}
							echo '</td>';
							echo '<td class="aligncenter"></td>';
							
							echo '</tr>';
						} ?>
					</tbody>
				</table>
				
			</div>
			
			<div class="col-sm-6">
				
				<h3>
					Alliances: <?php
					$alliances_count = count( $affiliations['alliance_ids_counts'] );
					$corporations_count = count( $affiliations['corporation_ids_counts'] );
					echo $alliances_count; ?>
				</h3>
				
				<table class="table table-striped table_valign_m lscan" id="lscan_alliances">
					<thead>
						<tr>
							<th></th>
							<th class="aligncenter">Pilots</th>
							<th class="aligncenter">Name</th>
							<th class="aligncenter">Corps</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$one_corp_alliance_count = 0;
						foreach( $affiliations['alliance_ids_counts'] as $alliance_id => $count )
						{
							$hidden = ($corporations_count > 500 && $count == 1);
							if( $hidden )
							{
								$one_corp_alliance_count += 1;
								continue;
							}
							
							echo '<tr';
							if( $hidden )
							{
								echo ' hidden';
							}
							echo ' data-eve-alliance-id="'.$alliance_id.'">';
							
							$alliance_name = '';
							if( !$hidden )
							{
								if( array_key_exists( $alliance_id, $affiliations['alliances_names'] ) )
								{
									$alliance_name = $affiliations['alliances_names'][$alliance_id]['name'];
									
									$alliance_link = '<a href="http://evemaps.dotlan.net/alliance/'. str_replace( ' ', '_', $alliance_name ) .'">';
									echo '<td>'.$alliance_link.'<img class="img-rounded" src="https://imageserver.eveonline.com/Alliance/'.$alliance_id.'_32.png"></a></td>';
								}
								else
								{
									echo '<td><img class="img-rounded" src="https://imageserver.eveonline.com/Alliance/'.$alliance_id.'_32.png"></td>';
								}
							}
							else
							{
								echo '<td></td>';
							}
							
							echo '<td class="aligncenter">'.$count.'</td>';
							echo '<td>'.$alliance_name.'</td>';
							echo '<td class="aligncenter"></td>';
							
							echo '</tr>';
						}
						if( $corporations_count > 500 && $one_corp_alliance_count > 0 )
						{
							echo '<tr>';
								echo '<td><i class="fa fa-exclamation-triangle fa-2x fa-fw" aria-hidden="true"></i></td>';
								echo '<td class="aligncenter">'.$one_corp_alliance_count.'</td>';
								echo '<td>High local count, single-corporation alliances unlisted</td>';
								echo '<td class="aligncenter">'.$one_corp_alliance_count.'</td>';
							echo '</tr>';
						}
						echo '<tr id="no_alliance">';
							echo '<td><img class="img-rounded" src="https://imageserver.eveonline.com/Alliance/0_32.png"></td>';
							echo '<td class="aligncenter">'.$affiliations['no_alliance_count'].'</td>';
							echo '<td>No Alliance</td>';
							echo '<td class="aligncenter">'.($corporations_count - count($affiliations['corporation_alliance_map'])).'</td>';
						echo '</tr>';
						?>
					</tbody>
				</table>
				
			</div>
			
			<div class="col-sm-6">
				
				<h3>
					Corporations: <?php
					echo $corporations_count; ?>
				</h3>
				
				<table class="table table-striped table_valign_m lscan" id="lscan_corps">
					<thead>
						<tr>
							<th></th>
							<th class="aligncenter">Pilots</th>
							<th class="aligncenter">Name</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$one_man_corp_count = 0;
						foreach( $affiliations['corporation_ids_counts'] as $corporation_id => $count )
						{
							$hidden = ($corporations_count > 500 && $count == 1);
							if( $hidden )
							{
								$one_man_corp_count += 1;
							}
							
							echo '<tr';
							if( $hidden )
							{
								echo ' hidden';
							}
							echo ' data-eve-corporation-id="'.$corporation_id.'"';
							if( array_key_exists( $corporation_id, $affiliations['corporation_alliance_map'] ) )
							{
								$alliance_id = $affiliations['corporation_alliance_map'][$corporation_id];
								echo ' data-eve-alliance-id="'.$alliance_id.'"';
							}
							if( array_key_exists( $corporation_id, $affiliations['corporation_faction_map'] ) )
							{
								$faction_id = $affiliations['corporation_faction_map'][$corporation_id];
								echo ' data-eve-faction-id="'.$faction_id.'"';
							}
							echo '>';
							
							$corporation_name = '';
							if( !$hidden )
							{
								if( array_key_exists( $corporation_id, $affiliations['corporations_names'] ) )
								{
									$corporation_name = $affiliations['corporations_names'][$corporation_id]['name'];
									
									$corporation_link = '<a href="http://evemaps.dotlan.net/corp/'. str_replace( ' ', '_', $corporation_name ) .'">';
									echo '<td>'.$corporation_link.'<img class="img-rounded" src="https://imageserver.eveonline.com/Corporation/'.$corporation_id.'_32.png"></a></td>';
								}
								else
								{
									echo '<td><img class="img-rounded" src="https://imageserver.eveonline.com/Corporation/'.$corporation_id.'_32.png"></td>';
								}
							}
							else
							{
								echo '<td></td>';
							}
							echo '<td class="aligncenter">'.$count.'</td>';
							echo '<td>'.$corporation_name.'</td>';
							
							echo '</tr>';
						}
						if( $corporations_count > 500 && $one_man_corp_count > 0 )
						{
							echo '<tr>';
								echo '<td><i class="fa fa-exclamation-triangle fa-2x fa-fw" aria-hidden="true"></i></td>';
								echo '<td class="aligncenter">'.$one_man_corp_count.'</td>';
								echo '<td>High local count, single-person-presence corporations unlisted</td>';
							echo '</tr>';
						} ?>
					</tbody>
				</table>
				
			</div>
			
		</article>
		
		<script src="/js/tools_lscan.js"></script>

	</div>

</div><!--/#content-->