<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Modify core files
 * @return [type] [description]
 */
function mpwtl_modFile($fname, $searchF, $replaceW)
{
	$fhandle = fopen($fname, "r");
	$content = fread($fhandle, filesize($fname));
	if (strstr($content, $searchF)) {
		$content = str_replace($searchF, $replaceW, $content);
		$fhandle = fopen($fname, "w");
		fwrite($fhandle, $content);
	}
	fclose($fhandle);
	return true;
}

$pdf_arr = ['invoice', 'estimate', 'proposal', 'credit_note', 'expense', 'statement', 'contract', 'project_data'];
foreach ($pdf_arr as $pdf_title) {
	$search = "return \$actualPath;";
	$replace = "return hooks()->apply_filters('custom_".$pdf_title."PDF_file', \$actualPath);";
	$file_path = APPPATH . 'libraries/pdf/'.ucfirst($pdf_title).'_pdf.php';
	mpwtl_modFile($file_path, $search, $replace);
}

//move files
$destination_path = APPPATH . 'vendor/tecnickcom/tcpdf/fonts/';
$source_path = module_dir_path(WPDF_TEMPLATE, 'assets/files/');
$files_arr = array_values(array_diff(scandir($source_path), array('..', '.')));
$files_arr = json_decode(json_encode($files_arr));
foreach ($files_arr as $key => $file) {
	if (!file_exists($destination_path . $file)) {
		copy($source_path . $file, $destination_path . $file);
	}
}

//~/tecnnickcom/tcpdf/tools/ : $ php tcpdf_addfont.php -i your file.ttf