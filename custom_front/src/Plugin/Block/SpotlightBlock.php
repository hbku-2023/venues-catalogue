

		$node = \Drupal::routeMatch()->getParameter('node');

		if ($node instanceof Node && !$node->field_media->isEmpty() ) {
			return [
				$node->field_media->view('default'),
			];
		}


