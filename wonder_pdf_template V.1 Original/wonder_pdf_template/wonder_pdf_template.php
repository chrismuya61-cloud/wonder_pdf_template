<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Wonder PDF Template
Description: Module for Wonder PDF Template.
Version: 1.0.3
Requires at least: 2.3.*
Author: Swivernet
Author URI: https://tazamali.com
 */

define('WPDF_TEMPLATE', 'wonder_pdf_template');

define('TEMPLATE', 'gamma');

define('SLOGAN', 'Best water and energy solutions');

hooks()->add_action('admin_init', 'wpdf_template_permissions');
hooks()->add_action('admin_init', 'wpdf_template_module_init_menu_items');
hooks()->add_action('before_js_scripts_render', 'wpdf_template_variable_script', 9);
hooks()->add_action('before_js_scripts_render', 'wpdf_template_script', 10);
hooks()->add_action('pdf_header', 'pdf_header_override', 10);
hooks()->add_action('pdf_footer', 'pdf_footer_override', 10);
//hooks()->add_filter('custom_invoicePDF_file', 'add_custom_invoice_pdf');
//hooks()->add_filter('module_wonder_pdf_template_action_links', 'module_wonder_pdf_template_action_links');

$pdf_arrx = ['invoice', 'estimate', 'proposal', 'credit_note', 'expense', 'statement', 'contract', 'project_data'];
foreach ($pdf_arrx as $pdf_title) {
	$pdf_fn_name = 'add_custom_' . $pdf_title . '_pdf';

	hooks()->add_filter('custom_' . $pdf_title . 'PDF_file', $pdf_fn_name);
}

/**
 * Add additional settings for this module in the module list area
 * @param  array $actions current actions
 * @return array
 */
function module_wonder_pdf_template_action_links($actions)
{
	$actions[] = '<a href="#">' . _l('settings') . '</a>';

	return $actions;
}

/**
 * Register activation module hook
 */
register_activation_hook(WPDF_TEMPLATE, 'wpdf_template_module_activation_hook');

function wpdf_template_module_activation_hook()
{
	$CI = &get_instance();
	require_once __DIR__ . '/install.php';
}

/**
 * Register deactivation module hook
 */
register_uninstall_hook(WPDF_TEMPLATE, 'wpdf_template_module_uninstall_hook');

function wpdf_template_module_uninstall_hook()
{
	$CI = &get_instance();
	require_once __DIR__ . '/uninstall.php';
}

/**
 * Register deactivation module hook
 */
register_deactivation_hook(WPDF_TEMPLATE, 'wpdf_template_module_deactivation_hook');

function wpdf_template_module_deactivation_hook()
{
	$CI = &get_instance();
	require_once __DIR__ . '/deactivate.php';
}

/**
 * Load the module helper
 */
$CI = &get_instance();
$CI->load->helper(WPDF_TEMPLATE . '/' . WPDF_TEMPLATE);

/*
 * Load the module library
 */
//$CI->load->library(WPDF_TEMPLATE . '/Wonder_app_items_table');

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(WPDF_TEMPLATE, [WPDF_TEMPLATE]);

//methods
//
function wpdf_template_script()
{
	$CI = &get_instance();
	return $CI->app_scripts->add(WPDF_TEMPLATE . '-js', module_dir_url(WPDF_TEMPLATE, 'assets/js/' . WPDF_TEMPLATE . '.js') . '?v=' . $CI->app_scripts->core_version(), 'admin', ['app-js']);
}

function wpdf_template_variable_script()
{
	echo "<script> </script>";
}
/*
 * Init quickbooks module menu items in setup in admin_init hook
 */
function wpdf_template_module_init_menu_items()
{
	$CI = &get_instance();

	if (has_permission(WPDF_TEMPLATE, '', 'view')) {

		$CI->app_menu->add_setup_children_item('leads', [
			'slug' => WPDF_TEMPLATE,
			'name' => _l(WPDF_TEMPLATE),
			'href' => admin_url(WPDF_TEMPLATE . '/leads/forms'),
			'position' => 21,
		]);
	}
}

/*
 * Quickbooks Permissions
 */
function wpdf_template_permissions()
{
	$capabilities = [];

	$capabilities['capabilities'] = [
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
		'create' => _l('permission_create'),
		'edit' => _l('permission_edit'),
		'delete' => _l('permission_delete'),
	];

	register_staff_capabilities(WPDF_TEMPLATE, $capabilities, _l(WPDF_TEMPLATE));
}

