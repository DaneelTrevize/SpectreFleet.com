<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li><a href="/activity/recent_fleets">Fleets</a></li>
			<li class="active"><?php echo $header; ?></li>
		</ol>
	</div>
	
	<div class="row">
		
		<article class="entry style-single col-md-10 col-md-offset-1">
			
			<div class="entry-content">
			
				<h2><?php echo $header; ?></h2>
				
				<?php $this->load->view( 'common/fleet_type_key' ); ?>
				
				<?php echo $fleets_html; ?>
			
			</div>
			
		</article>
		
	</div>
	
</div><!--/#content-->