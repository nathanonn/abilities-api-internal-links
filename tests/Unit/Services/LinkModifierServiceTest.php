<?php
/**
 * Tests for LinkModifierService security methods.
 *
 * @package InternalLinksAPI\Tests\Unit\Services
 */

namespace InternalLinksAPI\Tests\Unit\Services;

use InternalLinksAPI\Services\LinkModifierService;
use InternalLinksAPI\Services\EditorDetectorService;
use InternalLinksAPI\Services\LinkParserService;
use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery;

/**
 * LinkModifierService test case.
 */
class LinkModifierServiceTest extends TestCase {

	/**
	 * Service under test.
	 *
	 * @var LinkModifierService
	 */
	private LinkModifierService $service;

	/**
	 * Mock editor detector.
	 *
	 * @var EditorDetectorService|Mockery\MockInterface
	 */
	private $editor_detector;

	/**
	 * Mock link parser.
	 *
	 * @var LinkParserService|Mockery\MockInterface
	 */
	private $link_parser;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		$this->editor_detector = Mockery::mock( EditorDetectorService::class );
		$this->link_parser = Mockery::mock( LinkParserService::class );
		$this->service = new LinkModifierService( $this->editor_detector, $this->link_parser );
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
	// URL Scheme Validation Tests (is_safe_url)
	// =========================================================================

	/**
	 * Test that HTTP URLs are allowed.
	 */
	public function test_is_safe_url_allows_http_urls(): void {
		Functions\when( 'wp_parse_url' )->alias( function ( $url ) {
			return parse_url( $url );
		} );

		$this->assertTrue( $this->service->is_safe_url( 'http://example.com/page' ) );
	}

	/**
	 * Test that HTTPS URLs are allowed.
	 */
	public function test_is_safe_url_allows_https_urls(): void {
		Functions\when( 'wp_parse_url' )->alias( function ( $url ) {
			return parse_url( $url );
		} );

		$this->assertTrue( $this->service->is_safe_url( 'https://example.com/page' ) );
	}

	/**
	 * Test that relative URLs are allowed.
	 */
	public function test_is_safe_url_allows_relative_urls(): void {
		Functions\when( 'wp_parse_url' )->alias( function ( $url ) {
			return parse_url( $url );
		} );

		$this->assertTrue( $this->service->is_safe_url( '/path/to/page' ) );
	}

	/**
	 * Test that javascript: URLs are blocked.
	 */
	public function test_is_safe_url_blocks_javascript_urls(): void {
		Functions\when( 'wp_parse_url' )->alias( function ( $url ) {
			return parse_url( $url );
		} );

		$this->assertFalse( $this->service->is_safe_url( 'javascript:alert("xss")' ) );
	}

	/**
	 * Test that data: URLs are blocked.
	 */
	public function test_is_safe_url_blocks_data_urls(): void {
		Functions\when( 'wp_parse_url' )->alias( function ( $url ) {
			return parse_url( $url );
		} );

		$this->assertFalse( $this->service->is_safe_url( 'data:text/html,<script>alert("xss")</script>' ) );
	}

	/**
	 * Test that vbscript: URLs are blocked.
	 */
	public function test_is_safe_url_blocks_vbscript_urls(): void {
		Functions\when( 'wp_parse_url' )->alias( function ( $url ) {
			return parse_url( $url );
		} );

		$this->assertFalse( $this->service->is_safe_url( 'vbscript:msgbox("xss")' ) );
	}

	/**
	 * Test that empty URLs are blocked.
	 */
	public function test_is_safe_url_blocks_empty_urls(): void {
		$this->assertFalse( $this->service->is_safe_url( '' ) );
		$this->assertFalse( $this->service->is_safe_url( '   ' ) );
	}

	// =========================================================================
	// Attribute Filtering Tests (via build_link_tag using reflection)
	// =========================================================================

	/**
	 * Helper to call private build_link_tag method.
	 *
	 * @param string $url        URL.
	 * @param string $text       Link text.
	 * @param array  $attributes Attributes.
	 * @return string HTML link tag.
	 */
	private function callBuildLinkTag( string $url, string $text, array $attributes = array() ): string {
		$reflection = new \ReflectionClass( $this->service );
		$method = $reflection->getMethod( 'build_link_tag' );
		$method->setAccessible( true );

		return $method->invoke( $this->service, $url, $text, $attributes );
	}

