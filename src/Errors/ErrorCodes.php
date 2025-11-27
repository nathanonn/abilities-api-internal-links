<?php
/**
 * Error code constants.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI\Errors;

/**
 * Error codes class with constants and status mapping.
 */
class ErrorCodes {

	// 404 errors.
	public const POST_NOT_FOUND = 'post_not_found';

	// 400 errors.
	public const INVALID_POST_TYPE       = 'invalid_post_type';
	public const TARGET_NOT_PUBLISHED    = 'target_not_published';
	public const ANCHOR_NOT_FOUND        = 'anchor_not_found';
	public const OCCURRENCE_OUT_OF_RANGE = 'occurrence_out_of_range';
	public const ANCHOR_SPANS_ELEMENTS   = 'anchor_spans_elements';
	public const LINK_NOT_FOUND          = 'link_not_found';
	public const INVALID_IDENTIFIER      = 'invalid_identifier';
	public const BATCH_LIMIT_EXCEEDED    = 'batch_limit_exceeded';
	public const VALIDATION_ERROR        = 'validation_error';
	public const NO_CHANGES_REQUESTED    = 'no_changes_requested';
	public const SELF_LINK_NOT_ALLOWED   = 'self_link_not_allowed';

	// 403 errors.
	public const PERMISSION_DENIED = 'permission_denied';

	// 423 errors.
	public const POST_LOCKED = 'post_locked';

	/**
	 * HTTP status code mapping.
	 *
	 * @var array
	 */
	private static array $status_map = array(
		self::POST_NOT_FOUND          => 404,
		self::INVALID_POST_TYPE       => 400,
		self::TARGET_NOT_PUBLISHED    => 400,
		self::ANCHOR_NOT_FOUND        => 400,
		self::OCCURRENCE_OUT_OF_RANGE => 400,
		self::ANCHOR_SPANS_ELEMENTS   => 400,
		self::LINK_NOT_FOUND          => 400,
		self::INVALID_IDENTIFIER      => 400,
		self::BATCH_LIMIT_EXCEEDED    => 400,
		self::VALIDATION_ERROR        => 400,
		self::NO_CHANGES_REQUESTED    => 400,
		self::SELF_LINK_NOT_ALLOWED   => 400,
		self::PERMISSION_DENIED       => 403,
		self::POST_LOCKED             => 423,
	);

	/**
	 * Get HTTP status code for an error code.
	 *
	 * @param string $code Error code.
	 * @return int HTTP status code.
	 */
	public static function get_status( string $code ): int {
		return self::$status_map[ $code ] ?? 500;
	}
}
