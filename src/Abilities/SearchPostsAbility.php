<?php
/**
 * Search posts ability.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Abilities;

use InternalLinksAPI\Services\PostService;
use InternalLinksAPI\Settings;
use WP_Query;

/**
 * Ability to search posts for internal link targets.
 */
class SearchPostsAbility {

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
	 * Execute the search posts ability.
	 *
	 * @param array $input Input parameters.
	 * @return array Search results.
	 */
	public function execute( array $input = array() ): array {
		$supported_post_types = $this->settings->get_supported_post_types();
		$args = $this->post_service->build_search_query_args( $input, $supported_post_types );

		// Add title search filter if needed.
		if ( ! empty( $input['keyword'] ) && 'title' === ( $input['search_scope'] ?? 'all' ) ) {
			add_filter( 'posts_where', array( $this, 'filter_title_search' ), 10, 2 );
		}

		$query = new WP_Query( $args );

		// Remove title search filter.
		remove_filter( 'posts_where', array( $this, 'filter_title_search' ), 10 );

		$results = array();
		foreach ( $query->posts as $post ) {
			$results[] = $this->post_service->format_search_result( $post );
		}

		return array(
			'results'    => $results,
			'pagination' => array(
				'total'        => $query->found_posts,
				'total_pages'  => $query->max_num_pages,
				'current_page' => $args['paged'],
				'per_page'     => $args['posts_per_page'],
			),
		);
	}

	/**
	 * Filter for title-only search.
	 *
	 * @param string   $where Current WHERE clause.
	 * @param WP_Query $query Query object.
	 * @return string Modified WHERE clause.
	 */
	public function filter_title_search( string $where, WP_Query $query ): string {
		global $wpdb;

		$keyword = $query->get( 'post_title_like' );
		if ( $keyword ) {
			$where .= $wpdb->prepare(
				" AND {$wpdb->posts}.post_title LIKE %s",
				'%' . $wpdb->esc_like( $keyword ) . '%'
			);
		}

		return $where;
	}
}
