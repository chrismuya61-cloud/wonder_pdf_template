<?php
$dimensions = $pdf->getPageDimensions();



$font_size = get_option('pdf_font_size');
if ($font_size == '') {
	$font_size = 10;
}

$pdf->SetMargins(PDF_MARGIN_LEFT, 20, PDF_MARGIN_RIGHT);

$pdf->ln(40);

$heading = '<span style="font-weight:bold;font-size:27px; text-align:center;"><u>' . _l('estimate_pdf_heading') . '</u></span>';
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $heading, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(10);

//Billing & Shipping Section

$info_bill_shipping = '
<table border="0" cellpadding="5">
<thead>
<tr bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold;">
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"><b>' . strtoupper(_l('estimate_to')) . '</b></td>';
$info_bill_shipping .= '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"><b></b></td>';
$info_bill_shipping .= '</tr>
</thead>
<tbody>
<tr>';
$info_bill_shipping .= '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">';
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
	'show_on_pdf' => 1,
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

$shipping_details = '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">';
$info_right_column = '';

if (get_option('show_status_on_pdf_ei') == 1) {
	$status_name = format_estimate_status($status, '', false);
	// Top
	// Draft
	if ($status == 1) {
		$bg_status = '119, 119, 119';
	} else if ($status == 2) {
		// Sent
		$bg_status = '3, 169, 244';
	} else if ($status == 3) {
		//Declines
		$bg_status = '252, 45, 66';
	} else if ($status == 4) {
		//Accepted
		$bg_status = '0, 191, 54';
	} else {
		// Expired
		$bg_status = '255, 111, 0';
	}

	$info_right_column .= '
  <table style="text-align:center;border-spacing:3px 3px;padding:3px 4px 3px 4px;">
  <tbody>
  <tr>
  <td></td>
  <td></td>
  <td style="background-color:rgb(' . $bg_status . ');color:#fff;">' . mb_strtoupper($status_name, 'UTF-8') . '</td>
  </tr>
  </tbody>
  </table>';
}
$info_right_column .= '<div style="text-align: right"><b style="color:#4e4e4e;"># ' . $estimate_number . '</b>';

//dates
$info_right_column .= '<br><span style="color:#4e4e4e; font_size:12px;">' . _l('estimate_data_date') . ' ' . _d($estimate->date) . '</span>';
if (!empty($estimate->expirydate)) {
	$info_right_column .= '<br><span style="color:#4e4e4e; font_size:12px;">' . _l('estimate_data_expiry_date') . ' ' . _d($estimate->expirydate) . '</span>';
}
if (!empty($estimate->reference_no)) {
	$info_right_column .= '<br><span style="color:#4e4e4e; font_size:12px;">' . _l('reference_no') . ' ' . $estimate->reference_no . '</span>';
}
$shipping_details .= $info_right_column;
$shipping_details .= '</div></td>';
$info_bill_shipping .= $shipping_details;

$info_bill_shipping .= '</tr>
</tbody>
</table>';
$pdf->writeHTML($info_bill_shipping, true, false, false, false, '');
//$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_bill_shipping, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
//$pdf->ln(6);

//Sales Person Area
$info_sales_person = '
<table border="1" cellpadding="5">
<thead>
<tr bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold;">
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"><b>' . strtoupper(_l('sale_agent_string')) . '</b></td>
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"><b>' . strtoupper('Shipping Method & Terms') . '</b></td>
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"><b>' . strtoupper('Delivery Date') . '</b></td>
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"><b>' . strtoupper('Payment Terms') . '</b></td>
</tr>
</thead>
<tbody>
<tr style="color:#424242;">
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">';
if ($estimate->sale_agent != 0) {
	if (get_option('show_sale_agent_on_estimates') == 1) {
		$info_sales_person .= get_staff_full_name($estimate->sale_agent);
	}
}
$info_sales_person .= '</td>';
$info_sales_person .= '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">To be organised by buyer/Purchaser</td><td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">Upon Order</td><td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">Due on Receipt</td>';
$info_sales_person .= '</tr>
</tbody>
</table>';

