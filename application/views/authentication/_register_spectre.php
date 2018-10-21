		<div class="ui-15">

			<!-- REGISTER -->
			<div class="ui-content" style="max-width:400px;">
				<div class="container-fluid">
					<div class="row">
						<div class="col-sm-12 ui-padd">
							<!-- UI Form -->
							<div class="ui-form">
								<h2>Register</h2>
								<?php echo form_open('authentication/register_spectre'); ?>
									<div class="ui-input<?php if(form_error('username')!=NULL){echo ' has-error';}?>">
										<input type="text" name="username" placeholder="Enter Username" value="<?php echo set_value('username'); ?>" class="form-control" />
									</div>
									<div class="ui-input<?php if(form_error('password')!=NULL){echo ' has-error';}?>">
										<input type="password" name="password" placeholder="Enter Password" class="form-control" />
									</div>
									<div class="ui-input<?php if(form_error('passconf')!=NULL){echo ' has-error';}?>">
										<input type="password" name="passconf" placeholder="Confirm Password" class="form-control" />
									</div>
									<br>
									<input type="submit" name="submit" value="Register" class="btn btn-info btn-lg btn-block">
								</form>
							</div>
						</div>
					</div><?php
					if( validation_errors() == TRUE )
					{ ?>
					<div class="row">
						<div class="col-md-12 col-sm-12">
							<div class="ui-form">
								<h2>Errors</h2>
								<?php echo validation_errors(); ?>
							</div>
						</div>
					</div><?php
					} ?>
				</div>
			</div>
		</div>