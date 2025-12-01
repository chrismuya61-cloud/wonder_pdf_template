<?php

defined('BASEPATH') or exit('No direct script access allowed');
$doc_title = ucfirst(strtolower(_l('proposal')));

include_once 'partials/header.php';

// Proposal to
$client_details = '<b>' . _l('proposal_to') . '</b>';
$client_details .= '<div  style="font-weight:400 !important; color:#424242; font-size:' . ($font_size + 4) . 'px;">';
$client_details .= $proposal->proposal_to . '<br />';

if (!empty($proposal->address)) {
	$client_details .= $proposal->address . '<br />';
}
if (!empty($proposal->city)) {
	$client_details .= $proposal->city;
}
if (!empty($proposal->state)) {
	$client_details .= ', ' . $proposal->state;
}
$country = get_country_short_name($proposal->country);
if (!empty($country)) {
	$client_details .= '<br />' . $country;
}
if (!empty($proposal->zip)) {
	$client_details .= ', ' . $proposal->zip;
}
if (!empty($proposal->email)) {
	$client_details .= '<br />' . $proposal->email;
}
if (!empty($proposal->phone)) {
	$client_details .= '<br />' . $proposal->phone;
}
$client_details .= '</div>';

$client_details .= '</div>';

$proposal_date = _l('proposal_date') . ': ' . _d($proposal->date);
$open_till = '';

if (!empty($proposal->open_till)) {
	$open_till = _l('proposal_open_till') . ': ' . _d($proposal->open_till) . '<br />';
}

$info_right_column = '<b># ' . $number . '</b>
<div  style="font-weight:400 !important; color:#424242; font-size:' . ($font_size + 4) . 'px;"><span style="color:#000;">' . $proposal->subject . '</span><br />
' . $proposal_date . '
<br />
' . $open_till . '</div>';

$info_left_column = $client_details;

pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->ln(6);

$qty_heading = _l('estimate_table_quantity_heading', '', false);

if ($proposal->show_quantity_as == 2) {
	$qty_heading = _l($this->type . '_table_hours_heading', '', false);
} elseif ($proposal->show_quantity_as == 3) {
	$qty_heading = _l('estimate_table_quantity_heading', '', false) . '/' . _l('estimate_table_hours_heading', '', false);
}

$proposal_date = _l('proposal_date') . ': ' . _d($proposal->date);
$open_till = '';
if (!empty($proposal->open_till)) {
	$open_till = _l('proposal_open_till') . ': ' . _d($proposal->open_till);
}
$custom_fields_data = '';
$pdf_custom_fields = get_custom_fields('proposal', array('show_on_pdf' => 1));
foreach ($pdf_custom_fields as $field) {
	$value = get_custom_field_value($proposal->id, $field['id'], 'proposal');
	if ($value == '') {continue;}
	$custom_fields_data .= $field['name'] . ': ' . $value . '<br />';
}
// Add new line if found custom fields so the custom field can go on the next line
if ($custom_fields_data != '') {
	$custom_fields_data = '<br />' . $custom_fields_data;
}

$item_width = 38;
// If show item taxes is disabled in PDF we should increase the item width table heading
if (get_option('show_tax_per_item') == 0) {
	$item_width = $item_width + 15;
}

// Header
$qty_heading = _l('estimate_table_quantity_heading');
if ($proposal->show_quantity_as == 2) {
	$qty_heading = _l('estimate_table_hours_heading');
} elseif ($proposal->show_quantity_as == 3) {
	$qty_heading = _l('estimate_table_quantity_heading') . '/' . _l('estimate_table_hours_heading');
}

$border = 'border-bottom-color:#000000;border-bottom-width:2px; border-bottom-style:solid; border-top: none;';
$tblhtml = '
<table width="100%" border="0" bgcolor="#fff" cellspacing="0" cellpadding="8">
    <tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . '; font-weight:bold; font-size:' . (get_option('pdf_font_size') + 4) . 'px;">
        <th width="5%;" align="center" style="' . $border . '">#</th>
        <th width="' . $item_width . '%" align="left" style="' . $border . '">Description</th>
        <th width="12%" align="right" style="' . $border . '">' . ucfirst(strtolower(($qty_heading))) . '</th>
        <th width="15%" align="right" style="' . $border . '">' . ucfirst(strtolower(_l('estimate_table_rate_heading'))) . '</th>';
if (get_option('show_tax_per_item') == 1) {
	$tblhtml .= '<th width="15%" align="right" style="' . $border . '">' . ucfirst(strtolower(_l('estimate_table_tax_heading'))) . '</th>';
}
$tblhtml .= '<th width="15%" align="right" style="' . $border . '">' . ucfirst(strtolower(_l('estimate_table_amount_heading'))) . '</th>
</tr>';
// Items
$tblhtml .= '<tbody>';

