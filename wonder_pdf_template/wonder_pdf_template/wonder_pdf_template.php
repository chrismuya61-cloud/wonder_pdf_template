<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Wonder PDF Template
Description: Advanced PDF Engine for Bizit Services (Gamma Theme)
Version: 1.3.0
Author: Swivernet
*/

define('WPDF_TEMPLATE', 'wonder_pdf_template');

hooks()->add_action('admin_init', 'wpdf_init_menu');
hooks()->add_action('admin_init', 'wpdf_settings');

function wpdf_settings(){
    if(function_exists('add_option')){
        add_option('wonder_pdf_sec_logo', '');
        add_option('wonder_pdf_active_template', 'gamma');
    }
}

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

$CI = &get_instance();
$CI->load->helper(WPDF_TEMPLATE . '/wonder_pdf_template');
