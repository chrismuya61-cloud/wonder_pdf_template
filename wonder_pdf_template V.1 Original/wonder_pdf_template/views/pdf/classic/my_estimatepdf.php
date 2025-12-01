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
<td style="text-align:right;"><h1 style="padding:0; margin:0; font-size:40px;">' . _l('estimate_pdf_heading') . '</h1></td>
</tr>
<tr><td colspan="2" style="'.$borderTop.'"></td></tr>
</tbody>
</table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $top_header, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(0);

//logo area
$info_right_column = '';
$logo_area  = '';

if (get_option('show_status_on_pdf_ei') == 1) {
    $info_right_column .= '
    <table style="text-align:center;border-spacing:3px; padding:3px 4px 3px 4px;">
    <tbody>
    <tr>
    <td></td>
    <td></td>
    <td style="background-color:rgb(' . estimate_status_color_pdf($status) . '); color:#fff; font-size: ' . ($font_size + 4) . 'px; ">' . strtoupper(format_estimate_status($status, '', false)) . '</td>
    </tr>
    </tbody>
    </table>';
}

$info_right_column .= '<b style="color:#4e4e4e; font-size: ' . ($font_size + 4) . 'px;"># ' . $estimate_number . '</b>';

//dates
$info_right_column .= '<br><b style="color:#4e4e4e; font-size: ' . ($font_size + 4) . 'px;">' . _l('estimate_data_date') . ' ' . _d($estimate->date) . '</b>';
if (!empty($estimate->expirydate)) {
  $info_right_column .= '<br><b style="color:#4e4e4e; font-size: ' . ($font_size + 4) . 'px;">' . _l('estimate_data_expiry_date') . ' ' . _d($estimate->expirydate) . '</b>';
}
if (!empty($estimate->reference_no)) {
  $info_right_column .= '<br><b style="color:#4e4e4e; font-size: ' . ($font_size + 4) . 'px;">' . _l('reference_no') . ' ' . $estimate->reference_no . '</b>';
}
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
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';"><b>' . strtoupper("Company Info") . '</b></td>
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';"><b>' . strtoupper(_l('estimate_to')) . '</b></td>';
if ($estimate->include_shipping == 1 && $estimate->show_shipping_on_estimate == 1) {
  $info_bill_shipping .= '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';"><b>' . strtoupper(_l('ship_to')) . '</b></td>';
}
$info_bill_shipping .= '</tr>
</thead>
<tbody>
<tr>
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';">';
$estimate_info = '<div style="color:#424242;">';
$estimate_info .= '<b style="color:black;">' . get_option('invoice_company_name') . '</b><br />';
$estimate_info .= get_option('invoice_company_address') . '<br/>';
if (get_option('invoice_company_city') != '') {
  $estimate_info .= get_option('invoice_company_city') . ', ';
}
$estimate_info .= get_option('company_state') . ' ';
$estimate_info .= get_option('invoice_company_postal_code') . '<br />';
$estimate_info .= get_option('invoice_company_country_code') . '';

if (get_option('invoice_company_phonenumber') != '') {
  $estimate_info .= '<br />' . get_option('invoice_company_phonenumber');
}
if (get_option('company_vat') != '') {
  $estimate_info .= '<br />' . _l('company_vat_number') . ': ' . get_option('company_vat');
}
// check for company custom fields
$custom_company_fields = get_company_custom_fields();
if (count($custom_company_fields) > 0) {
  $estimate_info .= '<br />';
}
foreach ($custom_company_fields as $field) {
  $estimate_info .= $field['label'] . ': ' . $field['value'] . '<br />';
}
$estimate_info .= '</div>';
$info_bill_shipping .= $estimate_info . '</td>';
$info_bill_shipping .= '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';">';
// Bill to
$client_details = '<div style="color:#424242;">';
if ($estimate->client->show_primary_contact == 1) {
  $pc_id = get_primary_contact_user_id($estimate->clientid);
  if ($pc_id) {
    $client_details .= get_contact_full_name($pc_id) . '<br />';
  }
}

