<?php
/**
 * Add link ability.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Abilities;

use InternalLinksAPI\Errors\ErrorFactory;
use InternalLinksAPI\Services\LinkModifierService;
use InternalLinksAPI\Services\LinkParserService;
use InternalLinksAPI\Services\PostLockService;
use InternalLinksAPI\Services\PostService;
use InternalLinksAPI\Settings;
use WP_Error;

/**
 * Ability to add an internal link to a post.
 */
class AddLinkAbility {

	/**
	 * Post service.
	 *
	 * @var PostService
	 */
	private PostService $post_service;

	/**
	 * Link parser service.
	 *
	 * @var LinkParserService
	 */
	private LinkParserService $link_parser;

	/**
	 * Link modifier service.
	 *
	 * @var LinkModifierService
	 */
	private LinkModifierService $link_modifier;

	/**
	 * Post lock service.
	 *
	 * @var PostLockService
	 */
	private PostLockService $post_lock;

	/**
	 * Settings.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @param PostService         $post_service  Post service.
	 * @param LinkParserService   $link_parser   Link parser service.
	 * @param LinkModifierService $link_modifier Link modifier service.
	 * @param PostLockService     $post_lock     Post lock service.
	 * @param Settings            $settings      Settings.
	 */
	public function __construct(
		PostService $post_service,
		LinkParserService $link_parser,
		LinkModifierService $link_modifier,
		PostLockService $post_lock,
		Settings $settings
	) {
		$this->post_service = $post_service;
		$this->link_parser = $link_parser;
		$this->link_modifier = $link_modifier;
		$this->post_lock = $post_lock;
		$this->settings = $settings;
	}

	/**
	 * Execute the add link ability.
	 *
	 * @param array $input Input parameters.
	 * @return array|WP_Error Result or error.
	 */
	public function execute( array $input ) {
		$source_post_id = $input['source_post_id'];
		$target_post_id = $input['target_post_id'];
		$anchor_text = $input['anchor_text'];
		$occurrence = $input['occurrence'] ?? 'first';
		$attributes = $input['attributes'] ?? array();
		$if_exists = $input['if_exists'] ?? 'skip';

		// Validate source post.
		$source_post = $this->post_service->get_post( $source_post_id );
		if ( ! $source_post ) {
			return ErrorFactory::post_not_found( $source_post_id );
		}

		// Check source post type is supported.
		if ( ! $this->settings->is_supported_post_type( $source_post->post_type ) ) {
			return ErrorFactory::invalid_post_type( $source_post->post_type, $source_post_id );
		}

		// Check post lock.
		if ( $this->post_lock->is_locked( $source_post_id ) ) {
			return ErrorFactory::post_locked(
				$source_post_id,
				$this->post_lock->get_lock_user_name( $source_post_id )
			);
		}

		// Validate target post.
		$target_post = $this->post_service->get_post( $target_post_id );
		if ( ! $target_post ) {
			return ErrorFactory::post_not_found( $target_post_id );
		}

		// Check target post type is supported.
		if ( ! $this->settings->is_supported_post_type( $target_post->post_type ) ) {
			return ErrorFactory::invalid_post_type( $target_post->post_type, $target_post_id );
		}

		// Check target is published.
		if ( 'publish' !== $target_post->post_status ) {
			return ErrorFactory::target_not_published( $target_post_id, $target_post->post_status );
		}

		// Prevent self-linking.
		if ( $source_post_id === $target_post_id ) {
			return ErrorFactory::self_link_not_allowed( $source_post_id );
		}

		// Find anchor text occurrences.
		$occurrences = $this->link_parser->find_anchor_occurrences(
			$source_post->post_content,
			$anchor_text
		);

		if ( empty( $occurrences ) ) {
			return ErrorFactory::anchor_not_found( $anchor_text, $source_post_id );
		}

		// Validate occurrence parameter.
		if ( is_numeric( $occurrence ) && (int) $occurrence > count( $occurrences ) ) {
			return ErrorFactory::occurrence_out_of_range( (int) $occurrence, count( $occurrences ) );
		}

		// Check for spanning elements.
		if ( $this->link_parser->anchor_spans_elements( $source_post->post_content, $anchor_text, $occurrence ) ) {
			return ErrorFactory::anchor_spans_elements( $anchor_text );
		}

		// Fire before action.
		do_action( 'internal_links_api_before_add_link', $source_post_id, $target_post_id, $anchor_text );

		// Get target permalink.
		$target_permalink = get_permalink( $target_post );

		// Get editor type.
		$editor_type = $this->post_service->get_editor_type( $source_post );

		// Perform link addition.
		$result = $this->link_modifier->add_link(
			$source_post->post_content,
			$anchor_text,
			$target_permalink,
			$attributes,
			$occurrence,
			$if_exists,
			$editor_type
		);

		// Update post content.
		if ( $result['links_added'] > 0 ) {
			wp_update_post(
				array(
					'ID'           => $source_post_id,
					'post_content' => $result['content'],
				)
			);
		}

		// Fire after action.
		do_action( 'internal_links_api_after_add_link', $source_post_id, $result );

		return array(
			'success'            => $result['links_added'] > 0,
			'links_added'        => $result['links_added'],
			'source_post_id'     => $source_post_id,
			'target_post_id'     => $target_post_id,
			'target_permalink'   => $target_permalink,
			'anchor_text'        => $anchor_text,
			'occurrences_found'  => count( $occurrences ),
			'occurrences_linked' => $result['links_added'],
			'skipped'            => $result['skipped'],
		);
	}
}
