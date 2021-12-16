<?php


class Odoo_Sync_New
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'scripts']);
        add_action('rest_api_init', [$this, 'odoo_endpoint']);
    }

    public function get_editable_roles()
    {
        global $wp_roles;

        if (!isset($wp_roles))
            $wp_roles = new WP_Roles();

        return $wp_roles;
    }

    public function scripts()
    {
        wp_enqueue_script('odoo-sync-new', plugin_dir_url(__FILE__) . 'js/odoo-sync.js', ['jquery'], ODOO_VERSION, true);
        wp_localize_script('odoo-sync-new', 'varOdoo', [
            'syncURL' => rest_url('odoo/v1/sync'),
            'usrURL' => rest_url('odoo/v1/create-test-user'),
            'getURL' => rest_url('odoo/v1/get-users')
        ]);
    }

    public function odoo_endpoint()
    {
        register_rest_route('odoo/v1', '/sync', [
            'methods' => 'POST',
            'callback' => [$this, 'odoo_sync_func'],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route('odoo/v1', '/create-test-user', [
            'methods' => 'POST',
            'callback' => [$this, 'users'],
            'permission_callback' => '__return_true'
        ]);

        register_rest_route('odoo/v1', '/get-users', [
            'methods' => 'GET',
            'callback' => [$this, 'get_users'],
            'permission_callback' => '__return_true'
        ]);
    }

    public function odoo_sync_func(WP_REST_Request $request)
    {

        $id = $request->get_param('ID');


        if (null === $id || $id === '') wp_send_json_error('Falta el usuario.');

        $sync = $this->sync($id);

        wp_send_json_success($sync);
    }

    public function sync($u)
    {
        $user = get_user_by('id',$u);
        $email = $user->user_email;
        $name = $user->first_name . ' ' . $user->last_name;
        $exist = Odoo_Connection::user_exist(get_option('odoo-url'), get_option('odoo-user'), get_option('odoo-password'), get_option('odoo-db'), $email);

        if ($exist[0] < 1) {
            $sync = Odoo_Connection::contacts_create_basic(get_option('odoo-url'), get_option('odoo-user'), get_option('odoo-password'), get_option('odoo-db'), $name, $name, $email);

            update_user_meta( $user->ID,'_odoo_user_id',$sync);
            
            // $fp = fopen(__DIR__.'/log.txt','a+');
            // fwrite($fp,print_r($sync));

            return  $sync;
        }
    }

    public function get_users()
    {
        //$role_in = $request->get_param('role');
    
        $args = [
            'fields' => [
                'ID',
                //'user_email'
            ],
            'role__not_in' => ['administrator','shop_manager','editor','author','contributor','translator','ta_fotografo','ta_ads','ta_redactor','ta_socios_manager','ta_talleres'],
            'meta_key' => '_odoo_user_id',
            'meta_compare' => 'NOT EXISTS'
        ];
        /**
         * Get Users By Roles
         */
        $users = get_users($args);
        wp_send_json($users);
    }

    public function singup($user, $email, $password, $role = 'subscriber')
    {
        if (isset($email) && isset($password)) {
            $userdata = [
                'user_login' => $user,
                'first_name' => $user,
                'last_name'  => 'testing',
                'user_email' => $email,
                'user_pass' => $password,
                'role' => $role,
                'show_admin_bar_front' => false
            ];

            $user = wp_insert_user($userdata);

            if (is_wp_error($user)) {
                die($user->get_error_message());
            }

            return $user;
        }
        return false;
    }

    public function users(WP_REST_Request $request)
    {
        $data = $request->get_param('quantity');

        for ($i = 0; $i < $data; $i++) {
            $user = $this->generateRandomString();
            $email = $this->generateRandomString() . '@testing.com';
            $passw = $this->generateRandomString();
            $users = $this->singup($user, $email, $passw);
        }

        wp_send_json_success($users);
    }

    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

function odoo_sync()
{
    return new Odoo_Sync_New();
}

odoo_sync();
