<?php
/**
 * Link modifier service.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Services;

use DOMDocument;
use DOMElement;
use DOMText;

/**
 * Service for modifying links in HTML content.
 */
class LinkModifierService {

	/**
	 * Editor detector service.
	 *
	 * @var EditorDetectorService
	 */
	private EditorDetectorService $editor_detector;

	/**
	 * Link parser service.
	 *
	 * @var LinkParserService
	 */
	private LinkParserService $link_parser;

	/**
	 * Constructor.
	 *
	 * @param EditorDetectorService $editor_detector Editor detector service.
	 * @param LinkParserService     $link_parser     Link parser service.
	 */
	public function __construct( EditorDetectorService $editor_detector, LinkParserService $link_parser ) {
		$this->editor_detector = $editor_detector;
		$this->link_parser = $link_parser;
	}

	/**
	 * Add a link to anchor text in content.
	 *
	 * @param string     $content     HTML content.
	 * @param string     $anchor_text Text to link.
	 * @param string     $url         Target URL.
	 * @param array      $attributes  Link attributes.
	 * @param int|string $occurrence  Which occurrence: "first", "last", "all", or integer.
	 * @param string     $if_exists   Behavior if already linked: "skip" or "replace".
	 * @param string     $editor_type Editor type: "gutenberg" or "classic".
	 * @return array Result with 'content', 'links_added', 'skipped'.
	 */
	public function add_link(
		string $content,
		string $anchor_text,
		string $url,
		array $attributes = array(),
		$occurrence = 'first',
		string $if_exists = 'skip',
		string $editor_type = 'classic'
	): array {
		/**
		 * Filter the link attributes before insertion.
		 *
		 * @param array  $attributes  Link attributes.
		 * @param string $anchor_text Anchor text being linked.
		 * @param string $url         Target URL.
		 */
		$attributes = apply_filters( 'internal_links_api_link_attributes', $attributes, $anchor_text, $url );

		if ( 'gutenberg' === $editor_type ) {
			return $this->add_link_gutenberg( $content, $anchor_text, $url, $attributes, $occurrence, $if_exists );
		}

		return $this->add_link_classic( $content, $anchor_text, $url, $attributes, $occurrence, $if_exists );
	}

	/**
	 * Add link in Classic Editor content.
	 *
	 * @param string     $content     HTML content.
	 * @param string     $anchor_text Text to link.
	 * @param string     $url         Target URL.
	 * @param array      $attributes  Link attributes.
	 * @param int|string $occurrence  Which occurrence.
	 * @param string     $if_exists   Skip or replace.
	 * @return array Result.
	 */
	private function add_link_classic(
		string $content,
		string $anchor_text,
		string $url,
		array $attributes,
		$occurrence,
		string $if_exists
	): array {
		$occurrences = $this->link_parser->find_anchor_occurrences( $content, $anchor_text );

		if ( empty( $occurrences ) ) {
			return array(
				'content'     => $content,
				'links_added' => 0,
				'skipped'     => array(),
			);
		}

		$target_indices = $this->get_target_indices( $occurrence, count( $occurrences ) );
		$links_added = 0;
		$skipped = array();

		// Process in reverse order to maintain positions.
		$target_indices = array_reverse( $target_indices );

		foreach ( $target_indices as $index ) {
			if ( ! isset( $occurrences[ $index ] ) ) {
				continue;
			}

			$occ = $occurrences[ $index ];

			// Handle existing links.
			if ( $occ['is_linked'] ) {
				if ( 'skip' === $if_exists ) {
					$skipped[] = array(
						'occurrence'   => $index + 1,
						'reason'       => 'already_linked',
						'existing_url' => $occ['existing_url'],
					);
					continue;
				}
				// For 'replace', we'll process it below.
			}

			// Find and replace using string operations.
			$content = $this->replace_text_with_link(
				$content,
				$anchor_text,
				$url,
				$attributes,
				$index,
				$occ['is_linked'],
				$occurrences
			);

			$links_added++;
		}

		return array(
			'content'     => $content,
			'links_added' => $links_added,
			'skipped'     => $skipped,
		);
	}

