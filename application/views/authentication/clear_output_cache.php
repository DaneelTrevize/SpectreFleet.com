
			<div class="col-sm-12 entry-content">
				
				<div class="row">
					<h2>Clear Output cache</h2>
				</div>
				
				<div class="row">
					<?php echo form_open('authentication/clear_output_cache');
						echo form_hidden('confirm', TRUE); ?>
						<span><p>Are you sure you wish to delete all output cache files?</p></span>
						<div class="ui-input col-sm-4 col-sm-offset-4">
							<input type="submit" name="submit" value="Delete all output cache files" class="btn btn-danger btn-block btn-lg">
						</div>
					</form>
				</div>
				
				<div class="row">
					<?php echo validation_errors(); ?>
				</div>
				
			</div>
