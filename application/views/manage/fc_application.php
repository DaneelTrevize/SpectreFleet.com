
			<h2>Spectre Fleet Command Application</h2>
			
			<p>You can save your application, and then resume Editing it later via the 'Apply to FC' feature.</p>
			
			<?php echo form_open('manage/apply'); ?>
				<div class="col-sm-12 ui-input <?php if(form_error('SFexp')!=NULL){echo 'has-error';}?>">
					<label>Tell us about your Spectre Fleet experience:</label>
					<textarea type="text" name="SFexp" class="form-control" style="resize:vertical;" placeholder="How long have you been flying with SF, and other NPSI groups?"><?php echo set_value('SFexp'); ?></textarea>
				</div>
				
				&nbsp;<br>
				
				<div class="col-sm-12 ui-input <?php if(form_error('priorFC')!=NULL){echo 'has-error';}?>">
					<label>Do you have prior FC experience?</label>
					<textarea type="text" name="priorFC" class="form-control" style="resize:vertical;" placeholder="With which entities, for how long, what styles and sizes of fleets?"><?php echo set_value('priorFC'); ?></textarea>
				</div>
				
				&nbsp;<br>
				
				<div class="col-sm-12 ui-input <?php if(form_error('whySF')!=NULL){echo 'has-error';}?>">
					<label>Why do you want to FC for Spectre Fleet?</label>
					<textarea type="text" name="whySF" class="form-control" style="resize:vertical;" placeholder="How do you feel you or SF could benefit from having you as an FC?"><?php echo set_value('whySF'); ?></textarea>
				</div>
				
				&nbsp;<br>
				
				<div class="col-sm-12 ui-input<?php if(form_error('Timezone')!=NULL){echo ' has-error';}?>">
					<label>When would you most be likely to run fleets? (In Eve-Time)</label>
					<select name="Timezone" class="form-control">
						<?php foreach( $fc_timezones as $timezone => $timezone_label )
						{
							echo '<option value="'.$timezone.'"';
							if( isset($Timezone) && $timezone == $Timezone ) echo ' selected';
							echo '>'.$timezone_label."</option>\n";
						} ?>
					</select>
				</div>
				
				&nbsp;<br>
				
				<div class="col-sm-12 ui-input <?php if(form_error('fleetStyle')!=NULL){echo 'has-error';}?>">
					<label>What style of fleets would you like to run?</label>
					<textarea type="text" name="fleetStyle" class="form-control" style="resize:vertical;" placeholder="E.g. Black Ops, Interceptor roams, ranged BC/BS fleets, RR HACs, etc?"><?php echo set_value('fleetStyle'); ?></textarea>
				</div>
				
				&nbsp;<br>
				
				<div class="col-sm-12 ui-input <?php if(form_error('fleetSize')!=NULL){echo 'has-error';}?>">
					<label>What size of fleets are you comfortable with being responsible for?</label>
					<textarea type="text" name="fleetSize" class="form-control" style="resize:vertical;" placeholder="E.g. 2-10, 25-50, no less than 100 in order to have decent Logistics and backup FCs, etc?"><?php echo set_value('fleetSize'); ?></textarea>
				</div>
				
				&nbsp;<br>
				
				<div class="col-sm-6 col-sm-offset-3 ui-input">
					<input type="submit" name="submit" value="Save Draft Application" class="btn btn-primary btn-block">
				</div>
			</form>
			
			<div class="col-sm-12">
				<?php
				$validation_errors = validation_errors();
				if( $validation_errors != '' )
				{
					echo '<h3>Errors</h3>';
					echo $validation_errors;
				}
				?>
			</div>
