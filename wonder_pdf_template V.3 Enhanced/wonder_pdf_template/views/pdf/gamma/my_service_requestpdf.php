<?php

defined('BASEPATH') or exit('No direct script access allowed');
$doc_title = 'Service Request';

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
		$status_name = 'COMPLETED';
	}

	$info_right_column = '<div style="font-size:' . ($font_size + 4) . 'px;"><b style="color:#4e4e4e; "># ' . $request_number . '</b>';
	$info_right_column .= '<br /><span style="color:rgb(' . $bg_status . '); font-size:' . ($font_size + 6) . 'px;">' . mb_strtoupper($status_name, 'UTF-8') . '</span>';
}

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

$pdf->ln(6);

$border = 'border-bottom-color:#000000;border-bottom-width:3px; border-bottom-style:solid; border-top: 1px solid black;';
$borderBody = 'border-bottom-color:#000000;border-bottom-width:1px; border-bottom-style:solid;';

//Instrument Info
$info_instrument = '
    <table border="0" cellpadding="5">
      <thead>
        <tr  height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . (get_option('pdf_font_size') + 4) . 'px;">
          <td style="vertical-align: middle; font-size: ' . ($font_size + 6) . 'px; ' . $border . '"><b>TYPE</b></td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 6) . 'px; ' . $border . '"><b>MAKE</b></td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 6) . 'px; ' . $border . '"><b>MODEL</b></td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 6) . 'px; ' . $border . '"><b>SERIAL</b></td>
        </tr>
      </thead>
      <tbody>
        <tr style="color:#424242;">
          <td style="vertical-align: middle; font-size: ' . ($font_size + 6) . 'px; ' . $borderBody . '">' . $service_request->item_type . '</td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 6) . 'px; ' . $borderBody . '">' . $service_request->item_make . '</td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 6) . 'px; ' . $borderBody . '">' . $service_request->item_model . '</td>
          <td style="vertical-align: middle; font-size: ' . ($font_size + 6) . 'px; ' . $borderBody . '">' . $service_request->serial_no . '</td>
        </tr>
      </tbody>
    </table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_instrument, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(6);

$info_instrument_condition = '
    <table  border="0" cellpadding="5">
      <thead>
        <tr  height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . (get_option('pdf_font_size') + 4) . 'px;">
          <td style="vertical-align: middle; font-size: ' . ($font_size + 6) . 'px; ' . $border . '"><b>CONDITION OF INSTRUMENT</b></td>
        </tr>
      </thead>
      <tbody>
        <tr style="color:#424242;">
          <td style="vertical-align: middle; font-size: ' . ($font_size + 6) . 'px; ' . $borderBody . '">' . $service_request->condition . '</td>
        </tr>
      </tbody>
    </table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_instrument_condition, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(6);

$info_service_details = '
    <table  border="0" cellpadding="5">
      <thead>
        <tr  height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . (get_option('pdf_font_size') + 4) . 'px;">
          <td width="5%;" align="center" style="vertical-align: middle; font-size: ' . ($font_size + 6) . 'px; ' . $border . '"><b>#</b></td>
          <td width="35%"  align="left;" style="vertical-align: middle; font-size: ' . ($font_size + 6) . 'px; ' . $border . '"><b>EQUIPMENT</b></td>
          <td width="35%" align="left;" style="vertical-align: middle; font-size: ' . ($font_size + 6) . 'px; ' . $border . '"><b>SERVICE</b></td>
          <td width="25%" align="right"  style="vertical-align: middle; font-size: ' . ($font_size + 6) . 'px; ' . $border . '"><b>TOTAL PRICE (' . get_default_currency('symbol') . ')</b></td>
        </tr>
      </thead>
      <tbody>';

$i = 1;
$total = 0;
foreach ($service_request_details as $key => $detail_info) {
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
	$bg = 'bgcolor = "#ffffff"';
	$info_service_details .= '
        <tr' . $tr_attrs . ' ' . $bg . ' >
          <td width="5%;" align="center" style="' . $borderBody . '">' . $i . '</td>
          <td width="35%" class="description" align="left;" style="' . $borderBody . '"><span style="font-size:' . (isset($font_size) ? $font_size + 4 : '') . 'px;">' . $detail_info->name . '</span></td>
          <td width="35%" class="description" align="left;" style="' . $borderBody . '"><span style="font-size:' . (isset($font_size) ? $font_size + 4 : '') . 'px;">' . $detail_info->category_name . '</span></td>
          <td width="25%" align="right" style="' . $borderBody . '">' . app_format_money($detail_info->price, '') . '</td>
        </tr>';
	$total = $total + $detail_info->price;
	$i++;
}
$info_service_details .= '
      <tr>
          <td width="40%"  style="color:red; font-weight:bold;"></td>
          <td align="right" width="35%" style="' . $borderBody . '"><strong>' . _l('invoice_total') . '</strong></td>
          <td align="right" width="25%" style="' . $borderBody . '">' . app_format_money($total, get_default_currency('symbol')) . '</td>
      </tr>';
