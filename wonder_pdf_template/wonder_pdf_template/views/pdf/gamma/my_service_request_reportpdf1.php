<?php

defined('BASEPATH') or exit('No direct script access allowed');
$doc_title = 'Service Report';

include_once 'partials/header.php';

$pdf->Ln(2);

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
		$status_name = 'COMPLETED';
	}
}

$info_right_column = '<div style="font-size:' . ($font_size + 4) . 'px;"><b style="color:#4e4e4e; "># ' . $request_number . '</b>';
//$info_right_column .= '<br /><span style="color:rgb(' . $bg_status . '); font-size:' . ($font_size + 6) . 'px;">' . mb_strtoupper($status_name, 'UTF-8') . '</span>';

//dates
$info_right_column .= '<br>
<span style="font-size:' . ($font_size + 4) . 'px;"><b>Drop Off Date:</b> ' . _d($service_request->drop_off_date) . '
<br><b>Collection Date:</b> ' . _d($service_request->collection_date) . '
<br><b>Received By:</b> ';
if ($service_request->received_by != 0) {
	if (get_option('show_sale_agent_on_invoices') == 1) {
		$info_right_column .= get_staff_full_name($service_request->received_by);
	}
}
$info_right_column .= '</span>';

//Billing & Shipping Section
$info_bill_shipping = '
<table border="0" cellpadding="5">
<tr>
<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px;"><b>' . strtoupper('Client Info') . '</b></td>';

$info_bill_shipping .= '</tr>
<tbody>
<tr>';

$info_bill_shipping .= '<td style="vertical-align: middle; font-size: ' . ($font_size + 4) . 'px;">';
// Bill to
$client_details = '<div style="color:#424242;">';
if ($service_request_client->show_primary_contact == 1) {
	$pc_id = get_primary_contact_user_id($service_request_client->userid);
	if ($pc_id) {
		$client_details .= get_contact_full_name($pc_id) . '<br />';
	}
}

