<?php

defined('BASEPATH') or exit('No direct script access allowed');
$doc_title = 'Rental Agreement';

include_once 'partials/header.php';

$pdf->Ln(4);
//logo area
$info_right_column = '';

if (get_option('show_status_on_pdf_ei') == 1) {
	$status_name = format_invoice_status($status, '', false);
	if ($status == 0) {
		$bg_status = '255, 111, 0';
		$status_name = 'PENDING';
	} elseif ($status == 1) {
		$bg_status = '252, 45, 66';
		$status_name = 'CANCELED';
	} elseif ($status == 2) {
		$bg_status = '0, 191, 54';
		$status_name = 'PAID';
	} elseif ($status == 3) {
		$bg_status = '0, 191, 54';
		$status_name = 'PENDING PARTIALLY PAID';
	}

	$info_right_column = '<div style="font-size:' . ($font_size + 4) . 'px;"><b style="color:#4e4e4e; "># ' . $rental_agreement_number . '</b>';
	$info_right_column .= '<br /><span style="color:rgb(' . $bg_status . '); font-size:' . ($font_size + 6) . 'px;">' . mb_strtoupper($status_name, 'UTF-8') . '</span>';
}

//dates
$info_right_column .= '<br>
<span style="font-size:' . ($font_size + 4) . 'px;"><b>Rental Date:</b> ' . _d($service_rental_agreement->start_date) . '
<br><b>Return Date:</b> ' . _d($service_rental_agreement->end_date) . '
<br><b>Received By:</b> ';
if ($service_rental_agreement->received_by != 0) {
	if (get_option('show_sale_agent_on_invoices') == 1) {
		$info_right_column .= get_staff_full_name($service_rental_agreement->received_by);
	}
}

if ($service_rental_agreement->site_name != null) {
	$info_right_column .= '<br><b>' . _l('site_name') . ':</b> ' . $service_rental_agreement->site_name;
}
if ($service_rental_agreement->field_operator != null) {
	$info_right_column .= '<br><b>' . _l('field_operator') . ':</b> ' . get_staff_full_name($service_rental_agreement->field_operator);
}

$info_right_column .= '</span>';

//Billing & Shipping Section
$info_bill_shipping = '
<table border="0" cellpadding="5">
<thead>
<tr>
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; "><b>' . strtoupper('Client Info') . '</b></td>';

$info_bill_shipping .= '</tr>
</thead>
<tbody>
<tr>';

$info_bill_shipping .= '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px; ">';
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

$info_bill_shipping .= '</tr>
</tbody>
</table>';

$info_left_column = $info_bill_shipping;

pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 1.5) - $dimensions['lm']);

$pdf->ln(6);
$border = 'border-bottom-color:#000000;border-bottom-width:3px; border-bottom-style:solid; border-top: 1px solid black;';

$info_service_details = '
    <table  border="0" cellpadding="5">
      <thead>
        <tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . (get_option('pdf_font_size') + 6) . 'px;">
          <td width="10%;" align="center"  style="' . $border . '"><b>#</b></td>
          <td width="45%"  align="left;"  style="' . $border . '"><b>Serial No.</b></td>
          <td width="45%" align="left;"  style="' . $border . '"><b>Equipment</b></td>
        </tr>
      </thead>
      <tbody>';

