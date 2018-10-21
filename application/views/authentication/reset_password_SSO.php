<div id="content" class="content bg-base section">
	<div class="row">
		<div class="col-md-10 col-md-offset-1 col-sm-12 col-sm-offset-0 authentication">
			<div class="row">
				<div class="col-sm-12 aligncenter">
					<h2>Reset Spectre Fleet Password</h2>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-6 col-lg-offset-3 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">
					<p>Please supply a new Spectre Fleet-specific password, to enable you to login should the EVE SSO systems be experiencing issues:</p>
					<div class="col-sm-8">
						<label>Character Name</label>
						<div class="ui-input<?php if(form_error('ResettingCharacterName')!=NULL){echo ' has-error';}?>">
							<input disabled type="text" name="ResettingCharacterName" value="<?php
								$CharacterID = isset( $_SESSION['ResettingCharacterID'] ) ? $_SESSION['ResettingCharacterID'] : 1;
								$CharacterName = isset( $_SESSION['ResettingCharacterName'] ) ? $_SESSION['ResettingCharacterName'] : '';
								echo $CharacterName; ?>" class="form-control" />
						</div>
					</div>
					<div class="col-sm-4">
						<?php
						echo '<img src="https://imageserver.eveonline.com/Character/'.$CharacterID.'_128.jpg" class="img-rounded" alt="'.$CharacterName.'">'; ?>
					</div>
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
					<?php echo form_open('authentication/reset_password_SSO'); ?>
						<div class="ui-input<?php if(form_error('password')!=NULL){echo ' has-error';}?>">
							<label>Spectre Fleet-specific Password</label>
							<input type="password" name="password" placeholder="Enter Password" class="form-control" />
						</div>
						<div class="ui-input<?php if(form_error('passconf')!=NULL){echo ' has-error';}?>">
							<label>Confirm Password</label>
							<input type="password" name="passconf" placeholder="Confirm Password" class="form-control" />
						</div>
						<br>
						<div class="col-lg-6 col-lg-offset-3 col-md-8 col-md-offset-2 col-sm-8 col-sm-offset-2 ui-input">
							<input type="submit" name="submit" value="Reset Password" class="btn btn-warning btn-lg btn-block">
						</div>
					</form>
				</div>
			</div>
			<br>
		</div>
	</div>
</div><!--/#content-->
