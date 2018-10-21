		<aside id="reviewCarousel" class="widget carousel slide">

			<h2 class="widget-title ribbon"><span>What's New?</span></h2>

			<div class="carousel-inner">

				<?php
				for( $i = 0; $i < count($whats_new); $i++ )
				{
					$new = $whats_new[$i];
					?>

				<div class="item<?php if( $i == 0 ) echo ' active"'; ?>">
					
					<article class="entry style-grid style-review type-post">
		
						<header class="entry-header">
							<h3 class="entry-title"><a href="/articles/<?php echo $new['ArticleID']; ?>"><?php echo $new['ArticleName']; ?></a> </h3>
							<p class="small"><?php echo $new['ArticleDescription']; ?></p>
						</header>

						<div class="style-review-score">
							<?php echo $i+1; ?>
						</div>
									
						<figure class="entry-thumbnail">

							<a href="/join_command" class="overlay overlay-primary"></a>

							<!-- to disable lazy loading, remove data-src -->
							<img src="/media/image/placeholder.gif" data-src="/media/image<?php echo $new['ArticlePhoto']; ?>" width="480" height="280" alt="Placeholder Image">

							<!--fallback for no javascript browsers-->
							<noscript>
								<img src="/media/image<?php echo $new['ArticlePhoto']; ?>" alt="Article Image">
							</noscript>

						</figure>

					</article>

				</div><!--/.item.active-->
				
				<?php
				} ?>
				
			</div><!--/.carousel-inner-->

			<a class="left carousel-control" href="#reviewCarousel" data-slide="prev">
				<span><span class="fa fa-chevron-left"></span></span>
			</a>
			<a class="right carousel-control" href="#reviewCarousel" data-slide="next">
				<span><span class="fa fa-chevron-right"></span></span>
			</a>

		</aside><!--/.widget.carousel-->