<?php
/**
 * Post service.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Services;

use WP_Post;
use WP_User;

/**
 * Service for post retrieval and formatting.
 */
class PostService {

	/**
	 * Editor detector service.
	 *
	 * @var EditorDetectorService
	 */
	private EditorDetectorService $editor_detector;

	/**
	 * Constructor.
	 *
	 * @param EditorDetectorService $editor_detector Editor detector service.
	 */
	public function __construct( EditorDetectorService $editor_detector ) {
		$this->editor_detector = $editor_detector;
	}

	/**
	 * Get a post by ID.
	 *
	 * @param int $post_id Post ID.
	 * @return WP_Post|null Post object or null if not found.
	 */
	public function get_post( int $post_id ): ?WP_Post {
		$post = get_post( $post_id );

		if ( ! $post instanceof WP_Post ) {
			return null;
		}

		return $post;
	}

	/**
	 * Format post data for API response.
	 *
	 * @param WP_Post $post Post object.
	 * @return array Formatted post data.
	 */
	public function format_post( WP_Post $post ): array {
		return array(
			'id'               => $post->ID,
			'title'            => $post->post_title,
			'content'          => $post->post_content,
			'excerpt'          => $post->post_excerpt,
			'post_type'        => $post->post_type,
			'post_status'      => $post->post_status,
			'permalink'        => get_permalink( $post ),
			'slug'             => $post->post_name,
			'editor_type'      => $this->editor_detector->get_editor_type( $post ),
			'featured_image'   => $this->get_featured_image( $post->ID ),
			'author'           => $this->get_author_data( $post->post_author ),
			'date'             => gmdate( 'c', strtotime( $post->post_date_gmt ) ),
			'modified'         => gmdate( 'c', strtotime( $post->post_modified_gmt ) ),
			'categories'       => $this->get_taxonomy_terms( $post->ID, 'category' ),
			'tags'             => $this->get_taxonomy_terms( $post->ID, 'post_tag' ),
			'custom_taxonomies' => $this->get_custom_taxonomy_terms( $post ),
		);
	}

	/**
	 * Format post for search results (less detail).
	 *
	 * @param WP_Post $post Post object.
	 * @return array Formatted search result.
	 */
	public function format_search_result( WP_Post $post ): array {
		return array(
			'id'         => $post->ID,
			'title'      => $post->post_title,
			'post_type'  => $post->post_type,
			'permalink'  => get_permalink( $post ),
			'excerpt'    => $this->get_excerpt( $post ),
			'author'     => $this->get_author_data( $post->post_author ),
			'date'       => gmdate( 'c', strtotime( $post->post_date_gmt ) ),
			'modified'   => gmdate( 'c', strtotime( $post->post_modified_gmt ) ),
			'categories' => $this->get_taxonomy_terms( $post->ID, 'category' ),
			'tags'       => $this->get_taxonomy_terms( $post->ID, 'post_tag' ),
		);
	}

	/**
	 * Get editor type for a post.
	 *
	 * @param WP_Post $post Post object.
	 * @return string "gutenberg" or "classic".
	 */
	public function get_editor_type( WP_Post $post ): string {
		return $this->editor_detector->get_editor_type( $post );
	}

	/**
	 * Get featured image data.
	 *
	 * @param int $post_id Post ID.
	 * @return array|null Featured image data or null.
	 */
	private function get_featured_image( int $post_id ): ?array {
		$thumbnail_id = get_post_thumbnail_id( $post_id );

		if ( ! $thumbnail_id ) {
			return null;
		}

		$image_url = wp_get_attachment_url( $thumbnail_id );

		// wp_get_attachment_url returns false if attachment doesn't exist.
		if ( false === $image_url ) {
			return null;
		}

		$alt_text = get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );

