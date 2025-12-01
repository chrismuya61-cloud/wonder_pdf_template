<?php

defined('BASEPATH') or exit('No direct script access allowed');
$doc_title = ucfirst(strtolower(_l('Contract')));

include_once 'partials/header.php';

// These lines should aways at the end of the document left side. Dont indent these lines
$html = <<<EOF
<div style="width:680px !important;">
$contract->content
</div>
EOF;
$pdf->writeHTML($html, true, false, true, false, '');