$items_data = get_table_gamma_items_and_taxes($proposal->items, 'proposal');

$taxes = $items_data['taxes'];
$tblhtml .= $items_data['html'];

$tblhtml .= '</tbody>';
$tblhtml .= '</table>';
$tblhtml .= '<br /><br />';
$tbltotal = '';

$borderTotal = 'border-bottom-color:#000000;border-bottom-width:1px; border-bottom-style:solid;';

$tbltotal .= '<table cellpadding="8" style="font-size:' . ($font_size + 2) . 'px" border="0">';
$tbltotal .= '
<tr>
    <td width="65%"></td>
    <td align="right" width="20%" style="' . $borderTotal . '">' . _l('estimate_subtotal') . '</td>
    <td align="right" width="15%" style="' . $borderTotal . '">' . app_format_money($proposal->subtotal, $proposal->symbol) . '</td>
</tr>';
if ($proposal->discount_percent != 0) {
	$tbltotal .= '
    <tr>
    <td width="65%"></td>
        <td align="right" width="20%" style="' . $borderTotal . '">' . _l('estimate_discount') . '(' . _format_number($proposal->discount_percent, true) . '%)' . '</td>
        <td align="right" width="15%" style="' . $borderTotal . '">-' . app_format_money($proposal->discount_total, $proposal->symbol) . '</td>
    </tr>';
}
foreach ($taxes as $tax) {
	$total = array_sum($tax['total']);
	if ($proposal->discount_percent != 0 && $proposal->discount_type == 'before_tax') {
		$total_tax_calculated = ($total * $proposal->discount_percent) / 100;
		$total = ($total - $total_tax_calculated);
	}
	// The tax is in format TAXNAME|20
	$_tax_name = explode('|', $tax['tax_name']);
	$tbltotal .= '<tr>
    <td width="65%"></td>
    <td align="right" width="20%" style="' . $borderTotal . '">' . $_tax_name[0] . '(' . _format_number($tax['taxrate']) . '%)' . '</td>
    <td align="right" width="15%" style="' . $borderTotal . '">' . app_format_money($total, $proposal->symbol) . '</td>
</tr>';
}
if ((int) $proposal->adjustment != 0) {
	$tbltotal .= '<tr>
    <td width="65%"></td>
    <td align="right" width="20%" style="' . $borderTotal . '">' . _l('estimate_adjustment') . '</td>
    <td align="right" width="15%" style="' . $borderTotal . '">' . app_format_money($proposal->adjustment, $proposal->symbol) . '</td>
</tr>';
}
$tbltotal .= '
<tr style="color:red; font-weight:bold;">
    <td width="65%"></td>
    <td align="right" width="20%" style="' . $borderTotal . '">' . _l('estimate_total') . '</td>
    <td align="right" width="15%" style="' . $borderTotal . '">' . app_format_money($proposal->total, $proposal->symbol) . '</td>
</tr>';

if ($proposal->status == 3) {
	$tbltotal .= '
    <tr>
    <td width="65%"></td>
        <td align="right" width="20%" style="' . $borderTotal . '">' . _l('estimate_total_paid') . '</td>
        <td align="right" width="15%" style="' . $borderTotal . '">' . app_format_money(sum_from_table('tblestimatepaymentrecords', array(
		'field' => 'amount',
		'where' => array(
			'estimateid' => $proposal->id,
		),
	)), $proposal->symbol) . '</td>
    </tr>
    <tr>
    <td width="65%"></td>
       <td align="right" width="20%" style="' . $borderTotal . '">' . _l('estimate_amount_due') . '</td>
       <td align="right" width="15%" style="' . $borderTotal . '">' . app_format_money(get_estimate_total_left_to_pay($proposal->id, $proposal->total), $proposal->symbol) . '</td>
   </tr>';
}
$tbltotal .= '</table>';
$tblhtml .= $tbltotal;
if (get_option('total_to_words_enabled') == 1) {
	$tblhtml .= '<br /><br /><br />';
	$tblhtml .= '<strong style="text-align:center;">' . _l('num_word') . ': ' . $CI->numberword->convert($proposal->total, $proposal->currency_name) . '</strong>';
}
$proposal->content = str_replace('{proposal_items}', $tblhtml, $proposal->content);
// Get the proposals css
$css = file_get_contents(FCPATH . 'assets/css/proposals.css');
// Theese lines should aways at the end of the document left side. Dont indent these lines
$html = <<<EOF
<style>
    $css
</style>
<div style="width:675px !important;">
$proposal->content
</div>
EOF;
$pdf->writeHTML($html, true, false, true, false, '');