$client_details .= $service_request_client->company . '<br />';
$client_details .= $service_request_client->billing_street . '<br />';
if (!empty($service_request_client->billing_city)) {
	$client_details .= $service_request_client->billing_city;
}
if (!empty($service_request_client->billing_state)) {
	$client_details .= ', ' . $service_request_client->billing_state;
}
$billing_country = get_country_short_name($service_request_client->billing_country);
if (!empty($billing_country)) {
	$client_details .= '<br />' . $billing_country;
}
if (!empty($service_request_client->billing_zip)) {
	$client_details .= ', ' . $service_request_client->billing_zip;
}
if (!empty($service_request_client->vat)) {
	$client_details .= '<br />' . _l('invoice_vat') . ': ' . $service_request_client->vat;
}
// check for invoice custom fields which is checked show on pdf
$pdf_custom_fields = get_custom_fields('customers', array(
	'show_on_pdf' => 1,
));
if (count($pdf_custom_fields) > 0) {
	$client_details .= '<br />';
	foreach ($pdf_custom_fields as $field) {
		$value = get_custom_field_value($service_request_client->userid, $field['id'], 'customers');
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

$pdf->ln(3);

$border = 'border-bottom-color:#000000;border-bottom-width:2px; border-bottom-style:solid; border-top: none;';
$borderBody = 'border-bottom-color:#000000;border-bottom-width:1px; border-bottom-style:solid;';

//Instrument Info
$info_instrument = '
    <table border="0" cellpadding="5">
      <thead>
        <tr  height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . (get_option('pdf_font_size') + 4) . 'px;">
          <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; ' . $border . '"><b>TYPE</b></td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; ' . $border . '"><b>MAKE</b></td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; ' . $border . '"><b>MODEL</b></td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; ' . $border . '"><b>SERIAL</b></td>
        </tr>
      </thead>
      <tbody>
        <tr style="color:#424242;">
          <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; ' . $borderBody . '">' . $service_request->item_type . '</td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; ' . $borderBody . '">' . $service_request->item_make . '</td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; ' . $borderBody . '">' . $service_request->item_model . '</td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; ' . $borderBody . '">' . $service_request->serial_no . '</td>
        </tr>
      </tbody>
    </table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_instrument, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(6);

//Report content Start

$info_report = '';
if ($service_request->item_type == 'Level') {
	$info_report .= '<b style="font-size:12px; color:maroon;">COLLIMATION ERROR (TWO PEG TEST):</b><br><br>
    <table border="0" cellpadding="5">
      <thead>
        <tr  height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . (get_option('pdf_font_size') + 4) . 'px;">
          <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">SETUP</td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">BACKSIGHT A</td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">FORESIGHT B</td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">DIFF (A-B)</td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">ERROR</td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">CORRECTED<br> FORESIGHT B</td>
        </tr>
      </thead>
      <tbody>';

	$i_backsight_a = $calibration_info->i_backsight_a;
	$i_foresight_b = $calibration_info->i_foresight_b;
	$ii_backsight_a = $calibration_info->ii_backsight_a;
	$ii_foresight_b = $calibration_info->ii_foresight_b;
	$iii_backsight_a = $calibration_info->iii_backsight_a;
	$iii_foresight_b = $calibration_info->iii_foresight_b;
	$iv_backsight_a = $calibration_info->iv_backsight_a;
	$iv_foresight_b = $calibration_info->iv_foresight_b;
	$diff_i = $i_backsight_a - $i_foresight_b;
	$diff_ii = $ii_backsight_a - $ii_foresight_b;
	$diff_iii = $iii_backsight_a - $iii_foresight_b;
	$diff_iv = $iv_backsight_a - $iv_foresight_b;
	$error_ii = round($diff_i - $diff_ii, 3);
	$error_iv = round($diff_iii - $diff_iv, 3);
	$err1 = $ii_foresight_b + ($error_ii - $error_iv);
	$err2 = $iv_foresight_b + ($error_ii - $error_iv);

	$info_report .= '<tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">I</td>
                                <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_backsight_a . '</td>
                                <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_foresight_b . '</td>
                                <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_i . '</td>
                                <td class="desc"></td>
                                <td class="total"></td>
                            </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">II</td>
                                <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_backsight_a . '</td>
                                <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_foresight_b . '</td>
                                <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_ii . '</td>
                                <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $error_ii . '</td>
                                <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $err1 . '</td>
                            </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">III</td>
                                <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iii_backsight_a . '</td>
                                <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iii_foresight_b . '</td>
                                <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_iii . '</td>
                                <td class="desc"></td>
                                <td class="total"></td>
                            </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">IV</td>
                                <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_backsight_a . '</td>
                                <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_foresight_b . '</td>
                                <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_iv . '</td>
                                <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $error_iv . '</td>
                                <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $err2 . '</td>
                            </tr>
      </tbody>
    </table>';
} else if ($service_request->item_type == 'Total Station' or $service_request->item_type == 'Theodolite') {
	$info_report .= '<table border="0" cellpadding="5">
                                          <tr style="color:#424242;">
                                              <td></td>
                                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b>HORIZONTAL CIRCLE INDEX ERROR</b></td>
                                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan="2"><b>VERTICAL CIRCLE INDEX ERROR</b></td>
                                              <td></td>
                                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b>EDM CHECKS</b></td>
                                          </tr>
                              <thead>

                                  <tr  height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . (get_option('pdf_font_size') + 4) . 'px;">
                                      <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">FACE</th>
                                      <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">START A</th>
                                      <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">END B</th>
                                      <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">START A</th>
                                      <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">END B</th>
                                      <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">FACE</th>
                                      <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">START A</th>
                                      <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">END B</th>
                                  </tr>
                              </thead>
                              <tbody>';

	$i_h_a = $calibration_info->i_h_a;
	$ii_h_a = $calibration_info->ii_h_a;
	$sum_h_a = $i_h_a + $ii_h_a;
	$d_error_h_a = (dms2dec(180, 0, 0) - $sum_h_a);

	$i_h_b = $calibration_info->i_h_b;
	$ii_h_b = $calibration_info->ii_h_b;
	$sum_h_b = $i_h_b + $ii_h_b;
	$d_error_h_b = (dms2dec(180, 0, 0) - $sum_h_b);

	$i_v_a = $calibration_info->i_v_a;
	$ii_v_a = $calibration_info->ii_v_a;
	$sum_v_a = $i_v_a + $ii_v_a;
	$d_error_v_a = (dms2dec(360, 0, 0) - $sum_v_a);

	$i_v_b = $calibration_info->i_v_b;
	$ii_v_b = $calibration_info->ii_v_b;
	$sum_v_b = $i_v_b + $ii_v_b;
	$d_error_v_b = (dms2dec(360, 0, 0) - $sum_v_b);

	$i_edm_a_1 = number_format(round($calibration_info->i_edm_a_1, 3), 3);
	$i_edm_a_2 = number_format(round($calibration_info->i_edm_a_2, 3), 3);
	$i_edm_a_3 = number_format(round($calibration_info->i_edm_a_3, 3), 3);
	$f_i_edm_a = ($i_edm_a_1 + $i_edm_a_2 + $i_edm_a_3) / 3;

	$i_edm_b_1 = number_format(round($calibration_info->i_edm_b_1, 3), 3);
	$i_edm_b_2 = number_format(round($calibration_info->i_edm_b_2, 3), 3);
	$i_edm_b_3 = number_format(round($calibration_info->i_edm_b_3, 3), 3);
	$f_i_edm_b = ($i_edm_b_1 + $i_edm_b_2 + $i_edm_b_3) / 3;

	$ii_edm_a_1 = number_format(round($calibration_info->ii_edm_a_1, 3), 3);
	$ii_edm_a_2 = number_format(round($calibration_info->ii_edm_a_2, 3), 3);
	$ii_edm_a_3 = number_format(round($calibration_info->ii_edm_a_3, 3), 3);
	$f_ii_edm_a = ($ii_edm_a_1 + $ii_edm_a_2 + $ii_edm_a_3) / 3;

	$ii_edm_b_1 = number_format(round($calibration_info->ii_edm_b_1, 3), 3);
	$ii_edm_b_2 = number_format(round($calibration_info->ii_edm_b_2, 3), 3);
	$ii_edm_b_3 = number_format(round($calibration_info->ii_edm_b_3, 3), 3);
	$f_ii_edm_b = ($ii_edm_b_1 + $ii_edm_b_2 + $ii_edm_b_3) / 3;

	$f_i_ii_edm_a = ($f_i_edm_a + $f_ii_edm_a) / 2;
	$f_i_ii_edm_b = ($f_i_edm_b + $f_ii_edm_b) / 2;

	$info_report .= '<tr style="color:#424242;">
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>I</b></td>
                                      <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($i_h_a) . '</td>
                                      <td  class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($i_h_b) . '</td>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($i_v_a) . '</td>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($i_v_b) . '</td>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" rowspan="3"><b>I</b></td>
                                      <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_edm_a_1 . '</td>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_edm_b_1 . '</td>
                                  </tr>
                                  <tr>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>II</b></td>
                                      <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($ii_h_a) . '</td>
                                      <td  class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($ii_h_b) . '</td>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($ii_v_a) . '</td>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($ii_v_b) . '</td>
                                      <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_edm_a_2 . '</td>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_edm_b_2 . '</td>
                                  </tr>
                                  <tr>
                                      <td></td>
                                      <td colspan="2" class="text-center total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>Half Circle is 180&deg; 00\' 00"</b></td>
                                      <td colspan="2" class="text-center" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>Half Circle is 360&deg; 00\' 00"</b></td>
                                      <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_edm_a_3 . '</td>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_edm_b_3 . '</td>
                                  </tr>
                                  <tr>
                                      <td class="desc"></td>
                                      <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($sum_h_a) . '</td>
                                      <td  class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($sum_h_b) . '</td>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($sum_v_a) . '</td>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($sum_v_b) . '</td>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" rowspan="3"><b>II</b></td>
                                      <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_edm_a_1 . '</td>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_edm_b_1 . '</td>
                                  </tr>
                                  <tr>
                                      <td></td>
                                      <td colspan="2" class="text-center total"><b></b></td>
                                      <td colspan="2" class="text-center"><b></b></td>
                                      <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_edm_a_2 . '</td>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_edm_b_2 . '</td>
                                  </tr>
                                  <tr>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>DOUBLE ERROR</b></td>
                                      <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($d_error_h_a) . '</td>
                                      <td  class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($d_error_h_b) . '</td>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($d_error_v_a) . '</td>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($d_error_v_b) . '</td>
                                      <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_edm_a_3 . '</td>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_edm_b_3 . '</td>
                                  </tr>
                                  <tr>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>INDEX ERROR (ACCEPTABLE)</b></td>
                                      <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">YES..... NO.....</td>
                                      <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">YES..... NO.....</td>
                                      <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">YES..... NO.....</td>
                                      <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">YES..... NO.....</td>
                                      <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="3"></td>
                                  </tr>';
	$info_report .= ' <tr>
                                      <td class="no-border" rowspan="3" colspan="4">
                                      </td>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:right;" colspan="2"><b>EDM FACE I AVERAGE</b></td>
                                      <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($f_i_edm_a, 2) . '</td>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($f_i_edm_b, 2) . '</td>
                                  </tr>
                                  <tr>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:right;" colspan="2"><b>EDM FACE II AVERAGE</b></td>
                                      <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($f_ii_edm_a, 2) . '</td>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($f_ii_edm_b, 2) . '</td>
                                  </tr>
                                  <tr>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:right;" colspan="2"><b>EDM FACE I&II MEAN</b></td>
                                      <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($f_i_ii_edm_a, 2) . '</td>
                                      <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center;">' . round($f_i_ii_edm_b, 2) . '</td>
                                  </tr>
                              </tbody>
              </table>';
} else if ($service_request->item_type == 'GPS') {
 
	$info_report .= '<table border="0" cellpadding="5">
                                         
                                        <thead>';
                                        $info_report .= '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . (get_option('pdf_font_size') + 4) . 'px;">
                                            <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Series</th>
                                            <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Seq. No.</th>
                                            <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Set</th>
                                            <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Rover Point</th>
                                            <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Eastings</th>
                                            <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Northings</th>
                                            <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Elevation</th>
                                            <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Horizontal Distance (m)</th>
                                            <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Elevation Accuracy (mm)</th>
                                        </tr>';

                              $i_v_a_1 = $calibration_info->i_v_a_1;
                              $i_v_a_2 = $calibration_info->i_v_a_2;
                              $i_v_a_3 = $calibration_info->i_v_a_3;
                              $ii_v_a_1 = $calibration_info->ii_v_a_1;
                              $ii_v_a_2 = $calibration_info->ii_v_a_2;
                              $ii_v_a_3 = $calibration_info->ii_v_a_3;
                              $iii_v_a_1 = $calibration_info->iii_v_a_1;
                              $iii_v_a_2 = $calibration_info->iii_v_a_2;
                              $iii_v_a_3 = $calibration_info->iii_v_a_3; 
                              $iv_v_a_1 = $calibration_info->iv_v_a_1;
                              $iv_v_a_2 = $calibration_info->iv_v_a_2;
                              $iv_v_a_3 = $calibration_info->iv_v_a_3;
                              $v_v_a_1 = $calibration_info->v_v_a_1;
                              $v_v_a_2 = $calibration_info->v_v_a_2;
                              $v_v_a_3 = $calibration_info->v_v_a_3; 
                              $vi_v_a_1 = $calibration_info->vi_v_a_1;
                              $vi_v_a_2 = $calibration_info->vi_v_a_2;
                              $vi_v_a_3 = $calibration_info->vi_v_a_3; 
                              $vii_v_a_1 = $calibration_info->vii_v_a_1;
                              $vii_v_a_2 = $calibration_info->vii_v_a_2;
                              $vii_v_a_3 = $calibration_info->vii_v_a_3;
                              $viii_v_a_1 = $calibration_info->viii_v_a_1;
                              $viii_v_a_2 = $calibration_info->viii_v_a_2;
                              $viii_v_a_3 = $calibration_info->viii_v_a_3;
                              $xi_v_a_1 = $calibration_info->xi_v_a_1;
                              $xi_v_a_2 = $calibration_info->xi_v_a_2;
                              $xi_v_a_3 = $calibration_info->xi_v_a_3; 
                              $x_v_a_1 = $calibration_info->x_v_a_1;
                              $x_v_a_2 = $calibration_info->x_v_a_2;
                              $x_v_a_3 = $calibration_info->x_v_a_3;

                              $sumValue1 = round(sqrt(pow(($i_v_a_1 - $ii_v_a_1), 2) + pow(($i_v_a_2 - $ii_v_a_2), 2)), 4);
                              $sumValue2 = round(sqrt(pow(($iii_v_a_1 - $iv_v_a_1), 2) + pow(($iii_v_a_2 - $iv_v_a_2), 2)), 4);
                              $sumValue3 = round(sqrt(pow(($v_v_a_1 - $vi_v_a_1), 2) + pow(($v_v_a_2 - $vi_v_a_2), 2)), 4);
                              $sumValue4 = round(sqrt(pow(($vii_v_a_1 - $viii_v_a_1), 2) + pow(($vii_v_a_2 - $viii_v_a_2), 2)), 4);
                              $sumValue5 = round(sqrt(pow(($xi_v_a_1 - $x_v_a_1), 2) + pow(($xi_v_a_2 - $x_v_a_2), 2)), 4);

                              $sumValue6 = round($ii_v_a_3 - $i_v_a_3, 4);
                              $sumValue7 = round($iv_v_a_3 - $iii_v_a_3, 4);
                              $sumValue8 = round($vi_v_a_3 - $v_v_a_3, 4);
                              $sumValue9 = round($viii_v_a_3 - $vii_v_a_3, 4);
                              $sumValue10 = round($x_v_a_3 - $xi_v_a_3, 4);

                              $sumValue11 = round($i_v_a_1 + $iii_v_a_1 + $v_v_a_1 + $vii_v_a_1 + $xi_v_a_1) / 5;
                              $sumValue12 = round($i_v_a_2 + $iii_v_a_2 + $v_v_a_2 + $vii_v_a_2 + $xi_v_a_2) / 5;
                              $sumValue13 = round($i_v_a_3 + $iii_v_a_3 + $v_v_a_3 + $vii_v_a_3 + $xi_v_a_3) / 5;

                              $sumValue14 = round($ii_v_a_1 + $iv_v_a_1 + $vi_v_a_1 + $viii_v_a_1 + $x_v_a_1) / 5;
                              $sumValue15 = round($ii_v_a_2 + $iv_v_a_2 + $vi_v_a_2 + $viii_v_a_2 + $x_v_a_2) / 5;
                              $sumValue16 = round($ii_v_a_3 + $iv_v_a_3 + $vi_v_a_3 + $viii_v_a_3 + $x_v_a_3) / 5;

                              $sumValue17 = round(($sumValue1 + $sumValue2 + $sumValue3 + $sumValue4 + $sumValue5)/ 5, 3);
                              $sumValue18 = round(($sumValue6 + $sumValue7 + $sumValue8 + $sumValue9 + $sumValue10) / 5, 3);

                              $sumValue19 = round(($sumValue17) / 5, 3);
                              $sumValue20 = round(($sumValue18) / 5, 3);

                    

                          $info_report .= '<tr style="color:#424242;">
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($i_v_a_1, 3) . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_v_a_2 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_v_a_3 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                          </tr>';
                          
                          $info_report .= '<tr style="color:#424242;">
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_v_a_1 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_v_a_2 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_v_a_3 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue1 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue6 . '</td>
                          </tr>';
                          
                          $info_report .= '<tr style="color:#424242;">
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">3</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iii_v_a_1 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iii_v_a_2 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iii_v_a_3 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                          </tr>';
                          
                          $info_report .= '<tr style="color:#424242;">
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">4</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_v_a_1 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_v_a_2 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_v_a_3 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue2 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue7 . '</td>
                          </tr>';
                          
                          $info_report .= '<tr style="color:#424242;">
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">5</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">3</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $v_v_a_1 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $v_v_a_2 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $v_v_a_3 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                          </tr>';
                          
                          $info_report .= '<tr style="color:#424242;">
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">6</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">3</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_v_a_1 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_v_a_2 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_v_a_3 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue3 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue8 . '</td>
                          </tr>';
                          
                          $info_report .= '<tr style="color:#424242;">
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">7</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">4</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vii_v_a_1 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vii_v_a_2 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vii_v_a_3 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                          </tr>';
                          
                          $info_report .= '<tr style="color:#424242;">
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">8</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">4</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_v_a_1 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_v_a_2 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_v_a_3 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue4 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue9 . '</td>
                          </tr>';
                          
                          $info_report .= '<tr style="color:#424242;">
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">9</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">5</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $xi_v_a_1 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $xi_v_a_2 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $xi_v_a_3 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                          </tr>';
                          
                          $info_report .= '<tr style="color:#424242;">
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">10</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">5</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_v_a_1 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_v_a_2 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_v_a_3 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue5 . '</td>
                              <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue10 . '</td>
                          </tr>
                          <tr style="color:#424242;">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b>STOP TIME:</b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan="2"><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                          </tr>
                          <tr style="color:#424242;">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b>START TIME:</b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan="2"><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                         </tr>';

                         $i_v_b_1 = $calibration_info->i_v_b_1;
                                  $i_v_b_2 = $calibration_info->i_v_b_2;
                                  $i_v_b_3 = $calibration_info->i_v_b_3;
                                  $ii_v_b_1 = $calibration_info->ii_v_b_1;
                                  $ii_v_b_2 = $calibration_info->ii_v_b_2;
                                  $ii_v_b_3 = $calibration_info->ii_v_b_3;
                                  $iii_v_b_1 = $calibration_info->iii_v_b_1;
                                  $iii_v_b_2 = $calibration_info->iii_v_b_2;
                                  $iii_v_b_3 = $calibration_info->iii_v_b_3; 
                                  $iv_v_b_1 = $calibration_info->iv_v_b_1;
                                  $iv_v_b_2 = $calibration_info->iv_v_b_2;
                                  $iv_v_b_3 = $calibration_info->iv_v_b_3;
                                  $v_v_b_1 = $calibration_info->v_v_b_1;
                                  $v_v_b_2 = $calibration_info->v_v_b_2;
                                  $v_v_b_3 = $calibration_info->v_v_b_3; 
                                  $vi_v_b_1 = $calibration_info->vi_v_b_1;
                                  $vi_v_b_2 = $calibration_info->vi_v_b_2;
                                  $vi_v_b_3 = $calibration_info->vi_v_b_3; 
                                  $vii_v_b_1 = $calibration_info->vii_v_b_1;
                                  $vii_v_b_2 = $calibration_info->vii_v_b_2;
                                  $vii_v_b_3 = $calibration_info->vii_v_b_3;
                                  $viii_v_b_1 = $calibration_info->viii_v_b_1;
                                  $viii_v_b_2 = $calibration_info->viii_v_b_2;
                                  $viii_v_b_3 = $calibration_info->viii_v_b_3;
                                  $xi_v_b_1 = $calibration_info->xi_v_b_1;
                                  $xi_v_b_2 = $calibration_info->xi_v_b_2;
                                  $xi_v_b_3 = $calibration_info->xi_v_b_3; 
                                  $x_v_b_1 = $calibration_info->x_v_b_1;
                                  $x_v_b_2 = $calibration_info->x_v_b_2;
                                  $x_v_b_3 = $calibration_info->x_v_b_3;

                                  $sumValue1 = round(sqrt(pow(($i_v_b_1 - $ii_v_b_1), 2) + pow(($i_v_b_2 - $ii_v_b_2), 2)), 4);
                                  $sumValue2 = round(sqrt(pow(($iii_v_b_1 - $iv_v_b_1), 2) + pow(($iii_v_b_2 - $iv_v_b_2), 2)), 4);
                                  $sumValue3 = round(sqrt(pow(($v_v_b_1 - $vi_v_b_1), 2) + pow(($v_v_b_2 - $vi_v_b_2), 2)), 4);
                                  $sumValue4 = round(sqrt(pow(($vii_v_b_1 - $viii_v_b_1), 2) + pow(($vii_v_b_2 - $viii_v_b_2), 2)), 4);
                                  $sumValue5 = round(sqrt(pow(($xi_v_b_1 - $x_v_b_1), 2) + pow(($xi_v_b_2 - $x_v_b_2), 2)), 4);

                                  $sumValue6 = round($ii_v_b_3 - $i_v_b_3, 4);
                                  $sumValue7 = round($iv_v_b_3 - $iii_v_b_3, 4);
                                  $sumValue8 = round($vi_v_b_3 - $v_v_b_3, 4);
                                  $sumValue9 = round($viii_v_b_3 - $vii_v_b_3, 4);
                                  $sumValue10 = round($x_v_b_3 - $xi_v_b_3, 4);

                                  $sumValue11 = round($i_v_b_1 + $iii_v_b_1 + $v_v_b_1 + $vii_v_b_1 + $xi_v_b_1) / 5;
                                  $sumValue12 = round($i_v_b_2 + $iii_v_b_2 + $v_v_b_2 + $vii_v_b_2 + $xi_v_b_2) / 5;
                                  $sumValue13 = round($i_v_b_3 + $iii_v_b_3 + $v_v_b_3 + $vii_v_b_3 + $xi_v_b_3) / 5;

                                  $sumValue14 = round($ii_v_b_1 + $iv_v_b_1 + $vi_v_b_1 + $viii_v_b_1 + $x_v_b_1) / 5;
                                  $sumValue15 = round($ii_v_b_2 + $iv_v_b_2 + $vi_v_b_2 + $viii_v_b_2 + $x_v_b_2) / 5;
                                  $sumValue16 = round($ii_v_b_3 + $iv_v_b_3 + $vi_v_b_3 + $viii_v_b_3 + $x_v_b_3) / 5;

                                  $sumValue17 = round(($sumValue1 + $sumValue2 + $sumValue3 + $sumValue4 + $sumValue5)/ 5, 3);
                                  $sumValue18 = round(($sumValue6 + $sumValue7 + $sumValue8 + $sumValue9 + $sumValue10) / 5, 3);

                                  $sumValue19 = round(($sumValue17) / 5, 3);
                                  $sumValue20 = round(($sumValue18) / 5, 3);

                        // TABLE A3
                        $info_report .= '<tr style="color:#424242;">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">11</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($i_v_b_1, 3) . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_v_b_2 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_v_b_3 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                        </tr>';

                        $info_report .= '<tr style="color:#424242;">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">12</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_v_b_1 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_v_b_2 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_v_b_3 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue1 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue6 . '</td>
                        </tr>';

                        $info_report .= '<tr style="color:#424242;">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">13</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iii_v_b_1 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iii_v_b_2 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iii_v_b_3 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                        </tr>';

                        $info_report .= '<tr style="color:#424242;">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">14</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_v_b_1 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_v_b_2 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_v_b_3 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue2 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue7 . '</td>
                        </tr>';

                        $info_report .= '<tr style="color:#424242;">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">15</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">3</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $v_v_b_1 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $v_v_b_2 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $v_v_b_3 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                        </tr>';

                        $info_report .= '<tr style="color:#424242;">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">16</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">3</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_v_b_1 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_v_b_2 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_v_b_3 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue3 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue8 . '</td>
                        </tr>';

                        $info_report .= '<tr style="color:#424242;">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">17</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">4</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vii_v_b_1 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vii_v_b_2 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vii_v_b_3 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                        </tr>';

                        $info_report .= '<tr style="color:#424242;">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">18</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">4</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_v_b_1 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_v_b_2 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_v_b_3 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue4 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue9 . '</td>
                        </tr>';

                        $info_report .= '<tr style="color:#424242;">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">19</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">5</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $xi_v_b_1 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $xi_v_b_2 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $xi_v_b_3 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                        </tr>';

                        $info_report .= '<tr style="color:#424242;">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">20</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">5</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_v_b_1 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_v_b_2 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_v_b_3 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue5 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue10 . '</td>
                        </tr>

                       
                        <tr style="color:#424242;">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b>STOP TIME:</b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan="2"><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                        </tr>
                         <tr style="color:#424242;">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b>START TIME:</b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan="2"><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                        </tr>';
                    
$info_report .= '<tr style="color:#424242;">
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">21</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($i_v_c_1, 3) . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_v_c_2 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_v_c_3 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
</tr>';

$info_report .= '<tr style="color:#424242;">
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">22</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_v_c_1 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_v_c_2 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_v_c_3 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue1 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue6 . '</td>
</tr>';

$info_report .= '<tr style="color:#424242;">
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">23</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iii_v_c_1 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iii_v_c_2 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iii_v_c_3 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
</tr>';

$info_report .= '<tr style="color:#424242;">
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">24</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_v_c_1 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_v_c_2 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_v_c_3 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue2 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue7 . '</td>
</tr>';

$info_report .= '<tr style="color:#424242;">
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">3</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">25</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">3</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $v_v_c_1 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $v_v_c_2 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $v_v_c_3 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
</tr>';

$info_report .= '<tr style="color:#424242;">
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">26</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">3</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_v_c_1 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_v_c_2 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_v_c_3 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue3 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue8 . '</td>
</tr>';

$info_report .= '<tr style="color:#424242;">
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">27</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">4</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vii_v_c_1 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vii_v_c_2 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vii_v_c_3 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
</tr>';

$info_report .= '<tr style="color:#424242;">
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">28</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">4</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_v_c_1 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_v_c_2 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_v_c_3 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue4 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue9 . '</td>
</tr>';

$info_report .= '<tr style="color:#424242;">
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">29</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">5</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $xi_v_c_1 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $xi_v_c_2 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $xi_v_c_3 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
</tr>';

$info_report .= '<tr style="color:#424242;">
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">30</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">5</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_v_c_1 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_v_c_2 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_v_c_3 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue5 . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue10 . '</td>
</tr>
</thead>
<tr style="color:#424242;">
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b>STOP TIME:</b></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan="2"><b></b></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
</tr>';

$info_report .= '<tr style="color:#424242;">
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Averaged</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . ($sumValue11) . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . ($sumValue12) . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . ($sumValue13) . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
</tr>';

$info_report .= '<tr style="color:#424242;">
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . ($sumValue14) . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . ($sumValue15) . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . ($sumValue16) . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . ($sumValue17) . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($sumValue18, 4) . '</td>
</tr>';

$info_report .= '<tr style="color:#424242;">
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td colspan="4" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:right;">Horizontal Distance/Elevation Accuracy</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($sumValue19, 4) . '</td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($sumValue20, 4) . '</td>
</tr>';

$info_report .= '<tr style="color:#424242;">
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td colspan="5" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
    <td colspan="2" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; background-color: yellow;">Passed / Failed</td>
</tr>



                        



                                  
                              <tbody>
                              </tbody>
              </table>';

};
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_report, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(6);

//Report content End

//$pdf->Ln(10);
$validate = '
        <b style="font-size: ' . ($font_size + 2) . 'px; font-weight:bold; color:maroon;">REMARKS:</b><br>
        <table border="0" cellpadding="5">
          <tbody>
            <tr>
              <td style="font-size: ' . ($font_size + 2) . 'px; font-weight:bold;">' . $calibration_info->calibration_remark . '</td>
            </tr>
          </tbody>
        </table>
        <div style="height:20px;"></div>
        <b style="font-size: ' . ($font_size + 2) . 'px; font-weight:bold; color:maroon;">REPORT AUTHORIZATION:</b><br>
        <table border="0" cellpadding="5">
          <tbody>
            <tr>
              <td style="font-size: ' . ($font_size + 2) . 'px; font-weight:bold;">
              SURVEYOR:(name).............................................................. (sign).....................................................
              </td>
            </tr>
            <tr>
              <td style="font-size: ' . ($font_size + 2) . 'px; font-weight:bold;">   DATE:............./............../20............</td>
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