<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li class="active">Command Application</li>
		</ol>
	</div>
	
	<div class="row">

		<header class="page-header col-md-10 col-md-offset-1">

			<h2 class="page-title full-page-title">
				Spectre Fleet Command Application
			</h2>

		</header>
		
		<article class="entry style-single style-single-full type-post col-md-10 col-md-offset-1">

			<div class="entry-content col-md-6 col-md-offset-3">
				
				<?php echo form_open('manage/submit_fc_application'); ?>
					<h3>
						Character Name
					</h3>
					
					<div class="ui-input<?php if(form_error('CharacterName')!=NULL){echo ' has-error';}?>">
						<input type="text" name="CharacterName" placeholder="Enter Character Name" class="form-control" value="<?php echo set_value('CharacterName'); ?>">
					</div>
					
					<h3>
						Primary Timezone
					</h3>
					
					<div class="ui-input<?php if(form_error('Timezone')!=NULL){echo ' has-error';}?>">
						<select name="Timezone" class="form-control">
							<option></option>
							<option value="EU" <?php echo set_select('Timezone', 'EU'); ?>>12:00 - 20:00 (Europe, Middle East, Africa)</option>
							<option value="US" <?php echo set_select('Timezone', 'US'); ?>>20:00 - 04:00 (Americas)</option>
							<option value="AU" <?php echo set_select('Timezone', 'AU'); ?>>04:00 - 12:00 (Asia-Pacific)</option>
						</select>
					</div>
					<br>
					<h3>
						Previous FC Experience
					</h3>
					
					<div class="ui-input<?php if(form_error('FC_Experience')!=NULL){echo ' has-error';}?>">
						<select name="FC_Experience" class="form-control">
							<option></option>
							<optgroup label="Standard">
								<option value="None" <?php echo set_select('FC_Experience', 'None'); ?>>None</option>
								<option value="Gang" <?php echo set_select('FC_Experience', 'Gang'); ?>>Gang (2-10)</option>
								<option value="Skirmish" <?php echo set_select('FC_Experience', 'Skirmish'); ?>>Skirmish (11-50)</option>
								<option value="Fleet" <?php echo set_select('FC_Experience', 'Fleet'); ?>>Fleet (51-250)</option>
								<option value="Command" <?php echo set_select('FC_Experience', 'Command'); ?>>Command (250+)</option>
							</optgroup>
							<optgroup label="Other">
								<option value="Blops" <?php echo set_select('FC_Experience', 'Blops'); ?>>Black Ops</option>
								<option value="Incursion" <?php echo set_select('FC_Experience', 'Incursion'); ?>>Incursions</option>
								<option value="Ganking" <?php echo set_select('FC_Experience', 'Ganking'); ?>>Ganking</option>
								<option value="Wardecs" <?php echo set_select('FC_Experience', 'Wardecs'); ?>>Wardecs</option>
							</optgroup>
						</select>
					</div>
					
					<h3>
						Previous NPSI Experience
					</h3>
					
					<div class="ui-input<?php if(form_error('NPSI_Experience')!=NULL){echo ' has-error';}?>">
						<select name="NPSI_Experience" class="form-control">
							<option></option>
							<option value="None" <?php echo set_select('NPSI_Experience', 'None'); ?>>None</option>
							<option value="1 Month" <?php echo set_select('NPSI_Experience', '1 Month'); ?>>Less than 1 Month</option>
							<option value="6 Months" <?php echo set_select('NPSI_Experience', '6 Months'); ?>>Less than 6 Months</option>
							<option value="1 Year" <?php echo set_select('NPSI_Experience', '1 Year'); ?>>Less than 1 Year</option>
							<option value="Lots" <?php echo set_select('NPSI_Experience', 'Lots'); ?>>More than 1 Year</option>
						</select>
					</div>
					
					<h3>
						Why do you want to FC for Spectre Fleet?
					</h3>
					
					<div class="ui-input">
						<textarea type="text" name="Comments" placeholder="Optional" class="form-control" style="resize:vertical;"><?php echo set_value('Comments'); ?></textarea><br>
					</div>
					
					<div class="ui-input">
						<input type="submit" name="submit" value="Submit Application" class="btn btn-primary btn-block">
					</div>
				</form>

			</div>
			
			<div class="col-md-3">
				<?php
				if(validation_errors() == TRUE )
				{
					echo '<h3>Form Errors:</h3>';
					
					echo validation_errors();
				} ?>
			</div>
			
		</article>

	</div>

</div><!--/#content-->