$i = 1;
$total = 0;
foreach ($service_rental_agreement_details as $key => $detail_info) {
	$tr_attrs = '';

	if (class_exists('pdf')) {
		$font_size = 10; //get_option('pdf_font_size');
		if ($font_size == '') {
			$font_size = 10;
		}

		$tr_attrs .= ' style="font-size:' . ($font_size + 4) . 'px;"';
	}
	$bg = '';
	if ($i % 2 == 0) {$bg = ' bgcolor = "#f4f4f4"';} else { $bg = ' bgcolor = "#ffffff"';}

	$bg = ' bgcolor = "#ffffff"';

	$border = 'border-bottom-color:#000000;border-bottom-width:1px; border-bottom-style:solid;';

	$info_service_details .= '
        <tr' . $tr_attrs . ' ' . $bg . ' >
          <td width="10%;" align="center"  style="' . $border . '">' . $i . '</td>
          <td width="45%" class="description" align="left;"  style="' . $border . '"><span style="font-size:' . (isset($font_size) ? $font_size + 2 : '') . 'px;">' . $detail_info->rental_serial . '</span></td>
          <td width="45%" class="description" align="left;"  style="' . $border . '"><span style="font-size:' . (isset($font_size) ? $font_size + 2 : '') . 'px;">' . $detail_info->name . '</span></td>
        </tr>';
	$total = $total + $detail_info->price;
	$i++;
}
$info_service_details .= '</tbody>
      </table>';

      

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_service_details, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(6);


$remarks = '
    <b style="font-size: ' . ($font_size + 2) . 'px; font-weight:bold;">REMARKS:</b><br>
    <table border="0" cellpadding="5">
      <tbody>
        <tr>
          <td style="font-size: ' . ($font_size + 2) . 'px;">' . $service_rental_agreement->rental_agreement_note . '</td>
        </tr>
      </tbody>
    </table>';
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $remarks, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
$pdf->Ln(2);

//PAGE BREAK
$pdf->AddPage();
$pdf->Ln(10);
$terms_and_conditions = '
    <b style="font-size: ' . ($font_size + 2) . 'px; font-weight:bold; text-align:center;">EQUIPMENT RENTAL TERMS AND CONDITIONS</b><br>
    <table border="0" cellpadding="5">
      <tbody>
        <tr>
          <td style="font-size: ' . ($font_size + 2) . 'px;">
           <ol>
              <li>These conditions shall apply to all contracts for the hire of equipment owned by Measurement Systems Limited to any company, firm or individual (the Hirer).</li>
              <li>The Hirer shall completely indemnify the Owner in respect of all claims by any person whatsoever for injury to persons and/ or damage to property caused by or in connection with, or arising out of the use of the Equipment, and in respect of all costs and charges in connection therewith, whether arising under common or statute law.
              </li>
              <li>During the continuance of this Agreement the Hirer will keep the said Equipment in good condition and repair and in the case of damage or loss by fire or otherwise will indemnify the Owners from all loss of and damage to the Equipment.
              </li>
              <li>The Equipment is collected by the Hirer from the Owner’s business premises and returned to the same address on the completion of the hire; hire commences at the time of collection and is deemed to continue until the Equipment is received back by the Owner. No allowance will be made for inclement weather or any other reason whatsoever beyond the Owner’s control.
              </li>
              <li>The Hirer shall not misuse the Equipment. Equipment must be returned in the same condition as supplied (except for fair wear and tear), otherwise a charge for cleaning, reconditioning, renewing or replacing will be made as considered necessary by the Owner).
              </li>
              <li>In the event of any Equipment becoming defective, faulty or stolen, the Hirer must immediately communicate with the Owner who will make every reasonable endeavour to rectify the defect or supply replacement Equipment.
              </li>
              <li>All Equipment not returned will be charged at the Manufacturers List Price. Hire charges will not be taken into account in calculating amounts due under this clause.
              </li>
              <li>Although every effort is made to supply all Equipment at the time requested, no liability can be accepted in respect of late or non-delivery, mechanical breakdown, or other circumstances beyond our control.
              </li>
              <li>Although every possible precaution has been take to ensure that the Equipment is in good serviceable condition, no liability whatsoever can be accepted by the Owner for the consequences of any failure or inaccuracies of the Equipment. The Hirer is expected to satisfy himself that the equipment is functional before attempting to use it on site.
              </li>
              <li>The Hirer shall pay Measurement Systems Ltd during the course of the hire the hire term rental at the rate set out overleaf. Rental is payable for the whole of the term, notwithstanding that the equipment may be returned before the term has expired. Punctual payment shall be a condition of the hiring. If at any time the hirer shall be 7 days or more in arrears Measurement Systems Ltd shall be at liberty to forthwith terminate this hiring and recover the possession of the equipment.          </li>
              <li>In accepting delivery of the equipment the hirer thereby agrees that the equipment has been inspected, it is of satisfactory quality, free from defect and is suitable for the purpose of the hirer.
              </li>
              <li>Any literature to the Hirer shall not constitute a representation to the Hirer and exclude from these standard conditions of hire.
              </li>
              <li>The Hirer shall insure the equipment against loss of damage to the full replacement value thereof whilst in the Hirer\'s possession or control.
              </li>
              <li>The Hirer shall keep the equipment in good and serviceable repair and condition, fair wear and tear excepted. Any damage caused to the equipment whilst in the Hirer\'s possession or control shall be deemed the responsibility of the Hirer.       </li>
              <li>If the equipment is not returned within seven says of the completion of the hire term, Measurement Systems Ltd shall be entitled to deem the equipment lost and to purchase the replacement and the cost shall be charged to the Hirer. If the equipment is returned in a condition which, in the opinion of Measurement Systems Ltd, is not good and serviceable, Measurement Systems Ltd may, at its sole discretion repair or replace the equipment and the cost shall be charged to the Hirer at the full list price. </li>
              <li>The hire term shall not be completed until the Hirer shall return the equipment to Measurement Systems Ltd in good and serviceable condition, fair wear and tear excepted and hire charges will continue to accrue
              </li>
              <li>The equipment shall remain personal property and continue in the ownership of Measurement Systems Ltd notwithstanding that it may have been affixed to any land or property. If it so affixed Measurement Systems Ltd shall be entitled to enter the property on which the equipment is located without leave and to sever the equipment therefrom and remove it and Hirer shall be responsible for any damage thereby caused.</li>
              </ol>
          </td>
        </tr>
      </tbody>
    </table>';
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $terms_and_conditions, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
$pdf->Ln(10);

