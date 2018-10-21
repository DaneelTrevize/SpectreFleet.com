
			<h1>Create Article</h1>
			
			<div class="row">
			<?php echo form_open('editor/create_article'); ?>
				Article Name
				<div class="ui-input<?php if(form_error('ArticleName')!=NULL){echo ' has-error';}?>">
					<input type="text" name="ArticleName" class="form-control" value="<?php echo set_value('ArticleName'); ?>">
				</div>
				Article Description
				<div class="ui-input<?php if(form_error('ArticleDescription')!=NULL){echo ' has-error';}?>">
					<input type="text" name="ArticleDescription" class="form-control" value="<?php echo set_value('ArticleDescription'); ?>">
				</div>
				Article Category
				<div class="ui-input<?php if(form_error('ArticleCategory')!=NULL){echo ' has-error';}?>">
					<select name="ArticleCategory" class="form-control">
						<?php foreach( $categories as $category )
						{
							echo '<option value="' . $category . '"';
								if( isset($ArticleCategory) && $ArticleCategory == $category ) echo ' selected';
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
								foreach($photos as $image)
								{
									echo '<option value="/uploads/'.$image.'"';		// Probably shouldn't have fragments of paths here
									if( isset($ArticlePhoto) && $ArticlePhoto == $image ) echo ' selected';
									echo '>'.$image.'</option>';
								}
							}
							?>
					</select>
				</div>
				Article Content
				<div class="ui-input col-sm-12">
					<textarea id="ckeditor" name="ArticleContent" class="form-control">
					<?php
					if( set_value('ArticleContent') )
					{
						echo set_value('ArticleContent');
					}
					else
					{
						echo 'Your work is not saved automatically! You can submit a Draft and continue to work on it before putting it forward for Review & Publishing.';
					}?>
					</textarea>
				</div>
				
				&nbsp;<br>
				&nbsp;<br>
				
				<div class="ui-input col-sm-6 col-sm-offset-3">
					<input type="submit" name="submit" value="Create Draft Article" class="btn btn-primary btn-block">
				</div>
			</form>
			</div>
			
			<div class="row">
				<br><?php echo validation_errors(); ?>
			</div>
			
			<script src="/js/start_CKEditor.js"></script>
