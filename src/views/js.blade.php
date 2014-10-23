<script type="text/javascript">

	var TU = {
	
		ids: {
			input: '{{ Surpass::renderId('input') }}', 
			preview: '{{ Surpass::renderId('preview') }}'
		}, 
		maxFile: 0, 
		formData: {}, 
		previewParameters: {}, 
		processingFile: 0, 
		loadData: [], 
		overCallback: null, 
		overCallbackFlag: false, 
		init: function() {
			
			if(TU.loadData.length > 0) {
				
				TU.initialPreview();
				
			}
			
			$('#'+ TU.ids['input']).fileupload({
				dataType: 'json',
				add: function (e, data) {
					
					if(TU.processingFile < TU.maxFile) {
	
						TU.processingFile++;
						data.submit();
						
					} else if(TU.overCallbackFlag && $.isFunction(TU.overCallback)) {
	
						TU.overCallback();
						TU.overCallbackFlag = false;
	
					}
					
				}, 
			    change: function (e, data) {
	
					TU.overCallbackFlag = true;
					
			    },
				done: function (e, data) {

					var file = data.files[0];
					
					if(data['result']['result']) {

						loadImage(file, function (img) {
								TU.preview(img, data['result']['insertId'], file.name)
							}, TU.previewParameters
						);

					} else {

						TU.processingFile--;

					}
					
				}, 
				formData: TU.formData
				
			});
	
		}, 
		preview: function(img, id, filename) {
	
			var previewBox = tmpl('preview_box', {});
			var previewFooter = tmpl('preview_footer', {surpassId: id, filename: filename});
			var content = $(previewBox).append(img).append(previewFooter);
			$('#'+ TU.ids['preview']).append(content);
	
		}, 
		initialPreview: function() {
			
			TU.processingFile = TU.loadData.length;
			
			$.each(TU.loadData, function(key, loadValues){

				var id = loadValues['id'];
				var url = loadValues['url'];
				var filename = loadValues['filename'];
				var img = $('<img/>', {
					src: url
				});
				loadImage(url, function (img) {
						TU.preview(img, id, filename)
					}, TU.previewParameters
				);
				
			});
			
		}, 
		remove: function(self, id) {
	
			var index = $(self).parent().index();
			var removeUrl = $('#'+ TU.ids['input']).data('removeUrl');
			var formData = TU.formData;
			formData['remove_id'] = id;
			
			$.post(removeUrl, TU.formData, function(data){
	
				if(data['result']) {
	
					$(self).parent().remove();
					TU.processingFile--;
	
				}
				
			}, 'json');
			
			return false;
	
		}
	
	};
	
</script>
<script type="text/x-tmpl" id="preview_box">
	<div{{ Surpass::renderCss('div') }}></div>
</script>
<script type="text/x-tmpl" id="preview_footer">
	<br>
	{%=o.filename%}
	<br>
	<input type="hidden" name="surpass_ids[]" value="{%=o.surpassId%}">
	<button{{ Surpass::renderCss('button') }} onclick="return TU.remove(this, {%=o.surpassId%});">{{ $button_label }}</button>
</script>
<script>

	$(document).ready(function(){

		TU.ids = {
			input: '{{ Surpass::renderId('input') }}', 
			preview: '{{ Surpass::renderId('preview') }}'
		};
		TU.maxFile = {{ $max_file }};
		
		@if(!empty($load_data))

			TU.loadData = {{ json_encode($load_data) }};
			
		@endif

		@if(!empty($form_data))

			TU.formData = {{ json_encode($form_data) }};
			
		@endif

		@if(!empty($preview_params))

			TU.previewParameters = {{ json_encode($preview_params) }};
			
		@endif
		
		TU.overCallback = function(){
			alert('{{ $alert }}');
		};
		TU.init();
		
	});
	
</script>