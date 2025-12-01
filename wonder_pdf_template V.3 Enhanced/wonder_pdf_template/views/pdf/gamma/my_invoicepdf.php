<?php

defined('BASEPATH') or exit('No direct script access allowed');

$dimensions = $pdf->getPageDimensions();

$info_right_column = '<div style="color:#424242; font-size: 14px;">' . format_organization_info() . '</div>';
$info_left_column = wonder_pdf_logo_url();

// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 3.33) - $dimensions['lm']);

$pdf->ln(2);
$underLine = '<div style="width:100%; border-bottom: 2px solid red;"></div>';
$pdf->writeHTML($underLine, true, false, false, false, '');

$pdf->ln(-8);

$invoice_heading = ucfirst(strtolower(_l('invoice_pdf_heading')));
if (!empty($invoice->datecs_esd_response)) {
	$invoice_heading = get_option('tax_invoice_name');;
}

$title = '<span style="font-weight:600; font-size: 38pt;  text-align:right">' . $invoice_heading . '</span><br />';
$brands = '<img src="' . module_dir_path(WPDF_TEMPLATE, 'assets/images/brands.png') . '"/>';

//$pdf->writeHTML($title, true, false, false, false, '');
pdf_multi_row($brands, $title, $pdf, ($dimensions['wk'] / 2.5) - $dimensions['lm']);
$pdf->ln(1);

$invoice_info = '<b style="color:#4e4e4e;"># ' . $invoice_number . '</b>';

if (get_option('show_status_on_pdf_ei') == 1) {
	$invoice_info .= '<br /><span style="color:rgb(' . invoice_status_color_pdf($status) . ');text-transform:uppercase;">' . format_invoice_status($status, '', false) . '</span>';
}

if (
	$status != Invoices_model::STATUS_PAID && $status != Invoices_model::STATUS_CANCELLED && get_option('show_pay_link_to_invoice_pdf') == 1
	&& found_invoice_mode($payment_modes, $invoice->id, false)
) {
	$invoice_info .= ' - <a style="color:#84c529;text-decoration:none;text-transform:uppercase;" href="' . site_url('invoice/' . $invoice->id . '/' . $invoice->hash) . '"><1b>' . _l('view_invoice_pdf_link_pay') . '</1b></a>';
}

$invoice_info .= '<br /><span style="font-weight:bold;">' . _l('invoice_data_date') . '</span> ' . _d($invoice->date) . '<br />';

if (!empty($invoice->duedate)) {
	$invoice_info .= '<span style="font-weight:bold;">' . _l('invoice_data_duedate') . '</span> ' . _d($invoice->duedate) . '<br />';
}

if ($invoice->sale_agent != 0 && get_option('show_sale_agent_on_invoices') == 1) {
	$invoice_info .= '<span style="font-weight:bold;">' . _l('sale_agent_string') . ':</span> ' . get_staff_full_name($invoice->sale_agent) . '<br />';
}

if ($invoice->project_id != 0 && get_option('show_project_on_invoice') == 1) {
	$invoice_info .= '<span style="font-weight:bold;">' . _l('project') . ':</span> ' . get_project_name_by_id($invoice->project_id) . '<br />';
}

// Bill to
$customer_info = '<span style="font-weight:bold;">BILL TO:</span>';
$customer_info .= '<div style="font-weight:400 !important; color:#4e4e4e;">';
$customer_info .= format_customer_info($invoice, 'invoice', 'billing');
$customer_info .= '</div>';

$info_right_column = $invoice_info;

$info_left_column = $customer_info;

pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

// The items table
// The items table
$items = wonder_get_items_table_data($invoice, 'invoice', 'pdf', false);

// Generate the services table
$tblhtml = $items->table();
$borderTotal = 'border-bottom-color:#000000;border-bottom-width:1px; border-bottom-style:solid;';
$tbltotal = '';

// ✅ Determine the Last Service Item Number
$lastItemNumber = count($invoice->items); // Assuming $invoice->items contains service items

