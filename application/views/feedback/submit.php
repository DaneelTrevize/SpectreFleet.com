<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li><a href="/activity/FCs">Commanders</a></li>
			<li class="active">Feedback</li>
		</ol>
	</div>
	
	<div class="row">

		<header class="page-header col-md-8 col-md-offset-2">

			<h3 class="page-title full-page-title">
				Feedback Form
			</h3>
			<p>Spectre Fleet appreciates you taking the time to provide a review of your recent experiences with your FCs and other fellow fleetmates.</p>

		</header>
		
		<article class="entry style-single style-single-full type-post col-md-10 col-md-offset-1">

			<div class="entry-content col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
				
				<h3>
					Choose whom to leave feedback for:
				</h3>
				
				<div class="row">
				<?php echo form_open('feedback/submit'); ?>
					
					<select name="UserID" class="form-control select2-fc-dropdown">
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
							$CharacterID = $FC['CharacterID'] == NULL ? 1 : $FC['CharacterID'];
							echo ' data-eve-character-id="' .$CharacterID. '"';
							echo ' data-eve-character-name="' .$FC['CharacterName']. '"';
							if( isset( $UserID ) && $FC['UserID'] == $UserID )
							{
								echo ' selected';
							}
							echo '>' . $FC['CharacterName']."</option>\n";
							$last_rank = $this_rank;
						}
						if( $last_rank != NULL )	// We opened at least 1 rank optgroup
						{
							echo "</optgroup>\n";
						}
						?>
					</select>
					
					<h3>
						Feedback details:
					</h3>
					
					<div class="ui-input<?php if(form_error('Feedback')!=NULL){echo ' has-error';}?>">
						<textarea type="text" name="Feedback" class="form-control" rows="5" style="resize:vertical;" placeholder="Please provide constructive feedback with reasons as to your opinion and rating. This could include comments on the FC, your other fleetmates, or your own ability to participate in and enjoy the fleet. Thank you."><?php echo set_value('Feedback'); ?></textarea>
					</div>
					
					<h3>
						Rate your recent experience with this FC
					</h3>
					
					<div class="row ui-input">
						<div class="col-xs-1 aligncenter"> </div>
						<div class="col-xs-2 aligncenter">-10</div>
						<div class="col-xs-2 aligncenter">-5</div>
						<div class="col-xs-2 aligncenter">0</div>
						<div class="col-xs-2 aligncenter">+5</div>
						<div class="col-xs-2 aligncenter">+10</div>
						<div class="col-xs-1 aligncenter"> </div>
					</div>
					<div class="row ui-input">
						<div class="col-xs-1 aligncenter">-</div>
						<div class="col-xs-2 aligncenter"><input type="radio" name="Score" value="2" <?php echo set_checkbox('Score',2); ?>></div>
						<div class="col-xs-2 aligncenter"><input type="radio" name="Score" value="4" <?php echo set_checkbox('Score',4); ?>></div>
						<div class="col-xs-2 aligncenter"><input type="radio" name="Score" value="6" <?php echo set_checkbox('Score',6); ?>></div>
						<div class="col-xs-2 aligncenter"><input type="radio" name="Score" value="8" <?php echo set_checkbox('Score',8); ?>></div>
						<div class="col-xs-2 aligncenter"><input type="radio" name="Score" value="10" <?php echo set_checkbox('Score',10); ?>></div>
						<div class="col-xs-1 aligncenter">+</div>
					</div>
					<div class="row ui-input">
						<div class="col-xs-1 aligncenter"> </div>
						<div class="col-xs-2 aligncenter"><span class="sf_rate_2">-</span></div>
						<div class="col-xs-2 aligncenter"><span class="sf_rate_4">-</span></div>
						<div class="col-xs-2 aligncenter"><span class="sf_rate_6">=</span></div>
						<div class="col-xs-2 aligncenter"><span class="sf_rate_8">+</span></div>
						<div class="col-xs-2 aligncenter"><span class="sf_rate_10">+</span></div>
						<div class="col-xs-1 aligncenter"> </div>
					</div>
					
					<div class="ui-input col-md-10 col-md-offset-1">
						<br><input type="submit" name="submit" value="Submit Feedback" class="btn btn-primary btn-block">
					</div>
				</form>
				</div>
				
				<div class="row">
					<br><?php echo validation_errors(); ?>
				</div>
				
				<script src="/js/fc_select2.js"></script>
	
			</div>

		</article>

	</div>

</div><!--/#content-->