	/**
	 * Set up common mocks for build_link_tag tests.
	 */
	private function setupBuildLinkTagMocks(): void {
		Functions\when( 'esc_url' )->alias( function ( $url ) {
			return htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
		} );

		Functions\when( 'esc_attr' )->alias( function ( $attr ) {
			return htmlspecialchars( $attr, ENT_QUOTES, 'UTF-8' );
		} );

		Functions\when( 'wp_kses' )->alias( function ( $text, $allowed_html ) {
			// Simple simulation: strip script tags, keep em/strong.
			$text = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $text );
			$text = preg_replace( '/<img[^>]+onerror[^>]*>/i', '', $text );
			return $text;
		} );
	}

	/**
	 * Test that safe attributes are allowed.
	 */
	public function test_build_link_tag_allows_safe_attributes(): void {
		$this->setupBuildLinkTagMocks();

		$result = $this->callBuildLinkTag(
			'https://example.com',
			'Click here',
			array(
				'rel'    => 'noopener',
				'target' => '_blank',
				'title'  => 'Example',
				'class'  => 'my-link',
			)
		);

		$this->assertStringContainsString( 'rel="noopener"', $result );
		$this->assertStringContainsString( 'target="_blank"', $result );
		$this->assertStringContainsString( 'title="Example"', $result );
		$this->assertStringContainsString( 'class="my-link"', $result );
	}

	/**
	 * Test that onclick attribute is stripped.
	 */
	public function test_build_link_tag_strips_onclick_attribute(): void {
		$this->setupBuildLinkTagMocks();

		$result = $this->callBuildLinkTag(
			'https://example.com',
			'Click here',
			array(
				'onclick' => 'alert("xss")',
				'rel'     => 'noopener',
			)
		);

		$this->assertStringNotContainsString( 'onclick', $result );
		$this->assertStringContainsString( 'rel="noopener"', $result );
	}

	/**
	 * Test that onerror attribute is stripped.
	 */
	public function test_build_link_tag_strips_onerror_attribute(): void {
		$this->setupBuildLinkTagMocks();

		$result = $this->callBuildLinkTag(
			'https://example.com',
			'Click here',
			array( 'onerror' => 'alert("xss")' )
		);

		$this->assertStringNotContainsString( 'onerror', $result );
	}

	/**
	 * Test that onmouseover attribute is stripped.
	 */
	public function test_build_link_tag_strips_onmouseover_attribute(): void {
		$this->setupBuildLinkTagMocks();

		$result = $this->callBuildLinkTag(
			'https://example.com',
			'Click here',
			array( 'onmouseover' => 'alert("xss")' )
		);

		$this->assertStringNotContainsString( 'onmouseover', $result );
	}

	/**
	 * Test that data-* attributes are allowed.
	 */
	public function test_build_link_tag_allows_data_attributes(): void {
		$this->setupBuildLinkTagMocks();

		$result = $this->callBuildLinkTag(
			'https://example.com',
			'Click here',
			array(
				'data-id'     => '123',
				'data-action' => 'track',
			)
		);

		$this->assertStringContainsString( 'data-id="123"', $result );
		$this->assertStringContainsString( 'data-action="track"', $result );
	}

	/**
	 * Test that aria-* attributes are allowed.
	 */
	public function test_build_link_tag_allows_aria_attributes(): void {
		$this->setupBuildLinkTagMocks();

		$result = $this->callBuildLinkTag(
			'https://example.com',
			'Click here',
			array(
				'aria-label'       => 'Click for more',
				'aria-describedby' => 'desc-123',
			)
		);

		$this->assertStringContainsString( 'aria-label="Click for more"', $result );
		$this->assertStringContainsString( 'aria-describedby="desc-123"', $result );
	}

	// =========================================================================
	// Anchor Text Sanitization Tests (wp_kses in build_link_tag)
	// =========================================================================

	/**
	 * Test that em tags are allowed in anchor text.
	 */
	public function test_build_link_tag_allows_em_tags(): void {
		Functions\when( 'esc_url' )->alias( function ( $url ) {
			return htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
		} );

		Functions\when( 'esc_attr' )->alias( function ( $attr ) {
			return htmlspecialchars( $attr, ENT_QUOTES, 'UTF-8' );
		} );

		Functions\when( 'wp_kses' )->alias( function ( $text, $allowed_html ) {
			// Simulate allowing em tags.
			return $text;
		} );

		$result = $this->callBuildLinkTag(
			'https://example.com',
			'<em>Emphasized</em> text',
			array()
		);

		$this->assertStringContainsString( '<em>Emphasized</em>', $result );
	}

	/**
	 * Test that strong tags are allowed in anchor text.
	 */
	public function test_build_link_tag_allows_strong_tags(): void {
		Functions\when( 'esc_url' )->alias( function ( $url ) {
			return htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
		} );

		Functions\when( 'esc_attr' )->alias( function ( $attr ) {
			return htmlspecialchars( $attr, ENT_QUOTES, 'UTF-8' );
		} );

		Functions\when( 'wp_kses' )->alias( function ( $text, $allowed_html ) {
			// Simulate allowing strong tags.
			return $text;
		} );

		$result = $this->callBuildLinkTag(
			'https://example.com',
			'<strong>Bold</strong> text',
			array()
		);

		$this->assertStringContainsString( '<strong>Bold</strong>', $result );
	}

	/**
	 * Test that script tags are stripped from anchor text.
	 */
	public function test_build_link_tag_strips_script_tags(): void {
		Functions\when( 'esc_url' )->alias( function ( $url ) {
			return htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
		} );

		Functions\when( 'esc_attr' )->alias( function ( $attr ) {
			return htmlspecialchars( $attr, ENT_QUOTES, 'UTF-8' );
		} );

		Functions\when( 'wp_kses' )->alias( function ( $text, $allowed_html ) {
			// Strip script tags.
			return preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $text );
		} );

		$result = $this->callBuildLinkTag(
			'https://example.com',
			'Click <script>alert("xss")</script>here',
			array()
		);

		$this->assertStringNotContainsString( '<script>', $result );
		$this->assertStringNotContainsString( 'alert', $result );
	}

	/**
	 * Test that img with onerror is stripped from anchor text.
	 */
	public function test_build_link_tag_strips_img_onerror(): void {
		Functions\when( 'esc_url' )->alias( function ( $url ) {
			return htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
		} );

		Functions\when( 'esc_attr' )->alias( function ( $attr ) {
			return htmlspecialchars( $attr, ENT_QUOTES, 'UTF-8' );
		} );

		Functions\when( 'wp_kses' )->alias( function ( $text, $allowed_html ) {
			// Strip img tags with onerror.
			return preg_replace( '/<img[^>]+onerror[^>]*>/i', '', $text );
		} );

		$result = $this->callBuildLinkTag(
			'https://example.com',
			'Click <img src=x onerror="alert(1)">here',
			array()
		);

		$this->assertStringNotContainsString( 'onerror', $result );
		$this->assertStringNotContainsString( '<img', $result );
	}

	// =========================================================================
	// Normal Operation Tests
	// =========================================================================

	/**
	 * Test that add_link creates a valid link.
	 */
	public function test_add_link_creates_valid_link(): void {
		$content = '<p>This is some text with anchor here.</p>';
		$anchor_text = 'anchor';
		$url = 'https://example.com/target';

		$this->link_parser->shouldReceive( 'find_anchor_occurrences' )
			->with( $content, $anchor_text )
			->andReturn( array(
				array(
					'position'     => 25,
					'is_linked'    => false,
					'existing_url' => null,
				),
			) );

		// apply_filters returns the first argument (attributes array) after filter name.
		Functions\when( 'apply_filters' )->alias( function ( $filter_name, $value ) {
			return $value;
		} );
		Functions\when( 'esc_url' )->returnArg( 1 );
		Functions\when( 'esc_attr' )->returnArg( 1 );
		Functions\when( 'wp_kses' )->returnArg( 1 );

		$result = $this->service->add_link( $content, $anchor_text, $url, array(), 'first', 'skip', 'classic' );

		$this->assertArrayHasKey( 'content', $result );
		$this->assertArrayHasKey( 'links_added', $result );
		$this->assertEquals( 1, $result['links_added'] );
		$this->assertStringContainsString( 'href="https://example.com/target"', $result['content'] );
	}

	/**
	 * Test that remove_link keeps text when unlinking.
	 */
	public function test_remove_link_removes_link_keeps_text(): void {
		$content = '<p>Click <a href="https://example.com">here</a> for more.</p>';
		$identifier = array(
			'by'  => 'url',
			'url' => 'https://example.com',
		);

		$this->link_parser->shouldReceive( 'find_links_by_identifier' )
			->with( $content, $identifier )
			->andReturn( array(
				array(
					'url'         => 'https://example.com',
					'anchor_text' => 'here',
					'attributes'  => array(),
					'position'    => 0,
				),
			) );

		$result = $this->service->remove_link( $content, $identifier, 'unlink', 'classic' );

		$this->assertArrayHasKey( 'content', $result );
		$this->assertArrayHasKey( 'links_removed', $result );
		$this->assertEquals( 1, $result['links_removed'] );
		$this->assertStringContainsString( 'here', $result['content'] );
		$this->assertStringNotContainsString( '<a href', $result['content'] );
	}
}
