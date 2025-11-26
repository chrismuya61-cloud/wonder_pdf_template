<?php

defined('BASEPATH') or exit('No direct script access allowed');

$dimensions = $pdf->getPageDimensions();

$font_size = get_option('pdf_font_size');
if ($font_size == '') {
	$font_size = 10;
}

$pdf->SetMargins(PDF_MARGIN_LEFT, 20, PDF_MARGIN_RIGHT);

$pdf->ln(40);

$heading = '<span style="font-weight:bold;font-size:27px; text-align:center;"><u>' . _l('credit_note_pdf_heading') . '</u></span>';
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $heading, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(10);


$borderBottom = 'border-bottom-color:#000000;border-bottom-width:1px; border-bottom-style:solid;';
$borderTop = 'border-top-color:#000000;border-top-width:1px; border-top-style:solid;';
$table_border = "border:1px solid #ccc;";


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
    <td style="background-color:rgb(' . credit_note_status_color_pdf($credit_note->status) . '); color:#fff; font-size: ' . ($font_size + 4) . 'px; ">'. strtoupper(pdf_format_credit_note_status($credit_note->status, '', false)) . '</td>
    </tr>
    </tbody>
    </table>';
}


$info_right_column .= '<span style="font-weight:bold; color:#4e4e4e; font-size: ' . ($font_size + 4) . 'px;"># ' . $credit_note_number . '</span>';

//dates
$info_right_column .= '<br><span style="font-weight:bold; color:#4e4e4e; font-size: ' . ($font_size + 4) . 'px;">' . _l('credit_note_date') . '</span> ' . _d($credit_note->date);
$info_right_column = hooks()->apply_filters('credit_note_pdf_header_after_date', $info_right_column, $credit_note);

if (!empty($credit_note->reference_no)) {
  $info_right_column .= '<br><span style="font-weight:bold; color:#4e4e4e; font-size: ' . ($font_size + 4) . 'px;">' . _l('reference_no') . '</span> ' .$credit_note->reference_no;
  $info_right_column = hooks()->apply_filters('credit_note_pdf_header_after_reference_no', $info_right_column, $credit_note);
}
if ($credit_note->project_id != 0 && get_option('show_project_on_credit_note') == 1) {
  $info_right_column .= '<br><span style="font-weight:bold; color:#4e4e4e; font-size: ' . ($font_size + 4) . 'px;">' ._l('project') . '</span> ' . get_project_name_by_id($credit_note->project_id);
  $info_right_column = hooks()->apply_filters('credit_note_pdf_header_after_project', $info_right_column, $credit_note);
}

$info_right_column = hooks()->apply_filters('credit_note_pdf_header_before_custom_fields', $info_right_column, $credit_note);

foreach ($pdf_custom_fields as $field) {
  $value = get_custom_field_value($credit_note->id, $field['id'], 'credit_note');
  if ($value == '') {
      continue;
  }
  $info_right_column .= '<br><span style="font-weight:bold; color:#4e4e4e; font-size: ' . ($font_size + 4) . 'px;">' .$field['name'] . '</span> ' . $value;
}

$info_right_column = hooks()->apply_filters('credit_note_pdf_header_after_custom_fields', $info_right_column, $credit_note);



//Billing & Shipping Section
$info_bill_shipping = '
<table border="0" cellpadding="5">
<thead>
<tr bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold;">
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';"><b>' . strtoupper(_l('credit_note_bill_to')) . '</b></td>
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';"><b>' . '' . '</b></td>';
if ($credit_note->include_shipping == 1 && $credit_note->show_shipping_on_credit_note == 1) {
  $info_bill_shipping .= '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';"><b>' . strtoupper(_l('ship_to')) . '</b></td>';
}
$info_bill_shipping .= '</tr>
</thead>
<tbody>
<tr>';
$info_bill_shipping .= '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';">';
// Bill to
$client_details = '<div style="color:#424242;">';
if ($credit_note->client->show_primary_contact == 1) {
  $pc_id = get_primary_contact_user_id($credit_note->clientid);
  if ($pc_id) {
    $client_details .= get_contact_full_name($pc_id) . '<br />';
  }
}

