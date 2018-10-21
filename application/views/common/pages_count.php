				<div class="row">
					<div class="col-xs-12">
						<?php
						if( $results_on_page == 0 )
						{
							echo '<div class="aligncenter">';
							echo 'No additional results found.';
						}
						else
						{
							echo '<div class="pull-right">';
							echo 'Showing results '.(($page*$pageSize)+1).' to '.((($page+1)*$pageSize)-($pageSize-$results_on_page));
						} ?></div>
						<br><br>
					</div>
				</div>