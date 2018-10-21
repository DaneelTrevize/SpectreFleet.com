<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li><a href="/activity/FCs">Commanders</a></li>
			<li class="active">FC Profiles</li>
		</ol>
	</div>
	
	<div class="row">

		<header class="page-header col-xs-12">

			<h2 class="page-title full-page-title">
				FC Profiles Search
			</h2>

		</header>
		
		<article class="col-md-10 col-md-offset-1">

			<div>
				<h4>
					Please select the Fleet Commander who's profile you wish to view:
				</h4>
				
				<?php echo form_open('activity/FC'); ?>
				<div class="col-md-4 col-md-offset-2">
					<div class="ui-input">
						<select name="FC_ID" class="form-control select2-fc-dropdown">
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
								if( isset($FC_ID) && $FC['UserID'] == $FC_ID ) echo ' selected';
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
				<div class="ui-input col-md-4">
					<button type="submit" class="btn btn-primary btn-block">View FC Profile</button><br>
				</div>
				</form>
				
				<script src="/js/fc_select2.js"></script>
			
			</div>

		</article>

	</div>

</div><!--/#content-->