
			<h2>Git Information</h2>
			cwd: <?php
			$cwd = getcwd();
			echo $cwd;
			?><br>
			git version: <?php
			if( $cwd === 'K:\spectre_clone\html' )
			{
				echo shell_exec( 'cd K:\git\spectrefleet.com && git rev-parse HEAD 2>&1' );
			}
			else
			{
				echo shell_exec( 'git rev-parse HEAD 2>&1' );
			}
			?><br>
			
			<h2>Database Information</h2>
			db platform: <?php echo $db_platform; ?><br>
			db version: <?php echo $db_version; ?><br>
			
			<h2>Timezone Information</h2>
			Default timezone: <?php echo date_default_timezone_get(); ?><br>
			Current time: <?php echo date( 'Y-m-d H:i:s' ); ?><br>
			<br>
			Database time: <?php echo $db_datetime; ?><br>
			
			<h2>Resets</h2>
			
			<a href="/authentication/reset_users_password">Reset user's password</a>.
			
			<br>
			<?php
			echo '<h2>Cache Information</h2>';
			$cache_path = $this->config->item( 'cache_path' );
			$cache_path = ($cache_path == '') ? APPPATH.'cache' : $cache_path;
			echo 'File path: ' .$cache_path.DIRECTORY_SEPARATOR. "<br>\n";
			$cache_files = scandir( $cache_path );
			if( $cache_files != FALSE ) {
				echo 'Cached files count: ' . (count($cache_files) - 2) . "<br>\n";		// Don't count "." and ".."
			} ?>
			<br>
			<a href="/authentication/clear_output_cache">Clear Output cache</a>.<br>
			
			<br>
			<?php
			echo '<h2>Session Information</h2>';
			$session_path = session_save_path();
			echo 'File path: ' . $session_path.DIRECTORY_SEPARATOR . "<br>\n";
			$session_files = scandir( $session_path );
			if( $session_files != FALSE ) {
				echo 'Sessions count: ' . (count($session_files) - 2) . "<br>\n";		// Don't count "." and ".."
			} ?>
			<br>
			<a href="/authentication/delete_all_sessions">Delete all sessions</a>.<br>
			
			<br>
			<?php
			echo '<pre>';
			echo htmlentities( print_r( $_SESSION, TRUE ), ENT_QUOTES );
			echo '</pre>';
			?>
			
			
			<?php
			echo '<h2>Log Information</h2>';
			
			$today_log_filename = 'log-' . date('Y-m-d') . '.php';
			$today_log_path = APPPATH . 'logs' .DIRECTORY_SEPARATOR. $today_log_filename ;
			echo "$today_log_path";
			if( file_exists( $today_log_path ) )
			{
				echo ' exists.';
				echo '<pre>'. htmlentities( file_get_contents( $today_log_path ) , ENT_QUOTES ) .'</pre>';
			}
			else
			{
				echo ' doesn\'t exist.';
			}
			
			echo "<br>\n";
			
			$yesterday_log_filename = 'log-' . date('Y-m-d', strtotime('-1 days')) . '.php';
			$yesterday_log_path = APPPATH . 'logs' .DIRECTORY_SEPARATOR. $yesterday_log_filename ;
			echo "$yesterday_log_path";
			if( file_exists( $yesterday_log_path ) )
			{
				echo ' exists.';
				echo '<pre>'. htmlentities( file_get_contents( $yesterday_log_path ) , ENT_QUOTES ) .'</pre>';
			}
			else
			{
				echo ' doesn\'t exist.';
			}  ?>
