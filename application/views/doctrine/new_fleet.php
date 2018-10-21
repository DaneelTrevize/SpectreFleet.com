
			<h2>Create New Fleet</h2>
			
			<?php echo form_open('doctrine/new_fleet'); ?>
				<div class="col-sm-12 ui-input <?php if(form_error('fleetName')!=NULL){echo 'has-error';}?>">
					<label>Fleet Name</label>
					<input type="text" name="fleetName" value="<?php echo set_value('fleetName', $info['fleetName']); ?>" placeholder="Fleet Name" class="form-control">
				</div>
				
				&nbsp;<br>
				
				<div class="col-sm-12 ui-input <?php if(form_error('fleetType')!=NULL){echo 'has-error';}?>">
					<label>Combat Type</label>
					<select name="fleetType" id="basic-dropdown" class="form-control">
						<option value="" disabled selected>Fleet Type</option>
						<?php
						$fleetType = $info['fleetType'];
						foreach( $roles as $category_name => $category )
						{
							echo '<optgroup label="'.$category_name."\">\n";
							foreach( $category as $category_value => $category_name )
							{
								echo '<option value="'.$category_value.'"';
								if($fleetType == $category_value) echo ' selected';
								echo '>'.$category_name."</option>\n";
							}
							echo "</optgroup>\n";
						}
						?>
					</select>
				</div>
				
				&nbsp;<br>
				
				<div class="col-sm-12 ui-input <?php if(form_error('fleetComposition[]')!=NULL){echo 'has-error';}?>">
					<label>Fits</label>
					<p>You can type in this field to filter based upon the ID number, hull type name, Official status, and fit name</p>
					<select name="fleetComposition[]" multiple="multiple" class="form-control select2-fits-dropdown">
						<?php
						$currentRole = '';
						foreach( $fits as $fit )
						{
							if( $fit['fitRole'] != $currentRole )
							{
								if( $currentRole != '' )	// Check it's not the first group
								{
									echo '</optgroup>';
								}
								$currentRole = $fit['fitRole'];
								echo '<optgroup label="'.$currentRole.'">';
							}
							echo '<option value="'.$fit['fitID'].'"';
							
							echo ' data-eve-type-id="' .$fit['shipID']. '"';
							echo ' data-eve-type-name="' .$fit['shipName']. '"';
							echo ' data-is-official="' .( $fit['status'] == 'Official' ? 'true' : 'false' ). '"';
							echo ' data-fit-name="' .$fit['fitName']. '"';
							
							if( in_array( $fit['fitID'], $ships ) ) echo ' selected';
							echo '>';
							
							$searchable_name = $fit['fitID'] .' '. $fit['shipName'] .' '. ( $fit['status'] == 'Official' ? 'Official ' : '' ) . $fit['fitName'];
							echo $searchable_name . '</option>';
						}
						if( $currentRole != '' )	// Close the last group
						{
							echo '</optgroup>';
						}
						?>
					</select>
				</div>
				
				&nbsp;<br>
				
				<div class="col-sm-12 ui-input <?php if(form_error('fleetDescription')!=NULL){echo 'has-error';}?>">
					<label>Description</label>
					<textarea id="ckeditor" name="fleetDescription" class="form-control"><?php echo set_value('fleetDescription', $info['fleetDescription']); ?></textarea>
				</div>
				
				&nbsp;<br>
				
				<div class="col-sm-6 col-sm-offset-3 ui-input">
					<input type="submit" name="submit" value="Create Fleet" class="btn btn-primary btn-block">
				</div>
			</form>
			
			<div class="col-sm-12">
				<?php
				if( validation_errors() != '' )
				{
					echo '<h3>Errors</h3>';
				}
				echo validation_errors(); ?>
			</div>
			
			<script src="/js/start_CKEditor.js"></script>
			
			<script src="/js/fits_select2.js"></script>
			<script type="text/javascript">
			$(document).ready(function(){
				$("#basic-dropdown").select2({
					minimumResultsForSearch: Infinity
				});
			});
			</script>