$client_details .= $credit_note->client->company . '<br />';
$client_details .= $credit_note->billing_street . '<br />';
if (!empty($credit_note->billing_city)) {
  $client_details .= $credit_note->billing_city;
}
if (!empty($credit_note->billing_state)) {
  $client_details .= ', ' . $credit_note->billing_state;
}
$billing_country = get_country_short_name($credit_note->billing_country);
if (!empty($billing_country)) {
  $client_details .= '<br />' . $billing_country;
}
if (!empty($credit_note->billing_zip)) {
  $client_details .= ', ' . $credit_note->billing_zip;
}
if (!empty($credit_note->client->vat)) {
  $client_details .= '<br />' . _l('credit_note_vat') . ': ' . $credit_note->client->vat;
}
// check for credit_note custom fields which is checked show on pdf
$pdf_custom_fields = get_custom_fields('customers', array(
  'show_on_pdf' => 1
));
if (count($pdf_custom_fields) > 0) {
  $client_details .= '<br />';
  foreach ($pdf_custom_fields as $field) {
    $value = get_custom_field_value($credit_note->clientid, $field['id'], 'customers');
    if ($value == '') {
      continue;
    }
    $client_details .= $field['name'] . ': ' . $value . '<br />';
  }
}
$client_details .= '</div>';
$info_bill_shipping .= $client_details . '</td>';
$info_bill_shipping .= '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';"><div style="text-align: right">'.$info_right_column.'</div></td>';
// ship to to
if ($credit_note->include_shipping == 1 && $credit_note->show_shipping_on_credit_note == 1) {
  $shipping_details = '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';">';
  $shipping_details .= '<div style="color:#424242;">';
  $shipping_details .= $credit_note->shipping_street . '<br />' . $credit_note->shipping_city . ', ' . $credit_note->shipping_state . '<br />' . get_country_short_name($credit_note->shipping_country) . ', ' . $credit_note->shipping_zip;
  $shipping_details .= '</div></td>';
  $info_bill_shipping .= $shipping_details;
}

$info_bill_shipping .= '</tr>
</tbody>
</table>';

$pdf->writeHTML($info_bill_shipping, true, false, false, false, '');
$pdf->ln(5);



