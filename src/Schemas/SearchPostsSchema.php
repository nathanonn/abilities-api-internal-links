<?php
/**
 * Search posts ability schema.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Schemas;

/**
 * Schema definitions for the search-posts ability.
 */
class SearchPostsSchema {

	/**
	 * Get input schema.
	 *
	 * @return array JSON Schema.
	 */
	public static function get_input_schema(): array {
		return array(
			'type'                 => 'object',
			'properties'           => array(
				'keyword'      => array(
					'type'        => 'string',
					'description' => __( 'Search keyword for full-text search', 'internal-links-api' ),
				),
				'post_type'    => array(
					'oneOf'       => array(
						array( 'type' => 'string' ),
						array(
							'type'  => 'array',
							'items' => array( 'type' => 'string' ),
						),
					),
					'description' => __( 'Post type(s) to search', 'internal-links-api' ),
				),
				'post_status'  => array(
					'type'        => 'string',
					'default'     => 'publish',
					'description' => __( 'Post status filter', 'internal-links-api' ),
				),
				'category'     => array(
					'oneOf'       => array(
						array( 'type' => 'integer' ),
						array(
							'type'  => 'array',
							'items' => array( 'type' => 'integer' ),
						),
					),
					'description' => __( 'Category ID(s) to filter by', 'internal-links-api' ),
				),
				'tag'          => array(
					'oneOf'       => array(
						array( 'type' => 'integer' ),
						array(
							'type'  => 'array',
							'items' => array( 'type' => 'integer' ),
						),
					),
					'description' => __( 'Tag ID(s) to filter by', 'internal-links-api' ),
				),
				'taxonomy'     => array(
					'type'        => 'object',
					'properties'  => array(
						'taxonomy' => array( 'type' => 'string' ),
						'terms'    => array(
							'type'  => 'array',
							'items' => array( 'type' => 'integer' ),
						),
					),
					'required'    => array( 'taxonomy', 'terms' ),
					'description' => __( 'Custom taxonomy query', 'internal-links-api' ),
				),
				'author'       => array(
					'type'        => 'integer',
					'description' => __( 'Author user ID', 'internal-links-api' ),
				),
				'date_after'   => array(
					'type'        => 'string',
					'format'      => 'date-time',
					'description' => __( 'Posts published after this date (ISO 8601)', 'internal-links-api' ),
				),
				'date_before'  => array(
					'type'        => 'string',
					'format'      => 'date-time',
					'description' => __( 'Posts published before this date (ISO 8601)', 'internal-links-api' ),
				),
				'search_scope' => array(
					'type'        => 'string',
					'enum'        => array( 'all', 'title', 'content', 'excerpt' ),
					'default'     => 'all',
					'description' => __( 'Search scope', 'internal-links-api' ),
				),
				'orderby'      => array(
					'type'        => 'string',
					'enum'        => array( 'relevance', 'date', 'title', 'modified' ),
					'default'     => 'relevance',
					'description' => __( 'Order by field', 'internal-links-api' ),
				),
				'order'        => array(
					'type'        => 'string',
					'enum'        => array( 'asc', 'desc' ),
					'default'     => 'desc',
					'description' => __( 'Sort order', 'internal-links-api' ),
				),
				'page'         => array(
					'type'        => 'integer',
					'minimum'     => 1,
					'default'     => 1,
					'description' => __( 'Page number for pagination', 'internal-links-api' ),
				),
				'per_page'     => array(
					'type'        => 'integer',
					'minimum'     => 1,
					'maximum'     => 100,
					'default'     => 20,
					'description' => __( 'Results per page (max: 100)', 'internal-links-api' ),
				),
				'exclude'      => array(
					'oneOf'       => array(
						array( 'type' => 'integer' ),
						array(
							'type'  => 'array',
							'items' => array( 'type' => 'integer' ),
						),
					),
					'description' => __( 'Post ID(s) to exclude from results', 'internal-links-api' ),
				),
			),
			'additionalProperties' => false,
		);
	}

	/**
	 * Get output schema.
	 *
	 * @return array JSON Schema.
	 */
	public static function get_output_schema(): array {
		return array(
			'type'       => 'object',
			'properties' => array(
				'results'    => array(
					'type'        => 'array',
					'description' => __( 'Search results', 'internal-links-api' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'         => array( 'type' => 'integer' ),
							'title'      => array( 'type' => 'string' ),
							'post_type'  => array( 'type' => 'string' ),
							'permalink'  => array( 'type' => 'string', 'format' => 'uri' ),
							'excerpt'    => array( 'type' => 'string' ),
							'author'     => array(
								'type'       => 'object',
								'properties' => array(
									'id'   => array( 'type' => 'integer' ),
									'name' => array( 'type' => 'string' ),
								),
							),
							'date'       => array( 'type' => 'string', 'format' => 'date-time' ),
							'modified'   => array( 'type' => 'string', 'format' => 'date-time' ),
							'categories' => array(
								'type'  => 'array',
								'items' => array(
									'type'       => 'object',
									'properties' => array(
										'id'   => array( 'type' => 'integer' ),
										'name' => array( 'type' => 'string' ),
										'slug' => array( 'type' => 'string' ),
									),
								),
							),
							'tags'       => array(
								'type'  => 'array',
								'items' => array(
									'type'       => 'object',
									'properties' => array(
										'id'   => array( 'type' => 'integer' ),
										'name' => array( 'type' => 'string' ),
										'slug' => array( 'type' => 'string' ),
									),
								),
							),
						),
					),
				),
				'pagination' => array(
					'type'       => 'object',
					'properties' => array(
						'total'        => array( 'type' => 'integer' ),
						'total_pages'  => array( 'type' => 'integer' ),
						'current_page' => array( 'type' => 'integer' ),
						'per_page'     => array( 'type' => 'integer' ),
					),
				),
			),
		);
	}
}
