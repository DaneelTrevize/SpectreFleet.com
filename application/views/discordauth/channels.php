
			<h2>Discord Channels</h2>
			<?php
			$categories = array();
			$channels_by_category = array();
			foreach( $channels as $channel )
			{
				if( $channel['type'] === 4 )	// 'GUILD_CATEGORY'
				{
					// Ensure the category is made a key for child channels
					$category_id = 'c'. $channel['id'];
					// Assume each category only occurs once
					$categories[$category_id] = $channel;
					$channels_by_category[$category_id] = array();
				}
			}
			//echo print_r( $categories, TRUE );
			//echo print_r( $channels_by_category, TRUE );
			
			foreach( $channels as $channel )
			{
				if( $channel['type'] !== 4 && array_key_exists( 'parent_id', $channel ) )
				{
					// Put the channel in the parent category
					$category_id = 'c'. $channel['parent_id'];
					$channel_id = 'c'. $channel['id'];
					if( !array_key_exists( $category_id, $channels_by_category ) )
					{
						// Bad channel category, ignore?
						//echo $channel_id;
						continue;
					}
					$category = $channels_by_category[$category_id];
					$category[$channel_id] = $channel;
					$channels_by_category[$category_id] = $category;
				}
			}
			?>
			<p>Categories: <?php echo count( $categories ); ?></p>
			<table class="table table-striped table_valign_m">
				<thead>
					<tr>
					<th>ID</td>
					<th>Name</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach( $categories as $channel_id => $channel )
					{
						echo '<tr>';
						echo '<td>'. $channel['id'] .'</td>';
						echo '<td>'. $channel['name'] .'</td>';
						echo '</tr>';
					} ?>
				</tbody>
			</table>
			
			<hr>
			
			<p>Channels: <?php echo count( $channels ) - count( $categories ); ?></p>
			<table class="table table-striped table_valign_m">
				<thead>
					<tr>
					<th>ID</td>
					<th>Name</th>
					<th>Type</th>
					<th>Parent</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach( $channels_by_category as $category_id => $grouped_channels )
					{
						foreach( $grouped_channels as $channel_id => $channel )
						{
							echo '<tr>';
							echo '<td>'. $channel['id'] .'</td>';
							echo '<td>'. $channel['name'] .'</td>';
							
							echo '<td>';
							switch( $channel['type'] )
							{
								case 0:
									echo 'Text';	// 'GUILD_TEXT'
									break;
								case 1:
									echo 'DM';
									break;
								case 2:
									echo 'Voice';	// 'GUILD_VOICE'
									break;
								case 3:
									echo 'GROUP_DM';
									break;
								case 4:
									echo 'GUILD_CATEGORY';
									break;
								default:
									echo 'Unknown';
							}
							echo '</td>';
							
							echo '<td>';
							if( array_key_exists( 'parent_id', $channel ) )
							{
								$parent_id = 'c'. $channel['parent_id'];
								if( array_key_exists( $parent_id, $categories ) )
								{
									echo $categories[$parent_id]['name'];
								}
								else
								{
									echo $channel['parent_id'];
								}
							}
							echo '</td>';
							
							echo '</tr>';
						}
					} ?>
				</tbody>
			</table>