function add_custom_invoice_pdf($actualPath)
{
	if (file_exists(module_dir_path(WPDF_TEMPLATE, 'views/pdf/' . TEMPLATE . '/my_invoicepdf.php'))) {
		return module_dir_path(WPDF_TEMPLATE, 'views/pdf/' . TEMPLATE . '/my_invoicepdf.php');
	}
	return $actualPath;
}

function add_custom_estimate_pdf($actualPath)
{
	if (file_exists(module_dir_path(WPDF_TEMPLATE, 'views/pdf/' . TEMPLATE . '/my_estimatepdf.php'))) {
		return module_dir_path(WPDF_TEMPLATE, 'views/pdf/' . TEMPLATE . '/my_estimatepdf.php');
	}
	return $actualPath;
}

function add_custom_proposal_pdf($actualPath)
{
	if (file_exists(module_dir_path(WPDF_TEMPLATE, 'views/pdf/' . TEMPLATE . '/my_proposalpdf.php'))) {
		return module_dir_path(WPDF_TEMPLATE, 'views/pdf/' . TEMPLATE . '/my_proposalpdf.php');
	}
	return $actualPath;
}

function add_custom_credit_note_pdf($actualPath)
{
	if (file_exists(module_dir_path(WPDF_TEMPLATE, 'views/pdf/' . TEMPLATE . '/my_credit_notepdf.php'))) {
		return module_dir_path(WPDF_TEMPLATE, 'views/pdf/' . TEMPLATE . '/my_credit_notepdf.php');
	}
	return $actualPath;
}

function add_custom_expense_pdf($actualPath)
{
	if (file_exists(module_dir_path(WPDF_TEMPLATE, 'views/pdf/' . TEMPLATE . '/my_expensepdf.php'))) {
		return module_dir_path(WPDF_TEMPLATE, 'views/pdf/' . TEMPLATE . '/my_expensepdf.php');
	}
	return $actualPath;
}

function add_custom_statement_pdf($actualPath)
{
	if (file_exists(module_dir_path(WPDF_TEMPLATE, 'views/pdf/' . TEMPLATE . '/my_statementpdf.php'))) {
		return module_dir_path(WPDF_TEMPLATE, 'views/pdf/' . TEMPLATE . '/my_statementpdf.php');
	}
	return $actualPath;
}
function add_custom_contract_pdf($actualPath)
{
	if (file_exists(module_dir_path(WPDF_TEMPLATE, 'views/pdf/' . TEMPLATE . '/my_contractpdf.php'))) {
		return module_dir_path(WPDF_TEMPLATE, 'views/pdf/' . TEMPLATE . '/my_contractpdf.php');
	}
	return $actualPath;
}
function add_custom_project_data_pdf($actualPath)
{
	if (file_exists(module_dir_path(WPDF_TEMPLATE, 'views/pdf/' . TEMPLATE . '/my_project_datapdf.php'))) {
		return module_dir_path(WPDF_TEMPLATE, 'views/pdf/' . TEMPLATE . '/my_project_datapdf.php');
	}
	return $actualPath;
}


function pdf_header_override($data)
{
	if (TEMPLATE == 'geoid') {
		$pdf_instance = $data['pdf_instance'];

		if ($pdf_instance->getPage() == 1) {
			$header = '<img src="' . module_dir_path(WPDF_TEMPLATE, '/assets/images/geoid/header.png') . '" />';
			$pdf_instance->writeHTMLCell(210, '', 0, 0, $header, 0, 1, false, true, 'L', false);
		}
	}
}

function pdf_footer_override($data)
{
	if (TEMPLATE == 'geoid') {
		$pdf_instance = $data['pdf_instance'];

		$pdf_instance->SetFont($pdf_instance->get_font_name(), '', 0);

		$footerX = 0; 
		$footerFileName = module_dir_path(WPDF_TEMPLATE, '/assets/images/geoid/footer.png');
		$footerWidth = 210;
		$footerY = 285;
		$footer = $pdf_instance->Image($footerFileName, $footerX, $footerY, $footerWidth);
		$pdf_instance->Cell(10,10, $footer, 0, 0, 'C');		
	}
}