	/**
	 * Add link in Gutenberg content.
	 *
	 * @param string     $content     Block content.
	 * @param string     $anchor_text Text to link.
	 * @param string     $url         Target URL.
	 * @param array      $attributes  Link attributes.
	 * @param int|string $occurrence  Which occurrence.
	 * @param string     $if_exists   Skip or replace.
	 * @return array Result.
	 */
	private function add_link_gutenberg(
		string $content,
		string $anchor_text,
		string $url,
		array $attributes,
		$occurrence,
		string $if_exists
	): array {
		$blocks = parse_blocks( $content );
		$total_links_added = 0;
		$total_skipped = array();
		$global_occurrence = 0;

		$target_indices = null; // Will be calculated on first pass.

		$modified_blocks = $this->process_blocks_for_linking(
			$blocks,
			$anchor_text,
			$url,
			$attributes,
			$occurrence,
			$if_exists,
			$global_occurrence,
			$total_links_added,
			$total_skipped,
			$target_indices
		);

		return array(
			'content'     => serialize_blocks( $modified_blocks ),
			'links_added' => $total_links_added,
			'skipped'     => $total_skipped,
		);
	}

	/**
	 * Process blocks recursively for linking.
	 *
	 * @param array       $blocks           Blocks to process.
	 * @param string      $anchor_text      Text to link.
	 * @param string      $url              Target URL.
	 * @param array       $attributes       Link attributes.
	 * @param int|string  $occurrence       Which occurrence.
	 * @param string      $if_exists        Skip or replace.
	 * @param int         $global_occurrence Global occurrence counter.
	 * @param int         $total_links_added Total links added counter.
	 * @param array       $total_skipped    Skipped array.
	 * @param array|null  $target_indices   Target indices.
	 * @return array Modified blocks.
	 */
	private function process_blocks_for_linking(
		array $blocks,
		string $anchor_text,
		string $url,
		array $attributes,
		$occurrence,
		string $if_exists,
		int &$global_occurrence,
		int &$total_links_added,
		array &$total_skipped,
		?array &$target_indices
	): array {
		$modified_blocks = array();

		foreach ( $blocks as $block ) {
			if ( empty( $block['blockName'] ) ) {
				$modified_blocks[] = $block;
				continue;
			}

			// Process innerHTML if present.
			if ( ! empty( $block['innerHTML'] ) ) {
				$inner_html = $block['innerHTML'];
				$occurrences = $this->link_parser->find_anchor_occurrences( $inner_html, $anchor_text );

				if ( ! empty( $occurrences ) && null === $target_indices ) {
					// First pass - need to count total occurrences across all blocks.
					$total_occurrences = $this->count_total_occurrences( $blocks, $anchor_text );
					$target_indices = $this->get_target_indices( $occurrence, $total_occurrences );
				}

				foreach ( $occurrences as $occ ) {
					$global_occurrence++;
					$current_index = $global_occurrence - 1;

					if ( null !== $target_indices && in_array( $current_index, $target_indices, true ) ) {
						if ( $occ['is_linked'] && 'skip' === $if_exists ) {
							$total_skipped[] = array(
								'occurrence'   => $global_occurrence,
								'reason'       => 'already_linked',
								'existing_url' => $occ['existing_url'],
							);
						} else {
							$result = $this->add_link_classic(
								$inner_html,
								$anchor_text,
								$url,
								$attributes,
								1, // Process first occurrence in this block segment.
								$if_exists
							);
							$inner_html = $result['content'];
							$total_links_added += $result['links_added'];
							$total_skipped = array_merge( $total_skipped, $result['skipped'] );
						}
					}
				}

				$block['innerHTML'] = $inner_html;
			}

			// Process innerContent.
			if ( ! empty( $block['innerContent'] ) ) {
				$block['innerContent'] = array_map(
					function ( $item ) use ( $anchor_text, $url, $attributes, $if_exists ) {
						if ( is_string( $item ) && ! empty( $item ) ) {
							$result = $this->add_link_classic(
								$item,
								$anchor_text,
								$url,
								$attributes,
								'all',
								$if_exists
							);
							return $result['content'];
						}
						return $item;
					},
					$block['innerContent']
				);
			}

			// Process inner blocks recursively.
			if ( ! empty( $block['innerBlocks'] ) ) {
				$block['innerBlocks'] = $this->process_blocks_for_linking(
					$block['innerBlocks'],
					$anchor_text,
					$url,
					$attributes,
					$occurrence,
					$if_exists,
					$global_occurrence,
					$total_links_added,
					$total_skipped,
					$target_indices
				);
			}

			$modified_blocks[] = $block;
		}

		return $modified_blocks;
	}

