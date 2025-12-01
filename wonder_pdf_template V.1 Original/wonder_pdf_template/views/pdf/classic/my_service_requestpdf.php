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
<td style="text-align:right;"><h1 style="padding:0; margin:0; font-size:40px;">SERVICE REQUEST</h1></td>
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
    $status_name = 'COMPLETED';  
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

    $info_instrument_condition = '
    <table  border="0" cellpadding="5">
      <thead>
        <tr bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold;">
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"><b>CONDITION OF INSTRUMENT</b></td>
        </tr>
      </thead>
      <tbody>  
        <tr style="color:#424242;">
          <td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';">'.$service_request->condition.'</td>
        </tr>
      </tbody>
    </table>';

    $pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_instrument_condition, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
    $pdf->ln(6);


    $info_service_details = '
    <table  border="0" cellpadding="5">
      <thead>
        <tr bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold;">
          <td width="5%;" align="center" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"><b>#</b></td>
          <td width="35%"  align="left;" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"><b>EQUIPMENT</b></td>
          <td width="35%" align="left;" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"><b>SERVICE</b></td>
          <td width="25%" align="right"  style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"><b>TOTAL PRICE ('.get_default_currency( 'symbol' ).')</b></td>
        </tr>
      </thead>
      <tbody>';

        $i                 = 1;
        $total = 0;
      foreach ($service_request_details as $key => $detail_info) {        
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
          <td width="5%;" align="center" style="border:1px solid #ccc;">' . $i . '</td>
          <td width="35%" class="description" align="left;" style="border:1px solid #ccc;"><span style="font-size:'.(isset($font_size) ? $font_size+4 : '').'px;">'.$detail_info->name.'</span></td>
          <td width="35%" class="description" align="left;" style="border:1px solid #ccc;"><span style="font-size:'.(isset($font_size) ? $font_size+4 : '').'px;">'.$detail_info->category_name.'</span></td>
          <td width="25%" align="right" style="border:1px solid #ccc;">'.format_money($detail_info->price, '').'</td>
        </tr>';
        $total = $total + $detail_info->price;
        $i++;
      }
      $info_service_details .= '
      <tr>
          <td width="40%"></td>
          <td align="right" width="35%" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('invoice_total') . '</strong></td>
          <td align="right" width="25%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . format_money($total, get_default_currency( 'symbol' )) . '</td>
      </tr>';
      $info_service_details .= '</tbody>
      </table>';

    $pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_service_details, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
    $pdf->ln(6);

//Sales Person Area
/*$info_sales_person = '
<table border="0" cellpadding="5">
<thead>
<tr bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold;">
<td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"><b>'.strtoupper(_l('sale_agent_string')).'</b></td>
<td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"><b>' .strtoupper(_l('invoice')) . '</b></td>
<td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"><b>' .strtoupper(_l('invoice_data_date')) . '</b></td>
</tr>
</thead>
<tbody>
<tr style="color:#424242;">
<td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';">';
if ($service_request->received_by != 0) {
    if (get_option('show_sale_agent_on_invoices') == 1) {
        $info_sales_person .= get_staff_full_name($service_request->received_by);
    }
}
$info_sales_person .= '</td>';
$info_sales_person .= '<td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';"># ' . $request_number . '</td><td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';">' . _d($service_request->collection_date).'</td>';
$info_sales_person .='</tr>
</tbody>
</table>';
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_sales_person, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);*/

//Item Table
/*$pdf->Ln(7);
$item_width = 60;

$tblhtml = '
<table width="100%" border="0" bgcolor="#fff" cellspacing="0" cellpadding="5">
    <tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold;">
        <th width="5%;" align="center" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';">#</th>
        <th width="' . $item_width . '%" align="left" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';">' .strtoupper(_l('invoice_table_item_heading')) . '</th>
        <th width="19%" align="right" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid ' . get_option('pdf_table_border_color') . ';">VERIFY(Tick)</th>
</tr>';
// Items
$tblhtml .= '<tbody>';

//$items_data = get_table_products_bulk($invoice->id);
//$tblhtml .= $items_data['html'];
$tblhtml .= '</tbody>';
$tblhtml .= '</table>';
$pdf->writeHTML($tblhtml, true, false, false, false, '');*/



$pdf->Ln(10);
$validate = '
    <b style="font-size: '.($font_size+4).'px; font-weight:bold;">REMARKS:</b><br>
    <table border="0" cellpadding="5">
      <tbody>  
        <tr>
          <td style="font-size: '.($font_size+4).'px; font-weight:bold;">'.$service_request->service_note.'</td>
        </tr>
      </tbody>
    </table>
    <div style="height:20px;"></div>
    <b style="font-size: '.($font_size+4).'px; font-weight:bold;">AGREED CONDITIONS:</b><br>
    <table border="0" cellpadding="5">
      <tbody>  
        <tr>
          <td style="font-size: '.($font_size+4).'px; font-weight:bold;">
          1. The Owner is responsible for Delivery and Collection of the equipment<br>
          2. The Owner is liable for any additional costs required in servicing the machine
          </td>
        </tr>
        <tr>
          <td style="font-size: '.($font_size+4).'px; font-weight:bold;">OWNER:(name).............................................. (sign)...................................</td>
        </tr>
        <tr>
          <td style="font-size: '.($font_size+4).'px; font-weight:bold;">ENGINEER:(name).............................................. (sign)...................................</td>
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