// ✅ Add Accessory Data with Continued Numbering and <hr> Below Each Item
$accessoryRows = '';
if (!empty($invoice->accessories)) {
    $currentNumber = $lastItemNumber + 1;

    foreach ($invoice->accessories as $accessory) {
        $qty = 1; // Assuming each accessory has a quantity of 1
        $rate = $accessory->accessory_price;
        $amount = $qty * $rate;

        $accessoryRows .= '
        <tr>
            <td align="center">' . $currentNumber . '</td>
            <td>' . htmlspecialchars($accessory->accessory_name) . '</td>
            <td align="right">' . $qty . '</td>
            <td align="right">' . number_format($rate, 2). '</td>
            <td align="right">0%</td>
            <td align="right">' . number_format($amount, 2) . '</td>
        </tr>
        <tr><td colspan="12"><hr colspan="12" style="border:1px solid #000;"></td></tr>'; // ✅ HR Tag

        $currentNumber++; // Increment for next accessory
    }
}

// ✅ Insert accessories right after the service items
$tblhtml = str_replace('</tbody>', $accessoryRows . '</tbody>', $tblhtml);

// ✅ Totals Table
$tbltotal .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';
$tbltotal .= '
<tr>
    <td align="right" width="65%"></td>
    <td align="right" width="20%" style="background-color:#f0f0f0;' . $borderTotal . '">' . _l('invoice_subtotal') . '</td>
    <td align="right" width="15%" style="background-color:#f0f0f0;' . $borderTotal . '">' . app_format_money($invoice->subtotal, $invoice->currency_name) . '</td>
</tr>';


if (is_sale_discount_applied($invoice)) {
	$tbltotal .= '
    <tr>
        <td align="right" width="65%"></td>
        <td align="right" width="20%" style="' . $borderTotal . '">' . _l('invoice_discount');
	if (is_sale_discount($invoice, 'percent')) {
		$tbltotal .= '(' . app_format_number($invoice->discount_percent, true) . '%)';
	}
	$tbltotal .= '';
	$tbltotal .= '</td>';
	$tbltotal .= '<td align="right" width="15%" style="' . $borderTotal . '">-' . app_format_money($invoice->discount_total, $invoice->currency_name) . '</td>
    </tr>';
}

foreach ($items->taxes() as $tax) {
	$tbltotal .= '<tr>
	<td align="right" width="65%"></td>
    <td align="right" width="20%" style="' . $borderTotal . '">' . $tax['taxname'] . ' (' . app_format_number($tax['taxrate']) . '%)' . '</td>
    <td align="right" width="15%" style="' . $borderTotal . '">' . app_format_money($tax['total_tax'], $invoice->currency_name) . '</td></tr>';
}

if ((int) $invoice->adjustment != 0) {
	$tbltotal .= '<tr>
    <td align="right" width="65%"></td>
    <td align="right" width="20%" style="' . $borderTotal . '">' . _l('invoice_adjustment') . '</td>
    <td align="right" width="15%" style="' . $borderTotal . '">' . app_format_money($invoice->adjustment, $invoice->currency_name) . '</td></tr>';
}

$tbltotal .= '
<tr style="color:red; font-weight:bold; font-size: 10.1pt;">
    <td align="right" width="65%"></td>
    <td align="right" width="20%" style="background-color:#f0f0f0;' . $borderTotal . '">' . _l('invoice_total') . '</td>
    <td align="right" width="15%" style="background-color:#f0f0f0;' . $borderTotal . '">' . app_format_money($invoice->total, $invoice->currency_name) . '</td>
</tr>';

if (count($invoice->payments) > 0 && get_option('show_total_paid_on_invoice') == 1) {
	$tbltotal .= '
    <tr>
        <td align="right" width="65%"></td>
    <td align="right" width="20%" style="' . $borderTotal . '">' . _l('invoice_total_paid') . '</td>
        <td align="right" width="15%" style="' . $borderTotal . '">-' . app_format_money(sum_from_table(db_prefix() . 'invoicepaymentrecords', [
		'field' => 'amount',
		'where' => [
			'invoiceid' => $invoice->id,
		],
	]), $invoice->currency_name) . '</td>
    </tr>';
}

if (get_option('show_credits_applied_on_invoice') == 1 && $credits_applied = total_credits_applied_to_invoice($invoice->id)) {
	$tbltotal .= '
    <tr>
        <td align="right" width="65%"></td>
    <td align="right" width="20%" style="' . $borderTotal . '">' . _l('applied_credits') . '</td>
        <td align="right" width="15%" style="' . $borderTotal . '">-' . app_format_money($credits_applied, $invoice->currency_name) . '</td>
    </tr>';
}