//$pdf->writeHTML($info_sales_person, true, false, false, false, '');
//$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_sales_person, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
//$pdf->ln(6);

// check for estimate custom fields which is checked show on pdf
$pdf_custom_fields = get_custom_fields('estimate', array(
	'show_on_pdf' => 1,
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
        <th width="5%;" align="center" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">#</th>
        <th width="' . $item_width . '%" align="left" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">' . strtoupper(_l('estimate_table_item_heading')) . '</th>
        <th width="12%" align="right" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">' . strtoupper($qty_heading) . '</th>
        <th width="15%" align="right" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">' . strtoupper(_l('estimate_table_rate_heading')) . '</th>';
if (get_option('show_tax_per_item') == 1) {
	$tblhtml .= '<th width="15%" align="right" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">' . strtoupper(_l('estimate_table_tax_heading')) . '</th>';
}
$tblhtml .= '<th width="15%" align="right" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">' . strtoupper(_l('estimate_table_amount_heading')) . '</th>
</tr>';
// Items
$tblhtml .= '<tbody>';

$items_data = pdf_get_table_items_and_taxes($estimate->items, 'estimate');
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
    <td width="70%"></td>
    <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('estimate_subtotal') . '</strong></td>
    <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . app_format_money($estimate->subtotal, $estimate->symbol) . '</td>
</tr>';
if ($estimate->discount_percent != 0) {
	$tbltotal .= '
    <tr>
    <td width="70%"></td>
        <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('estimate_discount') . '(' . _format_number($estimate->discount_percent, true) . '%)' . '</strong></td>
        <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">-' . app_format_money($estimate->discount_total, $estimate->symbol) . '</td>
    </tr>';
}
foreach ($taxes as $tax) {
	$total = array_sum($tax['total']);
	if ($estimate->discount_percent != 0 && $estimate->discount_type == 'before_tax') {
		$total_tax_calculated = ($total * $estimate->discount_percent) / 100;
		$total = ($total - $total_tax_calculated);
	}
	// The tax is in format TAXNAME|20
	$_tax_name = explode('|', $tax['tax_name']);
	$tbltotal .= '<tr>
    <td width="70%"></td>
    <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . $_tax_name[0] . '(' . _format_number($tax['taxrate']) . '%)' . '</strong></td>
    <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . app_format_money($total, $estimate->symbol) . '</td>
</tr>';
}
if ((int) $estimate->adjustment != 0) {
	$tbltotal .= '<tr>
    <td width="70%"></td>
    <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('estimate_adjustment') . '</strong></td>
    <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . app_format_money($estimate->adjustment, $estimate->symbol) . '</td>
</tr>';
}
$tbltotal .= '
<tr>
    <td width="70%"></td>
    <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('estimate_total') . '</strong></td>
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
	$pdf->SetFont($font_name, 'B', $font_size + 2);
	$pdf->Cell(0, 0, _l('estimate_note'), 0, 1, 'L', 0, '', 0);
	$pdf->SetFont($font_name, '', $font_size + 2);
	$pdf->Ln(2);
	$pdf->writeHTMLCell('', '', '', '', $estimate->clientnote, 0, 1, false, true, 'L', true);
}

if (!empty($estimate->terms)) {
	$pdf->Ln(4);
	$pdf->SetFont($font_name, 'B', $font_size + 2);
	$pdf->Cell(0, 0, _l('terms_and_conditions'), 0, 1, 'L', 0, '', 0);
	$pdf->SetFont($font_name, '', $font_size + 2);
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
$footer_thank_msg .= '<tr>
<td style="vertical-align: middle; text-align:center; font-size: ' . ($font_size + 4) . 'px; border-top:2px solid #dedede;"><b>' . strtoupper("Thank You for Giving Us the Chance to Serve You!
") . '</b></td>
</tr>
</thead>
</table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $footer_thank_msg, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);