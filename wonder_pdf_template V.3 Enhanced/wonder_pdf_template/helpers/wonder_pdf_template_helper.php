<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('wonder_pdf_logo_url')) {

    /**
     * Fetches custom pdf logo url for pdf or use the default logo uploaded for the company
     * Additional statements applied because this function wont work on all servers. All depends how the server is configured.
     * @return string
     */
    function wonder_pdf_logo_url()
    {
        $custom_pdf_logo_image_url = get_option('custom_pdf_logo_image_url');
        $width = get_option('pdf_logo_width');
        $companyUploadPath = get_upload_path_by_type('company');
        $logoUrl = '';

        if ($width == '') {
            $width = 250;
        }

        if ($custom_pdf_logo_image_url != '') {
            $logoUrl = $custom_pdf_logo_image_url;
        } else {
            if (get_option('company_logo_dark') != '' && file_exists($companyUploadPath . get_option('company_logo_dark'))) {
                $logoUrl = $companyUploadPath . get_option('company_logo_dark');
            } elseif (get_option('company_logo') != '' && file_exists($companyUploadPath . get_option('company_logo'))) {
                $logoUrl = $companyUploadPath . get_option('company_logo');
            }
        }

        $logoImage = '';

        if ($logoUrl != '') {
            $logoImage = '<img width="' . $width . 'px" src="' . $logoUrl . '">';
        }

        return hooks()->apply_filters('pdf_logo_url', $logoImage);
    }
}

/**
 * Get items table for preview
 * @param  object  $transaction   e.q. invoice, estimate from database result row
 * @param  string  $type          type, e.q. invoice, estimate, proposal
 * @param  string  $for           where the items will be shown, html or pdf
 * @param  boolean $admin_preview is the preview for admin area
 * @return object
 */
function wonder_get_items_table_data($transaction, $type, $for = 'html', $admin_preview = false)
{
    // FIX: Check database option instead of undefined constant TEMPLATE
    $active_template = get_option('wonder_pdf_active_template');
    
    // Default to Gamma if option is not set or matches gamma
    if ($active_template == 'gamma' || empty($active_template)) {
        include_once module_dir_path(WPDF_TEMPLATE, 'libraries/Gamma_app_items_table.php');
        $class = new Gamma_app_items_table($transaction, $type, $for, $admin_preview);

        $class = hooks()->apply_filters('gamma_items_table_class', $class, $transaction, $type, $for, $admin_preview);

        if (!$class instanceof App_items_table_template) {
            show_error(get_class($class) . ' must be instance of "Gamma_app_items_template"');
        }
        
        return $class;
    }
    
    // Fallback if no template matches
    return null;
}

