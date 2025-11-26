<?php
$dimensions = $pdf->getPageDimensions();



$font_size = get_option('pdf_font_size');
if ($font_size == '') {
	$font_size = 10;
}

$pdf->SetMargins(PDF_MARGIN_LEFT, 50, PDF_MARGIN_RIGHT);

$pdf->ln(40);

$heading = '<span style="font-weight:bold;font-size:27px; text-align:center;"><u>' . _l('inventory_checklist_pdf_heading') . '</u></span>';
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $heading, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(10);

//logo area
$info_right_column = '';
$logo_area = '';

//Billing & Shipping Section
$info_bill_shipping = '
<table border="0" cellpadding="5">
<thead>
<tr bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold;">
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"><b>' . strtoupper("Company Info") . '</b></td>';
if ($invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1) {
	$info_bill_shipping .= '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"><b>' . strtoupper(_l('ship_to')) . '</b></td>';
}
$info_bill_shipping .= '</tr>
</thead>
<tbody>
<tr>
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">';
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

// ship to to
if ($invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1) {
	$shipping_details = '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">';

	$shipping_details .= '<div style="color:#424242;">';
	if ($invoice->client->show_primary_contact == 1) {
		$pc_id = get_primary_contact_user_id($invoice->clientid);
		if ($pc_id) {
			$shipping_details .= get_contact_full_name($pc_id) . '<br />';
		}
	}

	$shipping_details .= $invoice->client->company . '<br />';
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
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"><b>' . strtoupper(_l('sale_agent_string')) . '</b></td>
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"><b>' . strtoupper(_l('invoice')) . '</b></td>
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"><b>' . strtoupper(_l('invoice_data_date')) . '</b></td>
</tr>
</thead>
<tbody>
<tr style="color:#424242;">
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">';
if ($invoice->sale_agent != 0) {
	if (get_option('show_sale_agent_on_invoices') == 1) {
		$info_sales_person .= get_staff_full_name($invoice->sale_agent);
	}
}
$info_sales_person .= '</td>';
$info_sales_person .= '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"># ' . $invoice_number . '</td><td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">' . _d($invoice->date) . '</td>';
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
$item_width = 60;
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
        <th width="5%;" align="center" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">#</th>
        <th width="' . $item_width . '%" align="left" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">' . strtoupper(_l('invoice_table_item_heading')) . '</th>
        <th width="19%" align="right" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">VERIFY(Tick)</th>
</tr>';
// Items
$tblhtml .= '<tbody>';

$items_data = get_table_products_bulk($invoice->id);
$tblhtml .= $items_data['html'];
$tblhtml .= '</tbody>';
$tblhtml .= '</table>';
$pdf->writeHTML($tblhtml, true, false, false, false, '');

$pdf->Ln(10);
$validate = '
<table border="0" cellpadding="5">
<thead>
<tr>
<td style="font-size: ' . ($font_size + 4) . 'px; font-weight:bold;">Checked & Verified By Sign...............................................................</td>
</tr>
</thead>
</table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $validate, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);

$pdf->Ln(10);
$footer_thank_msg = '
<table border="0" cellpadding="5">
<thead>
<tr>
<td style="vertical-align: middle; text-align:center; font-size: ' . ($font_size + 4) . 'px; border-top:2px solid #dedede;"><b>' . strtoupper("Thank You For Your Business") . '</b></td>
</tr>
</thead>
</table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $footer_thank_msg, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);