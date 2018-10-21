
			<script type="text/javascript">csrf_hash = '<?php echo $csrf_hash; ?>';</script>
			
			<div class="col-sm-12 entry-content">
			
				<?php
				if( isset($_SESSION['flash_message']) )
				{ ?>
				<div class="row">
					<h2>Notifications</h2>
					<?php echo '<p>' . $_SESSION['flash_message'] . '</p>'; ?>
				</div><?php
				} ?>
				
				<div class="row">
					<h3>Auto-Detect Fleet</h3>
				</div>
				
				<div class="row">
					<span><p>Once you are Fleet Boss&nbsp;<i class="fa fa-star" aria-hidden="true"></i>, this tool can attempt to auto-detect your current in-game fleet. This is limited by CCP to only a few attempts per minute, so ideally start your new fleet in-game beforehand, or use the manual External Fleet Link method below.</p></span>
					
					<div class="ui-input col-md-4 col-md-offset-2 col-sm-5 col-sm-offset-0 col-xs-10 col-xs-offset-1">
						<label class="checkbox">
							<input checked type="checkbox" data-toggle="toggle" data-onstyle="success" data-on="Auto Detecting<span class='visible-lg-inline'> Fleet</span>" data-offstyle="default" data-off="<span class='visible-lg-inline'>Auto </span>Detecting Disabled" data-width="100%" id="auto_get">
						</label>
					</div>
					<div class="ui-input col-md-6 col-sm-7 col-xs-12" id="fleet_url_response">
						<span id="no_fleet"><i class="fa fa-spinner fa-fw fa-pulse" aria-hidden="true"></i>&nbsp;No fleet detected, please wait before we can try again.</span>
						<span hidden id="getting_fleet"><i class="fa fa-cog fa-fw fa-spin" aria-hidden="true"></i>&nbsp;Attempting to detect fleet...</span>
						<span hidden id="auto_disabled">Please submit a Fleet Link below.</span>
					</div>
				</div>
				
				<hr>
				
				<div class="row">
					<h3>Enter External Fleet Link</h3>
				</div>
				
				<div class="row">
					<span><p>In-game, as the Fleet Boss&nbsp;<i class="fa fa-star" aria-hidden="true"></i>, from the top-left "Fleet actions" dropdown menu, select "Copy External Fleet Link" and then Paste below:</p></span>
					<?php echo form_open('fleets2/get_fleet_url'); ?>
						<div class="ui-input col-md-7 col-sm-7<?php if(form_error('url')!=NULL){echo ' has-error';}?>">
							<input type="text" name="url" placeholder="https://esi.tech.ccp.is/v1/fleets/?????????????/?datasource=tranquility" class="form-control" value="">
							<br>
						</div>
						<div class="ui-input col-md-3 col-sm-5 col-sm-offset-0 col-xs-10 col-xs-offset-1">
							<input type="submit" name="submit" value="Submit Fleet Link" class="btn btn-block">
						</div>
					</form>
				</div>
				
				<div class="row">
					<div class="col-sm-12">
						<?php echo validation_errors(); ?>
					</div>
				</div>
				
				<div class="row">
					<br>
					<br>
					
					<div class="col-sm-4 col-sm-offset-1">
						<p>Here is an example of the in-game "Fleet actions" menu and the Copy External Fleet Link option:</p>
					</div>
					<div class="col-sm-4 col-sm-offset-1">
						<img src="/media/image/fleets/external fleet url 2.png" alt="Copy External Fleet URL">
					</div>
				</div>
			</div>
			
			<script src="/js/fleet_url.js"></script>
