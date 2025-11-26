<?php

defined('BASEPATH') or exit('No direct script access allowed');

$dimensions = $pdf->getPageDimensions();

/*275 == 100%
213 = 213/275 * 100%

 */

$full_width = $dimensions['wk']; //100%
$middle_column = 5;
$right_column = ((($full_width - (($full_width / 1.33) - $dimensions['lm'])) / $full_width) * 100) - $middle_column;
$left_column = 100 - $right_column - $middle_column;

$header = '<table>
<tr>
<td width="' . $left_column . '%">' . wonder_pdf_logo_url() . '</td>
<td width="' . $middle_column . '%"></td>
<td style="border-bottom: 8px solid ' . get_option('pdf_table_heading_text_color') . ';" width="' . $right_column . '%"></td>
</tr>
</table>';

//$pdf->writeHTML($header, true, false, false, false, '');

$info_right_column = '<div style="background-color: white; width: 100%; border-bottom: 8px solid ' . get_option('pdf_table_heading_text_color') . ';"></div>';
$info_left_column = wonder_pdf_logo_url();

// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 1.33) - $dimensions['lm']);

//$pdf->ln(1);

$info_left_column = '<span style="font-weight:bold;font-size:25pt; font-family:WonderUnitSans-Black;">' . _l('invoice_pdf_heading') . '</span><br />';

//$info_right_column = '<span style="font-weight:bold;font-size:21pt; font-family:WonderUnitSans-Black; text-align:left;">FRNCK.</span><br />';

$img_base64_encoded = 'data:image/png;base64,' . set_qrcode($invoice);
$img = '<img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $img_base64_encoded) . '" width="80px" style="margin:0 auto;">';

$info_right_column = '<table><tr><td style="vertical-align: middle;
    text-align:center;">' . $img . '<br /><span style="font-size: 8pt; font-family:WonderUnitSans-Regular;">*Scan QR*</span></td></tr></table>';

pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 1.33) - $dimensions['lm']);

$pdf->ln(1);

$info_left_column = '';
$info_right_column = '';

$info_left_column .= '<b style="font-size:10pt; font-family:WonderUnitSans-Bold;">Invoice No. <br>' . $invoice_number . '</b>';

if (get_option('show_status_on_pdf_ei') == 1) {
	$info_right_column .= '<br /><span style="color:rgb(' . invoice_status_color_pdf($status) . ');text-transform:uppercase;  text-align:left;font-size:10pt; font-family:WonderUnitSans-Bold;">' . format_invoice_status($status, '', false) . '</span>';
}

if ($status != Invoices_model::STATUS_PAID && $status != Invoices_model::STATUS_CANCELLED && get_option('show_pay_link_to_invoice_pdf') == 1
	&& found_invoice_mode($payment_modes, $invoice->id, false)) {
	$info_right_column .= ' - <a style="color:#84c529;text-decoration:none;text-transform:uppercase;  text-align:left; font-size:10pt; font-family:WonderUnitSans-Bold;" href="' . site_url('invoice/' . $invoice->id . '/' . $invoice->hash) . '"><1b>' . _l('view_invoice_pdf_link_pay') . '</1b></a>';
}

pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 1.33) - $dimensions['lm']);

$pdf->ln(7);

$invoice_info = '<div style="border-top: 1px solid black; text-align:left;font-size:10pt; font-family:WonderUnitSans-Regular;">
<br/>
<b style="">' . _l('invoice_data_date') . ' </b><br><span style="color:#4e4e4e;">' . _d($invoice->date) . '</span>';
if (!empty($invoice->duedate)) {
	$invoice_info .= '<br/><b style="">' . _l('invoice_data_duedate') . ' </b><br><span style="color:#4e4e4e;">' . _d($invoice->duedate) . '</span>';
}

// Bill to
$invoice_info .= '<br/><br/>
<b style="">Recepient : </b>';
$invoice_info .= '<div style="color:#4e4e4e;">';
$invoice_info .= wonder_format_customer_info($invoice, 'invoice', 'billing');
$invoice_info .= '</div>';

