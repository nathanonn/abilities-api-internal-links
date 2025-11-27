<?php
/**
 * Post lock service.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Services;

/**
 * Service for managing post edit locks.
 */
class PostLockService {

	/**
	 * Check if a post is locked for editing.
	 *
	 * @param int $post_id Post ID.
	 * @return bool True if locked by another user.
	 */
	public function is_locked( int $post_id ): bool {
		$lock = $this->get_lock( $post_id );
		return ! empty( $lock );
	}

	/**
	 * Get lock information for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return array|null Lock data with user_id and user_name, or null if not locked.
	 */
	public function get_lock( int $post_id ): ?array {
		$lock = wp_check_post_lock( $post_id );

		if ( ! $lock ) {
			return null;
		}

		$user = get_userdata( $lock );
		if ( ! $user ) {
			return array(
				'user_id'   => $lock,
				'user_name' => __( 'Unknown user', 'internal-links-api' ),
			);
		}

		return array(
			'user_id'   => $lock,
			'user_name' => $user->display_name,
		);
	}

	/**
	 * Get the name of the user holding the lock.
	 *
	 * @param int $post_id Post ID.
	 * @return string|null User name or null if not locked.
	 */
	public function get_lock_user_name( int $post_id ): ?string {
		$lock = $this->get_lock( $post_id );
		return $lock['user_name'] ?? null;
	}
}
