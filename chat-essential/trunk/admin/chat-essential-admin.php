<?php

/**
 * @link       http://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/admin
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Chat_Essential_Admin {

	/**
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * @since    0.0.1
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action( 'admin_menu', array( $this, 'add_menu' ) );
	}

	/**
	 * @since    0.0.1
	 */
	public function add_menu() {
		add_menu_page(
			'Chat Essential',
			'Chat Essential',
			'manage_options',
			'chat-essential',
			array( $this, 'menu_main_page' ),
			plugin_dir_url(__FILE__) . '../images/qr-icon-gray.png',
			20
		);
	}

	/**
	 * @since    0.0.1
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/chat-essential-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * @since    0.0.1
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/chat-essential-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * @since    0.0.1
	 */
	public function menu_main_page() {
  		$options = get_option('chat-essential');
		$settings_page = "";
		if (isset($options) && !empty($options)) {
  			$settings_page = new Chat_Essential_Admin_Main(array(
				  "app_id" => $options['app_id'],
				  "secret" => $options['secret'],
				  "identity_verification" => "",
			));
		} else {
			$settings_page = new Chat_Essential_Admin_Main(array(
				"app_id" => "",
				"secret" => "",
				"identity_verification" => "",
			));
		}
  		echo $settings_page->htmlUnclosed();
  		wp_nonce_field('chat-essential-update');
  		echo $settings_page->htmlClosed();
//		include_once plugin_dir_path( __FILE__ ) . 'partials/chat-essential-admin-menu-main.php';
	}

}