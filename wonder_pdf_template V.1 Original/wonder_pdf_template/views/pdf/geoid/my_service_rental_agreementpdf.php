<?php
$dimensions = $pdf->getPageDimensions();



$font_size = get_option('pdf_font_size');
if ($font_size == '') {
	$font_size = 10;
}

$pdf->SetMargins(PDF_MARGIN_LEFT, 50, PDF_MARGIN_RIGHT);

$pdf->ln(40);

$heading = '<span style="font-weight:bold;font-size:27px; text-align:center;"><u>RENTAL AGREEMENT</u></span>';
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $heading, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(10);

//Billing & Shipping Section
$info_bill_shipping = '
<table border="0" cellpadding="5">
<thead>
<tr bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold;">
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"><b>' . strtoupper(_l('invoice_bill_to')) . '</b></td>';

$info_bill_shipping .= '
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"></td></tr>
</thead>
<tbody>
<tr>';

$info_bill_shipping .= '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">';
// Bill to
// Bill to
$client_details = '<div style="color:#424242;">';
if ($service_rental_agreement_client->show_primary_contact == 1) {
	$pc_id = get_primary_contact_user_id($service_rental_agreement_client->userid);
	if ($pc_id) {
		$client_details .= get_contact_full_name($pc_id) . '<br />';
	}
}

$client_details .= $service_rental_agreement_client->company . '<br />';
$client_details .= $service_rental_agreement_client->billing_street . '<br />';
if (!empty($service_rental_agreement_client->billing_city)) {
	$client_details .= $service_rental_agreement_client->billing_city;
}
if (!empty($service_rental_agreement_client->billing_state)) {
	$client_details .= ', ' . $service_rental_agreement_client->billing_state;
}
$billing_country = get_country_short_name($service_rental_agreement_client->billing_country);
if (!empty($billing_country)) {
	$client_details .= '<br />' . $billing_country;
}
if (!empty($service_rental_agreement_client->billing_zip)) {
	$client_details .= ', ' . $service_rental_agreement_client->billing_zip;
}
if (!empty($service_rental_agreement_client->vat)) {
	$client_details .= '<br />' . _l('invoice_vat') . ': ' . $service_rental_agreement_client->vat;
}
// check for invoice custom fields which is checked show on pdf
$pdf_custom_fields = get_custom_fields('customers', array(
	'show_on_pdf' => 1,
));
if (count($pdf_custom_fields) > 0) {
	$client_details .= '<br />';
	foreach ($pdf_custom_fields as $field) {
		$value = get_custom_field_value($service_rental_agreement_client->userid, $field['id'], 'customers');
		if ($value == '') {
			continue;
		}
		$client_details .= $field['name'] . ': ' . $value . '<br />';
	}
}
$client_details .= '</div>';
$info_bill_shipping .= $client_details . '</td>';

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
  </tbody>
  </table>';
}

$info_right_column .= '<div style="text-align: right"><b style="color:#4e4e4e;"># ' . $rental_agreement_number . '</b>';
//dates
$info_right_column .= '<br>
<b style="color:#4e4e4e; font_size:12px;">Start Date: ' . _d($service_rental_agreement->start_date) . '
<br>End Date: ' . _d($service_rental_agreement->end_date) . '
<br>Received By: ';
if ($service_rental_agreement->received_by != 0) {
	if (get_option('show_sale_agent_on_invoices') == 1) {
		$info_right_column .= get_staff_full_name($service_rental_agreement->received_by);
	}
}

if ($service_rental_agreement->site_name != null) {
	$info_right_column .= '<br>' . _l('site_name') . ': ' . $service_rental_agreement->site_name;
}
if ($service_rental_agreement->field_operator != null) {
	$info_right_column .= '<br>' . _l('field_operator') . ': ' . get_staff_full_name($service_rental_agreement->field_operator);
}

$info_right_column .= '</b>';
$info_bill_shipping .= $info_right_column;

$info_bill_shipping .= '</div></td></tr>
</tbody>
</table>';

$pdf->writeHTML($info_bill_shipping, true, false, false, false, '');
$pdf->ln(6);

//Rental Info
$info_rental = '
    <table border="0" cellpadding="5">
      <thead>
        <tr bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold;">
          <td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"><b>RENTAL DATE</b></td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"><b>RETURN DATE</b></td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"><b>AGREEMENT CODE</b></td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"><b>RECEIVED BY</b></td>
        </tr>
      </thead>
      <tbody>
        <tr style="color:#424242;">
          <td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">' . _d($service_rental_agreement->start_date) . '</td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">' . _d($service_rental_agreement->end_date) . '</td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">' . get_option('service_rental_agreement_prefix') . $service_rental_agreement->service_rental_agreement_code . '</td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;">' . get_staff_full_name($service_rental_agreement->received_by) . '</td>
        </tr>
      </tbody>
    </table>';

