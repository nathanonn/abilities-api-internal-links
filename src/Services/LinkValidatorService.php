<?php
/**
 * Link validator service.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Services;

use WP_Post;

/**
 * Service for validating internal links.
 */
class LinkValidatorService {

	/**
	 * Link parser service.
	 *
	 * @var LinkParserService
	 */
	private LinkParserService $link_parser;

	/**
	 * Constructor.
	 *
	 * @param LinkParserService $link_parser Link parser service.
	 */
	public function __construct( LinkParserService $link_parser ) {
		$this->link_parser = $link_parser;
	}

	/**
	 * Validate all internal links in a post.
	 *
	 * @param WP_Post $post Post to validate.
	 * @return array Validation results.
	 */
	public function validate( WP_Post $post ): array {
		$links = $this->link_parser->extract_links( $post->post_content );
		$internal_links = array_filter(
			$links,
			function ( $link ) {
				return $link['is_internal'];
			}
		);

		$summary = array(
			'valid'              => 0,
			'broken'             => 0,
			'unpublished'        => 0,
			'permalink_mismatch' => 0,
		);

		$issues = array();

		foreach ( $internal_links as $link ) {
			$validation = $this->validate_single_link( $link );

			if ( null === $validation ) {
				$summary['valid']++;
			} else {
				$summary[ $validation['type'] ]++;
				$issues[] = $validation;
			}
		}

		return array(
			'post_id'              => $post->ID,
			'total_internal_links' => count( $internal_links ),
			'validation_summary'   => $summary,
			'issues'               => $issues,
		);
	}

	/**
	 * Validate a single link.
	 *
	 * @param array $link Link data from extract_links().
	 * @return array|null Issue data or null if valid.
	 */
	public function validate_single_link( array $link ): ?array {
		$url = $link['url'];
		$post_id = $this->link_parser->get_post_id_from_url( $url );

		// Check if post exists.
		if ( null === $post_id ) {
			return array(
				'type'        => 'broken',
				'anchor_text' => $link['anchor_text'],
				'url'         => $url,
				'position'    => $link['position'],
				'reason'      => __( 'Post does not exist', 'internal-links-api' ),
			);
		}

		$target_post = get_post( $post_id );

		if ( ! $target_post ) {
			return array(
				'type'        => 'broken',
				'anchor_text' => $link['anchor_text'],
				'url'         => $url,
				'position'    => $link['position'],
				'reason'      => __( 'Post does not exist', 'internal-links-api' ),
			);
		}

		// Check if post is published.
		if ( 'publish' !== $target_post->post_status ) {
			return array(
				'type'           => 'unpublished',
				'anchor_text'    => $link['anchor_text'],
				'url'            => $url,
				'target_post_id' => $post_id,
				'target_status'  => $target_post->post_status,
				'position'       => $link['position'],
			);
		}

		// Check for permalink mismatch.
		$current_permalink = get_permalink( $target_post );
		if ( $this->permalinks_differ( $url, $current_permalink ) ) {
			return array(
				'type'              => 'permalink_mismatch',
				'anchor_text'       => $link['anchor_text'],
				'url'               => $url,
				'target_post_id'    => $post_id,
				'current_permalink' => $current_permalink,
				'position'          => $link['position'],
			);
		}

		// Link is valid.
		return null;
	}