	/**
	 * Update an existing link.
	 *
	 * @param string      $content          HTML content.
	 * @param array       $identifier       Link identifier.
	 * @param int|null    $new_target_id    New target post ID.
	 * @param string|null $new_anchor_text  New anchor text.
	 * @param array|null  $new_attributes   New attributes.
	 * @param bool        $merge_attributes Merge or replace attributes.
	 * @param string      $editor_type      Editor type.
	 * @return array Result with 'content', 'links_updated', 'changes'.
	 */
	public function update_link(
		string $content,
		array $identifier,
		?int $new_target_id = null,
		?string $new_anchor_text = null,
		?array $new_attributes = null,
		bool $merge_attributes = false,
		string $editor_type = 'classic'
	): array {
		$doc = $this->link_parser->load_html( $content );
		if ( ! $doc ) {
			return array(
				'content'       => $content,
				'links_updated' => 0,
				'changes'       => array(),
			);
		}

		$matching_links = $this->link_parser->find_links_by_identifier( $content, $identifier );

		if ( empty( $matching_links ) ) {
			return array(
				'content'       => $content,
				'links_updated' => 0,
				'changes'       => array(),
			);
		}

		$changes = array(
			'target'     => null,
			'anchor'     => null,
			'attributes' => array( 'added' => array(), 'removed' => array() ),
		);

		$new_url = null;
		if ( $new_target_id ) {
			$new_url = get_permalink( $new_target_id );
		}

		// Apply changes using string replacement.
		$links_updated = 0;
		foreach ( $matching_links as $link ) {
			$old_url = $link['url'];
			$old_anchor = $link['anchor_text'];
			$old_attrs = $link['attributes'];

			// Build old link tag.
			$old_link_pattern = $this->build_link_regex( $old_url, $old_anchor );

			// Build new link tag.
			$final_url = $new_url ?? $old_url;
			$final_anchor = $new_anchor_text ?? $old_anchor;

			if ( null !== $new_attributes ) {
				$final_attrs = $merge_attributes
					? array_merge( $old_attrs, $new_attributes )
					: $new_attributes;

				// Track changes.
				$changes['attributes']['added'] = array_diff_key( $final_attrs, $old_attrs );
				$changes['attributes']['removed'] = array_keys( array_diff_key( $old_attrs, $final_attrs ) );
			} else {
				$final_attrs = $old_attrs;
			}

			$new_link = $this->build_link_tag( $final_url, $final_anchor, $final_attrs );

			// Perform replacement.
			$content = preg_replace( $old_link_pattern, $new_link, $content, 1, $count );

			if ( $count > 0 ) {
				$links_updated++;

				if ( $new_url && $new_url !== $old_url ) {
					$changes['target'] = array(
						'old' => $old_url,
						'new' => $new_url,
					);
				}

				if ( $new_anchor_text && $new_anchor_text !== $old_anchor ) {
					$changes['anchor'] = array(
						'old' => $old_anchor,
						'new' => $new_anchor_text,
					);
				}
			}
		}

		return array(
			'content'       => $content,
			'links_updated' => $links_updated,
			'changes'       => $changes,
		);
	}

	/**
	 * Remove a link from content.
	 *
	 * @param string $content     HTML content.
	 * @param array  $identifier  Link identifier.
	 * @param string $action      "unlink" or "delete".
	 * @param string $editor_type Editor type.
	 * @return array Result with 'content', 'links_removed', 'removed_links'.
	 */
	public function remove_link(
		string $content,
		array $identifier,
		string $action = 'unlink',
		string $editor_type = 'classic'
	): array {
		$matching_links = $this->link_parser->find_links_by_identifier( $content, $identifier );

		if ( empty( $matching_links ) ) {
			return array(
				'content'       => $content,
				'links_removed' => 0,
				'removed_links' => array(),
			);
		}

		$removed_links = array();
		$links_removed = 0;

		foreach ( $matching_links as $link ) {
			$old_link_pattern = $this->build_link_regex( $link['url'], $link['anchor_text'] );

			$replacement = 'unlink' === $action ? $link['anchor_text'] : '';

			$content = preg_replace( $old_link_pattern, $replacement, $content, 1, $count );

			if ( $count > 0 ) {
				$links_removed++;
				$removed_links[] = array(
					'anchor_text' => $link['anchor_text'],
					'target_url'  => $link['url'],
					'position'    => $link['position'],
				);
			}
		}

		return array(
			'content'       => $content,
			'links_removed' => $links_removed,
			'removed_links' => $removed_links,
		);
	}

