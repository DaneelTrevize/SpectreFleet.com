<div id="content" class="content bg-base section">
	<div class="row">
		<div class="col-md-10 col-md-offset-1 col-sm-12 col-sm-offset-0 authentication">
			<div class="row">
				<div class="col-sm-12 aligncenter">
					<h2>Change Password</h2>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-6 col-lg-offset-3 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">
					<p>If you wish to use the SSO-based password reset, please <a href="/logout">log out</a> and use the link on the login page, which will use a fresh SSO token.<br>
					Otherwise, please enter your existing password to change it:</p>
				</div>
				<div class="col-lg-6 col-lg-offset-3 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1"><?php
					if( validation_errors() == TRUE )
					{ ?>
						<div class="row">
							<div class="col-sm-10 col-sm-offset-1">
								<h3>Errors</h3>
								<?php echo validation_errors(); ?>
							</div>
						</div><?php
					} ?>
					<?php echo form_open('authentication/change_password'); ?>
						<div class="ui-input<?php if(form_error('oldpassword')!=NULL){echo ' has-error';}?>">
							<label>Old Password</label>
							<input type="password" name="oldpassword" placeholder="Enter Old Password" class="form-control">
						</div>
						<div class="ui-input<?php if(form_error('newpassword')!=NULL){echo ' has-error';}?>">
							<label>New Password</label>
							<input type="password" name="newpassword" placeholder="Enter New Password" class="form-control">
						</div>
						<div class="ui-input<?php if(form_error('newpassconf')!=NULL){echo ' has-error';}?>">
							<input type="password" name="newpassconf" placeholder="Confirm New Password" class="form-control">
						</div>
						<br>
						<div class="col-lg-6 col-lg-offset-3 col-md-8 col-md-offset-2 col-sm-8 col-sm-offset-2 ui-input">
							<input type="submit" name="submit" value="Change Password" class="btn btn-warning btn-lg btn-block">
						</div>
					</form>
				</div>
			</div>
			<br>
		</div>
	</div>
</div><!--/#content-->
