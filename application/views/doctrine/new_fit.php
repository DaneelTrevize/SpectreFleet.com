
			<h2>Create New Fit</h2>
			
			<?php echo form_open('doctrine/new_fit'); ?>
				<div class="col-sm-12 ui-input <?php if(form_error('fitName')!=NULL){echo 'has-error';}?>">
					<label>Fit Name</label>
					<input type="text" name="fitName" value="<?php echo set_value('fitName', $info['fitName']); ?>" placeholder="Fit Name (Optional)" class="form-control">
				</div>
				
				&nbsp;<br>
				
				<div class="col-sm-12 ui-input <?php if(form_error('fitRole')!=NULL){echo 'has-error';}?>">
					<label>Combat Role</label>
					<select name="fitRole" class="form-control" id="basic-dropdown">
						<option value="" disabled selected>Fit Role</option>
						<?php
						$fitRole = $info['fitRole'];
						foreach( $roles as $category_name => $category )
						{
							echo '<optgroup label="'.$category_name."\">\n";
							foreach( $category as $role )
							{
								echo '<option value="'.$role.'"';
								if($fitRole == $role) echo ' selected';
								echo '>'.$role."</option>\n";
							}
							echo "</optgroup>\n";
						}
						?>
					</select>
				</div>
				
				&nbsp;<br>
				
				<div class="col-sm-12 ui-input <?php if(form_error('fitDescription')!=NULL){echo 'has-error';}?>">
					<label>Description</label>
					<textarea id="ckeditor" name="fitDescription" class="form-control"><?php echo set_value('fitDescription', $info['fitDescription']); ?></textarea>
				</div>
				
				&nbsp;<br>
				
				<div class="col-sm-12 ui-input <?php if(form_error('EFT')!=NULL || form_error('parsedFit')!=NULL){echo 'has-error';}?>">
					<label>EVE Fit format</label>
					<textarea name="EFT" class="form-control" style="height:300px;resize:vertical;"><?php echo set_value('EFT', $info['EFT']); ?></textarea>
				</div>
				
				&nbsp;<br>
				
				<div class="col-sm-6 col-sm-offset-3 ui-input">
					<input type="submit" name="submit" value="Create Fit" class="btn btn-primary btn-block">
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
			
			<script type="text/javascript">
			$(document).ready(function(){
				$("#basic-dropdown").select2({
					minimumResultsForSearch: Infinity
				});
			});
			</script>
