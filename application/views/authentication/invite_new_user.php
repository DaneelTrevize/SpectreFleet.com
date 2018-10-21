
			<div class="col-sm-12 entry-content">
				
				<div class="row">
					<h2>Invite new user</h2>
				</div>
				
				<div class="row">
					<?php echo form_open('authentication/invite_new_user'); ?>
						<span><p>Please provide a Username:</p></span>
						<div class="ui-input<?php if(form_error('Username')!=NULL){echo ' has-error';}?>">
							<input type="text" name="Username" value="<?php echo set_value('Username'); ?>" class="form-control" />
						</div>
						<br>
						<div class="ui-input col-sm-4 col-sm-offset-4">
							<input type="submit" name="submit" value="Invite New User" class="btn btn-warning btn-block">
						</div>
					</form>
				</div>
				
				<div class="row">
					<?php echo validation_errors(); ?>
				</div>
			</div>
