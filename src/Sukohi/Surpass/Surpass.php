<?php namespace Sukohi\Surpass;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;

class Surpass {

	const TABLE = 'image_files';
	const DIR_HIDDEN_NAME = 'surpass_hidden_dir';
	const ID_HIDDEN_NAME = 'surpass_ids';
	private $_path, $_dir;
	private $_alert = 'You can upload up to %d files.';
	private $_button = 'Remove';
	private $_max_files = 5;
	private $_form_data, $_result, $_load = array();
	private $_ids = array(
			
		'input' => 'image_upload',
		'preview' => 'preview_images'
		
	);
	private $_preview_params = array(
			
			'maxHeight' => 120
	);
	
	public function path($path) {
	
		$this->_path = $path;
		return $this;
	
	}

	public function dir($dir) {
	
		$this->_dir = $dir;
		return $this;
	
	}

	public function id($mode, $id) {
	
		$this->_ids[$mode] = $id;
		return $this;
	
	}

	public function ids($ids) {
	
		$this->_ids = $ids;
		return $this;
	
	}
	
	public function renderId($mode) {
		
		return $this->_ids[$mode];
		
	}

	public function maxFiles($max_files) {
	
		$this->_max_files = $max_files;
		return $this;
	
	}

	public function alert($alert) {
	
		$this->_alert = $alert;
		return $this;
	
	}

	public function formData($form_data) {
	
		$this->_form_data = $form_data;
		return $this;
	
	}

	public function button($label) {
	
		$this->_button = $label;
		return $this;
	
	}

	public function preview($params) {
	
		$this->_preview_params = $params;
		return $this;
	
	}
	
	public function css($css) {
		
		$this->_css = $css;
		return $this;
		
	}
	
	public function renderCss($mode) {
		
		return (!empty($this->_css[$mode])) ? ' class="'. $this->_css[$mode] .'"' : '';
		
	}
	
	public function html($mode, $options=array()) {

		if($mode == 'preview') {
			
			return View::make('packages.sukohi.surpass.preview')->render();
			
		} else if($mode == 'js') {
			
			$load_data = [];
			
			if(!empty($this->_load)) {
				
				foreach ($this->_load as $id => $load) {
					
					$load_data[] = array(
						
						'id' => $id, 
						'url' => $load->url, 
						'filename' => $load->filename
							
					);
					
				}
				
			}
			$this->_form_data[self::DIR_HIDDEN_NAME] = $this->_dir;
			
			return View::make('packages.sukohi.surpass.js', array(
					
					'max_file' => $this->_max_files, 
					'load_data' => $load_data, 
					'form_data' => $this->_form_data, 
					'alert' => sprintf($this->_alert, $this->_max_files), 
					'button_label' => $this->_button,  
					'preview_params' => $this->_preview_params, 
					'id_hidden_name' => self::ID_HIDDEN_NAME
					
			))->render();
			
		}
		
	}
	
	public function save() {

		$this->dir(Input::get(self::DIR_HIDDEN_NAME));
		$result = false;
		$id = -1;
		$input_id = $this->_ids['input'];
		$extension = Input::file($input_id)->getClientOriginalExtension();
		$filename = str_random(10) .'.'. $extension;
		$size = Input::file($input_id)->getSize();
		
		DB::beginTransaction();
		
		try {

			$save_path = $this->filePath($this->_dir);
			Input::file($input_id)->move($save_path, $filename);
			
			$id = DB::table(self::TABLE)->insertGetId([
		
				'dir' => $this->_dir,
				'filename' => $filename,
				'extension' => $extension,
				'size' => $size,
				'created_at' => date('Y-m-d H:i:s')
			
			]);
			DB::commit();
			$this->addLoadObject($id, $this->_dir, $filename);
			$result = true;
			
		} catch (Exception $e) {
			
			DB::rollback();
			
		}
		
		$this->_result = array(
			'result' => $result,
			'insertId' => $id
		);
		
		return $result;
		
	}
	
	public function remove() {
		
		$result = $this->removeById(Input::get('remove_id'));
		$this->_result = array('result' => $result);
		return $result;
		
	}
	
	public function removeById($id) {
		
		$result = false;
		
		DB::beginTransaction();
		
		try {
		
			$db = DB::table(self::TABLE)->where('id', '=', $id);
			$image_file = $db->select('dir', 'filename')->first();
			$remove_path = $this->filePath($image_file->dir, $image_file->filename);
			File::delete($remove_path);
			$db->delete();
			DB::commit();
			$result = true;
		
		} catch (Exception $e) {
		
			DB::rollback();
		
		}
		
		return $result;
		
	}
	
	public function result() {
		
		$result = $this->_result;
		$this->_result = array();
		return Response::json($result);
		
	}
	
	public function refresh() {
		
		DB::beginTransaction();
		
		try {
		
			$image_files = DB::table(self::TABLE)->select('id', 'dir', 'filename')->get();
			
			foreach ($image_files as $key => $image_file) {
				
				$path = $this->filePath($image_file->dir, $image_file->filename);
				
				if(!file_exists($path)) {
					
					 DB::table(self::TABLE)
					 		->where('id', '=', $image_file->id)
				 			->delete();
					
				}
				
			}
			
			DB::commit();
			return true;
				
		} catch (Exception $e) {
				
			DB::rollback();
			return false;
				
		}
		
	}
	
	public function load($ids, $old_flag=true) {
		
		if(!is_array($ids)) {
			
			$ids = [$ids];
			
		}
		
		if($old_flag 
				&& Input::old(self::ID_HIDDEN_NAME) 
				&& is_array(Input::old(self::ID_HIDDEN_NAME))) {
			
			$ids = Input::old(self::ID_HIDDEN_NAME);
				
		}
		
		$this->_load = array();
		$image_files = DB::table(self::TABLE)
							->select('id', 'dir', 'filename')
							->whereIn('id', $ids)
							->get();
		
		foreach ($image_files as $image_file) {
			
			$this->addLoadObject(
					
				$image_file->id, 
				$image_file->dir, 
				$image_file->filename
					
			);
			
		}
		return $this;
		
	}
	
	public function loadSaved() {
		
		$keys = array_keys($this->_load);
		return $this->_load[end($keys)];
		
	}
	
	public function loadData() {

		return $this->_load;
		
	}
	
	public function imageFileIds() {
		
		$ids = !empty(Input::get(self::ID_HIDDEN_NAME)) ? Input::get(self::ID_HIDDEN_NAME) : [];
		sort($ids);
		return $ids;
		
	}
	
	private function filePath($dir, $filename='') {
		
		$path = $this->_path .'/'. $dir;
		
		if(!empty($filename)) {
			
			$path .= '/'. $filename;
			
		}
		
		return public_path($path);
		
	}
	
	private function fileUrl($dir, $filename) {
		
		return URL::to($this->_path .'/'. $dir .'/'. $filename);
		
	}
	
	private function addLoadObject($id, $dir, $filename) {

		$load = new \stdClass;
		$load->id = $id;
		$load->dir = $dir;
		$load->filename = $filename;
		$load->path = $this->filePath($dir, $filename);
		$load->url = $this->fileUrl($dir, $filename);
		$this->_load[$id] = $load;
		
	}
	
}