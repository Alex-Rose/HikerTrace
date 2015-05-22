<div class="row">
	<div class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1">
		<?php echo Form::open('AdminUpload/create', ['enctype' => "multipart/form-data"]);?>
			
		<div class="form-group">
			<div class="form-label">
				<label>Name</label>
			</div>
			<input name="name" type="text" class="form-control">
		</div>
		
		<div class="form-group">
			<div class="form-label">
				<label>Description</label>
			</div>
			<input name="description" type="text" class="form-control">
		</div>
		
		<div class="form-group">
				<?php echo Form::submit('save', 'Create', ['class' => 'btn btn-primary']);?>
		</div>
			
		<?php echo Form::close(); ?>
		
	</div>
</div>

<div id="feedback"></div>
	

<script>

	$(document).ready(function(){
		$('input:submit[name=save]').click(function(e){
			e.preventDefault();
			var url = $(this).closest('form').attr('action');
			var name = $('input[name=name]').val();
			var description = $('input[name=description]').val();
			$.ajax({
				url: url,
				type: 'POST',
				data:{ name: name, description: description}
			}).done(function(data){
				$('#feedback').html(data.feedback);
			});
		});
	});
	
	$(document).on('click', 'input:submit[name=upload]', function(e){
		e.preventDefault();
		uploadFile('file');
	});
	
	$(document).on('click', 'input:submit[name=upload_image]', function(e){
		e.preventDefault();
		uploadFile('image');
	});
	
	function uploadFile(name)
	{
		var input = $('input[name='+name+']');
		var file = document.getElementsByName(name)[0].files[0];
		var url = input.closest('form').attr('action');
		var xhr = new XMLHttpRequest();

		// var loader = $('#progress');
		// var progress = $('#value');
		// var bar = $('div.progress-bar');
		// var progressContainer = $('div.progress-container');

		// loader.show({easing: 'swing'});
		xhr.addEventListener('progress', function(e) {
			var done = e.loaded, total = e.total;
			var value =(Math.floor(done/total*1000)/1)
			var pcent = (value == Infinity ? 100 : value) + '%';
			// progress.html(pcent);
			// bar.css('width', pcent);
		}, false);
		if ( xhr.upload ) {
			xhr.upload.onprogress = function(e) {
				var done = e.loaded, total = e.total;
				var pcent = (Math.floor(done/total*100)/1) + '%';
				// progress.html(pcent);
				// bar.css('width', pcent);
			};
		}
		xhr.onreadystatechange = function(e) {
			if ( 4 == this.readyState ) {
				// loader.hide({easing: 'swing'});
				data = xhr.responseText
				// setTimeout(function(){progressContainer.hide({easing: 'swing'})}, 5000);

				//setTimeout(function(){progressContainer.hide({easing: 'swing'})}, 5000);
				
				getImageUploader();
			}
		};

		var formData = new FormData();
		formData.append(name, file);


		xhr.open('post', url, true);
		xhr.send(formData);
	}
	

</script>