if (get_option('show_amount_due_on_invoice') == 1 && $invoice->status != Invoices_model::STATUS_CANCELLED) {
	$tbltotal .= '<tr>
       <td align="right" width="65%"></td>
    <td align="right" width="20%"  style="background-color:#f0f0f0;' . $borderTotal . '">' . _l('invoice_amount_due') . '</td>
       <td align="right" width="15%" style="background-color:#f0f0f0;' . $borderTotal . '">' . app_format_money($invoice->total_left_to_pay, $invoice->currency_name) . '</td>
   </tr>';
}

$tbltotal .= '</table>';
$tblhtml .= $tbltotal;
if (get_option('total_to_words_enabled') == 1) {
	$tblhtml .= '<b>' . _l('num_word') . ': ' . $CI->numberword->convert($invoice->total, $invoice->currency_name) . '</b>';
}
if (count($invoice->payments) > 0 && get_option('show_transactions_on_invoice_pdf') == 1) {
	$border = 'border-bottom-color:#000000;border-bottom-width:1px;border-bottom-style:solid; 1px solid black;';
	$tblhtml .= '<p></p><p></p><p></p><div><b>' . _l('invoice_received_payments') . '</b></div>';
	$tblhtml .= '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="0">
        <tr height="20"  style="color:#000;border:1px solid #000;">
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_number_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_mode_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_date_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_amount_heading') . '</th>
    </tr>';
	$tblhtml .= '<tbody>';
	foreach ($invoice->payments as $payment) {
		$payment_name = $payment['name'];
		if (!empty($payment['paymentmethod'])) {
			$payment_name .= ' - ' . $payment['paymentmethod'];
		}
		$tblhtml .= '
            <tr>
            <td>' . $payment['paymentid'] . '</td>
            <td>' . $payment_name . '</td>
            <td>' . _d($payment['date']) . '</td>
            <td>' . app_format_money($payment['amount'], $invoice->currency_name) . '</td>
            </tr>
        ';
	}
	$tblhtml .= '</tbody>';
	$tblhtml .= '</table>';
}
$tblhtml .= '<table style="font-size:' . ($font_size + 4) . 'px">
<tr height="40"><td></td><td></td></tr>
<tr><td style="width:60%;">';
if (found_invoice_mode($payment_modes, $invoice->id, true, true)) {
	$tblhtml .= '<p></p><div><b>' . _l('invoice_html_offline_payment') . '</b>';

	foreach ($payment_modes as $mode) {
		if (is_numeric($mode['id'])) {
			if (!is_payment_mode_allowed_for_invoice($mode['id'], $invoice->id)) {
				continue;
			}
		}
		if (isset($mode['show_on_pdf']) && $mode['show_on_pdf'] == 1) {
			$tblhtml .= '<br/><br/><b>' . $mode['name'] . '</b><br/>';
			$tblhtml .= $mode['description'];
		}
	}
	$tblhtml .= '</div>';
}

if (!empty($invoice->clientnote)) {
	$tblhtml .= '<p></p><b>' . _l('invoice_note') . '</b><br/>' . $invoice->clientnote;
}

if (!empty($invoice->terms)) {
	$tblhtml .= '<p></p><b>' . _l('terms_and_conditions') . '</b><br/>' . $invoice->terms;
}
$tblhtml .= '</td><td style="width:40%;">';
$tblhtml .= !empty($invoice->datecs_esd_response) ? datecs_esd_itax_info($invoice) : "";
$tblhtml .= '</td></tr></table>';

$content = '<table><tr><td style="width:99%;">' . $tblhtml . '</td><td style="width:1%;"></td></tr></table>';

$pdf->writeHTML($content, true, false, false, false, '');

$pdf->Ln(4);
$footer_thank_msg = '<table border="0" cellpadding="5">';
if (get_option('e_sign_invoice')) {
	$footer_thank_msg .= '<tr><td>' . pdf_signatures() . '</td></tr>';
}
$footer_thank_msg .= '<tr><td style="vertical-align: middle; text-align:center; font-size: ' . ($font_size + 4) . 'px; border-top:2px solid #dedede;"><b>' . strtoupper("Thank You For Your Business") . '</b></td>
</tr>
</table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $footer_thank_msg, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