if (!function_exists('wonder_format_customer_info')) {
    /**
     * Format customer address info
     * @param  object  $data        customer object from database
     * @param  string  $for         where this format will be used? Eq statement invoice etc
     * @param  string  $type        billing/shipping
     * @param  boolean $companyLink company link to be added on customer company/name, this is used in admin area only
     * @return string
     */
    function wonder_format_customer_info($data, $for, $type, $companyLink = false)
    {
        $format = get_option('customer_info_format');
        $clientId = '';

        if ($for == 'statement') {
            $clientId = $data->userid;
        } elseif ($type == 'billing') {
            $clientId = $data->clientid;
        }

        $companyName = '';
        if ($for == 'statement') {
            $companyName = get_company_name($clientId);
        } elseif ($type == 'billing') {
            $companyName = $data->client->company;
        }

        if ($for == 'invoice' || $for == 'estimate' || $for == 'payment' || $for == 'credit_note') {
            if (isset($data->client->show_primary_contact) && $data->client->show_primary_contact == 1) {
                $primaryContactId = get_primary_contact_user_id($clientId);
                if ($primaryContactId) {
                    $companyName = get_contact_full_name($primaryContactId) . '<br />' . $companyName;
                }
            }
        }

        $street = '';
        if ($type == 'billing') {
            $street = $data->billing_street;
        } elseif ($type == 'shipping') {
            $street = $data->shipping_street;
        }

        $city = '';
        if ($type == 'billing') {
            $city = $data->billing_city;
        } elseif ($type == 'shipping') {
            $city = $data->shipping_city;
        }
        $state = '';
        if ($type == 'billing') {
            $state = $data->billing_state;
        } elseif ($type == 'shipping') {
            $state = $data->shipping_state;
        }
        $zipCode = '';
        if ($type == 'billing') {
            $zipCode = $data->billing_zip;
        } elseif ($type == 'shipping') {
            $zipCode = $data->shipping_zip;
        }

        $countryCode = '';
        $countryName = '';
        $country = null;
        if ($type == 'billing') {
            $country = get_country($data->billing_country);
        } elseif ($type == 'shipping') {
            $country = get_country($data->shipping_country);
        }

        if ($country) {
            $countryCode = $country->iso2;
            $countryName = $country->short_name;
        }

        $phone = '';
        if ($for == 'statement' && isset($data->phonenumber)) {
            $phone = $data->phonenumber;
        } elseif ($type == 'billing' && isset($data->client->phonenumber)) {
            $phone = $data->client->phonenumber;
        }

        $vat = '';
        if ($for == 'statement' && isset($data->vat)) {
            $vat = $data->vat;
        } elseif ($type == 'billing' && isset($data->client->vat)) {
            $vat = $data->client->vat;
        }

        if ($companyLink && (!isset($data->deleted_customer_name) || (isset($data->deleted_customer_name) && empty($data->deleted_customer_name)))) {
            $companyName = '<a href="' . admin_url('clients/client/' . $clientId) . '" target="_blank">' . $companyName . '</a>';
        } elseif ($companyName != '') {
            $companyName = $companyName;
        }

        $format = _info_format_replace('company_name', $companyName, $format);
        $format = _info_format_replace('customer_id', $clientId, $format);
        $format = _info_format_replace('street', $street, $format);
        $format = _info_format_replace('city', $city, $format);
        $format = _info_format_replace('state', $state, $format);
        $format = _info_format_replace('zip_code', $zipCode, $format);
        $format = _info_format_replace('country_code', $countryCode, $format);
        $format = _info_format_replace('country_name', $countryName, $format);
        $format = _info_format_replace('phone', $phone, $format);
        $format = _info_format_replace('vat_number', $vat, $format);
        $format = _info_format_replace('vat_number_with_label', $vat == '' ? '' : _l('client_vat_number') . ': ' . $vat, $format);

        $customFieldsCustomer = [];

        // On shipping address no custom fields are shown
        if ($type != 'shipping') {
            $whereCF = [];

            if (is_custom_fields_for_customers_portal()) {
                $whereCF['show_on_client_portal'] = 1;
            }

            $customFieldsCustomer = get_custom_fields('customers', $whereCF);
        }

        foreach ($customFieldsCustomer as $field) {
            $value = get_custom_field_value($clientId, $field['id'], 'customers');
            $format = _info_format_custom_field($field['id'], $field['name'], $value, $format);
        }

        // If no custom fields found replace all custom fields merge fields to empty
        $format = _info_format_custom_fields_check($customFieldsCustomer, $format);
        $format = _maybe_remove_first_and_last_br_tag($format);

        // Remove multiple white spaces
        $format = preg_replace('/\s+/', ' ', $format);
        $format = trim($format);

        return hooks()->apply_filters('customer_info_text', $format, [
            'data' => $data,
            'for' => $for,
            'type' => $type,
            'company_link' => $companyLink,
        ]);
    }
}

