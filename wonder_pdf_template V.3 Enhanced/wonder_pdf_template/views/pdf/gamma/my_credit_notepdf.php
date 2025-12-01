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

$credit_note_heading = ucfirst(strtolower(_l('credit_note_pdf_heading')));
if (!empty($credit_note->datecs_esd_response)) {
	$credit_note_heading = get_option('tax_credit_note_name');
}

$title = '<span style="font-weight:600; font-size: 38pt;  text-align:right">' . $credit_note_heading . '</span><br />';
$brands = '<img src="' .module_dir_path(WPDF_TEMPLATE, 'assets/images/brands.png').'"/>';

//$pdf->writeHTML($title, true, false, false, false, '');
pdf_multi_row($brands, $title, $pdf, ($dimensions['wk'] / 2.5) - $dimensions['lm']);
$pdf->ln(1);

$credit_note_info = '<b style="color:#4e4e4e;"># ' . $credit_note_number . '</b>';

if (get_option('show_status_on_pdf_ei') == 1) {
	$credit_note_info .= '<br /><span style="color:rgb(' . credit_note_status_color_pdf($status) . ');text-transform:uppercase;">' . format_credit_note_status($status, '', false) . '</span>';
}

$credit_note_info .= '<br /><span style="font-weight:bold;">' . _l('credit_note_date') . '</span> ' . _d($credit_note->date) . '<br />';

if (!empty($credit_note->duedate)) {
	$credit_note_info .= '<span style="font-weight:bold;">' . _l('credit_note_data_duedate') . '</span> ' . _d($credit_note->duedate) . '<br />';
}


if (!empty($credit_note->reference_no)) {
	$credit_note_info .= '<span style="font-weight:bold;">' . _l('reference_no') . '</span> ' . $credit_note->reference_no . '<br />';
	$credit_note_info = hooks()->apply_filters('credit_note_pdf_header_after_reference_no', $credit_note_info, $credit_note);
  }


if ($credit_note->project_id != 0 && get_option('show_project_on_credit_note') == 1) {
	$credit_note_info .= '<span style="font-weight:bold;">' . _l('project') . ':</span> ' . get_project_name_by_id($credit_note->project_id) . '<br />';

}

// Bill to
$customer_info = '<span style="font-weight:bold;">BILL TO:</span>';
$customer_info .= '<div style="font-weight:400 !important; color:#4e4e4e;">';
$customer_info .= format_customer_info($credit_note, 'credit_note', 'billing');
$customer_info .= '</div>';

$info_right_column = $credit_note_info;

$info_left_column = $customer_info;

pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);
$pdf->ln(1);

// The items table
$items = wonder_get_items_table_data($credit_note, 'credit_note', 'pdf', false, 'gamma');

$tblhtml = $items->table();
$borderTotal = 'border-bottom-color:#000000;border-bottom-width:1px; border-bottom-style:solid;';
$tbltotal = '';
$tbltotal .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';
$tbltotal .= '
<tr>
    <td align="right" width="65%"></td>
    <td align="right" width="20%" style="background-color:#f0f0f0;' . $borderTotal . '">' . _l('credit_note_subtotal') . '</td>
    <td align="right" width="15%" style="background-color:#f0f0f0;' . $borderTotal . '">' . app_format_money($credit_note->subtotal, $credit_note->currency_name) . '</td>
</tr>';


if (is_sale_discount_applied($credit_note)) {
	$tbltotal .= '
    <tr>
        <td align="right" width="65%"></td>
        <td align="right" width="20%" style="' . $borderTotal . '">' . _l('credit_note_discount');
	if (is_sale_discount($credit_note, 'percent')) {
		$tbltotal .= '(' . app_format_number($credit_note->discount_percent, true) . '%)';
	}
	$tbltotal .= '';
	$tbltotal .= '</td>';
	$tbltotal .= '<td align="right" width="15%" style="' . $borderTotal . '">-' . app_format_money($credit_note->discount_total, $credit_note->currency_name) . '</td>
    </tr>';
}

foreach ($items->taxes() as $tax) {
	$tbltotal .= '<tr>
	<td align="right" width="65%"></td>
    <td align="right" width="20%" style="' . $borderTotal . '">' . $tax['taxname'] . ' (' . app_format_number($tax['taxrate']) . '%)' . '</td>
    <td align="right" width="15%" style="' . $borderTotal . '">' . app_format_money($tax['total_tax'], $credit_note->currency_name) . '</td>
</tr>';
}

