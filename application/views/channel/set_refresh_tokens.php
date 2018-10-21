
			<div class="col-sm-12 entry-content">
				
				<div class="row">
					<h2>Set channel refresh tokens</h2>
				</div>
				
				<div class="row">
					<?php echo form_open('channel/set_refresh_tokens');
						echo form_hidden('confirm', TRUE); ?>
						<span><p>Are you sure you wish to set new refresh tokens?</p></span>
						<div class="ui-input col-sm-4 col-sm-offset-4">
							<input type="submit" name="submit" value="Set channel refresh tokens" class="btn btn-danger btn-block btn-lg">
						</div>
					</form>
				</div>
				
				<div class="row">
					<?php echo validation_errors(); ?>
				</div>
				
			</div>
