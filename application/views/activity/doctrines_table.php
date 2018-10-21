<?php
if( !empty( $doctrines_changes ) )
{ ?>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>&nbsp;</th>
			<?php
			if( !$single_FC )
			{ ?>
				<th class="col-md-5">Title</th>
				<th class="col-md-4 aligncenter">Fleet Commander</th>
			<?php
			}
			else
			{ ?>
				<th class="col-md-7">Title</th>
			<?php
			} ?>
			<th>Date</th>
			<th class="alignright">Time</th>
		</tr>
		</thead>
		<tbody>
		<?php
		//echo '<pre>'. print_r( $doctrines_changes, TRUE ) .'</pre>';
		foreach( $doctrines_changes as $doctrine_change )
		{
			echo '<tr>';
				// FitNotFleet, ID, F_Name, lastEdited, CharacterName
				echo '<td>';
				if( $doctrine_change->FitNotFleet === 't' )
				{
					echo '<i class="fa fa-user fa-fw" aria-hidden="true"></i>';
				}
				else
				{
					echo '<i class="fa fa-users fa-fw" aria-hidden="true"></i>';
				}
				echo '</td>';
				
				echo '<td>';
				echo '<a href="/doctrine/';
				if( $doctrine_change->FitNotFleet === 't' )
				{
					echo 'fit/'. $doctrine_change->ID .'">';
				}
				else
				{
					echo 'fleet/'. $doctrine_change->ID .'">';
				}
				//echo $doctrine_change->FitNotFleet .'">';
				echo $doctrine_change->F_Name .'</a>';
				echo '</td>';
				
				if( !$single_FC )
				{
					echo '<td class="aligncenter">';
					echo '<a href="/activity/FC/'.$doctrine_change->userID.'">';
					echo $doctrine_change->CharacterName.'</a>';
					echo '</td>';
				}
				
				$datetime = DateTime::createFromFormat( 'Y-m-d H:i:s', $doctrine_change->lastEdited );
				echo '<td>' . $datetime->format( 'F jS' ) . '</td>';
				echo '<td class="alignright">' . $datetime->format( 'H:i' ) . '</td>';
			echo '</tr>';
		} ?>
		</tbody>
	</table>
<?php
} ?>