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

$font_size = get_option('pdf_font_size');
if ($font_size == '') {
	$font_size = 10;
}

$borderBottom = 'border-bottom-color:#000000;border-bottom-width:1px; border-bottom-style:solid;';
$borderTop = 'border-top-color:#000000;border-top-width:1px; border-top-style:solid;';
$table_border = "border:1px solid #ccc;";

// top_header
$top_header = '
<table border="0" style="border-spacing:3px 3px;padding:3px 4px 3px 4px; width:100%; margin-bottom:2px;">
<tbody>
<tr>
<td style="text-align:left; font-size: ' . ($font_size + 4) . 'px; font-style: italic; word-wrap: normal;">“' . SLOGAN . '”</td>
<td style="text-align:right;"><h1 style="padding:0; margin:0; font-size:40px;">' . _l('invoice_pdf_heading') . '</h1></td>
</tr>
<tr><td colspan="2" style="'.$borderTop.'"></td></tr>
</tbody>
</table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $top_header, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(0);

//logo area
$info_right_column = '';
$logo_area = '';

if (get_option('show_status_on_pdf_ei') == 1) {
	$info_right_column .= '
  <table style="text-align:center;border-spacing:3px; padding:3px 4px 3px 4px;">
  <tbody>
  <tr>
  <td></td>
  <td></td>
  <td style="background-color:rgb(' . invoice_status_color_pdf($status) . '); color:#fff; font-size: ' . ($font_size + 4) . 'px; ">' . strtoupper(format_invoice_status($status, '', false)) . '</td>
  </tr>
  </tbody>
  </table>';
}

$info_right_column .= '<b style="color:#4e4e4e; font-size: ' . ($font_size + 4) . 'px;"># ' . $invoice_number . '</b>';

if ($status != 2 && $status != 5 && get_option('show_pay_link_to_invoice_pdf') == 1) {
	$info_right_column .= '<br /><a style="color:#84c529;text-decoration:none;font-size: ' . ($font_size + 4) . 'px;" href="' . site_url('viewinvoice/' . $invoice->id . '/' . $invoice->hash) . '">' . _l('view_invoice_pdf_link_pay') . '</a>';
}
//dates
$info_right_column .= '<br><b style="color:#4e4e4e; font-size: ' . ($font_size + 4) . 'px;">' . _l('invoice_data_date') . ' ' . _d($invoice->date) . '<br>' . _l('invoice_data_duedate') . ' ' . _d($invoice->duedate) . '</b>';

