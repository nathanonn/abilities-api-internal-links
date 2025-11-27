<?php
/**
 * Update link ability schema.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Schemas;

/**
 * Schema definitions for the update-link ability.
 */
class UpdateLinkSchema {

	/**
	 * Get input schema.
	 *
	 * @return array JSON Schema.
	 */
	public static function get_input_schema(): array {
		return array(
			'type'                 => 'object',
			'properties'           => array(
				'source_post_id'     => array(
					'type'        => 'integer',
					'minimum'     => 1,
					'description' => __( 'The post ID containing the link', 'internal-links-api' ),
				),
				'identifier'         => array(
					'type'        => 'object',
					'description' => __( 'How to identify the link', 'internal-links-api' ),
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
				'new_target_post_id' => array(
					'type'        => 'integer',
					'minimum'     => 1,
					'description' => __( 'New target post ID (if changing target)', 'internal-links-api' ),
				),
				'new_anchor_text'    => array(
					'type'        => 'string',
					'minLength'   => 1,
					'description' => __( 'New anchor text (if changing text)', 'internal-links-api' ),
				),
				'attributes'         => array(
					'type'                 => 'object',
					'additionalProperties' => array( 'type' => 'string' ),
					'description'          => __( 'New attributes (replaces all existing unless merge_attributes is true)', 'internal-links-api' ),
				),
				'merge_attributes'   => array(
					'type'        => 'boolean',
					'default'     => false,
					'description' => __( 'If true, merge attributes instead of replacing', 'internal-links-api' ),
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
				'links_updated'  => array( 'type' => 'integer' ),
				'source_post_id' => array( 'type' => 'integer' ),
				'changes'        => array(
					'type'       => 'object',
					'properties' => array(
						'target'     => array(
							'oneOf' => array(
								array( 'type' => 'null' ),
								array(
									'type'       => 'object',
									'properties' => array(
										'old' => array( 'type' => 'string' ),
										'new' => array( 'type' => 'string' ),
									),
								),
							),
						),
						'anchor'     => array(
							'oneOf' => array(
								array( 'type' => 'null' ),
								array(
									'type'       => 'object',
									'properties' => array(
										'old' => array( 'type' => 'string' ),
										'new' => array( 'type' => 'string' ),
									),
								),
							),
						),
						'attributes' => array(
							'type'       => 'object',
							'properties' => array(
								'added'   => array(
									'type'                 => 'object',
									'additionalProperties' => array( 'type' => 'string' ),
								),
								'removed' => array(
									'type'  => 'array',
									'items' => array( 'type' => 'string' ),
								),
							),
						),
					),
				),
			),
		);
	}
}
