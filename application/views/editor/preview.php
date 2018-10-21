
			<div class="row col-md-12">
			
				<header class="page-header">

					<h4 class="page-title full-page-title">
						<?php echo $submission['ArticleName']; ?>
					</h4>

				</header>
				
				<article class="entry style-single style-single-full type-post">

					<figure class="entry-thumbnail">

						<!-- to disable lazy loading, remove data-src and data-src-retina -->
						<img src="/media/image/placeholder.gif" data-src="/media/image/banner/default-banner.jpg" data-src-retina="/media/image/banner/default-banner.jpg" alt="">

						<!--fallback for no javascript browsers-->
						<noscript>
							<img src="/media/image/banner/default-banner.jpg" alt="">
						</noscript>

					</figure>
				
					<hr>
				
					<div class="entry-meta">
						<span class="author">by <?php echo $submission['Username']; ?></span>
						<span class="entry-date">on <?php echo date('Y-m-d'); ?></span>
						<span class="category">in <?php echo $submission['ArticleCategory']; ?></span>
						<hr>
					</div>
				
					<div class="entry-content">

						<?php echo $submission['ArticleContent']; ?>
				
					</div>
					
				</article>
			
			</div>
