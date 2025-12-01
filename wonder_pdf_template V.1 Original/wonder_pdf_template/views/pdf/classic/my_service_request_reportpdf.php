<?php
$dimensions = $pdf->getPageDimensions();

// Tag - used in BULK pdf exporter
if ($tag != '') {
  $pdf->SetFillColor(240, 240, 240);
  $pdf->SetDrawColor(245, 245, 245);
  $pdf->SetXY(0, 0);
  $pdf->SetFont($font_name, 'B', 15);
  $pdf->SetTextColor(0);
  $pdf->SetLineWidth(0.75);
  $pdf->StartTransform();
  $pdf->Rotate(-35, 109, 235);
  $pdf->Cell(100, 1, mb_strtoupper($tag, 'UTF-8'), 'TB', 0, 'C', '1');
  $pdf->StopTransform();
  $pdf->SetFont($font_name, '', $font_size);
  $pdf->setX(10);
  $pdf->setY(10);
}

$font_size = get_option('pdf_font_size');
  if($font_size == ''){
      $font_size = 10;
  }

// top_header
$top_header = '
<table border="0" style="border-spacing:3px 3px;padding:3px 4px 3px 4px;border-bottom:2px ' . get_option('pdf_table_border_color') . ' solid; width:100%; margin-bottom:2px;">
<tbody>
<tr>
<td style="text-align:left; font-size: '.($font_size+4).'px; font-style: italic; word-wrap: normal;">“'. get_option('company_slogan') .'”</td>
<td style="text-align:right;"><h1 style="padding:0; margin:0; font-size:40px;">SERVICE REPORT</h1></td>
</tr>
</tbody>
</table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $top_header, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(6);

//logo area
$info_right_column = '';
$logo_area  = '';

$info_right_column .= '<b style="color:#4e4e4e;"># ' . $request_number . '</b>';
//dates
$info_right_column .= '<br>
<b style="color:#4e4e4e; font_size:12px;">Drop Off Date: ' . _d($service_request->drop_off_date).'
<br>Collection Date: ' . _d($service_request->collection_date).'
<br>Received By: ';
if ($service_request->received_by != 0) {
    if (get_option('show_sale_agent_on_invoices') == 1) {
        $info_right_column .= get_staff_full_name($service_request->received_by);
    }
}
$info_right_column .= '</b>';

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
<td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"><b>'.strtoupper("Company Info").'</b></td>
<td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"><b>' .strtoupper('Client Info') . '</b></td>';

$info_bill_shipping .= '</tr>
</thead>
<tbody>
<tr>
<td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';">';
$service_request_info = '<div style="color:#424242;">';
$service_request_info .= '<b style="color:black;">' . get_option('invoice_company_name') . '</b><br />';
$service_request_info .= get_option('invoice_company_address') . '<br/>';
if (get_option('invoice_company_city') != '') {
  $service_request_info .= get_option('invoice_company_city') . ', ';
}
$service_request_info .= get_option('company_state') . ' ';
$service_request_info .= get_option('invoice_company_postal_code') . '<br />';
$service_request_info .= get_option('invoice_company_country_code') . '';

if (get_option('invoice_company_phonenumber') != '') {
  $service_request_info .= '<br />' . get_option('invoice_company_phonenumber');
}
if (get_option('company_vat') != '') {
  $service_request_info .= '<br />'. _l('company_vat_number').': '.get_option('company_vat');
}
          // check for company custom fields
$custom_company_fields = get_company_custom_fields();
if (count($custom_company_fields) > 0) {
  $service_request_info .= '<br />';
}
foreach ($custom_company_fields as $field) {
  $service_request_info .= $field['label'] . ': ' . $field['value'] . '<br />';
}
$service_request_info .= '</div>';
$info_bill_shipping .= $service_request_info.'</td>';

$info_bill_shipping .= '<td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';">';
            // Bill to
