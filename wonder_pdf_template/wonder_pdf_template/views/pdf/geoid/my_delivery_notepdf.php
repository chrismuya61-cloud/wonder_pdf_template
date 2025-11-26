<?php
$dimensions = $pdf->getPageDimensions();



$font_size = get_option('pdf_font_size');
if ($font_size == '') {
	$font_size = 10;
}

$pdf->SetMargins(PDF_MARGIN_LEFT, 50, PDF_MARGIN_RIGHT);

$pdf->ln(40);

$heading = '<span style="font-weight:bold;font-size:27px; text-align:center;"><u>' . _l('delivery_note_pdf_heading') . '</u></span>';
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $heading, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(10);

//Billing & Shipping Section
$info_bill_shipping = '
<table border="0" cellpadding="5">
<thead>
<tr bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold;">';
if (1 == 1 or ($invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1)) {
	$info_bill_shipping .= '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"><b>' . strtoupper(_l('ship_to')) . '</b></td>';
}
$info_bill_shipping .= '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"></td></tr>
</thead>
<tbody>
<tr>';

// ship to to
if (1 == 1 or ($invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1)) {
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

$info_bill_shipping .= '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">';

$info_right_column = '';

if (get_option('show_status_on_pdf_ei') == 1) {
	$status_name = format_invoice_status($status, '', false);
	if ($status == 1) {
		$bg_status = '252, 45, 66';
	} elseif ($status == 2) {
		$bg_status = '0, 191, 54';
	} elseif ($status == 3) {
		$bg_status = '255, 111, 0';
	} elseif ($status == 4) {
		$bg_status = '255, 111, 0';
	} elseif ($status == 5 || $status == 6) {
		$bg_status = '114, 123, 144';
	}

	$info_right_column .= '
  <table style="text-align:center;border-spacing:3px 3px;padding:3px 4px 3px 4px;">
  <tbody>
  <tr>
  <td></td>
  <td></td>
  <td style="background-color:rgb(' . $bg_status . ');color:#fff;">' . mb_strtoupper($status_name, 'UTF-8') . '</td>
  </tr>
  <tr style="color:#424242;">';
	$info_right_column .= '<td colspan="3"  style="text-align:right; vertical-align: middle; font-size: ' . ($font_size + 4) . 'px;">Invoice No: # ' . $invoice_number . '</td></tr>
  <tr style="color:#424242;"><td colspan="3"  style="text-align:right; vertical-align: middle; font-size: ' . ($font_size + 4) . 'px;">Invoice Date: ' . _d($invoice->date) . '</td>';
	$info_right_column .= '</tr>
  <tr style="color:#424242;">
<td colspan="3" style="text-align:right; vertical-align: middle; font-size: ' . ($font_size + 4) . 'px;">';
	if ($invoice->sale_agent != 0) {
		if (get_option('show_sale_agent_on_invoices') == 1) {
			$info_right_column .= 'Sales Agent: ' . get_staff_full_name($invoice->sale_agent);
		}
	}
	$info_right_column .= '</td></tr>
  </tbody>
  </table>';
}

$info_bill_shipping .= $info_right_column . '</td>';

$info_bill_shipping .= '</tr>
</tbody>
</table>';
$pdf->writeHTML($info_bill_shipping, true, false, false, false, '');
//$pdf->ln(6);

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
$item_width = 54;
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
<table width="100%" border="0" bgcolor="#fff"  cellpadding="5">
    <tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold;">
        <th width="10%;" align="center" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">#</th>
        <th width="' . $item_width . '%" align="left" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">' . strtoupper(_l('invoice_table_item_heading')) . '</th>
        <th width="20%" align="right" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">' . strtoupper($qty_heading) . '</th>
</tr>';
// Items
$tblhtml .= '<tbody>';

$items_data = pdf_get_table_items_and_taxes($invoice->items, 'delivery_note');
$tblhtml .= $items_data['html'];
$taxes = $items_data['taxes'];

$tblhtml .= '</tbody>';
$tblhtml .= '</table>';
$pdf->writeHTML($tblhtml, true, false, false, false, '');

$pdf->Ln(10);
$validate = '
<table border="0" cellpadding="5">
<thead>
<tr>
<td style="font-size: ' . ($font_size + 4) . 'px; font-weight:bold;">Received By <br><br><br>Sign...............................................................</td>
<td style="font-size: ' . ($font_size + 4) . 'px; font-weight:bold;">Delivered By <br><br><br>Sign...............................................................</td>
</tr>
</thead>
</table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $validate, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);

$pdf->Ln(10);
$footer_thank_msg = '
<table border="0" cellpadding="5">
<thead>';
if (get_option('e_sign_delivery_note')) {
	$footer_thank_msg .= '<tr>
  <td>' . pdf_signatures() . '</td>
  </tr>';
}
$footer_thank_msg .= '<tr>
<td style="vertical-align: middle; text-align:center; font-size: ' . ($font_size + 4) . 'px; border-top:2px solid #dedede;"><b>' . strtoupper("Thank You For Your Business") . '</b></td>
</tr>
</thead>
</table>';

//$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $footer_thank_msg, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