// check for credit_note custom fields which is checked show on pdf
$pdf_custom_fields = get_custom_fields('credit_note', array(
  'show_on_pdf' => 1
));
foreach ($pdf_custom_fields as $field) {
  $value = get_custom_field_value($credit_note->id, $field['id'], 'credit_note');
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
$qty_heading = _l('credit_note_table_quantity_heading');
if ($credit_note->show_quantity_as == 2) {
  $qty_heading = _l('credit_note_table_hours_heading');
} elseif ($credit_note->show_quantity_as == 3) {
  $qty_heading = _l('credit_note_table_quantity_heading') . '/' . _l('credit_note_table_hours_heading');
}
$tblhtml = '
<table width="100%" border="0" bgcolor="#fff" cellspacing="0" cellpadding="5">
    <tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold;">
        <th width="5%;" align="center" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';">#</th>
        <th width="' . $item_width . '%" align="left" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';">' . strtoupper(_l('credit_note_table_item_heading')) . '</th>
        <th width="12%" align="right" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';">' . strtoupper($qty_heading) . '</th>
        <th width="15%" align="right" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';">' . strtoupper(_l('credit_note_table_rate_heading')) . '</th>';
if (get_option('show_tax_per_item') == 1) {
  $tblhtml .= '<th width="15%" align="right" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';">' . strtoupper(_l('credit_note_table_tax_heading')) . '</th>';
}
$tblhtml .= '<th width="15%" align="right" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; '.$table_border.';">' . strtoupper(_l('credit_note_table_amount_heading')) . '</th>
</tr>';
// Items
$tblhtml .= '<tbody>';

$items_data = pdf_get_table_items_and_taxes($credit_note->items, 'credit_note', false, $table_border);
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
    <td width="'.(get_option('show_tax_per_item') == 1 ? '55%' : '58%').'"></td>
    <td align="right" width="'.(get_option('show_tax_per_item') == 1 ? '30%' : '27%') .'" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('credit_note_subtotal') . '</strong></td>
    <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . app_format_money($credit_note->subtotal, $credit_note->symbol) . '</td>
</tr>';
if ($credit_note->discount_percent != 0) {
  $tbltotal .= '
    <tr>
    <td width="'.(get_option('show_tax_per_item') == 1 ? '55%' : '58%').'"></td>
        <td align="right" width="'.(get_option('show_tax_per_item') == 1 ? '30%' : '27%') .'" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('credit_note_discount') . '(' . _format_number($credit_note->discount_percent, true) . '%)' . '</strong></td>
        <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">-' . app_format_money($credit_note->discount_total, $credit_note->symbol) . '</td>
    </tr>';
}
foreach ($taxes as $tax) {
  $total = array_sum($tax['total']);
  if ($credit_note->discount_percent != 0 && $credit_note->discount_type == 'before_tax') {
    $total_tax_calculated = ($total * $credit_note->discount_percent) / 100;
    $total                = ($total - $total_tax_calculated);
  }
  // The tax is in format TAXNAME|20
  $_tax_name = explode('|', $tax['tax_name']);
  $tbltotal .= '<tr>
    <td width="'.(get_option('show_tax_per_item') == 1 ? '55%' : '58%').'"></td>
    <td align="right" width="'.(get_option('show_tax_per_item') == 1 ? '30%' : '27%') .'" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . $_tax_name[0] . '(' . _format_number($tax['taxrate']) . '%)' . '</strong></td>
    <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . app_format_money($total, $credit_note->symbol) . '</td>
</tr>';
}
if ((int)$credit_note->adjustment != 0) {
  $tbltotal .= '<tr>
    <td width="'.(get_option('show_tax_per_item') == 1 ? '55%' : '58%').'"></td>
    <td align="right" width="'.(get_option('show_tax_per_item') == 1 ? '30%' : '27%') .'" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('credit_note_adjustment') . '</strong></td>
    <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . app_format_money($credit_note->adjustment, $credit_note->symbol) . '</td>
</tr>';
}
$tbltotal .= '
<tr>
    <td width="'.(get_option('show_tax_per_item') == 1 ? '55%' : '58%').'"></td>
    <td align="right" width="'.(get_option('show_tax_per_item') == 1 ? '30%' : '27%') .'" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('credit_note_total') . '</strong></td>
    <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . app_format_money($credit_note->total, $credit_note->symbol) . '</td>
</tr>';

if ($credit_note->credits_used) {
  $tbltotal .= '
  <tr>
      <td width="'.(get_option('show_tax_per_item') == 1 ? '55%' : '58%').'"></td>
      <td align="right" width="'.(get_option('show_tax_per_item') == 1 ? '30%' : '27%') .'" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('credits_used') . '</strong></td>
      <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . '-' . app_format_money($credit_note->credits_used, $credit_note->currency_name) . '</td>
  </tr>';
}

if ($credit_note->total_refunds) {
  $tbltotal .= '
  <tr>
      <td width="'.(get_option('show_tax_per_item') == 1 ? '55%' : '58%').'"></td>
      <td align="right" width="'.(get_option('show_tax_per_item') == 1 ? '30%' : '27%') .'" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('refund') . '</strong></td>
      <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . '-' . app_format_money($credit_note->total_refunds, $credit_note->currency_name) . '</td>
  </tr>';
}

$tbltotal .= '
  <tr>
      <td width="'.(get_option('show_tax_per_item') == 1 ? '55%' : '58%').'"></td>
      <td align="right" width="'.(get_option('show_tax_per_item') == 1 ? '30%' : '27%') .'" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('credits_remaining') . '</strong></td>
      <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . app_format_money($credit_note->remaining_credits, $credit_note->currency_name) . '</td>
 </tr>';

$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');

if (get_option('total_to_words_enabled') == 1) {
  // Set the font bold
  $pdf->SetFont($font_name, 'B', $font_size);
  $pdf->Cell(0, 0, _l('num_word') . ': ' . $CI->numberword->convert($credit_note->total, $credit_note->currency_name), 0, 1, 'C', 0, '', 0);
  // Set the font again to normal like the rest of the pdf
  $pdf->SetFont($font_name, '', $font_size);
  $pdf->Ln(4);
}


if (!empty($credit_note->clientnote)) {
  $pdf->Ln(4);
  $pdf->SetFont($font_name, 'B', $font_size);
  $pdf->Cell(0, 0, _l('credit_note_note'), 0, 1, 'L', 0, '', 0);
  $pdf->SetFont($font_name, '', $font_size);
  $pdf->Ln(2);
  $pdf->writeHTMLCell('', '', '', '', $credit_note->clientnote, 0, 1, false, true, 'L', true);
}

if (!empty($credit_note->terms)) {
  $pdf->Ln(4);
  $pdf->SetFont($font_name, 'B', $font_size);
  $pdf->Cell(0, 0, _l('terms_and_conditions'), 0, 1, 'L', 0, '', 0);
  $pdf->SetFont($font_name, '', $font_size);
  $pdf->Ln(2);
  $pdf->writeHTMLCell('', '', '', '', $credit_note->terms, 0, 1, false, true, 'L', true);
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