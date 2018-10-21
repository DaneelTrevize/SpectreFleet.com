					<div class="col-lg-4 col-md-6 col-sm-6">
						<?php
						$total = 0;
						$table_html = '
						<table class="table table-striped table_valign_m table_offgrid" data-sf-class-id="'.$sf_class.'" data-sf-class-name="'.$sf_class_name.'">
							<thead>
							<tr>
								<th class="aligncenter">Group</th>
								<th class="aligncenter">Σ</th>
								<th class="aligncenter" colspan="2">Type</th>
								<th class="aligncenter">Σ</th>
							</tr>
							</thead>
							<tbody>';
						foreach( $fleet_class as $summary )
						{
							$ship_type_id = $summary['ship_type_id'];
							$type_count = $summary['type_count'];
							$groupID = $summary['groupID'];
							$typeName = $summary['typeName'];
							$table_html .= '<tr data-eve-type-id="' .$ship_type_id. '" data-eve-type-volume="' .$summary['volume']. '" data-eve-group-id="' .$groupID. '" data-eve-category-id="' .$summary['categoryID']. '">';
								$total += $type_count;
								
								$table_html .= '<td class="aligncenter"><img src="/dscan/image/' .$sf_class. '/' .$groupID. '" title="'.$summary['groupName'].'"></td>';
								$table_html .= '<td class="aligncenter">'.number_format($group_counts[$groupID]).'</td>';
								
								$table_html .= '<td class="aligncenter"><img class="img-rounded img40px" src="https://imageserver.eveonline.com/Type/'.$ship_type_id.'_32.png" title="'.$typeName.'"></td>';
								$table_html .= '<td class="type-name">'.$typeName.'</td>';
								$table_html .= '<td class="aligncenter">'.number_format($type_count).'</td>';
								
							$table_html .= "</tr>\n";
						}
						$table_html .= '
							</tbody>
						</table>';
						
						echo '<h4>'.$sf_class_name.': <span id="fleet_sf_class_'.$sf_class.'_count">'.$total."</span></h4>\n";
						
						echo $table_html;
						?>
					</div>
