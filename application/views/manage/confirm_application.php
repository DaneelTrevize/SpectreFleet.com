
			<div class="entry-content col-xs-12 aligncenter">
				<h2>Spectre Fleet Command Application</h2>
			</div>
			
			<p>
				Spectre Fleet functions on a set of rules. The public Policies as well as the FCing Guide are found in the menu under the Guides section.
			</p>
			<p>
				We also have several tools to help FCs schedule, form and manage fleets & invites, the guides for which can also be found in the menu.
			</p>
			<p>
				Additionally there are features to help you manage fits & doctrines, as well as receive & review feedback of your FCing, which your recruiter can explain to you.
			</p>
			<p>
				SFCs would like to provide you with the following rules & tips summary:
				
				<ul>
					<li>You start off as a Junior FC, and will remain a one for at least 2 months or until deemed worthy of promotion.</li>
					<li>As a JFC you will limit fits to 125 mil ISK (Jita price).</li>
					<li>Spectre is not a personal army.</li>
					<li>No sexist or racist behavior allowed.</li>
					<li>No bullying or harrasing.</li>
					<li>We are NPSI, though you cannot force people to shoot blues.</li>
					<li>Have respect for fleet members and enemies. Promote a good environment, Eve is a game, games are supposed to be fun (definition of fun may vary).</li>
					<li>No shooting fleet members, warn first, kick second, shoot third if they are persistent in being annoying.</li>
					<li>You are in control of your fleet, no other FC is allowed to backseat your fleet without your permission.</li>
					<li>All Spectre fleets are hosted on the Spectre Fleet Mumble.</li>
					<li>All Message of the day (MOTD) times are and have always been form-up times.</li>
					<li>Follow the MOTD guide and do not break the MOTD or else.</li>
				</ul>
			</p>
			
			<?php echo form_open('manage/confirm_application/'.$ApplicationID); ?>
				<div class="col-sm-12 ui-input <?php if(form_error('Confirmed')!=NULL){echo 'has-error';}?>">
					<label>Please check to Confirm you have read and accept the rules:&nbsp;</label>
					<input type="checkbox" name="Confirmed" value="1" style="transform: scale(1.5);" <?php echo set_checkbox('Confirmed', '1'); ?> />
				</div>
				
				&nbsp;<br>
				
				<div class="col-sm-6 col-sm-offset-3 ui-input">
					<?php echo form_hidden('ApplicationID', $ApplicationID); ?>
					<input type="submit" name="submit" value="Submit Application" class="btn btn-primary btn-block">
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
