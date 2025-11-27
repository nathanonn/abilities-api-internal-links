<?php
/**
 * Get link report ability.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Abilities;

use InternalLinksAPI\Errors\ErrorFactory;
use InternalLinksAPI\Services\LinkValidatorService;
use InternalLinksAPI\Services\PostService;
use InternalLinksAPI\Settings;
use WP_Error;

/**
 * Ability to generate a comprehensive link report for a post.
 */
class GetLinkReportAbility {

	/**
	 * Post service.
	 *
	 * @var PostService
	 */
	private PostService $post_service;

	/**
	 * Link validator service.
	 *
	 * @var LinkValidatorService
	 */
	private LinkValidatorService $link_validator;

	/**
	 * Settings.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @param PostService          $post_service   Post service.
	 * @param LinkValidatorService $link_validator Link validator service.
	 * @param Settings             $settings       Settings.
	 */
	public function __construct(
		PostService $post_service,
		LinkValidatorService $link_validator,
		Settings $settings
	) {
		$this->post_service = $post_service;
		$this->link_validator = $link_validator;
		$this->settings = $settings;
	}

	/**
	 * Execute the get link report ability.
	 *
	 * @param array $input Input parameters.
	 * @return array|WP_Error Link report or error.
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

		return $this->link_validator->generate_report( $post );
	}
}
