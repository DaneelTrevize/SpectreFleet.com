<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li class="active">Local Scan</li>
		</ol>
	</div>
	
	<div class="row">

		<header class="page-header col-md-10 col-md-offset-1">

			<h2 class="page-title full-page-title">
				Local Scanner
			</h2>

		</header>
		
		<article class="col-md-10 col-md-offset-1">

			<div class="col-lg-10 col-lg-offset-1 col-md-12">
				
				<?php echo form_open('tool/lscan'); ?>
				
				<div class="row">
					<div class="col-md-6">
						<h3>
							Local members list
						</h3>
						<div class="ui-input">
							<textarea type="text" name="Local" class="form-control" style="resize:vertical;height:200px;"></textarea><br>
						</div>
					</div>
					
					<div class="col-md-6">
						<h3>
							Fleet members to filter out
						</h3>
						<div class="ui-input">
							<textarea type="text" name="Fleet" placeholder="Optional" class="form-control" style="resize:vertical;height:200px;"></textarea><br>
						</div>
					</div>
				</div>
				
				<div class="row">
					<div class="ui-input col-md-12">
						<span>System name: </span><input type="text" name="system" placeholder="Optional" class="form-control"><br>
					</div>
					<div class="col-md-6 text-muted">
						<p>
							You can right-click the System Name/Constellation/Region line at the top left of the in-game display, choose Copy, and then paste the result in the field above.</p>
					</div>
					<div class="col-md-6 text-muted">
						<img class="img-rounded" src="/media/image/misc/system name copy.png"></img>
					</div>
				</div>
				
				<br>
				<div class="row">
					<div class="ui-input col-sm-6 col-sm-offset-3">
						<input type="submit" name="submit" value="Submit" class="btn btn-primary btn-block">
					</div>
				</div>
				
				</form>
				
				<br>
				<div class="row">
					<?php echo validation_errors(); ?>
				</div>
	
			</div>
			<div class="col-sm-12 aligncenter text-muted">
				<p>
					Please note CCP's API takes approximately 0.5 seconds per 100 names that haven't recently been looked up.</p>
			</div>

		</article>

	</div>

</div><!--/#content-->