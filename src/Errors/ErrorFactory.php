<?php
/**
 * Error factory for creating WP_Error instances.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Errors;

use WP_Error;

/**
 * Factory class for creating standardized WP_Error instances.
 */
class ErrorFactory {

	/**
	 * Create a WP_Error instance.
	 *
	 * @param string $code       Error code.
	 * @param string $message    Error message.
	 * @param array  $extra_data Additional error data.
	 * @return WP_Error
	 */
	public static function create( string $code, string $message, array $extra_data = array() ): WP_Error {
		return new WP_Error(
			$code,
			$message,
			array_merge(
				array( 'status' => ErrorCodes::get_status( $code ) ),
				$extra_data
			)
		);
	}

	/**
	 * Create post not found error.
	 *
	 * @param int $post_id Post ID.
	 * @return WP_Error
	 */
	public static function post_not_found( int $post_id ): WP_Error {
		return self::create(
			ErrorCodes::POST_NOT_FOUND,
			__( 'The specified post does not exist.', 'internal-links-api' ),
			array( 'post_id' => $post_id )
		);
	}

	/**
	 * Create invalid post type error.
	 *
	 * @param string $post_type Post type.
	 * @param int    $post_id   Post ID.
	 * @return WP_Error
	 */
	public static function invalid_post_type( string $post_type, int $post_id ): WP_Error {
		return self::create(
			ErrorCodes::INVALID_POST_TYPE,
			sprintf(
				/* translators: %s: post type */
				__( 'Post type "%s" is not in the configured supported types.', 'internal-links-api' ),
				$post_type
			),
			array(
				'post_type' => $post_type,
				'post_id'   => $post_id,
			)
		);
	}

	/**
	 * Create target not published error.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $status  Current status.
	 * @return WP_Error
	 */
	public static function target_not_published( int $post_id, string $status ): WP_Error {
		return self::create(
			ErrorCodes::TARGET_NOT_PUBLISHED,
			__( 'Target post is not published.', 'internal-links-api' ),
			array(
				'post_id'       => $post_id,
				'current_status' => $status,
			)
		);
	}

	/**
	 * Create anchor not found error.
	 *
	 * @param string $anchor_text Anchor text.
	 * @param int    $post_id     Post ID.
	 * @return WP_Error
	 */
	public static function anchor_not_found( string $anchor_text, int $post_id ): WP_Error {
		return self::create(
			ErrorCodes::ANCHOR_NOT_FOUND,
			sprintf(
				/* translators: %s: anchor text */
				__( 'The anchor text "%s" was not found in the post content.', 'internal-links-api' ),
				$anchor_text
			),
			array(
				'anchor_text' => $anchor_text,
				'post_id'     => $post_id,
			)
		);
	}

	/**
	 * Create occurrence out of range error.
	 *
	 * @param int $requested Requested occurrence.
	 * @param int $available Available occurrences.
	 * @return WP_Error
	 */
	public static function occurrence_out_of_range( int $requested, int $available ): WP_Error {
		return self::create(
			ErrorCodes::OCCURRENCE_OUT_OF_RANGE,
			sprintf(
				/* translators: 1: requested occurrence, 2: available occurrences */
				__( 'Requested occurrence %1$d exceeds available matches (%2$d).', 'internal-links-api' ),
				$requested,
				$available
			),
			array(
				'requested' => $requested,
				'available' => $available,
			)
		);
	}

	/**
	 * Create anchor spans elements error.
	 *
	 * @param string $anchor_text Anchor text.
	 * @return WP_Error
	 */
	public static function anchor_spans_elements( string $anchor_text ): WP_Error {
		return self::create(
			ErrorCodes::ANCHOR_SPANS_ELEMENTS,
			sprintf(
				/* translators: %s: anchor text */
				__( 'The anchor text "%s" spans multiple HTML elements.', 'internal-links-api' ),
				$anchor_text
			),
			array( 'anchor_text' => $anchor_text )
		);
	}

	/**
	 * Create link not found error.
	 *
	 * @param array $identifier Link identifier.
	 * @return WP_Error
	 */
	public static function link_not_found( array $identifier ): WP_Error {
		return self::create(
			ErrorCodes::LINK_NOT_FOUND,
			__( 'No link matching the identifier was found.', 'internal-links-api' ),
			array( 'identifier' => $identifier )
		);
	}

	/**
	 * Create invalid identifier error.
	 *
	 * @param array $identifier Link identifier.
	 * @return WP_Error
	 */
	public static function invalid_identifier( array $identifier ): WP_Error {
		return self::create(
			ErrorCodes::INVALID_IDENTIFIER,
			__( 'Link identifier object is malformed.', 'internal-links-api' ),
			array( 'identifier' => $identifier )
		);
	}

	/**
	 * Create batch limit exceeded error.
	 *
	 * @param int $count     Requested count.
	 * @param int $max_limit Maximum allowed.
	 * @return WP_Error
	 */
	public static function batch_limit_exceeded( int $count, int $max_limit = 50 ): WP_Error {
		return self::create(
			ErrorCodes::BATCH_LIMIT_EXCEEDED,
			sprintf(
				/* translators: 1: requested count, 2: maximum limit */
				__( 'Batch operation with %1$d items exceeds maximum limit of %2$d.', 'internal-links-api' ),
				$count,
				$max_limit
			),
			array(
				'requested' => $count,
				'max_limit' => $max_limit,
			)
		);
	}

	/**
	 * Create permission denied error.
	 *
	 * @param string $capability Required capability.
	 * @return WP_Error
	 */
	public static function permission_denied( string $capability = '' ): WP_Error {
		$message = $capability
			? sprintf(
				/* translators: %s: capability name */
				__( 'You do not have permission to perform this action. Required capability: %s', 'internal-links-api' ),
				$capability
			)
			: __( 'You do not have permission to perform this action.', 'internal-links-api' );

		return self::create(
			ErrorCodes::PERMISSION_DENIED,
			$message,
			array( 'capability' => $capability )
		);
	}

	/**
	 * Create post locked error.
	 *
	 * @param int         $post_id   Post ID.
	 * @param string|null $lock_user User holding the lock.
	 * @return WP_Error
	 */
	public static function post_locked( int $post_id, ?string $lock_user = null ): WP_Error {
		$message = $lock_user
			? sprintf(
				/* translators: %s: user name */
				__( 'Post is currently being edited by %s.', 'internal-links-api' ),
				$lock_user
			)
			: __( 'Post is currently being edited by another user.', 'internal-links-api' );

		return self::create(
			ErrorCodes::POST_LOCKED,
			$message,
			array(
				'post_id'   => $post_id,
				'lock_user' => $lock_user,
			)
		);
	}

	/**
	 * Create no changes requested error.
	 *
	 * @return WP_Error
	 */
	public static function no_changes_requested(): WP_Error {
		return self::create(
			ErrorCodes::NO_CHANGES_REQUESTED,
			__( 'At least one change parameter must be provided.', 'internal-links-api' )
		);
	}

	/**
	 * Create self link not allowed error.
	 *
	 * @param int $post_id Post ID.
	 * @return WP_Error
	 */
	public static function self_link_not_allowed( int $post_id ): WP_Error {
		return self::create(
			ErrorCodes::SELF_LINK_NOT_ALLOWED,
			__( 'A post cannot link to itself.', 'internal-links-api' ),
			array( 'post_id' => $post_id )
		);
	}
}