$info_service_details .= '</tbody>
      </table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $info_service_details, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(6);

$accessory_details = '
<table border="0" cellpadding="5">
  <thead>
    <tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . (get_option('pdf_font_size') + 4) . 'px;">
      <td width="5%" align="center" style="vertical-align: middle; font-size: ' . ($font_size + 6) . 'px; ' . $border . '"><b>#</b></td>
      <td width="60%" align="left" style="vertical-align: middle; font-size: ' . ($font_size + 6) . 'px; ' . $border . '"><b>EQUIPMENT SPARES</b></td>
      <td width="35%" align="right" style="vertical-align: middle; font-size: ' . ($font_size + 6) . 'px; ' . $border . '"><b>PRICE (' . get_default_currency('symbol') . ')</b></td>
    </tr>
  </thead>
  <tbody>';

$counter = 1;
$accessory_total = 0;

foreach ($accessories as $accessory) {
    $bg = ($counter % 2 == 0) ? ' bgcolor="#f4f4f4"' : ' bgcolor="#ffffff"';
    $accessory_details .= '
    <tr' . $bg . ' style="font-size:' . ($font_size + 4) . 'px;">
      <td width="5%" align="center" style="' . $borderBody . '">' . $counter++ . '</td>
      <td width="60%" align="left" style="' . $borderBody . '">' . htmlspecialchars($accessory->item_description) . '</td>
      <td width="35%" align="right" style="' . $borderBody . '">' . app_format_money($accessory->price, get_default_currency('symbol')) . '</td>
    </tr>';
    $accessory_total += $accessory->price;
}

$accessory_details .= '
    <tr>
    <td width="40%"  style="color:red; font-weight:bold;"></td>
      <td colspan="2" align="right" width="35%" style="' . $borderBody . '"><strong>Accessory Total</strong></td>
      <td align="right" style="' . $borderBody . '"><strong>' . app_format_money($accessory_total, get_default_currency('symbol')) . '</strong></td>
    </tr>
  </tbody>
</table>';

