Surpass
=====

A PHP package mainly developed for Laravel to manage uploading images using Ajax and displaying thumbnail.

Requirements
====

jQuery, jQuery UI and 
[blueimp/jQuery-File-Upload](https://github.com/blueimp/jQuery-File-Upload)


Installation&setting for Laravel
====

After installation using composer, add the followings to the array in  app/config/app.php

    'providers' => array(  
        ...Others...,  
        'Sukohi\Surpass\SurpassServiceProvider', 
    )


    'aliases' => array(  
        ...Others...,  
        'Surpass' =>'Sukohi\Surpass\Facades\Surpass',
    )

And execute the followings.

    php artisan migrate --package=sukohi/surpass
    
    php artisan view:publish sukohi/surpass

Usage
====

**Upload  (in Controller)**

	$surpass = Surpass::path('img/uploads')
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
					->button('Remove');
	$surpass->load([1, 2, 3]);    // These are IDs of DB that you saved image(s) in the past.

	return View::make('surpass', [
			
		'surpass' => $surpass
			
	]);

**Upload  (in View)**

    (in View)    
    
    Note: Need to load jQuery, jQuery UI and jQuery-File-Upload(jquery.iframe-transport.js, jquery.fileupload.js, load-image.all.min.js, tmpl.min.js)

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
    	{{ $surpass->html('preview') }}
    </form>
    
    <!-- JS code will be displayed here -->
    {{ $surpass->html('js') }}


**Upload (in http://example.com/upload using Ajax)**

    // To save an image and the data into DB

	$surpass = Surpass::path('img/uploads')
					->id('input', 'image_upload');
	$dir = $surpass->requestDir();
	
	if($surpass->save()) {

		$load_item = $surpass->loadSaved();
		$id = $load_item->id;
		$dir = $load_item->dir;
		$filename = $load_item->filename;
		$path = $load_item->path;
		$url = $load_item->url;
		
	}
	
	return $surpass->result();  // This will return json.


**Remove (in http://example.com/remove using Ajax)**

    // To remove an image and the data into DB

	$surpass = Surpass::path('img/uploads')
	                ->id('input', 'image_upload');
	
	if($surpass->remove()) {
		// Something..
	}
	
	return $surpass->result();  // This will return json.
	
**Minimal Way**

(in Contoller)
    

	$surpass = Surpass::path('img/uploads')->dir('dir_name');
	$surpass->load([1, 2, 3]);
(in View)
See above. 
	
(in Upload Ajax)

	$surpass = Surpass::path('img/uploads');
	$dir = $surpass->requestDir();
	
	if($surpass->save()) {
		// Something..
	}
	
	return $surpass->result();
(in Remove Ajax)
	
	
	$surpass = Surpass::path('img/uploads');
	
	if($surpass->remove()) {
		// Something..
	}
	
	return $surpass->result();

**Refresh**
	
	Surpass::refresh();
	
**Remove by ID**
	
    $surpass = Surpass::path('img/uploads')
					->removeById(1);
					
**Load with validation**

    $ids = [1, 2, 3];
    $surpass->load($ids, $old_flag = true);
    // If $old_flag is true, $ids will be replaced with Input::old() value(s) automatically.
    
**Get image file ids when submitting**
	
    Surpass::imageFileIds();
	
**About Saved IDs**

Afeter uploading image(s) with Ajax, the preview(s) have hidden-input-tag(s) named "surpass_ids[]" (and of course the value(s) are ID of DB saved at the time).  
So when submitting, you can receive those data as array.

License
====

This package is licensed under the MIT License.

Copyright 2014 Sukohi Kuhoh