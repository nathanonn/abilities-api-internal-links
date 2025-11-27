<?php
/**
 * Main plugin class.
 *
 * @package InternalLinksAPI
 */

namespace InternalLinksAPI;

use InternalLinksAPI\Services\EditorDetectorService;
use InternalLinksAPI\Services\LinkModifierService;
use InternalLinksAPI\Services\LinkParserService;
use InternalLinksAPI\Services\LinkValidatorService;
use InternalLinksAPI\Services\PostLockService;
use InternalLinksAPI\Services\PostService;
use InternalLinksAPI\Services\RevisionService;

/**
 * Plugin class - Singleton pattern.
 */
class Plugin {

	/**
	 * Plugin instance.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Settings instance.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Ability registrar instance.
	 *
	 * @var AbilityRegistrar
	 */
	private AbilityRegistrar $ability_registrar;

	/**
	 * Services container.
	 *
	 * @var array
	 */
	private array $services = array();

	/**
	 * Get plugin instance.
	 *
	 * @return Plugin
	 */
	public static function get_instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor.
	 */
	private function __construct() {
		$this->init_services();
		$this->init_components();
		$this->init_hooks();
	}

	/**
	 * Initialize services.
	 *
	 * @return void
	 */
	private function init_services(): void {
		$this->services['editor_detector'] = new EditorDetectorService();
		$this->services['post_lock']       = new PostLockService();
		$this->services['revision']        = new RevisionService();
		$this->services['link_parser']     = new LinkParserService();
		$this->services['link_modifier']   = new LinkModifierService(
			$this->services['editor_detector'],
			$this->services['link_parser']
		);
		$this->services['post']            = new PostService(
			$this->services['editor_detector']
		);
		$this->services['link_validator']  = new LinkValidatorService(
			$this->services['link_parser']
		);
	}

	/**
	 * Initialize components.
	 *
	 * @return void
	 */
	private function init_components(): void {
		$this->settings          = new Settings();
		$this->ability_registrar = new AbilityRegistrar(
			$this->settings,
			$this->services
		);
	}

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	private function init_hooks(): void {
		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Load plugin text domain.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'internal-links-api',
			false,
			dirname( INTERNAL_LINKS_API_BASENAME ) . '/languages'
		);
	}

	/**
	 * Get a service by name.
	 *
	 * @param string $name Service name.
	 * @return object|null
	 */
	public function get_service( string $name ): ?object {
		return $this->services[ $name ] ?? null;
	}

	/**
	 * Get settings instance.
	 *
	 * @return Settings
	 */
	public function get_settings(): Settings {
		return $this->settings;
	}

	/**
	 * Prevent cloning.
	 *
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Prevent unserializing.
	 *
	 * @return void
	 * @throws \Exception When attempting to unserialize.
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize singleton.' );
	}
}
