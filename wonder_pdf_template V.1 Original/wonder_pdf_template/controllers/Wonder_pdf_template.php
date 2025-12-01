<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Wonder_pdf_template extends AdminController {
	public function index() {
		show_404();
		/*$destination_path = APPPATH . 'vendor/tecnickcom/tcpdf/fonts/';
			$source_path = module_dir_path(WPDF_TEMPLATE, 'assets/files/');
			$files_arr = array_values(array_diff(scandir($source_path), array('..', '.')));
			$files_arr = json_decode(json_encode($files_arr));
			foreach ($files_arr as $key => $file) {
				if (!file_exists($destination_path . $file)) {
					copy($source_path . $file, $destination_path . $file);
				}
		*/
	}

}
