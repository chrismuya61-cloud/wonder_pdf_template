<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modify core files
 * @return [type] [description]
 */
function mpwtl_modFile($fname, $searchF, $replaceW) {
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
$search = "\$result = data_tables_init(\$aColumns, \$sIndexColumn, \$sTable, [], [' AND is_mpwtl = 0'], ['form_key', 'id']);";
$replace = "\$result  = data_tables_init(\$aColumns, \$sIndexColumn, \$sTable, [], [], ['form_key', 'id']);";
$file_path = APPPATH . 'views/admin/tables/web_to_lead.php';
//mpwtl_modFile($file_path, $search, $replace);

$pdf_arr = ['invoice', 'estimate', 'proposal', 'credit_note', 'expense', 'statement', 'contract', 'project_data'];
foreach ($pdf_arr as $pdf_title) {
	$replace = "return \$actualPath;";
	$search = "return hooks()->apply_filters('custom_".$pdf_title."PDF_file', \$actualPath);";
	$file_path = APPPATH . 'libraries/pdf/'.ucfirst($pdf_title).'_pdf.php';
	mpwtl_modFile($file_path, $search, $replace);
}