
			<div class="col-sm-12 entry-content">
				
				<div class="row">
					<h2>Reset user's password</h2>
				</div>
				
				<div class="row">
					<?php echo form_open('authentication/reset_users_password'); ?>
						<span><p>Please provide a UserID:</p></span>
						<div class="ui-input<?php if(form_error('UserID')!=NULL){echo ' has-error';}?>">
							<input type="number" min="1" step="1" name="UserID" class="form-control" value="">
						</div>
						<br>
						<div class="ui-input col-sm-4 col-sm-offset-4">
							<input type="submit" name="submit" value="Reset user's password" class="btn btn-warning btn-block">
						</div>
					</form>
				</div>
				
				<div class="row">
					<?php echo validation_errors(); ?>
				</div>
			</div>
