
			<h2>Update site with MOTD</h2>
			
			<p>In-game, perform the following steps using the channel dropdown and/or right-click menu:</p>
			<ol>
				<li>'Clear All Content' from the chat channel, via the bottom menu entry.</li>
				<li>'Reload MOTD', via the top menu entry.</li>
				<li>Right-click on MOTD text, choose <strong>'Copy', not Copy-All</strong>!</li>
			</ol>
			
			<div class="row">
				<?php echo form_open('channel/refresh_spectre'); ?>
					<div class="col-sm-12 ui-input <?php if(form_error('rawMOTD')!=NULL || form_error('parsedData')!=NULL){echo 'has-error';}?>">
						<label>Spectre Channel MOTD:</label>
						<textarea name="rawMOTD" class="form-control" style="height:300px;resize:vertical;"><?php echo set_value('rawMOTD'); ?></textarea>
					</div>
					
					&nbsp;<br>
					
					<div class="col-sm-6 col-sm-offset-3 ui-input">
						<input type="submit" name="submit" value="Submit latest MOTD" class="btn btn-warning btn-block">
					</div>
				</form>
			</div>
			
			<div class="col-sm-12">
				<?php
				if( validation_errors() != '' )
				{
					echo '<h3>Errors</h3>';
				}
				echo validation_errors(); ?>
			</div>
			
			<br>
			<p>For more details on the acceptable format, see <a href="/pages/view/motd_format">the MOTD Format Guide</a>.</p>
			<p class="text-muted">You no longer have to remove the starting <code>[HH:MM:SS] EVE System > Channel MOTD: </code>&nbsp;portion, and the last empty line, when copying from chat.<br>
			The reported position of any unacceptable tag is 0-indexed, after any optional starting portion removal.</p>
