<?php
/**
 * Link parser service.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Services;

use DOMDocument;
use DOMXPath;
use DOMNode;
use DOMElement;

/**
 * Service for parsing and extracting links from HTML content.
 */
class LinkParserService {

	/**
	 * Site URL for internal link detection.
	 *
	 * @var string
	 */
	private string $site_url;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->site_url = trailingslashit( home_url() );
	}

	/**
	 * Extract all links from HTML content.
	 *
	 * @param string $content HTML content.
	 * @return array Array of link objects.
	 */
	public function extract_links( string $content ): array {
		if ( empty( trim( $content ) ) ) {
			return array();
		}

		$doc = $this->load_html( $content );
		if ( ! $doc ) {
			return array();
		}

		$xpath = new DOMXPath( $doc );
		$links = $xpath->query( '//a[@href]' );
		$result = array();
		$position = 0;

		foreach ( $links as $link ) {
			$position++;
			$href = $link->getAttribute( 'href' );

			$result[] = array(
				'position'    => $position,
				'anchor_text' => $link->textContent,
				'url'         => $href,
				'is_internal' => $this->is_internal_link( $href ),
				'attributes'  => $this->get_link_attributes( $link ),
			);
		}

		return $result;
	}

	/**
	 * Find occurrences of anchor text in content.
	 *
	 * @param string $content     HTML content.
	 * @param string $anchor_text Text to find.
	 * @return array Array of occurrences.
	 */
	public function find_anchor_occurrences( string $content, string $anchor_text ): array {
		if ( empty( trim( $content ) ) || empty( trim( $anchor_text ) ) ) {
			return array();
		}

		$doc = $this->load_html( $content );
		if ( ! $doc ) {
			return array();
		}

		$xpath = new DOMXPath( $doc );
		$occurrences = array();
		$position = 0;

		// Get all text nodes.
		$text_nodes = $xpath->query( '//text()' );

		foreach ( $text_nodes as $node ) {
			$text = $node->nodeValue;
			$offset = 0;

			// Find all occurrences within this text node (case-insensitive).
			while ( ( $pos = mb_stripos( $text, $anchor_text, $offset ) ) !== false ) {
				$position++;

				// Check if already linked (parent is <a>).
				$parent = $node->parentNode;
				$is_linked = $parent instanceof DOMElement && 'a' === strtolower( $parent->nodeName );
				$existing_url = $is_linked ? $parent->getAttribute( 'href' ) : null;

				$occurrences[] = array(
					'position'     => $position,
					'is_linked'    => $is_linked,
					'existing_url' => $existing_url,
					'node'         => $node,
					'char_offset'  => $pos,
				);

				$offset = $pos + mb_strlen( $anchor_text );
			}
		}

		return $occurrences;
	}

	/**
	 * Check if anchor text spans multiple HTML elements.
	 *
	 * @param string     $content     HTML content.
	 * @param string     $anchor_text Text to check.
	 * @param int|string $occurrence  Which occurrence (1-based) or "first"/"last"/"all".
	 * @return bool True if spans elements.
	 */
	public function anchor_spans_elements( string $content, string $anchor_text, $occurrence = 'first' ): bool {
		// Strip tags and check if the anchor text exists as a contiguous string.
		$plain_text = wp_strip_all_tags( $content );

		// If anchor text exists in plain text but we can't find it in a single text node,
		// it likely spans multiple elements.
		if ( mb_stripos( $plain_text, $anchor_text ) !== false ) {
			$occurrences = $this->find_anchor_occurrences( $content, $anchor_text );

			// Determine which occurrence to check.
			$target_indices = $this->get_target_indices( $occurrence, count( $occurrences ) );

			foreach ( $target_indices as $index ) {
				if ( isset( $occurrences[ $index ] ) ) {
					$occ = $occurrences[ $index ];
					$node_text = $occ['node']->nodeValue ?? '';
					$found_text = mb_substr( $node_text, $occ['char_offset'], mb_strlen( $anchor_text ) );

					// Check if the exact text is contained within a single text node.
					if ( mb_strtolower( $found_text ) !== mb_strtolower( $anchor_text ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Determine if a URL is internal.
	 *
	 * @param string $url URL to check.
	 * @return bool True if internal link.
	 */
	public function is_internal_link( string $url ): bool {
		// Handle relative URLs.
		if ( strpos( $url, '/' ) === 0 && strpos( $url, '//' ) !== 0 ) {
			return true;
		}

		// Parse the URL.
		$parsed = wp_parse_url( $url );
		if ( ! $parsed || empty( $parsed['host'] ) ) {
			return false;
		}

		// Get site host.
		$site_parsed = wp_parse_url( $this->site_url );
		$site_host = $site_parsed['host'] ?? '';

		// Compare hosts (case-insensitive).
		return strtolower( $parsed['host'] ) === strtolower( $site_host );
	}

	/**
	 * Get post ID from internal URL.
	 *
	 * @param string $url Internal URL.
	 * @return int|null Post ID or null if not found.
	 */
	public function get_post_id_from_url( string $url ): ?int {
		// Convert relative URL to absolute.
		if ( strpos( $url, '/' ) === 0 && strpos( $url, '//' ) !== 0 ) {
			$url = home_url( $url );
		}

		$post_id = url_to_postid( $url );

		return $post_id > 0 ? $post_id : null;
	}

	/**
	 * Find links by identifier.
	 *
	 * @param string $content    HTML content.
	 * @param array  $identifier Link identifier.
	 * @return array Matching links.
	 */
	public function find_links_by_identifier( string $content, array $identifier ): array {
		$links = $this->extract_links( $content );
		$matches = array();

		$by = $identifier['by'] ?? '';

		switch ( $by ) {
			case 'url':
				$target_url = $identifier['url'] ?? '';
				foreach ( $links as $link ) {
					if ( $link['url'] === $target_url ) {
						$matches[] = $link;
					}
				}
				break;

			case 'anchor':
				$anchor_text = $identifier['anchor_text'] ?? '';
				$occurrence = $identifier['occurrence'] ?? 1;
				$count = 0;
				foreach ( $links as $link ) {
					if ( mb_strtolower( $link['anchor_text'] ) === mb_strtolower( $anchor_text ) ) {
						$count++;
						if ( $count === $occurrence ) {
							$matches[] = $link;
							break;
						}
					}
				}
				break;

			case 'index':
				$index = $identifier['index'] ?? 0;
				if ( isset( $links[ $index - 1 ] ) ) {
					$matches[] = $links[ $index - 1 ];
				}
				break;
		}

		return $matches;
	}

	/**
	 * Load HTML into DOMDocument safely.
	 *
	 * @param string $html HTML content.
	 * @return DOMDocument|null
	 */
	public function load_html( string $html ): ?DOMDocument {
		if ( empty( trim( $html ) ) ) {
			return null;
		}

		$doc = new DOMDocument();
		$doc->encoding = 'UTF-8';

		// Wrap content with encoding declaration and container.
		$wrapped = '<?xml encoding="UTF-8"><div id="__ilapi_wrapper__">' . $html . '</div>';

		// Suppress warnings for malformed HTML.
		libxml_use_internal_errors( true );
		$result = $doc->loadHTML( $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		libxml_clear_errors();

		if ( ! $result ) {
			return null;
		}

		return $doc;
	}

	/**
	 * Extract inner HTML from loaded DOMDocument.
	 *
	 * @param DOMDocument $doc Document.
	 * @return string HTML content.
	 */
	public function get_inner_html( DOMDocument $doc ): string {
		$wrapper = $doc->getElementById( '__ilapi_wrapper__' );
		if ( ! $wrapper ) {
			return '';
		}

		$html = '';
		foreach ( $wrapper->childNodes as $child ) {
			$html .= $doc->saveHTML( $child );
		}

		return $html;
	}

	/**
	 * Get all attributes from a link element.
	 *
	 * @param DOMElement $link Link element.
	 * @return array Attributes as key-value pairs.
	 */
	private function get_link_attributes( DOMElement $link ): array {
		$attributes = array();

		foreach ( $link->attributes as $attr ) {
			if ( 'href' !== $attr->nodeName ) {
				$attributes[ $attr->nodeName ] = $attr->nodeValue;
			}
		}

		return $attributes;
	}

	/**
	 * Get target indices based on occurrence parameter.
	 *
	 * @param int|string $occurrence Occurrence parameter.
	 * @param int        $total      Total occurrences.
	 * @return array Array of 0-based indices.
	 */
	private function get_target_indices( $occurrence, int $total ): array {
		if ( $total === 0 ) {
			return array();
		}

		if ( 'first' === $occurrence ) {
			return array( 0 );
		}

		if ( 'last' === $occurrence ) {
			return array( $total - 1 );
		}

		if ( 'all' === $occurrence ) {
			return range( 0, $total - 1 );
		}

		if ( is_numeric( $occurrence ) ) {
			$index = (int) $occurrence - 1; // Convert to 0-based.
			if ( $index >= 0 && $index < $total ) {
				return array( $index );
			}
		}

		return array();
	}
}
