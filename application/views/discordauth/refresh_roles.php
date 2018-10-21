
			<div class="col-sm-12 entry-content">
				
				<div class="row">
					<h2>Refresh roles list</h2>
				</div>
				
				<div class="row">
					<?php echo form_open('discordauth/refresh_roles');
						echo form_hidden('confirm', TRUE); ?>
						<span><p>Are you sure you wish to refresh guild/server roles from the Discord API?</p></span>
						<div class="ui-input col-sm-4 col-sm-offset-4">
							<input type="submit" name="submit" value="Refresh roles" class="btn btn-danger btn-block btn-lg">
						</div>
					</form>
				</div>
				
				<div class="row">
					<?php echo validation_errors(); ?>
				</div>
				
			</div>
