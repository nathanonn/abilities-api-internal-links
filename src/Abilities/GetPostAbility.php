<?php
/**
 * Get post ability.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Abilities;

use InternalLinksAPI\Errors\ErrorFactory;
use InternalLinksAPI\Services\PostService;
use InternalLinksAPI\Settings;
use WP_Error;

/**
 * Ability to retrieve full post details.
 */
class GetPostAbility {

	/**
	 * Post service.
	 *
	 * @var PostService
	 */
	private PostService $post_service;

	/**
	 * Settings.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @param PostService $post_service Post service.
	 * @param Settings    $settings     Settings.
	 */
	public function __construct( PostService $post_service, Settings $settings ) {
		$this->post_service = $post_service;
		$this->settings = $settings;
	}

	/**
	 * Execute the get post ability.
	 *
	 * @param array $input Input parameters.
	 * @return array|WP_Error Post data or error.
	 */
	public function execute( array $input ) {
		$post_id = $input['post_id'];
		$post = $this->post_service->get_post( $post_id );

		if ( ! $post ) {
			return ErrorFactory::post_not_found( $post_id );
		}

		// Check post type is supported.
		if ( ! $this->settings->is_supported_post_type( $post->post_type ) ) {
			return ErrorFactory::invalid_post_type( $post->post_type, $post_id );
		}

		return $this->post_service->format_post( $post );
	}
}
