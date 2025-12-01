<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once APPPATH . 'libraries/App_items_table_template.php';

class Wonder_app_items_table extends App_items_table_template {
	public function __construct($transaction, $type, $for = 'html', $admin_preview = false) {
		// Required
		$this->type = strtolower($type);
		$this->admin_preview = $admin_preview;
		$this->for = $for;

		$this->set_transaction($transaction);
		$this->set_items($transaction->items);

		parent::__construct();
	}

	/**
	 * Builds the actual table items rows preview
	 * @return string
	 */
	public function items() {
		$html = '';

		$descriptionItemWidth = $this->get_description_item_width();

		$regularItemWidth = $this->get_regular_items_width(6);
		$customFieldsItems = $this->get_custom_fields_for_table();

		if ($this->for == 'html') {
			$descriptionItemWidth = $descriptionItemWidth - 5;
			$regularItemWidth = $regularItemWidth - 5;
		}

		$i = 1;
		foreach ($this->items as $item) {
			$border = 'border-bottom-color:#000000;border-bottom-width:1px; border-bottom-style:solid; font-size:10pt; font-family:WonderUnitSans-Regular;';

			$itemHTML = '';

			// Open table row
			$itemHTML .= '<tr' . $this->tr_attributes($item) . '>';

			// Table data number
			$itemHTML .= '<td' . $this->td_attributes() . ' align="center" width="5%" style="' . $border . '">' . $i . '</td>';

			$itemHTML .= '<td class="description" align="left;" width="' . $descriptionItemWidth . '%" style="' . $border . '">';

			/**
			 * Item description
			 */
			if (!empty($item['description'])) {
				$itemHTML .= '<span><strong>'
				. $this->period_merge_field($item['description'])
					. '</strong></span>';

				if (!empty($item['long_description'])) {
					$itemHTML .= '<br />';
				}
			}

			/**
			 * Item long description
			 */
			if (!empty($item['long_description'])) {
				$itemHTML .= '<span style="color:#424242;">' . $this->period_merge_field($item['long_description']) . '</span>';
			}

			$itemHTML .= '</td>';

			/**
			 * Item custom fields
			 */
			foreach ($customFieldsItems as $custom_field) {
				$itemHTML .= '<td align="left" width="' . $regularItemWidth . '%" style="' . $border . '">' . get_custom_field_value($item['id'], $custom_field['id'], 'items') . '</td>';
			}

			/**
			 * Item quantity
			 */
			$itemHTML .= '<td align="right" width="' . $regularItemWidth . '%" style="' . $border . '">' . floatVal($item['qty']);

			/**
			 * Maybe item has added unit?
			 */
			if ($item['unit']) {
				$itemHTML .= ' ' . $item['unit'];
			}

			$itemHTML .= '</td>';

			/**
			 * Item rate
			 * @var string
			 */
			$rate = hooks()->apply_filters(
				'item_preview_rate',
				app_format_money($item['rate'], $this->transaction->currency_name, $this->exclude_currency()),
				['item' => $item, 'transaction' => $this->transaction]
			);

			$itemHTML .= '<td align="right" width="' . $regularItemWidth . '%" style="' . $border . '">' . $rate . '</td>';

			/**
			 * Items table taxes HTML custom function because it's too general for all features/options
			 * @var string
			 */
			$itemHTML .= $this->wonder_taxes_html($item, $regularItemWidth);

			/**
			 * Possible action hook user to include tax in item total amount calculated with the quantiy
			 * eq Rate * QTY + TAXES APPLIED
			 */
			$item_amount_with_quantity = hooks()->apply_filters(
				'item_preview_amount_with_currency',
				app_format_money(($item['qty'] * $item['rate']), $this->transaction->currency_name, $this->exclude_currency()),
				$item,
				$this->transaction,
				$this->exclude_currency()
			);

			$itemHTML .= '<td class="amount" align="right" width="' . $regularItemWidth . '%" style="' . $border . '">' . $item_amount_with_quantity . '</td>';

			// Close table row
			$itemHTML .= '</tr>';

			$html .= $itemHTML;

			$i++;
		}

		return $html;
	}

	/**
	 * Helper method for taxes HTML, because is commonly used for all preview types
	 * @param  array $item
	 * @return string
	 */
	protected function wonder_taxes_html($item, $width) {

		$border = 'border-bottom-color:#000000;border-bottom-width:1px; border-bottom-style:solid; font-size:10pt; font-family:WonderUnitSans-Regular;';
		$itemHTML = '';

		if ($this->show_tax_per_item()) {
			$itemHTML .= '<td align="right" width="' . $width . '%" style="' . $border . '">';
			if (count($item['taxes']) > 0) {
				foreach ($item['taxes'] as $tax) {
					$item_tax = '';
					if ((count($item['taxes']) > 1 && get_option('remove_tax_name_from_item_table') == false) || get_option('remove_tax_name_from_item_table') == false || multiple_taxes_found_for_item($item['taxes'])) {
						$tmp = explode('|', $tax['taxname']);
						$item_tax = $tmp[0] . ' ' . app_format_number($tmp[1]) . '%<br />';
					} else {
						$item_tax .= app_format_number($tax['taxrate']) . '%';
					}

					$itemHTML .= hooks()->apply_filters('item_tax_table_row', $item_tax, $item);
				}
			} else {
				$itemHTML .= hooks()->apply_filters('item_tax_table_row', '0%', $item);
			}
			$itemHTML .= '</td>';
		}

		return $itemHTML;
	}

