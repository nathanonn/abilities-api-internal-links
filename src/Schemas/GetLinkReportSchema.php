<?php
/**
 * Get link report ability schema.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Schemas;

/**
 * Schema definitions for the get-link-report ability.
 */
class GetLinkReportSchema {

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
					'description' => __( 'The post ID to generate report for', 'internal-links-api' ),
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
		$link_item_schema = array(
			'type'       => 'object',
			'properties' => array(
				'position'    => array( 'type' => 'integer' ),
				'anchor_text' => array( 'type' => 'string' ),
				'url'         => array( 'type' => 'string' ),
				'attributes'  => array(
					'type'                 => 'object',
					'additionalProperties' => array( 'type' => 'string' ),
				),
				'target_post' => array(
					'type'       => 'object',
					'properties' => array(
						'id'         => array( 'type' => 'integer' ),
						'title'      => array( 'type' => 'string' ),
						'post_type'  => array( 'type' => 'string' ),
						'status'     => array( 'type' => 'string' ),
						'author'     => array(
							'type'       => 'object',
							'properties' => array(
								'id'   => array( 'type' => 'integer' ),
								'name' => array( 'type' => 'string' ),
							),
						),
						'date'       => array( 'type' => 'string', 'format' => 'date-time' ),
						'categories' => array( 'type' => 'array' ),
						'tags'       => array( 'type' => 'array' ),
					),
				),
				'reason'            => array( 'type' => 'string' ),
				'current_permalink' => array( 'type' => 'string' ),
			),
		);

		return array(
			'type'       => 'object',
			'properties' => array(
				'post_id'        => array( 'type' => 'integer' ),
				'post_title'     => array( 'type' => 'string' ),
				'generated_at'   => array( 'type' => 'string', 'format' => 'date-time' ),
				'summary'        => array(
					'type'       => 'object',
					'properties' => array(
						'total_links'         => array( 'type' => 'integer' ),
						'internal_links'      => array( 'type' => 'integer' ),
						'external_links'      => array( 'type' => 'integer' ),
						'broken_links'        => array( 'type' => 'integer' ),
						'links_with_nofollow' => array( 'type' => 'integer' ),
					),
				),
				'internal_links' => array(
					'type'       => 'object',
					'properties' => array(
						'valid'              => array(
							'type'  => 'array',
							'items' => $link_item_schema,
						),
						'broken'             => array(
							'type'  => 'array',
							'items' => $link_item_schema,
						),
						'unpublished'        => array(
							'type'  => 'array',
							'items' => $link_item_schema,
						),
						'permalink_mismatch' => array(
							'type'  => 'array',
							'items' => $link_item_schema,
						),
					),
				),
				'external_links' => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'position'    => array( 'type' => 'integer' ),
							'anchor_text' => array( 'type' => 'string' ),
							'url'         => array( 'type' => 'string' ),
							'attributes'  => array(
								'type'                 => 'object',
								'additionalProperties' => array( 'type' => 'string' ),
							),
						),
					),
				),
			),
		);
	}
}
