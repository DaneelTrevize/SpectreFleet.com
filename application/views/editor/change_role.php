
			<h2>Manage Submissions Team</h2>
			
			<?php if( isset($_SESSION['flash_message']) )
			{
				echo '<h3>Notifications</h3><p>' . $_SESSION['flash_message'] . '</p><br>';
			} ?>
			
			<div class="col-sm-12 entry-content">
				<h3>Change Editorial Role</h3>
						
				<?php echo form_open('editor/change_role'); ?>
					<div class="row">
						<div class="col-md-6">
							<div class="ui-input<?php if(form_error('UserID')!=NULL){echo ' has-error';}?>">
								<label>Select a non-Member User</label>
								<select name="UserID" class="form-control select2-fc-dropdown">
									<option></option>
									<?php
									$last_role = NULL;
									foreach( $sorted_editors as $editor )
									{
										$this_role = $editor['Editor'];
										if( $this_role !== $last_role )
										{
											if( $last_role != NULL )	// We were already in a role optgroup, close it before we open the next
											{
												echo "</optgroup>\n";
											}
											echo '<optgroup label="'.$role_names[$this_role].'">'."\n";
										}
										echo '<option value="'.$editor['UserID'].'"';
										if( isset($UserID) && $editor['UserID'] == $UserID ) echo ' selected';
										$CharacterID = $editor['CharacterID'] == NULL ? 1 : $editor['CharacterID'];
										echo ' data-eve-character-id="' .$CharacterID. '"';
										echo ' data-eve-character-name="' .$editor['CharacterName']. '"';
										echo '>'.$editor['CharacterName']."</option>\n";
										$last_role = $this_role;
									}
									if( $last_role != NULL )	// We opened at least 1 role optgroup
									{
										echo "</optgroup>\n";
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="ui-input<?php if(form_error('Username')!=NULL){echo ' has-error';}?>">
								<label>Or enter any User Name</label>
								<input type="text" name="Username" placeholder="User Name" value="<?php if( isset($Username) ) echo $Username; ?>" class="form-control">
							</div>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-md-6">
							<div class="ui-input<?php if(form_error('Role')!=NULL){echo ' has-error';}?>">
								<label>Select New Role</label>
								<select name="Role" class="form-control">
									<?php foreach( $role_names as $role => $role_name )
									{
										echo '<option value="'.$role.'"';
										if( isset($Role) && $role == $Role ) echo ' selected';
										echo '>'.$role_name."</option>\n";
									} ?>
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<span><br>Note: You cannot successfully 'change' a user's role to the same role they currently have.</span>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-sm-6 col-sm-offset-3 ui-input">
							<input type="submit" name="submit" value="Change user's role" class="btn btn-warning btn-block">
						</div>
					</div>
				</form>
				<br>
				<div class="row">
					<?php echo validation_errors(); ?>
				</div>
				
				<script src="/js/fc_select2.js"></script>
			</div>