$client_details = '<div style="color:#424242;">';
if ($service_request_client->show_primary_contact == 1) {
  $pc_id = get_primary_contact_user_id($service_request_client->userid);
  if ($pc_id) {
    $client_details .= get_contact_full_name($pc_id) .'<br />';
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
  'show_on_pdf' => 1
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
$info_bill_shipping .= $client_details.'</td>';


$info_bill_shipping .='</tr>
</tbody>
</table>';
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_bill_shipping, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(6);

//Instrument Info
$info_instrument = '
    <table border="0" cellpadding="5">
      <thead>
        <tr bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold;">
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"><b>TYPE</b></td>
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"><b>MAKE</b></td>
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"><b>MODEL</b></td>
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"><b>SERIAL</b></td>
        </tr>
      </thead>
      <tbody>  
        <tr style="color:#424242;">
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';">'.$service_request->item_type .'</td>
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';">'.$service_request->item_make .'</td>
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';">'.$service_request->item_model.'</td>
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';">'.$service_request->serial_no.'</td>
        </tr>
      </tbody>
    </table>';

    $pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_instrument, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
    $pdf->ln(6);

    //Report content Start 
    
$info_report = '';
  if($service_request->item_type == 'Level'){ 
    $info_report .= '<b style="font-size:12px; color:maroon;">COLLIMATION ERROR (TWO PEG TEST):</b><br><br>
    <table border="0" cellpadding="5">
      <thead>
        <tr bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold;">
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">SETUP</td>
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">BACKSIGHT A</td>
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">FORESIGHT B</td>
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">DIFF (A-B)</td>
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">ERROR</td>
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">CORRECTED<br> FORESIGHT B</td>
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
              $diff_i = $i_backsight_a-$i_foresight_b;
              $diff_ii = $ii_backsight_a-$ii_foresight_b;
              $diff_iii = $iii_backsight_a-$iii_foresight_b;
              $diff_iv = $iv_backsight_a-$iv_foresight_b;
              $error_ii = round($diff_i - $diff_ii, 3);
              $error_iv = round($diff_iii - $diff_iv, 3);
              $err1 = $ii_foresight_b + ($error_ii-$error_iv);
              $err2 = $iv_foresight_b + ($error_ii-$error_iv);
                            
            $info_report .= '<tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">I</td>
                                <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$i_backsight_a.'</td>
                                <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$i_foresight_b.'</td>
                                <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$diff_i.'</td>
                                <td class="desc"></td>
                                <td class="total"></td>
                            </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">II</td>
                                <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$ii_backsight_a.'</td>
                                <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$ii_foresight_b.'</td>
                                <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$diff_ii.'</td>
                                <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$error_ii.'</td>
                                <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$err1.'</td>
                            </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">III</td>
                                <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$iii_backsight_a.'</td>
                                <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$iii_foresight_b.'</td>
                                <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$diff_iii.'</td>
                                <td class="desc"></td>
                                <td class="total"></td>
                            </tr>
                            <tr style="color:#424242;">
                                <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">IV</td>
                                <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$iv_backsight_a.'</td>
                                <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$iv_foresight_b.'</td>
                                <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$diff_iv.'</td>
                                <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$error_iv.'</td>
                                <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$err2.'</td>
                            </tr>
      </tbody>
    </table>';
  } else if($service_request->item_type == 'Total Station' or $service_request->item_type == 'Theodolite'){
              $info_report .= '<table border="0" cellpadding="5">
                                          <tr style="color:#424242;">
                                              <td></td>
                                              <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center; color:maroon;" colspan="2"><b>HORIZONTAL CIRCLE INDEX ERROR</b></td>
                                              <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';text-align:center; color:maroon;" colspan="2"><b>VERTICAL CIRCLE INDEX ERROR</b></td>
                                              <td></td>
                                              <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center; color:maroon;" colspan="2"><b>EDM CHECKS</b></td>
                                          </tr>
                              <thead>
                                      
                                  <tr bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold;">
                                      <th style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">FACE</th>
                                      <th style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">START A</th>
                                      <th style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">END B</th>
                                      <th style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">START A</th>
                                      <th style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">END B</th>
                                      <th style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">FACE</th>
                                      <th style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">START A</th>
                                      <th style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">END B</th>
                                  </tr>
                              </thead>
                              <tbody>';
                                 
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
                                  
                  $info_report .= '<tr style="color:#424242;">
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;"><b>I</b></td>
                                      <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.dec2dms_full($i_h_a).'</td>
                                      <td  class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.dec2dms_full($i_h_b).'</td>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.dec2dms_full($i_v_a).'</td>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.dec2dms_full($i_v_b).'</td>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;" rowspan="3"><b>I</b></td>
                                      <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$i_edm_a_1.'</td>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$i_edm_b_1.'</td>
                                  </tr>
                                  <tr>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;"><b>II</b></td>
                                      <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.dec2dms_full($ii_h_a).'</td>
                                      <td  class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.dec2dms_full($ii_h_b).'</td>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.dec2dms_full($ii_v_a).'</td>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.dec2dms_full($ii_v_b).'</td>
                                      <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$i_edm_a_2.'</td>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$i_edm_b_2.'</td>
                                  </tr>
                                  <tr>
                                      <td></td>
                                      <td colspan="2" class="text-center total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;"><b>Half Circle is 180&deg; 00\' 00"</b></td>
                                      <td colspan="2" class="text-center" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;"><b>Half Circle is 360&deg; 00\' 00"</b></td>
                                      <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$i_edm_a_3.'</td>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$i_edm_b_3.'</td>
                                  </tr>
                                  <tr>
                                      <td class="desc"></td>
                                      <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.dec2dms_full($sum_h_a).'</td>
                                      <td  class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.dec2dms_full($sum_h_b).'</td>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.dec2dms_full($sum_v_a).'</td>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.dec2dms_full($sum_v_b).'</td>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;" rowspan="3"><b>II</b></td>
                                      <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$ii_edm_a_1.'</td>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$ii_edm_b_1.'</td>
                                  </tr>
                                  <tr>
                                      <td></td>
                                      <td colspan="2" class="text-center total"><b></b></td>
                                      <td colspan="2" class="text-center"><b></b></td>
                                      <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$ii_edm_a_2.'</td>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$ii_edm_b_2.'</td>
                                  </tr>
                                  <tr>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;"><b>DOUBLE ERROR</b></td>
                                      <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.dec2dms_full($d_error_h_a).'</td>
                                      <td  class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.dec2dms_full($d_error_h_b).'</td>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.dec2dms_full($d_error_v_a).'</td>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.dec2dms_full($d_error_v_b).'</td>
                                      <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$ii_edm_a_3.'</td>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.$ii_edm_b_3.'</td>
                                  </tr>
                                  <tr>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;"><b>INDEX ERROR (ACCEPTABLE)</b></td>
                                      <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">YES..... NO.....</td>
                                      <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">YES..... NO.....</td>
                                      <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">YES..... NO.....</td>
                                      <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">YES..... NO.....</td>
                                      <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;" colspan="3"></td>
                                  </tr>';
               $info_report .= ' <tr>
                                      <td class="no-border" rowspan="3" colspan="4">                                     
                                      </td>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:right;" colspan="2"><b>EDM FACE I AVERAGE</b></td>
                                      <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.round($f_i_edm_a,2).'</td>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.round($f_i_edm_b,2).'</td>
                                  </tr>
                                  <tr>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:right;" colspan="2"><b>EDM FACE II AVERAGE</b></td>
                                      <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.round($f_ii_edm_a,2).'</td>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.round($f_ii_edm_b,2).'</td>
                                  </tr>
                                  <tr>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:right;" colspan="2"><b>EDM FACE I&II MEAN</b></td>
                                      <td class="total" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . '; text-align:center;">'.round($f_i_ii_edm_a,2).'</td>
                                      <td class="desc" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';text-align:center;">'.round($f_i_ii_edm_b,2).'</td>
                                  </tr>
                              </tbody>
              </table>';
  }

  $pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_report, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
    $pdf->ln(6);

    //Report content End

    //$pdf->Ln(10);
    $validate = '
        <b style="font-size: '.($font_size+4).'px; font-weight:bold; color:maroon;">REMARKS:</b><br>
        <table border="0" cellpadding="5">
          <tbody>  
            <tr>
              <td style="font-size: '.($font_size+4).'px; font-weight:bold;">'.$calibration_info->calibration_remark.'</td>
            </tr>
          </tbody>
        </table>
        <div style="height:20px;"></div>
        <b style="font-size: '.($font_size+4).'px; font-weight:bold; color:maroon;">REPORT AUTHORIZATION:</b><br>
        <table border="0" cellpadding="5">
          <tbody>  
            <tr>
              <td style="font-size: '.($font_size+4).'px; font-weight:bold;">
              SURVEYOR:(name).............................................................. (sign).....................................................
              </td>
            </tr>
            <tr>
              <td style="font-size: '.($font_size+4).'px; font-weight:bold;">   DATE:............./............../20............</td>
            </tr>
          </tbody>
        </table>';

    $pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $validate, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);

    $pdf->Ln(10);
    $footer_thank_msg = '
    <table border="0" cellpadding="5">
    <thead>
    <tr>
    <td style="vertical-align: middle; text-align:center; font-size: '.($font_size+4).'px; border-top:1px solid ' . get_option('pdf_table_border_color') . ';"><b>'.strtoupper("Thank You For Your Business").'</b></td>
    </tr>
    </thead>
    </table>';

    $pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $footer_thank_msg, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);