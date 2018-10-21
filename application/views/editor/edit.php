
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
			<div class="row col-md-12">
			<hr><br>
				<?php echo form_open('editor/edit_article/'.$submission['SubmissionID']); ?>
					Article Name
					<div class="ui-input<?php if(form_error('ArticleName')!=NULL){echo ' has-error';}?>">
						<input type="text" name="ArticleName" class="form-control" value="<?php echo set_value('ArticleName', $submission['ArticleName'],FALSE); ?>">
					</div>
					Article Description
					<div class="ui-input<?php if(form_error('ArticleDescription')!=NULL){echo ' has-error';}?>">
						<input type="text" name="ArticleDescription" class="form-control" value="<?php echo set_value('ArticleDescription', $submission['ArticleDescription'],FALSE); ?>">
					</div>
					Article Category
					<div class="ui-input<?php if(form_error('ArticleCategory')!=NULL){echo ' has-error';}?>">
						<select name="ArticleCategory" class="form-control">
							<?php foreach( $categories as $category )
							{
								echo '<option value="' . $category . '"';
								if( $category === $submission['ArticleCategory'] ) echo ' selected';
								echo '>' . $category . '</option>';
							}
							?>
						</select>
					</div>
					Article Photo
					<div class="ui-input<?php if(form_error('ArticlePhoto')!=NULL){echo ' has-error';}?>">
						<select name="ArticlePhoto" class="form-control">
							<option value="/articles/banner-2.jpg">Default</option>
							<option disabled value=""></option>
								<?php
								if( $photos != NULL )
								{
									foreach($photos as $row)
									{
										echo '<option value="/uploads/'.$row.'"';
										if( $row === $submission['ArticlePhoto'] ) echo ' selected';
										echo '>'.$row.'</option>';
									}
								}
								?>
						</select>
					</div>
					Article Content
					<div class="ui-input col-sm-12">
						<textarea id="ckeditor" name="ArticleContent" class="form-control"><?php echo set_value('ArticleContent', $submission['ArticleContent'],FALSE); ?></textarea>
					</div>
				
					&nbsp;<br>
					&nbsp;<br>
					
					<?php echo form_hidden('SubmissionID', $submission['SubmissionID']); ?>
					
					<div class="ui-input col-sm-6 col-sm-offset-3">
						<input type="submit" name="submit" value="Submit Edits" class="btn btn-primary btn-block">
					</div>
				</form>
				
				<script src="/js/start_CKEditor.js"></script>
			</div>
			
			<div class="row">
				<br><?php echo validation_errors(); ?>
			</div>
