<?php
/**
 * Plugin settings management.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI;

/**
 * Settings class for managing plugin configuration.
 */
class Settings {

	/**
	 * Option key for plugin settings.
	 *
	 * @var string
	 */
	private const OPTION_KEY = 'internal_links_api_settings';

	/**
	 * Default supported post types.
	 *
	 * @var array
	 */
	private const DEFAULT_POST_TYPES = array( 'post', 'page' );

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add settings page to admin menu.
	 *
	 * @return void
	 */
	public function add_settings_page(): void {
		add_options_page(
			__( 'Internal Links API', 'internal-links-api' ),
			__( 'Internal Links API', 'internal-links-api' ),
			'manage_options',
			'internal-links-api',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register plugin settings.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			'internal_links_api',
			self::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array( 'post_types' => self::DEFAULT_POST_TYPES ),
			)
		);

		add_settings_section(
			'internal_links_api_main',
			__( 'General Settings', 'internal-links-api' ),
			array( $this, 'render_section_description' ),
			'internal-links-api'
		);

		add_settings_field(
			'post_types',
			__( 'Supported Post Types', 'internal-links-api' ),
			array( $this, 'render_post_types_field' ),
			'internal-links-api',
			'internal_links_api_main'
		);
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'internal_links_api' );
				do_settings_sections( 'internal-links-api' );
				submit_button( __( 'Save Settings', 'internal-links-api' ) );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render section description.
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__( 'Configure which post types are available for internal link management.', 'internal-links-api' ) . '</p>';
	}

	/**
	 * Render post types field.
	 *
	 * @return void
	 */
	public function render_post_types_field(): void {
		$options       = get_option( self::OPTION_KEY, array() );
		$selected      = $options['post_types'] ?? self::DEFAULT_POST_TYPES;
		$public_types  = get_post_types( array( 'public' => true ), 'objects' );

		echo '<fieldset>';
		foreach ( $public_types as $type ) {
			$checked = in_array( $type->name, $selected, true ) ? 'checked' : '';
			printf(
				'<label><input type="checkbox" name="%s[post_types][]" value="%s" %s> %s</label><br>',
				esc_attr( self::OPTION_KEY ),
				esc_attr( $type->name ),
				esc_attr( $checked ),
				esc_html( $type->label )
			);
		}
		echo '</fieldset>';
		echo '<p class="description">' . esc_html__( 'Select which post types should be available for internal link management. At least one post type must be selected.', 'internal-links-api' ) . '</p>';
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @param array $input Raw input data.
	 * @return array Sanitized data.
	 */
	public function sanitize_settings( array $input ): array {
		$sanitized = array();

		// Sanitize post types.
		if ( isset( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
			$public_types              = array_keys( get_post_types( array( 'public' => true ) ) );
			$sanitized['post_types']   = array_filter(
				$input['post_types'],
				function ( $type ) use ( $public_types ) {
					return in_array( $type, $public_types, true );
				}
			);
			$sanitized['post_types'] = array_values( $sanitized['post_types'] );
		}

		// Ensure at least one post type is selected.
		if ( empty( $sanitized['post_types'] ) ) {
			$sanitized['post_types'] = self::DEFAULT_POST_TYPES;
			add_settings_error(
				self::OPTION_KEY,
				'empty_post_types',
				__( 'At least one post type must be selected. Default post types have been restored.', 'internal-links-api' ),
				'error'
			);
		}

		return $sanitized;
	}

	/**
	 * Get supported post types.
	 *
	 * @return array Array of supported post type slugs.
	 */
	public function get_supported_post_types(): array {
		$options    = get_option( self::OPTION_KEY, array() );
		$post_types = $options['post_types'] ?? self::DEFAULT_POST_TYPES;

		/**
		 * Filter the supported post types.
		 *
		 * @param array $post_types Array of post type slugs.
		 */
		return apply_filters( 'internal_links_api_supported_post_types', $post_types );
	}

	/**
	 * Check if a post type is supported.
	 *
	 * @param string $post_type Post type slug.
	 * @return bool True if supported.
	 */
	public function is_supported_post_type( string $post_type ): bool {
		return in_array( $post_type, $this->get_supported_post_types(), true );
	}
}
