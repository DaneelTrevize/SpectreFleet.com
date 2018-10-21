<div id="content" class="content section row">

	<div class="col-md-8 bg-base col-lg-8 col-xl-9">

		<div class="ribbon ribbon-highlight">
			<ol class="breadcrumb ribbon-inner">
				<li><a href="/">Home</a></li>
				
				<?php if( $category == 'All Articles' )
				{
					echo '<li><a href="/articles">All Articles</a></li>';
				}
				else
				{
					echo '<li><a href="/articles/category/'.$category.'">'.$category.'</a></li>';
				}?>
					
			</ol>
		</div>
		
		<?php
		if( !empty($articles) )
		{ ?>
		<div class="entries">

			<?php foreach ($articles as $article): ?>
					
			<article class="entry style-media media type-post">

				<figure class="media-object pull-left entry-thumbnail hidden-xs">

					<!-- to disable lazy loading, remove data-src -->
					<img src="/media/image/placeholder.gif" data-src="/media/image<?php echo $article['ArticlePhoto']; ?>" style="height:100%;width:150px;" alt="Placeholder Image">

					<!--fallback for no javascript browsers-->
					<noscript>
						<img src="/media/image<?php echo $article['ArticlePhoto']; ?>" style="height:100%;width:150px;" alt="Article Image">
					</noscript>

				</figure>

				<div class="media-body">

					<header class="entry-header">
						<h3 class="entry-title">
							<a href="/articles/<?php echo $article['ArticleID']; ?>" rel="bookmark"><?php echo $article['ArticleName']; ?></a>
						</h3>

						<div class="entry-meta">
							<span class="author">by <?php echo $article['CharacterName']; ?></span>
							<span class="entry-date">on <?php echo substr($article['DatePublished'],0,10); ?></span>
							<span class="category">In <a href="/articles/category/<?php echo $article['ArticleCategory']; ?>"><?php echo $article['ArticleCategory']; ?></a></span>
						</div>
					</header>

					<p>
						<?php echo $article['ArticleDescription']; ?>
						<a href="/articles/<?php echo $article['ArticleID']; ?>" class="more-link">Continue Reading<span class="fa fa-long-arrow-right"></span></a>
					</p>

				</div>

			</article>
			
			<?php endforeach; ?>
			
		</div><!--/.entries-->
		
		<div class="row" style="padding-bottom:20px;">
		
			<div class="col-md-4 col-sm-6">
				<?php if( $Page > 0 )
				{
					if( $category == 'All Articles' )
					{
						echo '<h2><a href="/articles/page/'.($Page-1).'">&laquo; Prev Page</a></h2>';
					}
					else
					{
						echo '<h2><a href="/articles/category/'.$category.'/'.($Page-1).'">&laquo; Prev Page</a></h2>';
					}
				} ?>
			</div>
			
			<div class="col-md-4 hidden-sm">
				<!-- Place page counter here -->
			</div>
			
			<div class="col-md-4 col-sm-6">
				<?php if( $Page >= 0 )		// Next Page link should be conditional on more pages existing?
				{
					if( $category == 'All Articles' )
					{
						echo '<h2><a class="pull-right" href="/articles/page/'.($Page+1).'">Next Page &raquo;</a></h2>';
					}
					else
					{
						echo '<h2><a class="pull-right" href="/articles/category/'.$category.'/'.($Page+1).'">Next Page &raquo;</a></h2>';
					}
				} ?>
			</div>
			
			<br>
			
		</div>
		<?php
		}
		else
		{ ?>
			<p>
				No furthur articles found in this category.
			</p>
		<?php
		} ?>

	</div>

	<div class="sidebar col-md-4 col-lg-4 col-xl-3">
		
		<?php echo $whats_new_html; ?>
		
		<?php echo $featured_side_html; ?>

		<aside class="widget">

			<h2 class="widget-title ribbon"><span>What's Hot</span></h2>

			<div class="entries row">

				<?php $i=0; foreach ($hottest as $hot): ?>
			
				<article class="type-post style-media-list media col-sm-6 col-md-12">

					<!-- to disable lazy loading, remove data-src -->
					<img src="/media/image/placeholder.gif" data-src="/media/image<?php echo $hot['ArticlePhoto']; ?>" width="128" height="80" class="media-object pull-left" alt="Placeholder Image">

					<!--fallback for no javascript browsers-->
					<noscript>
						<img src="/media/image<?php echo $hot['ArticlePhoto']; ?>" width="128" height="80" alt="Article Image">
					</noscript>

					<div class="media-body">
						<h3  class="entry-title">
							<a href="/articles/<?php echo $hot['ArticleID']; ?>" rel="bookmark"><?php echo $hot['ArticleName']; ?></a>
						</h3>
						<div class="entry-meta">
							<span class="entry-date"> on <?php echo substr($hot['DatePublished'],0,10); ?></span>
							<span class="category">In <a href="/articles/category/<?php echo $hot['ArticleCategory']; ?>"><?php echo $hot['ArticleCategory']; ?></a></span>
						</div>
					</div>

				</article>
				
				<?php endforeach ?>

			</div>

		</aside><!--/.widget-->

		<aside class="widget">

			<h3 class="widget-title ribbon"><span>Featured Discussion</span></h3>

			<ul class="entries">

				<?php $i=0; foreach ($featured as $feature): ?>
			
				<li class="entry style-recent-list type-post">

					<a href="/articles/<?php echo $feature['ArticleID']; ?>" rel="bookmark" class="entry-title"><?php echo $feature['ArticleName']; ?></a>

				</li>

				<?php endforeach ?>
				
			</ul>

		</aside><!--/.widget-->

	</div><!--/.sidebar col-md-4 col-lg-4 col-xl-3-->
	
</div><!--/#content-->