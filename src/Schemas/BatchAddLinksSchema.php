<?php
/**
 * Batch add links ability schema.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Schemas;

/**
 * Schema definitions for the batch-add-links ability.
 */
class BatchAddLinksSchema {

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
					'description' => __( 'The post ID to add links to', 'internal-links-api' ),
				),
				'links'          => array(
					'type'        => 'array',
					'minItems'    => 1,
					'maxItems'    => 50,
					'description' => __( 'Array of link objects to add (max 50)', 'internal-links-api' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'target_post_id' => array(
								'type'    => 'integer',
								'minimum' => 1,
							),
							'anchor_text'    => array(
								'type'      => 'string',
								'minLength' => 1,
								'maxLength' => 1000,
							),
							'occurrence'     => array(
								'oneOf'   => array(
									array(
										'type' => 'string',
										'enum' => array( 'first', 'last', 'all' ),
									),
									array(
										'type'    => 'integer',
										'minimum' => 1,
									),
								),
								'default' => 'first',
							),
							'attributes'     => array(
								'type'                 => 'object',
								'additionalProperties' => array( 'type' => 'string' ),
								'default'              => array(),
							),
							'if_exists'      => array(
								'type'    => 'string',
								'enum'    => array( 'skip', 'replace' ),
								'default' => 'skip',
							),
						),
						'required'   => array( 'target_post_id', 'anchor_text' ),
					),
				),
				'stop_on_error'  => array(
					'type'        => 'boolean',
					'default'     => false,
					'description' => __( 'If true, stop processing on first error', 'internal-links-api' ),
				),
			),
			'required'             => array( 'source_post_id', 'links' ),
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
				'success'         => array( 'type' => 'boolean' ),
				'source_post_id'  => array( 'type' => 'integer' ),
				'total_requested' => array( 'type' => 'integer' ),
				'total_added'     => array( 'type' => 'integer' ),
				'total_skipped'   => array( 'type' => 'integer' ),
				'total_failed'    => array( 'type' => 'integer' ),
				'results'         => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'index'          => array( 'type' => 'integer' ),
							'status'         => array(
								'type' => 'string',
								'enum' => array( 'added', 'skipped', 'failed' ),
							),
							'anchor_text'    => array( 'type' => 'string' ),
							'target_post_id' => array( 'type' => 'integer' ),
							'reason'         => array( 'type' => 'string' ),
						),
					),
				),
			),
		);
	}
}
