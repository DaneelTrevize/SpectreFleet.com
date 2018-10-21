		<aside class="widget">

			<h2 class="widget-title ribbon"><span>Featured Posts</span></h2>

			<div class="entries row">
				
				<?php foreach ($featured as $feature): ?>
				
				<article class="type-post style-media-list media col-sm-6 col-md-12">

					<!-- to disable lazy loading, remove data-src -->
					<img src="/media/image/placeholder.gif" data-src="/media/image<?php echo $feature['ArticlePhoto']; ?>" width="128" height="80" class="media-object pull-left" alt="Placeholder Image">

					<!--fallback for no javascript browsers-->
					<noscript>
						<img src="/media/image<?php echo $feature['ArticlePhoto']; ?>" width="128" height="80" alt="Article Image">
					</noscript>

					<div class="media-body">
						<h3  class="entry-title">
							<a href="/articles/<?php echo $feature['ArticleID']; ?>" rel="bookmark"><?php echo $feature['ArticleName']; ?></a>
						</h3>
						<div class="entry-meta">
							<span class="entry-date"> on <?php echo substr($feature['DatePublished'],0,10); ?></span>
							<span class="category">In <a href="/articles/category/<?php echo $feature['ArticleCategory']; ?>"><?php echo $feature['ArticleCategory']; ?></a></span>
						</div>
					</div>
					
				</article>
				
				<?php endforeach ?>

			</div>

		</aside><!--/.widget-->