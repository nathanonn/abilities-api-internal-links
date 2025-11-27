<?php
/**
 * Validate links ability schema.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Schemas;

/**
 * Schema definitions for the validate-links ability.
 */
class ValidateLinksSchema {

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
					'description' => __( 'The post ID to validate links for', 'internal-links-api' ),
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
				'post_id'              => array( 'type' => 'integer' ),
				'total_internal_links' => array( 'type' => 'integer' ),
				'validation_summary'   => array(
					'type'       => 'object',
					'properties' => array(
						'valid'              => array( 'type' => 'integer' ),
						'broken'             => array( 'type' => 'integer' ),
						'unpublished'        => array( 'type' => 'integer' ),
						'permalink_mismatch' => array( 'type' => 'integer' ),
					),
				),
				'issues'               => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'type'              => array(
								'type' => 'string',
								'enum' => array( 'broken', 'unpublished', 'permalink_mismatch' ),
							),
							'anchor_text'       => array( 'type' => 'string' ),
							'url'               => array( 'type' => 'string' ),
							'position'          => array( 'type' => 'integer' ),
							'reason'            => array( 'type' => 'string' ),
							'target_post_id'    => array( 'type' => 'integer' ),
							'target_status'     => array( 'type' => 'string' ),
							'current_permalink' => array( 'type' => 'string' ),
						),
					),
				),
			),
		);
	}
}
