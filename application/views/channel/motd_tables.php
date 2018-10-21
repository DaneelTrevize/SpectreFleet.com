<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
		</ol>
	</div>
	
	<div class="row">
		
		<article class="entry style-single">
			
			<div class="entry-content motd">
				
				<div class="row">
					<div class="col-lg-3 col-lg-offset-1 col-md-4 col-md-offset-0 col-sm-5 col-sm-offset-0 col-xs-10 col-xs-offset-1">
					
						<div>
							<h3>Current EVE Time:</h3>
							<p><?php echo $currentEVEtime; ?></p>
						</div>
						
					</div>
					<div class="col-lg-4 col-lg-offset-3 col-md-5 col-md-offset-3 col-sm-6 col-sm-offset-1 col-xs-10 col-xs-offset-1">
					
						<?php
						if( isset( $lastQueried ) )
						{ ?>
						<div style="display:inline-block; float:right;">
							<table class="table">
								<thead>
								<tr>
									<th class="alignright">Updated</th>
									<th class="alignright">Date & Time</th>
								</tr>
								</thead>
								<tbody>
									<tr>
										<td class="alignright">Last: </td>
										<td><?php echo $lastQueried; ?></td>
									</tr>
								</tbody>
							</table>
						</div>
						<?php
						} ?>
						
					</div>
				</div>
				<div class="row">
					<br>
					
					<?php echo $scheduled_html; ?>
					
					<br>
				</div>
				
			</div>
			
		</article>
		
	</div>
	
</div><!--/#content-->