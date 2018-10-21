<div id="content" class="content bg-base section">
	<div class="row">
		<div class="col-md-10 col-md-offset-1 col-sm-12 col-sm-offset-0 authentication">
			<div class="row">
				<div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-sm-10 col-sm-offset-1 aligncenter">
					<h2>Log in</h2>
				</div>
			</div>
			<?php
			if( isset($_SESSION['flash_message']) )
			{ ?>
				<div class="row">
					<div class="col-sm-10 col-sm-offset-1">
						<?php echo '<p>' . $_SESSION['flash_message'] . '</p>'; ?>
					</div>
				</div><?php
			} ?>
			<div class="row">
				<div class="col-sm-6" id="left_login">
					<div class="row">
						<div class="col-sm-12 aligncenter">
							<h3>Using Spectre Fleet Credentials</h3>
						</div><?php
						if( validation_errors() == TRUE )
						{ ?>
							<div class="row">
								<div class="col-sm-10 col-sm-offset-1">
									<?php echo validation_errors(); ?>
								</div>
							</div><?php
						} ?>
						<div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-sm-10 col-sm-offset-1">
							<?php echo form_open('login'); ?>
								<div class="ui-input<?php if( validation_errors() == TRUE ){echo ' has-error';}?>">
									<input type="text" name="username" placeholder="Enter Username" value="<?php echo set_value('username'); ?>" class="form-control" />
								</div>
								<div class="ui-input<?php if( validation_errors() == TRUE ){echo ' has-error';}?>">
									<input type="password" name="password" placeholder="Enter Password" value="" class="form-control" />
								</div>
								<div class="col-sm-12 col-sm-offset-0 col-xs-10 col-xs-offset-1 ui-input">
									<input type="submit" name="submit" value="Log in" class="btn btn-success btn-lg btn-block">
								</div>
							</form>
						</div>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="row">
						<div class="col-sm-12 aligncenter">
							<h3>Using Eve Online SSO</h3>
						</div>
						<div class="col-sm-12">
							<p>In order to use the SSO login, you must have already registered with Spectre Fleet.</p>
						</div>
						<div class="col-sm-10 col-sm-offset-1 aligncenter">
							<?php echo form_open('authentication/set_SSO_state'); ?>
								<div class="ui-input">
									<button type="submit" name="action" value="login" class="btn" id="sso_button"><img src="/media/image/authentication/EVE_SSO_Login_Buttons_Large_Black.png" alt="Log in with Eve Online SSO"></button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xs-10 col-xs-offset-1">
			<hr>
		</div>
		<div class="col-md-10 col-md-offset-1 col-sm-12 col-sm-offset-0 authentication">
			<div class="row">
				<div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-sm-10 col-sm-offset-1">
					<h2>Register</h2>
					<p>Once registered, you can login using a Spectre Fleet-specific password, or via EVE SSO.</p>
					<div class="col-lg-6 col-lg-offset-3 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">
						<?php echo form_open('authentication/set_SSO_state'); ?>
							<div class="col-sm-12 col-sm-offset-0 col-xs-10 col-xs-offset-1 ui-input">
								<button type="submit" name="action" value="register" class="btn btn-info btn-lg btn-block">Register<span class="hidden-xs"> via SSO</span></button>
							</div>
						</form>
					</div>
				</div>
				<div class="col-xs-12">
					<hr>
				</div>
				<div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-sm-10 col-sm-offset-1">
					<h2>Forgotten Password</h2>
					<p>If you have forgotten your Spectre Fleet password, you can reset it via EVE SSO.</p>
					<div class="col-lg-6 col-lg-offset-3 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">
						<?php echo form_open('authentication/set_SSO_state'); ?>
							<div class="col-sm-12 col-sm-offset-0 col-xs-10 col-xs-offset-1 ui-input">
								<button type="submit" name="action" value="reset" class="btn btn-warning btn-block">Reset<span class="hidden-xs"> Spectre</span> Password</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<br>
		</div>
	</div>
</div><!--/#content-->
