<?php
$dimensions = $pdf->getPageDimensions();

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
<td style="text-align:right;"><h1 style="padding:0; margin:0; font-size:40px;">'. strtoupper(_l('purchase_order_pdf_heading')).'</h1></td>
</tr>
</tbody>
</table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $top_header, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(6);

//logo area
$info_right_column = '';
$logo_area  = '';

if (get_option('show_status_on_pdf_ei') == 1) {
  $status_name = format_purchase_order_status($purchase_order->status, '', false);
  if ($purchase_order->status == 0) {
    $bg_status = '114, 123, 144';
  } elseif ($purchase_order->status == 1) {
    $bg_status = '0, 191, 54';
  } elseif ($purchase_order->status == 2) {
    $bg_status = '255, 111, 0';
  } elseif ($purchase_order->status == 3) {
    $bg_status = '252, 45, 66';
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

$info_right_column .= '<b style="color:#4e4e4e;"># ' . get_option( 'purchase_order_prefix' ).$purchase_order->purchase_order_code . '</b>';

//dates
if($purchase_order->status == 0 or $purchase_order->status == 2){
$info_right_column .= '<br><b style="color:#4e4e4e; font_size:12px;">'._l('purchase_date_added') . ' ' . _d($purchase_order->date_added).'</b>';
}
if($purchase_order->status == 1 or $purchase_order->status == 2){
$info_right_column .= '<br><b style="color:#4e4e4e; font_size:12px;">'._l('purchase_date_raised') . ' ' . _d($purchase_order->date_raised).'</b>';
}
if($purchase_order->status == 2){
$info_right_column .= '<br><b style="color:#4e4e4e; font_size:12px;">'._l('purchase_date_received') . ' ' . _d($purchase_order->date_received).'</b>';
}

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
<td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid #ccc;"><b>'.strtoupper('Vendor Info').'</b></td>
<td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid #ccc;"><b>'.($purchase_order->inventory_related == 1 ? strtoupper(_l('ship_to')) : strtoupper(_l('purchaser_info'))).'</b></td>';

$info_bill_shipping .= '</tr>
</thead>
<tbody>
<tr>';

//Vendor
$info_bill_shipping .= '<td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid #ccc;">';
            // Bill to
$vendor_details = '<div style="color:#424242;"><b style="color:black;">' . $purchase_order->supplier->supplier_name . '</b><br />';
if (!empty($purchase_order->supplier->address)) {
  $vendor_details .= $purchase_order->supplier->address.'<br />';
}
if (!empty($purchase_order->supplier->email)) {
  $vendor_details .= $purchase_order->supplier->email.'<br />';
}
if (!empty($purchase_order->supplier->phone)) {
  $vendor_details .= $purchase_order->supplier->phone.'<br />';
}
if (!empty($purchase_order->supplier->supplier->contact_person)) {
  $vendor_details .= $purchase_order->supplier->contact_person.'<br />';
}
if (!empty($purchase_order->supplier->country_id)) {
  $vendor_details .= get_country($purchase_order->supplier->country_id)->short_name;
}

$vendor_details .= '</div>';
$info_bill_shipping .= $vendor_details.'</td>';

//Ship To
$info_bill_shipping .= '<td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid #ccc;">';
$invoice_info = '<div style="color:#424242;">';
$invoice_info .= '<b style="color:black;">' . get_option('invoice_company_name') . '</b><br />';
$invoice_info .= get_option('invoice_company_address') . '<br/>';
if (get_option('invoice_company_city') != '') {
  $invoice_info .= get_option('invoice_company_city') . ', ';
}
$invoice_info .= get_option('company_state') . ' ';
$invoice_info .= get_option('invoice_company_postal_code') . '<br />';
$invoice_info .= get_option('invoice_company_country_code') . '';

if (get_option('invoice_company_phonenumber') != '') {
  $invoice_info .= '<br />' . get_option('invoice_company_phonenumber');
}
if (get_option('company_vat') != '') {
  $invoice_info .= '<br />'. _l('company_vat_number').': '.get_option('company_vat');
}
          // check for company custom fields
$custom_company_fields = get_company_custom_fields();
if (count($custom_company_fields) > 0) {
  $invoice_info .= '<br />';
}
foreach ($custom_company_fields as $field) {
  $invoice_info .= $field['label'] . ': ' . $field['value'] . '<br />';
}
$invoice_info .= '</div>';
$info_bill_shipping .= $invoice_info.'</td>';


$info_bill_shipping .='</tr>
</tbody>
</table>';
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_bill_shipping, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(6);

//Delivery terms area
$info_sales_person = '
<table border="0" cellpadding="5">
<thead>
<tr bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold;">
<td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid #ccc;"><b>'.($purchase_order->inventory_related == 1 ? strtoupper('Shipping Method') : strtoupper(_l('purchase_subject_short'))).'</b></td>
<td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid #ccc;"><b>' .strtoupper('Delivery Terms') . '</b></td>
<td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid #ccc;"><b>' .strtoupper('Delivery Date') . '</b></td>
</tr>
</thead>
<tbody>
<tr style="color:#424242;">
<td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid #ccc;">'.($purchase_order->inventory_related == 1 ? $purchase_order->shipping_mode : get_field_value('tblpurchasecategories', array('id'=>$purchase_order->subject), 'name')).'</td>';
$info_sales_person .= '<td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid #ccc;">Organized by Vendor</td><td style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid #ccc;">On Order</td>';
$info_sales_person .='</tr>
</tbody>
</table>';
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_sales_person, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(6);


$item_width = 50;
$qty_heading = _l('invoice_table_quantity_heading');


$tblhtml = '
<table border="0" bgcolor="#fff" cellspacing="0" cellpadding="5">
    <tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold;">
        <th width="5%;" align="center" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid #ccc;">#</th>
        <th width="' . $item_width . '%" align="left" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid #ccc;">' .strtoupper(_l('invoice_table_item_heading')) . '</th>
        <th width="8%" align="right" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid #ccc;">' . strtoupper($qty_heading) . '</th>
        <th width="14%" align="right" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid #ccc;">' . strtoupper(_l('invoice_table_rate_heading')) . '</th>';
        if($purchase_order->inventory_related == 0){
          $tblhtml .= '<th width="8%" align="right" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid #ccc;">' . strtoupper(_l('invoice_table_tax_heading')) . '</th>';
        }

        $tblhtml .= '<th width="15%" align="right" style="vertical-align: middle; font-size: '.($font_size+4).'px; border:1px solid #ccc;">' . strtoupper(_l('invoice_table_amount_heading')) . '</th>
</tr>';

// Items
$tblhtml .= '<tbody>';

$taxname = array();
$taxes= array();
$total = 0;
$subtotal = 0;
$total_tax = 0;
foreach ($purchase_order->items as $key => $item) {
   $tblhtml .= '<tr>
   <td align="center" style="border:1px solid #ccc;">'.($key+1).'</td>
   <td align="left" style="border:1px solid #ccc;">'.$item->product_name.'</td>
   <td align="right" style="border:1px solid #ccc;">'.floatVal($item->qty).'</td>
   <td align="right" style="border:1px solid #ccc;">'._format_number($item->unit_price).'</td>';
   if($purchase_order->inventory_related == 0){
     $taxname[$key]['name'] = get_field_value('tbltaxes', array('id'=>$item->tax_id), 'name');
     $taxname[$key]['taxrate'] = $taxrate = get_field_value('tbltaxes', array('id'=>$item->tax_id), 'taxrate');
     $calculated_tax = ($item->sub_total / 100 * $taxrate);
     $_taxname = $taxname[$key]['name'].'|'.$taxname[$key]['taxrate'];
     if(!array_key_exists($_taxname, $taxes)){
         $taxes[$_taxname] = $calculated_tax;
     } else {
          // Increment total from this tax
          $taxes[$_taxname] += $calculated_tax;
      }
      $tax_rate_cell = get_field_value('tbltaxes', array('id'=>$item->tax_id), 'taxrate');
    $tblhtml .= '<td align="right" style="border:1px solid #ccc;">'.(empty($tax_rate_cell) ? '0' : $tax_rate_cell).'%'.'</td>';
   }
   $tblhtml .= '<td align="right" style="border:1px solid #ccc;">'._format_number($item->unit_price * $item->qty).'</td>
   </tr>';
   if($purchase_order->inventory_related == 1){
     $total = ($item->unit_price * $item->qty) + $total;
   }
}

$tblhtml .= '</tbody>';
$tblhtml .= '</table>';
$pdf->writeHTML($tblhtml, true, false, false, false, '');

$pdf->Ln(-7);

$tax_rows = null;
if($purchase_order->inventory_related == 0){
  
    foreach ($taxes as $tax_key => $value) {
      $total_tax = $value;
      $total += $total_tax;
      $_taxname = explode('|', $tax_key);
      if($total_tax > 0){
      $tax_rows .= '<tr>
      <td width="55%"></td>
      <td align="right" width="30%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">'.$_taxname[0].'('.$_taxname[1].'%)</td>
      <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">'.format_money($total_tax, get_default_currency('symbol')).'</td>
      </tr>'; 
     }

    }

    $subtotal = get_purchase_total($purchase_order->purchase_id);
    $total = ($total + $subtotal);
  }

$tbltotal = '';

$tbltotal .= '<table cellpadding="5" style="font-size:'.($font_size+4).'px" border="0">';

if($purchase_order->inventory_related == 0){
 $tbltotal .= '
<tr>
    <td width="55%"></td>
    <td align="right" width="30%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . _l('subtotal') . '</td>
    <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . app_format_money($subtotal, $purchase_order->symbol) . '</td>
</tr>'; 
$tbltotal .= $tax_rows;
}

$tbltotal .= '
<tr>
    <td width="55%"></td>
    <td align="right" width="30%" style="border:1px solid #ccc;" bgcolor="#f4f4f4"><strong>' . _l('invoice_total') . '</strong></td>
    <td align="right" width="15%" style="border:1px solid #ccc;" bgcolor="#f4f4f4">' . app_format_money($total, $purchase_order->symbol) . '</td>
</tr>';

$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');

if (get_option('total_to_words_enabled') == 1) {
    // Set the font bold
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('num_word') . ': ' . $CI->numberword->convert($total, $purchase_order->currency_name), 0, 1, 'C', 0, '', 0);
    // Set the font again to normal like the rest of the pdf
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(4);
}


if (!empty($purchase_order->terms)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('terms_and_conditions'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $purchase_order->terms, 0, 1, false, true, 'L', true);
}

$pdf->Ln(4);
$footer_thank_msg = '
<table border="0" cellpadding="5">
<thead>
<tr>
<td style="vertical-align: middle; text-align:center; font-size: '.($font_size+4).'px; border-top:2px solid #dedede;"><b>'.strtoupper("Thank You For Your Business").'</b></td>
</tr>
</thead>
</table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $footer_thank_msg, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