// ship to to
/*if ($invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1) {
$invoice_info .= '<br /><b>' . _l('ship_to') . ':</b>';
$invoice_info .= '<div style="color:#424242;">';
$invoice_info .= format_customer_info($invoice, 'invoice', 'shipping');
$invoice_info .= '</div>';
}*/
if ($invoice->sale_agent != 0 && get_option('show_sale_agent_on_invoices') == 1) {
	$invoice_info .= '<br/><br/>
<b style="">' . _l('sale_agent_string') . ': </b><br><span style="color:#4e4e4e;">' . get_staff_full_name($invoice->sale_agent) . '</span>';
}

if ($invoice->project_id != 0 && get_option('show_project_on_invoice') == 1) {
	$invoice_info .= '<br/><br/>
<b style="">' . _l('project') . ': </b><br><span style="color:#4e4e4e;"> ' . get_project_name_by_id($invoice->project_id) . '</span>';
}

foreach ($pdf_custom_fields as $field) {
	$value = get_custom_field_value($invoice->id, $field['id'], 'invoice');
	if ($value == '') {
		continue;
	}
	$invoice_info .= '<br/><br/><b style="">' . $field['name'] . ': <span style="color:#4e4e4e;">' . $value . '</span>';
}

$invoice_info .= '<div style="height:10px;"></div>';
$invoice_info .= '<div style="color:#424242; text-align:left;">';

$invoice_info .= format_organization_info();

$invoice_info .= '</div>';
$invoice_info .= '</div>';

// The items table
$items = wonder_get_items_table_data($invoice, 'invoice', 'pdf');

$tblhtml = $items->table();
$borderTotal = 'border-bottom-color:#000000;border-bottom-width:1px; border-bottom-style:solid;';
$tbltotal = '';
$tbltotal .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';
$tbltotal .= '
<tr>
    <td align="left" width="65%" rowspan="8" style="font-size:10pt; font-family:WonderUnitSans-Regular;">';

if (!empty($invoice->clientnote)) {
	$tbltotal .= '<p></p><b>' . _l('invoice_note') . '</b><br/>' . $invoice->clientnote;
}

$tbltotal .= '</td>
    <td align="right" width="20%" style="background-color:#f0f0f0;' . $borderTotal . '">' . _l('invoice_subtotal') . '</td>
    <td align="right" width="15%" style="background-color:#f0f0f0;' . $borderTotal . '">' . app_format_money($invoice->subtotal, $invoice->currency_name) . '</td>
</tr>';

if (is_sale_discount_applied($invoice)) {
	$tbltotal .= '
    <tr>
        <td align="right" width="20%" style="' . $borderTotal . '">' . _l('invoice_discount');
	if (is_sale_discount($invoice, 'percent')) {
		$tbltotal .= '(' . app_format_number($invoice->discount_percent, true) . '%)';
	}
	$tbltotal .= '';
	$tbltotal .= '</td>';
	$tbltotal .= '<td align="right" width="15%" style="' . $borderTotal . '">-' . app_format_money($invoice->discount_total, $invoice->currency_name) . '</td>
    </tr>';
}

foreach ($items->taxes() as $tax) {
	$tbltotal .= '<tr>

    <td align="right" width="20%" style="' . $borderTotal . '">' . $tax['taxname'] . ' (' . app_format_number($tax['taxrate']) . '%)' . '</td>
    <td align="right" width="15%" style="' . $borderTotal . '">' . app_format_money($tax['total_tax'], $invoice->currency_name) . '</td>
</tr>';
}

if ((int) $invoice->adjustment != 0) {
	$tbltotal .= '<tr>

    <td align="right" width="20%" style="' . $borderTotal . '">' . _l('invoice_adjustment') . '</td>
    <td align="right" width="15%" style="' . $borderTotal . '">' . app_format_money($invoice->adjustment, $invoice->currency_name) . '</td>
</tr>';
}

$tbltotal .= '
<tr style="color:red; font-weight:bold; font-size: 10.1pt;">

    <td align="right" width="20%" style="background-color:#f0f0f0;' . $borderTotal . '">' . _l('invoice_total') . '</td>
    <td align="right" width="15%" style="background-color:#f0f0f0;' . $borderTotal . '">' . app_format_money($invoice->total, $invoice->currency_name) . '</td>
</tr>';

