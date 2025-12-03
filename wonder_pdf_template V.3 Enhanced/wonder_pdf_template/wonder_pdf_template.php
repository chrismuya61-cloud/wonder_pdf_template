<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Wonder PDF Template
Description: Advanced PDF Engine for Bizit Services (Gamma Theme)
Version: 1.3.1
Author: Swivernet
*/

define('WPDF_TEMPLATE', 'wonder_pdf_template');

/*
 * Register Module Hooks
 */
hooks()->add_action('admin_init', 'wpdf_init_menu');
hooks()->add_action('admin_init', 'wpdf_settings');

// Load Module Helper
$CI = &get_instance();
$CI->load->helper(WPDF_TEMPLATE . '/wonder_pdf_template');

/*
 * -------------------------------------------------------------------------
 * PDF View Overrides (Aligning Perfex PDFs to Module)
 * -------------------------------------------------------------------------
 */
$wpdf_core_types = [
    'invoice', 
    'estimate', 
    'proposal', 
    'credit_note', 
    'contract', 
    'statement',
    'delivery_note', // specific to delivery note addons
    'purchase_order' // specific to PO addons
];

foreach ($wpdf_core_types as $type) {
    // Dynamically register a hook for each PDF type
    hooks()->add_filter("custom_{$type}PDF_file", function($original_path) use ($type) {
        
        // 1. Get the active template from settings (Default to 'gamma')
        $active_template = get_option('wonder_pdf_active_template');
        if (empty($active_template)) {
            $active_template = 'gamma';
        }

        // 2. Construct the path to the custom file in this module
        // Pattern: modules/wonder_pdf_template/views/pdf/{template}/my_{type}pdf.php
        $custom_path = module_dir_path(WPDF_TEMPLATE, "views/pdf/{$active_template}/my_{$type}pdf.php");

        // 3. Check if our custom file exists
        if (file_exists($custom_path)) {
            return $custom_path;
        }

        // 4. If not found, return the original Perfex path
        return $original_path;
    });
}

/*
 * -------------------------------------------------------------------------
 * Module Functions
 * -------------------------------------------------------------------------
 */

/**
 * Register Default Settings
 */
function wpdf_settings(){
    if(function_exists('add_option')){
        add_option('wonder_pdf_sec_logo', '');
        add_option('wonder_pdf_active_template', 'gamma');
        // Legacy options support
        add_option('custom_pdf_logo_image_url', '');
        add_option('pdf_logo_width', '250');
    }
}

/**
 * Init Menu Items
 */
function wpdf_init_menu(){
    $CI = &get_instance();
    if(is_admin()){
        $CI->app_menu->add_setup_menu_item('wpdf', [
            'slug'     => 'wpdf-settings',
            'name'     => 'Wonder PDF Settings',
            'href'     => admin_url('wonder_pdf_template/settings'),
            'position' => 65
        ]);
    }
}