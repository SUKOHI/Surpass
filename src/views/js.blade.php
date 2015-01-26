<script type="text/javascript">

	var TU{{ $dir_studly }} = {
	
		ids: {
			input: '{{ $input_id }}', 
			preview: '{{ $preview_id }}'
		}, 
		maxFile: 0, 
		formData: {}, 
		previewParameters: {}, 
		processingFile: 0, 
		loadData: [], 
		overCallback: null, 
		overCallbackFlag: false, 
		progress: '', 
		init: function() {
			
			if(TU{{ $dir_studly }}.loadData.length > 0) {
				
				TU{{ $dir_studly }}.initialPreview();
				
			}
			
			$('#'+ TU{{ $dir_studly }}.ids['input']).fileupload({
				dataType: 'json',
				add: function (e, data) {
					
					if(TU{{ $dir_studly }}.processingFile < TU{{ $dir_studly }}.maxFile) {

						if(TU{{ $dir_studly }}.progress != '') {

							var loadingBox = tmpl('loading_box_{{ $dir }}', { content: TU{{ $dir_studly }}.progress });
							$('#'+ TU{{ $dir_studly }}.ids['preview']).append(loadingBox);

						}
						
						TU{{ $dir_studly }}.processingFile++;
						{{ (!empty($callbacks['upload'])) ? $callbacks['upload'] : '' }}
						data.submit();
						
					} else if(TU{{ $dir_studly }}.overCallbackFlag && $.isFunction(TU{{ $dir_studly }}.overCallback)) {
	
						TU{{ $dir_studly }}.overCallback();
						TU{{ $dir_studly }}.overCallbackFlag = false;
	
					}
					
				}, 
			    change: function (e, data) {
	
					TU{{ $dir_studly }}.overCallbackFlag = true;
					
			    },
				done: function (e, data) {
					
					if(TU{{ $dir_studly }}.progress != '') {
					
						$.each($('#'+ TU{{ $dir_studly }}.ids['preview']).children(), function(index, child){
							
							if(!$(child).find('.{{ $id_hidden_name }}').length) {
								
								child.remove();
								return false;
	
							}
	
						});

					}
					
					var file = data.files[0];
					
					if(data['result']['result']) {

						loadImage(file, function (img) {
								TU{{ $dir_studly }}.preview(img, data['result']['insertId'], file.name);
								{{ (!empty($callbacks['done'])) ? $callbacks['done'] : '' }}
							}, TU{{ $dir_studly }}.previewParameters
						);

					} else {

						TU{{ $dir_studly }}.processingFile--;
						{{ (!empty($callbacks['failed'])) ? $callbacks['failed'] : '' }}

					}
					
				}, 
				formData: TU{{ $dir_studly }}.formData
				
			});
	
		}, 
		preview: function(img, id, filename) {
	
			var previewBox = tmpl('preview_box_{{ $dir }}', {});
			var previewFooter = tmpl('preview_footer_{{ $dir }}', {surpassId: id, filename: filename});
			var content = $(previewBox).append(img).append(previewFooter);
			$('#'+ TU{{ $dir_studly }}.ids['preview']).append(content);
	
		}, 
		initialPreview: function() {
			
			TU{{ $dir_studly }}.processingFile = TU{{ $dir_studly }}.loadData.length;
			
			$.each(TU{{ $dir_studly }}.loadData, function(key, loadValues){

				var id = loadValues['id'];
				var url = loadValues['url'];
				var filename = loadValues['filename'];
				var img = $('<img/>', {
					src: url
				});
				loadImage(url, function (img) {
						TU{{ $dir_studly }}.preview(img, id, filename)
					}, TU{{ $dir_studly }}.previewParameters
				);
				
			});
			
		}, 
		remove: function(self, id) {
	
			var index = $(self).parent().index();
			var removeUrl = $('#'+ TU{{ $dir_studly }}.ids['input']).data('removeUrl');
			var formData = TU{{ $dir_studly }}.formData;
			formData['remove_id'] = id;
			
			$.post(removeUrl, TU{{ $dir_studly }}.formData, function(data){
	
				if(data['result']) {
	
					$(self).parent().remove();
					TU{{ $dir_studly }}.processingFile--;
	
				}
				{{ (!empty($callbacks['remove'])) ? $callbacks['remove'] : '' }}
				
			}, 'json');
			
			return false;
	
		}
	
	};
	
</script>
<script type="text/x-tmpl" id="preview_box_{{ $dir }}">
	<div{{ $css_div }}></div>
</script>
<script type="text/x-tmpl" id="loading_box_{{ $dir }}">
	<div{{ $css_loading }}>{%#o.content%}</div>
</script>
<script type="text/x-tmpl" id="preview_footer_{{ $dir }}">
	<br>
	{%=o.filename%}
	<br>
	<input class="{{ $id_hidden_name }}" type="hidden" name="{{ $id_hidden_name }}[]" value="{%=o.surpassId%}">
	<button{{ $css_button }} onclick="return TU{{ $dir_studly }}.remove(this, {%=o.surpassId%});">{{ $button_label }}</button>
</script>
<script>

	$(document).ready(function(){

		TU{{ $dir_studly }}.ids = {
			input: '{{ $input_id }}', 
			preview: '{{ $preview_id }}'
		};
		TU{{ $dir_studly }}.maxFile = {{ $max_file }};
		
		@if(!empty($load_data))

			TU{{ $dir_studly }}.loadData = {{ json_encode($load_data) }};
			
		@endif

		@if(!empty($form_data))

			TU{{ $dir_studly }}.formData = {{ json_encode($form_data) }};
			
		@endif

		@if(!empty($preview_params))

			TU{{ $dir_studly }}.previewParameters = {{ json_encode($preview_params) }};
			
		@endif

		@if(!empty($progress))

			TU{{ $dir_studly }}.progress = '{{ $progress }}';
			
		@endif
		
		TU{{ $dir_studly }}.overCallback = function(){
			alert('{{ $alert }}');
		};
		TU{{ $dir_studly }}.init();
		
	});
	
</script>