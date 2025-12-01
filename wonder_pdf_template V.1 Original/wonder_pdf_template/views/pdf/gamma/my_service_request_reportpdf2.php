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

    $lv_v_a_1 = $calibration_info->lv_v_a_1;
    $lv_v_a_2 = $calibration_info->lv_v_a_2;
    $lv_v_a_3 = $calibration_info->lv_v_a_3;
    $lv_v_a_4 = $calibration_info->lv_v_a_4;
    $lv_v_a_5 = $calibration_info->lv_v_a_5;
    $lv_v_a_6 = $calibration_info->lv_v_a_6;
    $lv_v_a_7 = $calibration_info->lv_v_a_7;
    $lv_v_a_8 = $calibration_info->lv_v_a_8;
    $lv_v_a_9 = $calibration_info->lv_v_a_9;

            $info_report .= '<b style="font-size:12px; color:maroon;">INSTRUMENT INFORMATION REPORT</b><br><br>
            <table border="0" cellpadding="5">

                <thead>
                    <tr style="color:#424242;">
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; background-color: lightgrey; border:1px solid #000; text-align:left; color:maroon;" colspan="6"><b>INSTRUMENT INFORMATION</b></td>
                    </tr>
                </thead>
                <tbody>
                    <tr class="form-inline">
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT MAKE</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->lv_v_a_1 . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT MODEL</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->lv_v_a_2 . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT SERIAL NO.</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->lv_v_a_3 . '</td>
                    </tr>

                    <tr class="form-inline">
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT CONDITION</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->lv_v_a_4 . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">TEST DISTANCE (M)</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->lv_v_a_5 . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">MANUFACTURER ACCURACY (00)</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->lv_v_a_6 . '</td>
                    </tr>

                    <tr class="form-inline">
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">WEATHER CONDITION</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->lv_v_a_7 . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">TEMPERATURE (°C)</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->lv_v_a_8 . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">AIR PRESSURE (hPa)</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->lv_v_a_9 . '</td>
                    </tr>

                </tbody>
            </table><br><br>';
            
            $info_report .= '<b style="font-size:12px; color:maroon;">PRE-CALIBRATION REPORT</b><br><br>
                    <table border="1" cellpadding="4">
                        <thead>
                        <tr>
                            <th class="desc" style="text-align:center; font-weight:500;"></th>
                            <th class="total" style="text-align:center; font-weight:500;">BACKSIGHT </th>
                            <th class="desc" style="text-align:center; font-weight:500;">FORESIGHT B</th>
                            <th class="total" style="text-align:center; font-weight:500;">d1(mm) <br> Elevation (mm)</th>
                            <th class="desc" style="text-align:center; font-weight:500;">Residual from <br>  Mean (mm)</th>
                            <th class="total" style="text-align:center; font-weight:500;">Squared <br> Residuals (mm)</th>
                            <th class="total" style="text-align:center; font-weight:500;">BACKSIGHT </th>
                            <th class="desc" style="text-align:center; font-weight:500;">FORESIGHT B</th>
                            <th class="total" style="text-align:center; font-weight:500;">d2(mm) <br> Elevation (mm)</th>
                            <th class="desc" style="text-align:center; font-weight:500;">Residual from <br>  Mean (mm)</th>
                            <th class="total" style="text-align:center; font-weight:500;">Squared <br> Residuals (mm)</th>
                        </tr>
                        </thead>
                        <tbody>';

                        $i_backsight_a = $calibration_info->i_backsight_a;
                        $i_foresight_b = $calibration_info->i_foresight_b;
                        $i_backsight_c = $calibration_info->i_backsight_c;
                        $i_foresight_d = $calibration_info->i_foresight_d;
                        
                        $ii_backsight_a = $calibration_info->ii_backsight_a;
                        $ii_foresight_b = $calibration_info->ii_foresight_b;
                        $ii_backsight_c = $calibration_info->ii_backsight_c;
                        $ii_foresight_d = $calibration_info->ii_foresight_d;
                        
                        $iii_backsight_a = $calibration_info->iii_backsight_a;
                        $iii_foresight_b = $calibration_info->iii_foresight_b;
                        $iii_backsight_c = $calibration_info->iii_backsight_c;
                        $iii_foresight_d = $calibration_info->iii_foresight_d;
                        
                        $iv_backsight_a = $calibration_info->iv_backsight_a;
                        $iv_foresight_b = $calibration_info->iv_foresight_b;
                        $iv_backsight_c = $calibration_info->iv_backsight_c;
                        $iv_foresight_d = $calibration_info->iv_foresight_d;
                        
                        $v_backsight_a = $calibration_info->v_backsight_a;
                        $v_foresight_b = $calibration_info->v_foresight_b;
                        $v_backsight_c = $calibration_info->v_backsight_c;
                        $v_foresight_d = $calibration_info->v_foresight_d;
                        
                        $vi_backsight_a = $calibration_info->vi_backsight_a;
                        $vi_foresight_b = $calibration_info->vi_foresight_b;
                        $vi_backsight_c = $calibration_info->vi_backsight_c;
                        $vi_foresight_d = $calibration_info->vi_foresight_d;
                        
                        $vii_backsight_a = $calibration_info->vii_backsight_a;
                        $vii_foresight_b = $calibration_info->vii_foresight_b;
                        $vii_backsight_c = $calibration_info->vii_backsight_c;
                        $vii_foresight_d = $calibration_info->vii_foresight_d;
                        
                        $viii_backsight_a = $calibration_info->viii_backsight_a;
                        $viii_foresight_b = $calibration_info->viii_foresight_b;
                        $viii_backsight_c = $calibration_info->viii_backsight_c;
                        $viii_foresight_d = $calibration_info->viii_foresight_d;
                        
                        $ix_backsight_a = $calibration_info->ix_backsight_a;
                        $ix_foresight_b = $calibration_info->ix_foresight_b;
                        $ix_backsight_c = $calibration_info->ix_backsight_c;
                        $ix_foresight_d = $calibration_info->ix_foresight_d;
                        
                        $x_backsight_a = $calibration_info->x_backsight_a;
                        $x_foresight_b = $calibration_info->x_foresight_b;
                        $x_backsight_c = $calibration_info->x_backsight_c;
                        $x_foresight_d = $calibration_info->x_foresight_d;
                        


                        $diff_i = $i_backsight_a - $i_foresight_b;
                        $diff_ii = $i_backsight_c - $i_foresight_d;
                        
                        $diff_iii = $ii_backsight_a - $ii_foresight_b;
                        $diff_iv = $ii_backsight_c - $ii_foresight_d;
                        
                        $diff_v = $iii_backsight_a - $iii_foresight_b;
                        $diff_vi = $iii_backsight_c - $iii_foresight_d;
                        
                        $diff_vii = $iv_backsight_a - $iv_foresight_b;
                        $diff_viii = $iv_backsight_c - $iv_foresight_d;
                        
                        $diff_ix = $v_backsight_a - $v_foresight_b;
                        $diff_x = $v_backsight_c - $v_foresight_d;
                        
                        $diff_xi = $vi_backsight_a - $vi_foresight_b;
                        $diff_xii = $vi_backsight_c - $vi_foresight_d;
                        
                        $diff_xiii = $vii_backsight_a - $vii_foresight_b;
                        $diff_xiv = $vii_backsight_c - $vii_foresight_d;
                        
                        $diff_xv = $viii_backsight_a - $viii_foresight_b;
                        $diff_xvi = $viii_backsight_c - $viii_foresight_d;
                        
                        $diff_xvii = $ix_backsight_a - $ix_foresight_b;
                        $diff_xviii = $ix_backsight_c - $ix_foresight_d;
                        
                        $diff_xix = $x_backsight_a - $x_foresight_b;
                        $diff_xx = $x_backsight_c - $x_foresight_d;

                        // Calculate the sum of the specified diff variables
                        $sumdiffs1 = $diff_i + $diff_iii + $diff_v + $diff_vii + $diff_ix + $diff_xi + $diff_xiii + $diff_xv + $diff_xvii + $diff_xix;
                        $sumdiffs2 = $diff_ii + $diff_iv + $diff_vi + $diff_viii + $diff_x + $diff_xii + $diff_xiv + $diff_xvi + $diff_xviii + $diff_xx;

                        $sum_a = $i_backsight_a + $ii_backsight_a + $iii_backsight_a + $iv_backsight_a + $v_backsight_a +
                        $vi_backsight_a + $vii_backsight_a + $viii_backsight_a + $ix_backsight_a + $x_backsight_a;

                        $sum_b = $i_foresight_b + $ii_foresight_b + $iii_foresight_b + $iv_foresight_b + $v_foresight_b +
                        $vi_foresight_b + $vii_foresight_b + $viii_foresight_b + $ix_foresight_b + $x_foresight_b;

                        $sum_c = $i_backsight_c + $ii_backsight_c + $iii_backsight_c + $iv_backsight_c + $v_backsight_c +
                        $vi_backsight_c + $vii_backsight_c + $viii_backsight_c + $ix_backsight_c + $x_backsight_c;

                        $sum_d = $i_foresight_d + $ii_foresight_d + $iii_foresight_d + $iv_foresight_d + $v_foresight_d +
                        $vi_foresight_d + $vii_foresight_d + $viii_foresight_d + $ix_foresight_d + $x_foresight_d;

                        // Calculate the result of E44 divided by 10
                        $d1result = $sumdiffs1 / 10;
                        $d2result = $sumdiffs2 / 10;

                        // Calculate the result of E45 minus E34
                        $d1elevationresult1 = $d1result - $diff_i;
                        $d2elevationresult1 = $d2result - $diff_ii;
                        $d3elevationresult1 = $d1result - $diff_iii;
                        $d4elevationresult1 = $d2result - $diff_iv;

                        // Calculate the result of E45 minus E34
                        $d1elevationresult2 = $d1result - $diff_v;
                        $d2elevationresult2 = $d2result - $diff_vi;
                        $d3elevationresult2 = $d1result - $diff_vii;
                        $d4elevationresult2 = $d2result - $diff_viii;

                        // Calculate the result of E45 minus E34
                        $d1elevationresult3 = $d1result - $diff_ix;
                        $d2elevationresult3 = $d2result - $diff_x;
                        $d3elevationresult3 = $d1result - $diff_xi;
                        $d4elevationresult3 = $d2result - $diff_xii;

                        // Calculate the result of E45 minus E34
                        $d1elevationresult4 = $d1result - $diff_xiii;
                        $d2elevationresult4 = $d2result - $diff_xiv;
                        $d3elevationresult4 = $d1result - $diff_xv;
                        $d4elevationresult4 = $d2result - $diff_xvi;

                        // Calculate the result of E45 minus E34
                        $d1elevationresult5 = $d1result - $diff_xvii;
                        $d2elevationresult5 = $d2result - $diff_xviii;
                        $d3elevationresult5 = $d1result - $diff_xix;
                        $d4elevationresult5 = $d2result - $diff_xx;


                         // Calculate the square of F34
                        $srd1elevationresult1 = $d1elevationresult1 ** 2;
                        $srd2elevationresult1 = $d2elevationresult1 ** 2;
                        $srd3elevationresult1 = $d3elevationresult1 ** 2;
                        $srd4elevationresult1 = $d4elevationresult1 ** 2;

                        // Calculate the square of F34
                        $srd1elevationresult2 = $d1elevationresult2 ** 2;
                        $srd2elevationresult2 = $d2elevationresult2 ** 2;
                        $srd3elevationresult2 = $d3elevationresult2 ** 2;
                        $srd4elevationresult2 = $d4elevationresult2 ** 2;

                        // Calculate the square of F34
                        $srd1elevationresult3 = $d1elevationresult3 ** 2;
                        $srd2elevationresult3 = $d2elevationresult3 ** 2;
                        $srd3elevationresult3 = $d3elevationresult3 ** 2;
                        $srd4elevationresult3 = $d4elevationresult3 ** 2;

                        // Calculate the square of F34
                        $srd1elevationresult4 = $d1elevationresult4 ** 2;
                        $srd2elevationresult4 = $d2elevationresult4 ** 2;
                        $srd3elevationresult4 = $d3elevationresult4 ** 2;
                        $srd4elevationresult4 = $d4elevationresult4 ** 2;

                        // Calculate the square of F34
                        $srd1elevationresult5 = $d1elevationresult5 ** 2;
                        $srd2elevationresult5 = $d2elevationresult5 ** 2;
                        $srd3elevationresult5 = $d3elevationresult5 ** 2;
                        $srd4elevationresult5 = $d4elevationresult5 ** 2;


                        // Calculate the sum of the specified elevation results up to the fifth iteration
                        $sumElevationResults = 
                        $d1elevationresult1 + $d3elevationresult1 + 
                        $d1elevationresult2 + $d3elevationresult2 + 
                        $d1elevationresult3 + $d3elevationresult3 + 
                        $d1elevationresult4 + $d3elevationresult4 + 
                        $d1elevationresult5 + $d3elevationresult5;

                        $sumElevationResults2 = 
                        $d2elevationresult1 + $d4elevationresult1 + 
                        $d2elevationresult2 + $d4elevationresult2 + 
                        $d2elevationresult3 + $d4elevationresult3 + 
                        $d2elevationresult4 + $d4elevationresult4 + 
                        $d2elevationresult5 + $d4elevationresult5;

                        // Calculate the sum of the specified elevation results up to the fifth iteration
                        $sumsrdelevationresult = 
                        $srd1elevationresult1 + $srd3elevationresult1 + 
                        $srd1elevationresult2 + $srd3elevationresult2 + 
                        $srd1elevationresult3 + $srd3elevationresult3 + 
                        $srd1elevationresult4 + $srd3elevationresult4 + 
                        $srd1elevationresult5 + $srd3elevationresult5;

                        $sumsrdelevationresult1 = 
                        $srd2elevationresult1 + $srd4elevationresult1 + 
                        $srd2elevationresult2 + $srd4elevationresult2 + 
                        $srd2elevationresult3 + $srd4elevationresult3 + 
                        $srd2elevationresult4 + $srd4elevationresult4 + 
                        $srd2elevationresult5 + $srd4elevationresult5;

                        // Calculate the result of G92 divided by 9
                        $result = $sumsrdelevationresult / 9;
                        $roundedResult = round($result, 4);
                        $result1 = $sumsrdelevationresult1 / 9;
                        $roundedResult1 = round($result1, 4);


                        $standardDeviation = $d1result - $d2result;

                        // Calculate the result of 2.5 power by G46

                        $dd = pow(2.5, 0.27);

                        
                        $aceptablesd = pow(2.5, $roundedResult);
                        $aceptablesd = round($aceptablesd, 4);
                        $aceptablesd1 = pow(2.5, $roundedResult1);
                        $aceptablesd1 = round($aceptablesd1, 4);

                        // Perform the comparison
                        $absolute = $standardDeviation < $aceptablesd;
                        $absolute1 = $standardDeviation < $aceptablesd1;

                        // Determine the background color based on the comparison result
                        $backgroundColor = $absolute ? 'lightgreen' : '#FFCCCC'; // Green for true, faded red for false
                        $backgroundColor1 = $absolute1 ? 'lightgreen' : '#FFCCCC'; // Green for true, faded red for false

                        $elevationtestline1 = $sum_a - $sum_b;
                        $elevationtestline2 = $sum_c - $sum_d;

                       

                        // Results for each condition
                        $lresult1 = ($sumdiffs1 == $elevationtestline1 && $sumdiffs2 == $elevationtestline2) ? 'Pass' : 'Fail';
                        $lresult2 = ($sumElevationResults == 0 && $sumElevationResults2 == 0) ? 'Pass' : 'Fail';
                        $lresult3 = ($standardDeviation < $aceptablesd) ? 'Pass' : 'Fail';
                        $lresult4 = ($standardDeviation < $aceptablesd1) ? 'Pass' : 'Fail';

                       // Determine the row color based on results
                        $rowColor = ($lresult1 == 'Pass' && $lresult3 == 'Pass' && $lresult4 == 'Pass') ? 'lightgreen' : 'lightcoral';

                        $info_report .= '<tr style="color:#424242;">
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_backsight_a . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_foresight_b . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_i . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d1elevationresult1, 4) . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd1elevationresult1, 4) . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_backsight_c . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_foresight_d . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_ii . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d2elevationresult1, 4) . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd2elevationresult1, 4) . '</td>
                        </tr>';
                
                        $info_report .= '<tr style="color:#424242;">
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_backsight_a . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_foresight_b . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_iii . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d3elevationresult1, 4) . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd3elevationresult1, 4) . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_backsight_c . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_foresight_d . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_iv . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d4elevationresult1, 4) . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd4elevationresult1, 4) . '</td>
                        </tr>';
                        
                        $info_report .= '<tr style="color:#424242;">
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">3</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iii_backsight_a . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iii_foresight_b . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_v . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d1elevationresult2, 4) . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd1elevationresult2, 4) . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iii_backsight_c . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iii_foresight_d . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_vi . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d2elevationresult2, 4) . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd2elevationresult2, 4) . '</td>
                        </tr>';
                
                        $info_report .= '<tr style="color:#424242;">
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">4</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_backsight_a . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_foresight_b . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_vii . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d3elevationresult2, 4) . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd3elevationresult2, 4) . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_backsight_c . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_foresight_d . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_viii . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d4elevationresult2, 4) . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd4elevationresult2, 4) . '</td>
                        </tr>';
                        $info_report .= '<tr style="color:#424242;">
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">5</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $v_backsight_a . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $v_foresight_b . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_ix . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d1elevationresult3, 4) . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd1elevationresult3, 4) . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $v_backsight_c . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $v_foresight_d . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_x . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d2elevationresult3, 4) . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd2elevationresult3, 4) . '</td>
                        </tr>';

                        $info_report .= '<tr style="color:#424242;">
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">6</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_backsight_a . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_foresight_b . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_xi . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d3elevationresult3, 4) . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd3elevationresult3, 4) . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_backsight_c . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_foresight_d . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_xii . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d4elevationresult3, 4) . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd4elevationresult3, 4) . '</td>
                        </tr>';

                        $info_report .= '<tr style="color:#424242;">
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">7</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vii_backsight_a . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vii_foresight_b . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_xiii . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d1elevationresult4, 4) . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd1elevationresult4, 4) . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vii_backsight_c . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vii_foresight_d . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_xiv . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d2elevationresult4, 4) . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd2elevationresult4, 4) . '</td>
                        </tr>';

                        $info_report .= '<tr style="color:#424242;">
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">8</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_backsight_a . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_foresight_b . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_xv . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d3elevationresult4, 4) . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd3elevationresult4, 4) . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_backsight_c . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_foresight_d . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_xvi . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d4elevationresult4, 4) . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd4elevationresult4, 4) . '</td>
                        </tr>';

                        $info_report .= '<tr style="color:#424242;">
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">9</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ix_backsight_a . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ix_foresight_b . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_xvii . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d1elevationresult5, 4) . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd1elevationresult5, 4) . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ix_backsight_c . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ix_foresight_d . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_xviii . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d2elevationresult5, 4) . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd2elevationresult5, 4) . '</td>
                        </tr>';

                        $info_report .= '<tr style="color:#424242;">
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">10</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_backsight_a . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_foresight_b . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_xix . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d3elevationresult5, 4) . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd3elevationresult5, 4) . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_backsight_c . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_foresight_d . '</td>
                            <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_xx . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d4elevationresult5, 4) . '</td>
                            <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd4elevationresult5, 4) . '</td>
                        </tr>';

                    $info_report .= '<tr style="background-color: lightgrey;">
                        <td><b>Sum</b></td>
                        <td>' . $sum_a . '</td>
                        <td>' . $sum_b . '</td>
                        <td>' . $sumdiffs1 . '</td>
                        <td>' . round($sumElevationResults, 4) . '</td>
                        <td>' . round($sumsrdelevationresult, 4) . '</td>
                        <td>' . $sum_c . '</td>
                        <td>' . $sum_d . '</td>
                        <td>' . $sumdiffs2 . '</td>
                        <td>' . round($sumElevationResults2, 4) . '</td>
                        <td>' . round($sumsrdelevationresult1, 4) . '</td>
                    </tr>';
                    
                    $info_report .= '<tr style="background-color: lightgrey;">
                        <td></td>
                        <td><b>Mean</b></td>
                        <td>d1</td>
                        <td>' . $d1result . '</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>d2</td>
                        <td>' . $d2result . '</td>
                        <td></td>
                        <td></td>
                    </tr>';
                    
                    $info_report .= '<tr style="background-color: lightgrey;">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Standard deviation (mm)</td>
                        <td>s</td>
                        <td>' . round($roundedResult, 2) . '</td>                               
                        <td></td>
                        <td></td>
                        <td>Standard deviation (mm)</td>
                        <td>s</td>
                        <td>' . round($roundedResult1, 2) . '</td>                               
                    </tr>';
                    
                    $info_report .= '<tr style="background-color: lightgrey;">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Meaned Elevation differences (mm)</td>
                        <td>d1-d2</td>
                        <td>' . number_format($standardDeviation, 2, '.', '') . '</td>
                        <td></td>
                        <td></td>
                        <td>Meaned Elevation differences (mm)</td>
                        <td>d1-d2</td>
                        <td>' . number_format($standardDeviation, 2, '.', '') . '</td>
                    </tr>';
                    
                    $info_report .= '<tr style="background-color: lightgrey;">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Acceptable Standard Deviation (mm)</td>
                        <td>2.5 x s</td>
                        <td>' . number_format($aceptablesd, 2, '.', '') . '</td>
                        <td></td>
                        <td></td>
                        <td>Acceptable Standard Deviation (mm)</td>
                        <td>2.5 x s</td>
                        <td>' . number_format($aceptablesd1, 2, '.', '') . '</td>
                    </tr>';                   
                    $info_report .= '<tr style="background-color: lightgrey;">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Absolute</td>
                        <td>(d1-d2) &lt; 2.5*s</td>
                        <td style="background-color: ' . $backgroundColor . '; font-weight: bold; color: ' . ($absolute ? 'white' : 'black') . ';">
                            ' . ($absolute ? 'Passed' : 'Failed') . '
                        </td>                                
                        <td></td>
                        <td></td>
                        <td>Absolute</td>
                        <td>(d1-d2) &lt; 2.5*s</td>
                        <td style="background-color: ' . $backgroundColor1 . '; font-weight: bold; color: ' . ($absolute1 ? 'white' : 'black') . ';">
                            ' . ($absolute1 ? 'Passed' : 'Failed') . '
                        </td>                             
                    </tr>';                   
                    $info_report .= '<tr style="background-color: ' . $rowColor . '; height:40px;">
                        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                    </tr>
                    </tbody></table><br><br><br>';

                    $i_backsight_e = $calibration_info->i_backsight_e;
                    $i_foresight_f = $calibration_info->i_foresight_f;
                    $i_backsight_g = $calibration_info->i_backsight_g;
                    $i_foresight_h = $calibration_info->i_foresight_h;
                    
                    $ii_backsight_e = $calibration_info->ii_backsight_e;
                    $ii_foresight_f = $calibration_info->ii_foresight_f;
                    $ii_backsight_g = $calibration_info->ii_backsight_g;
                    $ii_foresight_h = $calibration_info->ii_foresight_h;
                    
                    $iii_backsight_e = $calibration_info->iii_backsight_e;
                    $iii_foresight_f = $calibration_info->iii_foresight_f;
                    $iii_backsight_g = $calibration_info->iii_backsight_g;
                    $iii_foresight_h = $calibration_info->iii_foresight_h;
                    
                    $iv_backsight_e = $calibration_info->iv_backsight_e;
                    $iv_foresight_f = $calibration_info->iv_foresight_f;
                    $iv_backsight_g = $calibration_info->iv_backsight_g;
                    $iv_foresight_h = $calibration_info->iv_foresight_h;
                    
                    $v_backsight_e = $calibration_info->v_backsight_e;
                    $v_foresight_f = $calibration_info->v_foresight_f;
                    $v_backsight_g = $calibration_info->v_backsight_g;
                    $v_foresight_h = $calibration_info->v_foresight_h;
                    
                    $vi_backsight_e = $calibration_info->vi_backsight_e;
                    $vi_foresight_f = $calibration_info->vi_foresight_f;
                    $vi_backsight_g = $calibration_info->vi_backsight_g;
                    $vi_foresight_h = $calibration_info->vi_foresight_h;
                    
                    $vii_backsight_e = $calibration_info->vii_backsight_e;
                    $vii_foresight_f = $calibration_info->vii_foresight_f;
                    $vii_backsight_g = $calibration_info->vii_backsight_g;
                    $vii_foresight_h = $calibration_info->vii_foresight_h;
                    
                    $viii_backsight_e = $calibration_info->viii_backsight_e;
                    $viii_foresight_f = $calibration_info->viii_foresight_f;
                    $viii_backsight_g = $calibration_info->viii_backsight_g;
                    $viii_foresight_h = $calibration_info->viii_foresight_h;
                    
                    $ix_backsight_e = $calibration_info->ix_backsight_e;
                    $ix_foresight_f = $calibration_info->ix_foresight_f;
                    $ix_backsight_g = $calibration_info->ix_backsight_g;
                    $ix_foresight_h = $calibration_info->ix_foresight_h;
                    
                    $x_backsight_e = $calibration_info->x_backsight_e;
                    $x_foresight_f = $calibration_info->x_foresight_f;
                    $x_backsight_g = $calibration_info->x_backsight_g;
                    $x_foresight_h = $calibration_info->x_foresight_h;
                    
                    $diff_1 = $i_backsight_e - $i_foresight_f;
                    $diff_2 = $i_backsight_g - $i_foresight_h;

                    $diff_3 = $ii_backsight_e - $ii_foresight_f;
                    $diff_4 = $ii_backsight_g - $ii_foresight_h;

                    $diff_5 = $iii_backsight_e - $iii_foresight_f;
                    $diff_6 = $iii_backsight_g - $iii_foresight_h;

                    $diff_7 = $iv_backsight_e - $iv_foresight_f;
                    $diff_8 = $iv_backsight_g - $iv_foresight_h;

                    $diff_9 = $v_backsight_e - $v_foresight_f;
                    $diff_10 = $v_backsight_g - $v_foresight_h;

                    $diff_11 = $vi_backsight_e - $vi_foresight_f;
                    $diff_12 = $vi_backsight_g - $vi_foresight_h;

                    $diff_13 = $vii_backsight_e - $vii_foresight_f;
                    $diff_14 = $vii_backsight_g - $vii_foresight_h;

                    $diff_15 = $viii_backsight_e - $viii_foresight_f;
                    $diff_16 = $viii_backsight_g - $viii_foresight_h;

                    $diff_17 = $ix_backsight_e - $ix_foresight_f;
                    $diff_18 = $ix_backsight_g - $ix_foresight_h;

                    $diff_19 = $x_backsight_e - $x_foresight_f;
                    $diff_20 = $x_backsight_g - $x_foresight_h;


                    // Calculate the sum of the specified diff variables
                    $sumdiffs3 = $diff_1 + $diff_3 + $diff_5 + $diff_7 + $diff_9 + $diff_11 + $diff_13 + $diff_15 + $diff_17 + $diff_19;
                    $sumdiffs4 = $diff_2 + $diff_4 + $diff_6 + $diff_8 + $diff_10 + $diff_12 + $diff_14 + $diff_16 + $diff_18 + $diff_20;

                    $sum_e = $i_backsight_e + $ii_backsight_e + $iii_backsight_e + $iv_backsight_e + $v_backsight_e +
                            $vi_backsight_e + $vii_backsight_e + $viii_backsight_e + $ix_backsight_e + $x_backsight_e;

                    $sum_f = $i_foresight_f + $ii_foresight_f + $iii_foresight_f + $iv_foresight_f + $v_foresight_f +
                            $vi_foresight_f + $vii_foresight_f + $viii_foresight_f + $ix_foresight_f + $x_foresight_f;

                    $sum_g = $i_backsight_g + $ii_backsight_g + $iii_backsight_g + $iv_backsight_g + $v_backsight_g +
                            $vi_backsight_g + $vii_backsight_g + $viii_backsight_g + $ix_backsight_g + $x_backsight_g;

                    $sum_h = $i_foresight_h + $ii_foresight_h + $iii_foresight_h + $iv_foresight_h + $v_foresight_h +
                            $vi_foresight_h + $vii_foresight_h + $viii_foresight_h + $ix_foresight_h + $x_foresight_h;


                    // Calculate the result of E44 divided by 10
                    $d3result = $sumdiffs3 / 10;
                    $d4result = $sumdiffs4 / 10;

                   // Calculate the result of E45 minus E34
                    $d1elevationresult6 = $d3result - $diff_1;
                    $d2elevationresult6 = $d4result - $diff_2;
                    $d3elevationresult6 = $d3result - $diff_3;
                    $d4elevationresult6 = $d4result - $diff_4;

                    $d1elevationresult7 = $d3result - $diff_5;
                    $d2elevationresult7 = $d4result - $diff_6;
                    $d3elevationresult7 = $d3result - $diff_7;
                    $d4elevationresult7 = $d4result - $diff_8;

                    $d1elevationresult8 = $d3result - $diff_9;
                    $d2elevationresult8 = $d4result - $diff_10;
                    $d3elevationresult8 = $d3result - $diff_11;
                    $d4elevationresult8 = $d4result - $diff_12;

                    $d1elevationresult9 = $d3result - $diff_13;
                    $d2elevationresult9 = $d4result - $diff_14;
                    $d3elevationresult9 = $d3result - $diff_15;
                    $d4elevationresult9 = $d4result - $diff_16;

                    $d1elevationresult10 = $d3result - $diff_17;
                    $d2elevationresult10 = $d4result - $diff_18;
                    $d3elevationresult10 = $d3result - $diff_19;
                    $d4elevationresult10 = $d4result - $diff_20;

                    // Calculate the square of F34
                    $srd1elevationresult6 = $d1elevationresult6 ** 2;
                    $srd2elevationresult6 = $d2elevationresult6 ** 2;
                    $srd3elevationresult6 = $d3elevationresult6 ** 2;
                    $srd4elevationresult6 = $d4elevationresult6 ** 2;

                    // Calculate the square of F34
                    $srd1elevationresult7 = $d1elevationresult7 ** 2;
                    $srd2elevationresult7 = $d2elevationresult7 ** 2;
                    $srd3elevationresult7 = $d3elevationresult7 ** 2;
                    $srd4elevationresult7 = $d4elevationresult7 ** 2;

                    // Calculate the square of F34
                    $srd1elevationresult8 = $d1elevationresult8 ** 2;
                    $srd2elevationresult8 = $d2elevationresult8 ** 2;
                    $srd3elevationresult8 = $d3elevationresult8 ** 2;
                    $srd4elevationresult8 = $d4elevationresult8 ** 2;

                    // Calculate the square of F34
                    $srd1elevationresult9 = $d1elevationresult9 ** 2;
                    $srd2elevationresult9 = $d2elevationresult9 ** 2;
                    $srd3elevationresult9 = $d3elevationresult9 ** 2;
                    $srd4elevationresult9 = $d4elevationresult9 ** 2;

                    // Calculate the square of F34
                    $srd1elevationresult10 = $d1elevationresult10 ** 2;
                    $srd2elevationresult10 = $d2elevationresult10 ** 2;
                    $srd3elevationresult10 = $d3elevationresult10 ** 2;
                    $srd4elevationresult10 = $d4elevationresult10 ** 2;



                    // Calculate the sum of the specified elevation results up to the fifth iteration
                    $sumElevationResults3 = 
                    $d1elevationresult6 + $d3elevationresult6 + 
                    $d1elevationresult7 + $d3elevationresult7 + 
                    $d1elevationresult8 + $d3elevationresult8 + 
                    $d1elevationresult9 + $d3elevationresult9 + 
                    $d1elevationresult10 + $d3elevationresult10;

                    $sumElevationResults4 = 
                    $d2elevationresult6 + $d4elevationresult6 + 
                    $d2elevationresult7 + $d4elevationresult7 + 
                    $d2elevationresult8 + $d4elevationresult8 + 
                    $d2elevationresult9 + $d4elevationresult9 + 
                    $d2elevationresult10 + $d4elevationresult10;

                    // Calculate the sum of the specified elevation results up to the fifth iteration
                    $sumsrdelevationresult3 = 
                    $srd1elevationresult6 + $srd3elevationresult6 + 
                    $srd1elevationresult7+ $srd3elevationresult7 + 
                    $srd1elevationresult8 + $srd3elevationresult8 + 
                    $srd1elevationresult9 + $srd3elevationresult9 + 
                    $srd1elevationresult10 + $srd3elevationresult10;

                    $sumsrdelevationresult4 = 
                    $srd2elevationresult6 + $srd4elevationresult6 + 
                    $srd2elevationresult7 + $srd4elevationresult7 + 
                    $srd2elevationresult8 + $srd4elevationresult8 + 
                    $srd2elevationresult9 + $srd4elevationresult9 + 
                    $srd2elevationresult10 + $srd4elevationresult10;

                    // Calculate the result of G92 divided by 9
                    $result3 = $sumsrdelevationresult3 / 9;
                    $roundedResult3 = round($result3, 4);
                    $result4 = $sumsrdelevationresult4 / 9;
                    $roundedResult4 = round($result4, 4);


                    $standardDeviation2 = $d1result - $d2result;

                    $aceptablesd3 = pow(2.5, $roundedResult3);
                    $aceptablesd3 = round($aceptablesd3, 4);
                    $aceptablesd4 = pow(2.5, $roundedResult4);
                    $aceptablesd4 = round($aceptablesd4, 4);

                    // Perform the comparison
                    $absolute3 = $standardDeviation2 < $aceptablesd3;
                    $absolute4 = $standardDeviation2 < $aceptablesd4;

                    // Determine the background color based on the comparison result
                    $backgroundColor3 = $absolute3 ? 'lightgreen' : '#FFCCCC'; // Green for true, faded red for false
                    $backgroundColor4 = $absolute4 ? 'lightgreen' : '#FFCCCC'; // Green for true, faded red for false

                    $elevationtestline3 = $sum_e - $sum_f;
                    $elevationtestline4 = $sum_g - $sum_h;

                   

                    // Results for each condition
                    $lresult5 = ($sumdiffs3 == $elevationtestline3 && $sumdiffs4 == $elevationtestline4) ? 'Pass' : 'Fail';
                    $lresult6 = ($sumElevationResults3 == 0 && $sumElevationResults4 == 0) ? 'Pass' : 'Fail';
                    $lresult7 = ($standardDeviation2 < $aceptablesd3) ? 'Pass' : 'Fail';
                    $lresult8 = ($standardDeviation2 < $aceptablesd4) ? 'Pass' : 'Fail';

                   // Determine the row color based on results
                    $rowColor2 = ($lresult5 == 'Pass' && $lresult7 == 'Pass'  && $lresult8 == 'Pass') ? 'lightgreen' : 'lightcoral'; // lightcoral for faded red



                    $info_report .= '<b style="font-size:12px; color:maroon;">POST-CALIBRATION REPORT</b><br><br>
                        <table border="0" cellpadding="4">
                             <tr style="color:#424242;">
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_backsight_e . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_foresight_f . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_1 . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d1elevationresult6, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd1elevationresult6, 4) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_backsight_g . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_foresight_h . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_2 . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d2elevationresult6, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd2elevationresult6, 4) . '</td>
                                </tr>
                                <tr style="color:#424242;">
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_backsight_e . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_foresight_f . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_3 . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d3elevationresult6, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd3elevationresult6, 4) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_backsight_g . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_foresight_h . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_4 . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d4elevationresult6, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd4elevationresult6, 4) . '</td>
                                </tr>
                                <tr style="color:#424242;">
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">3</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iii_backsight_e . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iii_foresight_f . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_5 . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d1elevationresult7, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd1elevationresult7, 4) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iii_backsight_g . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iii_foresight_h . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_6 . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d2elevationresult7, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd2elevationresult7, 4) . '</td>
                                </tr>

                                <tr style="color:#424242;">
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">4</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_backsight_e . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_foresight_f . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_7 . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d3elevationresult7, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd3elevationresult7, 4) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_backsight_g . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_foresight_h . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_8 . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d4elevationresult7, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd4elevationresult7, 4) . '</td>
                                </tr>
                                 <tr style="color:#424242;">
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">5</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $v_backsight_e . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $v_foresight_f . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_9 . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d1elevationresult8, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd1elevationresult8, 4) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $v_backsight_g . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $v_foresight_h . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_10 . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d2elevationresult8, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd2elevationresult8, 4) . '</td>
                                </tr>

                                <tr style="color:#424242;">
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">6</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_backsight_e . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_foresight_f . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_11 . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d3elevationresult8, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd3elevationresult8, 4) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_backsight_g . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_foresight_h . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_12 . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d4elevationresult8, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd4elevationresult8, 4) . '</td>
                                </tr>

                                <tr style="color:#424242;">
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">7</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vii_backsight_e . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vii_foresight_f . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_13 . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d1elevationresult9, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd1elevationresult9, 4) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vii_backsight_g . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vii_foresight_h . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_14 . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d2elevationresult9, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd2elevationresult9, 4) . '</td>
                                </tr>

                                <tr style="color:#424242;">
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">8</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_backsight_e . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_foresight_f . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_15 . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d3elevationresult9, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd3elevationresult9, 4) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_backsight_g . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_foresight_h . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_16 . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d4elevationresult9, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd4elevationresult9, 4) . '</td>
                                </tr>
                                <tr style="color:#424242;">
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">9</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ix_backsight_e . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ix_foresight_f . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_17 . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d1elevationresult10, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd1elevationresult10, 4) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ix_backsight_g . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ix_foresight_h . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_18 . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d2elevationresult10, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd2elevationresult10, 4) . '</td>
                                </tr>
                                     <tr style="color:#424242;">
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">10</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_backsight_e . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_foresight_f . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_19 . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d3elevationresult10, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd3elevationresult10, 4) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_backsight_g . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_foresight_h . '</td>
                                    <td class="total" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $diff_20 . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($d4elevationresult10, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($srd4elevationresult10, 4) . '</td>
                                </tr>';
                    $info_report .= '<tr style="background-color: lightgrey;">
                        <td><b>Sum</b></td>
                        <td>' . $sum_e . '</td>
                        <td>' . $sum_f . '</td>
                        <td>' . $sumdiffs3 . '</td>
                        <td>' . round($sumElevationResults3, 4) . '</td>
                        <td>' . round($sumsrdelevationresult3, 4) . '</td>
                        <td>' . $sum_g . '</td>
                        <td>' . $sum_h . '</td>
                        <td>' . $sumdiff4 . '</td>
                        <td>' . round($sumElevationResults4, 4) . '</td>
                        <td>' . round($sumsrdelevationresult4, 4) . '</td>
                    </tr>';
                    
                    $info_report .= '<tr style="background-color: lightgrey;">
                        <td></td>
                        <td><b>Mean</b></td>
                        <td>d1</td>
                        <td>' . $d3result . '</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>d2</td>
                        <td>' . $d4result . '</td>
                        <td></td>
                        <td></td>
                    </tr>';
                    
                    $info_report .= '<tr style="background-color: lightgrey;">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Standard deviation (mm)</td>
                        <td>s</td>
                        <td>' . round($roundedResult3, 2) . '</td>                               
                        <td></td>
                        <td></td>
                        <td>Standard deviation (mm)</td>
                        <td>s</td>
                        <td>' . round($roundedResult4, 2) . '</td>                               
                    </tr>';
                    
                    $info_report .= '<tr style="background-color: lightgrey;">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Meaned Elevation differences (mm)</td>
                        <td>d1-d2</td>
                        <td>' . number_format($standardDeviation2, 2, '.', '') . '</td>
                        <td></td>
                        <td></td>
                        <td>Meaned Elevation differences (mm)</td>
                        <td>d1-d2</td>
                        <td>' . number_format($standardDeviation2, 2, '.', '') . '</td>
                    </tr>';
                    
                    $info_report .= '<tr style="background-color: lightgrey;">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Acceptable Standard Deviation (mm)</td>
                        <td>2.5 x s</td>
                        <td>' . number_format($aceptablesd3, 2, '.', '') . '</td>
                        <td></td>
                        <td></td>
                        <td>Acceptable Standard Deviation (mm)</td>
                        <td>2.5 x s</td>
                        <td>' . number_format($aceptablesd4, 2, '.', '') . '</td>
                    </tr>';                   
                    $info_report .= '<tr style="background-color: lightgrey;">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Absolute</td>
                        <td>(d1-d2) &lt; 2.5*s</td>
                        <td style="background-color: ' . $backgroundColor3 . '; font-weight: bold; color: ' . ($absolute3 ? 'white' : 'black') . ';">
                            ' . ($absolute3 ? 'Passed' : 'Failed') . '
                        </td>                                
                        <td></td>
                        <td></td>
                        <td>Absolute</td>
                        <td>(d1-d2) &lt; 2.5*s</td>
                        <td style="background-color: ' . $backgroundColor4 . '; font-weight: bold; color: ' . ($absolute4 ? 'white' : 'black') . ';">
                            ' . ($absolute4 ? 'Passed' : 'Failed') . '
                        </td>                             
                    </tr>';                   
                    $info_report .= '<tr style="background-color: ' . $rowColor2 . '; height:40px;">
                        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                    </tr>                                              
                </tbody>
                </table>';
                
} else if ($service_request->item_type == 'Total Station') {

                            //  Assuming the form is submitted via POST
                            $t_v_a_1 = $calibration_info->t_v_a_1;
                            $t_v_a_2 = $calibration_info->t_v_a_2;
                            $t_v_a_3 = $calibration_info->t_v_a_3;
                            $t_v_a_4 = $calibration_info->t_v_a_4;
                            $t_v_a_5 = $calibration_info->t_v_a_5;
                            $t_v_a_6 = $calibration_info->t_v_a_6;
                            $t_v_a_7 = $calibration_info->t_v_a_7;
                            $t_v_a_8 = $calibration_info->t_v_a_8;
                            $t_v_a_9 = $calibration_info->t_v_a_9;
                            $t_v_a_10 = $calibration_info->t_v_a_10;
                            $t_v_a_11 = $calibration_info->t_v_a_11;
                            $t_v_a_12 = $calibration_info->t_v_a_12;
                            $t_v_a_13 = $calibration_info->t_v_a_13;
                            $t_v_a_14 = $calibration_info->t_v_a_14;
                            $t_v_a_15 = $calibration_info->t_v_a_15;
                            $t_v_a_16 = $calibration_info->t_v_a_16;

                            $i_edm_a_1 = $calibration_info->i_edm_a_1;
                            $i_edm_a_2 = $calibration_info->i_edm_a_2;
                            $i_edm_a_3 = $calibration_info->i_edm_a_3;
                            $i_edm_a_4 = $calibration_info->i_edm_a_4;
                            $i_edm_a_5 = $calibration_info->i_edm_a_5;
                            $i_edm_a_6 = $calibration_info->i_edm_a_6;
                            $i_edm_a_7 = $calibration_info->i_edm_a_7;
                            $i_edm_a_8 = $calibration_info->i_edm_a_8;
                            $i_edm_a_9 = $calibration_info->i_edm_a_9;
                            $i_edm_a_10 = $calibration_info->i_edm_a_10;
                            $i_edm_a_11 = $calibration_info->i_edm_a_11;
                            $i_edm_a_12 = $calibration_info->i_edm_a_12;
                            $i_edm_a_13 = $calibration_info->i_edm_a_13;
                            $i_edm_a_14 = $calibration_info->i_edm_a_14;
                            $i_edm_a_15 = $calibration_info->i_edm_a_15;
                            $i_edm_a_16 = $calibration_info->i_edm_a_16;
                            $i_edm_a_17 = $calibration_info->i_edm_a_17;
                            $i_edm_a_18 = $calibration_info->i_edm_a_18;
                            $i_edm_a_19 = $calibration_info->i_edm_a_19;
                            $i_edm_a_20 = $calibration_info->i_edm_a_20;
                            $i_edm_a_21 = $calibration_info->i_edm_a_21;
                            $i_edm_a_22 = $calibration_info->i_edm_a_22;
                            $i_edm_a_23 = $calibration_info->i_edm_a_23;
                            $i_edm_a_24 = $calibration_info->i_edm_a_24;
                            $i_edm_a_25 = $calibration_info->i_edm_a_25;
                            $i_edm_a_26 = $calibration_info->i_edm_a_26;
                            $i_edm_a_27 = $calibration_info->i_edm_a_27;
                            $i_edm_a_28 = $calibration_info->i_edm_a_28;
                            $i_edm_a_29 = $calibration_info->i_edm_a_29;
                            $i_edm_a_30 = $calibration_info->i_edm_a_30;
                            $i_edm_a_31 = $calibration_info->i_edm_a_31;
                            $i_edm_a_32 = $calibration_info->i_edm_a_32;
                            $i_edm_a_33 = $calibration_info->i_edm_a_33;
                            $i_edm_a_34 = $calibration_info->i_edm_a_34;
                            $i_edm_a_35 = $calibration_info->i_edm_a_35;
                            $i_edm_a_36 = $calibration_info->i_edm_a_36;
                            $i_edm_a_37 = $calibration_info->i_edm_a_37;
                            $i_edm_a_38 = $calibration_info->i_edm_a_38;
                            $i_edm_a_39 = $calibration_info->i_edm_a_39;
                            $i_edm_a_40 = $calibration_info->i_edm_a_40;
                            $i_edm_a_41 = $calibration_info->i_edm_a_41;
                            $i_edm_a_42 = $calibration_info->i_edm_a_42;
                            $i_edm_a_43 = $calibration_info->i_edm_a_43;
                            $i_edm_a_44 = $calibration_info->i_edm_a_44;
                            $i_edm_a_45 = $calibration_info->i_edm_a_45;
                            $i_edm_a_46 = $calibration_info->i_edm_a_46;
                            $i_edm_a_47 = $calibration_info->i_edm_a_47;
                            $i_edm_a_48 = $calibration_info->i_edm_a_48;





                          $dresult1 = sqrt(pow(($i_edm_a_1 - $i_edm_a_2), 2) + pow(($i_edm_a_3 - $i_edm_a_4), 2));
                          $dresult1 = number_format($dresult1, 4);
                          $eresult1 = $i_edm_a_5 - $i_edm_a_6;
                      
                          $dresult2 = sqrt(pow(($i_edm_a_7 - $i_edm_a_8), 2) + pow(($i_edm_a_9 - $i_edm_a_10), 2));
                          $dresult2 = number_format($dresult2, 4);
                          $eresult2 = $i_edm_a_11 - $i_edm_a_12;
                      
                          $dresult3 = sqrt(pow(($i_edm_a_13 - $i_edm_a_14), 2) + pow(($i_edm_a_15 - $i_edm_a_16), 2));
                          $dresult3 = number_format($dresult3, 4);
                          $eresult3 = $i_edm_a_17 - $i_edm_a_18;
                      
                          $dresult4 = sqrt(pow(($i_edm_a_19 - $i_edm_a_20), 2) + pow(($i_edm_a_21 - $i_edm_a_22), 2));
                          $dresult4 = number_format($dresult4, 4);
                          $eresult4 = $i_edm_a_23 - $i_edm_a_24;
                      
                          $dresult5 = sqrt(pow(($i_edm_a_25 - $i_edm_a_26), 2) + pow(($i_edm_a_27 - $i_edm_a_28), 2));
                          $dresult5 = number_format($dresult5, 4);
                          $eresult5 = $i_edm_a_29 - $i_edm_a_30;
                      
                          $dresult6 = sqrt(pow(($i_edm_a_31 - $i_edm_a_32), 2) + pow(($i_edm_a_33 - $i_edm_a_34), 2));
                          $dresult6 = number_format($dresult6, 4);
                          $eresult6 = $i_edm_a_35 - $i_edm_a_36;
                      
                          $dresult7 = sqrt(pow(($i_edm_a_37 - $i_edm_a_38), 2) + pow(($i_edm_a_39 - $i_edm_a_40), 2));
                          $dresult7 = number_format($dresult7, 4);
                          $eresult7 = $i_edm_a_41 - $i_edm_a_42;
                      
                          $dresult8 = sqrt(pow(($i_edm_a_43 - $i_edm_a_44), 2) + pow(($i_edm_a_45 - $i_edm_a_46), 2));
                          $dresult8 = number_format($dresult8, 4);
                          $eresult8 = $i_edm_a_47 - $i_edm_a_48;

                          $mean_total = ($dresult1 + $dresult2 + $dresult3 + $dresult4 + $dresult5 + $dresult6 + $dresult7 + $dresult8) / 8;
                          $elevation_total = ($eresult1 + $eresult2 + $eresult3 + $eresult4 + $eresult5 + $eresult6 + $eresult7 + $eresult8) / 8;

                          $mean_distance1 = ($dresult1 - $mean_total) * 1000;
                          $mean_distance2 = ($dresult2 - $mean_total) * 1000;
                          $mean_distance3 = ($dresult3 - $mean_total) * 1000;
                          $mean_distance4 = ($dresult4 - $mean_total) * 1000;
                          $mean_distance5 = ($dresult5 - $mean_total) * 1000;
                          $mean_distance6 = ($dresult6 - $mean_total) * 1000;
                          $mean_distance7 = ($dresult7 - $mean_total) * 1000;
                          $mean_distance8 = ($dresult8 - $mean_total) * 1000;

                          $elevation_distance1 = ($eresult1 - $elevation_total) * 1000;
                          $elevation_distance2 = ($eresult2 - $elevation_total) * 1000;
                          $elevation_distance3 = ($eresult3 - $elevation_total) * 1000;
                          $elevation_distance4 = ($eresult4 - $elevation_total) * 1000;
                          $elevation_distance5 = ($eresult5 - $elevation_total) * 1000;
                          $elevation_distance6 = ($eresult6 - $elevation_total) * 1000;
                          $elevation_distance7 = ($eresult7 - $elevation_total) * 1000;
                          $elevation_distance8 = ($eresult8 - $elevation_total) * 1000;

                          $standard_error1 = -$mean_distance1 / 2;
                          $standard_error2 = -$elevation_distance6 / 2;

                          $smdistance1 = pow($mean_distance1, 2);
                          $smdistance2 = pow($mean_distance2, 2);
                          $smdistance3 = pow($mean_distance3, 2);
                          $smdistance4 = pow($mean_distance4, 2);
                          $smdistance5 = pow($mean_distance5, 2);
                          $smdistance6 = pow($mean_distance6, 2);
                          $smdistance7 = pow($mean_distance7, 2);
                          $smdistance8 = pow($mean_distance8, 2);

                          $total_smdistance = $smdistance1 + $smdistance2 + $smdistance3 + $smdistance4 + $smdistance5 + $smdistance6 + $smdistance7 + $smdistance8;

                          $smelevation1 = pow($elevation_distance1, 2);
                          $smelevation2 = pow($elevation_distance2, 2);
                          $smelevation3 = pow($elevation_distance3, 2);
                          $smelevation4 = pow($elevation_distance4, 2);
                          $smelevation5 = pow($elevation_distance5, 2);
                          $smelevation6 = pow($elevation_distance6, 2);
                          $smelevation7 = pow($elevation_distance7, 2);
                          $smelevation8 = pow($elevation_distance8, 2);

                          $total_smelevation = $smelevation1 + $smelevation2 + $smelevation3 + $smelevation4 + $smelevation5 + $smelevation6 + $smelevation7 + $smelevation8;

                          $dfresult1 = $total_smdistance / 15;
                          $dfresult2 = $total_smelevation / 15;
                $info_report = '
                <table border="0" cellpadding="5">

                        <thead>
                        <tr style="color:#424242;">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; background-color: lightgrey; border:1px solid #000; text-align:left; color:maroon;" colspan="6"><b>INSTRUMENT INFORMATION</b></td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr class="form-inline">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT MAKE</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $t_v_a_1 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT MODEL</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $t_v_a_2 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT SERIAL NO.</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $t_v_a_3 . '</td>
                            
                        </tr>
                        <tr class="form-inline">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT CONDITION</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $t_v_a_4 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">BASELINE TEST DISTANCE (M)</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $t_v_a_5 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">WEATHER CONDITION</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $t_v_a_6 . '</td>

                            </tr>
                        <tr class="form-inline">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">TEMPERATURE (°C)</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $t_v_a_7 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">AIR PRESSURE (hPa)</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $t_v_a_8 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">MANUFACTURER ANGLE ACCURACY (00)</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $t_v_a_9 . '</td>

                            </tr>
                        <tr class="form-inline">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">MANUFACTURER EDM ACCURACY (MM)</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $t_v_a_10 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">TARGET PRISM CONSTANT (MM)</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $t_v_a_11 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">TARGET PRISM POLE HEIGHT (M)</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $t_v_a_12 . '</td>
                            </tr>
                        <tr class="form-inline">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT PRISM CONSTANT (MM)</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $t_v_a_13 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT HEIGHT (M)</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $t_v_a_14 . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">TARGET MAKE</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $t_v_a_15 . '</td>

                        </tr>
                        <tr class="form-inline">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">TARGET MODEL</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $t_v_a_16 . '</td>

                        </tr>

                        </tbody>
                </table>';

                        $i_h_a = $calibration_info->i_h_a;
                        $ii_h_a = $calibration_info->ii_h_a;
                        $sum_h_a = $i_h_a+$ii_h_a;
                        $d_error_h_a = (dms2dec(180,0,0)-$sum_h_a);

                        $i_h_b = $calibration_info->i_h_b;
                        $ii_h_b = $calibration_info->ii_h_b;                                
                        $sum_h_b = $i_h_b+$ii_h_b;
                        $d_error_h_b = (dms2dec(180,0,0)-$sum_h_b);

                        $i_v_a = $calibration_info->i_v_a;
                        $ii_v_a = $calibration_info->ii_v_a;                                
                        $sum_v_a = $i_v_a+$ii_v_a;
                        $d_error_v_a = (dms2dec(360,0,0)-$sum_v_a);

                        $i_v_b = $calibration_info->i_v_b;
                        $ii_v_b = $calibration_info->ii_v_b;
                        $sum_v_b = $i_v_b+$ii_v_b;
                        $d_error_v_b = (dms2dec(360,0,0)-$sum_v_b);

                        $i_edm_a_1 = number_format(round($calibration_info->i_edm_a_1, 3), 3);
                        $i_edm_a_2 = number_format(round($calibration_info->i_edm_a_2, 3), 3);
                        $i_edm_a_3 = number_format(round($calibration_info->i_edm_a_3, 3), 3);
                        $f_i_edm_a = ($i_edm_a_1 + $i_edm_a_2 + $i_edm_a_3)/3;

                        $i_edm_b_3 = number_format(round($calibration_info->i_edm_b_3, 3), 3);
                        $i_edm_b_4 = number_format(round($calibration_info->i_edm_b_4, 3), 3);
                        $i_edm_b_3 = number_format(round($calibration_info->i_edm_b_3, 3), 3);
                        $f_i_edm_b = ($i_edm_b_1 + $i_edm_b_2 + $i_edm_b_3)/3;

                        $ii_edm_a_1 = number_format(round($calibration_info->ii_edm_a_1, 3), 3);
                        $ii_edm_a_2 = number_format(round($calibration_info->ii_edm_a_2, 3), 3);
                        $ii_edm_a_3 = number_format(round($calibration_info->ii_edm_a_3, 3), 3);
                        $f_ii_edm_a = ($ii_edm_a_1 + $ii_edm_a_2 + $ii_edm_a_3)/3;

                        $ii_edm_b_1 = number_format(round($calibration_info->ii_edm_b_1, 3), 3);
                        $ii_edm_b_2 = number_format(round($calibration_info->ii_edm_b_2, 3), 3);
                        $ii_edm_b_3 = number_format(round($calibration_info->ii_edm_b_3, 3), 3);
                        $f_ii_edm_b = ($ii_edm_b_1 + $ii_edm_b_2 + $ii_edm_b_3)/3;

                        $f_i_ii_edm_a = ($f_i_edm_a + $f_ii_edm_a)/2;
                        $f_i_ii_edm_b = ($f_i_edm_b + $f_ii_edm_b)/2;

                          $i_edm_a_1 = $calibration_info->i_edm_a_1;
                          $i_edm_a_2 = $calibration_info->i_edm_a_2;
                          $i_edm_a_3 = $calibration_info->i_edm_a_3;
                          $i_edm_a_4 = $calibration_info->i_edm_a_4;
                          $i_edm_a_5 = $calibration_info->i_edm_a_5;
                          $i_edm_a_6 = $calibration_info->i_edm_a_6;
                          $i_edm_a_7 = $calibration_info->i_edm_a_7;
                          $i_edm_a_8 = $calibration_info->i_edm_a_8;
                          $i_edm_a_9 = $calibration_info->i_edm_a_9;
                          $i_edm_a_10 = $calibration_info->i_edm_a_10;
                          $i_edm_a_11 = $calibration_info->i_edm_a_11;
                          $i_edm_a_12 = $calibration_info->i_edm_a_12;
                          $i_edm_a_13 = $calibration_info->i_edm_a_13;
                          $i_edm_a_14 = $calibration_info->i_edm_a_14;
                          $i_edm_a_15 = $calibration_info->i_edm_a_15;
                          $i_edm_a_16 = $calibration_info->i_edm_a_16;
                          $i_edm_a_17 = $calibration_info->i_edm_a_17;
                          $i_edm_a_18 = $calibration_info->i_edm_a_18;
                          $i_edm_a_19 = $calibration_info->i_edm_a_19;
                          $i_edm_a_20 = $calibration_info->i_edm_a_20;
                          $i_edm_a_21 = $calibration_info->i_edm_a_21;
                          $i_edm_a_22 = $calibration_info->i_edm_a_22;
                          $i_edm_a_23 = $calibration_info->i_edm_a_23;
                          $i_edm_a_24 = $calibration_info->i_edm_a_24;
                          $i_edm_a_25 = $calibration_info->i_edm_a_25;
                          $i_edm_a_26 = $calibration_info->i_edm_a_26;
                          $i_edm_a_27 = $calibration_info->i_edm_a_27;
                          $i_edm_a_28 = $calibration_info->i_edm_a_28;
                          $i_edm_a_29 = $calibration_info->i_edm_a_29;
                          $i_edm_a_30 = $calibration_info->i_edm_a_30;
                          $i_edm_a_31 = $calibration_info->i_edm_a_31;
                          $i_edm_a_32 = $calibration_info->i_edm_a_32;
                          $i_edm_a_33 = $calibration_info->i_edm_a_33;
                          $i_edm_a_34 = $calibration_info->i_edm_a_34;
                          $i_edm_a_35 = $calibration_info->i_edm_a_35;
                          $i_edm_a_36 = $calibration_info->i_edm_a_36;
                          $i_edm_a_37 = $calibration_info->i_edm_a_37;
                          $i_edm_a_38 = $calibration_info->i_edm_a_38;
                          $i_edm_a_39 = $calibration_info->i_edm_a_39;
                          $i_edm_a_40 = $calibration_info->i_edm_a_40;
                          $i_edm_a_41 = $calibration_info->i_edm_a_41;
                          $i_edm_a_42 = $calibration_info->i_edm_a_42;
                          $i_edm_a_43 = $calibration_info->i_edm_a_43;
                          $i_edm_a_44 = $calibration_info->i_edm_a_44;
                          $i_edm_a_45 = $calibration_info->i_edm_a_45;
                          $i_edm_a_46 = $calibration_info->i_edm_a_46;
                          $i_edm_a_47 = $calibration_info->i_edm_a_47;
                          $i_edm_a_48 = $calibration_info->i_edm_a_48;

                          $t_v_a_10 = $calibration_info->t_v_a_10;




                          $dresult1 = sqrt(pow(($i_edm_a_1 - $i_edm_a_2), 2) + pow(($i_edm_a_3 - $i_edm_a_4), 2));
                          $dresult1 = number_format($dresult1, 4);
                          $eresult1 = $i_edm_a_5 - $i_edm_a_6;
                      
                          $dresult2 = sqrt(pow(($i_edm_a_7 - $i_edm_a_8), 2) + pow(($i_edm_a_9 - $i_edm_a_10), 2));
                          $dresult2 = number_format($dresult2, 4);
                          $eresult2 = $i_edm_a_11 - $i_edm_a_12;
                      
                          $dresult3 = sqrt(pow(($i_edm_a_13 - $i_edm_a_14), 2) + pow(($i_edm_a_15 - $i_edm_a_16), 2));
                          $dresult3 = number_format($dresult3, 4);
                          $eresult3 = $i_edm_a_17 - $i_edm_a_18;
                      
                          $dresult4 = sqrt(pow(($i_edm_a_19 - $i_edm_a_20), 2) + pow(($i_edm_a_21 - $i_edm_a_22), 2));
                          $dresult4 = number_format($dresult4, 4);
                          $eresult4 = $i_edm_a_23 - $i_edm_a_24;
                      
                          $dresult5 = sqrt(pow(($i_edm_a_25 - $i_edm_a_26), 2) + pow(($i_edm_a_27 - $i_edm_a_28), 2));
                          $dresult5 = number_format($dresult5, 4);
                          $eresult5 = $i_edm_a_29 - $i_edm_a_30;
                      
                          $dresult6 = sqrt(pow(($i_edm_a_31 - $i_edm_a_32), 2) + pow(($i_edm_a_33 - $i_edm_a_34), 2));
                          $dresult6 = number_format($dresult6, 4);
                          $eresult6 = $i_edm_a_35 - $i_edm_a_36;
                      
                          $dresult7 = sqrt(pow(($i_edm_a_37 - $i_edm_a_38), 2) + pow(($i_edm_a_39 - $i_edm_a_40), 2));
                          $dresult7 = number_format($dresult7, 4);
                          $eresult7 = $i_edm_a_41 - $i_edm_a_42;
                      
                          $dresult8 = sqrt(pow(($i_edm_a_43 - $i_edm_a_44), 2) + pow(($i_edm_a_45 - $i_edm_a_46), 2));
                          $dresult8 = number_format($dresult8, 4);
                          $eresult8 = $i_edm_a_47 - $i_edm_a_48;

                          $mean_total = ($dresult1 + $dresult2 + $dresult3 + $dresult4 + $dresult5 + $dresult6 + $dresult7 + $dresult8) / 8;
                          $elevation_total = ($eresult1 + $eresult2 + $eresult3 + $eresult4 + $eresult5 + $eresult6 + $eresult7 + $eresult8) / 8;

                          $mean_distance1 = ($dresult1 - $mean_total) * 1000;
                          $mean_distance2 = ($dresult2 - $mean_total) * 1000;
                          $mean_distance3 = ($dresult3 - $mean_total) * 1000;
                          $mean_distance4 = ($dresult4 - $mean_total) * 1000;
                          $mean_distance5 = ($dresult5 - $mean_total) * 1000;
                          $mean_distance6 = ($dresult6 - $mean_total) * 1000;
                          $mean_distance7 = ($dresult7 - $mean_total) * 1000;
                          $mean_distance8 = ($dresult8 - $mean_total) * 1000;

                          $elevation_distance1 = ($eresult1 - $elevation_total) * 1000;
                          $elevation_distance2 = ($eresult2 - $elevation_total) * 1000;
                          $elevation_distance3 = ($eresult3 - $elevation_total) * 1000;
                          $elevation_distance4 = ($eresult4 - $elevation_total) * 1000;
                          $elevation_distance5 = ($eresult5 - $elevation_total) * 1000;
                          $elevation_distance6 = ($eresult6 - $elevation_total) * 1000;
                          $elevation_distance7 = ($eresult7 - $elevation_total) * 1000;
                          $elevation_distance8 = ($eresult8 - $elevation_total) * 1000;

                          $standard_error1 = -$mean_distance1 / 2;
                          $standard_error2 = -$elevation_distance6 / 2;

                          $smdistance1 = pow($mean_distance1, 2);
                          $smdistance2 = pow($mean_distance2, 2);
                          $smdistance3 = pow($mean_distance3, 2);
                          $smdistance4 = pow($mean_distance4, 2);
                          $smdistance5 = pow($mean_distance5, 2);
                          $smdistance6 = pow($mean_distance6, 2);
                          $smdistance7 = pow($mean_distance7, 2);
                          $smdistance8 = pow($mean_distance8, 2);

                          $total_smdistance = $smdistance1 + $smdistance2 + $smdistance3 + $smdistance4 + $smdistance5 + $smdistance6 + $smdistance7 + $smdistance8;

                          $smelevation1 = pow($elevation_distance1, 2);
                          $smelevation2 = pow($elevation_distance2, 2);
                          $smelevation3 = pow($elevation_distance3, 2);
                          $smelevation4 = pow($elevation_distance4, 2);
                          $smelevation5 = pow($elevation_distance5, 2);
                          $smelevation6 = pow($elevation_distance6, 2);
                          $smelevation7 = pow($elevation_distance7, 2);
                          $smelevation8 = pow($elevation_distance8, 2);

                          $total_smelevation = $smelevation1 + $smelevation2 + $smelevation3 + $smelevation4 + $smelevation5 + $smelevation6 + $smelevation7 + $smelevation8;

                          $dfresult1 = $total_smdistance / 15;
                          $dfresult2 = $total_smelevation / 15;

                        

                $info_report .= '<table border="1" cellpadding="4">
                        <tr style="color:#424242;">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; background-color: lightgrey; border:1px solid #000; text-align:left; color:maroon;" colspan="6"><b>PRE-CALIBRATION CHECKS</b></td>
                        </tr>
                            <tr style="color:#424242;">
                                
                                <td style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="3"><b>HORIZONTAL CIRCLE INDEX ERROR</b></td>
                                <td style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="4"><b>VERTICAL CIRCLE INDEX ERROR</b></td>
                                
                            </tr>
                            <thead>
                                <tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . (get_option('pdf_font_size') + 4) . 'px;">
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="1">FACE</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="1">START A</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="1">END B</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">START A</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">END B</th>
                                </tr>
                            </thead>
                            <tbody>';

                            $info_report .= '
                                <tr style="color:#424242;">
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>I</b></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($calibration_info->i_h_a) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($calibration_info->i_h_b) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($calibration_info->i_v_a) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($calibration_info->i_v_b) . '</td>
                                    
                                </tr>
                                <tr style="color:#424242;">
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>II</b></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($calibration_info->ii_h_a) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($calibration_info->ii_h_b) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($calibration_info->ii_v_a) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($calibration_info->ii_v_b) . '</td>
                                
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-center total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>Half Circle is 180° 00\' 00"</b></td>
                                    <td colspan="4" class="text-center" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>Half Circle is 360° 00\' 00"</b></td>
                                </tr>
                                <tr style="color:#424242;">
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>SUM(I+II)</b></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($sum_h_a) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($sum_h_b) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($sum_v_a) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($sum_v_b) . '</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td colspan="2" class="text-center total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>(180° 00\' 00" - SUM(A+B))/2</b></td>
                                    <td colspan="2" class="text-center" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>(360° 00\' 00" - SUM(A+B))/2</b></td>
                                </tr>
                                <tr style="color:#424242;">
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>DOUBLE ERROR</b></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($d_error_h_a) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($d_error_h_b) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($d_error_v_a) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($d_error_v_b) . '</td>
                                </tr>
                                </tbody>
                        </table>';

                $info_report .= '<table border="1" cellpadding="4">
                            <thead>
                                <tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . (get_option('pdf_font_size') + 4) . 'px;">
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Residuals from Mean(mm)</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Squared meaned residuals(mm)</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></th>

                                </tr>
                                <tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . (get_option('pdf_font_size') + 4) . 'px;">
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">FACE</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">X</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Y</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Z</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Distance</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Elevation</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Distance</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Elevation</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Distance</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Elevation</th>

                                </tr>
                            </thead>
                            <tbody>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>I</b></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_1 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_3 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_5 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult1 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult1 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($mean_distance1, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($elevation_distance1, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smdistance1, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smelevation1, 4) . '</td>

                            </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b></b></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_2 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_4 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_6 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>

                                </tr>

                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>II</b></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_7 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_9 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_11 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult2 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult2 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($mean_distance2, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($elevation_distance2, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smdistance2, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smelevation2, 4) . '</td>

                                </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_8 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_10 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_12 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>

                                </tr>

                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>I</b></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_13 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_15 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_17 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult3 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult3 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($mean_distance3, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($elevation_distance3, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smdistance3, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smelevation3, 4) . '</td>

                                </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_14 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_16 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_18 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>

                            </tr>

                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>II</b></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_19 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_21 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_23 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult4 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult4 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($mean_distance4, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($elevation_distance4, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smdistance4, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smelevation4, 4) . '</td>

                                </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_20 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_22 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_24 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>

                                </tr>

                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>I</b></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_25 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_27 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_29 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult5 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult5 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($mean_distance5, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($elevation_distance5, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smdistance5, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smelevation5, 4) . '</td>

                                </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_26 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_28 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_30 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>

                                </tr>

                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>II</b></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_31 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_33 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_35 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult6 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult6 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($mean_distance6, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($elevation_distance6, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smdistance6, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smelevation6, 4) . '</td>

                                </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_32 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_34 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_36 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                </tr>

                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>I</b></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_37 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_39 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_41 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult7 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult7 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($mean_distance7, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($elevation_distance7, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smdistance7, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smelevation7, 4) . '</td>
                                </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_38 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_40 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_42 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                </tr>

                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>II</b></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_43 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_45 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_47 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult8 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult8 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($mean_distance8, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($elevation_distance8, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smdistance8, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smelevation8, 4) . '</td>

                                </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_44 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_46 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_48 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                </tr>
                                <tr style="color:#424242; background-color: lightgrey;">
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>Mean</b></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($mean_total, 4) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($elevation_total, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                </tr>
                                <tr style="color:#424242; background-color: lightgrey;">
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>Standard Errors (Max & Absolute)/2</b></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($standard_error1, 4) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($standard_error2, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>

                                </tr>
                                <tr style="color:#424242; background-color: lightgrey;">
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>Degree of Freedom</b></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($dfresult1, 4) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($dfresult2, 4) . '</td>
                                </tr>
                                <tr style="color:#424242;">
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" colspan="2" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; ' . ($standard_error1 < $t_v_a_10 && $standard_error2 < $t_v_a_10 && $dfresult1 < $t_v_a_10 && $dfresult2 < $t_v_a_10 ? 'background-color: lightgreen; color: white;' : 'background-color: red; color: white;') . '">
                                        ' . ($standard_error1 < $t_v_a_10 && $standard_error2 < $t_v_a_10 && $dfresult1 < $t_v_a_10 && $dfresult2 < $t_v_a_10 ? 'PASSED' : 'FAILED') . '
                                    </td>
                                </tr>
                                </tbody>
                        </table><br>';



                                  $i_h_a = $calibration_info->i_h_a;
                                  $ii_h_a = $calibration_info->ii_h_a;
                                  $sum_h_a = $i_h_a+$ii_h_a;
                                  $d_error_h_a = (dms2dec(180,0,0)-$sum_h_a);

                                  $i_h_b = $calibration_info->i_h_b;
                                  $ii_h_b = $calibration_info->ii_h_b;                                
                                  $sum_h_b = $i_h_b+$ii_h_b;
                                  $d_error_h_b = (dms2dec(180,0,0)-$sum_h_b);

                                  $i_v_a = $calibration_info->i_v_a;
                                  $ii_v_a = $calibration_info->ii_v_a;                                
                                  $sum_v_a = $i_v_a+$ii_v_a;
                                  $d_error_v_a = (dms2dec(360,0,0)-$sum_v_a);

                                  $i_v_b = $calibration_info->i_v_b;
                                  $ii_v_b = $calibration_info->ii_v_b;
                                  $sum_v_b = $i_v_b+$ii_v_b;
                                  $d_error_v_b = (dms2dec(360,0,0)-$sum_v_b);

                                  $i_edm_a_1 = number_format(round($calibration_info->i_edm_a_1, 3), 3);
                                  $i_edm_a_2 = number_format(round($calibration_info->i_edm_a_2, 3), 3);
                                  $i_edm_a_3 = number_format(round($calibration_info->i_edm_a_3, 3), 3);
                                  $f_i_edm_a = ($i_edm_a_1 + $i_edm_a_2 + $i_edm_a_3)/3;

                                  $i_edm_b_1 = number_format(round($calibration_info->i_edm_b_1, 3), 3);
                                  $i_edm_b_2 = number_format(round($calibration_info->i_edm_b_2, 3), 3);
                                  $i_edm_b_3 = number_format(round($calibration_info->i_edm_b_3, 3), 3);
                                  $f_i_edm_b = ($i_edm_b_1 + $i_edm_b_2 + $i_edm_b_3)/3;

                                  $ii_edm_a_1 = number_format(round($calibration_info->ii_edm_a_1, 3), 3);
                                  $ii_edm_a_2 = number_format(round($calibration_info->ii_edm_a_2, 3), 3);
                                  $ii_edm_a_3 = number_format(round($calibration_info->ii_edm_a_3, 3), 3);
                                  $f_ii_edm_a = ($ii_edm_a_1 + $ii_edm_a_2 + $ii_edm_a_3)/3;

                                  $ii_edm_b_1 = number_format(round($calibration_info->ii_edm_b_1, 3), 3);
                                  $ii_edm_b_2 = number_format(round($calibration_info->ii_edm_b_2, 3), 3);
                                  $ii_edm_b_3 = number_format(round($calibration_info->ii_edm_b_3, 3), 3);
                                  $f_ii_edm_b = ($ii_edm_b_1 + $ii_edm_b_2 + $ii_edm_b_3)/3;

                                  $f_i_ii_edm_a = ($f_i_edm_a + $f_ii_edm_a)/2;
                                  $f_i_ii_edm_b = ($f_i_edm_b + $f_ii_edm_b)/2;


                                $i_edm_a_49 = $calibration_info->i_edm_a_49;
                                $i_edm_a_50 = $calibration_info->i_edm_a_50;
                                $i_edm_a_51 = $calibration_info->i_edm_a_51;
                                $i_edm_a_52 = $calibration_info->i_edm_a_52;
                                $i_edm_a_53 = $calibration_info->i_edm_a_53;
                                $i_edm_a_54 = $calibration_info->i_edm_a_54;
                                $i_edm_a_55 = $calibration_info->i_edm_a_55;
                                $i_edm_a_56 = $calibration_info->i_edm_a_56;
                                $i_edm_a_57 = $calibration_info->i_edm_a_57;
                                $i_edm_a_58 = $calibration_info->i_edm_a_58;
                                $i_edm_a_59 = $calibration_info->i_edm_a_59;
                                $i_edm_a_60 = $calibration_info->i_edm_a_60;
                                $i_edm_a_61 = $calibration_info->i_edm_a_61;
                                $i_edm_a_62 = $calibration_info->i_edm_a_62;
                                $i_edm_a_63 = $calibration_info->i_edm_a_63;
                                $i_edm_a_64 = $calibration_info->i_edm_a_64;
                                $i_edm_a_65 = $calibration_info->i_edm_a_65;
                                $i_edm_a_66 = $calibration_info->i_edm_a_66;
                                $i_edm_a_67 = $calibration_info->i_edm_a_67;
                                $i_edm_a_68 = $calibration_info->i_edm_a_68;
                                $i_edm_a_69 = $calibration_info->i_edm_a_69;
                                $i_edm_a_70 = $calibration_info->i_edm_a_70;
                                $i_edm_a_71 = $calibration_info->i_edm_a_71;
                                $i_edm_a_72 = $calibration_info->i_edm_a_72;
                                $i_edm_a_73 = $calibration_info->i_edm_a_73;
                                $i_edm_a_74 = $calibration_info->i_edm_a_74;
                                $i_edm_a_75 = $calibration_info->i_edm_a_75;
                                $i_edm_a_76 = $calibration_info->i_edm_a_76;
                                $i_edm_a_77 = $calibration_info->i_edm_a_77;
                                $i_edm_a_78 = $calibration_info->i_edm_a_78;
                                $i_edm_a_79 = $calibration_info->i_edm_a_79;
                                $i_edm_a_80 = $calibration_info->i_edm_a_80;
                                $i_edm_a_81 = $calibration_info->i_edm_a_81;
                                $i_edm_a_82 = $calibration_info->i_edm_a_82;
                                $i_edm_a_83 = $calibration_info->i_edm_a_83;
                                $i_edm_a_84 = $calibration_info->i_edm_a_84;
                                $i_edm_a_85 = $calibration_info->i_edm_a_85;
                                $i_edm_a_86 = $calibration_info->i_edm_a_86;
                                $i_edm_a_87 = $calibration_info->i_edm_a_87;
                                $i_edm_a_88 = $calibration_info->i_edm_a_88;
                                $i_edm_a_89 = $calibration_info->i_edm_a_89;
                                $i_edm_a_90 = $calibration_info->i_edm_a_90;
                                $i_edm_a_91 = $calibration_info->i_edm_a_91;
                                $i_edm_a_92 = $calibration_info->i_edm_a_92;
                                $i_edm_a_93 = $calibration_info->i_edm_a_93;
                                $i_edm_a_94 = $calibration_info->i_edm_a_94;
                                $i_edm_a_95 = $calibration_info->i_edm_a_95;
                                $i_edm_a_96 = $calibration_info->i_edm_a_96;

                                $t_v_a_10 = $calibration_info->t_v_a_10;

                                $dresult9 = sqrt(pow(($i_edm_a_49 - $i_edm_a_50), 2) + pow(($i_edm_a_51 - $i_edm_a_52), 2));
                                $dresult9 = number_format($dresult9, 4);
                                $eresult9 = $i_edm_a_53 - $i_edm_a_54;

                                $dresult10 = sqrt(pow(($i_edm_a_55 - $i_edm_a_56), 2) + pow(($i_edm_a_57 - $i_edm_a_58), 2));
                                $dresult10 = number_format($dresult10, 4);
                                $eresult10 = $i_edm_a_59 - $i_edm_a_60;

                                $dresult11 = sqrt(pow(($i_edm_a_61 - $i_edm_a_62), 2) + pow(($i_edm_a_63 - $i_edm_a_64), 2));
                                $dresult11 = number_format($dresult11, 4);
                                $eresult11 = $i_edm_a_65 - $i_edm_a_66;

                                $dresult12 = sqrt(pow(($i_edm_a_67 - $i_edm_a_68), 2) + pow(($i_edm_a_69 - $i_edm_a_70), 2));
                                $dresult12 = number_format($dresult12, 4);
                                $eresult12 = $i_edm_a_71 - $i_edm_a_72;

                                $dresult13 = sqrt(pow(($i_edm_a_73 - $i_edm_a_74), 2) + pow(($i_edm_a_75 - $i_edm_a_76), 2));
                                $dresult13 = number_format($dresult13, 4);
                                $eresult13 = $i_edm_a_77 - $i_edm_a_78;

                                $dresult14 = sqrt(pow(($i_edm_a_79 - $i_edm_a_80), 2) + pow(($i_edm_a_81 - $i_edm_a_82), 2));
                                $dresult14 = number_format($dresult14, 4);
                                $eresult14 = $i_edm_a_83 - $i_edm_a_84;

                                $dresult15 = sqrt(pow(($i_edm_a_85 - $i_edm_a_86), 2) + pow(($i_edm_a_87 - $i_edm_a_88), 2));
                                $dresult15 = number_format($dresult15, 4);
                                $eresult15 = $i_edm_a_89 - $i_edm_a_90;

                                $dresult16 = sqrt(pow(($i_edm_a_91 - $i_edm_a_92), 2) + pow(($i_edm_a_93 - $i_edm_a_94), 2));
                                $dresult16 = number_format($dresult16, 4);
                                $eresult16 = $i_edm_a_95 - $i_edm_a_96;

                                $mean_total1 = ($dresult9 + $dresult10 + $dresult11 + $dresult12 + $dresult13 + $dresult14 + $dresult15 + $dresult16) / 8;
                                $elevation_total1 = ($eresult9 + $eresult10 + $eresult11 + $eresult12 + $eresult13 + $eresult14 + $eresult15 + $eresult16) / 8;

                                $mean_distance9 = ($dresult9 - $mean_total1) * 1000;
                                $mean_distance10 = ($dresult10 - $mean_total1) * 1000;
                                $mean_distance11 = ($dresult11 - $mean_total1) * 1000;
                                $mean_distance12 = ($dresult12 - $mean_total1) * 1000;
                                $mean_distance13 = ($dresult13 - $mean_total1) * 1000;
                                $mean_distance14 = ($dresult14 - $mean_total1) * 1000;
                                $mean_distance15 = ($dresult15 - $mean_total1) * 1000;
                                $mean_distance16 = ($dresult16 - $mean_total1) * 1000;

                                $elevation_distance9 = ($eresult9 - $elevation_total1) * 1000;
                                $elevation_distance10 = ($eresult10 - $elevation_total1) * 1000;
                                $elevation_distance11 = ($eresult11 - $elevation_total1) * 1000;
                                $elevation_distance12 = ($eresult12 - $elevation_total1) * 1000;
                                $elevation_distance13 = ($eresult13 - $elevation_total1) * 1000;
                                $elevation_distance14 = ($eresult14 - $elevation_total1) * 1000;
                                $elevation_distance15 = ($eresult15 - $elevation_total1) * 1000;
                                $elevation_distance16 = ($eresult16 - $elevation_total1) * 1000;

                                $standard_error3 = -$mean_distance9 / 2;
                                $standard_error4 = -$elevation_distance14 / 2;

                                $smdistance9 = pow($mean_distance9, 2);
                                $smdistance10 = pow($mean_distance10, 2);
                                $smdistance11 = pow($mean_distance11, 2);
                                $smdistance12 = pow($mean_distance12, 2);
                                $smdistance13 = pow($mean_distance13, 2);
                                $smdistance14 = pow($mean_distance14, 2);
                                $smdistance15 = pow($mean_distance15, 2);
                                $smdistance16 = pow($mean_distance16, 2);

                                $total_smdistance1 = $smdistance9 + $smdistance10 + $smdistance11 + $smdistance12 + $smdistance13 + $smdistance14 + $smdistance15 + $smdistance16;

                                $smelevation9 = pow($elevation_distance9, 2);
                                $smelevation10 = pow($elevation_distance10, 2);
                                $smelevation11 = pow($elevation_distance11, 2);
                                $smelevation12 = pow($elevation_distance12, 2);
                                $smelevation13 = pow($elevation_distance13, 2);
                                $smelevation14 = pow($elevation_distance14, 2);
                                $smelevation15 = pow($elevation_distance15, 2);
                                $smelevation16 = pow($elevation_distance16, 2);

                                $total_smelevation1 = $smelevation9 + $smelevation10 + $smelevation11 + $smelevation12 + $smelevation13 + $smelevation14 + $smelevation15 + $smelevation16;

                                $dfresult3 = $total_smdistance1 / 15;
                                $dfresult4 = $total_smelevation1 / 15;

                $info_report .= '<table border="1" cellpadding="4">
                        <tr style="color:#424242;">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; background-color: lightgrey; border:1px solid #000; text-align:left; color:maroon;" colspan="6"><b>POST-CALIBRATION CHECKS</b></td>
                        </tr>
                            <tr style="color:#424242;">
                                
                                <td style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="3"><b>HORIZONTAL CIRCLE INDEX ERROR</b></td>
                                <td style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="4"><b>VERTICAL CIRCLE INDEX ERROR</b></td>
                                
                            </tr>
                            <thead>
                                <tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . (get_option('pdf_font_size') + 4) . 'px;">
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="1">FACE</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="1">START A</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="1">END B</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">START A</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">END B</th>
                                </tr>
                            </thead>
                            <tbody>';

                            $info_report .= '
                                <tr style="color:#424242;">
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>I</b></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($calibration_info->t_h_a) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($calibration_info->t_h_b) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($calibration_info->i_v_a) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($calibration_info->i_v_b) . '</td>
                                    
                                </tr>
                                <tr style="color:#424242;">
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>II</b></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($calibration_info->ii_h_a) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($calibration_info->ii_h_b) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($calibration_info->ii_v_a) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($calibration_info->ii_v_b) . '</td>
                                
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-center total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>Half Circle is 180° 00\' 00"</b></td>
                                    <td colspan="4" class="text-center" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>Half Circle is 360° 00\' 00"</b></td>
                                </tr>
                                <tr style="color:#424242;">
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>SUM(I+II)</b></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($sum_h_a) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($sum_h_b) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($sum_v_a) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($sum_v_b) . '</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td colspan="2" class="text-center total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>(180° 00\' 00" - SUM(A+B))/2</b></td>
                                    <td colspan="2" class="text-center" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>(360° 00\' 00" - SUM(A+B))/2</b></td>
                                </tr>
                                <tr style="color:#424242;">
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>DOUBLE ERROR</b></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($d_error_h_a) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($d_error_h_b) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($d_error_v_a) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($d_error_v_b) . '</td>
                                </tr>
                                </tbody>
                        </table>';

                $info_report .= '<table border="0" cellpadding="4">
                            <thead>
                                <tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . (get_option('pdf_font_size') + 4) . 'px;">
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Residuals from Mean(mm)</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Squared meaned residuals(mm)</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></th>

                                </tr>
                                <tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . (get_option('pdf_font_size') + 4) . 'px;">
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">FACE</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">X</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Y</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Z</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Distance</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Elevation</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Distance</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Elevation</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Distance</th>
                                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Elevation</th>

                                </tr>
                            </thead>
                            <tbody>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>I</b></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_49 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_51 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_53 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult9 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult9 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($mean_distance9, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($elevation_distance9, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smdistance9, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smelevation9, 4) . '</td>

                            </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b></b></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_50 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_52 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_54 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>

                                </tr>

                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>II</b></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_55 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_57 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_59 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult10 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult10 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($mean_distance10, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($elevation_distance10, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smdistance10, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smelevation10, 4) . '</td>

                                </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_56 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_58 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_60 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>

                                </tr>

                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>I</b></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_61 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_63 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_65 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult11 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult11 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($mean_distance11, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($elevation_distance11, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smdistance11, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smelevation11, 4) . '</td>

                                </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_62 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_64 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_66 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>

                            </tr>

                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>II</b></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_67 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_69 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_71 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult12 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult12 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($mean_distance12, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($elevation_distance12, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smdistance12, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smelevation12, 4) . '</td>

                                </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_68 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_70 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_72 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>

                                </tr>

                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>I</b></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_73 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_75 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_77 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult13 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult13 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($mean_distance13, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($elevation_distance13, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smdistance13, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smelevation13, 4) . '</td>

                                </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_74 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_76 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_78 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>

                                </tr>

                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>II</b></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_79 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_81 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_83 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult14 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult14 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($mean_distance14, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($elevation_distance14, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smdistance14, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smelevation14, 4) . '</td>

                                </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_80 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_82 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_84 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>

                                </tr>

                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>I</b></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_85 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_87 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_89 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult15 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult15 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($mean_distance15, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($elevation_distance15, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smdistance15, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smelevation15, 4) . '</td>

                                </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_86 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_88 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_90 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>

                                </tr>

                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>II</b></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_91 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_93 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_95 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult16 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult16 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($mean_distance16, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($elevation_distance16, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smdistance16, 4) . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($smelevation16, 4) . '</td>

                                </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_92 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_94 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $calibration_info->i_edm_a_96 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $dresult0 . '</td>
                                <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $eresult0 . '</td>
                                </tr>
                                <tr style="color:#424242; background-color: lightgrey;">
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>Mean</b></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($mean_total1, 4) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($elevation_total1, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                </tr>
                                <tr style="color:#424242; background-color: lightgrey;">
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>Standard Errors (Max & Absolute)/2</b></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($standard_error3, 4) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($standard_error4, 4) . '</td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>

                                </tr>
                                <tr style="color:#424242; background-color: lightgrey;">
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>Degree of Freedom</b></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($dfresult3, 4) . '</td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . number_format($dfresult4, 4) . '</td>
                                </tr>
                                <tr style="color:#424242;">
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                    <td class="total" colspan="2" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; ' . ($standard_error3 < $t_v_a_10 && $standard_error4 < $t_v_a_10 && $dfresult3 < $t_v_a_10 && $dfresult4 < $t_v_a_10 ? 'background-color: lightgreen; color: white;' : 'background-color: red; color: white;') . '">
                                        ' . ($standard_error3 < $t_v_a_10 && $standard_error4 < $t_v_a_10 && $dfresult3 < $t_v_a_10 && $dfresult4 < $t_v_a_10 ? 'PASSED' : 'FAILED') . '
                                    </td>
                                </tr>

                        </tbody>
                    </table>';

}else if ($service_request->item_type == 'Theodolite') {

                //  Assuming the form is submitted via POST
                $th_v_a_1 = $calibration_info->th_v_a_1;
                $th_v_a_2 = $calibration_info->th_v_a_2;
                $th_v_a_3 = $calibration_info->th_v_a_3;
                $th_v_a_4 = $calibration_info->th_v_a_4;
                $th_v_a_5 = $calibration_info->th_v_a_5;
                $th_v_a_6 = $calibration_info->th_v_a_6;
                $th_v_a_7 = $calibration_info->th_v_a_7;
                $th_v_a_8 = $calibration_info->th_v_a_8;
                $th_v_a_9 = $calibration_info->th_v_a_9;
                $th_v_a_10 = $calibration_info->th_v_a_10;
                $th_v_a_11 = $calibration_info->th_v_a_11;
                $th_v_a_12 = $calibration_info->th_v_a_12;
                $th_v_a_13 = $calibration_info->th_v_a_13;
                $th_v_a_14 = $calibration_info->th_v_a_14;


    $info_report = '<table border="0" cellpadding="5">
                <thead>
                <tr style="color:#424242;">
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; background-color: lightgrey; border:1px solid #000; text-align:left; color:maroon;" colspan="6"><b>INSTRUMENT INFORMATION</b></td>
                </tr>
                </thead>
                <tbody>
                <tr class="form-inline">
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT MAKE</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $th_v_a_1 . '</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT MODEL</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $th_v_a_2 . '</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT SERIAL NO.</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $th_v_a_3 . '</td>
                </tr>
                <tr class="form-inline">
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT CONDITION</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $th_v_a_4 . '</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">BASELINE TEST DISTANCE (M)</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $th_v_a_5 . '</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">WEATHER CONDITION</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $th_v_a_6 . '</td>
                </tr>
                <tr class="form-inline">
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">TEMPERATURE (°C)</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $th_v_a_7 . '</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">AIR PRESSURE (hPa)</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $th_v_a_8 . '</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">MANUFACTURER ACCURACY (00\'\')</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $th_v_a_9 . '</td>
                </tr>
                <tr class="form-inline">
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">TARGET PRISM CONSTANT (MM)</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $th_v_a_10 . '</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT PRISM CONSTANT (MM)</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $th_v_a_11 . '</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT HEIGHT (M)</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $th_v_a_12 . '</td>
                </tr>
                <tr class="form-inline">
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT HEIGHT (M)</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $th_v_a_12 . '</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">TARGET MAKE</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $th_v_a_13 . '</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">TARGET MODEL</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $th_v_a_14 . '</td>
                </tr>
                </tbody>
                </table><br><br><br><br><br>';

                            $th_h_a = $calibration_info->th_h_a;
                            $thh_h_a = $calibration_info->thh_h_a;
                            $sum_h_a = $th_h_a+$thh_h_a;
                            $d_error_h_a = (dms2dec(180,0,0)-$sum_h_a);

                            $th_h_b = $calibration_info->th_h_b;
                            $thh_h_b = $calibration_info->thh_h_b;                                
                            $sum_h_b = $th_h_b+$thh_h_b;
                            $d_error_h_b = (dms2dec(180,0,0)-$sum_h_b);

                            $th_v_a = $calibration_info->th_v_a;
                            $thh_v_b = $calibration_info->thh_v_b;                                
                            $sum_v_a = $th_v_a+$thh_v_b;
                            $d_error_v_a = (dms2dec(360,0,0)-$sum_v_a);

                            $thh_v_a = $calibration_info->thh_v_a;
                            $thh_v_c = $calibration_info->thh_v_c;
                            $sum_v_b = $thh_v_a+$thh_v_c;
                            $d_error_v_b = (dms2dec(360,0,0)-$sum_v_b);

                            $i_edm_a_1 = number_format(round($calibration_info->i_edm_a_1, 3), 3);
                            $i_edm_a_2 = number_format(round($calibration_info->i_edm_a_2, 3), 3);
                            $i_edm_a_3 = number_format(round($calibration_info->i_edm_a_3, 3), 3);
                            $f_i_edm_a = ($i_edm_a_1 + $i_edm_a_2 + $i_edm_a_3)/3;

                            $i_edm_b_3 = number_format(round($calibration_info->i_edm_b_3, 3), 3);
                            $i_edm_b_4 = number_format(round($calibration_info->i_edm_b_4, 3), 3);
                            $i_edm_b_3 = number_format(round($calibration_info->i_edm_b_3, 3), 3);
                            $f_i_edm_b = ($i_edm_b_1 + $i_edm_b_2 + $i_edm_b_3)/3;

                            $ii_edm_a_1 = number_format(round($calibration_info->ii_edm_a_1, 3), 3);
                            $ii_edm_a_2 = number_format(round($calibration_info->ii_edm_a_2, 3), 3);
                            $ii_edm_a_3 = number_format(round($calibration_info->ii_edm_a_3, 3), 3);
                            $f_ii_edm_a = ($ii_edm_a_1 + $ii_edm_a_2 + $ii_edm_a_3)/3;

                            $ii_edm_b_1 = number_format(round($calibration_info->ii_edm_b_1, 3), 3);
                            $ii_edm_b_2 = number_format(round($calibration_info->ii_edm_b_2, 3), 3);
                            $ii_edm_b_3 = number_format(round($calibration_info->ii_edm_b_3, 3), 3);
                            $f_ii_edm_b = ($ii_edm_b_1 + $ii_edm_b_2 + $ii_edm_b_3)/3;

                            $f_i_ii_edm_a = ($f_i_edm_a + $f_ii_edm_a)/2;
                            $f_i_ii_edm_b = ($f_i_edm_b + $f_ii_edm_b)/2;

        
    $info_report .= '<table border="1" cellpadding="4">
        <tr style="color:#424242;">
            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; background-color: lightgrey; border:1px solid #000; text-align:left; color:maroon;" colspan="6"><b>PRE-CALIBRATION CHECKS</b></td>
        </tr>
            <tr style="color:#424242;">
                
                <td style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="3"><b>HORIZONTAL CIRCLE INDEX ERROR</b></td>
                <td style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="4"><b>VERTICAL CIRCLE INDEX ERROR</b></td>
                
            </tr>
            <thead>
                <tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . (get_option('pdf_font_size') + 4) . 'px;">
                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="1">FACE</th>
                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="1">START A</th>
                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="1">END B</th>
                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">START A</th>
                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">END B</th>
                </tr>
            </thead>
            <tbody>';

    $info_report .= '<tr style="color:#424242;">
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>I</b></td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($calibration_info->th_h_a) . '</td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($calibration_info->th_h_b) . '</td>
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($calibration_info->th_v_a) . '</td>
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($calibration_info->thh_v_a) . '</td>
                    
                </tr>
                <tr style="color:#424242;">
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>II</b></td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($calibration_info->thh_h_a) . '</td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($calibration_info->thh_h_b) . '</td>
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($calibration_info->thh_v_b) . '</td>
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($calibration_info->thh_v_c) . '</td>
                
                </tr>
                <tr>
                    <td colspan="3" class="text-center total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>Half Circle is 180° 00\' 00"</b></td>
                    <td colspan="4" class="text-center" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>Half Circle is 360° 00\' 00"</b></td>
                </tr>
                <tr style="color:#424242;">
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>SUM(I+II)</b></td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($sum_h_a) . '</td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($sum_h_b) . '</td>
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($sum_v_a) . '</td>
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($sum_v_b) . '</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="2" class="text-center total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>(180° 00\' 00" - SUM(A+B))/2</b></td>
                    <td colspan="2" class="text-center" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>(360° 00\' 00" - SUM(A+B))/2</b></td>
                </tr>
                <tr style="color:#424242;">
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>DOUBLE ERROR</b></td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($d_error_h_a) . '</td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($d_error_h_b) . '</td>
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($d_error_v_a) . '</td>
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($d_error_v_b) . '</td>
                </tr>
                <tr style="color:#424242;">
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; font-weight:500;"><b>Index Error (Double error/2)</b></td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($d_error_h_a) . '</td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($d_error_h_b) . '</td>
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">4.5◦0\' 0\'\'</td>
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($d_error_v_b) . '</td>
                </tr>
                <tr style="color:#424242;">
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; background-color: lightgreen; color: black;"><b>PASSED</b></td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; background-color: lightgreen; color: black;"><b>PASSED</b></td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; background-color: lightgreen; color: black;" colspan="2"><b>PASSED</b></td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; background-color: lightgreen; color: black;" colspan="2"><b>PASSED</b></td>
                </tr>
                </tbody>
                </table><br><br><br><br><br>+
                ';

                $th_h_a1 = $calibration_info->th_h_a1;
                $thh_h_a1 = $calibration_info->thh_h_a1;
                $sum_h_a1 = $th_h_a1+$thh_h_a1;
                $d_error_h_a1 = (dms2dec(180,0,0)-$sum_h_a1);

                $th_h_b1 = $calibration_info->th_h_b1;
                $thh_h_b1 = $calibration_info->thh_h_b1;                                
                $sum_h_b1 = $th_h_b1+$thh_h_b1;
                $d_error_h_b1 = (dms2dec(180,0,0)-$sum_h_b1);

                $th_v_a1 = $calibration_info->th_v_a1;
                $thh_v_b1 = $calibration_info->thh_v_b1;                                
                $sum_v_a1 = $th_v_a1+$thh_v_b1;
                $d_error_v_a1 = (dms2dec(360,0,0)-$sum_v_a1);

                $thh_v_a1 = $calibration_info->thh_v_a1;
                $thh_v_c1 = $calibration_info->thh_v_c1;
                $sum_v_b1 = $thh_v_a1+$thh_v_c1;
                $d_error_v_b1 = (dms2dec(360,0,0)-$sum_v_b1);

                $i_edm_a_1 = number_format(round($calibration_info->i_edm_a_1, 3), 3);
                $i_edm_a_2 = number_format(round($calibration_info->i_edm_a_2, 3), 3);
                $i_edm_a_3 = number_format(round($calibration_info->i_edm_a_3, 3), 3);
                $f_i_edm_a = ($i_edm_a_1 + $i_edm_a_2 + $i_edm_a_3)/3;

                $i_edm_b_1 = number_format(round($calibration_info->i_edm_b_1, 3), 3);
                $i_edm_b_2 = number_format(round($calibration_info->i_edm_b_2, 3), 3);
                $i_edm_b_3 = number_format(round($calibration_info->i_edm_b_3, 3), 3);
                $f_i_edm_b = ($i_edm_b_1 + $i_edm_b_2 + $i_edm_b_3)/3;

                $ii_edm_a_1 = number_format(round($calibration_info->ii_edm_a_1, 3), 3);
                $ii_edm_a_2 = number_format(round($calibration_info->ii_edm_a_2, 3), 3);
                $ii_edm_a_3 = number_format(round($calibration_info->ii_edm_a_3, 3), 3);
                $f_ii_edm_a = ($ii_edm_a_1 + $ii_edm_a_2 + $ii_edm_a_3)/3;

                $ii_edm_b_1 = number_format(round($calibration_info->ii_edm_b_1, 3), 3);
                $ii_edm_b_2 = number_format(round($calibration_info->ii_edm_b_2, 3), 3);
                $ii_edm_b_3 = number_format(round($calibration_info->ii_edm_b_3, 3), 3);
                $f_ii_edm_b = ($ii_edm_b_1 + $ii_edm_b_2 + $ii_edm_b_3)/3;

                $f_i_ii_edm_a = ($f_i_edm_a + $f_ii_edm_a)/2;
                $f_i_ii_edm_b = ($f_i_edm_b + $f_ii_edm_b)/2;

    $info_report .= '<table border="1" cellpadding="4">
        <tr style="color:#424242;">
            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; background-color: lightgrey; border:1px solid #000; text-align:left; color:maroon;" colspan="6"><b>POST-CALIBRATION CHECKS</b></td>
        </tr>
            <tr style="color:#424242;">
                
                <td style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="3"><b>HORIZONTAL CIRCLE INDEX ERROR</b></td>
                <td style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="4"><b>VERTICAL CIRCLE INDEX ERROR</b></td>
                
            </tr>
            <thead>
                <tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . (get_option('pdf_font_size') + 4) . 'px;">
                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="1">FACE</th>
                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="1">START A</th>
                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="1">END B</th>
                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">START A</th>
                    <th style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">END B</th>
                </tr>
            </thead>
            <tbody>';

            $info_report .= '
                <tr style="color:#424242;">
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>I</b></td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($calibration_info->th_h_a1) . '</td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($calibration_info->th_h_b1) . '</td>
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($calibration_info->th_v_a1) . '</td>
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($calibration_info->thh_v_a1) . '</td>
                    
                </tr>
                <tr style="color:#424242;">
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>II</b></td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($calibration_info->thh_h_a1) . '</td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($calibration_info->thh_h_b1) . '</td>
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($calibration_info->thh_v_b1) . '</td>
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($calibration_info->thh_v_c1) . '</td>
                
                </tr>
                <tr>
                    <td colspan="3" class="text-center total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>Half Circle is 180° 00\' 00"</b></td>
                    <td colspan="4" class="text-center" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>Half Circle is 360° 00\' 00"</b></td>
                </tr>
                <tr style="color:#424242;">
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>SUM(I+II)</b></td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($sum_h_a1) . '</td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($sum_h_b1) . '</td>
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($sum_v_a1) . '</td>
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($sum_v_b1) . '</td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="2" class="text-center total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>(180° 00\' 00" - SUM(A+B))/2</b></td>
                    <td colspan="2" class="text-center" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>(360° 00\' 00" - SUM(A+B))/2</b></td>
                </tr>
                <tr style="color:#424242;">
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"><b>DOUBLE ERROR</b></td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($d_error_h_a1) . '</td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($d_error_h_b1) . '</td>
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($d_error_v_a1) . '</td>
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($d_error_v_b1) . '</td>
                </tr>

                <tr style="color:#424242;">
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; font-weight:500;"><b>Index Error (Double error/2)</b></td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($d_error_h_a1) . '</td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . dec2dms_full($d_error_h_b1) . '</td>
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">4.5◦0\' 0\'\'</td>
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . dec2dms_full($d_error_v_b1) . '</td>
                </tr>
                <tr style="color:#424242;">
                    <td class="desc" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; background-color: lightgreen; color: black;"><b>PASSED</b></td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; background-color: lightgreen; color: black;"><b>PASSED</b></td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; background-color: lightgreen; color: black;" colspan="2"><b>PASSED</b></td>
                    <td class="total" style="vertical-align: middle; font-size:' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; background-color: lightgreen; color: black;" colspan="2"><b>PASSED</b></td>
                </tr>
                </tbody>
        </table>';

        

} else if ($service_request->item_type == 'GPS') {
                    // Assuming the form is submitted via POST
                    $r_v_a_1 = $calibration_info->r_v_a_1;
                    $r_v_a_2 = $calibration_info->r_v_a_2;
                    $r_v_a_3 = $calibration_info->r_v_a_3;
                    $r_v_a_4 = $calibration_info->r_v_a_4;
                    $r_v_a_5 = $calibration_info->r_v_a_5;
                    $r_v_a_6 = $calibration_info->r_v_a_6;
                    $r_v_a_7 = $calibration_info->r_v_a_7;
                    $r_v_a_8 = $calibration_info->r_v_a_8;
                    $r_v_a_9 = $calibration_info->r_v_a_9;
                    $r_v_a_10 = $calibration_info->r_v_a_10;
                    $r_v_a_11 = $calibration_info->r_v_a_11;
                    $r_v_a_12 = $calibration_info->r_v_a_12;
                    $r_v_a_13 = $calibration_info->r_v_a_13;


            // Instruments report
            $info_report = '
                <table border="0" cellpadding="5">

                    <thead>
                    <tr style="color:#424242;">
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; background-color: lightgrey; border:1px solid #000; text-align:left; color:maroon;" colspan="6"><b>INSTRUMENT INFORMATION</b></td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="form-inline">
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT MAKE</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $r_v_a_1 . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT MODEL</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $r_v_a_2 . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT SERIAL NO.</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $r_v_a_3 . '</td>
                    </tr>
                    <tr class="form-inline">
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT CONDITION</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $r_v_a_4 . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">TEST DISTANCE (M)</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $r_v_a_5 . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">WEATHER CONDITION</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $r_v_a_6 . '</td>
                    </tr>
                    <tr class="form-inline">
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">TEMPERATURE (°C)</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $r_v_a_7 . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">AIR PRESSURE (hPa)</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $r_v_a_8 . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">BASELINE HORIZONTAL DISTANCE (M)</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $r_v_a_9 . '</td>
                    </tr>
                    <tr class="form-inline">
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">BASELINE ELEVATION ACCURACY (MM)</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $r_v_a_10 . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">MANUFACTURER HRMS ACCURACY (MM)</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $r_v_a_11 . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">MANUFACTURER VRMS ACCURACY (MM)</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $r_v_a_12 . '</td>
                    </tr>
                    <tr class="form-inline">
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">INSTRUMENT HEIGHT (M)</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $r_v_a_13 . '</td>
                    </tr>

                    </tbody>
                    </table>';



            // pre-calibration report
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
                                        <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">HD (m)</th>
                                        <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">EA (mm)</th>
                                        <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Eastings</th>
                                        <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Northings</th>
                                        <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Elevation</th>
                                        <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Eastings</th>
                                        <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Northings</th>
                                        <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Elevation</th>

                                    </tr>
                                    <tr style="color:#424242;">
                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:left; color:red;" colspan="2"><b>START TIME:<b>' . (!empty($calibration_info->start_time_1) ? date('H:i A', strtotime($calibration_info->start_time_1)) : 'N/A') . '</b></b></td>
                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:left; color:maroon;" colspan=""><b></b></td>
                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>


                                    </tr>
                                    ';

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
  
                                    $sumValue1a = round(sqrt(pow(($i_v_a_1 - $ii_v_a_1), 2) + pow(($i_v_a_2 - $ii_v_a_2), 2)), 4);
                                    $sumValue2a = round(sqrt(pow(($iii_v_a_1 - $iv_v_a_1), 2) + pow(($iii_v_a_2 - $iv_v_a_2), 2)), 4);
                                    $sumValue3a = round(sqrt(pow(($v_v_a_1 - $vi_v_a_1), 2) + pow(($v_v_a_2 - $vi_v_a_2), 2)), 4);
                                    $sumValue4a = round(sqrt(pow(($vii_v_a_1 - $viii_v_a_1), 2) + pow(($vii_v_a_2 - $viii_v_a_2), 2)), 4);
                                    $sumValue5a = round(sqrt(pow(($xi_v_a_1 - $x_v_a_1), 2) + pow(($xi_v_a_2 - $x_v_a_2), 2)), 4);
  
                                    $sumValue6a = round($ii_v_a_3 - $i_v_a_3, 4);
                                    $sumValue7a = round($iv_v_a_3 - $iii_v_a_3, 4);
                                    $sumValue8a = round($vi_v_a_3 - $v_v_a_3, 4);
                                    $sumValue9a = round($viii_v_a_3 - $vii_v_a_3, 4);
                                    $sumValue10a = round($x_v_a_3 - $xi_v_a_3, 4);
  
                                    $sumValue12 = round($i_v_a_2 + $iii_v_a_2 + $v_v_a_2 + $vii_v_a_2 + $xi_v_a_2) / 5;
                                    $sumValue13 = round($i_v_a_3 + $iii_v_a_3 + $v_v_a_3 + $vii_v_a_3 + $xi_v_a_3) / 5;
  
                                    $sumValue14 = round($ii_v_a_1 + $iv_v_a_1 + $vi_v_a_1 + $viii_v_a_1 + $x_v_a_1) / 5;
                                    $sumValue15 = round($ii_v_a_2 + $iv_v_a_2 + $vi_v_a_2 + $viii_v_a_2 + $x_v_a_2) / 5;
                                    $sumValue16 = round($ii_v_a_3 + $iv_v_a_3 + $vi_v_a_3 + $viii_v_a_3 + $x_v_a_3) / 5;
  
                                    $sumValue17 = round(($sumValue1a + $sumValue2a + $sumValue3a + $sumValue4a + $sumValue5a)/ 5, 3);
                                    $sumValue18 = round(($sumValue6a + $sumValue7a + $sumValue8a + $sumValue9a + $sumValue10a) / 5, 3);
  
                                    $sumValue19 = round(($sumValue17) / 5, 3);
                                    $sumValue20 = round(($sumValue18) / 5, 3);
  
                                    session_start();  // Start the session
                                    $sumValue11a = $_SESSION['sumValue11a'];
                                    $sumValue12a = $_SESSION['sumValue12a'];
                                    $sumValue13a = $_SESSION['sumValue13a'];
                                    $sumValue14a = $_SESSION['sumValue14a'];
                                    $sumValue15a = $_SESSION['sumValue15a'];
                                    $sumValue16a = $_SESSION['sumValue16a'];
  
                                    // Residual Easting and Squared Values for set 1
                                    $residualEasting1a = ($sumValue11a - $i_v_a_1) * 1000;
                                    $residualEasting1b = ($sumValue12a - $i_v_a_2) * 1000;
                                    $residualEasting1c = ($sumValue13a - $i_v_a_3) * 1000;
                                    $squaredValue1a = $residualEasting1a ** 2;
                                    $squaredValue1b = $residualEasting1b ** 2;
                                    $squaredValue1c = $residualEasting1c ** 2;
  
                                    // Residual Easting and Squared Values for set 2
                                    $residualEasting2a = ($sumValue14a - $ii_v_a_1) * 1000;
                                    $residualEasting2b = ($sumValue15a - $ii_v_a_2) * 1000;
                                    $residualEasting2c = ($sumValue16a - $ii_v_a_3) * 1000;
                                    $squaredValue2a = $residualEasting2a ** 2;
                                    $squaredValue2b = $residualEasting2b ** 2;
                                    $squaredValue2c = $residualEasting2c ** 2;
  
                                    // Residual Easting and Squared Values for set 3
                                    $residualEasting3a = ($sumValue11a - $iii_v_a_1) * 1000;
                                    $residualEasting3b = ($sumValue12a - $iii_v_a_2) * 1000;
                                    $residualEasting3c = ($sumValue13a - $iii_v_a_3) * 1000;
                                    $squaredValue3a = $residualEasting3a ** 2;
                                    $squaredValue3b = $residualEasting3b ** 2;
                                    $squaredValue3c = $residualEasting3c ** 2;
  
                                    // Residual Easting and Squared Values for set 4
                                    $residualEasting4a = ($sumValue14a - $iv_v_a_1) * 1000;
                                    $residualEasting4b = ($sumValue15a - $iv_v_a_2) * 1000;
                                    $residualEasting4c = ($sumValue16a - $iv_v_a_3) * 1000;
                                    $squaredValue4a = $residualEasting4a ** 2;
                                    $squaredValue4b = $residualEasting4b ** 2;
                                    $squaredValue4c = $residualEasting4c ** 2;
  
                                    // Residual Easting and Squared Values for set 5
                                    $residualEasting5a = ($sumValue11a - $v_v_a_1) * 1000;
                                    $residualEasting5b = ($sumValue12a - $v_v_a_2) * 1000;
                                    $residualEasting5c = ($sumValue13a - $v_v_a_3) * 1000;
                                    $squaredValue5a = $residualEasting5a ** 2;
                                    $squaredValue5b = $residualEasting5b ** 2;
                                    $squaredValue5c = $residualEasting5c ** 2;
  
                                    // Residual Easting and Squared Values for set 6
                                    $residualEasting6a = ($sumValue14a - $vi_v_a_1) * 1000;
                                    $residualEasting6b = ($sumValue15a - $vi_v_a_2) * 1000;
                                    $residualEasting6c = ($sumValue16a - $vi_v_a_3) * 1000;
                                    $squaredValue6a = $residualEasting6a ** 2;
                                    $squaredValue6b = $residualEasting6b ** 2;
                                    $squaredValue6c = $residualEasting6c ** 2;
  
                                    // Residual Easting and Squared Values for set 7
                                    $residualEasting7a = ($sumValue11a - $vii_v_a_1) * 1000;
                                    $residualEasting7b = ($sumValue12a - $vii_v_a_2) * 1000;
                                    $residualEasting7c = ($sumValue13a - $vii_v_a_3) * 1000;
                                    $squaredValue7a = $residualEasting7a ** 2;
                                    $squaredValue7b = $residualEasting7b ** 2;
                                    $squaredValue7c = $residualEasting7c ** 2;
  
                                    // Residual Easting and Squared Values for set 8
                                    $residualEasting8a = ($sumValue14a - $viii_v_a_1) * 1000;
                                    $residualEasting8b = ($sumValue15a - $viii_v_a_2) * 1000;
                                    $residualEasting8c = ($sumValue16a - $viii_v_a_3) * 1000;
                                    $squaredValue8a = $residualEasting8a ** 2;
                                    $squaredValue8b = $residualEasting8b ** 2;
                                    $squaredValue8c = $residualEasting8c ** 2;
  
                                    // Residual Easting and Squared Values for set 9
                                    $residualEasting9a = ($sumValue11a - $xi_v_a_1) * 1000;
                                    $residualEasting9b = ($sumValue12a - $xi_v_a_2) * 1000;
                                    $residualEasting9c = ($sumValue13a - $xi_v_a_3) * 1000;
                                    $squaredValue9a = $residualEasting9a ** 2;
                                    $squaredValue9b = $residualEasting9b ** 2;
                                    $squaredValue9c = $residualEasting9c ** 2;
  
                                    // Residual Easting and Squared Values for set 10
                                    $residualEasting10a = ($sumValue14a - $x_v_a_1) * 1000;
                                    $residualEasting10b = ($sumValue15a - $x_v_a_2) * 1000;
                                    $residualEasting10c = ($sumValue16a - $x_v_a_3) * 1000;
                                    $squaredValue10a = $residualEasting10a ** 2;
                                    $squaredValue10b = $residualEasting10b ** 2;
                                    $squaredValue10c = $residualEasting10c ** 2;

                                            $info_report .= '<tr style="color:#424242;">
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($i_v_a_1, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($i_v_a_2, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($i_v_a_3, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting1a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting1b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting1c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue1a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue1b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue1c, 3) . '</td>

                                            </tr>';
                                            
                                            $info_report .= '<tr style="color:#424242;">
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_v_a_1 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_v_a_2 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_v_a_3 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue1a . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue6a . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting2a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting2b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting2c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue2a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue2b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue2c, 3) . '</td>

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
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting3a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting3b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting3c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue3a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue3b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue3c, 3) . '</td>


                                            </tr>';
                                            
                                            $info_report .= '<tr style="color:#424242;">
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">4</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_v_a_1 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_v_a_2 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_v_a_3 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue2a . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue7a . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting4a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting4b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting4c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue4a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue4b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue4c, 3) . '</td>


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
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting5a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting5b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting5c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue5a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue5b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue5c, 3) . '</td>


                                            </tr>';
                                            
                                            $info_report .= '<tr style="color:#424242;">
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">6</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">3</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_v_a_1 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_v_a_2 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_v_a_3 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue3a . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue8a . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting6a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting6b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting6c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue6a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue6b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue6c, 3) . '</td>


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
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting7a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting7b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting7c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue7a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue7b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue7c, 3) . '</td>


                                            </tr>';
                                            
                                            $info_report .= '<tr style="color:#424242;">
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">8</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">4</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_v_a_1 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_v_a_2 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_v_a_3 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue4a . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue9a . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting8a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting8b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting8c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue8a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue8b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue8c, 3) . '</td>


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
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting9a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting9b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting9c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue9a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue9b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue9c, 3) . '</td>


                                            </tr>';
                                            
                                            $info_report .= '<tr style="color:#424242;">
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">10</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">5</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_v_a_1 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_v_a_2 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_v_a_3 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue5a . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue10a . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting10a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting10b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting10c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue10a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue10b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue10c, 3) . '</td>


                                            </tr>
                                            <tr style="color:#424242;">
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:red;" colspan="2"><b>STOP TIME:  ' . (!empty($calibration_info->stop_time_1) ? date('H:i A', strtotime($calibration_info->stop_time_1)) : 'N/A') . ' </b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:left; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>

                                            </tr>
                                            <tr style="color:#424242;">
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:left; color:red;" colspan="2"><b>START TIME ' . (!empty($calibration_info->start_time_2) ? date('H:i A', strtotime($calibration_info->start_time_2)) : 'N/A') . '</b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:left; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>


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
          
                                            $sumValue1b = round(sqrt(pow(($i_v_b_1 - $ii_v_b_1), 2) + pow(($i_v_b_2 - $ii_v_b_2), 2)), 4);
                                            $sumValue2b = round(sqrt(pow(($iii_v_b_1 - $iv_v_b_1), 2) + pow(($iii_v_b_2 - $iv_v_b_2), 2)), 4);
                                            $sumValue3b = round(sqrt(pow(($v_v_b_1 - $vi_v_b_1), 2) + pow(($v_v_b_2 - $vi_v_b_2), 2)), 4);
                                            $sumValue4b = round(sqrt(pow(($vii_v_b_1 - $viii_v_b_1), 2) + pow(($vii_v_b_2 - $viii_v_b_2), 2)), 4);
                                            $sumValue5b = round(sqrt(pow(($xi_v_b_1 - $x_v_b_1), 2) + pow(($xi_v_b_2 - $x_v_b_2), 2)), 4);
          
                                            $sumValue6b = round($ii_v_b_3 - $i_v_b_3, 4);
                                            $sumValue7b = round($iv_v_b_3 - $iii_v_b_3, 4);
                                            $sumValue8b = round($vi_v_b_3 - $v_v_b_3, 4);
                                            $sumValue9b = round($viii_v_b_3 - $vii_v_b_3, 4);
                                            $sumValue10b = round($x_v_b_3 - $xi_v_b_3, 4);
          
                                            $sumValue12 = round($i_v_b_2 + $iii_v_b_2 + $v_v_b_2 + $vii_v_b_2 + $xi_v_b_2) / 5;
                                            $sumValue13 = round($i_v_b_3 + $iii_v_b_3 + $v_v_b_3 + $vii_v_b_3 + $xi_v_b_3) / 5;
          
                                            $sumValue14 = round($ii_v_b_1 + $iv_v_b_1 + $vi_v_b_1 + $viii_v_b_1 + $x_v_b_1) / 5;
                                            $sumValue15 = round($ii_v_a_2 + $iv_v_a_2 + $vi_v_a_2 + $viii_v_a_2 + $x_v_a_2) / 5;
                                            $sumValue16 = round($ii_v_b_3 + $iv_v_b_3 + $vi_v_b_3 + $viii_v_b_3 + $x_v_b_3) / 5;
          
                                            $sumValue17 = round(($sumValue1b + $sumValue2b + $sumValue3b + $sumValue4b + $sumValue5b)/ 5, 3);
                                            $sumValue18 = round(($sumValue6b + $sumValue7b + $sumValue8b + $sumValue9b + $sumValue10b) / 5, 3);
          
                                            $sumValue19 = round(($sumValue17) / 5, 3);
                                            $sumValue20 = round(($sumValue18) / 5, 3);
          
          
                                            session_start();  // Start the session
                                            $sumValue11a = $_SESSION['sumValue11a'];
                                            $sumValue12a = $_SESSION['sumValue12a'];
                                            $sumValue13a = $_SESSION['sumValue13a'];
                                            $sumValue14a = $_SESSION['sumValue14a'];
                                            $sumValue15a = $_SESSION['sumValue15a'];
                                            $sumValue16a = $_SESSION['sumValue16a'];
          
          
                                            // Residual Easting and Squared Values for set 11
                                            $residualEasting11a = ($sumValue11a - $i_v_b_1) * 1000;
                                            $residualEasting11b = ($sumValue12a - $i_v_b_2) * 1000;
                                            $residualEasting11c = ($sumValue13a - $i_v_b_3) * 1000;
                                            $squaredValue11a = $residualEasting11a ** 2;
                                            $squaredValue11b = $residualEasting11b ** 2;
                                            $squaredValue11c = $residualEasting11c ** 2;
          
                                            // Residual Easting and Squared Values for set 12
                                            $residualEasting12a = ($sumValue14a - $ii_v_b_1) * 1000;
                                            $residualEasting12b = ($sumValue15a - $ii_v_b_2) * 1000;
                                            $residualEasting12c = ($sumValue16a - $ii_v_b_3) * 1000;
                                            $squaredValue12a = $residualEasting12a ** 2;
                                            $squaredValue12b = $residualEasting12b ** 2;
                                            $squaredValue12c = $residualEasting12c ** 2;
          
                                            // Residual Easting and Squared Values for set 13
                                            $residualEasting13a = ($sumValue11a - $iii_v_b_1) * 1000;
                                            $residualEasting13b = ($sumValue12a - $iii_v_b_2) * 1000;
                                            $residualEasting13c = ($sumValue13a - $iii_v_b_3) * 1000;
                                            $squaredValue13a = $residualEasting13a ** 2;
                                            $squaredValue13b = $residualEasting13b ** 2;
                                            $squaredValue13c = $residualEasting13c ** 2;
          
                                            // Residual Easting and Squared Values for set 14
                                            $residualEasting14a = ($sumValue14a - $iv_v_b_1) * 1000;
                                            $residualEasting14b = ($sumValue15a - $iv_v_b_2) * 1000;
                                            $residualEasting14c = ($sumValue16a - $iv_v_b_3) * 1000;
                                            $squaredValue14a = $residualEasting14a ** 2;
                                            $squaredValue14b = $residualEasting14b ** 2;
                                            $squaredValue14c = $residualEasting14c ** 2;
          
                                            // Residual Easting and Squared Values for set 15
                                            $residualEasting15a = ($sumValue11a - $v_v_b_1) * 1000;
                                            $residualEasting15b = ($sumValue12a - $v_v_b_2) * 1000;
                                            $residualEasting15c = ($sumValue13a - $v_v_b_3) * 1000;
                                            $squaredValue15a = $residualEasting15a ** 2;
                                            $squaredValue15b = $residualEasting15b ** 2;
                                            $squaredValue15c = $residualEasting15c ** 2;
          
                                            // Residual Easting and Squared Values for set 16
                                            $residualEasting16a = ($sumValue14a - $vi_v_b_1) * 1000;
                                            $residualEasting16b = ($sumValue15a - $vi_v_b_2) * 1000;
                                            $residualEasting16c = ($sumValue16a - $vi_v_b_3) * 1000;
                                            $squaredValue16a = $residualEasting16a ** 2;
                                            $squaredValue16b = $residualEasting16b ** 2;
                                            $squaredValue16c = $residualEasting16c ** 2;
          
                                            // Residual Easting and Squared Values for set 17
                                            $residualEasting17a = ($sumValue11a - $vii_v_b_1) * 1000;
                                            $residualEasting17b = ($sumValue12a - $vii_v_b_2) * 1000;
                                            $residualEasting17c = ($sumValue13a - $vii_v_b_3) * 1000;
                                            $squaredValue17a = $residualEasting17a ** 2;
                                            $squaredValue17b = $residualEasting17b ** 2;
                                            $squaredValue17c = $residualEasting17c ** 2;
          
                                            // Residual Easting and Squared Values for set 18
                                            $residualEasting18a = ($sumValue14a - $viii_v_b_1) * 1000;
                                            $residualEasting18b = ($sumValue15a - $viii_v_b_2) * 1000;
                                            $residualEasting18c = ($sumValue16a - $viii_v_b_3) * 1000;
                                            $squaredValue18a = $residualEasting18a ** 2;
                                            $squaredValue18b = $residualEasting18b ** 2;
                                            $squaredValue18c = $residualEasting18c ** 2;
          
                                            // Residual Easting and Squared Values for set 19
                                            $residualEasting19a = ($sumValue11a - $xi_v_b_1) * 1000;
                                            $residualEasting19b = ($sumValue12a - $xi_v_b_2) * 1000;
                                            $residualEasting19c = ($sumValue13a - $xi_v_b_3) * 1000;
                                            $squaredValue19a = $residualEasting19a ** 2;
                                            $squaredValue19b = $residualEasting19b ** 2;
                                            $squaredValue19c = $residualEasting19c ** 2;
          
                                            // Residual Easting and Squared Values for set 20
                                            $residualEasting20a = ($sumValue14a - $x_v_b_1) * 1000;
                                            $residualEasting20b = ($sumValue15a - $x_v_b_2) * 1000;
                                            $residualEasting20c = ($sumValue16a - $x_v_b_3) * 1000;
                                            $squaredValue20a = $residualEasting20a ** 2;
                                            $squaredValue20b = $residualEasting20b ** 2;
                                            $squaredValue20c = $residualEasting20c ** 2;

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
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting11a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting11b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting11c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue11a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue11b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue11c, 3) . '</td>


                                            </tr>';

                                            $info_report .= '<tr style="color:#424242;">
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">12</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_v_b_1 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_v_b_2 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_v_b_3 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue1b . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue6b . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting12a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting12b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting12c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue12a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue12b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue12c, 3) . '</td>

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
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting13a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting13b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting13c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue13a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue13b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue13c, 3) . '</td>

                                            </tr>';

                                            $info_report .= '<tr style="color:#424242;">
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">14</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_v_b_1 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_v_b_2 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_v_b_3 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue2b . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue7b . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting14a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting14b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting14c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue14a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue14b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue14c, 3) . '</td>

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
                                               <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting15a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting15b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting15c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue15a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue15b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue15c, 3) . '</td>

                                            </tr>';

                                            $info_report .= '<tr style="color:#424242;">
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">16</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">3</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_v_b_1 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_v_b_2 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_v_b_3 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue3b . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue8b . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting16a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting16b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting16c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue16a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue16b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue16c, 3) . '</td>

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
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting17a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting17b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting17c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue17a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue17b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue17c, 3) . '</td>

                                            </tr>';

                                            $info_report .= '<tr style="color:#424242;">
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">18</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">4</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_v_b_1 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_v_b_2 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_v_b_3 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue4b . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue9b . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting18a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting18b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting18c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue18a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue18b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue18c, 3) . '</td>

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
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting19a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting19b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting19c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue19a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue19b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue19c, 3) . '</td>

                                            </tr>';

                                            $info_report .= '<tr style="color:#424242;">
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">20</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">5</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_v_b_1 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_v_b_2 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_v_b_3 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue5b . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue10b . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting20a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting20b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting20c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue20a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue20b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue20c, 3) . '</td>

                                            </tr>

                                            </thead>
                                            <tr style="color:#424242;">
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:red;" colspan="2"><b>STOP TIME: ' . (!empty($calibration_info->stop_time_2) ? date('H:i A', strtotime($calibration_info->stop_time_2)) : 'N/A') . '</b></td>                          
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:left; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                            </tr>
                                                    
                                        <tbody>
                                    </tbody>
                                </table>';

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
                                        <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">HD (m)</th>
                                        <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">EA (mm)</th>
                                        <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Eastings</th>
                                        <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Northings</th>
                                        <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Elevation</th>
                                        <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Eastings</th>
                                        <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Northings</th>
                                        <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Elevation</th>
                                </tr>
                                <tr style="color:#424242;">
                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:red;" colspan="2"><b>START TIME:' . (!empty($calibration_info->start_time_3) ? date('H:i A', strtotime($calibration_info->start_time_3)) : 'N/A') . '</b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:left; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                </tr>
                                ';

                                                        $i_v_c_1 = $calibration_info->i_v_c_1;
                                                        $i_v_c_2 = $calibration_info->i_v_c_2;
                                                        $i_v_c_3 = $calibration_info->i_v_c_3;
                                                        $ii_v_c_1 = $calibration_info->ii_v_c_1;
                                                        $ii_v_c_2 = $calibration_info->ii_v_c_2;
                                                        $ii_v_c_3 = $calibration_info->ii_v_c_3;
                                                        $iii_v_c_1 = $calibration_info->iii_v_c_1;
                                                        $iii_v_c_2 = $calibration_info->iii_v_c_2;
                                                        $iii_v_c_3 = $calibration_info->iii_v_c_3; 
                                                        $iv_v_c_1 = $calibration_info->iv_v_c_1;
                                                        $iv_v_c_2 = $calibration_info->iv_v_c_2;
                                                        $iv_v_c_3 = $calibration_info->iv_v_c_3;
                                                        $v_v_c_1 = $calibration_info->v_v_c_1;
                                                        $v_v_c_2 = $calibration_info->v_v_c_2;
                                                        $v_v_c_3 = $calibration_info->v_v_c_3; 
                                                        $vi_v_c_1 = $calibration_info->vi_v_c_1;
                                                        $vi_v_c_2 = $calibration_info->vi_v_c_2;
                                                        $vi_v_c_3 = $calibration_info->vi_v_c_3; 
                                                        $vii_v_c_1 = $calibration_info->vii_v_c_1;
                                                        $vii_v_c_2 = $calibration_info->vii_v_c_2;
                                                        $vii_v_c_3 = $calibration_info->vii_v_c_3;
                                                        $viii_v_c_1 = $calibration_info->viii_v_c_1;
                                                        $viii_v_c_2 = $calibration_info->viii_v_c_2;
                                                        $viii_v_c_3 = $calibration_info->viii_v_c_3;
                                                        $xi_v_c_1 = $calibration_info->xi_v_c_1;
                                                        $xi_v_c_2 = $calibration_info->xi_v_c_2;
                                                        $xi_v_c_3 = $calibration_info->xi_v_c_3; 
                                                        $x_v_c_1 = $calibration_info->x_v_c_1;
                                                        $x_v_c_2 = $calibration_info->x_v_c_2;
                                                        $x_v_c_3 = $calibration_info->x_v_c_3;

                                                        $r_v_a_10 = $calibration_info->r_v_a_10;

                                                        $sumValue1c = round(sqrt(pow(($i_v_c_1 - $ii_v_c_1), 2) + pow(($i_v_c_2 - $ii_v_c_2), 2)), 4);
                                                        $sumValue2c = round(sqrt(pow(($iii_v_c_1 - $iv_v_c_1), 2) + pow(($iii_v_c_2 - $iv_v_c_2), 2)), 4);
                                                        $sumValue3c = round(sqrt(pow(($v_v_c_1 - $vi_v_c_1), 2) + pow(($v_v_c_2 - $vi_v_c_2), 2)), 4);
                                                        $sumValue4c = round(sqrt(pow(($vii_v_c_1 - $viii_v_c_1), 2) + pow(($vii_v_c_2 - $viii_v_c_2), 2)), 4);
                                                        $sumValue5c = round(sqrt(pow(($xi_v_c_1 - $x_v_c_1), 2) + pow(($xi_v_c_2 - $x_v_c_2), 2)), 4);

                                                        $sumValue6c = round($ii_v_c_3 - $i_v_c_3, 4);
                                                        $sumValue7c = round($iv_v_c_3 - $iii_v_c_3, 4);
                                                        $sumValue8c = round($vi_v_c_3 - $v_v_c_3, 4);
                                                        $sumValue9c = round($viii_v_c_3 - $vii_v_c_3, 4);
                                                        $sumValue10c = round($x_v_c_3 - $xi_v_c_3, 4);

                                                        $sumValue11a = round(($i_v_a_1 + $iii_v_a_1 + $v_v_a_1 + $vii_v_a_1 + $xi_v_a_1 + $i_v_b_1 + $iii_v_b_1 + $v_v_b_1 + $vii_v_b_1 + $xi_v_b_1 + $i_v_c_1 + $iii_v_c_1 + $v_v_c_1 + $vii_v_c_1 + $xi_v_c_1) / 15, 3);
                                                        $sumValue12a = round(($i_v_a_2 + $iii_v_a_2 + $v_v_a_2 + $vii_v_a_2 + $xi_v_a_2 + $i_v_b_2 + $iii_v_b_2 + $v_v_b_2 + $vii_v_b_2 + $xi_v_b_2 + $i_v_c_2 + $iii_v_c_2 + $v_v_c_2 + $vii_v_c_2 + $xi_v_c_2) / 15, 3);
                                                        $sumValue13a = round(($i_v_a_3 + $iii_v_a_3 + $v_v_a_3 + $vii_v_a_3 + $xi_v_a_3 + $i_v_b_3 + $iii_v_b_3 + $v_v_b_3 + $vii_v_b_3 + $xi_v_b_3 + $i_v_c_3 + $iii_v_c_3 + $v_v_c_3 + $vii_v_c_3 + $xi_v_c_3) / 15, 3);
                                                        
                                                        $sumValue14a = round(($ii_v_a_1 + $iv_v_a_1 + $vi_v_a_1 + $viii_v_a_1 + $x_v_a_1 + $ii_v_b_1 + $iv_v_b_1 + $vi_v_b_1 + $viii_v_b_1 + $x_v_b_1 + $ii_v_c_1 + $iv_v_c_1 + $vi_v_c_1 + $viii_v_c_1 + $x_v_c_1) / 15, 3);
                                                        $sumValue15a = round(($ii_v_a_2 + $iv_v_a_2 + $vi_v_a_2 + $viii_v_a_2 + $x_v_a_2 + $ii_v_a_2 + $iv_v_a_2 + $vi_v_a_2 + $viii_v_a_2 + $x_v_a_2 + $ii_v_c_2 + $iv_v_c_2 + $vi_v_c_2 + $viii_v_c_2 + $x_v_c_2) / 15, 3);
                                                        $sumValue16a = round(($ii_v_a_3 + $iv_v_a_3 + $vi_v_a_3 + $viii_v_a_3 + $x_v_a_3 + $ii_v_b_3 + $iv_v_b_3 + $vi_v_b_3 + $viii_v_b_3 + $x_v_b_3 + $ii_v_c_3 + $iv_v_c_3 + $vi_v_c_3 + $viii_v_c_3 + $x_v_c_3) / 15, 3);

                                                        $sumValue17 = round($sumValue1a + $sumValue2a + $sumValue3a + $sumValue4a + $sumValue5a + $sumValue1b + $sumValue2b + $sumValue3b + $sumValue4b + $sumValue5b + $sumValue1c + $sumValue2c + $sumValue3c + $sumValue4c + $sumValue5c, 2);
                                                        $sumValue18 = round($sumValue6a + $sumValue7a + $sumValue8a + $sumValue9a + $sumValue10a + $sumValue6b + $sumValue7b + $sumValue8b + $sumValue9b + $sumValue10b + $sumValue6c + $sumValue7c + $sumValue8c + $sumValue9c + $sumValue10c, 2);

                                                        $sumValue19 = round(($sumValue17) / 15, 3);
                                                        $sumValue20 = round(($sumValue18) / 15, 3);

                                                        // Determine the background color based on the pass/fail status
                                                                $background_color = (
                                                                (abs($sumValue19 - $r_v_a_9) <= 0.003) &&
                                                                (abs($sumValue20 - $r_v_a_10) <= 0.003)
                                                                ) ? 'lightgreen' : 'rgba(255, 0, 0, 0.3)';


                                                        // Comparison logic
                                                        // $horizontal_distance_pass = (abs($sumValue19 - $r_v_a_9) <= 0.003) ? 'PASSED' : 'FAILED';

                                                        $horizontal_distance_pass = (
                                                            (abs($sumValue19 - $r_v_a_9) <= 0.003) &&
                                                            (abs($sumValue20 - $r_v_a_10) <= 0.003)
                                                        ) ? 'PASSED' : 'FAILED';

                                                        session_start();  // Start the session
                                                        $_SESSION['sumValue11a'] = $sumValue11a;
                                                        $_SESSION['sumValue12a'] = $sumValue12a;
                                                        $_SESSION['sumValue13a'] = $sumValue13a;
                                                        $_SESSION['sumValue14a'] = $sumValue14a;
                                                        $_SESSION['sumValue15a'] = $sumValue15a;
                                                        $_SESSION['sumValue16a'] = $sumValue16a;
                                                    
                                                        
                                                        // Residual Easting and Squared Values for set 21
                                                        $residualEasting21a = ($sumValue11a - $i_v_c_1) * 1000;
                                                        $residualEasting21b = ($sumValue12a - $i_v_c_2) * 1000;
                                                        $residualEasting21c = ($sumValue13a - $i_v_c_3) * 1000;
                                                        $squaredValue21a = $residualEasting21a ** 2;
                                                        $squaredValue21b = $residualEasting21b ** 2;
                                                        $squaredValue21c = $residualEasting21c ** 2;

                                                        // Residual Easting and Squared Values for set 22
                                                        $residualEasting22a = ($sumValue14a - $ii_v_c_1) * 1000;
                                                        $residualEasting22b = ($sumValue15a - $ii_v_c_2) * 1000;
                                                        $residualEasting22c = ($sumValue16a - $ii_v_c_3) * 1000;
                                                        $squaredValue22a = $residualEasting22a ** 2;
                                                        $squaredValue22b = $residualEasting22b ** 2;
                                                        $squaredValue22c = $residualEasting22c ** 2;

                                                        // Residual Easting and Squared Values for set 23
                                                        $residualEasting23a = ($sumValue11a - $iii_v_c_1) * 1000;
                                                        $residualEasting23b = ($sumValue12a - $iii_v_c_2) * 1000;
                                                        $residualEasting23c = ($sumValue13a - $iii_v_c_3) * 1000;
                                                        $squaredValue23a = $residualEasting23a ** 2;
                                                        $squaredValue23b = $residualEasting23b ** 2;
                                                        $squaredValue23c = $residualEasting23c ** 2;

                                                        // Residual Easting and Squared Values for set 24
                                                        $residualEasting24a = ($sumValue14a - $iv_v_c_1) * 1000;
                                                        $residualEasting24b = ($sumValue15a - $iv_v_c_2) * 1000;
                                                        $residualEasting24c = ($sumValue16a - $iv_v_c_3) * 1000;
                                                        $squaredValue24a = $residualEasting24a ** 2;
                                                        $squaredValue24b = $residualEasting24b ** 2;
                                                        $squaredValue24c = $residualEasting24c ** 2;

                                                        // Residual Easting and Squared Values for set 25
                                                        $residualEasting25a = ($sumValue11a - $v_v_c_1) * 1000;
                                                        $residualEasting25b = ($sumValue12a - $v_v_c_2) * 1000;
                                                        $residualEasting25c = ($sumValue13a - $v_v_c_3) * 1000;
                                                        $squaredValue25a = $residualEasting25a ** 2;
                                                        $squaredValue25b = $residualEasting25b ** 2;
                                                        $squaredValue25c = $residualEasting25c ** 2;

                                                        // Residual Easting and Squared Values for set 26
                                                        $residualEasting26a = ($sumValue14a - $vi_v_c_1) * 1000;
                                                        $residualEasting26b = ($sumValue15a - $vi_v_c_2) * 1000;
                                                        $residualEasting26c = ($sumValue16a - $vi_v_c_3) * 1000;
                                                        $squaredValue26a = $residualEasting26a ** 2;
                                                        $squaredValue26b = $residualEasting26b ** 2;
                                                        $squaredValue26c = $residualEasting26c ** 2;

                                                        // Residual Easting and Squared Values for set 27
                                                        $residualEasting27a = ($sumValue11a - $vii_v_c_1) * 1000;
                                                        $residualEasting27b = ($sumValue12a - $vii_v_c_2) * 1000;
                                                        $residualEasting27c = ($sumValue13a - $vii_v_c_3) * 1000;
                                                        $squaredValue27a = $residualEasting27a ** 2;
                                                        $squaredValue27b = $residualEasting27b ** 2;
                                                        $squaredValue27c = $residualEasting27c ** 2;

                                                        // Residual Easting and Squared Values for set 28
                                                        $residualEasting28a = ($sumValue14a - $viii_v_c_1) * 1000;
                                                        $residualEasting28b = ($sumValue15a - $viii_v_c_2) * 1000;
                                                        $residualEasting28c = ($sumValue16a - $viii_v_c_3) * 1000;
                                                        $squaredValue28a = $residualEasting28a ** 2;
                                                        $squaredValue28b = $residualEasting28b ** 2;
                                                        $squaredValue28c = $residualEasting28c ** 2;

                                                        // Residual Easting and Squared Values for set 29
                                                        $residualEasting29a = ($sumValue11a - $xi_v_c_1) * 1000;
                                                        $residualEasting29b = ($sumValue12a - $xi_v_c_2) * 1000;
                                                        $residualEasting29c = ($sumValue13a - $xi_v_c_3) * 1000;
                                                        $squaredValue29a = $residualEasting29a ** 2;
                                                        $squaredValue29b = $residualEasting29b ** 2;
                                                        $squaredValue29c = $residualEasting29c ** 2;

                                                        // Residual Easting and Squared Values for set 30
                                                        $residualEasting30a = ($sumValue14a - $x_v_c_1) * 1000;
                                                        $residualEasting30b = ($sumValue15a - $x_v_c_2) * 1000;
                                                        $residualEasting30c = ($sumValue16a - $x_v_c_3) * 1000;
                                                        $squaredValue30a = $residualEasting30a ** 2;
                                                        $squaredValue30b = $residualEasting30b ** 2;
                                                        $squaredValue30c = $residualEasting30c ** 2;

                                                        // Sum of squared values from $squaredValue1a up to $squaredValue30a
                                                        $sumSquaredValues1A = $squaredValue1a + $squaredValue2a + $squaredValue3a + $squaredValue4a + $squaredValue5a +
                                                                            $squaredValue6a + $squaredValue7a + $squaredValue8a + $squaredValue9a + $squaredValue10a +
                                                                            $squaredValue11a + $squaredValue12a + $squaredValue13a + $squaredValue14a + $squaredValue15a +
                                                                            $squaredValue16a + $squaredValue17a + $squaredValue18a + $squaredValue19a + $squaredValue20a +
                                                                            $squaredValue21a + $squaredValue22a + $squaredValue23a + $squaredValue24a + $squaredValue25a +
                                                                            $squaredValue26a + $squaredValue27a + $squaredValue28a + $squaredValue29a + $squaredValue30a;

                                                        // Sum of squared values from $squaredValue1b up to $squaredValue30b
                                                        $sumSquaredValues1B = $squaredValue1b + $squaredValue2b + $squaredValue3b + $squaredValue4b + $squaredValue5b +
                                                                            $squaredValue6b + $squaredValue7b + $squaredValue8b + $squaredValue9b + $squaredValue10b +
                                                                            $squaredValue11b + $squaredValue12b + $squaredValue13b + $squaredValue14b + $squaredValue15b +
                                                                            $squaredValue16b + $squaredValue17b + $squaredValue18b + $squaredValue19b + $squaredValue20b +
                                                                            $squaredValue21b + $squaredValue22b + $squaredValue23b + $squaredValue24b + $squaredValue25b +
                                                                            $squaredValue26b + $squaredValue27b + $squaredValue28b + $squaredValue29b + $squaredValue30b;

                                                        // Sum of squared values from $squaredValue1c up to $squaredValue30c
                                                        $sumSquaredValues1C = $squaredValue1c + $squaredValue2c + $squaredValue3c + $squaredValue4c + $squaredValue5c +
                                                                            $squaredValue6c + $squaredValue7c + $squaredValue8c + $squaredValue9c + $squaredValue10c +
                                                                            $squaredValue11c + $squaredValue12c + $squaredValue13c + $squaredValue14c + $squaredValue15c +
                                                                            $squaredValue16c + $squaredValue17c + $squaredValue18c + $squaredValue19c + $squaredValue20c +
                                                                            $squaredValue21c + $squaredValue22c + $squaredValue23c + $squaredValue24c + $squaredValue25c +
                                                                            $squaredValue26c + $squaredValue27c + $squaredValue28c + $squaredValue29c + $squaredValue30c;

                                                    $squaredeastingresult1 = sqrt($sumSquaredValues1A / 28);
                                                    $squaredeastingresult2 = sqrt($sumSquaredValues1B / 28);
                                                    $squaredeastingresult3 = sqrt($sumSquaredValues1C / 28);

                                                    // Calculate the result
                                                    $standarddeviationresult1 = sqrt(pow($squaredeastingresult1, 2) + pow($squaredeastingresult2, 2));


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
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting21a, 3) . '</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting21b, 3) . '</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting21c, 3) . '</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue21a, 3) . '</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue21b, 3) . '</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue21c, 3) . '</td>

                                                </tr>';

                                                $info_report .= '<tr style="color:#424242;">
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">22</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_v_c_1 . '</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_v_c_2 . '</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_v_c_3 . '</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue1c . '</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue6c . '</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting22a, 3) . '</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting22b, 3) . '</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting22c, 3) . '</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue22a, 3) . '</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue22b, 3) . '</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue22c, 3) . '</td>


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
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting23a, 3) . '</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting23b, 3) . '</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting23c, 3) . '</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue23a, 3) . '</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue23b, 3) . '</td>
                                                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue23c, 3) . '</td>

                                                </tr>';

                                                $info_report .= '<tr style="color:#424242;">
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">24</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_v_c_1 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_v_c_2 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $iv_v_c_3 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue2c . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue7c . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting24a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting24b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting24c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue24a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue24b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue24c, 3) . '</td>

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
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting25a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting25b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting25c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue25a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue25b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue25c, 3) . '</td>


                                                </tr>';

                                                $info_report .= '<tr style="color:#424242;">
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">26</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">3</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_v_c_1 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_v_c_2 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $vi_v_c_3 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue3c . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue8c . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting26a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting26b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting26c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue26a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue26b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue26c, 3) . '</td>

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
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting27a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting27b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting27c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue27a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue27b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue27c, 3) . '</td>

                                                </tr>';

                                                $info_report .= '<tr style="color:#424242;">
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">28</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">4</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_v_c_1 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_v_c_2 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $viii_v_c_3 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue4c . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue9c . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting28a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting28b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting28c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue28a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue28b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue28c, 3) . '</td>

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
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting29a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting29b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting29c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue29a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue29b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue29c, 3) . '</td>


                                                </tr>';

                                                $info_report .= '<tr style="color:#424242;">
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">30</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">5</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_v_c_1 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_v_c_2 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $x_v_c_3 . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue5c . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue10c . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting30a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting30b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($residualEasting30c, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue30a, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue30b, 3) . '</td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredValue30c, 3) . '</td>

                                                </tr>
                                                </thead>
                                                <tr style="color:#424242;">
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:red;" colspan="2"><b>STOP TIME:  ' . (!empty($calibration_info->stop_time_3) ? date('H:i A', strtotime($calibration_info->stop_time_3)) : 'N/A') . '</b></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:left; color:maroon;" colspan=""><b></b></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b>SUM</b></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b>SUM</b></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b>SUM</b></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b>' . round($sumSquaredValues1A, 4) . '</b></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b>' . round($sumSquaredValues1B, 4) . '</b></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b>' . round($sumSquaredValues1C, 4) . '</b></td>
                                                </tr>';

                                                $info_report .= '<tr style="color:#424242; background-color: lightgrey; font-weight: bold;">
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Averaged</td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . ($sumValue11a) . '</td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . ($sumValue12) . '</td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . ($sumValue13) . '</td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredeastingresult1, 4) . '</td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredeastingresult2, 4) . '</td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredeastingresult3, 4) . '</td>

                                                </tr>';

                                                $info_report .= '<tr style="color:#424242; background-color: lightgrey; font-weight: bold;">
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . ($sumValue14) . '</td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . ($sumValue15) . '</td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . ($sumValue16) . '</td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . ($sumValue17) . '</td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($sumValue18, 4) . '</td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>

                                                </tr>';

                                                $info_report .= '<tr style="color:#424242; background-color: lightgrey; font-weight: bold;">
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td colspan="4" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:right;">Horizontal Distance/Elevation Accuracy</td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($sumValue19, 4) . '</td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($sumValue20, 4) . '</td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Standard Deviation</td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;" colspan="2">' . round($standarddeviationresult1, 4) . '</td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($squaredeastingresult3, 4) . '</td>

                                                </tr>';

                                                $info_report .= '<tr style="color:#424242; background-color: lightgrey; font-weight: bold;">   
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td colspan="5" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                                                    <td colspan="2" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; background-color: lightgreen;">Passed</td>
                                                </tr>
                                                <tbody>
                                                </tbody>
                                                </table><br><br><br><hr>';






                                                $info_report .= '<table border="0" cellpadding="5">
                                                <tr style="color:#424242;">
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; background-color: lightgrey; border:1px solid #000; text-align:left; color:maroon;" colspan="6"><b>POST-CALIBRATION CHECKS</b></td>
                                                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; background-color: lightgrey; border:1px solid #000; text-align:center; color:maroon;" colspan="6"><b>MEASUREMENTS</b></td>
                                                </tr>
                                                <thead>';
                                                $info_report .= '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . (get_option('pdf_font_size') + 4) . 'px;">
                                                    <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Series</th>
                                                    <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Seq. No.</th>
                                                    <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Set</th>
                                                    <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Rover Point</th>
                                                    <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Eastings</th>
                                                    <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Northings</th>
                                                    <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Elevation</th>
                                                    <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">HD (m)</th>
                                                    <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">EA (mm)</th>
                                                    <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Eastings</th>
                                                    <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Northings</th>
                                                    <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Elevation</th>
                                                    <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Eastings</th>
                                                    <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Northings</th>
                                                    <th style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Elevation</th>
                                                </tr>
                                                <tr style="color:#424242;">
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-left:center; color:red;" colspan="2"><b>START TIME:' . (!empty($calibration_info->start_time_4) ? date('H:i A', strtotime($calibration_info->start_time_4)) : 'N/A') . '</b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:left; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                                                </tr>
                                                ';
                                                
              

                    $info_report .= '<tr style="color:#424242;">
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($i_v_aa_1, 3) . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_v_aa_2 . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $i_v_aa_3 . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>

                        </tr>';

                    $info_report .= '<tr style="color:#424242;">
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">1</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">2</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_v_aa_1 . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_v_aa_2 . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $ii_v_aa_3 . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue1aa . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue6aa . '</td>

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
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue2a . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue7a . '</td>
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
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue3a . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue8a . '</td>
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
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue4a . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue9a . '</td>
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
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue5a . '</td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue10a . '</td>
                        </tr>
                        <tr style="color:#424242;">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:red;" colspan="2"><b>STOP TIME:  ' . (!empty($calibration_info->stop_time_4) ? date('H:i A', strtotime($calibration_info->stop_time_4)) : 'N/A') . '</b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:left; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                        </tr>
                        <tr style="color:#424242;">
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:red;" colspan="2"><b>START TIME:  ' . (!empty($calibration_info->start_time_5) ? date('H:i A', strtotime($calibration_info->start_time_5)) : 'N/A') . '</b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:left; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>
                            <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan=""><b></b></td>

                        </tr><br>';

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

                            $sumValue1b = round(sqrt(pow(($i_v_b_1 - $ii_v_b_1), 2) + pow(($i_v_b_2 - $ii_v_b_2), 2)), 4);
                            $sumValue2b = round(sqrt(pow(($iii_v_b_1 - $iv_v_b_1), 2) + pow(($iii_v_b_2 - $iv_v_b_2), 2)), 4);
                            $sumValue3b = round(sqrt(pow(($v_v_b_1 - $vi_v_b_1), 2) + pow(($v_v_b_2 - $vi_v_b_2), 2)), 4);
                            $sumValue4b = round(sqrt(pow(($vii_v_b_1 - $viii_v_b_1), 2) + pow(($vii_v_b_2 - $viii_v_b_2), 2)), 4);
                            $sumValue5b = round(sqrt(pow(($xi_v_b_1 - $x_v_b_1), 2) + pow(($xi_v_b_2 - $x_v_b_2), 2)), 4);

                            $sumValue6b = round($ii_v_b_3 - $i_v_b_3, 4);
                            $sumValue7b = round($iv_v_b_3 - $iii_v_b_3, 4);
                            $sumValue8b = round($vi_v_b_3 - $v_v_b_3, 4);
                            $sumValue9b = round($viii_v_b_3 - $vii_v_b_3, 4);
                            $sumValue10b = round($x_v_b_3 - $xi_v_b_3, 4);

                            $sumValue11 = round($i_v_b_1 + $iii_v_b_1 + $v_v_b_1 + $vii_v_b_1 + $xi_v_b_1) / 5;
                            $sumValue12 = round($i_v_b_2 + $iii_v_b_2 + $v_v_b_2 + $vii_v_b_2 + $xi_v_b_2) / 5;
                            $sumValue13 = round($i_v_b_3 + $iii_v_b_3 + $v_v_b_3 + $vii_v_b_3 + $xi_v_b_3) / 5;

                            $sumValue14 = round($ii_v_b_1 + $iv_v_b_1 + $vi_v_b_1 + $viii_v_b_1 + $x_v_b_1) / 5;
                            $sumValue15 = round($ii_v_a_2 + $iv_v_a_2 + $vi_v_a_2 + $viii_v_a_2 + $x_v_a_2) / 5;
                            $sumValue16 = round($ii_v_b_3 + $iv_v_b_3 + $vi_v_b_3 + $viii_v_b_3 + $x_v_b_3) / 5;

                            $sumValue17 = round(($sumValue1b + $sumValue2b + $sumValue3b + $sumValue4b + $sumValue5b)/ 5, 3);
                            $sumValue18 = round(($sumValue6b + $sumValue7b + $sumValue8b + $sumValue9b + $sumValue10b) / 5, 3);

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
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue1b . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue6b . '</td>
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
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue2b . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue7b . '</td>
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
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue3b . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue8b . '</td>
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
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue4b . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue9b . '</td>
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
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue5b . '</td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue10b . '</td>
                        </tr>

                        </thead>
                        <tr style="color:#424242;">
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:red;" colspan="2"><b>STOP TIME:  ' . (!empty($calibration_info->stop_time_5) ? date('H:i A', strtotime($calibration_info->stop_time_5)) : 'N/A') . '</b></td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan="2"><b></b></td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                        <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                        </tr>

                        <tbody>
                        </tbody>
                        </table>';

            $info_report .= '<table border="0" cellpadding="5">             
                text-align:center;">' . $sumValue5c . '</td>
                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue10c . '</td>
                </tr>
                </thead>
                <tr style="color:#424242;">
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:red;" colspan="2"><b>STOP TIME:  ' . (!empty($calibration_info->stop_time_6) ? date('H:i A', strtotime($calibration_info->stop_time_6)) : 'N/A') . '</b></td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid<thead>';
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
                </tr>
                <tr style="color:#424242;">
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:red;" colspan="2"><b>START TIME:  ' . (!empty($calibration_info->start_time_6) ? date('H:i A', strtotime($calibration_info->start_time_6)) : 'N/A') . '</b></td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan="2"><b></b></td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                </tr>
                ';

                $i_v_c_1 = $calibration_info->i_v_c_1;
                $i_v_c_2 = $calibration_info->i_v_c_2;
                $i_v_c_3 = $calibration_info->i_v_c_3;
                $ii_v_c_1 = $calibration_info->ii_v_c_1;
                $ii_v_c_2 = $calibration_info->ii_v_c_2;
                $ii_v_c_3 = $calibration_info->ii_v_c_3;
                $iii_v_c_1 = $calibration_info->iii_v_c_1;
                $iii_v_c_2 = $calibration_info->iii_v_c_2;
                $iii_v_c_3 = $calibration_info->iii_v_c_3; 
                $iv_v_c_1 = $calibration_info->iv_v_c_1;
                $iv_v_c_2 = $calibration_info->iv_v_c_2;
                $iv_v_c_3 = $calibration_info->iv_v_c_3;
                $v_v_c_1 = $calibration_info->v_v_c_1;
                $v_v_c_2 = $calibration_info->v_v_c_2;
                $v_v_c_3 = $calibration_info->v_v_c_3; 
                $vi_v_c_1 = $calibration_info->vi_v_c_1;
                $vi_v_c_2 = $calibration_info->vi_v_c_2;
                $vi_v_c_3 = $calibration_info->vi_v_c_3; 
                $vii_v_c_1 = $calibration_info->vii_v_c_1;
                $vii_v_c_2 = $calibration_info->vii_v_c_2;
                $vii_v_c_3 = $calibration_info->vii_v_c_3;
                $viii_v_c_1 = $calibration_info->viii_v_c_1;
                $viii_v_c_2 = $calibration_info->viii_v_c_2;
                $viii_v_c_3 = $calibration_info->viii_v_c_3;
                $xi_v_c_1 = $calibration_info->xi_v_c_1;
                $xi_v_c_2 = $calibration_info->xi_v_c_2;
                $xi_v_c_3 = $calibration_info->xi_v_c_3; 
                $x_v_c_1 = $calibration_info->x_v_c_1;
                $x_v_c_2 = $calibration_info->x_v_c_2;
                $x_v_c_3 = $calibration_info->x_v_c_3;

                $sumValue1c = round(sqrt(pow(($i_v_c_1 - $ii_v_c_1), 2) + pow(($i_v_c_2 - $ii_v_c_2), 2)), 4);
                $sumValue2c = round(sqrt(pow(($iii_v_c_1 - $iv_v_c_1), 2) + pow(($iii_v_c_2 - $iv_v_c_2), 2)), 4);
                $sumValue3c = round(sqrt(pow(($v_v_c_1 - $vi_v_c_1), 2) + pow(($v_v_c_2 - $vi_v_c_2), 2)), 4);
                $sumValue4c = round(sqrt(pow(($vii_v_c_1 - $viii_v_c_1), 2) + pow(($vii_v_c_2 - $viii_v_c_2), 2)), 4);
                $sumValue5c = round(sqrt(pow(($xi_v_c_1 - $x_v_c_1), 2) + pow(($xi_v_c_2 - $x_v_c_2), 2)), 4);

                $sumValue6c = round($ii_v_c_3 - $i_v_c_3, 4);
                $sumValue7c = round($iv_v_c_3 - $iii_v_c_3, 4);
                $sumValue8c = round($vi_v_c_3 - $v_v_c_3, 4);
                $sumValue9c = round($viii_v_c_3 - $vii_v_c_3, 4);
                $sumValue10c = round($x_v_c_3 - $xi_v_c_3, 4);

                $sumValue11 = round(($i_v_a_1 + $iii_v_a_1 + $v_v_a_1 + $vii_v_a_1 + $xi_v_a_1 + $i_v_b_1 + $iii_v_b_1 + $v_v_b_1 + $vii_v_b_1 + $xi_v_b_1 + $i_v_c_1 + $iii_v_c_1 + $v_v_c_1 + $vii_v_c_1 + $xi_v_c_1) / 15, 2);
                $sumValue12 = round(($i_v_a_2 + $iii_v_a_2 + $v_v_a_2 + $vii_v_a_2 + $xi_v_a_2 + $i_v_b_2 + $iii_v_b_2 + $v_v_b_2 + $vii_v_b_2 + $xi_v_b_2 + $i_v_c_2 + $iii_v_c_2 + $v_v_c_2 + $vii_v_c_2 + $xi_v_c_2) / 15, 2);
                $sumValue13 = round(($i_v_a_3 + $iii_v_a_3 + $v_v_a_3 + $vii_v_a_3 + $xi_v_a_3 + $i_v_b_3 + $iii_v_b_3 + $v_v_b_3 + $vii_v_b_3 + $xi_v_b_3 + $i_v_c_3 + $iii_v_c_3 + $v_v_c_3 + $vii_v_c_3 + $xi_v_c_3) / 15, 2);
                
                $sumValue14 = round(($ii_v_a_1 + $iv_v_a_1 + $vi_v_a_1 + $viii_v_a_1 + $x_v_a_1 + $ii_v_b_1 + $iv_v_b_1 + $vi_v_b_1 + $viii_v_b_1 + $x_v_b_1 + $ii_v_c_1 + $iv_v_c_1 + $vi_v_c_1 + $viii_v_c_1 + $x_v_c_1) / 15, 2);
                $sumValue15 = round(($ii_v_a_2 + $iv_v_a_2 + $vi_v_a_2 + $viii_v_a_2 + $x_v_a_2 + $ii_v_a_2 + $iv_v_a_2 + $vi_v_a_2 + $viii_v_a_2 + $x_v_a_2 + $ii_v_c_2 + $iv_v_c_2 + $vi_v_c_2 + $viii_v_c_2 + $x_v_c_2) / 15, 2);
                $sumValue16 = round(($ii_v_a_3 + $iv_v_a_3 + $vi_v_a_3 + $viii_v_a_3 + $x_v_a_3 + $ii_v_b_3 + $iv_v_b_3 + $vi_v_b_3 + $viii_v_b_3 + $x_v_b_3 + $ii_v_c_3 + $iv_v_c_3 + $vi_v_c_3 + $viii_v_c_3 + $x_v_c_3) / 15, 2);

                $sumValue17 = round(($sumValue1a + $sumValue2a + $sumValue3a + $sumValue4a + $sumValue5a + $sumValue1b + $sumValue2b + $sumValue3b + $sumValue4b + $sumValue5b + $sumValue1c + $sumValue2c + $sumValue3c + $sumValue4c + $sumValue5c) / 15, 3);
                $sumValue18 = round(($sumValue6a + $sumValue7a + $sumValue8a + $sumValue9a + $sumValue10a + $sumValue6b + $sumValue7b + $sumValue8b + $sumValue9b + $sumValue10b + $sumValue6c + $sumValue7c + $sumValue8c + $sumValue9c + $sumValue10c) / 15, 3);

                $sumValue19 = round(($sumValue17) / 15, 3);
                $sumValue20 = round(($sumValue18) / 15, 3);


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
                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue1c . '</td>
                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue6c . '</td>
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
                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue2c . '</td>
                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue7c . '</td>
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
                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue3c . '</td>
                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue8c . '</td>
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
                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue4c . '</td>
                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . $sumValue9c . '</td>
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
                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;  #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000;text-align:center; color:maroon;" colspan="2"><b></b></td>
                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; color:maroon;" colspan="2"><b></b></td>
                </tr>';

                $info_report .= '<tr style="color:#424242; background-color: lightgrey; font-weight: bold;">
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">Averaged</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . ($sumValue11) . '</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . ($sumValue12) . '</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . ($sumValue13) . '</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>

                </tr>';

                $info_report .= '<tr style="color:#424242; background-color: lightgrey; font-weight: bold;">
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

                $info_report .= '<tr style="color:#424242; background-color: lightgrey; font-weight: bold;">
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                    <td colspan="4" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:right;">Horizontal Distance/Elevation Accuracy</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($sumValue19, 4) . '</td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;">' . round($sumValue20, 4) . '</td>
                </tr>';

                $info_report .= '<tr style="color:#424242; background-color: lightgrey; font-weight: bold;">   
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                    <td style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                    <td colspan="5" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center;"></td>
                    <td colspan="2" style="vertical-align: middle; font-size: ' . ($font_size + 2) . 'px; border:1px solid #000; text-align:center; background-color: lightgreen;">Passed</td>
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
            <td style="font-size:' . ($font_size + 2) . 'px; font-weight:bold;">
                OBSERVER: (name) .............................................................. (sign) .......................................................................
            </td>
            </tr>
            <tr>
                <td style="font-size:' . ($font_size + 2) . 'px; font-weight:bold;">
                    DATE: ............./............../20............
                </td>
            </tr>
            <tr>
                  <td style="font-size:' . ($font_size + 2) . 'px; font-weight:bold;">
                      SERVICE ENGINEER: (name) .............................................................. (sign) .....................................................
                  </td>
              </tr>
              <tr>
                  <td style="font-size:' . ($font_size + 2) . 'px; font-weight:bold;">
                      DATE: ............./............../20............
                  </td>
              </tr>
              <tr>
                  <td style="font-size:' . ($font_size + 2) . 'px; font-weight:bold;">
                      GENERAL ENGINEER: (name) .............................................................. (sign) .....................................................
                  </td>
              </tr>
              <tr>
                  <td style="font-size:' . ($font_size + 2) . 'px; font-weight:bold;">
                      DATE: ............./............../20............
                  </td>
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