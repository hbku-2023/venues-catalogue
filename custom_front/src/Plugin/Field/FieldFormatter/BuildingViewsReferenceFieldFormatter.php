

namespace Drupal\custom_front\Plugin\Field\FieldFormatter;

use Drupal\views\Views;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Field formatter for Viewsreference Field.
 *
 * @FieldFormatter(
 *   id = "builing_viewsreference_formatter",
 *   label = @Translation("Building Views Reference"),
 *   field_types = {"viewsreference"}
 * )
 */
class BuildingViewsReferenceFieldFormatter extends FormatterBase {

	/**
	 * {@inheritdoc}
	 */
	public static function defaultSettings() {
		$options = parent::defaultSettings();

		$options['plugin_types'] = ['block'];
		return $options;
	}

	/**
	 * {@inheritdoc}
	 */
	public function settingsForm(array $form, FormStateInterface $form_state) {
		$form = parent::settingsForm($form, $form_state);

		$types = Views::pluginList();
		$options = [];
		foreach ($types as $key => $type) {
			if ($type['type'] == 'display') {
				$options[str_replace('display:', '', $key)] = $type['title']->render();
			}
		}

		$form['plugin_types'] = [
			'#type' => 'checkboxes',
			'#options' => $options,
			'#title' => $this->t('View display plugins to allow'),
			'#default_value' => $this->getSetting('plugin_types'),
		];

		return $form;
	}

	/**
	 * {@inheritdoc}
	 */
	public function settingsSummary() {
		$summary = [];
		$settings = $this->getSettings();

		$allowed = [];
		foreach ($settings['plugin_types'] as $type) {
			if ($type) {
				$allowed[] = $type;
			}
		}
		$summary[] = $this->t('Allowed plugins: @view', ['@view' => implode(', ', $allowed)]);
		return $summary;
	}

	/**
	 * {@inheritdoc}
	 */
	public function viewElements(FieldItemListInterface $items, $langcode) {

		$elements = [];

		foreach ($items as $delta => $item) {
			$view_name = $item->getValue()['target_id'];
			$display_id = $item->getValue()['display_id'];
			$argument = $item->getValue()['argument'];

			$title = $item->getValue()['title'];
			$view = Views::getView($view_name);
			// Someone may have deleted the View.
			if (!is_object($view)) {
				continue;
			}
			// No access.
			if (!$view->access($display_id)) {
				continue;
			}

			$view->setDisplay($display_id);;

			if ($argument) {
				$view->element['#cache']['keys'][] = $argument;
				$node = \Drupal::routeMatch()->getParameter('node');
				$arguments = ($node && $node->hasField($argument) && !$node->$argument->isEmpty()) ?
					[(str_replace(' ', '', $node->$argument->entity->field_geolocation->getString())).'<=1500km'] : [];
				$view->setArguments($arguments);
			}

			$view->preExecute();
			$view->execute($display_id);


			unset($view->result[0]);


			if ($title) {
				$title = $view->getTitle();
				$title_render_array = [
					'#theme' => $view->buildThemeFunctions('viewsreference__view_title'),
					'#title' => $title,
					'#view' => $view,
				];
			}

			if ($this->getSetting('plugin_types')) {
				if ($title) {
					$elements[$delta]['title'] = $title_render_array;
				}
			}

			$elements[$delta]['contents'] = $view->buildRenderable($display_id);
		}

		return $elements;
	}

}