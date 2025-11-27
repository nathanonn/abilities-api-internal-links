<?php
/**
 * Editor detector service.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Services;

use WP_Post;

/**
 * Service for detecting whether content uses Gutenberg or Classic Editor.
 */
class EditorDetectorService {

	/**
	 * Detect editor type from content.
	 *
	 * @param string $content Post content.
	 * @return string "gutenberg" or "classic".
	 */
	public function detect( string $content ): string {
		return $this->has_blocks( $content ) ? 'gutenberg' : 'classic';
	}

	/**
	 * Check if content has block markers.
	 *
	 * @param string $content Post content.
	 * @return bool True if Gutenberg blocks detected.
	 */
	public function has_blocks( string $content ): bool {
		// Check for block comment markers.
		return (bool) preg_match( '/<!-- wp:[a-z][a-z0-9-]*\/[a-z][a-z0-9-]* |<!-- wp:[a-z][a-z0-9-]* /', $content );
	}

	/**
	 * Get editor type for a post.
	 *
	 * @param WP_Post|int $post Post object or ID.
	 * @return string "gutenberg" or "classic".
	 */
	public function get_editor_type( $post ): string {
		if ( is_int( $post ) ) {
			$post = get_post( $post );
		}

		if ( ! $post instanceof WP_Post ) {
			return 'classic';
		}

		return $this->detect( $post->post_content );
	}
}
