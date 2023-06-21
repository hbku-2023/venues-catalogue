<?php
/**
 * @file
 * Definition of Drupal\d8views\Plugin\views\filter\NodeTitles.
 */

namespace Drupal\custom_front\Plugin\views\filter;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\InOperator;
use Drupal\views\ViewExecutable;

/**
 * Filters by given list of node title options.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("node_titles")
 */
class NodeTitles extends InOperator {

	/**
	 * {@inheritdoc}
	 */
	public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
		parent::init($view, $display, $options);
		$this->valueTitle = t('Allowed node titles');
		$this->definition['options callback'] = array($this, 'generateOptions');
	}

	/**
	 * Override the query so that no filtering takes place if the user doesn't
	 * select any options.
	 */
	public function query() {


		$table = 'node__' . $this->realField;
		$column =  $this->realField.'_target_id';
		$this->query->addTable($table);
		$this->query->addWhere($this->options['group'], $table.'.'.$column, $this->value, 'IN');
		$this->query->distinct = True;

	}

	/**
	 * Skip validation if no options have been chosen so we can use it as a
	 * non-filter.
	 */
	public function validate() {
		if (!empty($this->value)) {
			parent::validate();
		}
	}

	/**
	 * Helper function that generates the options.
	 * @return array
	 */
	public function generateOptions() {
		// Array keys are used to compare with the table field values.
		$array = [];
		$nodes = \Drupal::entityTypeManager()->getStorage('node')
			->loadByProperties(
				['type' => 'building']
			);

		foreach ($nodes as $value) {
			if ($value->isPublished()) {
				$array[$value->id()] = $value->label();
			}
		}

		return $array;
	}

}
