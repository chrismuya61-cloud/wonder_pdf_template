<?php

defined('BASEPATH') or exit('No direct script access allowed');

// FIX: Initialize standard variables
$CI = &get_instance();
$font_size = get_option('pdf_font_size');
$dimensions = $pdf->getPageDimensions();

// --- HEADER SECTION ---
$info_right_column = '<div style="color:#424242; font-size: 14px;">' . format_organization_info() . '</div>';
$info_left_column = wonder_pdf_logo_url();

// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 3.33) - $dimensions['lm']);

$pdf->ln(2);
$underLine = '<div style="width:100%; border-bottom: 2px solid red;"></div>';
$pdf->writeHTML($underLine, true, false, false, false, '');

$pdf->ln(-8);

$expense_heading = ucfirst(strtolower(_l('expense_receipt')));

$title = '<span style="font-weight:600; font-size: 38pt;  text-align:right">' . $expense_heading . '</span><br />';
// Note: Ensure brands.png exists in your module assets
$brands = '<img src="' . module_dir_path(WPDF_TEMPLATE, 'assets/images/brands.png') . '"/>';

pdf_multi_row($brands, $title, $pdf, ($dimensions['wk'] / 2.5) - $dimensions['lm']);
$pdf->ln(1);

// --- EXPENSE INFO SECTION ---
$expense_info = '';

// Expense Name/Category
$expense_info .= '<b style="color:#4e4e4e; font-size: 14px;">' . $expense->category_name . '</b><br />';

// Date
$expense_info .= '<span style="font-weight:bold;">' . _l('expense_dt_table_heading_date') . ':</span> ' . _d($expense->date) . '<br />';

// Reference
if (!empty($expense->reference_no)) {
    $expense_info .= '<span style="font-weight:bold;">' . _l('expense_dt_table_heading_reference_no') . ':</span> ' . $expense->reference_no . '<br />';
}

// Payment Mode
if (!empty($expense->payment_mode) && !empty($expense->payment_mode_name)) {
    $expense_info .= '<span style="font-weight:bold;">' . _l('expense_dt_table_heading_payment_mode') . ':</span> ' . $expense->payment_mode_name . '<br />';
}

// Project
if ($expense->project_id != 0 && get_option('show_project_on_expense') == 1) {
    $expense_info .= '<span style="font-weight:bold;">' . _l('project') . ':</span> ' . get_project_name_by_id($expense->project_id) . '<br />';
}

// Bill to (Customer)
$customer_info = '';
if ($expense->clientid != 0) {
    $customer_info .= '<span style="font-weight:bold;">' . _l('expense_receipt_to') . ':</span><br />';
    $customer_info .= '<div style="font-weight:400 !important; color:#4e4e4e;">';
    $customer_info .= format_customer_info($expense->clientid, 'statement', 'billing'); // Using statement format as it handles IDs well
    $customer_info .= '</div>';
}

// Left/Right Column Rendering
pdf_multi_row($customer_info, $expense_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->ln(6);

// --- ITEM / DESCRIPTION TABLE ---
// Expenses don't use the standard items table function, so we build a simple one matching the style.
$border = 'border-bottom-color:#000000;border-bottom-width:1px; border-bottom-style:solid;';
$border_header = 'border-bottom-color:#000000;border-bottom-width:2px; border-bottom-style:solid;';

$tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="8">';

// Table Header
$tblhtml .= '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . ($font_size + 2) . 'px;">';
$tblhtml .= '<th width="5%" align="center" style="' . $border_header . '">#</th>';
$tblhtml .= '<th width="65%" align="left" style="' . $border_header . '">' . _l('expense_name') . '</th>';
$tblhtml .= '<th width="30%" align="right" style="' . $border_header . '">' . _l('expense_amount') . '</th>';
$tblhtml .= '</tr>';

// Table Body
$tblhtml .= '<tbody>';
$tblhtml .= '<tr>';
$tblhtml .= '<td width="5%" align="center" style="' . $border . '">1</td>';
$tblhtml .= '<td width="65%" align="left" style="' . $border . '">';
$tblhtml .= '<b>' . $expense->expense_name . '</b>';
if (!empty($expense->note)) {
    $tblhtml .= '<br /><span style="color:#777; font-size: smaller;">' . $expense->note . '</span>';
}
$tblhtml .= '</td>';
$tblhtml .= '<td width="30%" align="right" style="' . $border . '">' . app_format_money($expense->amount, $expense->currency_data) . '</td>';
$tblhtml .= '</tr>';
$tblhtml .= '</tbody>';
$tblhtml .= '</table>';

// --- TOTALS SECTION ---
$tbltotal = '';
$tbltotal .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';

// Tax
if ($expense->tax != 0) {
    $tbltotal .= '
    <tr>
        <td align="right" width="70%"></td>
        <td align="right" width="20%" style="' . $border . '">' . _l('tax_1') . '</td>
        <td align="right" width="10%" style="' . $border . '">' . app_format_money($expense->total_tax, $expense->currency_data) . '</td>
    </tr>';
}

if ($expense->tax2 != 0) {
    $tbltotal .= '
    <tr>
        <td align="right" width="70%"></td>
        <td align="right" width="20%" style="' . $border . '">' . _l('tax_2') . '</td>
        <td align="right" width="10%" style="' . $border . '">' . app_format_money($expense->tax2, $expense->currency_data) . '</td>
    </tr>';
}

// Total Amount
$tbltotal .= '
<tr style="color:red; font-weight:bold;">
    <td align="right" width="70%"></td>
    <td align="right" width="20%" style="background-color:#f0f0f0;' . $border . '">' . _l('expense_total') . '</td>
    <td align="right" width="10%" style="background-color:#f0f0f0;' . $border . '">' . app_format_money($expense->amount + $expense->total_tax, $expense->currency_data) . '</td>
</tr>';

$tbltotal .= '</table>';

$tblhtml .= $tbltotal;

// Number to words
if (get_option('total_to_words_enabled') == 1) {
    $tblhtml .= '<br /><br /><b>' . _l('num_word') . ': ' . $CI->numberword->convert($expense->amount + $expense->total_tax, $expense->currency_data) . '</b>';
}

$pdf->writeHTML($tblhtml, true, false, false, false, '');

// --- FOOTER ---
$pdf->Ln(4);
$footer_thank_msg = '<table border="0" cellpadding="5">';
$footer_thank_msg .= '<tr><td style="vertical-align: middle; text-align:center; font-size: ' . ($font_size + 4) . 'px; border-top:2px solid #dedede;"><b>' . strtoupper("Thank You For Your Business") . '</b></td>
</tr>
</table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $footer_thank_msg, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);