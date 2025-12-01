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
<td style="text-align:right;"><h1 style="padding:0; margin:0; font-size:35px;">RENTAL AGREEMENT</h1></td>
</tr>
</tbody>
</table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $top_header, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(6);

//logo area
$info_right_column = '';
$logo_area  = '';

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

$info_right_column .= '<b style="color:#4e4e4e;"># ' . $rental_agreement_number . '</b>';
//dates
$info_right_column .= '<br>
<b style="color:#4e4e4e; font_size:12px;">Start Date: ' . _d($service_rental_agreement->start_date).'
<br>End Date: ' . _d($service_rental_agreement->end_date).'
<br>Received By: ';
if ($service_rental_agreement->received_by != 0) {
    if (get_option('show_sale_agent_on_invoices') == 1) {
        $info_right_column .= get_staff_full_name($service_rental_agreement->received_by);
    }
}

if($service_rental_agreement->site_name != null){
 $info_right_column .= '<br>'. _l('site_name').': '. $service_rental_agreement->site_name ;
}
if($service_rental_agreement->field_operator != null){
 $info_right_column .= '<br>'. _l('field_operator').': '. get_staff_full_name($service_rental_agreement->field_operator) ;
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
$service_rental_agreement_info = '<div style="color:#424242;">';
$service_rental_agreement_info .= '<b style="color:black;">' . get_option('invoice_company_name') . '</b><br />';
$service_rental_agreement_info .= get_option('invoice_company_address') . '<br/>';
if (get_option('invoice_company_city') != '') {
  $service_rental_agreement_info .= get_option('invoice_company_city') . ', ';
}
$service_rental_agreement_info .= get_option('company_state') . ' ';
$service_rental_agreement_info .= get_option('invoice_company_postal_code') . '<br />';
$service_rental_agreement_info .= get_option('invoice_company_country_code') . '';

if (get_option('invoice_company_phonenumber') != '') {
  $service_rental_agreement_info .= '<br />' . get_option('invoice_company_phonenumber');
}
if (get_option('company_vat') != '') {
  $service_rental_agreement_info .= '<br />'. _l('company_vat_number').': '.get_option('company_vat');
}
          // check for company custom fields
$custom_company_fields = get_company_custom_fields();
if (count($custom_company_fields) > 0) {
  $service_rental_agreement_info .= '<br />';
}
foreach ($custom_company_fields as $field) {
  $service_rental_agreement_info .= $field['label'] . ': ' . $field['value'] . '<br />';
}
$service_rental_agreement_info .= '</div>';
$info_bill_shipping .= $service_rental_agreement_info.'</td>';

$info_bill_shipping .= '<td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';">';
            // Bill to
$client_details = '<div style="color:#424242;">';
if ($service_rental_agreement_client->show_primary_contact == 1) {
  $pc_id = get_primary_contact_user_id($service_rental_agreement_client->userid);
  if ($pc_id) {
    $client_details .= get_contact_full_name($pc_id) .'<br />';
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
  'show_on_pdf' => 1
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
$info_bill_shipping .= $client_details.'</td>';


$info_bill_shipping .='</tr>
</tbody>
</table>';
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_bill_shipping, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(6);

//Rental Info
    $info_rental = '
    <table border="0" cellpadding="5">
      <thead>
        <tr bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold;">
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"><b>RENTAL DATE</b></td>
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"><b>RETURN DATE</b></td>
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"><b>AGREEMENT CODE</b></td>
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"><b>RECEIVED BY</b></td>
        </tr>
      </thead>
      <tbody>  
        <tr style="color:#424242;">
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';">'._d($service_rental_agreement->start_date) .'</td>
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';">'._d($service_rental_agreement->end_date) .'</td>
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';">'.get_option('service_rental_agreement_prefix').$service_rental_agreement->service_rental_agreement_code.'</td>
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';">'.get_staff_full_name($service_rental_agreement->received_by).'</td>
        </tr>
      </tbody>
    </table>';

    $pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_rental, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
    $pdf->ln(6);

    $info_service_details = '
    <table  border="0" cellpadding="5">
      <thead>
        <tr bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold;">
          <td width="10%;" align="center" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"><b>#</b></td>
          <td width="45%"  align="left;" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"><b>SERIAL No.</b></td>
          <td width="45%" align="left;" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"><b>EQUIPMENT</b></td>
        </tr>
      </thead>
      <tbody>';

        $i                 = 1;
        $total = 0;
      foreach ($service_rental_agreement_details as $key => $detail_info) {        
        $tr_attrs       = '';

            if(class_exists('pdf')){
                $font_size = get_option('pdf_font_size');
                if($font_size == ''){
                    $font_size = 10;
                }

                $tr_attrs .= ' style="font-size:'.($font_size+4).'px;"';
            }
            $bg = '';
            if($i%2==0){$bg = ' bgcolor = "#f4f4f4"'; }
            else{$bg = ' bgcolor = "#ffffff"'; }

        $info_service_details .= '
        <tr' . $tr_attrs . ' '.$bg.' >
          <td width="10%;" align="center" style="border:1px solid #ccc;">' . $i . '</td>
          <td width="45%" class="description" align="left;" style="border:1px solid #ccc;"><span style="font-size:'.(isset($font_size) ? $font_size+4 : '').'px;">'.$detail_info->rental_serial.'</span></td>
          <td width="45%" class="description" align="left;" style="border:1px solid #ccc;"><span style="font-size:'.(isset($font_size) ? $font_size+4 : '').'px;">'.$detail_info->name.'</span></td>
        </tr>';
        $total = $total + $detail_info->price;
        $i++;
      }
      $info_service_details .= '</tbody>
      </table>';

    $pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_service_details, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
    $pdf->ln(6);

$remarks = '
    <b style="font-size: '.($font_size+4).'px; font-weight:bold;">REMARKS:</b><br>
    <table border="0" cellpadding="5">
      <tbody>  
        <tr>
          <td style="font-size: '.($font_size+4).'px;">'.$service_rental_agreement->rental_agreement_note.'</td>
        </tr>
      </tbody>
    </table>';
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $remarks, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
$pdf->Ln(10);

//PAGE BREAK
$pdf->AddPage();
$pdf->Ln(10);
$terms_and_conditions = '
    <b style="font-size: '.($font_size+4).'px; font-weight:bold; text-align:center;">EQUIPMENT RENTAL TERMS AND CONDITIONS</b><br>
    <table border="0" cellpadding="5">
      <tbody> 
        <tr>
          <td style="font-size: '.($font_size+4).'px;">
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
         <td style="font-size: '.($font_size+4).'px;"><b>DATE:</b></td>
         <td>'._d(date('Y-m-d')).'</td>
         <td style="font-size: '.($font_size+4).'px;"><b>AUTHORISED SIGNATURE:</b></td>
         <td><b>.................................</b></td>
         <td style="font-size: '.($font_size+4).'px;"><b>NAME OF CLIENT:</b></td>
         <td>'.$service_rental_agreement_client->company.'</td>
        </tr>
        <tr>
         <td style="font-size: '.($font_size+4).'px;"><b>DATE:</b></td>
         <td>'._d(date('Y-m-d')).'</td>
         <td style="font-size: '.($font_size+4).'px;"><b>AUTHORISED SIGNATURE:</b></td>
         <td><b>.................................</b></td>
         <td style="font-size: '.($font_size+4).'px;"><b>PROCESSED BY:</b></td>
         <td>'.get_staff_full_name($service_rental_agreement->received_by).'</td>
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