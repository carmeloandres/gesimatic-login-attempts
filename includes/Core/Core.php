<?php

namespace GesimaticLoginAttempts\Core;

use GesimaticLoginAttempts\Core\Setup;

/**
 * Class Core
 *
 * This class contains the code necessary to manage the necesary function hooks.
 *
 * @package GesimaticLoginAttempts\Core.
 */
class Core extends Setup{
   /**
     * Class constructor.
     *
     * Sets the value of the properties, adds the actions necessary for the class operation
     */    
    function __construct()
    {
        //call to parent constructor
        parent::__construct();

        // checks if the ip is not bloqued
        add_filter('authenticate', array($this,'validate_ip'),5,3);

    }

    /**
     * This method checks if the ip is valid.
     * 
     * @param WP_User
     * @param string
     * @param string
     * 
     * @return WP_User|WP_Error
     */
    function validate_ip($user, $username, $password){

        // to check previous errors
        if (is_wp_error($user)) {
           return $user;
        }
        
        $options = get_option('gsmtc_access_settings',array()); 
        
        if (isset($options['enabled']) && ($options['enabled'] == true) ){

            // Gesimatic Login Attempts uses REMOTE_ADDR by default. 
            // If the site is behind Cloudflare or a reverse proxy, 
            // The administrator must configure the appropriate IP discovery method.
            
            $ip = $_SERVER['REMOTE_ADDR'];

            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                return new \WP_Error('invalid_ip',__('Unable to verify your IP address.', 'gesimatic-login-attempts'));

            if( $this->is_bloqued_ip($ip)){
                return new \WP_Error( 'blocked_ip', __( 'Login attempts from your IP address have been blocked due to security reasons.', 'gesimatic-login-attempts' ) ); 
            }
        }
        return $user;
    }


}