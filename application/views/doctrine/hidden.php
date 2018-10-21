<div id="EFT" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title"><?php echo $info['fitName']; ?></h4>
			</div>
			<div class="modal-body">
				<form name="myform">
					<div class="form-group">
						<textarea onclick="this.select();" class="col-md-12" style="line-height:14px;" rows="15" readonly="readonly"><?php echo $EFT; ?></textarea>
					</div>
					&nbsp;<br>
					<div class="form-group aligncenter">
						<a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>