if ((int) $credit_note->adjustment != 0) {
	$tbltotal .= '<tr>
    <td align="right" width="65%"></td>
    <td align="right" width="20%" style="' . $borderTotal . '">' . _l('credit_note_adjustment') . '</td>
    <td align="right" width="15%" style="' . $borderTotal . '">' . app_format_money($credit_note->adjustment, $credit_note->currency_name) . '</td>
</tr>';
}

$tbltotal .= '
<tr style="color:red; font-weight:bold; font-size: 10.1pt;">
    <td align="right" width="65%"></td>
    <td align="right" width="20%" style="background-color:#f0f0f0;' . $borderTotal . '">' . _l('credit_note_total') . '</td>
    <td align="right" width="15%" style="background-color:#f0f0f0;' . $borderTotal . '">' . app_format_money($credit_note->total, $credit_note->currency_name) . '</td>
</tr>';



if ($credit_note->credits_used) {
	$tbltotal .= '
	<tr>
		<td width="65%"></td>
		<td align="right" width="20%" style="background-color:#f0f0f0;' . $borderTotal . '"><strong>' . _l('credits_used') . '</strong></td>
		<td align="right" width="15%" style="background-color:#f0f0f0;' . $borderTotal . '">' . '-' . app_format_money($credit_note->credits_used, $credit_note->currency_name) . '</td>
	</tr>';
  }
  
  if ($credit_note->total_refunds) {
	$tbltotal .= '
	<tr>
		<td width="65%"></td>
		<td align="right" width="20%" style="background-color:#f0f0f0;' . $borderTotal . '"><strong>' . _l('refund') . '</strong></td>
		<td align="right" width="15%" style="background-color:#f0f0f0;' . $borderTotal . '">' . '-' . app_format_money($credit_note->total_refunds, $credit_note->currency_name) . '</td>
	</tr>';
  }
  
  $tbltotal .= '
	<tr>
		<td width="65%"></td>
		<td align="right" width="20%" style="background-color:#f0f0f0;' . $borderTotal . '"><strong>' . _l('credits_remaining') . '</strong></td>
		<td align="right" width="15%" style="background-color:#f0f0f0;' . $borderTotal . '">' . app_format_money($credit_note->remaining_credits, $credit_note->currency_name) . '</td>
   </tr>';
  
  $tbltotal .= '</table>';  
$tblhtml .= $tbltotal;
if (get_option('total_to_words_enabled') == 1) {
	$tblhtml .= '<b>' . _l('num_word') . ': ' . $CI->numberword->convert($credit_note->total, $credit_note->currency_name) . '</b>';
}

$tblhtml .= '<table style="font-size:10pt;">
<tr height="40"><td></td><td></td></tr>
<tr><td style="width:50%;">';

if (!empty($credit_note->clientnote)) {
	$tblhtml .= '<p></p><b>' . _l('credit_note_note') . '</b><br/>' . $credit_note->clientnote;
}

if (!empty($credit_note->terms)) {
	$tblhtml .= '<p></p><b>' . _l('terms_and_conditions') . '</b><br/>' . $credit_note->terms;
}
$tblhtml .='</td><td style="width:50%;">';
$tblhtml .= !empty($credit_note->datecs_esd_response) ? datecs_esd_itax_info($credit_note, 'credit_note') : "";
$tblhtml .= '</td></tr></table>';

$content = '<table style="font-size:10pt;"><tr><td style="width:99%;">' . $tblhtml . '</td><td style="width:1%;"></td></tr></table>';

$pdf->writeHTML($content, true, false, false, false, '');

$pdf->Ln(4);
$footer_thank_msg = '
<table border="0" cellpadding="5">
<thead>';
if (get_option('e_sign_credit_note')) {
	$footer_thank_msg .= '<tr>
  <td>' . pdf_signatures() . '</td>
  </tr>';
}
$footer_thank_msg .= '<tr><td style="vertical-align: middle; text-align:center; font-size: ' . ($font_size + 4) . 'px; border-top:2px solid #dedede;"><b>' . strtoupper("Thank You For Your Business") . '</b></td>
</tr>
</thead>
</table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $footer_thank_msg, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);