	/**
	 * Generate a comprehensive link report for a post.
	 *
	 * @param WP_Post $post Post to generate report for.
	 * @return array Link report.
	 */
	public function generate_report( WP_Post $post ): array {
		$links = $this->link_parser->extract_links( $post->post_content );

		$internal_links = array(
			'valid'              => array(),
			'broken'             => array(),
			'unpublished'        => array(),
			'permalink_mismatch' => array(),
		);

		$external_links = array();
		$links_with_nofollow = 0;

		foreach ( $links as $link ) {
			// Check for nofollow.
			$rel = $link['attributes']['rel'] ?? '';
			if ( strpos( $rel, 'nofollow' ) !== false ) {
				$links_with_nofollow++;
			}

			if ( ! $link['is_internal'] ) {
				$external_links[] = array(
					'position'    => $link['position'],
					'anchor_text' => $link['anchor_text'],
					'url'         => $link['url'],
					'attributes'  => $link['attributes'],
				);
				continue;
			}

			// Validate internal link.
			$validation = $this->validate_single_link( $link );

			if ( null === $validation ) {
				// Valid link - get target post details.
				$post_id = $this->link_parser->get_post_id_from_url( $link['url'] );
				$target_post = get_post( $post_id );

				$internal_links['valid'][] = array(
					'position'    => $link['position'],
					'anchor_text' => $link['anchor_text'],
					'url'         => $link['url'],
					'target_post' => $this->get_target_post_data( $target_post ),
					'attributes'  => $link['attributes'],
				);
			} else {
				// Add to appropriate category.
				$type = $validation['type'];
				$link_data = array(
					'position'    => $link['position'],
					'anchor_text' => $link['anchor_text'],
					'url'         => $link['url'],
					'attributes'  => $link['attributes'],
				);

				// Merge validation-specific data.
				if ( isset( $validation['reason'] ) ) {
					$link_data['reason'] = $validation['reason'];
				}
				if ( isset( $validation['target_post_id'] ) ) {
					$target_post = get_post( $validation['target_post_id'] );
					if ( 'unpublished' === $type ) {
						$link_data['target_post'] = array(
							'id'     => $validation['target_post_id'],
							'title'  => $target_post ? $target_post->post_title : '',
							'status' => $validation['target_status'],
						);
					} elseif ( 'permalink_mismatch' === $type ) {
						$link_data['current_permalink'] = $validation['current_permalink'];
						$link_data['target_post'] = array(
							'id'    => $validation['target_post_id'],
							'title' => $target_post ? $target_post->post_title : '',
						);
					}
				}

				$internal_links[ $type ][] = $link_data;
			}
		}

		return array(
			'post_id'        => $post->ID,
			'post_title'     => $post->post_title,
			'generated_at'   => gmdate( 'c' ),
			'summary'        => array(
				'total_links'        => count( $links ),
				'internal_links'     => count( $links ) - count( $external_links ),
				'external_links'     => count( $external_links ),
				'broken_links'       => count( $internal_links['broken'] ),
				'links_with_nofollow' => $links_with_nofollow,
			),
			'internal_links' => $internal_links,
			'external_links' => $external_links,
		);
	}

	/**
	 * Get target post data for report.
	 *
	 * @param WP_Post|null $post Target post.
	 * @return array|null Post data or null.
	 */
	private function get_target_post_data( ?WP_Post $post ): ?array {
		if ( ! $post ) {
			return null;
		}

		$author = get_userdata( $post->post_author );

		return array(
			'id'         => $post->ID,
			'title'      => $post->post_title,
			'post_type'  => $post->post_type,
			'status'     => $post->post_status,
			'author'     => array(
				'id'   => $post->post_author,
				'name' => $author ? $author->display_name : '',
			),
			'date'       => gmdate( 'c', strtotime( $post->post_date_gmt ) ),
			'categories' => $this->get_taxonomy_terms( $post->ID, 'category' ),
			'tags'       => $this->get_taxonomy_terms( $post->ID, 'post_tag' ),
		);
	}

	/**
	 * Get taxonomy terms for a post.
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $taxonomy Taxonomy name.
	 * @return array Array of term data.
	 */
	private function get_taxonomy_terms( int $post_id, string $taxonomy ): array {
		$terms = get_the_terms( $post_id, $taxonomy );

		if ( ! is_array( $terms ) ) {
			return array();
		}

		return array_map(
			function ( $term ) {
				return array(
					'id'   => $term->term_id,
					'name' => $term->name,
				);
			},
			$terms
		);
	}

	/**
	 * Check if two permalinks differ (accounting for trailing slashes).
	 *
	 * @param string $url1 First URL.
	 * @param string $url2 Second URL.
	 * @return bool True if they differ.
	 */
	private function permalinks_differ( string $url1, string $url2 ): bool {
		// Normalize URLs.
		$url1 = trailingslashit( strtolower( $url1 ) );
		$url2 = trailingslashit( strtolower( $url2 ) );

		// Remove query strings and fragments for comparison.
		$url1 = strtok( $url1, '?#' );
		$url2 = strtok( $url2, '?#' );

		return $url1 !== $url2;
	}
}
