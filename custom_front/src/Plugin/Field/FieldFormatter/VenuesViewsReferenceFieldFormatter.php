
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
//			if (!$view->access($display_id)) {
//				continue;
//			}
//kint($argument);die;
			$view->setDisplay($display_id);;
			$node = \Drupal::routeMatch()->getParameter('node');
			if ($argument) {
				$view->element['#cache']['keys'][] = $argument;
				$arguments = ($node && $node->hasField($argument) && !$node->$argument->isEmpty()) ? [$node->$argument->entity->id()] : [];
				$view->setArguments($arguments);
			}

			$view->preExecute();
			$view->execute($display_id);


			foreach ($view->result as $key => $result) {
				if ($result->_entity->id() == $node->id()) {
					unset($view->result[$key]);
				}
			}

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
