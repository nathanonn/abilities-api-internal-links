<?php
/**
 * Batch remove links ability schema.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Schemas;

/**
 * Schema definitions for the batch-remove-links ability.
 */
class BatchRemoveLinksSchema {

	/**
	 * Get input schema.
	 *
	 * @return array JSON Schema.
	 */
	public static function get_input_schema(): array {
		$identifier_schema = array(
			'type'  => 'object',
			'oneOf' => array(
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
		);

		return array(
			'type'                 => 'object',
			'properties'           => array(
				'source_post_id' => array(
					'type'        => 'integer',
					'minimum'     => 1,
					'description' => __( 'The post ID to remove links from', 'internal-links-api' ),
				),
				'links'          => array(
					'type'        => 'array',
					'minItems'    => 1,
					'maxItems'    => 50,
					'description' => __( 'Array of link removal objects (max 50)', 'internal-links-api' ),
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'identifier' => $identifier_schema,
							'action'     => array(
								'type'    => 'string',
								'enum'    => array( 'unlink', 'delete' ),
								'default' => 'unlink',
							),
						),
						'required'   => array( 'identifier' ),
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
				'total_removed'   => array( 'type' => 'integer' ),
				'total_failed'    => array( 'type' => 'integer' ),
				'results'         => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'index'       => array( 'type' => 'integer' ),
							'status'      => array(
								'type' => 'string',
								'enum' => array( 'removed', 'failed' ),
							),
							'action'      => array( 'type' => 'string' ),
							'anchor_text' => array( 'type' => 'string' ),
							'reason'      => array( 'type' => 'string' ),
						),
					),
				),
			),
		);
	}
}
