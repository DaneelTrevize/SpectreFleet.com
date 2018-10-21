<div class="section row entries bg-primary section-no-margin-bottom">
<?php
$first = TRUE;
foreach( $featured as $article )
{
	if( $first )
	{
		echo '<article class="entry style-grid style-hero hero-sm-largest type-post col-sm-12 col-md-6 colheight-sm-1 colheight-md-2 colheight-lg-2 colheight-xl-2">';
		
		$first = FALSE;
	}
	else
	{
		echo '<article class="entry style-grid style-hero type-post col-md-6 col-lg-3 colheight-sm-1">';
	} ?>
		<div class="ribbon ribbon-pulled ribbon-small ribbon-highlight">
			<a href="/articles/category/<?php echo $article['ArticleCategory']; ?>"><?php echo $article['ArticleCategory']; ?></a>
		</div>

		<header class="entry-header">
			<h3 class="entry-title"><a href="/articles/<?php echo $article['ArticleID']; ?>"><?php echo $article['ArticleName']; ?></a> </h3>
			<div class="entry-meta">
				<span class="entry-date">On <?php echo substr($article['DatePublished'],0,10); ?></span>
				<span class="entry-author"> by <?php echo $article['CharacterName']; ?></span>
			</div>
		</header>

		<figure class="entry-thumbnail">

			<a href="/articles/<?php echo $article['ArticleID']; ?>" class="overlay overlay-primary"></a>

			<!-- to disable lazy loading, remove data-src -->
			<img src="../media/image/placeholder.gif" data-src="/media/image<?php echo $article['ArticlePhoto']; ?>" width="680" height="452" alt="Placeholder Image">

			<!--fallback for no javascript browsers-->
			<noscript>
				<img src="/media/image<?php echo $article['ArticlePhoto']; ?>" alt="Article Image">
			</noscript>

		</figure>

	</article>
	<?php
} ?>
</div>