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
        overwriteFlag: {{ ($overwrite) ? 'true' : 'false' }},
        overwritePreviewBox: null,
        @if(!empty($drop_zone_id))
        dropZone: $('#{{ $drop_zone_id }}'),
        @endif

        init: function() {

            if(TU{{ $dir_studly }}.loadData.length > 0) {

                TU{{ $dir_studly }}.initialPreview();

            }

            $('#'+ TU{{ $dir_studly }}.ids['input']).fileupload({
                dataType: 'json',
                @if(!empty($resize_params['size']))
                disableImageResize: false,
                @endif
                @if(!empty($resize_params['size']['maxWidth']))
                imageMaxWidth: {{ intval($resize_params['size']['maxWidth']) }},
                @endif
                @if(!empty($resize_params['size']['maxHeight']))
                imageMaxHeight: {{ intval($resize_params['size']['maxHeight']) }},
                @endif
                @if(!empty($resize_params['force_crop']))
                imageCrop: {{ ($resize_params['force_crop']) ? 'true' : 'false' }},
                @endif
                @if($timeout > 0)
                timeout: {{ $timeout }},
                @endif
                add: function(e, data) {

                    var fileType = data.files[0].type;

                    if(fileType.indexOf('image/') === 0) {

                        if(!TU{{ $dir_studly }}.isFull()) {

                            if(TU{{ $dir_studly }}.progress != '') {

                                var loadingBox = tmpl('loading_box_{{ $dir }}', { content: TU{{ $dir_studly }}.progress });
                                $('#'+ TU{{ $dir_studly }}.ids['preview']).append(loadingBox);

                            }

                            TU{{ $dir_studly }}.processingFile++;
                            {{ (!empty($callbacks['upload'])) ? $callbacks['upload'] : '' }}
                            $.blueimp.fileupload.prototype.options.add.call(this, e, data);
                            data.submit();

                        } else if(TU{{ $dir_studly }}.overCallbackFlag && $.isFunction(TU{{ $dir_studly }}.overCallback)) {

                            TU{{ $dir_studly }}.overCallback();
                            TU{{ $dir_studly }}.overCallbackFlag = false;

                        }

                    } else {

                        {{ (!empty($callbacks['file_type_error'])) ? $callbacks['file_type_error'] : '' }}

                    }

                },
                error: function(e, data) {

                    {{ (!empty($callbacks['timeout'])) ? $callbacks['timeout'] : '' }}
                    this.done(e, {result: {result: false}, files: []});

                },
                change: function (e, data) {

                    TU{{ $dir_studly }}.overCallbackFlag = true;

                },
                drop: function (e, data) {

                    TU{{ $dir_studly }}.overCallbackFlag = true;

                },
                done: function (e, data) {

                    if(TU{{ $dir_studly }}.progress != '') {

                        $.each($('#'+ TU{{ $dir_studly }}.ids['preview']).children(), function(index, child){

                            if(!$(child).find('.{{ $id_hidden_name }}').length) {

                                $(child).remove();
                                return false;

                            }

                        });

                    }

                    var file = (typeof(data.files[0]) == 'undefined') ? null : data.files[0];

                    if(file != null && data['result']['result']) {

                        loadImage(file, function (img) {
                                    TU{{ $dir_studly }}.preview(
                                            img,
                                            data['result']['insertId'],
                                            file.name,
                                            data['result']['saveMode']
                                    );
                                    {{ (!empty($callbacks['done'])) ? $callbacks['done'] : '' }}
                                }, TU{{ $dir_studly }}.previewParameters
                        );

                    } else {

                        TU{{ $dir_studly }}.processingFile--;
                        {{ (!empty($callbacks['failed'])) ? $callbacks['failed'] : '' }}

                    }

                }

            }).bind('fileuploadsubmit', function (e, data) {

                data.formData = TU{{ $dir_studly }}.formData;

            });

        },
        preview: function(img, id, filename, saveMode) {

            var previewBox = tmpl('preview_box_{{ $dir }}', {});
            var previewFooter = tmpl('preview_footer_{{ $dir }}', {surpassId: id, filename: filename});
            var hiddenObj = $('.{{ $id_hidden_name }}[value='+ id +']');

            if(hiddenObj.length) {

                hiddenObj.remove();

            }

            var content = $(previewBox).append(img).append(previewFooter);

            if(saveMode == 'overwrite') {

                var originalObj = TU{{ $dir_studly }}.overwritePreviewBox;
                originalObj.after(content);
                TU{{ $dir_studly }}.removeBox(originalObj);

            } else {

                $('#'+ TU{{ $dir_studly }}.ids['preview']).append(content);

            }

            {{ (!empty($callbacks['load'])) ? $callbacks['load'] : '' }}

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

            var removeUrl = $('#'+ TU{{ $dir_studly }}.ids['input']).data('removeUrl');
            var formData = TU{{ $dir_studly }}.formData;
            formData['remove_id'] = id;

            $.post(removeUrl, TU{{ $dir_studly }}.formData, function(data){

                if(data['result']) {

                    TU{{ $dir_studly }}.removeBox($(self).parent());

                }
                {{ (!empty($callbacks['remove'])) ? $callbacks['remove'] : '' }}

            }, 'json');

            return false;

        },
        removeBox: function(targetObj) {

            targetObj.remove();
            TU{{ $dir_studly }}.processingFile--;
            TU{{ $dir_studly }}.formData['surpass_overwrite_id'] = -1;
            TU{{ $dir_studly }}.overwritePreviewBox = null;

        },
        overwrite: function(self, targetId) {

            TU{{ $dir_studly }}.formData['surpass_overwrite_id'] = targetId;
            TU{{ $dir_studly }}.overwritePreviewBox = $(self).parent();
            $('#'+ TU{{ $dir_studly }}.ids['input']).click();

        },
        isFull: function() {

            var processingFileCount = TU{{ $dir_studly }}.processingFile;
            var maxFileCount = TU{{ $dir_studly }}.maxFile;

            if(TU{{ $dir_studly }}.formData['surpass_overwrite_id'] > 0
                    && processingFileCount <= maxFileCount) {

                return false;

            } else if(processingFileCount < maxFileCount) {

                return false;

            }

            return true;

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
	@if($overwrite)
        <button{{ $css_button }} onclick="return TU{{ $dir_studly }}.overwrite(this, {%=o.surpassId%});">{{ $button_label }}</button>
    @else
        <button{{ $css_button }} onclick="return TU{{ $dir_studly }}.remove(this, {%=o.surpassId%});">{{ $button_label }}</button>
    @endif
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