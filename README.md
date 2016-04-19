Surpass
=====

A PHP package mainly developed for Laravel to manage uploading images using Ajax and displaying thumbnails.  
(This is for Laravel 5+. [For Laravel 4.2](https://github.com/SUKOHI/Surpass/tree/1.0))

**[Demo](http://demo-laravel52.capilano-fw.com/surpass)**

(Example)  
!['Kitsilano'](http://i.imgur.com/cJ6t50G.png)

Requirements
====

* [jQuery](https://jquery.com/)  
* [jQuery UI](https://jqueryui.com/)  
* [blueimp/jQuery-File-Upload](https://github.com/blueimp/jQuery-File-Upload)


Installation
====

Add this package name in composer.json

    "require": {
      "sukohi/surpass": "2.*"
    }

Execute composer command.

    composer update

Register the service provider in app.php

    'providers' => [
        ...Others...,  
        Sukohi\Surpass\SurpassServiceProvider::class,
    ]

Also alias

    'aliases' => [
        ...Others...,  
        'Surpass'   => Sukohi\Surpass\Facades\Surpass::class
    ]

And execute the next commands.  

    php artisan vendor:publish  
    php artisan migrate
    
**Note: If you get errors after updating, execute the next.**

    php artisan vendor:publish --force
    php artisan migrate:rollback
    php artisan migrate

Usage
====

**Basic Usage**

    $path = 'img/uploads'; // The folder to save images.    
    $dir = 'dir_name'; // The directory name to save images.  
    $surpass = \Surpass::path($path)->dir($dir);

(See also a folder named "exaple" which has some files.)

**Upload  (in Controller)**

	$surpass = \Surpass::path('img/uploads')
					->dir('dir_name')
					->ids([
						'input' => 'image_upload',
						'preview' => 'preview_images'
					])
					->maxFiles(5)
					->alert('You can upload up to %d files.')
					->formData([
						'key_1' => 'value_1', 
						'key_2' => 'value_2', 
						'key_3' => 'value_3'
					])
					->preview(['maxHeight' => 120])
					->css([
						'div' => 'div_class', 
						'button' => 'button_class', 
						'preview' => 'preview_class', 
						'loading' => 'loading_class'
					])
					->progress('<img src="http://example.com/img/ajax-loader.gif"><br>Uploading..')
					->callback([
						'upload' => 'alert("Uploading..");', 
						'done' => 'alert("Done.");',
						'failed' => 'alert("Failed..");', 
						'remove' => 'alert("Removed");', 
						'load' => 'alert("Loading..");',
                        'timeout' => 'alert("Timeout..");',
                        'file_type_error' => 'alert("Only image files are allowed");'
					])
					->timeout(3000) // 3 seconds
					->overwrite(false)   // When using overwriting-mode
					->resize(['maxWidth' => '100', 'maxHeight' => '50'], $force_crop = false)   // Client Resizing(See "About resizing")
					->dropZone('drop_zone_id')  // See "Drop Zone"
					->button('Remove');
	$surpass->load([1, 2, 3]);    // These are IDs of DB that you saved image(s) in the past.

	return view('surpass', [
			
		'surpass' => $surpass
			
	]);
	
	
*Note: method dir('dir_name') can no longer receive "/" and "." to protect from directory traversal attack.

**Upload  (in View)**

Note1:  
Need to load jQuery, jQuery UI and jQuery-File-Upload. (Loading order is important. See the below)  
If you can use bower, use `bower install blueimp-file-upload --save-dev` to install all of the packages easily.  

Note2:  
Changed tags `{{` and `}}` to `{!!` and `!!}` from the previous version.  

    @section('content')
    
        <form>
        	<input 
        		id="image_upload" 
        		name="image_upload" 
        		title="Select Image" 
        		data-url="http://example.com/upload" 
        		data-remove-url="http://example.com/remove" 
        		accept="image/*" 
        		type="file" multiple>
        		
            <!-- Preview(s) will be displayed here -->
            {!! $surpass->html('preview') !!}
        </form>
    @stop
    
    @section('script')
    
        <!-- Load required JS libraries. -->
        <script src="bower_components/jquery/dist/jquery.min.js"></script>
        <script src="bower_components/blueimp-file-upload/js/vendor/jquery.ui.widget.js"></script>
        <script src="bower_components/blueimp-load-image/js/load-image.all.min.js"></script>
        <script src="bower_components/blueimp-canvas-to-blob/js/canvas-to-blob.js"></script>
        <script src="bower_components/blueimp-file-upload/js/jquery.iframe-transport.js"></script>
        <script src="bower_components/blueimp-file-upload/js/jquery.fileupload.js"></script>
        <script src="bower_components/blueimp-file-upload/js/jquery.fileupload-process.js"></script>
        <script src="bower_components/blueimp-file-upload/js/jquery.fileupload-image.js"></script>
        <script src="bower_components/blueimp-tmpl/js/tmpl.min.js"></script>
    
        <!-- JS code (including <script></script> tag) will be displayed here. -->
        {!! $surpass->html('js') !!}
    @stop
    
**Upload (Ajax)**
	
*Important: To save images you want, you need to make a specific dir which must be writable in advance.
	
    // To save an image and its data into DB

	$surpass = \Surpass::path('img/uploads')->id('input', 'image_upload');
	$attributes = array('alt' => 'alt_value', 'title' => 'title_value');  // Optional
	$save_dir = $surpass->saveDir();    // The directory you want to save.(Optional)
    $surpass->renameFiles(true);    // If you'd like to use the original filename, set false.(Optional)

	if($surpass->save($attributes = array())) {
	
	    // You can get the data of saved image like this.
	
		$load_item = $surpass->loadSaved();
		$id = $load_item->id;
		$dir = $load_item->dir;
		$filename = $load_item->filename;
		$path = $load_item->path;
		$url = $load_item->url;
		$attributes = $load_item->attributes;
		$tag = $load_item->tag;
		
		// You can save attributes also here. (Of course you can do that at other places.)
		
		$id = $load_item->id;
		$surpass->saveAttributes($id, array(
		    'key_1' => 'value_1',
		    'key_2' => 'value_2',
		    'key_3' => 'value_3'
		));
		
	}
	
	return $surpass->result();  // This will return json.
	
*Note 1: If uploading completed, the result data(json) has the following values.  
*Note 2: About renameFiles(), [See the details](#rename_files)

1. result (true / false)  
2. insertId  
3. path  
4. dir  
5. filename  
6. file_path  
7. extension  
8. width  
9. height  
10. mime_type  
11. saveMode : (overwrite / insert)

**Remove (Ajax)**

    // To remove an image and the data into DB

	$surpass = \Surpass::path('img/uploads');
	
	if($surpass->remove()) {
		// Something..
	}
	
	return $surpass->result();  // This will return json.
	
**Minimal Way**

(in Contoller)
    

	$surpass = \Surpass::path('img/uploads')->dir('dir_name');
	$surpass->load([1, 2, 3]);
(in View)
See above. 
	
(in Upload Ajax)

	$surpass = \Surpass::path('img/uploads');
	$dir = $surpass->requestDir();
	
	if($surpass->save()) {
		// Something..
	}
	
	return $surpass->result();
(in Remove Ajax)
	
	
	$surpass = \Surpass::path('img/uploads');
	
	if($surpass->remove()) {
		// Something..
	}
	
	return $surpass->result();

**Refresh**  
This method will remove all data and images that don't already exist.  

    $surpass = \Surpass::path('img/uploads');
    $surpass->refresh();
	
**Remove by ID**
	
    $surpass = \Surpass::path('img/uploads')->removeById(1);
  
  
	// or
  
  
    $surpass = \Surpass::path('img/uploads')->removeById([1, 2, 3]);
**Load with validation**

    $ids = [1, 2, 3];
    $surpass->load($ids, $old_flag = true);
    // If $old_flag is true, $ids will be replaced with Input::old() value(s) automatically.
    
**<a name="image_file_id"></a>Get image file id(s) when submitting**
	
    \Surpass::imageFileId('dir_name');
    \Surpass::imageFileIds('dir_name');
	
**About Saved IDs**

Afeter uploading image(s) with Ajax, the preview(s) have hidden-input-tag(s) named "surpass_ids[]" (and of course the value(s) are ID of DB saved at the time).  
So when submitting, you can receive those data as array.  

Now You can use **[imageFileId()](#image_file_id)** and **[imageFileIds()](#image_file_id)** to get image ID(s) easily.

**with Multiple file-inputs**  
(in Controller)

	$surpass = \Surpass::path('img/uploads');
	$surpass_x = clone $surpass->dir('xxx')
					->ids([
						'input' => 'input-xxx',
						'preview' => 'preview-xxx'
					]);
	$surpass_y = clone $surpass->dir('yyy')
					->ids([
						'input' => 'input-yyy',
						'preview' => 'preview-yyy'
					]);
	return view('view', [
		'surpass_x' => $surpass_x,
		'surpass_y' => $surpass_y
	]);
	
(in View)  

	<input 
		id="input-xxx" 
		name="input-xxx" 
		title="Select Image" 
		data-url="http://example.com/upload" 
		data-remove-url="http://example.com/remove" 
		accept="image/*" 
		type="file" multiple>
	{!! $surpass_x->html('preview') !!}  
	
	<input 
		id="input-yyy" 
		name="input-yyy" 
		title="Select Image" 
		data-url="http://example.com/upload" 
		data-remove-url="http://example.com/remove" 
		accept="image/*" 
		type="file" multiple>
	{!! $surpass_y->html('preview') !!}  
	
    // JS
    {!! $surpass_x->html('js') !!}
    {!! $surpass_y->html('js') !!}

**Set filename length**

    \Surpass::filenameLength(10);    // Default: 10

**Insert**

Note:  
This method is to save image(s) and their data directly like seeding.  
So, in usual you should use save() method.

    $insert_id = \Surpass::path('path')
                    ->dir('dir')
                    ->insert('file_path', $attributes = []);

**Drop Zone**

If you'd like to upload images through Drop Zone(using Drag and Drop), add a div-tag like the below.
    
    (in Controller)
    
    $surpass->dropZone('drop_zone_id');
    
    
    (in View)
    
    <div id="drop_zone_id">Drop images here!</div>

**Save Attributes**

    $id = 1;    // Here means ID of "image_files" table.
    $surpass->saveAttributes($id, [
        'key_1' => 'value_1',
        'key_2' => 'value_2',
        'key_3' => 'value_3'
    ]);
    
    *Note: The old attributes data will be removed.

Methods
====

* path($path)
    
    The path to save images.  
  
  
* dir($dir)
    
    The directory to save images.
    (The actual image file path is $path .'.'. $dir .'*******.***')
  
  
* ids($ids)

    The IDs that you'd like to set for HTML input tags.
    You can set the following ID names.
    
    * input
    * preview

    This method is optional. (If you need multiple image uploads, use this method.)
    (Default: input -> image_upload, preview -> preview_images)
    
    e.g.)
    \Surpass::ids([
        'input' => 'image_upload',
        'preview' => 'preview_images'
    ]);
  
  
* maxFiles($max_file)

    The maximum number of image files to upload.
    This method is optional.(Default: 5)
  
  
* alert($message)

    The message for when the count of the number of the images uploaded reach a maximum.

    e.g)
    \Surpass::alert('You can upload up to %d files.');
    This method is optional.(Default: "You can upload up to %d files.")
  
  
* formData($values)

    The additional data that will be included uploading request through Ajax.

    e.g)
        \Surpass::formData([
            'key_1' => 'value_1', 
            'key_2' => 'value_2', 
            'key_3' => 'value_3'
        ]);
    
    This method is optional.
  
  
* preview($preview_options)

    The options for preview like width, height and so on.
    See [here](https://github.com/blueimp/JavaScript-Load-Image#options).
    
    e.g)
    \Surpass::preview(['maxHeight' => 120]);
    
    This method is optional.
  
  
* css($css_values)

    The css values that will be set to specific elements.
    You can set the following types.
    
    * preview   : for div element containing all elements.
    * div       : for div element containing an individual image preview.
    * loading   : for div element containing a loading message.
    * button    : for button element to remove or overwrite.

    e.g.)
    css([
        'div' => 'div_class', 
        'button' => 'button_class', 
        'preview' => 'preview_class', 
        'loading' => 'loading_class'
    ])

    This method is optional.
  
  
* progress($loading_message)

    The content displayed when uploading an image.
    
    e.g.)
    \Surpass::progress('Uploading..')
    
    This method is optional.(If you skip this method, loading message not displayed.)
  
  
* callback($callbacks)

    The callback values for JavaScript.
    You can set the following callbacks.
    
    upload          : called when uploading an image.
    done            : called when uploading completed.
    failed          : called when uploading failed.
    remove          : called when an preview removed.
    load            : called when loading an image.
    timeout         : called when uploading is timeout.
    file_type_error : called when selected file is not image.

    e.g.)  
        \Surpass::callback([
            'upload' => 'console.log(data);', 
            'done' => 'console.log(data);',
            'failed' => 'console.log(data);', 
            'remove' => 'console.log(data);', 
            'load' => 'console.log(data);', 
            'timeout' => 'console.log(data);', 
            'file_type_error' => 'console.log(data);'
        ]);

    Except "remove" can use "console.log(e);"
    This method is optional.
  
  
* timeout($seconds)

    The seconds for timeout.
    This method is optional.
    
    e.g.)
    \Surpass::timeout(3000);
  
  
* overwrite($boolean)

    A setting whether to use overwrite-mode.
    In overwrite-mode you can't remove images.
    This method is optional.
  
  
* resize($options, $force_crop = false)

    Settings for client resizing.
    You can set the following types.
    
    * maxWidth
    * maxHeight

    See [here](https://github.com/blueimp/jQuery-File-Upload/wiki/Options#imageminwidth).  
  
    * If $force_crop is true, imageCrop added.
    
    See [here](https://github.com/blueimp/jQuery-File-Upload/wiki/Options#imagecrop).
  
    This method is optional.
  
  
* dropZone($drop_zone_id)

    If you'd like to upload by drag-and-drop.
    $drop_zone_id refers to ID of HTML element like the following.
    
    <div id="drop_zone">
        Drop Zone
    </div>
    
    This method is optional.
  
  
* button($label)

    The text that will be displayed on the Remove(Overwrite) button.
    This method is optional.(Default: Remove)
  
  
* load($ids)

    (In a case of that `$ids` is empty)

    This method will automatically display previews using Input::old('***') when redirecting with input data.
  
    (In a case that `$ids` is not empty)

    If you'd like to display specific preview(s) by default, use this method.
    $ids refers to IDs of image_files from DB.

<a name="rename_files"></a>

* Surpass::renameFiles($bool = true)  
By this method, you can choose whether file name will be decided randomly or not when saving.(Optional)  
    
    * If $bool is `true`, file name will be decided randomly.
    * If $bool is `false`, file name will be the same with the original file name.  
    
    However, if the same file already exists, incremental number will be added like `filename-1.jpg`.  


        $surpass = Surpass::path('YOUR_PATH');
        $surpass->renameFiles(false);
        
        if($surpass->save()) {
        
            // Something..
        
        }

Configuration
====

After executing `vendor:publish`, you have a configuration file called `surpass.php` in /config.  
In there, you can change table name that Surpass will use.  

**Note**: For stable DB design, the actual table name in your database will NOT be changed automatically.  
So, in order to rename, you need to generate a migration file and to execute it after setting the new table name in the configuration file.  
For more details, see [Renaming / Dropping Tables](https://laravel.com/docs/master/migrations#renaming-and-dropping-tables)
or
`examples/migrations/2016_04_03_063049_rename_image_files_to_thumbnail_files.php`.


Special thanks
====

Thank you guys for your contributions!

* [dsfser](https://github.com/dsfser)
* [Marco Mazzocchi](https://github.com/marco-mazzocchi)
* [nmfzone](https://github.com/nmfzone)

License
====

This package is licensed under the MIT License.

Copyright 2014 Sukohi Kuhoh