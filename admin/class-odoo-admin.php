<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://genosha.com.ar
 * @since      1.0.0
 *
 * @package    Odoo
 * @subpackage Odoo/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Odoo
 * @subpackage Odoo/admin
 * @author     Juan Iriart <juan.e@genosha.com.ar>
 */
class Odoo_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->requires();
		/**
		 * Admin Menu
		 */
		add_action('admin_menu', [$this, 'odoo_add_menu']);
		add_action('admin_init', [$this, 'odoo_sync_settings']);
		/**
		 * Ajax Sync
		 */
		add_action('wp_ajax_nopriv_odoo_ajax_contact_sync', [$this, 'contact_sync']);
		add_action('wp_ajax_odoo_ajax_contact_sync', [$this, 'contact_sync']);
		/**
		 * WC: get user and add to Odoo after order confirm
		 */
		add_action('woocommerce_thankyou', [$this, 'woocommerce_odoo_sync'], 10, 1);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/odoo-admin.css', array(), $this->version, 'all');
	}

	public function requires()
	{
		require __DIR__.'/odoo-admin-sync.php';
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		/**
		 * Admin Global Script
		 */
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/odoo-admin.js', array('jquery'), $this->version, false);
		/**
		 * Ajax Sync Script
		 */
		wp_enqueue_script('odoo_ajax', plugin_dir_url(__FILE__) . 'js/ajax.js', array('jquery'), $this->version, false);

		wp_localize_script('odoo_ajax', 'odoo_vars', [
			'ajaxurl' => admin_url('admin-ajax.php'),
			'action' => 'odoo_ajax_contact_sync',
			'nonce' => wp_create_nonce('my-ajax-nonce'),
			'msg_confirm' => __('All clients are about to be synchronized. Are you sure?','odoo'),
			'msg_success' => __('All clients were synced...','odoo'),
			'msg_error' => __('An error occurred, check the console...','odoo'),
			'msg_odoo' => __('Mass sync did not start.','odoo')
		]);
	}
	/**
	 * WP Admin Menu function
	 */
	public function odoo_add_menu()
	{
		add_menu_page('Odoo Plugin', 'Odoo Plugin', 'manage_options', 'odoo-sync-world', [$this, 'odoo_sync_page'], 'dashicons-welcome-widgets-menus', 30);
		add_submenu_page('odoo-sync-world', __('Contacts', 'odoo'), __('Contacts', 'odoo'), 'manage_options', 'odoo-sync-world-contacts', [$this, 'list_contacts']);
	}
	/**
	 * Credentials form
	 */
	public function odoo_sync_page()
	{
		echo '<div class="wrap">';
		echo '<form method="post" action="options.php">
					<h1>'.__('Configuration','odoo').'</h1>
					<p>'.__('Add configuration data of the Odoo instance','odoo').'</p>';
		settings_fields("odoo_sync_config");
		do_settings_sections("odoo-sync-world");
		submit_button();
		echo '</form>';
		echo '</div>';
	}
	/**
	 * Plugin and Odoo Settings
	 */
	public function odoo_sync_settings()
	{
		add_settings_section("odoo_sync_config", "", null, "odoo-sync-world");

		add_settings_field("odoo-url", __("Odoo URL", "odoo"), [$this, "odoo_sync_options"], "odoo-sync-world", "odoo_sync_config", array("odoo-url"));
		add_settings_field("odoo-user", __("Odoo USER", "odoo"), [$this, "odoo_sync_options"], "odoo-sync-world", "odoo_sync_config", array("odoo-user"));
		add_settings_field("odoo-password", __("Odoo PASSWORD", "odoo"), [$this, "odoo_sync_options"], "odoo-sync-world", "odoo_sync_config", array("odoo-password"));
		add_settings_field("odoo-db", __("Odoo DATABASE", "odoo"), [$this, "odoo_sync_options"], "odoo-sync-world", "odoo_sync_config", array("odoo-db"));

		register_setting("odoo_sync_config", "odoo-url", 'esc_attr');
		register_setting("odoo_sync_config", "odoo-user", 'esc_attr');
		register_setting("odoo_sync_config", "odoo-password", 'esc_attr');
		register_setting("odoo_sync_config", "odoo-db", 'esc_attr');
	}
	/**
	 * Form fields
	 */
	public function odoo_sync_options($args)
	{
		$option = get_option($args[0]);
		if ($args[0] == 'odoo-url') {
			$type = 'url';
			$help = '<span class="help">Ex: https://myodooserver.com:8069 or http://127.0.0.1:8069</span>';
		} else if ($args[0] === 'odoo-db') {
			$type = 'text';
			$help = '<span class="help">'.__('Activate developer mode to see the base name','odoo').', <a href="https://www.odoo.com/documentation/user/13.0/es/general/developer_mode/activate.html#:~:text=Vaya%20a%20Configuraci%C3%B3n%20%2D%3E%20Activar%20el,el%20modo%20desarrollador%20est%C3%A1%20disponible." target="_blank">'.__('Help','odoo').'</a></span>';
		} else if ($args[0] == 'odoo-password') {
			$type = 'password';
			$help = '';
		} else {
			$type = 'text';
			$help = '';
		}
		echo '<input type="' . $type . '" id="' . $args[0] . '" name="' . $args[0] . '" value="' . $option . '" /><br />';
		echo $help;
	}

	public function get_editable_roles() {
		global $wp_roles;

		if ( ! isset( $wp_roles ) )
			$wp_roles = new WP_Roles();
		
		return $wp_roles;
	}
	/**
	 * List of current Odoo users
	 */
	public function list_contacts()
	{
		/**
		 * Get roles in site
		 */
		$roles = $this->get_editable_roles();
		$r = array();
		foreach($roles->role_names as $key => $value) {
			$r[] = $key;
		}
		$role = array_diff($r,['administrator','shop_manager','editor','author','contributor','translator','ta_fotografo','ta_ads','ta_redactor','ta_socios_manager','ta_talleres']);
		/**
		 * Sync form and Fields
		 */
		echo '<div class="wrap">';
		if (get_option('odoo-url') && get_option('odoo-user') && get_option('odoo-password') && get_option('odoo-db')) {

			echo '<div class="alert alert-success">'.__('Odoo is connected','odoo').'</div>';
			/**
			 * We list Odoo users
			 */
			echo '<div class="medio">';
			echo '<h4>'.__('Odoo Contacts','odoo').'</h4>
			<p>'.__('Current Odoo Users','odoo').'</p>';
			foreach (Odoo_Connection::contacts_list(get_option('odoo-url'), get_option('odoo-user'), get_option('odoo-password'), get_option('odoo-db')) as $p) {
				echo $p['id'] . ' - ' . $p['name'] . ' - Email: ' . $p['email'] . ' - Creado: ' . $p['create_date'];
				echo '<br>';
			}
			echo '</div>';
			/**
			 * We list all the fields of the model
			 */
			echo '<div class="medio">
			<h4>'.__('Model fields','odoo').'</h4>
			<p>'.__('These fields that can be used to sync users.','odoo').'</p>';
			echo '<pre>';
			echo (Odoo_Connection::contacts_fields(get_option('odoo-url'), get_option('odoo-user'), get_option('odoo-password'), get_option('odoo-db')));
			echo '</pre>';
			echo '</div>';
			echo '<div class="roles-select">';
			echo '<h3>'.__('Select the roles to sync','odoo').'</h3>';
			echo '<p>'.__('Seleccione al menos un rol para sincronizar.','odoo').'</p>';
			foreach($role as $key => $value){
				echo '<label class="rol"><input type="checkbox" name="role[]" value="'.$value.'" /> '.$value.'</label>';
			}
			echo '</div>';
			echo '<div class="odoo-btn-container"><div id="syn-contacts-loading" style="display:none"></div>';
			echo '<div class="btn-odoo">
			<button class="btn bnt-sync" id="btn-sync-contact-new" type="button">'.__('Synchronize all users.','odoo').'</button></div>';
			if(get_current_user_id() === 1) {
				echo '
				<div id="user-creation"><input type="text" name="numero" id="numero" value="" /><button class="btn bnt-sync" id="c-users" type="button">'.__('Crear usuarios de testing.','odoo').'</button></div>
				</div>';
			}
			
		} else {
			echo '<div class="alert alert-danger">'.__('Odoo is NOT connected','odoo').'</div>';
		}
		echo '</div>';
	}
	/**
	 * Ajax masive sync
	 */
	public function user_sync()
	{
		/**
		 * Get all roles in site
		 */
		$rol = $this->get_editable_roles();
		$r = array();
		foreach($rol->role_names as $key => $value) {
			$r[] = $key;
		}
		/** 
		 * Get roles field from ajax.js
		 */
		$role_in = sanitize_text_field($_POST['roles']);
		if($role_in){
			$roles = [$role_in];
		} else {
			$roles = array_diff($r,['administrator','shop_manager']); //quit administrator role and shop_manager role (role from WC)
		}
		/**
		 * Users arguments
		 */
		$args = [
			'fields' => [
				'ID',
				'user_email'
			],
			'role__in' => $roles
		];
		/**
		 * Get Users By Roles
		 */
		$users = get_users($args);
		/**
		 * Add to Odoo
		 */
		foreach ($users as $u) {
			$id_user = get_user_meta($u->ID, '_odoo_user_id', true);
			$email = $u->user_email;
			$name = get_user_meta($u->ID, 'first_name', true) . ' ' . get_user_meta($u->ID, 'last_name', true);
			$exist = Odoo_Connection::user_exist(get_option('odoo-url'), get_option('odoo-user'), get_option('odoo-password'), get_option('odoo-db'), $email);
			if (!$id_user) { // Double check if user exists
				if ($exist[0] < 1) { //Verify if user exists
					$sync = Odoo_Connection::contacts_create_basic(get_option('odoo-url'), get_option('odoo-user'), get_option('odoo-password'), get_option('odoo-db'), $name, $name, $email);
					update_user_meta($u->ID, '_odoo_user_id', $sync);
				} else {
					$sync = __('A user with that email exists in Odoo','odoo');
				}
			} else {
				$sync = __('One or more users are already synchronized.','odoo');
			}
		}
		echo $sync;
	}
	/**
	 * Call sync function
	 */
	public function contact_sync()
	{
		$this->user_sync();
	}
	/**
	 * Add user to odoo after finish the order in WC
	 */
	public function woocommerce_odoo_sync($order_id)
	{
		$order = new WC_Order($order_id);
		$name = $order->billing_first_name . ' ' . $order->billing_last_name;
		$email = $order->billing_email;
		$id_user = get_user_meta(get_current_user_id(), '_odoo_user_id', true);

		$exist = Odoo_Connection::user_exist(get_option('odoo-url'), get_option('odoo-user'), get_option('odoo-password'), get_option('odoo-db'), $email);

		if (!$id_user) {
			if ($exist[0] < 1) {
				$sync = Odoo_Connection::contacts_create_basic(get_option('odoo-url'), get_option('odoo-user'), get_option('odoo-password'), get_option('odoo-db'), $name, $name, $email);
				update_user_meta($id_user, '_odoo_user_id', $sync);
			}
		}
	}
}
