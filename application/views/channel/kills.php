<?php
if( !empty($kills) || !empty($enhanced_kills) )
{
	echo '<h2>Highlighted Kills</h2>';
	
	if( !empty($enhanced_kills) )
	{ ?>
		<table class="table table-striped table_valign_m">
			<thead>
			<tr>
				<th class="col-md-2 alignright">Value</th>
				<th class="aligncenter">Ship</th>
				<th class="aligncenter">Victim</th>
				<th class="col-md-3 aligncenter">Location</th>
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
				
				echo '<td class="aligncenter">' . $a;
				echo '<img src="https://imageserver.eveonline.com/Type/'.$kill->victim_ship_type_ID.'_64.png" class="img-rounded img40px" title="'.$kill->victim_ship_type_name.'">';
				echo $e_a . '</td>';
				
				echo '<td class="aligncenter">' . $a;
				echo '<img src="https://imageserver.eveonline.com/Character/'.$kill->victim_character_ID.'_64.jpg" class="img-rounded img40px" title="'.$kill->victim_name.'">';
				echo $e_a . '</td>';
				
				echo '<td class="aligncenter">' . $a;
				echo $kill->solar_system_name;
				echo $e_a . '</td>';
				
				echo '<td class="aligncenter">' . $a . $kill->attackers_count . $e_a . '</td>';
			echo "</tr>\n";
		}
		?>
			</tbody>
		</table>
	<?php
	}
	
	if( !empty($kills) )
	{
		foreach( $kills as $kill )
		{
			echo '<a href="https://zkillboard.com/kill/' .$kill['ID']. '/">' .$kill['text']. "</a><br>\n";
		}
	}
	
}
?>