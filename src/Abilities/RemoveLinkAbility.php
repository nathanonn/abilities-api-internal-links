<?php
/**
 * Remove link ability.
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
 * Ability to remove a link from a post.
 */
class RemoveLinkAbility {

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
	 * Execute the remove link ability.
	 *
	 * @param array $input Input parameters.
	 * @return array|WP_Error Result or error.
	 */
	public function execute( array $input ) {
		$source_post_id = $input['source_post_id'];
		$identifier = $input['identifier'];
		$action = $input['action'] ?? 'unlink';

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

		// Fire before action.
		do_action( 'internal_links_api_before_remove_link', $source_post_id, $identifier );

		// Get editor type.
		$editor_type = $this->post_service->get_editor_type( $source_post );

		// Perform link removal.
		$result = $this->link_modifier->remove_link(
			$source_post->post_content,
			$identifier,
			$action,
			$editor_type
		);

		// Update post content.
		if ( $result['links_removed'] > 0 ) {
			wp_update_post(
				array(
					'ID'           => $source_post_id,
					'post_content' => $result['content'],
				)
			);
		}

		// Fire after action.
		do_action( 'internal_links_api_after_remove_link', $source_post_id, $result );

		return array(
			'success'        => $result['links_removed'] > 0,
			'links_removed'  => $result['links_removed'],
			'source_post_id' => $source_post_id,
			'action'         => $action,
			'removed_links'  => $result['removed_links'],
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
