<?php
/**
 * Tests for schema validation constraints.
 *
 * @package InternalLinksAPI\Tests\Unit\Schemas
 */

namespace InternalLinksAPI\Tests\Unit\Schemas;

use InternalLinksAPI\Schemas\AddLinkSchema;
use InternalLinksAPI\Schemas\BatchAddLinksSchema;
use InternalLinksAPI\Schemas\UpdateLinkSchema;
use InternalLinksAPI\Schemas\RemoveLinkSchema;
use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

/**
 * Schema validation test case.
 */
class SchemaValidationTest extends TestCase {

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		// Mock WordPress translation function.
		Functions\when( '__' )->returnArg( 1 );
	}

	/**
	 * Tear down test fixtures.
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	// =========================================================================
	// AddLinkSchema Tests
	// =========================================================================

	/**
	 * Test that AddLinkSchema has maxLength constraint on anchor_text.
	 */
	public function test_add_link_schema_has_anchor_text_max_length(): void {
		$schema = AddLinkSchema::get_input_schema();

		$this->assertArrayHasKey( 'properties', $schema );
		$this->assertArrayHasKey( 'anchor_text', $schema['properties'] );
		$this->assertArrayHasKey( 'maxLength', $schema['properties']['anchor_text'] );
		$this->assertEquals( 1000, $schema['properties']['anchor_text']['maxLength'] );
	}

	/**
	 * Test that AddLinkSchema has minLength constraint on anchor_text.
	 */
	public function test_add_link_schema_has_anchor_text_min_length(): void {
		$schema = AddLinkSchema::get_input_schema();

		$this->assertArrayHasKey( 'minLength', $schema['properties']['anchor_text'] );
		$this->assertEquals( 1, $schema['properties']['anchor_text']['minLength'] );
	}

	/**
	 * Test that AddLinkSchema requires anchor_text field.
	 */
	public function test_add_link_schema_requires_anchor_text(): void {
		$schema = AddLinkSchema::get_input_schema();

		$this->assertArrayHasKey( 'required', $schema );
		$this->assertContains( 'anchor_text', $schema['required'] );
	}

	// =========================================================================
	// BatchAddLinksSchema Tests
	// =========================================================================

	/**
	 * Test that BatchAddLinksSchema has maxLength constraint on anchor_text.
	 */
	public function test_batch_add_links_schema_has_anchor_text_max_length(): void {
		$schema = BatchAddLinksSchema::get_input_schema();

		$this->assertArrayHasKey( 'properties', $schema );
		$this->assertArrayHasKey( 'links', $schema['properties'] );
		$this->assertArrayHasKey( 'items', $schema['properties']['links'] );

		$link_item_schema = $schema['properties']['links']['items'];
		$this->assertArrayHasKey( 'properties', $link_item_schema );
		$this->assertArrayHasKey( 'anchor_text', $link_item_schema['properties'] );
		$this->assertArrayHasKey( 'maxLength', $link_item_schema['properties']['anchor_text'] );
		$this->assertEquals( 1000, $link_item_schema['properties']['anchor_text']['maxLength'] );
	}

	/**
	 * Test that BatchAddLinksSchema has maxItems constraint.
	 */
	public function test_batch_add_links_schema_has_max_items(): void {
		$schema = BatchAddLinksSchema::get_input_schema();

		$this->assertArrayHasKey( 'maxItems', $schema['properties']['links'] );
		$this->assertEquals( 50, $schema['properties']['links']['maxItems'] );
	}

	// =========================================================================
	// UpdateLinkSchema Tests
	// =========================================================================

	/**
	 * Test that UpdateLinkSchema has maxLength constraint on new_anchor_text.
	 */
	public function test_update_link_schema_has_new_anchor_text_max_length(): void {
		$schema = UpdateLinkSchema::get_input_schema();

		$this->assertArrayHasKey( 'properties', $schema );
		$this->assertArrayHasKey( 'new_anchor_text', $schema['properties'] );
		$this->assertArrayHasKey( 'maxLength', $schema['properties']['new_anchor_text'] );
		$this->assertEquals( 1000, $schema['properties']['new_anchor_text']['maxLength'] );
	}

	/**
	 * Test that UpdateLinkSchema has maxLength constraint on identifier anchor_text.
	 */
	public function test_update_link_schema_has_identifier_anchor_text_max_length(): void {
		$schema = UpdateLinkSchema::get_input_schema();

		$this->assertArrayHasKey( 'identifier', $schema['properties'] );
		$this->assertArrayHasKey( 'oneOf', $schema['properties']['identifier'] );

		// Find the anchor identifier option.
		$anchor_option = null;
		foreach ( $schema['properties']['identifier']['oneOf'] as $option ) {
			if ( isset( $option['properties']['by'] ) &&
				isset( $option['properties']['by']['const'] ) &&
				'anchor' === $option['properties']['by']['const'] ) {
				$anchor_option = $option;
				break;
			}
		}

		$this->assertNotNull( $anchor_option, 'Anchor identifier option not found' );
		$this->assertArrayHasKey( 'anchor_text', $anchor_option['properties'] );
		$this->assertArrayHasKey( 'maxLength', $anchor_option['properties']['anchor_text'] );
		$this->assertEquals( 1000, $anchor_option['properties']['anchor_text']['maxLength'] );
	}

	// =========================================================================
	// RemoveLinkSchema Tests
	// =========================================================================

	/**
	 * Test that RemoveLinkSchema has maxLength constraint on identifier anchor_text.
	 */
	public function test_remove_link_schema_has_identifier_anchor_text_max_length(): void {
		$schema = RemoveLinkSchema::get_input_schema();

		$this->assertArrayHasKey( 'identifier', $schema['properties'] );
		$this->assertArrayHasKey( 'oneOf', $schema['properties']['identifier'] );

		// Find the anchor identifier option.
		$anchor_option = null;
		foreach ( $schema['properties']['identifier']['oneOf'] as $option ) {
			if ( isset( $option['properties']['by'] ) &&
				isset( $option['properties']['by']['const'] ) &&
				'anchor' === $option['properties']['by']['const'] ) {
				$anchor_option = $option;
				break;
			}
		}

		$this->assertNotNull( $anchor_option, 'Anchor identifier option not found' );
		$this->assertArrayHasKey( 'anchor_text', $anchor_option['properties'] );
		$this->assertArrayHasKey( 'maxLength', $anchor_option['properties']['anchor_text'] );
		$this->assertEquals( 1000, $anchor_option['properties']['anchor_text']['maxLength'] );
	}

	/**
	 * Test that RemoveLinkSchema has valid action enum values.
	 */
	public function test_remove_link_schema_has_valid_action_enum(): void {
		$schema = RemoveLinkSchema::get_input_schema();

		$this->assertArrayHasKey( 'action', $schema['properties'] );
		$this->assertArrayHasKey( 'enum', $schema['properties']['action'] );
		$this->assertEquals( array( 'unlink', 'delete' ), $schema['properties']['action']['enum'] );
	}
}
