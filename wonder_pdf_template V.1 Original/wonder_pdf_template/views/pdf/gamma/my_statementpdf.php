<?php

defined('BASEPATH') or exit('No direct script access allowed');
$doc_title = ucwords(strtolower(_l('statement')));

include_once 'partials/header.php';

$pdf->ln(5);

// Get Y position for the separation
$y = $pdf->getY();

// Bill to
$client_details = '<b>' . _l('statement_bill_to') . '</b><br />';
$client_details .= '<div style="color:#424242;">';

$client_details .= $statement['client']->company . '<br />';
$client_details .= $statement['client']->billing_street . '<br />';
if (!empty($statement['client']->billing_city)) {
	$client_details .= $statement['client']->billing_city;
}
if (!empty($statement['client']->billing_state)) {
	$client_details .= ', ' . $statement['client']->billing_state;
}
$billing_country = get_country_short_name($statement['client']->billing_country);
if (!empty($billing_country)) {
	$client_details .= '<br />' . $billing_country;
}
if (!empty($statement['client']->billing_zip)) {
	$client_details .= ', ' . $statement['client']->billing_zip;
}
if (!empty($statement['client']->vat)) {
	$client_details .= '<br />' . _l('invoice_vat') . ': ' . $statement['client']->vat;
}

$client_details .= '</div>';

$pdf->writeHTMLCell(($dimensions['wk'] / 2) - $dimensions['lm'] + 15, '', '', $y, $client_details, 0, 0, false, true, 'J', true);

$summary = '';
$summary .= '<h2>' . _l('account_summary') . '</h2>';
$summary .= '<div style="color:#676767;">' . _l('statement_from_to', array(_d($statement['from']), _d($statement['to']))) . '</div>';
$summary .= '<hr />';
$summary .= '
<table cellpadding="4" border="0" style="color:#424242;" width="100%">
   <tbody>
      <tr>
          <td align="left"><br /><br />' . _l('statement_beginning_balance') . ':</td>
          <td><br /><br />' . app_format_money($statement['beginning_balance'], $statement['currency']->symbol) . '</td>
      </tr>
      <tr>
          <td align="left">' . _l('invoiced_amount') . ':</td>
          <td>' . app_format_money($statement['invoiced_amount'], $statement['currency']->symbol) . '</td>
      </tr>
      <tr>
          <td align="left">' . _l('amount_paid') . ':</td>
          <td>' . app_format_money($statement['amount_paid'], $statement['currency']->symbol) . '</td>
      </tr>
  </tbody>
  <tfoot>
      <tr>
        <td align="left"><b>' . _l('balance_due') . '</b>:</td>
        <td>' . app_format_money($statement['balance_due'], $statement['currency']->symbol) . '</td>
    </tr>
  </tfoot>
</table>';

$pdf->writeHTMLCell(($dimensions['wk'] / 2) - $dimensions['rm'] - 15, '', '', '', $summary, 0, 1, false, true, 'R', true);

$summary_info = '
<div style="text-align: center;">
    ' . _l('customer_statement_info', array(_d($statement['from']), _d($statement['to']))) . '
</div>';

$pdf->ln(9);
$pdf->writeHTMLCell($dimensions['wk'] - ($dimensions['rm'] + $dimensions['lm']), '', '', $pdf->getY(), $summary_info, 0, 1, false, true, 'C', false);
$pdf->ln(9);

$tmpBeginningBalance = $statement['beginning_balance'];

$borderBody = 'border-bottom-color:#000000;border-bottom-width:1px; border-bottom-style:solid;';
$border = 'border-bottom-color:#000000;border-bottom-width:2px; border-bottom-style:solid; border-top: none;';

$tblhtml = '<table width="100%" cellspacing="0" cellpadding="8" border="0">
<thead>
 <tr  height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . (get_option('pdf_font_size') + 4) . 'px;">
     <th width="13%" style="' . $border . '"><b>' . _l('statement_heading_date') . '</b></th>
     <th width="27%" style="' . $border . '"><b>' . _l('statement_heading_details') . '</b></th>
     <th align="right" style="' . $border . '"><b>' . _l('statement_heading_amount') . '</b></th>
     <th align="right" style="' . $border . '"><b>' . _l('statement_heading_payments') . '</b></th>
     <th align="right" style="' . $border . '"><b>' . _l('statement_heading_balance') . '</b></th>
 </tr>
</thead>
<tbody>
 <tr>
     <td width="13%" style="' . $borderBody . '">' . _d($statement['from']) . '</td>
     <td width="27%" style="' . $borderBody . '">' . _l('statement_beginning_balance') . '</td>
     <td align="right" style="' . $borderBody . '">' . _format_number($statement['beginning_balance']) . '</td>
     <td style="' . $borderBody . '"></td>
     <td align="right" style="' . $borderBody . '">' . _format_number($statement['beginning_balance']) . '</td>
 </tr>';
$count = 0;
foreach ($statement['result'] as $data) {
	$tblhtml .= '<tr' . (++$count % 2 ? " bgcolor=\"#f6f5f5\"" : "") . '>
  <td width="13%" style="' . $borderBody . '">' . _d($data['date']) . '</td>
  <td width="27%" style="' . $borderBody . '">';
	if (isset($data['invoice_id'])) {
		$tblhtml .= _l('statement_invoice_details', array(format_invoice_number($data['invoice_id']), _d($data['duedate'])));
	} else if (isset($data['payment_id'])) {
		$tblhtml .= _l('statement_payment_details', array('#' . $data['payment_id'], format_invoice_number($data['payment_invoice_id'])));
	}
	$tblhtml .= '</td>
    <td align="right" style="' . $borderBody . '">';
	if (isset($data['invoice_id'])) {
		$tblhtml .= _format_number($data['invoice_amount']);
	}
	$tblhtml .= '</td>
        <td align="right" style="' . $borderBody . '">';
	if (isset($data['payment_id'])) {
		$tblhtml .= _format_number($data['payment_total']);
	}
	$tblhtml .= '</td>
            <td align="right" style="' . $borderBody . '">';
	if (isset($data['invoice_id'])) {
		$tmpBeginningBalance = ($tmpBeginningBalance + $data['invoice_amount']);
	} else if (isset($data['payment_id'])) {
		$tmpBeginningBalance = ($tmpBeginningBalance - $data['payment_total']);
	}
	$tblhtml .= _format_number($tmpBeginningBalance);
	$tblhtml .= '</td>
            </tr>';
}
$tblhtml .= '</tbody>
        <tfoot>
         <tr style="color:#424242;">
             <td></td>
             <td></td>
             <td align="right" style="' . $borderBody . '"><b>' . _l('balance_due') . '</b></td>
             <td style="' . $borderBody . '"></td>
             <td align="right" style="' . $borderBody . '">
                 <b>' . app_format_money($statement['balance_due'], $statement['currency']->symbol) . '</b>
             </td>
         </tr>
     </tfoot>
 </table>';

$pdf->writeHTML($tblhtml, true, false, false, false, '');
