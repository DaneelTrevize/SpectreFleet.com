
			<div class="col-xs-12 aligncenter">
				<h2>Legacy Spectre FC Application</h2>
				
				<h3>Character Name: "<?php echo $application['CharacterName']; ?>" (Character ownership was not verified at submission)</h3>
			</div>
			
			<div class="col-sm-5 col-sm-offset-1">
				<label>Application status:</label>
				<p><?php echo $application['Status']; ?></p>
			</div>
			<div class="col-sm-5 col-sm-offset-1">
				<label>Date submitted:</label>
				<p><?php
				$DateSubmitted = $application['DateSubmitted'];
				echo date("F jS, Y",strtotime($DateSubmitted));
				?></p>
			</div>
			
			&nbsp;<hr>
			
			<div class="col-sm-12">
				<label>Primary Timezone:</label>
				<p><?php $Timezone = $application['Timezone'];
					echo $fc_timezones[$Timezone]; ?></p>
			</div>
			
			&nbsp;<br>
			
			<div class="col-sm-12">
				<label>Why do you want to FC for Spectre Fleet?</label>
				<p><?php echo $application['Comments']; ?></p>
			</div>
			
			&nbsp;<br>
			
			<div class="col-sm-12">
				<label>Previous FC Experience:</label>
				<p><?php
				$FC_Experience = $application['FC_Experience'];
				switch( $FC_Experience )
				{
					case 'None':
						echo 'None';
						break;
					case 'Gang':
						echo 'Gang (2-10)';
						break;
					case 'Skirmish':
						echo 'Skirmish (11-50)';
						break;
					case 'Fleet':
						echo 'Fleet (51-250)';
						break;
					case 'Command':
						echo 'Command (250+)';
						break;
						
					case 'Blops':
						echo 'Black Ops';
						break;
					case 'Incursions':
						echo 'Incursions';
						break;
					case 'Ganking':
						echo 'Ganking';
						break;
					case 'Wardecs':
						echo 'Wardecs';
						break;
					default:
						
						break;
				} ?></p>
			</div>
			
			&nbsp;<br>
			
			<div class="col-sm-12">
				<label>Previous NPSI Experience:</label>
				<p><?php echo $application['NPSI_Experience']; ?></p>
			</div>
			
			&nbsp;<br>