		return array(
			'id'  => $thumbnail_id,
			'url' => $image_url,
			'alt' => $alt_text ?: '',
		);
	}

	/**
	 * Get author data.
	 *
	 * @param int $author_id Author user ID.
	 * @return array Author data.
	 */
	private function get_author_data( int $author_id ): array {
		$user = get_userdata( $author_id );

		if ( ! $user instanceof WP_User ) {
			return array(
				'id'   => $author_id,
				'name' => __( 'Unknown', 'internal-links-api' ),
				'slug' => '',
			);
		}

		return array(
			'id'   => $user->ID,
			'name' => $user->display_name,
			'slug' => $user->user_nicename,
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
					'slug' => $term->slug,
				);
			},
			$terms
		);
	}

	/**
	 * Get custom taxonomy terms for a post.
	 *
	 * @param WP_Post $post Post object.
	 * @return array Array of custom taxonomy terms keyed by taxonomy slug.
	 */
	private function get_custom_taxonomy_terms( WP_Post $post ): array {
		$result = array();
		$taxonomies = get_object_taxonomies( $post->post_type, 'objects' );

		foreach ( $taxonomies as $taxonomy ) {
			// Skip built-in taxonomies.
			if ( in_array( $taxonomy->name, array( 'category', 'post_tag', 'post_format' ), true ) ) {
				continue;
			}

			// Only include public taxonomies.
			if ( ! $taxonomy->public ) {
				continue;
			}

			$terms = $this->get_taxonomy_terms( $post->ID, $taxonomy->name );

			if ( ! empty( $terms ) ) {
				$result[ $taxonomy->name ] = $terms;
			}
		}

		return $result;
	}

	/**
	 * Get excerpt for a post.
	 *
	 * @param WP_Post $post Post object.
	 * @return string Post excerpt.
	 */
	private function get_excerpt( WP_Post $post ): string {
		if ( ! empty( $post->post_excerpt ) ) {
			return $post->post_excerpt;
		}

		// Generate excerpt from content.
		$content = wp_strip_all_tags( $post->post_content );
		$content = strip_shortcodes( $content );

		return wp_trim_words( $content, 55, '...' );
	}

	/**
	 * Build WP_Query args for post search.
	 *
	 * @param array $params Search parameters.
	 * @param array $supported_post_types Supported post types.
	 * @return array WP_Query arguments.
	 */
	public function build_search_query_args( array $params, array $supported_post_types ): array {
		$args = array(
			'post_type'      => $params['post_type'] ?? $supported_post_types,
			'post_status'    => $params['post_status'] ?? 'publish',
			'posts_per_page' => min( $params['per_page'] ?? 20, 100 ),
			'paged'          => $params['page'] ?? 1,
		);

		// Ensure post types are supported.
		if ( is_array( $args['post_type'] ) ) {
			$args['post_type'] = array_intersect( $args['post_type'], $supported_post_types );
		} elseif ( ! in_array( $args['post_type'], $supported_post_types, true ) ) {
			$args['post_type'] = $supported_post_types;
		}

		// Keyword search.
		if ( ! empty( $params['keyword'] ) ) {
			$search_scope = $params['search_scope'] ?? 'all';

			if ( 'title' === $search_scope ) {
				$args['s'] = '';
				$args['post_title_like'] = $params['keyword'];
			} else {
				$args['s'] = $params['keyword'];
			}
		}

		// Category filter.
		if ( ! empty( $params['category'] ) ) {
			$args['category__in'] = (array) $params['category'];
		}

		// Tag filter.
		if ( ! empty( $params['tag'] ) ) {
			$args['tag__in'] = (array) $params['tag'];
		}

		// Custom taxonomy filter.
		if ( ! empty( $params['taxonomy'] ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => $params['taxonomy']['taxonomy'],
					'field'    => 'term_id',
					'terms'    => $params['taxonomy']['terms'],
				),
			);
		}

		// Author filter.
		if ( ! empty( $params['author'] ) ) {
			$args['author'] = $params['author'];
		}

		// Date filters.
		$date_query = array();
		if ( ! empty( $params['date_after'] ) ) {
			$date_query['after'] = $params['date_after'];
		}
		if ( ! empty( $params['date_before'] ) ) {
			$date_query['before'] = $params['date_before'];
		}
		if ( ! empty( $date_query ) ) {
			$args['date_query'] = array( $date_query );
		}

		// Exclude posts.
		if ( ! empty( $params['exclude'] ) ) {
			$args['post__not_in'] = (array) $params['exclude'];
		}

		// Order.
		$orderby_map = array(
			'relevance' => 'relevance',
			'date'      => 'date',
			'title'     => 'title',
			'modified'  => 'modified',
		);

		$orderby = $params['orderby'] ?? 'relevance';
		$args['orderby'] = $orderby_map[ $orderby ] ?? 'relevance';
		$args['order'] = strtoupper( $params['order'] ?? 'desc' );

		// If no keyword search and orderby is relevance, default to date.
		if ( empty( $params['keyword'] ) && 'relevance' === $args['orderby'] ) {
			$args['orderby'] = 'date';
		}

		return $args;
	}
}
