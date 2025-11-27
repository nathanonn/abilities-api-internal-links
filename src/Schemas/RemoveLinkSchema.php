<?php
/**
 * Remove link ability schema.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Schemas;

/**
 * Schema definitions for the remove-link ability.
 */
class RemoveLinkSchema {

	/**
	 * Get input schema.
	 *
	 * @return array JSON Schema.
	 */
	public static function get_input_schema(): array {
		return array(
			'type'                 => 'object',
			'properties'           => array(
				'source_post_id' => array(
					'type'        => 'integer',
					'minimum'     => 1,
					'description' => __( 'The post ID containing the link', 'internal-links-api' ),
				),
				'identifier'     => array(
					'type'        => 'object',
					'description' => __( 'How to identify the link (same as update-link)', 'internal-links-api' ),
					'oneOf'       => array(
						array(
							'type'       => 'object',
							'properties' => array(
								'by'  => array( 'type' => 'string', 'const' => 'url' ),
								'url' => array( 'type' => 'string', 'format' => 'uri' ),
							),
							'required'   => array( 'by', 'url' ),
						),
						array(
							'type'       => 'object',
							'properties' => array(
								'by'          => array( 'type' => 'string', 'const' => 'anchor' ),
								'anchor_text' => array( 'type' => 'string' ),
								'occurrence'  => array( 'type' => 'integer', 'minimum' => 1, 'default' => 1 ),
							),
							'required'   => array( 'by', 'anchor_text' ),
						),
						array(
							'type'       => 'object',
							'properties' => array(
								'by'    => array( 'type' => 'string', 'const' => 'index' ),
								'index' => array( 'type' => 'integer', 'minimum' => 1 ),
							),
							'required'   => array( 'by', 'index' ),
						),
					),
				),
				'action'         => array(
					'type'        => 'string',
					'enum'        => array( 'unlink', 'delete' ),
					'default'     => 'unlink',
					'description' => __( 'Action: "unlink" (keep text) or "delete" (remove text too)', 'internal-links-api' ),
				),
			),
			'required'             => array( 'source_post_id', 'identifier' ),
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
				'success'        => array( 'type' => 'boolean' ),
				'links_removed'  => array( 'type' => 'integer' ),
				'source_post_id' => array( 'type' => 'integer' ),
				'action'         => array( 'type' => 'string' ),
				'removed_links'  => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'anchor_text' => array( 'type' => 'string' ),
							'target_url'  => array( 'type' => 'string' ),
							'position'    => array( 'type' => 'integer' ),
						),
					),
				),
			),
		);
	}
}
