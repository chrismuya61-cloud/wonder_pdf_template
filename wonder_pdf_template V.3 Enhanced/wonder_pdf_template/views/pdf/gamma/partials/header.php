<?php
defined('BASEPATH') or exit('No direct script access allowed');

$dimensions = $pdf->getPageDimensions();

$info_right_column = '<div style="color:#424242; font-size: 14px;">' . format_organization_info() . '</div>';
$info_left_column = wonder_pdf_logo_url();

// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 3.33) - $dimensions['lm']);

$pdf->ln(4);
$underLine = '<div style="width:100%; border-bottom: 2px solid red;"></div>';
$pdf->writeHTML($underLine, true, false, false, false, '');

$pdf->ln(-14);


if (isset($doc_title) && !empty($doc_title)) {
	$title = '<span style="font-weight:600; font-size: 34pt;  text-align:right">' . $doc_title . '</span><br />';
	$brands = '<img src="' . module_dir_path(WPDF_TEMPLATE, 'assets/images/brands.png') . '"/>';

	//$pdf->writeHTML($title, true, false, false, false, '');
	pdf_multi_row($brands, $title, $pdf, ($dimensions['wk'] / 2.5) - $dimensions['lm']);

	$pdf->ln(1);
}