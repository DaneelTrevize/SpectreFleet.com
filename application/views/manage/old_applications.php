
			<h2>Legacy Spectre FC Applications</h2>
			
			<div class="row">
				<div class="col-sm-12">
					<table class="table table-striped table_valign_m">
						<thead>
							<tr>
								<th>ID</th>
								<th>Date</th>
								<th class="col-sm-2">Name</th>
								<th class="col-sm-3">Information</th>
								<th class="col-sm-4">Comments</th>
								<th class="col-sm-2 text-center">Status</th>
							</tr>
						</thead>
						<tbody>
						<?php
						foreach( $applications as $row )
						{
							echo '<tr>';
								echo '<td><a href="/manage/application/'.$row['ApplicationID'].'">'.$row['ApplicationID'].'</a></td>';
								echo '<td>'.substr($row['DateSubmitted'],5,5).'</td>';
								echo '<td>'.$row['CharacterName'].'</td>';
								echo '<td><div style="white-space:nowrap;">Timezone: '.$row['Timezone'];
								echo '<br>FC Experience: '.$row['FC_Experience'];
								echo '<br>NPSI Experience: '.$row['NPSI_Experience'].'</div></td>';
								echo '<td>'.$row['Comments'].'</td>';
								echo '<td class="text-center">'.$row['Status'].'</td>';
								?>
							</tr>
							</form><?php
						}
						?>
						</tbody>
					</table>
				</div>
			</div>
