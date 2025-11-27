<?php
/**
 * Add link ability schema.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Schemas;

/**
 * Schema definitions for the add-link ability.
 */
class AddLinkSchema {

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
					'description' => __( 'The post ID to add the link to', 'internal-links-api' ),
				),
				'target_post_id' => array(
					'type'        => 'integer',
					'minimum'     => 1,
					'description' => __( 'The post ID to link to', 'internal-links-api' ),
				),
				'anchor_text'    => array(
					'type'        => 'string',
					'minLength'   => 1,
					'description' => __( 'The text to convert into a link', 'internal-links-api' ),
				),
				'occurrence'     => array(
					'oneOf'       => array(
						array(
							'type' => 'string',
							'enum' => array( 'first', 'last', 'all' ),
						),
						array(
							'type'    => 'integer',
							'minimum' => 1,
						),
					),
					'default'     => 'first',
					'description' => __( 'Which occurrence to link: "first", "last", "all", or integer (1-based)', 'internal-links-api' ),
				),
				'attributes'     => array(
					'type'                 => 'object',
					'additionalProperties' => array( 'type' => 'string' ),
					'default'              => array(),
					'description'          => __( 'Link attributes as key-value pairs (e.g., rel, target)', 'internal-links-api' ),
				),
				'if_exists'      => array(
					'type'        => 'string',
					'enum'        => array( 'skip', 'replace' ),
					'default'     => 'skip',
					'description' => __( 'Behavior if anchor text already linked: "skip" or "replace"', 'internal-links-api' ),
				),
			),
			'required'             => array( 'source_post_id', 'target_post_id', 'anchor_text' ),
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
				'success'            => array( 'type' => 'boolean' ),
				'links_added'        => array( 'type' => 'integer' ),
				'source_post_id'     => array( 'type' => 'integer' ),
				'target_post_id'     => array( 'type' => 'integer' ),
				'target_permalink'   => array( 'type' => 'string', 'format' => 'uri' ),
				'anchor_text'        => array( 'type' => 'string' ),
				'occurrences_found'  => array( 'type' => 'integer' ),
				'occurrences_linked' => array( 'type' => 'integer' ),
				'skipped'            => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'occurrence'   => array( 'type' => 'integer' ),
							'reason'       => array( 'type' => 'string' ),
							'existing_url' => array( 'type' => 'string' ),
						),
					),
				),
			),
		);
	}
}
