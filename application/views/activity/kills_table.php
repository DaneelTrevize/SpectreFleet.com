	<?php
	if( empty($enhanced_kills) )
	{
		echo '<p>No highlighted kills.</p>';
	}
	else
	{ ?>
	<table class="table table-striped table-hover kills">
		<thead>
		<tr>
			<th class="col-md-2 alignright">Value</th>
			<th class="aligncenter">Ship</th>
			<th class="aligncenter">Victim</th>
			<th class="hidden-md col-md-3 aligncenter">Location</th>
			<th class="aligncenter">Involved</th>
		</tr>
		</thead>
		<tbody>
	<?php
	foreach( $enhanced_kills as $kill )
	{
		//echo '<pre>'. print_r( $kill, TRUE ) .'</pre>';
		echo '<tr>';
			
			$a = '<a href="https://zkillboard.com/kill/'. $kill->ID .'">';
			$e_a = '</a>';
			
			echo '<td class="alignright text-nowrap">' . $a;
			if( property_exists( $kill, 'totalValue_text' ) )
			{
				echo $kill->totalValue_text;
			}
			else
			{
				echo '???';
			}
			echo $e_a . '</td>';
			
			echo '<td class="aligncenter">';
			echo '<div>' . $a;
			echo '<img src="https://imageserver.eveonline.com/Type/'.$kill->victim_ship_type_ID.'_64.png" class="img-rounded img40px" title="'.$kill->victim_ship_type_name.'">';
			echo $e_a . '</div>' . '</td>';
			
			echo '<td class="aligncenter">';
			echo '<div>' . $a;
			$victim_character_ID = ($kill->victim_character_ID != NULL) ? $kill->victim_character_ID : '1';
			echo '<img src="https://imageserver.eveonline.com/Character/'.$victim_character_ID.'_64.jpg" class="img-rounded img40px" title="'.$kill->victim_name.'">';
			echo $e_a . '</div>' . '</td>';
			
			echo '<td class="hidden-md aligncenter">' . $a;
			echo $kill->solar_system_name;
			echo $e_a . '</td>';
			
			echo '<td class="aligncenter">' . $a . $kill->attackers_count . $e_a . '</td>';
		echo "</tr>\n";
	}
	?>
		</tbody>
	</table>
	<?php
	} ?>
	