if (!function_exists('set_qrcode')) {
    /*** QRcode Generate ***/
    function set_qrcode($data)
    {
        $CI = &get_instance();
        //load library
        $CI->load->library(WPDF_TEMPLATE . '/Tazamali_QRcode');
        $filePath = TEMP_FOLDER . 'temp_file.png';

        $codeContents = site_url('invoice/' . $data->id . '/' . $data->hash);

        QRcode::png($codeContents, $filePath, QR_ECLEVEL_L, 3, 0);

        $qrImage = file_get_contents($filePath);

        unlink($filePath);

        return base64_encode($qrImage);
    }
}


if (!function_exists('pdf_get_table_items_and_taxes')) {
    /**
     * Function for all table items HTML and PDF
     * @param  array  $items         all items
     * @param  string  $type          where do items come form, eq invoice,estimate,proposal etc..
     * @param  boolean $admin_preview in admin preview add additional sortable classes
     * @return array
     */
    function pdf_get_table_items_and_taxes($items, $type, $admin_preview = false, $table_border = 'border:1px solid #ccc;')
    {
        $cf = count($items) > 0 ? get_items_custom_fields_for_table_html($items[0]['rel_id'], $type) : [];

        static $rel_data = null;

        $result['html']    = '';
        $result['taxes']   = [];
        $_calculated_taxes = [];
        $i                 = 1;
        foreach ($items as $item) {

              // No relation data on preview becuase taxes are not saved in database
            if (!defined('INVOICE_PREVIEW_SUBSCRIPTION')) {
                if (!$rel_data) {
                    $rel_data = get_relation_data($item['rel_type'], $item['rel_id']);
                }
            } else {
                $rel_data = $GLOBALS['items_preview_transaction'];
            }

            $item_taxes = [];

            // Separate functions exists to get item taxes for Invoice, Estimate, Proposal, Credit Note
            $func_taxes = 'get_' . $type . '_item_taxes';
            if (function_exists($func_taxes)) {
                $item_taxes = call_user_func($func_taxes, $item['id']);
            }

            $itemHTML        = '';
            $trAttributes    = '';
            $tdFirstSortable = '';

            if ($admin_preview == true) {
                $trAttributes    = ' class="sortable" data-item-id="' . $item['id'] . '"';
                $tdFirstSortable = ' class="dragger item_no"';
            }

            if (class_exists('pdf', false) || class_exists('app_pdf', false)) {
                $font_size = get_option('pdf_font_size');
                if ($font_size == '') {
                    $font_size = 10;
                }

                $trAttributes .= ' style="font-size:' . ($font_size + 4) . 'px;"';
            }

            $itemHTML .= '<tr nobr="true"' . $trAttributes . '>';
            $itemHTML .= '<td' . $tdFirstSortable . ' align="center" style="'.$table_border.'">' . $i . '</td>';

            $itemHTML .= '<td class="description" align="left;" style="'.$table_border.'">';
            if (!empty($item['description'])) {
                $itemHTML .= '<span style="font-size:' . (isset($font_size) ? $font_size + 4 : '') . 'px;"><strong>' . $item['description'] . '</strong></span>';

                if (!empty($item['long_description'])) {
                    $itemHTML .= '<br />';
                }
            }
            if (!empty($item['long_description'])) {
                $itemHTML .= '<span style="color:#424242;">' . $item['long_description'] . '</span>';
            }

            $itemHTML .= '</td>';

            foreach ($cf as $custom_field) {
                $itemHTML .= '<td align="left" style="'.$table_border.'">' . get_custom_field_value($item['id'], $custom_field['id'], 'items') . '</td>';
            }

            $itemHTML .= '<td align="right" style="'.$table_border.'">' . floatVal($item['qty']);
            if ($item['unit']) {
                $itemHTML .= ' ' . $item['unit'];
            }

            $rate = hooks()->apply_filters(
                'item_preview_rate',
                app_format_number($item['rate']),
                ['item' => $item, 'relation' => $rel_data, 'taxes' => $item_taxes]
            );

            $itemHTML .= '</td>';
            $itemHTML .= '<td align="right" style="'.$table_border.'">' . $rate . '</td>';
            if (get_option('show_tax_per_item') == 1) {
                $itemHTML .= '<td align="right" style="'.$table_border.'">';
            }

            if (defined('INVOICE_PREVIEW_SUBSCRIPTION')) {
                $item_taxes = $item['taxname'];
            }

            if (count($item_taxes) > 0) {
                foreach ($item_taxes as $tax) {
                    $calc_tax     = 0;
                    $tax_not_calc = false;

                    if (!in_array($tax['taxname'], $_calculated_taxes)) {
                        array_push($_calculated_taxes, $tax['taxname']);
                        $tax_not_calc = true;
                    }
                    if ($tax_not_calc == true) {
                        $result['taxes'][$tax['taxname']]          = [];
                        $result['taxes'][$tax['taxname']]['total'] = [];
                        array_push($result['taxes'][$tax['taxname']]['total'], (($item['qty'] * $item['rate']) / 100 * $tax['taxrate']));
                        $result['taxes'][$tax['taxname']]['tax_name'] = $tax['taxname'];
                        $result['taxes'][$tax['taxname']]['taxrate']  = $tax['taxrate'];
                    } else {
                        array_push($result['taxes'][$tax['taxname']]['total'], (($item['qty'] * $item['rate']) / 100 * $tax['taxrate']));
                    }
                    if (get_option('show_tax_per_item') == 1) {
                        $item_tax = '';
                        if ((count($item_taxes) > 1 && get_option('remove_tax_name_from_item_table') == false) || get_option('remove_tax_name_from_item_table') == false || multiple_taxes_found_for_item($item_taxes)) {
                            $tmp      = explode('|', $tax['taxname']);
                            $item_tax = $tmp[0] . ' ' . app_format_number($tmp[1]) . '%<br />';
                        } else {
                            $item_tax .= app_format_number($tax['taxrate']) . '%';
                        }

                        $itemHTML .= hooks()->apply_filters('item_tax_table_row', $item_tax, [
                            'item_taxes' => $item_taxes,
                            'item_id'    => $item['id'],
                        ]);
                    }
                }
            } else {
                if (get_option('show_tax_per_item') == 1) {
                    $itemHTML .= hooks()->apply_filters('item_tax_table_row', '0%', [
                            'item_taxes' => $item_taxes,
                            'item_id'    => $item['id'],
                        ]);
                }
            }

            if (get_option('show_tax_per_item') == 1) {
                $itemHTML .= '</td>';
            }

            /**
             * Possible action hook user to include tax in item total amount calculated with the quantiy
             * eq Rate * QTY + TAXES APPLIED
             */

            $item_amount_with_quantity = hooks()->apply_filters(
                'item_preview_amount_with_currency',
                app_format_number(($item['qty'] * $item['rate'])),
                [
                'item'       => $item,
                'item_taxes' => $item_taxes,
            ]
            );

            $itemHTML .= '<td class="amount" align="right" style="'.$table_border.'">' . $item_amount_with_quantity . '</td>';
            $itemHTML .= '</tr>';
            $result['html'] .= $itemHTML;
            $i++;
        }

        if ($rel_data) {
            foreach ($result['taxes'] as $tax) {
                $total_tax = array_sum($tax['total']);
                if ($rel_data->discount_percent != 0 && $rel_data->discount_type == 'before_tax') {
                    $total_tax_tax_calculated = ($total_tax * $rel_data->discount_percent) / 100;
                    $total_tax                = ($total_tax - $total_tax_tax_calculated);
                } elseif ($rel_data->discount_total != 0 && $rel_data->discount_type == 'before_tax') {
                    $t         = ($rel_data->discount_total / $rel_data->subtotal) * 100;
                    $total_tax = ($total_tax - $total_tax * $t / 100);
                }

                $result['taxes'][$tax['tax_name']]['total_tax'] = $total_tax;
                // Tax name is in format NAME|PERCENT
                $tax_name_array                               = explode('|', $tax['tax_name']);
                $result['taxes'][$tax['tax_name']]['taxname'] = $tax_name_array[0];
            }
        }

        // Order taxes by taxrate
        // Lowest tax rate will be on top (if multiple)
        usort($result['taxes'], function ($a, $b) {
            return $a['taxrate'] - $b['taxrate'];
        });

        $rel_data = null;

        return hooks()->apply_filters('before_return_table_items_html_and_taxes', $result, [
            'items'         => $items,
            'type'          => $type,
            'admin_preview' => $admin_preview,
        ]);
    }
}

