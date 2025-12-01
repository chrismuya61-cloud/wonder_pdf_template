<?php

defined('BASEPATH') or exit('No direct script access allowed');

$dimensions = $pdf->getPageDimensions();

$info_right_column = '<div style="color:#424242; font-size: 14px;">' . format_organization_info() . '</div>';
$info_left_column = wonder_pdf_logo_url();

// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 3.33) - $dimensions['lm']);

$pdf->ln(2);
$underLine = '<div style="width:100%; border-bottom: 2px solid red;"></div>';
$pdf->writeHTML($underLine, true, false, false, false, '');

$pdf->ln(-8);

$estimate_heading = ucfirst(strtolower(_l('estimate_pdf_heading')));

$title = '<span style="font-weight:600; font-size: 38pt;  text-align:right">' . $estimate_heading . '</span><br />';
$brands = '<img src="' . module_dir_path(WPDF_TEMPLATE, 'assets/images/brands.png') . '"/>';

//$pdf->writeHTML($title, true, false, false, false, '');
pdf_multi_row($brands, $title, $pdf, ($dimensions['wk'] / 2.5) - $dimensions['lm']);
$pdf->ln(1);

$estimate_info = '<b style="color:#4e4e4e;"># ' . $estimate_number . '</b>';

if (get_option('show_status_on_pdf_ei') == 1) {
	$estimate_info .= '<br /><span style="color:rgb(' . estimate_status_color_pdf($status) . ');text-transform:uppercase;">' . format_estimate_status($status, '', false) . '</span>';
}


$estimate_info .= '<br /><span style="font-weight:bold;">' . _l('estimate_data_date') . '</span> ' . _d($estimate->date) . '<br />';

if (!empty($estimate->duedate)) {
	$estimate_info .= '<span style="font-weight:bold;">' . _l('estimate_data_duedate') . '</span> ' . _d($estimate->duedate) . '<br />';
}

if ($estimate->sale_agent != 0 && get_option('show_sale_agent_on_estimates') == 1) {
	$estimate_info .= '<span style="font-weight:bold;">' . _l('sale_agent_string') . ':</span> ' . get_staff_full_name($estimate->sale_agent) . '<br />';
}

if ($estimate->project_id != 0 && get_option('show_project_on_estimate') == 1) {
	$estimate_info .= '<span style="font-weight:bold;">' . _l('project') . ':</span> ' . get_project_name_by_id($estimate->project_id) . '<br />';
}

// Bill to
$customer_info = '<span style="font-weight:bold;">BILL TO:</span>';
$customer_info .= '<div style="font-weight:400 !important; color:#4e4e4e;">';
$customer_info .= format_customer_info($estimate, 'estimate', 'billing');
$customer_info .= '</div>';

$info_right_column = $estimate_info;

$info_left_column = $customer_info;

pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

// The items table
$items = wonder_get_items_table_data($estimate, 'estimate', 'pdf', false);

$tblhtml = $items->table();
$borderTotal = 'border-bottom-color:#000000;border-bottom-width:1px; border-bottom-style:solid;';
$tbltotal = '';
$tbltotal .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';
$tbltotal .= '
<tr>
    <td align="right" width="65%"></td>
    <td align="right" width="20%" style="background-color:#f0f0f0;' . $borderTotal . '">' . _l('estimate_subtotal') . '</td>
    <td align="right" width="15%" style="background-color:#f0f0f0;' . $borderTotal . '">' . app_format_money($estimate->subtotal, $estimate->currency_name) . '</td>
</tr>';

if (is_sale_discount_applied($estimate)) {
	$tbltotal .= '
    <tr>
        <td align="right" width="65%"></td>
        <td align="right" width="20%" style="' . $borderTotal . '">' . _l('estimate_discount');
	if (is_sale_discount($estimate, 'percent')) {
		$tbltotal .= '(' . app_format_number($estimate->discount_percent, true) . '%)';
	}
	$tbltotal .= '';
	$tbltotal .= '</td>';
	$tbltotal .= '<td align="right" width="15%" style="' . $borderTotal . '">-' . app_format_money($estimate->discount_total, $estimate->currency_name) . '</td>
    </tr>';
}

