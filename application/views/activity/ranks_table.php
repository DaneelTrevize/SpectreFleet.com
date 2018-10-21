
	<table class="table table-striped">
		<thead>
		<tr>
			<th class="col-md-4 aligncenter">Fleet Commander</th>
			<th class="col-md-4 aligncenter">New Rank</th>
			<th>Date</th>
			<th class="alignright">Time</th>
		</tr>
		</thead>
		<tbody>
		<?php
		//echo '<pre>'. print_r( $ranks_changes, TRUE ) .'</pre>';
		foreach( $ranks_changes as $rank_change )
		{
			echo '<tr>';
				// NewRole, users_role_log.timestamp, TargetUserID, users.CharacterName
				echo '<td class="aligncenter">';
				echo '<a href="/activity/FC/'.$rank_change->TargetUserID.'">';
				echo $rank_change->CharacterName.'</a>';
				echo '</td>';
				
				echo '<td class="aligncenter">';
				echo $rank_names[$rank_change->NewRole];
				echo '</td>';
				
				$datetime = DateTime::createFromFormat( 'Y-m-d H:i:s.ue', $rank_change->timestamp );
				echo '<td>' . $datetime->format( 'F jS' ) . '</td>';
				echo '<td class="alignright">' . $datetime->format( 'H:i' ) . '</td>';
			echo '</tr>';
		} ?>
		</tbody>
	</table>
	