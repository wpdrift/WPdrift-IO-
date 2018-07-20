<?php
/**
 * WordPress OAuth Server Main Class
 * Responsible for being the main handler
 *
 * @author Justin Greer <justin@justin-greer.com>
 * @package WordPress OAuth Server
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class WO_Server {

	/** Plugin Version */
	public $version = WPDRIFT_HELPER_VERSION;

	/** Environment Type  */
	public $env = 'production';

	/** Server Instance */
	public static $_instance = null;

	/** Default Settings */
	public $defualt_settings = array(
		'enabled'                    => 1,
		'client_id_length'           => 30,
		'auth_code_enabled'          => 0,
		'client_creds_enabled'       => 0,
		'user_creds_enabled'         => 0,
		'refresh_tokens_enabled'     => 0,
		'jwt_bearer_enabled'         => 0,
		'implicit_enabled'           => 0,
		'require_exact_redirect_uri' => 0,
		'enforce_state'              => 0,
		'refresh_token_lifetime'     => 63072000, // 2 Year
		'access_token_lifetime'      => 31536000, // 1 Year
		'use_openid_connect'         => 0,
		'id_token_lifetime'          => 3600,
		'token_length'               => 40,
		'beta'                       => 0
	);

	function __construct() {

		if ( ! defined( 'WOABSPATH' ) ) {
			define( 'WOABSPATH', dirname( __FILE__ ) );
		}

		if ( ! defined( 'WOURI' ) ) {
			define( 'WOURI', plugins_url( '/', __FILE__ ) );
		}

		if ( ! defined( 'WOCHECKSUM' ) ) {
			define( 'WOCHECKSUM', 'F2B0D73C4BE99511D25BBAE6DF0BB28F' );
		}

		if ( function_exists( '__autoload' ) ) {
			spl_autoload_register( '__autoload' );
		}
		spl_autoload_register( array( $this, 'autoload' ) );

		//if ( ! defined( 'DOING_CRON' ) ) {
		add_filter( 'determine_current_user', array( $this, '_wo_authenicate_bypass' ), 9999 );
		add_action( 'init', array( __CLASS__, 'includes' ) );
		//}

	}

	/**
	 * Awesomeness for 3rd party support
	 *
	 * Filter; determine_current_user
	 * Other Filter: check_authentication
	 *
	 * This creates a hook in the determine_current_user filter that can check for a valid access_token
	 * and user services like WP JSON API and WP REST API.
	 *
	 * @param  [type] $user_id User ID to
	 *
	 * @author Mauro Constantinescu Modified slightly but still a contribution to the project.
	 *
	 * @return void
	 */
	public function _wo_authenicate_bypass( $user_id ) {
		if ( $user_id && $user_id > 0 ) {
			return (int) $user_id;
		}

		if ( wo_setting( 'enabled' ) == 0 ) {
			return (int) $user_id;
		}

		require_once( dirname( WPDRIFT_HELPER_FILE ) . '/library/OAuth2/Autoloader.php' );
		OAuth2\Autoloader::register();
		$server  = new OAuth2\Server( new OAuth2\Storage\Wordpressdb() );
		$request = OAuth2\Request::createFromGlobals();
		if ( $server->verifyResourceRequest( $request ) ) {
			$token = $server->getAccessTokenData( $request );
			if ( isset( $token['user_id'] ) && $token['user_id'] > 0 ) {
				return (int) $token['user_id'];
			}
		}

		return null;
	}

	/**
	 * populate the instance if the plugin for extendability
	 *
	 * @return object plugin instance
	 */
	public
	static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * setup plugin class autoload
	 *
	 * @return void
	 */
	public function autoload( $class ) {
		$path  = null;
		$class = strtolower( $class );
		$file  = 'class-' . str_replace( '_', '-', $class ) . '.php';

		if ( strpos( $class, 'wo_' ) === 0 ) {
			$path = dirname( __FILE__ ) . '/library/' . trailingslashit( substr( str_replace( '_', '-', $class ), 18 ) );
		}

		if ( $path && is_readable( $path . $file ) ) {
			include_once $path . $file;

			return;
		}
	}

	/**
	 * plugin includes called during load of plugin
	 *
	 * @return void
	 */
	public static function includes() {
		if ( is_admin() ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . '/includes/admin-options.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . '/includes/admin/post.php';

			/** include the ajax class if DOING_AJAX is defined */
			if ( defined( 'DOING_AJAX' ) ) {
				require_once plugin_dir_path( dirname( __FILE__ ) ) . '/includes/ajax/class-wo-ajax.php';
			}
		}

	}
}

function _WO() {
	return WO_Server::instance();
}

$GLOBAL['WO'] = _WO();
