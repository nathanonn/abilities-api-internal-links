<?php
/**
 * Ability registrar.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI;

use InternalLinksAPI\Abilities\AddLinkAbility;
use InternalLinksAPI\Abilities\BatchAddLinksAbility;
use InternalLinksAPI\Abilities\BatchRemoveLinksAbility;
use InternalLinksAPI\Abilities\GetLinkReportAbility;
use InternalLinksAPI\Abilities\GetPostAbility;
use InternalLinksAPI\Abilities\RemoveLinkAbility;
use InternalLinksAPI\Abilities\SearchPostsAbility;
use InternalLinksAPI\Abilities\UpdateLinkAbility;
use InternalLinksAPI\Abilities\ValidateLinksAbility;
use InternalLinksAPI\Schemas\AddLinkSchema;
use InternalLinksAPI\Schemas\BatchAddLinksSchema;
use InternalLinksAPI\Schemas\BatchRemoveLinksSchema;
use InternalLinksAPI\Schemas\GetLinkReportSchema;
use InternalLinksAPI\Schemas\GetPostSchema;
use InternalLinksAPI\Schemas\RemoveLinkSchema;
use InternalLinksAPI\Schemas\SearchPostsSchema;
use InternalLinksAPI\Schemas\UpdateLinkSchema;
use InternalLinksAPI\Schemas\ValidateLinksSchema;

/**
 * Registers the ability category and all abilities.
 */
class AbilityRegistrar {

	/**
	 * Category slug.
	 *
	 * @var string
	 */
	private const CATEGORY_SLUG = 'internal-links';

	/**
	 * Ability namespace.
	 *
	 * @var string
	 */
	private const ABILITY_NAMESPACE = 'internal-links-api';

	/**
	 * Settings instance.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Services array.
	 *
	 * @var array
	 */
	private array $services;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings Settings instance.
	 * @param array    $services Services array.
	 */
	public function __construct( Settings $settings, array $services ) {
		$this->settings = $settings;
		$this->services = $services;

		add_action( 'wp_abilities_api_categories_init', array( $this, 'register_category' ) );
		add_action( 'wp_abilities_api_init', array( $this, 'register_abilities' ) );
	}

	/**
	 * Register the ability category.
	 *
	 * @return void
	 */
	public function register_category(): void {
		wp_register_ability_category(
			self::CATEGORY_SLUG,
			array(
				'label'       => __( 'Internal Links', 'internal-links-api' ),
				'description' => __( 'Abilities for searching content, managing internal links, and generating link reports within WordPress', 'internal-links-api' ),
			)
		);
	}

	/**
	 * Register all abilities.
	 *
	 * @return void
	 */
	public function register_abilities(): void {
		$this->register_search_posts();
		$this->register_get_post();
		$this->register_add_link();
		$this->register_update_link();
		$this->register_remove_link();
		$this->register_validate_links();
		$this->register_get_link_report();
		$this->register_batch_add_links();
		$this->register_batch_remove_links();
	}

	/**
	 * Register search-posts ability.
	 *
	 * @return void
	 */
	private function register_search_posts(): void {
		$ability = new SearchPostsAbility(
			$this->services['post'],
			$this->settings
		);

		wp_register_ability(
			self::ABILITY_NAMESPACE . '/search-posts',
			array(
				'label'               => __( 'Search Posts', 'internal-links-api' ),
				'description'         => __( 'Search for posts, pages, and custom post types to find potential internal link targets. Supports filtering by keywords, taxonomies, author, date range, and more.', 'internal-links-api' ),
				'category'            => self::CATEGORY_SLUG,
				'input_schema'        => SearchPostsSchema::get_input_schema(),
				'output_schema'       => SearchPostsSchema::get_output_schema(),
				'execute_callback'    => array( $ability, 'execute' ),
				'permission_callback' => array( $this, 'check_read_permission' ),
				'meta'                => array(
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
					'annotations'  => array(
						'readonly'    => true,
						'destructive' => false,
					),
				),
			)
		);
	}

	/**
	 * Register get-post ability.
	 *
	 * @return void
	 */
	private function register_get_post(): void {
		$ability = new GetPostAbility(
			$this->services['post'],
			$this->settings
		);

		wp_register_ability(
			self::ABILITY_NAMESPACE . '/get-post',
			array(
				'label'               => __( 'Get Post', 'internal-links-api' ),
				'description'         => __( 'Retrieve full details of a specific post, page, or custom post type by ID, including content, metadata, and taxonomies.', 'internal-links-api' ),
				'category'            => self::CATEGORY_SLUG,
				'input_schema'        => GetPostSchema::get_input_schema(),
				'output_schema'       => GetPostSchema::get_output_schema(),
				'execute_callback'    => array( $ability, 'execute' ),
				'permission_callback' => array( $this, 'check_read_post_permission' ),
				'meta'                => array(
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
					'annotations'  => array(
						'readonly'    => true,
						'destructive' => false,
					),
				),
			)
		);
	}

