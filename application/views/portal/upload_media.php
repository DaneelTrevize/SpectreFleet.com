
			<h2>Upload Media</h2>
			
			<?php if( isset($_SESSION['flash_message']) )
			{
				echo '<h3>Notifications</h3><p>' . $_SESSION['flash_message'] . '</p><br>';
			} ?>
			
			<p>Article images should be between 1:1 and 16:9 aspect ratio. Banner images should be between 4:1 and 6:1 aspect ratio.<br>
			<br>
			Filetypes are limited to <code>.png</code>, <code>.jpeg</code>, and <code>.gif</code> for images, and <code>.mp3</code> for audio files.<br>
			Video files are not currently accepted.<br>
			<br>
			Files must be uploaded using this utility before they can become usable during the article submission process.</p>
			
			<div class="col-sm-8 col-sm-offset-2">
			<?php echo form_open_multipart('upload/index'); ?>
				<div class="ui-input">
					<select name="filetype" class="form-control">
						<option value="">Select File Type</option>
						<option disabled value=""></option>
						<option value="audio">Audio</option>
						<option value="image">Image</option>
						<!--<option value="video">Video</option>-->
					</select>
					<br>
					<input type="file" name="userfile">
					<br>
					<input type="submit" name="submit" value="Upload File" class="btn btn-primary btn-block">
				</div>
			</form>

			<div class="row">
				<?php if( isset($error) ) { echo $error; } ?>
				<?php echo validation_errors(); ?>
			</div>
			
			</div>