$client_details .= $estimate->client->company . '<br />';
$client_details .= $estimate->billing_street . '<br />';
if (!empty($estimate->billing_city)) {
  $client_details .= $estimate->billing_city;
}
if (!empty($estimate->billing_state)) {
  $client_details .= ', ' . $estimate->billing_state;
}
$billing_country = get_country_short_name($estimate->billing_country);
if (!empty($billing_country)) {
  $client_details .= '<br />' . $billing_country;
}
if (!empty($estimate->billing_zip)) {
  $client_details .= ', ' . $estimate->billing_zip;
}
if (!empty($estimate->client->vat)) {
  $client_details .= '<br />' . _l('estimate_vat') . ': ' . $estimate->client->vat;
}
// check for estimate custom fields which is checked show on pdf
$pdf_custom_fields = get_custom_fields('customers', array(
  'show_on_pdf' => 1
));
if (count($pdf_custom_fields) > 0) {
  $client_details .= '<br />';
  foreach ($pdf_custom_fields as $field) {
    $value = get_custom_field_value($estimate->clientid, $field['id'], 'customers');
    if ($value == '') {
      continue;
    }
    $client_details .= $field['name'] . ': ' . $value . '<br />';
  }
}
$client_details .= '</div>';
$info_bill_shipping .= $client_details . '</td>';
// ship to to
if ($estimate->include_shipping == 1 && $estimate->show_shipping_on_estimate == 1) {
  $shipping_details = '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';">';
  $shipping_details .= '<div style="color:#424242;">';
  $shipping_details .= $estimate->shipping_street . '<br />' . $estimate->shipping_city . ', ' . $estimate->shipping_state . '<br />' . get_country_short_name($estimate->shipping_country) . ', ' . $estimate->shipping_zip;
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
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';"><b>' . strtoupper(_l('sale_agent_string')) . '</b></td>
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';"><b>' . strtoupper('Shipping Method & Terms') . '</b></td>
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';"><b>' . strtoupper('Delivery Date') . '</b></td>
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';"><b>' . strtoupper('Payment Terms') . '</b></td>
</tr>
</thead>
<tbody>
<tr style="color:#424242;">
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';">';
if ($estimate->sale_agent != 0) {
  if (get_option('show_sale_agent_on_estimates') == 1) {
    $info_sales_person .= get_staff_full_name($estimate->sale_agent);
  }
}
$info_sales_person .= '</td>';
$info_sales_person .= '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';">To be organised by buyer/Purchaser</td><td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';">Upon Order</td><td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';">Due on Receipt</td>';
$info_sales_person .= '</tr>
</tbody>
</table>';
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_sales_person, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(6);

// check for estimate custom fields which is checked show on pdf
$pdf_custom_fields = get_custom_fields('estimate', array(
  'show_on_pdf' => 1
));
foreach ($pdf_custom_fields as $field) {
  $value = get_custom_field_value($estimate->id, $field['id'], 'estimate');
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
$qty_heading = _l('estimate_table_quantity_heading');
if ($estimate->show_quantity_as == 2) {
  $qty_heading = _l('estimate_table_hours_heading');
} elseif ($estimate->show_quantity_as == 3) {
  $qty_heading = _l('estimate_table_quantity_heading') . '/' . _l('estimate_table_hours_heading');
}
$tblhtml = '
<table width="100%" border="0" bgcolor="#fff" cellspacing="0" cellpadding="5">
    <tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold;">
        <th width="5%;" align="center" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';">#</th>
        <th width="' . $item_width . '%" align="left" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';">' . strtoupper(_l('estimate_table_item_heading')) . '</th>
        <th width="12%" align="right" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';">' . strtoupper($qty_heading) . '</th>
        <th width="15%" align="right" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';">' . strtoupper(_l('estimate_table_rate_heading')) . '</th>';
if (get_option('show_tax_per_item') == 1) {
  $tblhtml .= '<th width="15%" align="right" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';">' . strtoupper(_l('estimate_table_tax_heading')) . '</th>';
}
$tblhtml .= '<th width="15%" align="right" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';">' . strtoupper(_l('estimate_table_amount_heading')) . '</th>
</tr>';
// Items
$tblhtml .= '<tbody>';

$items_data = pdf_get_table_items_and_taxes($estimate->items, 'estimate', false, $table_border);
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
    <td align="right" width="30%" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('estimate_subtotal') . '</strong></td>
    <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . app_format_money($estimate->subtotal, $estimate->symbol) . '</td>
</tr>';
if ($estimate->discount_percent != 0) {
  $tbltotal .= '
    <tr>
    <td width="55%"></td>
        <td align="right" width="30%" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('estimate_discount') . '(' . _format_number($estimate->discount_percent, true) . '%)' . '</strong></td>
        <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">-' . app_format_money($estimate->discount_total, $estimate->symbol) . '</td>
    </tr>';
}
foreach ($taxes as $tax) {
  $total = array_sum($tax['total']);
  if ($estimate->discount_percent != 0 && $estimate->discount_type == 'before_tax') {
    $total_tax_calculated = ($total * $estimate->discount_percent) / 100;
    $total                = ($total - $total_tax_calculated);
  }
  // The tax is in format TAXNAME|20
  $_tax_name = explode('|', $tax['tax_name']);
  $tbltotal .= '<tr>
    <td width="55%"></td>
    <td align="right" width="30%" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . $_tax_name[0] . '(' . _format_number($tax['taxrate']) . '%)' . '</strong></td>
    <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . app_format_money($total, $estimate->symbol) . '</td>
</tr>';
}
if ((int)$estimate->adjustment != 0) {
  $tbltotal .= '<tr>
    <td width="55%"></td>
    <td align="right" width="30%" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('estimate_adjustment') . '</strong></td>
    <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . app_format_money($estimate->adjustment, $estimate->symbol) . '</td>
</tr>';
}
$tbltotal .= '
<tr>
    <td width="55%"></td>
    <td align="right" width="30%" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('estimate_total') . '</strong></td>
    <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . app_format_money($estimate->total, $estimate->symbol) . '</td>
</tr>';

$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');

if (get_option('total_to_words_enabled') == 1) {
  // Set the font bold
  $pdf->SetFont($font_name, 'B', $font_size);
  $pdf->Cell(0, 0, _l('num_word') . ': ' . $CI->numberword->convert($estimate->total, $estimate->currency_name), 0, 1, 'C', 0, '', 0);
  // Set the font again to normal like the rest of the pdf
  $pdf->SetFont($font_name, '', $font_size);
  $pdf->Ln(4);
}


if (!empty($estimate->clientnote)) {
  $pdf->Ln(4);
  $pdf->SetFont($font_name, 'B', $font_size);
  $pdf->Cell(0, 0, _l('estimate_note'), 0, 1, 'L', 0, '', 0);
  $pdf->SetFont($font_name, '', $font_size);
  $pdf->Ln(2);
  $pdf->writeHTMLCell('', '', '', '', $estimate->clientnote, 0, 1, false, true, 'L', true);
}

if (!empty($estimate->terms)) {
  $pdf->Ln(4);
  $pdf->SetFont($font_name, 'B', $font_size);
  $pdf->Cell(0, 0, _l('terms_and_conditions'), 0, 1, 'L', 0, '', 0);
  $pdf->SetFont($font_name, '', $font_size);
  $pdf->Ln(2);
  $pdf->writeHTMLCell('', '', '', '', $estimate->terms, 0, 1, false, true, 'L', true);
}

$pdf->Ln(4);
$footer_thank_msg = '
<table border="0" cellpadding="5">
<thead>';
if (get_option('e_sign_estimate')) {
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
