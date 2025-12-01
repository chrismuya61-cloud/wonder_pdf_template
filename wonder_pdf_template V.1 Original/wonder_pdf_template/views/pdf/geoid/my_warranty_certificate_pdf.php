<?php
$dimensions = $pdf->getPageDimensions();



$font_size = get_option('pdf_font_size');
if ($font_size == '') {
	$font_size = 10;
}

$pdf->SetMargins(PDF_MARGIN_LEFT, 20, PDF_MARGIN_RIGHT);

$pdf->ln(40);

$heading = '<span style="font-weight:bold;font-size:27px; text-align:center;"><u>' . strtoupper(_l('warranty_certificate')) . '</u></span>';
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $heading, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(10);

//logo area
$info_right_column = '';
$logo_area = '';

$info_right_column = '<div style="color:#424242;">';
$info_right_column .= '<b style="color:black;">' . get_option('invoice_company_name') . '</b><br />';
$info_right_column .= get_option('invoice_company_address') . '<br/>';
if (get_option('invoice_company_city') != '') {
	$info_right_column .= get_option('invoice_company_city') . ', ';
}
$info_right_column .= get_option('company_state') . ' ';
$info_right_column .= get_option('invoice_company_postal_code') . '<br />';
$info_right_column .= get_option('invoice_company_country_code') . '';

if (get_option('invoice_company_phonenumber') != '') {
	$info_right_column .= '<br />' . get_option('invoice_company_phonenumber');
}
if (get_option('company_vat') != '') {
	$info_right_column .= '<br />' . _l('company_vat_number') . ': ' . get_option('company_vat');
}
// check for company custom fields
$custom_company_fields = get_company_custom_fields();
if (count($custom_company_fields) > 0) {
	$info_right_column .= '<br />';
}
foreach ($custom_company_fields as $field) {
	$info_right_column .= $field['label'] . ': ' . $field['value'] . '<br />';
}
$info_right_column .= '</div>';

// write the first column
//$logo_area .= pdf_logo_url();
$pdf->MultiCell(($dimensions['wk'] / 2) - $dimensions['lm'], 0, $logo_area, 0, 'J', 0, 0, '', '', true, 0, true, true, 0);
// write the second column
$pdf->MultiCell(($dimensions['wk'] / 2) - $dimensions['rm'], 0, $info_right_column, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
$pdf->ln(10);

$warranty_body = '<table border="0" cellpadding="5">
                  <thead>
                  <tr><td align="left" style="border:1px solid #ccc; font-size: ' . ($font_size + 4) . 'px; font-weight:bold;" bgcolor="#f4f4f4">Product</td><td align="left" style="border:1px solid #ccc; font-size: ' . ($font_size + 4) . 'px;">' . $warranty->product_name . '</td></tr>
                  <tr><td align="left" style="border:1px solid #ccc; font-size: ' . ($font_size + 4) . 'px; font-weight:bold;" bgcolor="#f4f4f4">Product Code</td><td align="left" style="border:1px solid #ccc; font-size: ' . ($font_size + 4) . 'px;">' . get_option('product_code_prefix') . $warranty->product_code . '</td></tr>
                  <tr><td align="left" style="border:1px solid #ccc; font-size: ' . ($font_size + 4) . 'px; font-weight:bold;" bgcolor="#f4f4f4">Product Serial Number</td><td align="left" style="border:1px solid #ccc; font-size: ' . ($font_size + 4) . 'px;">#' . $warranty->serial . '</td></tr>
                  <tr><td align="left" style="border:1px solid #ccc; font-size: ' . ($font_size + 4) . 'px; font-weight:bold;" bgcolor="#f4f4f4">Date Purchased</td><td align="left" style="border:1px solid #ccc; font-size: ' . ($font_size + 4) . 'px;">' . _d($warranty->date_sold) . '</td></tr>
                  <tr><td align="left" style="border:1px solid #ccc; font-size: ' . ($font_size + 4) . 'px; font-weight:bold;" bgcolor="#f4f4f4">Warranty Period</td><td align="left" style="border:1px solid #ccc; font-size: ' . ($font_size + 4) . 'px;">' . $warranty->warranty_duration . ' Days</td></tr>
                  <tr><td align="left" style="border:1px solid #ccc; font-size: ' . ($font_size + 4) . 'px; font-weight:bold;" bgcolor="#f4f4f4">Warranty Expiry</td><td align="left" style="border:1px solid #ccc; font-size: ' . ($font_size + 4) . 'px;">' . _d($warranty->warranty_end_date) . '</td></tr>
                  </thead></table>';
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $warranty_body, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);

$pdf->Ln(10);
$terms = "<h4>Warranty period</h4>
<p>Any claims on the purchased product, the buyer must submit in all cases only through the dealer from whom the goods were purchased. Prepare your original purchase receipt.</p>
<p>The right to claim warranty given by the manufacturer expires:</p>
<ul>
<li>Failure to present the original proof of purchase in case of a claim</li>
<li>The given warranty period, for the claimed product has expired</li>
<li>Violation of any protective seals and labels, if available on the product</li>
<li>Damage to the goods during transportation such damage must be claimed directly with the shipping forwarder)</li>
<li>Use of goods in conditions that do not match the allowed temperature, dust, moisture, chemical and mechanical environmental influences, usual for the product</li>
<li>Unprofessional use, handling, operation, or negligence of goods</li>
<li>If the goods are damaged by use contrary to the allowed conditions specified in the installation manual or in the supplied documentation or general principles of handling of such products</li>
<li>Warranty does not cover wear and tear caused by normal use</li>
</ul>";
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $terms, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);

$pdf->Ln(10);
$validate = '
<table border="0" cellpadding="5">
<thead>
<tr>
<td style="font-size: ' . ($font_size + 4) . 'px; font-weight:bold;">Authorised Official Sign...............................................................</td>
<td style="font-size: ' . ($font_size + 4) . 'px; font-weight:bold;">Customer Sign...............................................................</td>
</tr>
</thead>
</table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $validate, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);

$pdf->Ln(10);
$footer_thank_msg = '
<table border="0" cellpadding="5">
<thead>
<tr>
<td style="vertical-align: middle; text-align:center; font-size: ' . ($font_size + 4) . 'px; border-top:2px solid #dedede;"><b>' . strtoupper("Thank You for Giving Us the Chance to Serve You!") . '</b></td>
</tr>
</thead>
</table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $footer_thank_msg, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
