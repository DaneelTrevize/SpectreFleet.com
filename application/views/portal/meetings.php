
			<?php
			function human_filesize( $bytes, $decimals = 2 )
			{
				$size = array( 'B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' );
				$factor = floor( (strlen($bytes) - 1) / 3 );
				return sprintf( "%.{$decimals}f", $bytes / pow(1024, $factor) ) . @$size[$factor];
			}// human_filesize()
			
			function sort_filenames_desc($a, $b )
			{
				$comp = $b['date'] - $a['date'];
				if( $comp == 0 )
				{
					// Sort by filename, case sensitive. Just for consistent ordering, can't have 2 with the same name.
					return strnatcmp( $b['name'], $a['name'] );
				}
				else
				{
					return $comp;
				}
			}// sort_filenames_desc()

			?>
			
			<h3>Command Meeting Records</h3>
			
			<table class="table table-striped table_valign_m">
				<thead>
					<tr>
						<th>Filename</th>
						<th>Upload Date & Time</th>
						<th>File Size</th>
						<th>Listen</th>
					</tr>
				</thead>
				<tbody>
				<?php
				// Sort by upload time, descending
				usort( $meetings, 'sort_filenames_desc' );
				
				foreach( $meetings as $meeting )
				{
					echo '<tr>';
					// [name], [server_path], [size], [date], [relative_path]
					
					echo '<td>';
						echo $meeting['name'];
					echo '</td>';
					echo '<td>';
						$upload_dtz = DateTime::createFromFormat( 'U', $meeting['date'] );
						echo $upload_dtz->format( 'l jS F Y \a\t H:i' );
					echo '</td>';
					echo '<td>';
						echo human_filesize( $meeting['size'] );
					echo '</td>';
					
					echo '<td>';
						$mp3_url = '/portal/meetings/'.$meeting['name'];
						echo '<audio controls="controls" preload="none" src="'.$mp3_url.'">';
						echo 'Your browser does not support the <code>audio</code> element. Here is a <a href="'.$mp3_url.'">link to the audio</a> instead.</audio>';
					echo '</td>';
					
					echo '</tr>';
				} ?>
				</tbody>
			</table>
			<script src="/js/quieten.js"></script>
