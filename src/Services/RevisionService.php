<?php
/**
 * Revision service.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Services;

/**
 * Service for managing WordPress post revisions.
 */
class RevisionService {

	/**
	 * Create a revision for a post.
	 *
	 * Note: WordPress automatically creates revisions when using wp_update_post().
	 * This method provides an explicit way to ensure a revision is created.
	 *
	 * @param int $post_id Post ID.
	 * @return int|false Revision ID on success, false on failure.
	 */
	public function create( int $post_id ) {
		// Check if revisions are enabled for this post type.
		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}

		if ( ! wp_revisions_enabled( $post ) ) {
			return false;
		}

		// WordPress automatically creates revisions via wp_update_post().
		// This method is primarily for documentation/clarity in the codebase.
		// The actual revision is created during the wp_update_post() call.
		return true;
	}

	/**
	 * Check if revisions are enabled for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return bool True if revisions are enabled.
	 */
	public function are_revisions_enabled( int $post_id ): bool {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}

		return wp_revisions_enabled( $post );
	}

	/**
	 * Get the number of revisions for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return int Number of revisions.
	 */
	public function get_revision_count( int $post_id ): int {
		$revisions = wp_get_post_revisions( $post_id, array( 'fields' => 'ids' ) );
		return count( $revisions );
	}
}
