<div id="content" class="content bg-base section">
	
	<div class="ribbon ribbon-highlight">
		<ol class="breadcrumb ribbon-inner">
			<li><a href="/">Home</a></li>
			<li><a href="/articles/category/<?php echo $article['ArticleCategory']; ?>"><?php echo $article['ArticleCategory']; ?></a></li>
			<li class="active"><?php echo $article['ArticleName']; ?></li>
		</ol>
	</div>
	
	<div class="row">

		<header class="page-header col-md-10 col-md-offset-1">

			<h2 class="page-title full-page-title">
				<?php echo $article['ArticleName']; ?>
			</h2>

		</header>
		
		<article class="entry style-single style-single-full type-post col-md-10 col-md-offset-1">

			<figure class="entry-thumbnail">

				<img src="/media/image/banner/default-banner.jpg" alt="Article Banner Image">
				
				<div class="entry-meta">
					<span class="author">by <?php echo $article['Username']; ?></span>
					<span class="entry-date">on <?php echo substr($article['DatePublished'],0,10); ?></span>
					<span class="category">in <a href="/articles/category/<?php echo $article['ArticleCategory']; ?>"><?php echo $article['ArticleCategory']; ?></a></span>
				</div>
				
			</figure>
		
			<div class="entry-content">

				<?php echo $article['ArticleContent']; ?>
		
			</div>
			
		</article>

	</div>

</div><!--/#content-->