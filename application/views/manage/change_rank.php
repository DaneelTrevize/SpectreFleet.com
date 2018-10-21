
			<h2>Manage Command Team</h2>
			
			<?php if( isset($_SESSION['flash_message']) )
			{
				echo '<h3>Notifications</h3><p>' . $_SESSION['flash_message'] . '</p><br>';
			} ?>
			
			<div class="col-sm-12 entry-content">
				<h3>Change FC Rank</h3>
						
				<?php echo form_open('manage/change_rank'); ?>
					<div class="row">
						<div class="col-md-6">
							<div class="ui-input<?php if(form_error('UserID')!=NULL){echo ' has-error';}?>">
								<label>Select a non-Member User</label>
								<select name="UserID" class="form-control select2-fc-dropdown">
									<option></option>
									<?php
									$last_rank = NULL;
									foreach( $sorted_commanders as $FC )
									{
										$this_rank = $FC['Rank'];
										if( $this_rank !== $last_rank )
										{
											if( $last_rank != NULL )	// We were already in a rank optgroup, close it before we open the next
											{
												echo "</optgroup>\n";
											}
											echo '<optgroup label="'.$rank_names[$this_rank].'">'."\n";
										}
										echo '<option value="'.$FC['UserID'].'"';
										if( isset($UserID) && $FC['UserID'] == $UserID ) echo ' selected';
										$CharacterID = $FC['CharacterID'] == NULL ? 1 : $FC['CharacterID'];
										echo ' data-eve-character-id="' .$CharacterID. '"';
										echo ' data-eve-character-name="' .$FC['CharacterName']. '"';
										echo '>'.$FC['CharacterName']."</option>\n";
										$last_rank = $this_rank;
									}
									if( $last_rank != NULL )	// We opened at least 1 rank optgroup
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
							<div class="ui-input<?php if(form_error('Rank')!=NULL){echo ' has-error';}?>">
								<label>Select New Rank</label>
								<select name="Rank" class="form-control">
									<?php foreach( $rank_names as $rank => $rank_name )
									{
										echo '<option value="'.$rank.'"';
										if( isset($Rank) && $rank == $Rank ) echo ' selected';
										echo '>'.$rank_name."</option>\n";
									} ?>
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<span><br>Note: You cannot successfully 'change' a user's rank to the same rank they currently have.</span>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-sm-6 col-sm-offset-3 ui-input">
							<input type="submit" name="submit" value="Change user's rank" class="btn btn-warning btn-block">
						</div>
					</div>
				</form>
				<br>
				<div class="row">
					<?php echo validation_errors(); ?>
				</div>
				
				<script src="/js/fc_select2.js"></script>
				
			</div>
