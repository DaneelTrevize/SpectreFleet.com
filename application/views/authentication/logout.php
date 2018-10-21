
			<div class="col-sm-12 entry-content">
				
				<div class="row">
					<h2>Log out from Spectrefleet.com</h2>
				</div>
				
				<div class="row">
					<?php echo form_open('authentication/logout');
						echo form_hidden('confirm', TRUE); ?>
						<span><p>Please confirm you wish to log out from Spectrefleet.com:</p></span>
						<div class="ui-input col-sm-4 col-sm-offset-4">
							<input type="submit" name="submit" value="Confirm logout" class="btn btn-warning btn-block">
						</div>
					</form>
				</div>
				
				<div class="row">
					<?php echo validation_errors(); ?>
				</div>
				
			</div>
