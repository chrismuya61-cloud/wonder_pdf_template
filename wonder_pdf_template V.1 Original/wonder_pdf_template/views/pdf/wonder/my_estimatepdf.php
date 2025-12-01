<?php

defined('BASEPATH') or exit('No direct script access allowed');

$dimensions = $pdf->getPageDimensions();

$info_right_column = '<div style="background-color: white;  width: 100%; border-bottom: 8px solid black;"></div>';
$info_left_column = wonder_pdf_logo_url();

// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 1.33) - $dimensions['lm']);

$pdf->ln(3);

//$info_left_column = '<span style="font-weight:bold;font-size:21pt; font-family:WonderUnitSans-Black;">' . _l('estimate_pdf_heading') . '</span><br />';
$info_left_column = '<span style="font-weight:bold;font-size:21pt; font-family:WonderUnitSans-Black;">Quotation</span><br />';

$info_right_column = '<span style="font-weight:bold;font-size:21pt; font-family:WonderUnitSans-Black; text-align:left;">FRNCK.</span><br />';

pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 1.33) - $dimensions['lm']);

$pdf->ln(3);

$info_left_column = '';
$info_right_column = '';

$info_left_column .= '<b style="font-size:10pt; font-family:WonderUnitSans-Bold;">Quotation No. <br>' . $estimate_number . '</b>';

if (get_option('show_status_on_pdf_ei') == 1) {
	$info_right_column .= '<br /><span style="color:rgb(' . estimate_status_color_pdf($status) . ');text-transform:uppercase;  text-align:left;font-size:10pt; font-family:WonderUnitSans-Bold;">' . format_estimate_status($status, '', false) . '</span>';
}

pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 1.33) - $dimensions['lm']);

$pdf->ln(7);

$estimate_info = '<div style="border-top: 1px solid black; text-align:left;font-size:10pt; font-family:WonderUnitSans-Regular;">
<br/>
<b style="">' . _l('estimate_data_date') . ' </b><br><span style="color:#4e4e4e;">' . _d($estimate->date) . '</span>';
if (!empty($estimate->duedate)) {
	$estimate_info .= '<br/><b style="">' . _l('estimate_data_duedate') . ' </b><br><span style="color:#4e4e4e;">' . _d($estimate->duedate) . '</span>';
}

// Bill to
$estimate_info .= '<br/><br/>
<b style="">Recepient : </b>';
$estimate_info .= '<div style="color:#4e4e4e;">';
$estimate_info .= wonder_format_customer_info($estimate, 'estimate', 'billing');
$estimate_info .= '</div>';

// ship to to
/*if ($estimate->include_shipping == 1 && $estimate->show_shipping_on_estimate == 1) {
$estimate_info .= '<br /><b>' . _l('ship_to') . ':</b>';
$estimate_info .= '<div style="color:#424242;">';
$estimate_info .= format_customer_info($estimate, 'estimate', 'shipping');
$estimate_info .= '</div>';
}*/
if ($estimate->sale_agent != 0 && get_option('show_sale_agent_on_estimates') == 1) {
	$estimate_info .= '<br/><br/>
<b style="">' . _l('sale_agent_string') . ': </b><br><span style="color:#4e4e4e;">' . get_staff_full_name($estimate->sale_agent) . '</span>';
}

if ($estimate->project_id != 0 && get_option('show_project_on_estimate') == 1) {
	$estimate_info .= '<br/><br/>
<b style="">' . _l('project') . ': </b><br><span style="color:#4e4e4e;"> ' . get_project_name_by_id($estimate->project_id) . '</span>';
}

foreach ($pdf_custom_fields as $field) {
	$value = get_custom_field_value($estimate->id, $field['id'], 'estimate');
	if ($value == '') {
		continue;
	}
	$estimate_info .= '<br/><br/><b style="">' . $field['name'] . ': <span style="color:#4e4e4e;">' . $value . '</span>';
}

$estimate_info .= '<div style="height:10px;"></div>';
$estimate_info .= '<div style="color:#424242; text-align:left;">';

$estimate_info .= format_organization_info();

$estimate_info .= '</div>';
$estimate_info .= '</div>';

// The items table
$items = wonder_get_items_table_data($estimate, 'estimate', 'pdf');

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
    <td align="right" width="15%" style="' . $borderTotal . '">' . app_format_money($tax['total_tax'], $estimate->currency_name) . '</td>
</tr>';
}

if ((int) $estimate->adjustment != 0) {
	$tbltotal .= '<tr>
    <td align="right" width="65%"></td>
    <td align="right" width="20%" style="' . $borderTotal . '">' . _l('estimate_adjustment') . '</td>
    <td align="right" width="15%" style="' . $borderTotal . '">' . app_format_money($estimate->adjustment, $estimate->currency_name) . '</td>
</tr>';
}

$tbltotal .= '
<tr style="color:red; font-weight:bold; font-size: 10.1pt;">
    <td align="right" width="65%"></td>
    <td align="right" width="20%" style="background-color:#f0f0f0;' . $borderTotal . '">' . _l('estimate_total') . '</td>
    <td align="right" width="15%" style="background-color:#f0f0f0;' . $borderTotal . '">' . app_format_money($estimate->total, $estimate->currency_name) . '</td>
</tr>';

$tbltotal .= '</table>';
$tblhtml .= $tbltotal;
if (get_option('total_to_words_enabled') == 1) {
	$tblhtml .= '<b>' . _l('num_word') . ': ' . $CI->numberword->convert($estimate->total, $estimate->currency_name) . '</b>';
}

if (!empty($estimate->clientnote)) {
	$tblhtml .= '<p></p><b>' . _l('estimate_note') . '</b><br/>' . $estimate->clientnote;
}

if (!empty($estimate->terms)) {
	$tblhtml .= '<p></p><b>' . _l('terms_and_conditions') . '</b><br/>' . $estimate->terms;
}

$info_left_column = '<table style="font-size:10pt; font-family:WonderUnitSans-Regular;"><tr><td style="width:95%;">' . $tblhtml . '</td><td style="width:5%;"></td></tr></table>';

pdf_multi_row($info_left_column, $estimate_info, $pdf, ($dimensions['wk'] / 1.33) - $dimensions['lm']);
