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