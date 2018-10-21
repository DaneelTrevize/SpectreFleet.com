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

			<h3 class="page-title">
				Feedback successfully submitted!
			</h3>

		</header>
		
		<div class="col-md-8 col-md-offset-2 entry-content">
			
			<table class="table table_valign_m">
				<thead>
					<tr>
						<th class="aligncenter">Fleet Commander</th>
						<th class="aligncenter">Day & Date</th>
						<th class="aligncenter">Time</th>
						<th class="aligncenter">Rating</th>
					</tr>
				</thead>
				<tbody>
				<?php
				if( isset($feedback_data) )
				{
					echo '<tr>';
						$UserID = $feedback_data['UserID'];
						$CharacterID = $feedback_data['CharacterID'];
						$CharacterName = $feedback_data['CharacterName'];
						echo '<td class="aligncenter" style="white-space: nowrap;"><a href="/activity/FC/' .$UserID;
						echo '"><img class="img-rounded" src="https://imageserver.eveonline.com/Character/' .$CharacterID. '_64.jpg" alt="' .$CharacterName. '"> ' .$CharacterName. '</a></td>';
						
						$datetime = DateTime::createFromFormat( 'Y-m-d H:i:s', $feedback_data['Date'] );
						echo '<td class="aligncenter">'.$datetime->format( 'l F jS' ).'</td>';
						echo '<td class="aligncenter">'.$datetime->format( 'H:i' ).'</td>';
						
						$score = $feedback_data['Score'];
						$score_char = ( $score < 6 ) ? ( '-' ) : (( $score > 6 ) ? ( '+' ) : '=');
						echo '<td class="aligncenter"><span class="sf_rate_'.$score.'">'.$score_char.'</span></td>';
					echo '</tr>';
				}
				?>
				</tbody>
			</table>
			
			<div>
				<h3>Feedback details:</h3>
				<p><?php echo '<td>'.$feedback_data['Feedback'].'</td>'; ?></p>
				<br>
			</div>
		
		</div><!--/.col-md-10 entry-content-->

	</div>

</div><!--/#content-->