	/**
	 * Html headings preview
	 * @return string
	 */
	public function html_headings() {
		$html = '<tr>';
		$html .= '<th align="center">' . $this->number_heading() . '</th>';
		$html .= '<th class="description" width="' . $this->get_description_item_width() . '%" align="left">' . $this->item_heading() . '</th>';

		$customFieldsItems = $this->get_custom_fields_for_table();
		foreach ($customFieldsItems as $cf) {
			$html .= '<th class="custom_field" align="left">' . $cf['name'] . '</th>';
		}

		$html .= '<th align="right">' . $this->qty_heading() . '</th>';
		$html .= '<th align="right">' . $this->rate_heading() . '</th>';
		if ($this->show_tax_per_item()) {
			$html .= '<th align="right">' . $this->tax_heading() . '</th>';
		}
		$html .= '<th align="right">' . $this->amount_heading() . '</th>';
		$html .= '</tr>';

		return $html;
	}

	/**
	 * PDF headings preview
	 * @return string
	 */
	public function pdf_headings() {
		$descriptionItemWidth = $this->get_description_item_width();
		$regularItemWidth = $this->get_regular_items_width(6);
		$customFieldsItems = $this->get_custom_fields_for_table();

		$border = 'border-bottom-color:#000000;border-bottom-width:3px; border-bottom-style:solid; border-top: 1px solid black; color:' . get_option('pdf_table_heading_text_color') . ';';

		$tblhtml = '<tr height="30" bgcolor="#fff" style="font-weight:bold; font-size:11pt; font-family:WonderUnitSans-Bold;">';

		$tblhtml .= '<th width="5%;" align="center" style="' . $border . '">' . $this->number_heading() . '</th>';
		$tblhtml .= '<th width="' . $descriptionItemWidth . '%" align="left" style="' . $border . '">Description</th>';

		foreach ($customFieldsItems as $cf) {
			$tblhtml .= '<th width="' . $regularItemWidth . '%" align="left" style="' . $border . '">' . $cf['name'] . '</th>';
		}

		$tblhtml .= '<th width="' . $regularItemWidth . '%" align="right" style="' . $border . '">' . $this->qty_heading() . '</th>';
		$tblhtml .= '<th width="' . $regularItemWidth . '%" align="right" style="' . $border . '">' . $this->rate_heading() . '</th>';

		if ($this->show_tax_per_item()) {
			$tblhtml .= '<th width="' . $regularItemWidth . '%" align="right" style="' . $border . '">' . $this->tax_heading() . '</th>';
		}

		$tblhtml .= '<th width="' . $regularItemWidth . '%" align="right" style="' . $border . '">' . $this->amount_heading() . '</th>';
		$tblhtml .= '</tr>';

		return $tblhtml;
	}

	/**
	 * Check for period merge field for recurring invoices
	 *
	 * @return string
	 */
	protected function period_merge_field($text) {
		if ($this->type != 'invoice') {
			return $text;
		}

		// Is subscription invoice
		if (!property_exists($this->transaction, 'recurring_type')) {
			return $text;
		}

		$compareRecurring = $this->transaction->recurring_type;
		$compareDate = !$this->transaction->last_recurring_date ? $this->transaction->date : $this->transaction->last_recurring_date;
		$transactionDate = $this->transaction->date;

		// Is not Y-m-d format
		if (!preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $compareDate)) {
			$compareDate = to_sql_date($compareDate);
		}

		if (!preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $transactionDate)) {
			$transactionDate = to_sql_date($transactionDate);
		}

		if ($this->transaction->custom_recurring == 0) {
			$compareRecurring = 'month';
		}

		$next_date = date('Y-m-d', strtotime(
			'+' . $this->transaction->recurring . ' ' . strtoupper($compareRecurring),
			strtotime($compareDate)
		));

		return str_ireplace('{period}', _d($transactionDate) . ' - ' . _d(date('Y-m-d', strtotime('-1 day', strtotime($next_date)))), $text);
	}

	protected function get_description_item_width() {
		$item_width = hooks()->apply_filters('item_description_td_width', 38);

		// If show item taxes is disabled in PDF we should increase the item width table heading
		return $this->show_tax_per_item() == 0 ? $item_width + 15 : $item_width;
	}

	protected function get_regular_items_width($adjustment) {
		$descriptionItemWidth = $this->get_description_item_width();
		$customFieldsItems = $this->get_custom_fields_for_table();
		// Calculate headings width, in case there are custom fields for items
		$totalheadings = $this->show_tax_per_item() == 1 ? 4 : 3;
		$totalheadings += count($customFieldsItems);

		return (100 - ($descriptionItemWidth + $adjustment)) / $totalheadings;
	}
}
