
			<h2>Manage Special Interest Groups</h2>
			
			<?php if( isset($_SESSION['flash_message']) )
			{
				echo '<h3>Notifications</h3><p>' . $_SESSION['flash_message'] . '</p><br>';
			} ?>
			
			<div class="col-sm-12 entry-content">
				<h3>Change User's Groups</h3>
						
				<?php echo form_open('SIGs/change'); ?>
					<div class="row">
						<div class="col-md-6">
							<div class="ui-input<?php if(form_error('Username')!=NULL){echo ' has-error';}?>">
								<label>Enter a User Name</label>
								<input type="text" name="Username" placeholder="User Name" value="<?php if( isset($Username) ) echo $Username; ?>" class="form-control">
							</div>
						</div>
						<div class="col-md-6">
							<div class="ui-input<?php if(form_error('GroupID')!=NULL){echo ' has-error';}?>">
								<label>Select Group</label>
								<select name="GroupID" class="form-control">
									<?php foreach( $groups as $group )
									{
										$this_groupID = $group['groupID'];
										echo '<option value="'.$this_groupID.'"';
										if( isset($GroupID) && $GroupID == $this_groupID ) echo ' selected';
										echo '>'.$group['groupName']."</option>\n";
									} ?>
								</select>
							</div>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-sm-12 ui-input<?php if(form_error('comment')!=NULL){echo ' has-error';}?>">
							<label>Reason</label>
							<textarea type="text" name="comment" class="form-control" style="resize:vertical;" placeholder="Please give a reason or evidence for this change, e.g. a related zkillboard URL"><?php echo set_value('comment'); ?></textarea>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-md-4 col-md-offset-1 ui-input">
							<input type="submit" name="Add" value="Add User to Group" class="btn btn-warning btn-block">
						</div>
						<div class="col-md-4 col-md-offset-2 ui-input">
							<input type="submit" name="Remove" value="Remove User from Group" class="btn btn-danger btn-block">
						</div>
					</div>
				</form>
				<br>
				<div class="row">
					<?php echo validation_errors(); ?>
				</div>
				
			</div>
