<?php

class SurpassTestController extends BaseController {

    // You need to make a directory to upload and meed to set permission "777" for it in advance.
    // In this case, "LARAVEL_DIR/public/img/uploads/surpass_test"

    private $_surpass_test_path = 'img/uploads';
    private $_surpass_test_dir = 'surpass_test';

    public function surpass_test() {

        $surpass = \Surpass::path($this->_surpass_test_path)
            ->dir($this->_surpass_test_dir)
            ->progress('Uploading..')
            ->css([
                'div' => 'pull-left text-center preview',
                'button' => 'btn btn-danger btn-md'
            ]);

        return View::make('surpass_test', [
            'surpass' => $surpass
        ]);

    }

    public function surpass_upload_test() {

        $surpass = \Surpass::path($this->_surpass_test_path);

        if($surpass->save()) {

            // Something..

        }

        return $surpass->result();

    }

    public function surpass_remove_test() {

        $surpass = \Surpass::path($this->_surpass_test_path);

        // You may need to check authorization here.

        if($surpass->remove()) {

            // Something..

        }

        return $surpass->result();

    }

}