$pdf->MultiCell($dimensions['wk'] - $dimensions['rm'] - $dimensions['lm'], 0, $accessory_details, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(6);



$pre_inspection_table = '
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold;">
            <td colspan="1" style="vertical-align: middle; font-size: '.($font_size+4).'px; text-align:center;">#</td>
            <td colspan="4" style="vertical-align: middle; font-size: '.($font_size+4).'px;">INSPECTION ITEM</td>
            <td colspan="4" style="vertical-align: middle; font-size: '.($font_size+4).'px;">TYPE</td>
            <td colspan="4" style="vertical-align: middle; font-size: '.($font_size+4).'px;">REMARKS</td>
        </tr>
    </thead>
    <tbody>';

    if (!empty($pre_inspection_items)) {
        $inspection_counter = 1;
        foreach ($pre_inspection_items as $item) {
            $pre_inspection_table .= '
            <tr>
                <td colspan="1" style="vertical-align: middle; font-size: '.($font_size+4).'px; text-align:center;">' . $inspection_counter . '</td>
                <td colspan="4" style="vertical-align: middle; font-size: '.($font_size+4).'px;">' . htmlspecialchars(ucwords(str_replace('_', ' ', $item->inspection_item))) . '</td>
                <td colspan="4" style="vertical-align: middle; font-size: '.($font_size+4).'px;">' . htmlspecialchars(ucwords(str_replace('_', ' ', $item->inspection_type))) . '</td>
                <td colspan="4" style="vertical-align: middle; font-size: '.($font_size+4).'px;">' . htmlspecialchars($item->remarks_condition) . '</td>
            </tr>';
            $inspection_counter++;
        }
    } else {
        $pre_inspection_table .= '<tr>
            <td colspan="4" style="text-align:center; font-size: '.($font_size+4).'px;">No pre-inspection items available.</td>
        </tr>';
    }
    

$pre_inspection_table .= '</tbody>
</table>';

$pdf->writeHTML('<h3 style="color:red;">Instrument Pre-Inspection</h3>', true, false, false, false, '');
$pdf->writeHTML($pre_inspection_table, true, false, false, false, '');


// Generate the info table with dynamic data from $service_request
$info_table = '
<div style="margin-top: 10px;">
    <table border="0" cellpadding="5" cellspacing="0">
        <tr>
            <!-- Dropped Off Information -->
            <td style="width: 50%; vertical-align: top;">
                <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
                    <tbody>
                        <!-- Dropped Off By Section -->
                        <tr>
                            <td style="font-weight: bold; color:red;">Dropped Off By</td>
                            <td>' . htmlspecialchars($service_request->dropped_off_by ?? 'N/A') . '</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Dated</td>
                            <td>' . htmlspecialchars($service_request->dropped_off_date ?? 'N/A') . '</td>
                        </tr>
                       
                        <tr>
                            <td style="font-weight: bold;">ID/Phone No.</td>
                            <td>' . htmlspecialchars($service_request->dropped_off_id_number ?? 'N/A') . '</td>
                        </tr>
                    </tbody>
                </table>
            </td>

            <!-- Received Information -->
            <td style="width: 50%; vertical-align: top;">
                <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
                    <tbody>
                        <!-- Received By Section -->
                        <tr>
                            <td style="font-weight: bold; color:red;">Received By</td>
                            <td>' . htmlspecialchars($service_request->req_received_by ?? 'N/A') . '</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Dated</td>
                            <td>' . htmlspecialchars($service_request->received_date ?? 'N/A') . '</td>
                        </tr>
                        
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</div><br><br><br>';

// Write the HTML table into the PDF
$pdf->writeHTML($info_table, true, false, false, false, '');
$pdf->ln(6);



$terms = "<span font-size='" . ($font_size + 2) . "px;'>
<h2><b><u>Terms and Conditions of Service</u></b></h2>
<p><b>1. The Owner Is Responsible For Delivery And Collection Of The Equipment</b></p>
<p><b>2. The Owner Is Liable For Any Additional Costs Incurred In Servicing The Machine</b></p>
<p><b>3. The Owner Should Carry This Request Form During Date Of Collection Of Equipment</b></p>
<p><b>4. The Owner Is Responsible For Carrying Out Tests Before Collection Of The Equipment To Make Sure He/She Is Satisfied With The Service</b></p>
<p><b>5. Complaints Should Be Forwarded Immediately Or Within Six Working Days From Date Of Collection Of The Equipment</b></p>
<p><b>6. A Minimum Fee Of Ksh 2,000 Is Applicable On Any Equipment Attended To Either For Calibration Or Repair And Has Not Been Serviced Due To Lack Of Spare Parts Or Its Beyond Calibration & Repair. This Is To Cater For The Time Taken To Work For The Equipment</b></p>
<p><b>7. Warranty Period For Workmanship Is Within Six Working Days From Date Of Collection Of The Equipment</b></p>
<p><b>8. Measurement Systems Shall Not Be Held Liable For Any Damages, Losses Or Costs Resulting From Poor Handling And Transportation</b></p>
<p><b>9. Equipment With More Than One Month In Our Storage Facilities Will Be Charged With A Minimal Storage Fee Of 3,000/= Per Month, Those That Exceed More Than One Year Will Be Auctioned And Prior Notice Will Be Issued To The Owner 3 Weeks Before Auction</b></p>
<p><b>10. In Case The Equipment Breaks Down Due To Poor Handling, The Six Days Service Warranty Will Not Be Applicable And The Owner Will Be Charged As A New Service Once It Is Brought Back To The Service Center</b></p>
<p><b>11. The Prices That Are Indicated On Repair And Calibration Services Do Not Include Installation Or Replacement Of New Spare Parts Whatsoever. If In Any Case The Equipment Will Need To Be Replaced With A New Spare Part, The Price Will Change According To The Price Of The Spare Part Replaced</b></p>
</span>";

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $terms, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);


