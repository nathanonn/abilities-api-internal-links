<?php
/**
 * Batch remove links ability.
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
 * Ability to remove multiple links from a post in one operation.
 */
class BatchRemoveLinksAbility {

	private const MAX_BATCH_SIZE = 50;

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
	 * Execute the batch remove links ability.
	 *
	 * @param array $input Input parameters.
	 * @return array|WP_Error Result or error.
	 */
	public function execute( array $input ) {
		$source_post_id = $input['source_post_id'];
		$links = $input['links'];
		$stop_on_error = $input['stop_on_error'] ?? false;

		// Check batch size.
		if ( count( $links ) > self::MAX_BATCH_SIZE ) {
			return ErrorFactory::batch_limit_exceeded( count( $links ), self::MAX_BATCH_SIZE );
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

		// Process removals.
		$content = $source_post->post_content;
		$editor_type = $this->post_service->get_editor_type( $source_post );
		$results = array();
		$total_removed = 0;
		$total_failed = 0;

		foreach ( $links as $index => $link_data ) {
			$identifier = $link_data['identifier'];
			$action = $link_data['action'] ?? 'unlink';

			// Validate identifier.
			if ( ! $this->validate_identifier( $identifier ) ) {
				if ( $stop_on_error ) {
					return ErrorFactory::invalid_identifier( $identifier );
				}

				$results[] = array(
					'index'  => $index,
					'status' => 'failed',
					'action' => $action,
					'reason' => __( 'Invalid identifier', 'internal-links-api' ),
				);
				$total_failed++;
				continue;
			}

			// Find matching links.
			$matching_links = $this->link_parser->find_links_by_identifier( $content, $identifier );

			if ( empty( $matching_links ) ) {
				if ( $stop_on_error ) {
					return ErrorFactory::link_not_found( $identifier );
				}

				$results[] = array(
					'index'  => $index,
					'status' => 'failed',
					'action' => $action,
					'reason' => __( 'Link not found', 'internal-links-api' ),
				);
				$total_failed++;
				continue;
			}

			// Remove the link.
			$result = $this->link_modifier->remove_link(
				$content,
				$identifier,
				$action,
				$editor_type
			);

			$content = $result['content'];

			if ( $result['links_removed'] > 0 ) {
				$anchor_text = ! empty( $result['removed_links'] )
					? $result['removed_links'][0]['anchor_text']
					: '';

				$results[] = array(
					'index'       => $index,
					'status'      => 'removed',
					'action'      => $action,
					'anchor_text' => $anchor_text,
				);
				$total_removed++;
			} else {
				$results[] = array(
					'index'  => $index,
					'status' => 'failed',
					'action' => $action,
					'reason' => __( 'Link removal failed', 'internal-links-api' ),
				);
				$total_failed++;
			}
		}

		// Update post content (single revision for entire batch).
		if ( $total_removed > 0 ) {
			wp_update_post(
				array(
					'ID'           => $source_post_id,
					'post_content' => $content,
				)
			);
		}

		return array(
			'success'         => $total_removed > 0,
			'source_post_id'  => $source_post_id,
			'total_requested' => count( $links ),
			'total_removed'   => $total_removed,
			'total_failed'    => $total_failed,
			'results'         => $results,
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
