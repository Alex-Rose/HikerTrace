<div class="row">
	<div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
		<?php echo Form::open('AdminUpload/gpx/'.$id, ['enctype' => "multipart/form-data"]); ?>
		
		<div class="form-group">
			<div class="form-label">
				<label>GPX File</label>
			</div>
			<?php echo Form::file('file', ['class' => 'form-control']);?>
		</div>
			
		<div class="form-group">
				<?php echo Form::submit('upload', 'Upload', ['class' => 'btn btn-primary']);?>
		</div>
	</div>
</div>