$post_inspection_table = '
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight: bold;">
            <td colspan="1" style="vertical-align: middle; font-size: '.($font_size+4).'px; text-align:center;">#</td>
            <td colspan="4" style="vertical-align: middle; font-size: '.($font_size+4).'px;">INSPECTION ITEM</td>
            <td colspan="4" style="vertical-align: middle; font-size: '.($font_size+4).'px;">TYPE</td>
            <td colspan="4" style="vertical-align: middle; font-size: '.($font_size+4).'px;">REMARKS</td>
        </tr>
    </thead>
    <tbody>';

    if (!empty($post_inspection_items)) {
        $inspection_counter = 1;
        foreach ($post_inspection_items as $item) {
            $post_inspection_table .= '
            <tr>
                <td colspan="1" style="vertical-align: middle; font-size: '.($font_size+4).'px; text-align:center;">' . $inspection_counter . '</td>
                <td colspan="4" style="vertical-align: middle; font-size: '.($font_size+4).'px;">' . htmlspecialchars(ucwords(str_replace('_', ' ', $item->inspection_item))) . '</td>
                <td colspan="4" style="vertical-align: middle; font-size: '.($font_size+4).'px;">' . htmlspecialchars(ucwords(str_replace('_', ' ', $item->inspection_type))) . '</td>
                <td colspan="4" style="vertical-align: middle; font-size: '.($font_size+4).'px;">' . htmlspecialchars($item->remarks_condition) . '</td>
            </tr>';
            $inspection_counter++;
        }
    } else {
        $post_inspection_table .= '<tr>
            <td colspan="4" style="text-align:center; font-size: '.($font_size+4).'px;">No post-inspection items available.</td>
        </tr>';
    }
    

$post_inspection_table .= '</tbody>
</table>';

// Render the tables in the PDF

$pdf->writeHTML('<h3 style="color:red;">Instrument Post-Inspection</h3>', true, false, false, false, '');
$pdf->writeHTML($post_inspection_table, true, false, false, false, '');



$info_table = '
<div style="margin-top: 10px;">
    <table border="0" cellpadding="5" cellspacing="0">
        <tr>
            <!-- Checklist Table -->
            <td style="width: 50%; vertical-align: top;">
                <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 70%; text-align: left; font-weight: bold; color:red;">CHECKLIST ITEM</th>
                            <th style="width: 30%; text-align: center; font-weight: bold;">X/√</th>
                        </tr>
                    </thead>
                    <tbody>';
                    
                      if (isset($checklist_items) && !empty($checklist_items)) {
                          foreach ($checklist_items as $item) {
                              $info_table .= '
                                              <tr>
                                                  <td>' . htmlspecialchars($item->item) . '</td>
                                                  <td style="text-align: center;">' . htmlspecialchars($item->status ?: 'N/A') . '</td>
                                              </tr>';
                          }
                      } else {
                          $info_table .= '
                                              <tr>
                                                  <td colspan="2" style="text-align: center;">No checklist items available.</td>
                                              </tr>';
                      }

                      $info_table .= '
                    </tbody>
                </table>
            </td>

            <!-- Released By and Collected By Section -->
            <td style="width: 50%; vertical-align: top;">
                <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
                    <tbody>
                        <!-- Released By Section -->
                        <tr>
                            <td style="font-weight: bold; color:red;">Released By</td>
                            <td>' . htmlspecialchars($released_info->released_by ?? 'N/A') . '</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">Dated</td>
                            <td>' . htmlspecialchars($released_info->released_date ?? 'N/A') . '</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">ID/Phone No.</td>
                            <td>' . htmlspecialchars($released_info->released_id_number ?? 'N/A') . '</td>
                        </tr>

                        
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</div>';

// Write the HTML table into the PDF
$pdf->writeHTML($info_table, true, false, false, false, '');



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
    <b style="font-size: ' . ($font_size + 2) . 'px; font-weight:bold;">REMARKS:</b><br>
    <table border="0" cellpadding="5">
      <tbody>
        <tr>
          <td style="font-size: ' . ($font_size + 2) . 'px; font-weight:bold;">' . $service_request->service_note . '</td>
        </tr>
      </tbody>
    </table>
    <div style="height:20px;"></div>
    <b style="font-size: ' . ($font_size + 2) . 'px; font-weight:bold;">AGREED CONDITIONS:</b><br>
    <table border="0" cellpadding="5">
      <tbody>
        <tr>
          <td style="font-size: ' . ($font_size + 2) . 'px; font-weight:bold;">
          1. The Owner is responsible for Delivery and Collection of the equipment<br>
          2. The Owner is liable for any additional costs required in servicing the machine
          </td>
        </tr>
        <tr>
          <td style="font-size: ' . ($font_size + 2) . 'px; font-weight:bold;">OWNER:(name).............................................. (sign)...................................</td>
        </tr>
        <tr>
          <td style="font-size: ' . ($font_size + 2) . 'px; font-weight:bold;">ENGINEER:(name).............................................. (sign)...................................</td>
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