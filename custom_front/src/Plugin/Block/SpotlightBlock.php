

namespace Drupal\custom_front\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;


/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "spotlight_bock",
 *   admin_label = @Translation("Node spotlight"),
 *   category = "Custom"
 * )
 */
class SpotlightBlock extends BlockBase {

	/**
	 * {@inheritdoc}
	 */
	public function build() {

		$node = \Drupal::routeMatch()->getParameter('node');

		if ($node instanceof Node && !$node->field_media->isEmpty() ) {
			return [
				$node->field_media->view('default'),
			];
		}

	}

	/**
	 * @return int
	 */
	public function getCacheMaxAge() {
		return 0;
	}
}
