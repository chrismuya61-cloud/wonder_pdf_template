<?php

defined('BASEPATH') or exit('No direct script access allowed');
$doc_title = ucwords(strtolower(_l('Delivery Note')));

include_once 'partials/header.php';

if (get_option('show_status_on_pdf_ei') == 1) {
	$status_name = format_invoice_status($status, '', false);
	if ($status == 1) {
		$bg_status = '252, 45, 66';
	} elseif ($status == 2) {
		$bg_status = '0, 191, 54';
	} elseif ($status == 3 || $status == 4) {
		$bg_status = '255, 111, 0';
	} elseif ($status == 5 || $status == 6) {
		$bg_status = '114, 123, 144';
	}

	$invoice_info = '<div style="font-size:' . ($font_size + 4) . 'px;"><b style="color:#4e4e4e; "># ' . $invoice_number . '</b>';
	$invoice_info .= '<br /><span style="color:rgb(' . $bg_status . '); font-size:' . ($font_size + 6) . 'px;">' . mb_strtoupper($status_name, 'UTF-8') . '</span>';
}

$invoice_info .= '<br /><span style="font-weight:bold; ">' . _l('invoice_data_date') . '</span> ' . _d($invoice->date) . '<br />';

if ($invoice->sale_agent != 0 && get_option('show_sale_agent_on_invoices') == 1) {
	$invoice_info .= '<span style="font-weight:bold; ">' . _l('sale_agent_string') . ':</span> ' . get_staff_full_name($invoice->sale_agent) . '<br />';
}

$invoice_info .= '</div>';

//Billing & Shipping Section
$info_bill_shipping = '
<table border="0" cellpadding="2">
<thead>
<tr>
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; "><b>' . strtoupper(_l('invoice_bill_to')) . '</b></td>';
if ($invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1) {
	$info_bill_shipping .= '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px;"><b>' . strtoupper(_l('ship_to')) . '</b></td>';
}
$info_bill_shipping .= '</tr>
</thead>
<tbody>
<tr>
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px;">';
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
	$shipping_details = '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px;">';
	$shipping_details .= '<div style="color:#424242;">';
	$shipping_details .= $invoice->shipping_street . '<br />' . $invoice->shipping_city . ', ' . $invoice->shipping_state . '<br />' . get_country_short_name($invoice->shipping_country) . ', ' . $invoice->shipping_zip;
	$shipping_details .= '</div></td>';
	$info_bill_shipping .= $shipping_details;
}

$info_bill_shipping .= '</tr>
</tbody>
</table>';

$info_right_column = $invoice_info;

$info_left_column = $info_bill_shipping;

pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 1.5) - $dimensions['lm']);

$pdf->ln(5);

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
$border = 'border-bottom-color:#000000;border-bottom-width:2px; border-bottom-style:solid; border-top: none;';
$tblhtml = '
<table width="110%" border="0" bgcolor="#fff"  cellpadding="5">
    <tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . (get_option('pdf_font_size') + 4) . 'px;">
        <th width="10%;" align="center"  style="' . $border . '">#</th>
        <th width="' . $item_width . '%" align="left"  style="' . $border . '">' . strtoupper(_l('invoice_table_item_heading')) . '</th>
        <th width="20%" align="right"  style="' . $border . '">' . strtoupper($qty_heading) . '</th>
</tr>';
// Items
$tblhtml .= '<tbody>';

$items_data = get_table_gamma_items_and_taxes($invoice->items, 'delivery_note');
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
<td style="font-size: ' . ($font_size + 4) . 'px; font-weight:bold;">Received By:</td>
<td style="font-size: ' . ($font_size + 4) . 'px; font-weight:bold;">Delivered By:</td>
</tr>
<tr>
<td style="font-size: ' . ($font_size + 4) . 'px; font-weight:bold;">Name...............................................................</td>
<td style="font-size: ' . ($font_size + 4) . 'px; font-weight:bold;">Name...............................................................</td>
</tr>
<tr>
<td></td>
<td></td>
</tr>
<tr>
<td style="font-size: ' . ($font_size + 4) . 'px; font-weight:bold;">Sign...............................................................</td>
<td style="font-size: ' . ($font_size + 4) . 'px; font-weight:bold;">Sign...............................................................</td>
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
<td style="vertical-align: middle; text-align:center; font-size: ' . ($font_size + 4) . 'px; border-top:1px solid ' . get_option('pdf_table_border_color') . ';"><b>' . strtoupper("Thank You For Your Business") . '</b></td>
</tr>
</thead>
</table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $footer_thank_msg, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);