// write the first column
$logo_area .= pdf_logo_url();
$pdf->MultiCell(($dimensions['wk'] / 2) - $dimensions['lm'], 0, $logo_area, 0, 'J', 0, 0, '', '', true, 0, true, true, 0);
// write the second column
$pdf->MultiCell(($dimensions['wk'] / 2) - $dimensions['rm'], 0, $info_right_column, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(6);

//Billing & Shipping Section
$info_bill_shipping = '
<table border="0" cellpadding="5">
<thead>
<tr bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold;">
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'"><b>' . strtoupper("Company Info") . '</b></td>
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'"><b>' . strtoupper(_l('invoice_bill_to')) . '</b></td>';
if ($invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1) {
	$info_bill_shipping .= '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'"><b>' . strtoupper(_l('ship_to')) . '</b></td>';
}
$info_bill_shipping .= '</tr>
</thead>
<tbody>
<tr>
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'">';
$invoice_info = '<div style="color:#424242;">';
$invoice_info .= '<b style="color:black;">' . get_option('invoice_company_name') . '</b><br />';
$invoice_info .= get_option('invoice_company_address') . '<br/>';
if (get_option('invoice_company_city') != '') {
	$invoice_info .= get_option('invoice_company_city') . ', ';
}
$invoice_info .= get_option('company_state') . ' ';
$invoice_info .= get_option('invoice_company_postal_code') . '<br />';
$invoice_info .= get_option('invoice_company_country_code') . '';

if (get_option('invoice_company_phonenumber') != '') {
	$invoice_info .= '<br />' . get_option('invoice_company_phonenumber');
}
if (get_option('company_vat') != '') {
	$invoice_info .= '<br />' . _l('company_vat_number') . ': ' . get_option('company_vat');
}
// check for company custom fields
$custom_company_fields = get_company_custom_fields();
if (count($custom_company_fields) > 0) {
	$invoice_info .= '<br />';
}
foreach ($custom_company_fields as $field) {
	$invoice_info .= $field['label'] . ': ' . $field['value'] . '<br />';
}
$invoice_info .= '</div>';
$info_bill_shipping .= $invoice_info . '</td>';
$info_bill_shipping .= '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'">';
// Bill to
$client_details = '<div style="color:#424242;">';
if ($invoice->client->show_primary_contact == 1) {
	$pc_id = get_primary_contact_user_id($invoice->clientid);
	if ($pc_id) {
		$client_details .= get_contact_full_name($pc_id) . '<br />';
	}
}

$client_details .= $invoice->client->company . '<br />';
$client_details .= $invoice->billing_street . '<br />';
if (!empty($invoice->billing_city)) {
	$client_details .= $invoice->billing_city;
}
if (!empty($invoice->billing_state)) {
	$client_details .= ', ' . $invoice->billing_state;
}
$billing_country = get_country_short_name($invoice->billing_country);
if (!empty($billing_country)) {
	$client_details .= '<br />' . $billing_country;
}
if (!empty($invoice->billing_zip)) {
	$client_details .= ', ' . $invoice->billing_zip;
}
if (!empty($invoice->client->vat)) {
	$client_details .= '<br />' . _l('invoice_vat') . ': ' . $invoice->client->vat;
}
// check for invoice custom fields which is checked show on pdf
$pdf_custom_fields = get_custom_fields('customers', array(
	'show_on_pdf' => 1,
));
if (count($pdf_custom_fields) > 0) {
	$client_details .= '<br />';
	foreach ($pdf_custom_fields as $field) {
		$value = get_custom_field_value($invoice->clientid, $field['id'], 'customers');
		if ($value == '') {
			continue;
		}
		$client_details .= $field['name'] . ': ' . $value . '<br />';
	}
}
$client_details .= '</div>';
$info_bill_shipping .= $client_details . '</td>';
// ship to to
if ($invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1) {
	$shipping_details = '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'">';
	$shipping_details .= '<div style="color:#424242;">';
	$shipping_details .= $invoice->shipping_street . '<br />' . $invoice->shipping_city . ', ' . $invoice->shipping_state . '<br />' . get_country_short_name($invoice->shipping_country) . ', ' . $invoice->shipping_zip;
	$shipping_details .= '</div></td>';
	$info_bill_shipping .= $shipping_details;
}

$info_bill_shipping .= '</tr>
</tbody>
</table>';
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_bill_shipping, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(6);

//Sales Person Area
$info_sales_person = '
<table border="0" cellpadding="5">
<thead>
<tr bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold;">
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'"><b>' . strtoupper(_l('sale_agent_string')) . '</b></td>
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'"><b>' . strtoupper('Payment Terms') . '</b></td>
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'"><b>' . strtoupper('Due Date') . '</b></td>
</tr>
</thead>
<tbody>
<tr style="color:#424242;">
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'">';
if ($invoice->sale_agent != 0) {
	if (get_option('show_sale_agent_on_invoices') == 1) {
		$info_sales_person .= get_staff_full_name($invoice->sale_agent);
	}
}
$info_sales_person .= '</td>';
$info_sales_person .= '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'">Due on Receipt</td><td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'">Upon Receipt of Goods/Services</td>';
$info_sales_person .= '</tr>
</tbody>
</table>';
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_sales_person, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(6);

// check for invoice custom fields which is checked show on pdf
$pdf_custom_fields = get_custom_fields('invoice', array(
	'show_on_pdf' => 1,
));
foreach ($pdf_custom_fields as $field) {
	$value = get_custom_field_value($invoice->id, $field['id'], 'invoice');
	if ($value == '') {
		continue;
	}
	$pdf->writeHTMLCell(0, '', '', '', $field['name'] . ': ' . $value, 0, 1, false, true, ($swap == '1' ? 'J' : 'R'), true);
}

//Item Table
$pdf->Ln(7);
$item_width = 38;
// If show item taxes is disabled in PDF we should increase the item width table heading
if (get_option('show_tax_per_item') == 0) {
	$item_width = $item_width + 15;
}
// Header
$qty_heading = _l('invoice_table_quantity_heading');
if ($invoice->show_quantity_as == 2) {
	$qty_heading = _l('invoice_table_hours_heading');
} elseif ($invoice->show_quantity_as == 3) {
	$qty_heading = _l('invoice_table_quantity_heading') . '/' . _l('invoice_table_hours_heading');
}
$tblhtml = '
<table width="100%" border="0" bgcolor="#fff" cellspacing="0" cellpadding="5">
    <tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold;">
        <th width="5%;" align="center" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'">#</th>
        <th width="' . $item_width . '%" align="left" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'">' . strtoupper(_l('invoice_table_item_heading')) . '</th>
        <th width="12%" align="right" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'">' . strtoupper($qty_heading) . '</th>
        <th width="15%" align="right" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'">' . strtoupper(_l('invoice_table_rate_heading')) . '</th>';
if (get_option('show_tax_per_item') == 1) {
	$tblhtml .= '<th width="15%" align="right" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'">' . strtoupper(_l('invoice_table_tax_heading')) . '</th>';
}
$tblhtml .= '<th width="15%" align="right" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'">' . strtoupper(_l('invoice_table_amount_heading')) . '</th>
</tr>';
// Items
$tblhtml .= '<tbody>';

$items_data = pdf_get_table_items_and_taxes($invoice->items, 'invoice',false, $table_border);
$tblhtml .= $items_data['html'];
$taxes = $items_data['taxes'];

$tblhtml .= '</tbody>';
$tblhtml .= '</table>';
$pdf->writeHTML($tblhtml, true, false, false, false, '');

$pdf->Ln(-6);
$tbltotal = '';

$tbltotal .= '<table cellpadding="5" style="font-size:' . ($font_size + 4) . 'px" border="0">';
$tbltotal .= '
<tr>
    <td width="55%"></td>
    <td align="right" width="30%" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('invoice_subtotal') . '</strong></td>
    <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . app_format_money($invoice->subtotal, $invoice->symbol) . '</td>
</tr>';
if ($invoice->discount_percent != 0) {
	$tbltotal .= '
    <tr>
    <td width="55%"></td>
        <td align="right" width="30%" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('invoice_discount') . '(' . _format_number($invoice->discount_percent, true) . '%)' . '</strong></td>
        <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">-' . app_format_money($invoice->discount_total, $invoice->symbol) . '</td>
    </tr>';
}
foreach ($taxes as $tax) {
	$total = array_sum($tax['total']);
	if ($invoice->discount_percent != 0 && $invoice->discount_type == 'before_tax') {
		$total_tax_calculated = ($total * $invoice->discount_percent) / 100;
		$total = ($total - $total_tax_calculated);
	}
	// The tax is in format TAXNAME|20
	$_tax_name = explode('|', $tax['tax_name']);
	$tbltotal .= '<tr>
    <td width="55%"></td>
    <td align="right" width="30%" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . $_tax_name[0] . '(' . _format_number($tax['taxrate']) . '%)' . '</strong></td>
    <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . app_format_money($total, $invoice->symbol) . '</td>
</tr>';
}
if ((int) $invoice->adjustment != 0) {
	$tbltotal .= '<tr>
    <td width="55%"></td>
    <td align="right" width="30%" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('invoice_adjustment') . '</strong></td>
    <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . app_format_money($invoice->adjustment, $invoice->symbol) . '</td>
</tr>';
}
$tbltotal .= '
<tr>
    <td width="55%"></td>
    <td align="right" width="30%" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('invoice_total') . '</strong></td>
    <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . app_format_money($invoice->total, $invoice->symbol) . '</td>
</tr>';

if ($invoice->status == 3) {
	$tbltotal .= '
    <tr>
    <td width="55%"></td>
        <td align="right" width="30%" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('invoice_total_paid') . '</strong></td>
        <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . app_format_money(sum_from_table('tblinvoicepaymentrecords', array(
		'field' => 'amount',
		'where' => array(
			'invoiceid' => $invoice->id,
		),
	)), $invoice->symbol) . '</td>
    </tr>
    <tr>
    <td width="55%"></td>
       <td align="right" width="30%" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('invoice_amount_due') . '</strong></td>
       <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . app_format_money(get_invoice_total_left_to_pay($invoice->id, $invoice->total), $invoice->symbol) . '</td>
   </tr>';
}
$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');

if (get_option('total_to_words_enabled') == 1) {
	// Set the font bold
	$pdf->SetFont($font_name, 'B', $font_size);
	$pdf->Cell(0, 0, _l('num_word') . ': ' . $CI->numberword->convert($invoice->total, $invoice->currency_name), 0, 1, 'C', 0, '', 0);
	// Set the font again to normal like the rest of the pdf
	$pdf->SetFont($font_name, '', $font_size);
	$pdf->Ln(4);
}

if (count($invoice->payments) > 0 && get_option('show_transactions_on_invoice_pdf') == 1) {
	$pdf->Ln(4);
	$border = 'color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold; '.$table_border.''; //'border-bottom-color:#000000;border-bottom-width:1px;border-bottom-style:solid; 1px solid black;';
	$pdf->SetFont($font_name, 'B', $font_size);
	$pdf->Cell(0, 0, _l('invoice_received_payments'), 0, 1, 'L', 0, '', 0);
	$pdf->SetFont($font_name, '', $font_size);
	$pdf->Ln(4);
	$tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="0">
        <tr height="20"  style="color:#000;border:1px solid #000;">
        <th width="25%;" bgcolor="' . get_option('pdf_table_heading_color') . '" style="' . $border . '">' . _l('invoice_payments_table_number_heading') . '</th>
        <th width="25%;" bgcolor="' . get_option('pdf_table_heading_color') . '" style="' . $border . '">' . _l('invoice_payments_table_mode_heading') . '</th>
        <th width="25%;" bgcolor="' . get_option('pdf_table_heading_color') . '" style="' . $border . '">' . _l('invoice_payments_table_date_heading') . '</th>
        <th width="25%;" bgcolor="' . get_option('pdf_table_heading_color') . '" style="' . $border . '">' . _l('invoice_payments_table_amount_heading') . '</th>
    </tr>';
	$tblhtml .= '<tbody>';
	foreach ($invoice->payments as $payment) {
		$payment_name = $payment['name'];
		if (!empty($payment['paymentmethod'])) {
			$payment_name .= ' - ' . $payment['paymentmethod'];
		}
		$tblhtml .= '
            <tr>
            <td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'">' . $payment['paymentid'] . '</td>
            <td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'">' . $payment_name . '</td>
            <td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'">' . _d($payment['date']) . '</td>
            <td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.'">' . app_format_money($payment['amount'], $invoice->symbol) . '</td>
            </tr>
        ';
	}
	$tblhtml .= '</tbody>';
	$tblhtml .= '</table>';
	$pdf->writeHTML($tblhtml, true, false, false, false, '');
}

if (found_invoice_mode($payment_modes, $invoice->id, true, true)) {
	$pdf->Ln(4);
	$pdf->SetFont($font_name, 'B', ($font_size));
	$pdf->Cell(0, 0, _l('invoice_html_offline_payment'), 0, 1, 'L', 0, '', 0);
	$pdf->SetFont($font_name, '', ($font_size));
	foreach ($payment_modes as $mode) {
		if (is_numeric($mode['id'])) {
			if (!is_payment_mode_allowed_for_invoice($mode['id'], $invoice->id)) {
				continue;
			}
		}
		if (isset($mode['show_on_pdf']) && $mode['show_on_pdf'] == 1) {
			$pdf->Ln(2);
			$pdf->Cell(0, 0, $mode['name'], 0, 1, 'L', 0, '', 0);
			$pdf->MultiCell($dimensions['wk'] - ($dimensions['lm'] + $dimensions['rm']), 0, clear_textarea_breaks($mode['description']), 0, 'L');
		}
	}
}

if (!empty($invoice->clientnote)) {
	$pdf->Ln(4);
	$pdf->SetFont($font_name, 'B', $font_size);
	$pdf->Cell(0, 0, _l('invoice_note'), 0, 1, 'L', 0, '', 0);
	$pdf->SetFont($font_name, '', $font_size);
	$pdf->Ln(2);
	$pdf->writeHTMLCell('', '', '', '', $invoice->clientnote, 0, 1, false, true, 'L', true);
}

if (!empty($invoice->terms)) {
	$pdf->Ln(4);
	$pdf->SetFont($font_name, 'B', $font_size);
	$pdf->Cell(0, 0, _l('terms_and_conditions'), 0, 1, 'L', 0, '', 0);
	$pdf->SetFont($font_name, '', $font_size);
	$pdf->Ln(2);
	$pdf->writeHTMLCell('', '', '', '', $invoice->terms, 0, 1, false, true, 'L', true);
}

$pdf->Ln(4);
$footer_thank_msg = '
<table border="0" cellpadding="5">
<thead>';
if (get_option('e_sign_invoice')) {
	$footer_thank_msg .= '<tr>
  <td>' . pdf_signatures() . '</td>
  </tr>';
}
$footer_thank_msg .= '<tr><td style="vertical-align: middle; text-align:center; font-size: ' . ($font_size + 6) . 'px;"><b>Borehole services, Water pumps, Solar, Generators, Pools, Water treatment & Irrigation</b></td></tr>';
$footer_thank_msg .= '<tr><td style="vertical-align: middle; text-align:center; font-size: ' . ($font_size + 6) . 'px; border-top:2px solid #dedede;"><b>' . strtoupper("Thank You For Your Business") . '</b></td>
</tr>
</thead>
</table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $footer_thank_msg, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