if (count($invoice->payments) > 0 && get_option('show_total_paid_on_invoice') == 1) {
	$tbltotal .= '
    <tr>

    <td align="right" width="20%" style="' . $borderTotal . '">' . _l('invoice_total_paid') . '</td>
        <td align="right" width="15%" style="' . $borderTotal . '">-' . app_format_money(sum_from_table(db_prefix() . 'invoicepaymentrecords', [
		'field' => 'amount',
		'where' => [
			'invoiceid' => $invoice->id,
		],
	]), $invoice->currency_name) . '</td>
    </tr>';
}

if (get_option('show_credits_applied_on_invoice') == 1 && $credits_applied = total_credits_applied_to_invoice($invoice->id)) {
	$tbltotal .= '
    <tr>

    <td align="right" width="20%" style="' . $borderTotal . '">' . _l('applied_credits') . '</td>
        <td align="right" width="15%" style="' . $borderTotal . '">-' . app_format_money($credits_applied, $invoice->currency_name) . '</td>
    </tr>';
}

if (get_option('show_amount_due_on_invoice') == 1 && $invoice->status != Invoices_model::STATUS_CANCELLED) {
	$tbltotal .= '<tr>

    <td align="right" width="20%"  style="background-color:#f0f0f0;' . $borderTotal . '">' . _l('invoice_amount_due') . '</td>
       <td align="right" width="15%" style="background-color:#f0f0f0;' . $borderTotal . '">' . app_format_money($invoice->total_left_to_pay, $invoice->currency_name) . '</td>
   </tr>';
}

$tbltotal .= '</table>';
$tblhtml .= $tbltotal;
if (get_option('total_to_words_enabled') == 1) {
	$tblhtml .= '<b>' . _l('num_word') . ': ' . $CI->numberword->convert($invoice->total, $invoice->currency_name) . '</b>';
}
if (count($invoice->payments) > 0 && get_option('show_transactions_on_invoice_pdf') == 1) {
	$border = 'border-bottom-color:#000000;border-bottom-width:1px;border-bottom-style:solid; 1px solid black;';
	$tblhtml .= '<p></p><p></p><p></p><div><b>' . _l('invoice_received_payments') . '</b></div>';
	$tblhtml .= '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="0">
        <tr height="20"  style="color:#000;border:1px solid #000;">
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_number_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_mode_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_date_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_amount_heading') . '</th>
    </tr>';
	$tblhtml .= '<tbody>';
	foreach ($invoice->payments as $payment) {
		$payment_name = $payment['name'];
		if (!empty($payment['paymentmethod'])) {
			$payment_name .= ' - ' . $payment['paymentmethod'];
		}
		$tblhtml .= '
            <tr>
            <td>' . $payment['paymentid'] . '</td>
            <td>' . $payment_name . '</td>
            <td>' . _d($payment['date']) . '</td>
            <td>' . app_format_money($payment['amount'], $invoice->currency_name) . '</td>
            </tr>
        ';
	}
	$tblhtml .= '</tbody>';
	$tblhtml .= '</table>';
}

if (found_invoice_mode($payment_modes, $invoice->id, true, true)) {
	$tblhtml .= '<p></p><div><b>' . _l('invoice_html_offline_payment') . '</b><br/><br/>';

	foreach ($payment_modes as $mode) {
		if (is_numeric($mode['id'])) {
			if (!is_payment_mode_allowed_for_invoice($mode['id'], $invoice->id)) {
				continue;
			}
		}
		if (isset($mode['show_on_pdf']) && $mode['show_on_pdf'] == 1) {
			$tblhtml .= '<b>' . $mode['name'] . '</b><br/>';
			$tblhtml .= $mode['description'] . '</div>';
		}
	}
}

/*if (!empty($invoice->clientnote)) {
$tblhtml .= '<p></p><b>' . _l('invoice_note') . '</b><br/>' . $invoice->clientnote;
}*/

if (!empty($invoice->terms)) {
	$tblhtml .= '<p></p><b>' . _l('terms_and_conditions') . '</b><br/>' . $invoice->terms;
}

$info_left_column = '<table style="font-size:10pt; font-family:WonderUnitSans-Regular;"><tr><td style="width:95%;">' . $tblhtml . '</td><td style="width:5%;"></td></tr></table>';

pdf_multi_row($info_left_column, $invoice_info, $pdf, ($dimensions['wk'] / 1.33) - $dimensions['lm']);
