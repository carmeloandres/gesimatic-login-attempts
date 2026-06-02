<?php

namespace GesimaticLoginAttempts\Core;

use GesimaticLoginAttempts\Core\Setup;
use GesimaticLoginAttempts\Security\Security;

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

        // add to block the access to the ip blocked
        add_filter('login_message', array($this,'login_message'),10,1);

        // update ip status in the database
        add_action('wp_login_failed', array($this,'login_failed'),10,2);


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
        
        $settings = get_option(self::OPTION_SETTINGS,array()); 
        
        if (isset($settings['enabled']) && ($settings['enabled'] == true) ){

            // Gesimatic Login Attempts uses REMOTE_ADDR by default. 
            // If the site is behind Cloudflare or a reverse proxy, 
            // The administrator must configure the appropriate IP discovery method.
            
            $ip = Security::get_client_ip();

            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                return new \WP_Error('invalid_ip',__('Unable to verify your IP address.', 'gesimatic-login-attempts'));
            }

            if( Security::is_ip_blocked($ip)){
                return new \WP_Error( 'blocked_ip', __( 'Login attempts from your IP address have been blocked due to security reasons.', 'gesimatic-login-attempts' ) ); 
            }
        }

        return $user;

    }

    /**
     * This method creates a message at login time.
     * 
     * @param string
     * 
     * @return string
     */
    function login_message($message){
        
        $options = get_option(self::OPTION_SETTINGS); 

        if (isset($options['enabled']) && ($options['enabled'] == true) ){
            
            $ip = $_SERVER['REMOTE_ADDR'];
            
            $status = $this->get_ip_status($ip);

            if( $this->is_bloqued_ip($ip)){
                
                $now = time();
                if ($status['lockUntil'] > $now)
                    $time_left = intval($status['lockUntil']) - $now;
                else $time_left = 0;

                $message = $message. "<div class='notice notice-error'><p>".__('You have used all available access attempts, for security reasons you are blocked until', 'gesimatic' )." : <span id='gsmtc_login_until'></span></p></div>
                    <script>window.onload = function (){
                        let loginError = document.getElementById('login_error');
                        if (loginError != undefined)
                            loginError.style.display = 'none';
                        let gsmtcLoginUntil = document.getElementById('gsmtc_login_until');
                        if (gsmtcLoginUntil != undefined){
                            let timeLeft=".$time_left."; 
                            let date = new Date(); 
                            date.setSeconds(date.getSeconds() + timeLeft); 
                            let year = date.getFullYear();
                            let month = (date.getMonth() + 1).toString();
                            if (month.length == 1)
                                month = '0'+month;
                            let day = date.getDate().toString();
                            if (day.length == 1)
                                day = '0'+day;
                            let hours = date.getHours().toString();
                            if (hours.length == 1)
                                hours = '0'+hours;
                            let minutes = date.getMinutes().toString();
                            if (minutes.length == 1)
                                minutes = '0'+minutes;
                            gsmtcLoginUntil.innerHTML = ' ' +year+'/'+month+'/'+day+' '+hours+':'+minutes;
                        }
                        let loginForm = document.getElementById('loginform');
                        if (loginform != undefined)
                            loginform.style.display = 'none';
                let nav = document.getElementById('nav');
                if (nav != undefined)
                    nav.style.display = 'none';
                let languageSwitchers= Array.from(document.getElementsByClassName('language-switcher'));
                languageSwitchers.forEach(switcher => {switcher.style.display = 'none';})
                }</script>";
            }else {
    
                $rest_attempts = intval($options['attempts']) - (intval($status['attempts']) % intval($options['attempts']));
                       
                $message = '<div id="gsmtc-login-message" class="notice notice-info message"><p>';
                $message .= __('Access is protected by limiting the maximun number of failed attempts, you are left','gesimatic');
                $message .= ' <strong>'.$rest_attempts.'</strong> '.__('attempts','gesimatic').'</p></div>';
                $message .= '<script>window.onload = function (){';
                $message .= "   let login_error = document.getElementById('login_error')
                                if (login_error != undefined){
                                    let gsmtc = document.getElementById('gsmtc-login-message')
                                    gsmtc.style.display = 'none'}";
                $message .= '}</script>';
            }
        }

        return $message;

    }

        /**
     * This method checks if the ip is bloqued.
     * 
     * @param string The ip to checks if is bloqued

     * @return boolean 
     */
    function is_bloqued_ip($ip){

        // default return value
        $bloquedIp = false;
        // get the gsmtc_bloqued_ips option
        $bloquedIps = get_option('gsmtc_bloqued_ips',array());
        // creates am array to store the new bloqued ips
        $newBloquedIps = array();
        // boolean to checks if bloqued_ips has been changed
        $updated_bloqued_ips = false;
        // gets the current time
        $now = time();

        // checks if ip is in bloquedIps array 
        foreach ($bloquedIps as $bloqued){
            // To checks if an ip is bloqued, it must check two values, if the ip is in array.
            if ($bloqued['ip'] == $ip){
                // and if bloqued time hasn`t expired, the current time is minor than "until" bloqued time
                if ($now < intval($bloqued['until'])){
                    $bloquedIp = true;
                    $newBloquedIps[] = $bloqued;
                } else {// the current time is higher than "until" bloqued time it must unlock the ip
                    $this->unlock_ip($bloqued['ip']);
                    $updated_bloqued_ips = true;
                }
            } else {
                $newBloquedIps[] = $bloqued;
            }
        } ;
    
        if ($updated_bloqued_ips)
            update_option('gsmtc_bloqued_ips',$newBloquedIps);

        return $bloquedIp;
    }

     /**
     * Method login_failed
     * 
     * This method to update the ip status in the gsmtc_login_status_ip.
     * 
     * @param user 
     * @param error 
     * 
     * @return void
     */
    function login_failed($user,$error): void{

        $options = get_option(self::OPTION_SETTINGS);

        if (isset($options['enabled']) && ($options['enabled'] == true) ){
            
            $ip = Security::get_client_ip();

            // is previously bloqued ends
            if(Security::is_ip_blocked($ip))
                return;

            // spotlight to checks if the are queries of the option in process
            $lock_time = 30; // Maximun time of bloqued in seconds
            $retry_delay = 1; // Seconds to retry the query

            // wait until the transient expires or is deleted
            while (get_transient(SPOTLIGHT_QUERING_BLOCKED_IPS) == 'true'){
                sleep($retry_delay);
            }
            // set the spotlight to  disabling the access to the option
            set_transient(SPOTLIGHT_QUERING_BLOCKED_IPS,'true',$lock_time);

            $status = $this->get_ip_status($ip);
                        
            // update the user to the current user
            $status['userLogin'] = $user;
    
            // increment the attempts
            $status['attempts'] = intval($status['attempts']) + 1;
            // gets the current time, seconds from 1/1/1070
            $now = time();                
            // update the lastAttempt 
            $status['lastAttempt'] = $now;

            //Checks the attempts values and proceed as expected
            // are in break points
            if(intval($status['attempts']) % intval($options['attempts']) == 0){

                $until = $now + (intval($status['currentPeriod']) * 60);
                $this->lock_ip($ip, $until); // adds the ip to the OPTION_BLOCKED_IPS
                
                $status['lockUntil'] = $until;
                $status['currentPeriod'] = intval($options['multiplier']) * intval($status['currentPeriod']);
                $status['status'] = 'bloqued';
            }           

            $this->set_ip_status($status);
                
            // delete the spotlight to enabling the access to the option
            delete_transient(SPOTLIGHT_QUERING_BLOCKED_IPS);
        }        
    }

    /**
     * Method get_ip_status
     * 
     * This method gets from database the ip status or provide a start status record.
     * 
     * @param string The ip to gets the information
     * 
     * @return array
     */
    function get_ip_status($ip){
        global $wpdb;
    
        $query = "SELECT * FROM ".self::$table_name_login_status_ip." WHERE ip = '".$ip."'";
        $result = $wpdb->get_row($query,ARRAY_A);
        if ($result == null){

            $options = get_option(self::OPTION_SETTINGS);

            $result = array (
                'userLogin' => '',
                'ip' => $ip,
                'attempts' => 0,
                'currentPeriod' => intval($options['initialLock']),
                'lockUntil' => 0,
                'lastAttempt' => 0,
                'status' => 'enabled', 
            );
        }

        return $result;
    }

      /**
     * This method sets an Ip as bloqued.
     * 
     * @param string The ip to checks if is bloqued
     * @param string The until time to block the ip represented in seconds
     * 
     * @return void 
     */
    function lock_ip($ip, $until): void{

        $is_bloqued = false;
        $bloquedIps = get_option(OPTION_BLOCKED_IPS,array());

        // update the until time if the ip is yet bloqued
        foreach($bloquedIps as $bloqued){
            if ($bloqued['ip'] == $ip ){
                $bloqued['until'] = $until;
                $is_bloqued = true;
            }
        }
        // adds the ip to the bloqued_ips array to set as bloqued ip
        if (! $is_bloqued)
            $bloquedIps[] = array(
                                    'ip' => $ip,
                                    'until' => $until
            );
    
        update_option(OPTION_BLOCKED_IPS,$bloquedIps);

    }
    
  /**
     * This method sets to the database the ip status.
     * 
     * @param array status of the ip

     * @return boolean
     */
    function set_ip_status($status){
        global $wpdb;
    
        if (isset($status['id'])){
            $result = $wpdb->update(self::$table_name_login_status_ip, $status, array( 'id' => $status['id']));
        } else $result = $wpdb->insert(self::$table_name_login_status_ip, $status);

        if ($result != null)
            return true;
        else return false;
    }


}