<?php
/**
 * Batch add links ability.
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
 * Ability to add multiple links to a post in one operation.
 */
class BatchAddLinksAbility {

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
	 * Execute the batch add links ability.
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

		// Pre-validate all target posts.
		$validation_errors = $this->validate_targets( $links );
		if ( $stop_on_error && ! empty( $validation_errors ) ) {
			return $validation_errors[0];
		}

		// Process links.
		$content = $source_post->post_content;
		$editor_type = $this->post_service->get_editor_type( $source_post );
		$results = array();
		$total_added = 0;
		$total_skipped = 0;
		$total_failed = 0;

		foreach ( $links as $index => $link_data ) {
			// Check if this link had validation errors.
			if ( isset( $validation_errors[ $index ] ) ) {
				$results[] = array(
					'index'          => $index,
					'status'         => 'failed',
					'anchor_text'    => $link_data['anchor_text'],
					'target_post_id' => $link_data['target_post_id'],
					'reason'         => $validation_errors[ $index ]->get_error_message(),
				);
				$total_failed++;
				continue;
			}

			$target_post = get_post( $link_data['target_post_id'] );
			$target_permalink = get_permalink( $target_post );

			// Check if anchor text exists.
			$occurrences = $this->link_parser->find_anchor_occurrences(
				$content,
				$link_data['anchor_text']
			);

			if ( empty( $occurrences ) ) {
				if ( $stop_on_error ) {
					// Rollback by not saving.
					return ErrorFactory::anchor_not_found( $link_data['anchor_text'], $source_post_id );
				}

				$results[] = array(
					'index'          => $index,
					'status'         => 'failed',
					'anchor_text'    => $link_data['anchor_text'],
					'target_post_id' => $link_data['target_post_id'],
					'reason'         => __( 'Anchor text not found', 'internal-links-api' ),
				);
				$total_failed++;
				continue;
			}

			// Add the link.
			$result = $this->link_modifier->add_link(
				$content,
				$link_data['anchor_text'],
				$target_permalink,
				$link_data['attributes'] ?? array(),
				$link_data['occurrence'] ?? 'first',
				$link_data['if_exists'] ?? 'skip',
				$editor_type
			);

			$content = $result['content'];

			if ( $result['links_added'] > 0 ) {
				$results[] = array(
					'index'          => $index,
					'status'         => 'added',
					'anchor_text'    => $link_data['anchor_text'],
					'target_post_id' => $link_data['target_post_id'],
				);
				$total_added++;
			} elseif ( ! empty( $result['skipped'] ) ) {
				$results[] = array(
					'index'          => $index,
					'status'         => 'skipped',
					'anchor_text'    => $link_data['anchor_text'],
					'target_post_id' => $link_data['target_post_id'],
					'reason'         => 'already_linked',
				);
				$total_skipped++;
			}
		}

		// Update post content (single revision for entire batch).
		if ( $total_added > 0 ) {
			wp_update_post(
				array(
					'ID'           => $source_post_id,
					'post_content' => $content,
				)
			);
		}

		return array(
			'success'         => $total_added > 0 || $total_skipped > 0,
			'source_post_id'  => $source_post_id,
			'total_requested' => count( $links ),
			'total_added'     => $total_added,
			'total_skipped'   => $total_skipped,
			'total_failed'    => $total_failed,
			'results'         => $results,
		);
	}

	/**
	 * Pre-validate all target posts.
	 *
	 * @param array $links Link data array.
	 * @return array Array of validation errors keyed by index.
	 */
	private function validate_targets( array $links ): array {
		$errors = array();

		foreach ( $links as $index => $link_data ) {
			$target_post_id = $link_data['target_post_id'];
			$target_post = get_post( $target_post_id );

			if ( ! $target_post ) {
				$errors[ $index ] = ErrorFactory::post_not_found( $target_post_id );
				continue;
			}

			if ( ! $this->settings->is_supported_post_type( $target_post->post_type ) ) {
				$errors[ $index ] = ErrorFactory::invalid_post_type( $target_post->post_type, $target_post_id );
				continue;
			}

			if ( 'publish' !== $target_post->post_status ) {
				$errors[ $index ] = ErrorFactory::target_not_published( $target_post_id, $target_post->post_status );
			}
		}

		return $errors;
	}
}
