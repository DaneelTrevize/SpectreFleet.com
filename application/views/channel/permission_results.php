
			<h2>Channel Configuration Checker Results</h2>
			
			<div class="ui-input col-md-6 col-md-offset-3">
				<a href="/channel/permissions" class="btn btn-primary btn-block">Re-Check Permissions</a>
			</div>
			<br>
			
			<hr>
			
			<h3>'SF Spectre Fleet' Channel Operator lists</h3>
			
			<div class="row">
			<div class="col-sm-6 entry-content">
				<h4>Characters that should be added to the operator list (if they are not already Owners):</h4>
				
				<?php
				$result = array_diff_key( $FCs, $operators );
				//natcasesort( $result );
				?>
				<table class="table table-hover table-bordered">
					<thead>
						<tr>
							<th class="aligncenter">Rank</th>
							<th>Name</th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach( $result as $ID => $character )
						{
							$name = $character['CharacterName'];
							echo '<tr>';
							echo '<td class="alignright">'. $rank_names[$character['Rank']] .'</td>';
							echo '<td>'. $name ."</td>\n";
							echo '</tr>';
						}
						?>
					</tbody>
				</table>
			</div>
		
			<div class="col-sm-6 entry-content">
				<h4>Characters that should be removed from the operator list:</h4>
				
				<table class="table table-striped table_valign_m">
					<thead>
						<tr>
							<th></th>
							<th>Name</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$result = array_diff_key( $operators, $FCs );
						//natcasesort( $result );
						foreach( $result as $ID => $character )
						{
							echo '<tr>';
							echo '<td><img src="https://imageserver.eveonline.com/Character/'.$ID.'_64.jpg" class="img-rounded img40px" title="'.$character.'"></td>';
							echo '<td>'. $character ."</td>\n";
							echo '</tr>';
						}
						?>
					</tbody>
				</table>
			</div>
			</div>
			
			<hr>
			
			<h3>Banned lists</h3>
			
			<?php
			$all_blocked = array();
			if( $Spectre_Fleet_accessors !== FALSE )
			{
				foreach( $Spectre_Fleet_accessors['blocked'] as $ID => $accessor )
				{
					$all_blocked[$ID] = $accessor;
				}
			}
			if( $XUP1_accessors !== FALSE )
			{
				foreach( $XUP1_accessors['blocked'] as $ID => $accessor )
				{
					$all_blocked[$ID] = $accessor;
				}
			}
			if( $XUP2_accessors !== FALSE )
			{
				foreach( $XUP2_accessors['blocked'] as $ID => $accessor )
				{
					$all_blocked[$ID] = $accessor;
				}
			}
			if( $XUP3_accessors !== FALSE )
			{
				foreach( $XUP3_accessors['blocked'] as $ID => $accessor )
				{
					$all_blocked[$ID] = $accessor;
				}
			}
			?>
			
			<div class="row">
				<div class="col-sm-6 entry-content">
				<?php
				if( $Spectre_Fleet_accessors !== FALSE )
				{
					?>
					<div class="row">
						SF Spectre Fleet Channel API Cache Last Updated: <?php echo $Spectre_Fleet_accessors['lastQueried']; ?>
					</div>
						
					<h4>Characters that should be added to the 'SF Spectre Fleet' blocked list:</h4>
					
					<table class="table table-striped table_valign_m">
						<thead>
							<tr>
								<th></th>
								<th>Name</th>
							</tr>
						</thead>
						<tbody>
							<?php
							//echo '<tr><td>'.print_r( $Spectre_Fleet_missing, TRUE ) .'</td>';
							//echo '<td>'.print_r( $Spectre_Fleet_accessors['blocked'], TRUE ) .'</td></tr>';
							foreach( $all_blocked as $ID => $accessor )
							{
								if( !array_key_exists( $ID, $Spectre_Fleet_accessors['blocked'] ) )
								{
									$name = $accessor['accessorName'];
									echo '<tr>';
									// Check [accessorType] == character/0?
									echo '<td><img src="https://imageserver.eveonline.com/Character/'.$ID.'_64.jpg" class="img-rounded img40px" title="'.$name.'"></td>';
									echo '<td>'. $name ."</td>\n";
									echo '</tr>';
								}
							} ?>
						</tbody>
					</table>
					<?php
				}
				else
				{ ?><h4>SF Spectre Fleet Channel API Cache could not be queried.</h4>
					<?php
				} ?>
				</div>
				
				<div class="col-sm-6 entry-content">
				<?php
				if( $XUP1_accessors !== FALSE )
				{
					?>
					<div class="row">
						XUP~1 Channel API Cache Last Updated: <?php echo $XUP1_accessors['lastQueried']; ?>
					</div>
					
					<h4>Characters that should be added to the 'XUP~1' blocked list:</h4>
					
					<table class="table table-striped table_valign_m">
						<thead>
							<tr>
								<th></th>
								<th>Name</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach( $all_blocked as $ID => $accessor )
							{
								if( !array_key_exists( $ID, $XUP1_accessors['blocked'] ) )
								{
									$name = $accessor['accessorName'];
									echo '<tr>';
									// Check [accessorType] == character/0?
									echo '<td><img src="https://imageserver.eveonline.com/Character/'.$ID.'_64.jpg" class="img-rounded img40px" title="'.$name.'"></td>';
									echo '<td>'. $name ."</td>\n";
									echo '</tr>';
								}
							} ?>
						</tbody>
					</table>
					<?php
				}
				else
				{ ?><h4>XUP~1 Channel API Cache could not be queried.</h4>
					<?php
				} ?>
				</div>
			</div>
			
			<hr>
			
			<div class="row">
				<div class="col-sm-6 entry-content">
				<?php
				if( $XUP2_accessors !== FALSE )
				{ ?>
					<div class="row">
						XUP~2 Channel API Cache Last Updated: <?php echo $XUP2_accessors['lastQueried']; ?>
					</div>
					
					<h4>Characters that should be added to the 'XUP~2' blocked list:</h4>
					
					<table class="table table-striped table_valign_m">
						<thead>
							<tr>
								<th></th>
								<th>Name</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach( $all_blocked as $ID => $accessor )
							{
								if( !array_key_exists( $ID, $XUP2_accessors['blocked'] ) )
								{
									$name = $accessor['accessorName'];
									echo '<tr>';
									// Check [accessorType] == character/0?
									echo '<td><img src="https://imageserver.eveonline.com/Character/'.$ID.'_64.jpg" class="img-rounded img40px" title="'.$name.'"></td>';
									echo '<td>'. $name ."</td>\n";
									echo '</tr>';
								}
							} ?>
						</tbody>
					</table>
					<?php
				}
				else
				{ ?><h4>XUP~2 Channel API Cache could not be queried.</h4>
					<?php
				} ?>
				</div>
			
				<div class="col-sm-6 entry-content">
				<?php
				if( $XUP3_accessors !== FALSE )
				{ ?>
					<div class="row">
						XUP~3 Channel API Cache Last Updated: <?php echo $XUP3_accessors['lastQueried']; ?>
					</div>
					
					<h4>Characters that should be added to the 'XUP~3' blocked list:</h4>
					
					<table class="table table-striped table_valign_m">
						<thead>
							<tr>
								<th></th>
								<th>Name</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach( $all_blocked as $ID => $accessor )
							{
								if( !array_key_exists( $ID, $XUP3_accessors['blocked'] ) )
								{
									$name = $accessor['accessorName'];
									echo '<tr>';
									// Check [accessorType] == character/0?
									echo '<td><img src="https://imageserver.eveonline.com/Character/'.$ID.'_64.jpg" class="img-rounded img40px" title="'.$name.'"></td>';
									echo '<td>'. $name ."</td>\n";
									echo '</tr>';
								}
							} ?>
						</tbody>
					</table>
					<?php
				}
				else
				{ ?><h4>XUP~3 Channel API Cache could not be queried.</h4>
					<?php
				} ?>
				</div>
			</div>
