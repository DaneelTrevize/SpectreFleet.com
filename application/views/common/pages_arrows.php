				<div class="row" style="margin-bottom:20px;">
		
					<div class="col-xs-4 text-right">
						<?php if( $page > 0 )
						{
							echo '<a href="' .$search_string. '&page='.($page-1).'">&laquo; Prev Page</a>';
						} ?>
					</div>
					
					<div class="col-xs-4">
						<div class="aligncenter" style="font-size:24px;">Page&nbsp;<?php echo ($page+1); ?></div>
					</div>
					
					<div class="col-xs-4">
						<?php if( $page >= 0 && $results_on_page == $pageSize )
						{
							echo '<a href="' .$search_string. '&page='.($page+1).'">Next Page &raquo;</a>';
						} ?>
					</div>
					
					<br>
					
				</div>