$validate = '<table border="0" cellpadding="5">
      <tbody>
        <tr>
         <td style="font-size: ' . ($font_size + 2) . 'px;"><b>DATE:</b></td>
         <td style="font-size: ' . ($font_size + 4) . 'px;">' . _d(date('Y-m-d')) . '</td>
         <td style="font-size: ' . ($font_size + 2) . 'px;"><b>AUTHORISED SIGNATURE:</b></td>
         <td style="font-size: ' . ($font_size + 2) . 'px;"><b>................................</b></td>
         <td style="font-size: ' . ($font_size + 2) . 'px;"><b>NAME OF CLIENT:</b></td>
         <td style="font-size: ' . ($font_size + 4) . 'px;">' . $service_rental_agreement_client->company . '</td>
        </tr>
        <tr>
         <td style="font-size: ' . ($font_size + 2) . 'px;"><b>DATE:</b></td>
         <td style="font-size: ' . ($font_size + 4) . 'px;">' . _d(date('Y-m-d')) . '</td>
         <td style="font-size: ' . ($font_size + 2) . 'px;"><b>AUTHORISED SIGNATURE:</b></td>
         <td style="font-size: ' . ($font_size + 2) . 'px;"><b>................................</b></td>
         <td style="font-size: ' . ($font_size + 2) . 'px;"><b>PROCESSED BY:</b></td>
         <td style="font-size: ' . ($font_size + 4) . 'px;">' . get_staff_full_name($service_rental_agreement->received_by) . '</td>
        </tr>
      </tbody>
    </table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $validate, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
$pdf->Ln(10);

$footer_thank_msg = '
<table border="0" cellpadding="5">
<thead>
<tr>
<td style="vertical-align: middle; text-align:center; font-size: ' . ($font_size + 2) . 'px; border-top:2px solid #dedede;"><b>' . strtoupper("Thank You For Your Business") . '</b></td>
</tr>
</thead>
</table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $footer_thank_msg, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);