
			<h2>Channel Configuration Checker</h2>
			
			<p>The below cannot automatically populated from the dead channel API (thanks CCP...), please open up Spectre Fleet channel's settings/Configuration window, select an Operator's name, then press <code>CTRL+A</code> then <code>CTRL+C</code>. Paste the results below.</p>
			
			<?php echo form_open('channel/permission_results'); ?>
				<div class="row">
					<div class="ui-input col-md-8 col-md-offset-2">
						<textarea onclick="this.select();" type="text" name="operators" placeholder="Paste Results" value="" class="form-control" style="line-height:14px;" rows="15"><?php
						foreach( $operators as $operator )
						{
							echo $operator['accessorName'] ."\n";
						} ?></textarea>
					</div>
				</div>
				<br>
				<div class="row">
					<div class="ui-input col-md-6 col-md-offset-3">
						<input type="submit" name="submit" value="Check Permissions" class="btn btn-block">
					</div>
				</div>
			</form>
			
			<br>
			<br>
			<div class="row">
				<div class="col-sm-6">
				
					<div style="display:inline-block;">
						<h3>Current EVE Time:</h3>
						<p><?php echo $currentEVEtime; ?></p>
					</div>
					
				</div>
				<div class="col-sm-6">
				
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