$pdf->writeHTML($info_rental, true, false, false, false, '');
$pdf->ln(6);

$info_service_details = '
    <table  border="0" cellpadding="5">
      <thead>
        <tr bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold;">
          <td width="10%;" align="center" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"><b>#</b></td>
          <td width="45%"  align="left;" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"><b>SERIAL No.</b></td>
          <td width="45%" align="left;" style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; border:1px solid #ccc;"><b>EQUIPMENT</b></td>
        </tr>
      </thead>
      <tbody>';

$i = 1;
$total = 0;
foreach ($service_rental_agreement_details as $key => $detail_info) {
	$tr_attrs = '';

	if (class_exists('pdf')) {
		$font_size = get_option('pdf_font_size');
		if ($font_size == '') {
			$font_size = 10;
		}

		$tr_attrs .= ' style="font-size:' . ($font_size + 4) . 'px;"';
	}
	$bg = '';
	if ($i % 2 == 0) {$bg = ' bgcolor = "#f4f4f4"';} else { $bg = ' bgcolor = "#ffffff"';}

	$info_service_details .= '
        <tr' . $tr_attrs . ' ' . $bg . ' >
          <td width="10%;" align="center" style="border:1px solid #ccc;">' . $i . '</td>
          <td width="45%" class="description" align="left;" style="border:1px solid #ccc;"><span style="font-size:' . (isset($font_size) ? $font_size + 4 : '') . 'px;">' . $detail_info->rental_serial . '</span></td>
          <td width="45%" class="description" align="left;" style="border:1px solid #ccc;"><span style="font-size:' . (isset($font_size) ? $font_size + 4 : '') . 'px;">' . $detail_info->name . '<br>';
	if (!empty(unserialize($detail_info->rental_accessories_booked))) {
		foreach (unserialize($detail_info->rental_accessories_booked) as $key => $value) {
			$info_service_details .= '<span style="color:green; font-size:16px; font-weight: bold;">+</span> ' . $value . '<br>';
		}
	}
	$info_service_details .= '</span></td>
        </tr>';
	$total = $total + $detail_info->price;
	$i++;
}
$info_service_details .= '</tbody>
      </table>';

$pdf->writeHTML($info_service_details, true, false, false, false, '');
$pdf->ln(6);

$remarks = '
    <b style="font-size: ' . ($font_size + 4) . 'px; font-weight:bold;">REMARKS:</b><br>
    <table border="0" cellpadding="5">
      <tbody>
        <tr>
          <td style="font-size: ' . ($font_size + 4) . 'px;">' . $service_rental_agreement->rental_agreement_note . '</td>
        </tr>
      </tbody>
    </table>';
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $remarks, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
$pdf->Ln(10);

//PAGE BREAK
$pdf->AddPage();
$pdf->Ln(10);
$terms_and_conditions = '
    <b style="font-size: ' . ($font_size + 4) . 'px; font-weight:bold; text-align:center;">EQUIPMENT RENTAL TERMS AND CONDITIONS</b><br>
    <table border="0" cellpadding="5">
      <tbody>
        <tr>
          <td style="font-size: ' . ($font_size + 4) . 'px;">
           ' . get_option('rental_terms_n_conditions') . '
          </td>
        </tr>
      </tbody>
    </table>';
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $terms_and_conditions, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
$pdf->Ln(10);

$validate = '<table border="0" cellpadding="5">
      <tbody>
        <tr>
         <td style="font-size: ' . ($font_size + 4) . 'px;"><b>DATE:</b></td>
         <td>' . _d(date('Y-m-d')) . '</td>
         <td style="font-size: ' . ($font_size + 4) . 'px;"><b>AUTHORISED SIGNATURE:</b></td>
         <td><b>.................................</b></td>
         <td style="font-size: ' . ($font_size + 4) . 'px;"><b>NAME OF CLIENT:</b></td>
         <td>' . $service_rental_agreement_client->company . '</td>
        </tr>
        <tr>
         <td style="font-size: ' . ($font_size + 4) . 'px;"><b>DATE:</b></td>
         <td>' . _d(date('Y-m-d')) . '</td>
         <td style="font-size: ' . ($font_size + 4) . 'px;"><b>AUTHORISED SIGNATURE:</b></td>
         <td><b>.................................</b></td>
         <td style="font-size: ' . ($font_size + 4) . 'px;"><b>PROCESSED BY:</b></td>
         <td>' . get_staff_full_name($service_rental_agreement->received_by) . '</td>
        </tr>
      </tbody>
    </table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $validate, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
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