	/**
	 * Register add-link ability.
	 *
	 * @return void
	 */
	private function register_add_link(): void {
		$ability = new AddLinkAbility(
			$this->services['post'],
			$this->services['link_parser'],
			$this->services['link_modifier'],
			$this->services['post_lock'],
			$this->settings
		);

		wp_register_ability(
			self::ABILITY_NAMESPACE . '/add-link',
			array(
				'label'               => __( 'Add Internal Link', 'internal-links-api' ),
				'description'         => __( 'Add an internal link to a post by specifying anchor text, target post, and optional link attributes.', 'internal-links-api' ),
				'category'            => self::CATEGORY_SLUG,
				'input_schema'        => AddLinkSchema::get_input_schema(),
				'output_schema'       => AddLinkSchema::get_output_schema(),
				'execute_callback'    => array( $ability, 'execute' ),
				'permission_callback' => array( $this, 'check_edit_post_permission' ),
				'meta'                => array(
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
					'annotations'  => array(
						'readonly'    => false,
						'destructive' => false,
					),
				),
			)
		);
	}

	/**
	 * Register update-link ability.
	 *
	 * @return void
	 */
	private function register_update_link(): void {
		$ability = new UpdateLinkAbility(
			$this->services['post'],
			$this->services['link_parser'],
			$this->services['link_modifier'],
			$this->services['post_lock'],
			$this->settings
		);

		wp_register_ability(
			self::ABILITY_NAMESPACE . '/update-link',
			array(
				'label'               => __( 'Update Internal Link', 'internal-links-api' ),
				'description'         => __( 'Update an existing internal link\'s target, anchor text, or attributes.', 'internal-links-api' ),
				'category'            => self::CATEGORY_SLUG,
				'input_schema'        => UpdateLinkSchema::get_input_schema(),
				'output_schema'       => UpdateLinkSchema::get_output_schema(),
				'execute_callback'    => array( $ability, 'execute' ),
				'permission_callback' => array( $this, 'check_edit_post_permission' ),
				'meta'                => array(
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
					'annotations'  => array(
						'readonly'    => false,
						'destructive' => false,
					),
				),
			)
		);
	}

	/**
	 * Register remove-link ability.
	 *
	 * @return void
	 */
	private function register_remove_link(): void {
		$ability = new RemoveLinkAbility(
			$this->services['post'],
			$this->services['link_parser'],
			$this->services['link_modifier'],
			$this->services['post_lock'],
			$this->settings
		);

		wp_register_ability(
			self::ABILITY_NAMESPACE . '/remove-link',
			array(
				'label'               => __( 'Remove Internal Link', 'internal-links-api' ),
				'description'         => __( 'Remove an internal link from a post, with option to keep or delete the anchor text.', 'internal-links-api' ),
				'category'            => self::CATEGORY_SLUG,
				'input_schema'        => RemoveLinkSchema::get_input_schema(),
				'output_schema'       => RemoveLinkSchema::get_output_schema(),
				'execute_callback'    => array( $ability, 'execute' ),
				'permission_callback' => array( $this, 'check_edit_post_permission' ),
				'meta'                => array(
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
					'annotations'  => array(
						'readonly'    => false,
						'destructive' => true,
					),
				),
			)
		);
	}

	/**
	 * Register validate-links ability.
	 *
	 * @return void
	 */
	private function register_validate_links(): void {
		$ability = new ValidateLinksAbility(
			$this->services['post'],
			$this->services['link_validator'],
			$this->settings
		);

		wp_register_ability(
			self::ABILITY_NAMESPACE . '/validate-links',
			array(
				'label'               => __( 'Validate Internal Links', 'internal-links-api' ),
				'description'         => __( 'Validate all internal links within a post to identify broken links, unpublished targets, and permalink mismatches.', 'internal-links-api' ),
				'category'            => self::CATEGORY_SLUG,
				'input_schema'        => ValidateLinksSchema::get_input_schema(),
				'output_schema'       => ValidateLinksSchema::get_output_schema(),
				'execute_callback'    => array( $ability, 'execute' ),
				'permission_callback' => array( $this, 'check_read_post_permission' ),
				'meta'                => array(
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
					'annotations'  => array(
						'readonly'    => true,
						'destructive' => false,
					),
				),
			)
		);
	}