foreach ($items->taxes() as $tax) {
	$tbltotal .= '<tr>
	<td align="right" width="65%"></td>
    <td align="right" width="20%" style="' . $borderTotal . '">' . $tax['taxname'] . ' (' . app_format_number($tax['taxrate']) . '%)' . '</td>
    <td align="right" width="15%" style="' . $borderTotal . '">' . app_format_money($tax['total_tax'], $estimate->currency_name) . '</td></tr>';
}

if ((int) $estimate->adjustment != 0) {
	$tbltotal .= '<tr>
    <td align="right" width="65%"></td>
    <td align="right" width="20%" style="' . $borderTotal . '">' . _l('estimate_adjustment') . '</td>
    <td align="right" width="15%" style="' . $borderTotal . '">' . app_format_money($estimate->adjustment, $estimate->currency_name) . '</td></tr>';
}

$tbltotal .= '
<tr style="color:red; font-weight:bold;">
    <td align="right" width="65%"></td>
    <td align="right" width="20%" style="background-color:#f0f0f0;' . $borderTotal . '">' . _l('estimate_total') . '</td>
    <td align="right" width="15%" style="background-color:#f0f0f0;' . $borderTotal . '">' . app_format_money($estimate->total, $estimate->currency_name) . '</td>
</tr>';



if (get_option('show_credits_applied_on_estimate') == 1 && $credits_applied = total_credits_applied_to_estimate($estimate->id)) {
	$tbltotal .= '
    <tr>
        <td align="right" width="65%"></td>
    <td align="right" width="20%" style="' . $borderTotal . '">' . _l('applied_credits') . '</td>
        <td align="right" width="15%" style="' . $borderTotal . '">-' . app_format_money($credits_applied, $estimate->currency_name) . '</td>
    </tr>';
}

if (get_option('show_amount_due_on_estimate') == 1 && $estimate->status != estimates_model::STATUS_CANCELLED) {
	$tbltotal .= '<tr>
       <td align="right" width="65%"></td>
    <td align="right" width="20%"  style="background-color:#f0f0f0;' . $borderTotal . '">' . _l('estimate_amount_due') . '</td>
       <td align="right" width="15%" style="background-color:#f0f0f0;' . $borderTotal . '">' . app_format_money($estimate->total_left_to_pay, $estimate->currency_name) . '</td>
   </tr>';
}

$tbltotal .= '</table>';
$tblhtml .= $tbltotal;
if (get_option('total_to_words_enabled') == 1) {
	$tblhtml .= '<b>' . _l('num_word') . ': ' . $CI->numberword->convert($estimate->total, $estimate->currency_name) . '</b>';
}

$tblhtml .= '<table style="font-size:' . ($font_size + 4) . 'px">
<tr height="40"><td></td><td></td></tr>
<tr><td style="width:100%;">';


if (!empty($estimate->terms)) {
	$tblhtml .= '<p></p><b>' . _l('terms_and_conditions') . '</b><br/>' . $estimate->terms;
}
$tblhtml .= '</td><td style="width:50%;">';
$tblhtml .= '</td></tr></table>';

$content = '<table style="font-size: 12px;"><tr><td style="width:99%;">' . $tblhtml . '</td><td style="width:1%;"></td></tr></table>';

$pdf->writeHTML($content, true, false, false, false, '');

$pdf->Ln(4);
$footer_thank_msg = '
<table border="0" cellpadding="5">
<thead>';
if (get_option('e_sign_estimate')) {
	$footer_thank_msg .= '<tr>
  <td>' . pdf_signatures() . '</td>
  </tr>';
}
$footer_thank_msg .= '<tr><td style="vertical-align: middle; text-align:center; font-size: ' . ($font_size + 4) . 'px; border-top:2px solid #dedede;"><b>' . strtoupper("Thank You For Your Business") . '</b></td>
</tr>
</thead>
</table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $footer_thank_msg, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
