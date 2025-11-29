<?php
/**
 * Tests for AbilityRegistrar permission callbacks.
 *
 * @package InternalLinksAPI\Tests\Unit
 */

namespace InternalLinksAPI\Tests\Unit;

use InternalLinksAPI\AbilityRegistrar;
use InternalLinksAPI\Settings;
use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery;

/**
 * AbilityRegistrar test case.
 */
class AbilityRegistrarTest extends TestCase {

	/**
	 * Registrar under test.
	 *
	 * @var AbilityRegistrar
	 */
	private AbilityRegistrar $registrar;

	/**
	 * Mock settings.
	 *
	 * @var Settings|Mockery\MockInterface
	 */
	private $settings;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->settings = Mockery::mock( Settings::class );

		// Mock WordPress hooks to prevent actual registration.
		Functions\when( 'add_action' )->justReturn( true );

		$this->registrar = new AbilityRegistrar( $this->settings, array() );
	}

	/**
	 * Tear down test fixtures.
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		Mockery::close();
		parent::tearDown();
	}

	// =========================================================================
	// Permission Callback Tests
	// =========================================================================

	/**
	 * Test that check_read_post_permission returns false without post_id.
	 */
	public function test_check_read_post_permission_returns_false_without_post_id(): void {
		// Should return false when no post_id is provided.
		$result = $this->registrar->check_read_post_permission( array() );
		$this->assertFalse( $result );
	}

	/**
	 * Test that check_read_post_permission returns false with post_id of 0.
	 */
	public function test_check_read_post_permission_returns_false_with_zero_post_id(): void {
		$result = $this->registrar->check_read_post_permission( array( 'post_id' => 0 ) );
		$this->assertFalse( $result );
	}

	/**
	 * Test that check_read_post_permission returns true with valid post_id and permission.
	 */
	public function test_check_read_post_permission_returns_true_with_valid_post_id(): void {
		Functions\when( 'current_user_can' )->alias( function ( $cap, $post_id = null ) {
			return $cap === 'read_post' && $post_id === 123;
		} );

		$result = $this->registrar->check_read_post_permission( array( 'post_id' => 123 ) );
		$this->assertTrue( $result );
	}

	/**
	 * Test that check_read_post_permission returns false when user lacks permission.
	 */
	public function test_check_read_post_permission_returns_false_when_user_cannot_read(): void {
		Functions\when( 'current_user_can' )->justReturn( false );

		$result = $this->registrar->check_read_post_permission( array( 'post_id' => 123 ) );
		$this->assertFalse( $result );
	}

	/**
	 * Test that check_edit_post_permission returns false without source_post_id.
	 */
	public function test_check_edit_post_permission_returns_false_without_post_id(): void {
		$result = $this->registrar->check_edit_post_permission( array() );
		$this->assertFalse( $result );
	}

	/**
	 * Test that check_edit_post_permission returns false with source_post_id of 0.
	 */
	public function test_check_edit_post_permission_returns_false_with_zero_post_id(): void {
		$result = $this->registrar->check_edit_post_permission( array( 'source_post_id' => 0 ) );
		$this->assertFalse( $result );
	}

	/**
	 * Test that check_edit_post_permission returns true with valid permission.
	 */
	public function test_check_edit_post_permission_returns_true_with_valid_permission(): void {
		Functions\when( 'current_user_can' )->alias( function ( $cap, $post_id = null ) {
			return $cap === 'edit_post' && $post_id === 456;
		} );

		$result = $this->registrar->check_edit_post_permission( array( 'source_post_id' => 456 ) );
		$this->assertTrue( $result );
	}

	/**
	 * Test that check_edit_post_permission returns false when user lacks permission.
	 */
	public function test_check_edit_post_permission_returns_false_when_user_cannot_edit(): void {
		Functions\when( 'current_user_can' )->justReturn( false );

		$result = $this->registrar->check_edit_post_permission( array( 'source_post_id' => 456 ) );
		$this->assertFalse( $result );
	}

	/**
	 * Test that check_read_permission allows any authenticated user.
	 */
	public function test_check_read_permission_allows_authenticated_user(): void {
		Functions\when( 'current_user_can' )->alias( function ( $cap ) {
			return $cap === 'read';
		} );

		$result = $this->registrar->check_read_permission( array() );
		$this->assertTrue( $result );
	}
}