	/**
	 * Register get-link-report ability.
	 *
	 * @return void
	 */
	private function register_get_link_report(): void {
		$ability = new GetLinkReportAbility(
			$this->services['post'],
			$this->services['link_validator'],
			$this->settings
		);

		wp_register_ability(
			self::ABILITY_NAMESPACE . '/get-link-report',
			array(
				'label'               => __( 'Get Link Report', 'internal-links-api' ),
				'description'         => __( 'Generate a comprehensive report of all links within a specific post, including internal and external links, grouped by status.', 'internal-links-api' ),
				'category'            => self::CATEGORY_SLUG,
				'input_schema'        => GetLinkReportSchema::get_input_schema(),
				'output_schema'       => GetLinkReportSchema::get_output_schema(),
				'execute_callback'    => array( $ability, 'execute' ),
				'permission_callback' => array( $this, 'check_read_post_permission' ),
				'meta'                => array(
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
					'annotations'  => array(
						'readonly'    => true,
						'destructive' => false,
					),
				),
			)
		);
	}

	/**
	 * Register batch-add-links ability.
	 *
	 * @return void
	 */
	private function register_batch_add_links(): void {
		$ability = new BatchAddLinksAbility(
			$this->services['post'],
			$this->services['link_parser'],
			$this->services['link_modifier'],
			$this->services['post_lock'],
			$this->settings
		);

		wp_register_ability(
			self::ABILITY_NAMESPACE . '/batch-add-links',
			array(
				'label'               => __( 'Batch Add Links', 'internal-links-api' ),
				'description'         => __( 'Add multiple internal links to a single post in one operation.', 'internal-links-api' ),
				'category'            => self::CATEGORY_SLUG,
				'input_schema'        => BatchAddLinksSchema::get_input_schema(),
				'output_schema'       => BatchAddLinksSchema::get_output_schema(),
				'execute_callback'    => array( $ability, 'execute' ),
				'permission_callback' => array( $this, 'check_edit_post_permission' ),
				'meta'                => array(
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
					'annotations'  => array(
						'readonly'    => false,
						'destructive' => false,
					),
				),
			)
		);
	}

	/**
	 * Register batch-remove-links ability.
	 *
	 * @return void
	 */
	private function register_batch_remove_links(): void {
		$ability = new BatchRemoveLinksAbility(
			$this->services['post'],
			$this->services['link_parser'],
			$this->services['link_modifier'],
			$this->services['post_lock'],
			$this->settings
		);

		wp_register_ability(
			self::ABILITY_NAMESPACE . '/batch-remove-links',
			array(
				'label'               => __( 'Batch Remove Links', 'internal-links-api' ),
				'description'         => __( 'Remove multiple internal links from a single post in one operation.', 'internal-links-api' ),
				'category'            => self::CATEGORY_SLUG,
				'input_schema'        => BatchRemoveLinksSchema::get_input_schema(),
				'output_schema'       => BatchRemoveLinksSchema::get_output_schema(),
				'execute_callback'    => array( $ability, 'execute' ),
				'permission_callback' => array( $this, 'check_edit_post_permission' ),
				'meta'                => array(
					'show_in_rest' => true,
					'mcp'          => array(
						'public' => true,
						'type'   => 'tool',
					),
					'annotations'  => array(
						'readonly'    => false,
						'destructive' => true,
					),
				),
			)
		);
	}

	/**
	 * Check basic read permission.
	 *
	 * @param array $input Input data.
	 * @return bool True if user can read.
	 */
	public function check_read_permission( array $input = array() ): bool {
		return current_user_can( 'read' );
	}

	/**
	 * Check read permission for a specific post.
	 *
	 * @param array $input Input data with post_id.
	 * @return bool True if user can read the post.
	 */
	public function check_read_post_permission( array $input = array() ): bool {
		$post_id = $input['post_id'] ?? 0;

		// Require a valid post_id for post-specific abilities.
		if ( ! $post_id ) {
			return false;
		}

		return current_user_can( 'read_post', $post_id );
	}

	/**
	 * Check edit permission for source post.
	 *
	 * @param array $input Input data with source_post_id.
	 * @return bool True if user can edit the post.
	 */
	public function check_edit_post_permission( array $input = array() ): bool {
		$post_id = $input['source_post_id'] ?? 0;

		if ( ! $post_id ) {
			return false;
		}

		return current_user_can( 'edit_post', $post_id );
	}
}
