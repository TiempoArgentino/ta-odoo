<?php

/**
 * A static class because :)
 */
class Odoo_Connection extends Ripcord_Client{

    static private $initialized = false;
    static private $url;
    static private $user;
    static private $password;
    static private $db;
    static private $id;

    static public function initialize()
    {
        if (self::$initialized)
            return false;
        self::$initialized = true;
        self::connect(self::$url, self::$user, self::$password, self::$db);
        self::contacts_fields(self::$url, self::$user, self::$password, self::$db);
        self::contacts_list(self::$url, self::$user, self::$password, self::$db);
        
        return true;
    }
    /**
     * Connection
     */
    static public function connect($url,$user,$password,$db) {
        $info = ripcord::client($url.'/xmlrpc/2/common');
        $uid = $info->authenticate($db, $user, $password, array());
        return $uid;
    }
    /**
     * Call a model
     */
    static public function model_url($url) {
        return ripcord::client("$url/xmlrpc/2/object");
    }
    /**
     * ID Types, not being used
     */
    static public function identy_odoo($url,$user,$password,$db) { //l10n_latam.identification.type
        $contacts_list = self::model_url($url)->execute_kw($db, self::connect($url,$user,$password,$db), $password, 'l10n_latam.identification.type', 'search_read', 
        array(array(array('id', '>', 0))),
        array('fields' => array('name', 'id'), 'order' => 'id'));
        $list = json_encode($contacts_list,JSON_PRETTY_PRINT); 
        $list_show = json_decode($list, true);
        return $list_show;
    }
    /**
     * Country States (Argentina), not being used
     */
    static public function states_odoo($url,$user,$password,$db) {
        $contacts_list = self::model_url($url)->execute_kw($db, self::connect($url,$user,$password,$db), $password, 'res.country.state', 'search_read', 
        array(array(array('country_id', '=', 10))), //10 is the ID from country
        array('fields' => array('name', 'code', 'id', 'display_name'), 'order' => 'id'));
        $list = json_encode($contacts_list,JSON_PRETTY_PRINT); 
        $list_show = json_decode($list, true);
        return $list_show;
    }
    /**
     * We list the users
     */
    static public function contacts_list($url,$user,$password,$db) {
        $contacts_list = self::model_url($url)->execute_kw($db, self::connect($url,$user,$password,$db), $password, 'res.partner', 'search_read', 
        array(array(array('id', '>', 0))),
        array('fields' => array('display_name', 'name', 'id', 'create_date', 'email', 'email_formatted'), 'order' => 'id'));
        $list = json_encode($contacts_list,JSON_PRETTY_PRINT); 
        $list_show = json_decode($list, true);
        return $list_show;
    }
    /**
     * We list the fields of the model
     */
    static public function contacts_fields($url,$user,$password,$db){
        $contacts_field = self::model_url($url)->execute_kw($db, self::connect($url,$user,$password,$db), $password, 'res.partner', 'fields_get', array(), array('attributes' => array('string', 'help', 'type')));
        $list = json_encode($contacts_field,JSON_PRETTY_PRINT); 
        return $list;
    }
    /**
     * If User Exists in Odoo
     */
    static public function user_exist($url,$user,$password,$db,$email) {
        $user_list = self::model_url($url)->execute_kw($db, self::connect($url,$user,$password,$db), $password, 'res.partner', 'search', 
        array(array(array('email', '=', $email))), array('limit'=>1));
        $list = json_encode($user_list,JSON_PRETTY_PRINT); 
        $list_show = json_decode($list, true);
        return $user_list;
    }
    /**
     * We add the user to Odoo
     */
    static public function contacts_create_basic($url,$user,$password,$db,$name,$display_name,$email){
        $fields = [
            'name' => $name,
            'display_name' => $display_name,
            'email' => $email
        ];
        return self::model_url($url)->execute_kw($db, self::connect($url,$user,$password,$db), $password, 'res.partner', 'create', array($fields));
    }
}