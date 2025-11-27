<?php
/**
 * Update link ability.
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
 * Ability to update an existing link in a post.
 */
class UpdateLinkAbility {

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
	 * Execute the update link ability.
	 *
	 * @param array $input Input parameters.
	 * @return array|WP_Error Result or error.
	 */
	public function execute( array $input ) {
		$source_post_id = $input['source_post_id'];
		$identifier = $input['identifier'];
		$new_target_post_id = $input['new_target_post_id'] ?? null;
		$new_anchor_text = $input['new_anchor_text'] ?? null;
		$new_attributes = $input['attributes'] ?? null;
		$merge_attributes = $input['merge_attributes'] ?? false;

		// Validate at least one change is requested.
		if ( null === $new_target_post_id && null === $new_anchor_text && null === $new_attributes ) {
			return ErrorFactory::no_changes_requested();
		}

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

		// Validate identifier.
		if ( ! $this->validate_identifier( $identifier ) ) {
			return ErrorFactory::invalid_identifier( $identifier );
		}

		// Find matching links.
		$matching_links = $this->link_parser->find_links_by_identifier(
			$source_post->post_content,
			$identifier
		);

		if ( empty( $matching_links ) ) {
			return ErrorFactory::link_not_found( $identifier );
		}

		// Validate new target post if provided.
		if ( null !== $new_target_post_id ) {
			$target_post = $this->post_service->get_post( $new_target_post_id );
			if ( ! $target_post ) {
				return ErrorFactory::post_not_found( $new_target_post_id );
			}

			if ( ! $this->settings->is_supported_post_type( $target_post->post_type ) ) {
				return ErrorFactory::invalid_post_type( $target_post->post_type, $new_target_post_id );
			}

			if ( 'publish' !== $target_post->post_status ) {
				return ErrorFactory::target_not_published( $new_target_post_id, $target_post->post_status );
			}
		}

		// Get editor type.
		$editor_type = $this->post_service->get_editor_type( $source_post );

		// Perform link update.
		$result = $this->link_modifier->update_link(
			$source_post->post_content,
			$identifier,
			$new_target_post_id,
			$new_anchor_text,
			$new_attributes,
			$merge_attributes,
			$editor_type
		);

		// Update post content.
		if ( $result['links_updated'] > 0 ) {
			wp_update_post(
				array(
					'ID'           => $source_post_id,
					'post_content' => $result['content'],
				)
			);
		}

		return array(
			'success'        => $result['links_updated'] > 0,
			'links_updated'  => $result['links_updated'],
			'source_post_id' => $source_post_id,
			'changes'        => $result['changes'],
		);
	}

	/**
	 * Validate link identifier.
	 *
	 * @param array $identifier Identifier to validate.
	 * @return bool True if valid.
	 */
	private function validate_identifier( array $identifier ): bool {
		if ( ! isset( $identifier['by'] ) ) {
			return false;
		}

		switch ( $identifier['by'] ) {
			case 'url':
				return ! empty( $identifier['url'] );
			case 'anchor':
				return ! empty( $identifier['anchor_text'] );
			case 'index':
				return isset( $identifier['index'] ) && is_numeric( $identifier['index'] );
			default:
				return false;
		}
	}
}