/**
 * Format credit note status - OVERRIDE
 * @param  mixed  $status credit note current status
 * @param  boolean $text   to return text or with applied styles
 * @return string
 */
function pdf_format_credit_note_status($status, $text = false)
{
    $CI = &get_instance();
    if (!class_exists('credit_notes_model')) {
        $CI->load->model('credit_notes_model');
    }

    $statuses    = $CI->credit_notes_model->get_statuses();
    $statusArray = false;
    foreach ($statuses as $s) {
        if ($s['id'] == $status) {
            $statusArray = $s;

            break;
        }
    }

    if (!$statusArray) {
        return $status;
    }

    if ($text) {
        return $statusArray['name'];
    }

    $style = 'border: 1px solid ' . $statusArray['color'] . ';';
    $class = 'label s-status';

    return '<span class="' . $class . '" style="' . $style . '">' . $statusArray['name'] . '</span>';
}

if (!function_exists('get_table_gamma_items_and_taxes')) {
/**
 * Pluggable function for all table items HTML and PDF
 * @param  array  $items         all items
 * @param  [type]  $type          where do items come form, eq invoice,estimate,proposal etc..
 * @param  boolean $admin_preview in admin preview add additional sortable classes
 * @return array
 */
    function get_table_gamma_items_and_taxes($items, $type, $admin_preview = false) {
        $result['html'] = '';
        $result['taxes'] = array();
        $_calculated_taxes = array();
        $i = 1;
        foreach ($items as $item) {
            $_item = '';
            $tr_attrs = '';
            $td_first_sortable = '';
            if ($admin_preview == true) {
                $tr_attrs = ' class="sortable" data-item-id="' . $item['id'] . '"';
                $td_first_sortable = ' class="dragger item_no"';
            }

            if (class_exists('pdf')) {
                $font_size = get_option('pdf_font_size');
                if ($font_size == '') {
                    $font_size = 10;
                }

                $tr_attrs .= ' style="font-size:' . (get_option('pdf_font_size') + 2) . 'px;"';
            }
            $bg = ' bgcolor = "#ffffff"';

            $border = 'border-bottom-color:#000000;border-bottom-width:1px; border-bottom-style:solid;';

            $_item .= '<tr' . $tr_attrs . ' ' . $bg . ' >';
            $_item .= '<td' . $td_first_sortable . ' align="center" style="' . $border . '">' . $i . '</td>';
            $_item .= '<td class="description" align="left;" style="' . $border . '"><span ><strong>' . $item['description'] . '</strong></span><br /><span style="color:#424242;">' . $item['long_description'] . '</span></td>';

            $_item .= '<td align="right" style="' . $border . '">' . floatVal($item['qty']);
            if ($item['unit']) {
                $_item .= ' ' . $item['unit'];
            }

            $_item .= '</td>';

            if ($type != 'delivery_note') {

                $_item .= '<td align="right" style="' . $border . '">' . _format_number($item['rate']) . '</td>';
                if (get_option('show_tax_per_item') == 1) {
                    $_item .= '<td align="right">';
                }

                $item_taxes = array();

                // Separate functions exists to get item taxes for Invoice, Estimate, Proposal.
                $func_taxes = 'get_' . $type . '_item_taxes';
                if (function_exists($func_taxes)) {
                    $item_taxes = call_user_func($func_taxes, $item['id']);
                }

                if (count($item_taxes) > 0) {
                    foreach ($item_taxes as $tax) {
                        $calc_tax = 0;
                        $tax_not_calc = false;
                        if (!in_array($tax['taxname'], $_calculated_taxes)) {
                            array_push($_calculated_taxes, $tax['taxname']);
                            $tax_not_calc = true;
                        }
                        if ($tax_not_calc == true) {
                            $result['taxes'][$tax['taxname']] = array();
                            $result['taxes'][$tax['taxname']]['total'] = array();
                            array_push($result['taxes'][$tax['taxname']]['total'], (($item['qty'] * $item['rate']) / 100 * $tax['taxrate']));
                            $result['taxes'][$tax['taxname']]['tax_name'] = $tax['taxname'];
                            $result['taxes'][$tax['taxname']]['taxrate'] = $tax['taxrate'];
                        } else {
                            array_push($result['taxes'][$tax['taxname']]['total'], (($item['qty'] * $item['rate']) / 100 * $tax['taxrate']));
                        }
                        if (get_option('show_tax_per_item') == 1) {
                            $item_tax = '';
                            if ((count($item_taxes) > 1 && get_option('remove_tax_name_from_item_table') == false) || get_option('remove_tax_name_from_item_table') == false || mutiple_taxes_found_for_item($item_taxes)) {
                                $tmp = explode('|', $tax['taxname']);
                                $item_tax = $tmp[0] . ' ' . _format_number($tmp[1]) . '%<br />';
                            } else {
                                $item_tax .= _format_number($tax['taxrate']) . '%';
                            }
                            $hook_data = array('final_tax_html' => $item_tax, 'item_taxes' => $item_taxes, 'item_id' => $item['id']);
                            $hook_data = hooks()->apply_filters('item_tax_table_row', $hook_data);
                            $item_tax = $hook_data['final_tax_html'];
                            $_item .= $item_tax;
                        }
                    }
                } else {
                    if (get_option('show_tax_per_item') == 1) {
                        $hook_data = array('final_tax_html' => '0%', 'item_taxes' => $item_taxes, 'item_id' => $item['id']);
                        $hook_data = hooks()->apply_filters('item_tax_table_row', $hook_data);
                        $_item .= $hook_data['final_tax_html'];
                    }
                }

                if (get_option('show_tax_per_item') == 1) {
                    $_item .= '</td>';
                }

                /**
                 * Since @version 1.7.0
                 * Possible action hook user to include tax in item total amount calculated with the quantiy
                 * eq Rate * QTY + TAXES APPLIED
                 */

                // FIX: Removed nested function definition from here.
                hooks()->add_action('after_pdf_init', 'wonder_pdf_load_libraries');

                $hook_data = hooks()->apply_filters('final_item_amount', array(
                    'amount' => ($item['qty'] * $item['rate']),
                    'item_taxes' => $item_taxes,
                    'item' => $item,
                ));

                $item_amount_with_quantity = _format_number($hook_data['amount']);

                $_item .= '<td class="amount" align="right" style="' . $border . '">' . $item_amount_with_quantity . '</td>';
            }
            $_item .= '</tr>';
            $result['html'] .= $_item;
            $i++;
        }

        return hooks()->apply_filters('before_return_table_gamma_items_html_and_taxes', $result);
    }
}

// FIX: Function moved outside the loop to global scope
if (!function_exists('wonder_pdf_load_libraries')) {
    function wonder_pdf_load_libraries($pdf_instance) {
        $CI = &get_instance();
        // Check if library file exists before loading to prevent fatal error if path is wrong
        if(file_exists(module_dir_path(WPDF_TEMPLATE, 'libraries/Gamma_app_items_table.php'))) {
             $CI->load->library('wonder_pdf_template/gamma_app_items_table', ['pdf' => $pdf_instance]);
        }
    }
}