	/**
	 * Replace text with a link.
	 *
	 * @param string $content     HTML content.
	 * @param string $anchor_text Text to link.
	 * @param string $url         Target URL.
	 * @param array  $attributes  Link attributes.
	 * @param int    $target_index Target occurrence index (0-based).
	 * @param bool   $is_linked   Whether text is already linked.
	 * @param array  $occurrences All occurrences.
	 * @return string Modified content.
	 */
	private function replace_text_with_link(
		string $content,
		string $anchor_text,
		string $url,
		array $attributes,
		int $target_index,
		bool $is_linked,
		array $occurrences
	): string {
		if ( $is_linked ) {
			// Replace existing link.
			$pattern = $this->build_link_regex( $occurrences[ $target_index ]['existing_url'], $anchor_text );
			$replacement = $this->build_link_tag( $url, $anchor_text, $attributes );
			return preg_replace( $pattern, $replacement, $content, 1 );
		}

		// Find the Nth occurrence of the anchor text (case-insensitive).
		$offset = 0;
		$current_index = 0;

		while ( ( $pos = mb_stripos( $content, $anchor_text, $offset ) ) !== false ) {
			if ( $current_index === $target_index ) {
				// Check if this position is already inside a link tag.
				$before = mb_substr( $content, 0, $pos );
				$open_a = mb_substr_count( mb_strtolower( $before ), '<a ' ) + mb_substr_count( mb_strtolower( $before ), '<a>' );
				$close_a = mb_substr_count( mb_strtolower( $before ), '</a>' );

				if ( $open_a > $close_a ) {
					// Inside a link, skip this occurrence.
					$offset = $pos + mb_strlen( $anchor_text );
					continue;
				}

				// Get the actual text at this position (preserve case).
				$actual_text = mb_substr( $content, $pos, mb_strlen( $anchor_text ) );

				// Build the link.
				$link = $this->build_link_tag( $url, $actual_text, $attributes );

				// Replace.
				return mb_substr( $content, 0, $pos ) . $link . mb_substr( $content, $pos + mb_strlen( $anchor_text ) );
			}

			$offset = $pos + mb_strlen( $anchor_text );
			$current_index++;
		}

		return $content;
	}

	/**
	 * Build a link tag.
	 *
	 * @param string $url        URL.
	 * @param string $text       Link text.
	 * @param array  $attributes Additional attributes.
	 * @return string HTML link tag.
	 */
	private function build_link_tag( string $url, string $text, array $attributes = array() ): string {
		$attr_string = '';
		foreach ( $attributes as $name => $value ) {
			$attr_string .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( $value ) );
		}

		return sprintf(
			'<a href="%s"%s>%s</a>',
			esc_url( $url ),
			$attr_string,
			$text // Preserve original text (don't escape, it might contain entities).
		);
	}

	/**
	 * Build a regex pattern to match a link.
	 *
	 * @param string $url  URL to match.
	 * @param string $text Link text to match.
	 * @return string Regex pattern.
	 */
	private function build_link_regex( string $url, string $text ): string {
		$url_pattern = preg_quote( $url, '/' );
		$text_pattern = preg_quote( $text, '/' );

		return '/<a\s+[^>]*href=["\']' . $url_pattern . '["\'][^>]*>' . $text_pattern . '<\/a>/i';
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

	/**
	 * Count total occurrences across all blocks.
	 *
	 * @param array  $blocks      Blocks to search.
	 * @param string $anchor_text Text to find.
	 * @return int Total occurrences.
	 */
	private function count_total_occurrences( array $blocks, string $anchor_text ): int {
		$count = 0;

		foreach ( $blocks as $block ) {
			if ( ! empty( $block['innerHTML'] ) ) {
				$occurrences = $this->link_parser->find_anchor_occurrences( $block['innerHTML'], $anchor_text );
				$count += count( $occurrences );
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$count += $this->count_total_occurrences( $block['innerBlocks'], $anchor_text );
			}
		}

		return $count;
	}
}
