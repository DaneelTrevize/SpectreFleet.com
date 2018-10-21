
			<h1>Create Poll</h1>
			
			<div class="row">
				<?php echo form_open('polls/create'); ?>
					<div class="col-sm-12 ui-input<?php if(form_error('Title')!=NULL){echo ' has-error';}?>">
						<span>Poll Title</span>
						<input type="text" name="Title" class="form-control" value="<?php echo set_value('Title'); ?>">
					</div>
					
					&nbsp;<br>
					
					<div class="col-sm-12 aligncenter">
						<div class="col-sm-4 ui-input">
							<label>Everyone can see and vote</label>
							<input type="radio" name="accessMode" class="form-control" value="0" <?php echo set_radio('accessMode','0',TRUE); ?>>
						</div>
						<div class="col-sm-4 ui-input">
							<label>Everyone can see but only FCs can vote</label>
							<input type="radio" name="accessMode" class="form-control" value="1" <?php echo set_radio('accessMode','1'); ?>>
						</div>
						<div class="col-sm-4 ui-input">
							<label>Only FCs can see and/or vote</label>
							<input type="radio" name="accessMode" class="form-control" value="2" <?php echo set_radio('accessMode','2'); ?>>
						</div>
					</div>
					
					&nbsp;<br>
					
					<div class="col-sm-12 ui-input <?php if(form_error('Details')!=NULL){echo 'has-error';}?>">
						<label>Details</label>
						<textarea id="ckeditor" name="Details" class="form-control"><?php echo set_value('Details'); ?></textarea>
					</div>
					
					&nbsp;<br>
					
					<div class="col-sm-12 ui-input">
						<span>Maximum number of option votes per user:</span><br>
						<input type="number" min="1" step="1" name="maximumVotesPerUser" class="form-control" value="<?php echo set_value('maximumVotesPerUser', 1); ?>">
					</div>
					
					&nbsp;<br>
					
					<div class="col-sm-12 ui-input<?php if(form_error('options[]')!=NULL){echo ' has-error';}?>">
						<span>Voting options</span>
						<?php
						for( $optionNum = 0; $optionNum <= Polls_model::MAX_OPTIONS_PER_POLL-1; $optionNum++ )
						{ ?>
							<input type="text" name="options[<?php echo $optionNum; ?>]" class="form-control" value="<?php echo set_value('options['.$optionNum.']'); ?>">
						<?php
						} ?>
					</div>
					
					&nbsp;<br>
					
					<div class="col-sm-6 col-sm-offset-3 ui-input">
						<input type="submit" name="submit" value="Create Draft Poll" class="btn btn-primary btn-block">
					</div>
				</form>
			</div>
			
			<div class="col-sm-12">
				<?php
				if( validation_errors() != '' )
				{
					echo '<h3>Errors</h3>';
				}
				echo validation_errors(); ?>
			</div>
			
			<script src="/js/start_CKEditor.js"></script>
