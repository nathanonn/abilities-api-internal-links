<?php
/**
 * Get post ability schema.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Schemas;

/**
 * Schema definitions for the get-post ability.
 */
class GetPostSchema {

	/**
	 * Get input schema.
	 *
	 * @return array JSON Schema.
	 */
	public static function get_input_schema(): array {
		return array(
			'type'                 => 'object',
			'properties'           => array(
				'post_id' => array(
					'type'        => 'integer',
					'minimum'     => 1,
					'description' => __( 'The post ID to retrieve', 'internal-links-api' ),
				),
			),
			'required'             => array( 'post_id' ),
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
				'id'                => array( 'type' => 'integer' ),
				'title'             => array( 'type' => 'string' ),
				'content'           => array( 'type' => 'string' ),
				'excerpt'           => array( 'type' => 'string' ),
				'post_type'         => array( 'type' => 'string' ),
				'post_status'       => array( 'type' => 'string' ),
				'permalink'         => array( 'type' => 'string', 'format' => 'uri' ),
				'slug'              => array( 'type' => 'string' ),
				'editor_type'       => array(
					'type' => 'string',
					'enum' => array( 'gutenberg', 'classic' ),
				),
				'featured_image'    => array(
					'oneOf' => array(
						array( 'type' => 'null' ),
						array(
							'type'       => 'object',
							'properties' => array(
								'id'  => array( 'type' => 'integer' ),
								'url' => array( 'type' => 'string', 'format' => 'uri' ),
								'alt' => array( 'type' => 'string' ),
							),
						),
					),
				),
				'author'            => array(
					'type'       => 'object',
					'properties' => array(
						'id'   => array( 'type' => 'integer' ),
						'name' => array( 'type' => 'string' ),
						'slug' => array( 'type' => 'string' ),
					),
				),
				'date'              => array( 'type' => 'string', 'format' => 'date-time' ),
				'modified'          => array( 'type' => 'string', 'format' => 'date-time' ),
				'categories'        => array(
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
				'tags'              => array(
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
				'custom_taxonomies' => array(
					'type'                 => 'object',
					'additionalProperties' => array(
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
		);
	}
}
