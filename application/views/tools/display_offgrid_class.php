					<div class="col-lg-3 col-md-4 col-sm-6">
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
						foreach( $offgrid_class as $summary )
						{
							$table_html .= '<tr data-eve-type-id="' .$summary['typeID']. '" data-eve-type-volume="' .$summary['volume']. '" data-eve-group-id="' .$summary['groupID']. '" data-eve-category-id="' .$summary['categoryID']. '">';
								$total += $summary['count'];
								
								$table_html .= '<td class="aligncenter"><img src="/dscan/image/' .$sf_class. '/' .$summary['groupID']. '" title="'.$summary['groupName'].'"></td>';
								$table_html .= '<td class="aligncenter"></td>';	// Group count
								
								$table_html .= '<td class="aligncenter"><img class="img-rounded" src="https://imageserver.eveonline.com/Type/'.$summary['typeID'].'_32.png" title="'.$summary['typeName'].'"></td>';
								$table_html .= '<td class="type-name">'.$summary['typeName'].'</td>';
								$table_html .= '<td class="aligncenter">'.number_format($summary['count']).'</td>';
								
							$table_html .= "</tr>\n";
						}
						$table_html .= '
							</tbody>
						</table>';
						
						echo '<h4>'.$sf_class_name.': <span id="offgrid_sf_class_'.$sf_class.'_count">'.$total."</span></h4>\n";
						
						echo $table_html;
						?>
					</div>
