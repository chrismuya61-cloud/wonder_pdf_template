<?php

defined('BASEPATH') or exit('No direct script access allowed');
$doc_title = ucwords(strtolower(_l('Warranty Card')));
$titleTopMargin = -10;

include_once 'partials/header.php';

$pdf->Ln(2);

$warranty_body = '<table border="0" cellpadding="5">
                  <thead>
                  <tr><td align="left" style="border:1px solid #f4f4f4; font-size: ' . ($font_size + 4) . 'px; font-weight:bold;" >Product</td><td align="left" style="border:1px solid #f4f4f4; font-size: ' . ($font_size + 4) . 'px;">' . $warranty->name . '</td></tr>
                  <tr><td align="left" style="border:1px solid #f4f4f4; font-size: ' . ($font_size + 4) . 'px; font-weight:bold;" >Product Code</td><td align="left" style="border:1px solid #f4f4f4; font-size: ' . ($font_size + 4) . 'px;">' . $warranty->commodity_code . '</td></tr>
                  <tr><td align="left" style="border:1px solid #f4f4f4; font-size: ' . ($font_size + 4) . 'px; font-weight:bold;" >Product Serial No.</td><td align="left" style="border:1px solid #f4f4f4; font-size: ' . ($font_size + 4) . 'px;">' . $warranty->serial_number. '</td></tr>
                  <tr><td align="left" style="border:1px solid #f4f4f4; font-size: ' . ($font_size + 4) . 'px; font-weight:bold;" >Date Purchased</td><td align="left" style="border:1px solid #f4f4f4; font-size: ' . ($font_size + 4) . 'px;">' . _d($warranty->date_sold) . '</td></tr>
                  <tr><td align="left" style="border:1px solid #f4f4f4; font-size: ' . ($font_size + 4) . 'px; font-weight:bold;" >Warranty Period</td><td align="left" style="border:1px solid #f4f4f4; font-size: ' . ($font_size + 4) . 'px;">' . 365 . ' Days</td></tr>
                  <tr><td align="left" style="border:1px solid #f4f4f4; font-size: ' . ($font_size + 4) . 'px; font-weight:bold;" >Warranty Expiry</td><td align="left" style="border:1px solid #f4f4f4; font-size: ' . ($font_size + 4) . 'px;">' . _d($warranty->warranty_end_date) . '</td></tr>
                  </thead></table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $warranty_body, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);

$pdf->Ln(5);
$terms = "<span font-size='" . ($font_size + 2) . "px;'><b>Warranty period</b>
<p>This warranty applies to products, manufactured by Pentax, FOIF, TOKNAV, E-SURVEY, SINOGNSS, MUYA, BAOFENG, MILESEEY, PQWT, AIDUSH, LANGRY, HICHANCE, GARMIN, TOPCON, LEICA, HIRA, UTEST and disputed with the purchase contract or defects have been
submitted to the seller within the warranty period. With expert and professional training and installation you can significantly influence functionality, utility value of the product and of
course user satisfaction. Measurement Systems Limited products, sold and installed by us are provided with a 1-year warranty period only from the date of receipt / purchase of goods
from the dealer.</p>
<b>Warranty conditions</b>
<p>The Buyer is required to inspect the goods upon receipt from the seller. Subsequent claims related to obvious defects are not accepted by Measurement Systems Limited.</br>
Any claims on the purchased product, the buyer must submit in all cases only through the dealer from whom the goods were purchased. Prepare your original purchase receipt.</p>
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
</ul></span>";
$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $terms, 0, 'L', 0, 1, '', '', true, 0, true, false, 0);

$pdf->Ln(6);
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

$pdf->Ln(6);
$footer_thank_msg = '
<table border="0" cellpadding="5">
<thead>
<tr>
<td style="vertical-align: middle; text-align:center; font-size: ' . ($font_size + 4) . 'px; border-top:1px solid ' . get_option('pdf_table_border_color') . ';"><b>' . strtoupper("Thank You For Your Business") . '</b></td>
</tr>
</thead>
</table>';

$pdf->MultiCell(($dimensions['wk']) - $dimensions['rm'] - $dimensions['lm'], 0, $footer_thank_msg, 0, 'R', 0, 1, '', '', true, 0, true, false, 0);
