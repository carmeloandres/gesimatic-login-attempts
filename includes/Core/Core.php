<?php

namespace GesimaticLoginAttempts\Core;

use GesimaticLoginAttempts\Admin\Admin;
use GesimaticLoginAttempts\Api\DoLoginAttemptsStatusIpsAction;
use GesimaticLoginAttempts\Api\GetLoginAttemptsPagination;
use GesimaticLoginAttempts\Api\GetLoginAttemptsSettings;
use GesimaticLoginAttempts\Api\GetLoginAttemptsStatusIps;
use GesimaticLoginAttempts\Api\SetLoginAttemptsSettings;
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
     * Array to store dinamicaly the instances of each class when they are required.
     *
     * @var array
     */
    protected array $instances = [];

   /**
     * Class constructor.
     *
     * Sets the value of the properties, adds the actions necessary for the class operation
     */    
    function __construct()
    {
        //call to parent constructor
        parent::__construct();

        // To register the gesimatic-login-attempts admin page
        add_action('admin_menu',[$this,'register_admin_page']);

        // to load the smtp admin assets
        add_action('admin_enqueue_scripts',[$this,'admin_enqueue_assets'], 10, 1);

        // Gesimatic menu highlighting using CSS/JS
        add_action( 'admin_head', [ $this, 'force_menu_highlight' ] );

        // adding the login attempts to gesimatic admin page
        add_filter( 'gesimatic_admin_tabs', function( $tabs ) {
                $tabs['gesimatic-login-attempts'] = esc_html__( 'Login attempts', 'gesimatic-login-attempts' );
            return $tabs;
        });
        // adding protection to all plugins that requires the ip is not blocked
        add_filter('gesimatic_is_ip_blocked',function ($blocked, $ip) {
            return Security::is_ip_blocked($ip);
        },10,2);

        // enabling acces failure to all gesimatic apis
        add_action('gesimatic_api_permissions_failure',[$this,'access_failure'],10,1);

        // enabling acces succes to all gesimatic apis
        add_action('gesimatic_api_permissions_success',[$this,'access_success'],10,1);

        // adds the admin api actions
        add_filter('gesimatic_admin_actions',[$this,'register_gesimatic_login_attempts_api_actions']);


        // checks if the ip is not bloqued
        add_filter('authenticate', array($this,'validate_ip'),5,3);

        // add to block the access to the ip blocked
        add_filter('login_message', array($this,'login_message'),10,1);

        // update ip status in the database
        add_action('wp_login_failed', array($this,'login_failed'),10,2);

        // Custom the error message
        add_filter('login_errors',array($this,'login_errors_message'),10,1);

        // Resets the login_status_ip and update the loged_ip table
        add_action('wp_login',array($this,'loged_ip'),10,2);



    }

    /**
     * Loads the Admin class to register the gesimatic-login-attempts admin page
     * 
     * @param void
     * @return void
     */
    function register_admin_page(): void{

        // Load the Admin class if not is loaded
        if (! isset($this->instances['admin']))
            $this->instances['admin'] = new Admin();
        $this->instances['admin']->register_admin_page();
    }

    /**
     * Loads the Admin class to enqueue the gesimatic-smtp assets
     * 
     * @param void
     * @return void
     */
    function admin_enqueue_assets($hook): void{

        // Load the Admin class if not is loaded
        if (! isset($this->instances['admin']))
            $this->instances['admin'] = new Admin();
        $this->instances['admin']->admin_enqueue_assets($hook);
    }

    /**
     * Force highlighting of the main menu using CSS/JS when on a hidden modular page.
     * 
     * @param void
     * @return void
     */
    function force_menu_highlight(): void{

        // Load the Admin class if not is loaded
        if (! isset($this->instances['admin']))
            $this->instances['admin'] = new Admin();
        $this->instances['admin']->force_menu_highlight();
    }

    /**
     * Registers the gesimatic login attempts api actions
     * 
     * @param array 
     * @return array
     */
    public function register_gesimatic_login_attempts_api_actions($actions){

        $new_actions = $actions;

        $new_actions['set_login_attempts_settings'] = [
            'validate' => [SetLoginAttemptsSettings::class, 'validate'],
            'handle' => [SetLoginAttemptsSettings::class, 'handle'],
        ];
       
        $new_actions['get_login_attempts_settings'] = [
            'validate' => [GetLoginAttemptsSettings::class, 'validate'],
            'handle' => [GetLoginAttemptsSettings::class, 'handle'],
        ];              

        $new_actions['get_login_attempts_status_ips'] = [
            'validate' => [GetLoginAttemptsStatusIps::class, 'validate'],
            'handle' => [GetLoginAttemptsStatusIps::class, 'handle'],
        ];

        $new_actions['get_login_attempts_pagination'] = [
            'validate' => [GetLoginAttemptsPagination::class, 'validate'],
            'handle' => [GetLoginAttemptsPagination::class, 'handle'],
        ];

        $new_actions['do_login_attempts_status_ips_action'] = [
            'validate' => [DoLoginAttemptsStatusIpsAction::class, 'validate'],
            'handle' => [DoLoginAttemptsStatusIpsAction::class, 'handle'],
        ];

        //        error_log ('Gesimatic-login-attempts Core register_gesimatic_login_attempts_api_actions(), $new_actions: '.var_export($new_actions,true));

        return $new_actions;
    }


    /**
     * Resets an ip, it means tha the ip is erased in table table_name_status_ip
     * and update the last loged date ip.
     * 
     * @param string $user_login the username of the loged user.
     * @param WP_User $user, the user information object
     *
     * @return void 
     */
    function loged_ip($user_login, $user){
            
            $ip = Security::get_client_ip();
            
            // Reset ipStatus
            $this->access_success($ip);

            $options = get_option(self::OPTION_SETTINGS,array());

            if(isset($user->roles) && isset($options['logedInAlert']) && ($options['logedInAlert'] == true) && isset($options['triggerRoles']) && is_array($options['triggerRoles']) && count($options['triggerRoles']) > 0){

                foreach( $user->roles as $user_role)
                    // if role is enabled to recibe alarms
                    if(in_array($user_role,$options['triggerRoles'])){
                        // Create and sendthe email alarm
                        $subject = __('Access notification from','gesimatic-login-attempts').' '.$ip.' IP';
            
                        $body_title =__('Access notification','gesimatic-login-attempts');
                        $body_content=__('Loged as','gesimatic-login-attempts').' '.$user_login.' '.__('user, from IP:','gesimatic-login-attempts').' '.$ip.PHP_EOL;
                        $body_content.= __('This user is','gesimatic-login-attempts').' :'.translate_user_role($user_role);

                        $this->send_formated_html_email_to_user($user->data->user_email,$subject, $body_title, $body_content);
                    }
            }
        //}
    }

    /**
     * This method customize the error login message.
     * 
     * @param string the original errors message 
     * 
     * @return string the updated errors message
     */
    function login_errors_message($errors): string{

        $options = get_option(self::OPTION_SETTINGS);

        if ($options['enabled'] == true){

            $ip = Security::get_client_ip();
            $status = $this->get_ip_status($ip);

            // if is not bloqued send the remain attempts
            if(intval($status['attempts']) % intval($options['attempts']) != 0){ 

                $rest_attempts =intval($options['attempts']) - (intval($status['attempts']) % intval($options['attempts']));
        
                $errors =  __('Access is protected by limiting the maximun number of failed attempts, you are left','gesimatic');
                $errors .= ' <strong>'.$rest_attempts.'</strong> '.__('attempts','gesimatic');
            }
        }
        return $errors;
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

            error_log ('GesimaticLoginAttempts Core->login_failed(), $options: '.var_export($options,true));


//        if (isset($options['enabled']) && ($options['enabled'] == true) ){
            
            $ip = Security::get_client_ip();

            // is previously bloqued ends
            if(Security::is_ip_blocked($ip))
                return;

            error_log ('GesimaticLoginAttempts Core->login_failed(), $ip: '.var_export($ip,true));

//            $this->access_failed($ip);
            // spotlight to checks if the are queries of the option in process
            $lock_time = 30; // Maximun time of bloqued in seconds
            $retry_delay = 1; // Seconds to retry the query

            // wait until the transient expires or is deleted
            while (get_transient(self::SPOTLIGHT_QUERING_BLOCKED_IPS) == 'true'){
                sleep($retry_delay);
            }
            // set the spotlight to  disabling the access to the option
            set_transient(self::SPOTLIGHT_QUERING_BLOCKED_IPS,'true',$lock_time);

            $status = $this->get_ip_status($ip);

            error_log ('GesimaticLoginAttempts Core->login_failed(), $status: '.var_export($status,true));
                        
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
            delete_transient(self::SPOTLIGHT_QUERING_BLOCKED_IPS);

      //  }        
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
    
        $query = "SELECT * FROM ".self::$table_name_status_ip." WHERE ip = '".$ip."'";
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
            $result = $wpdb->update(self::$table_name_status_ip, $status, array( 'id' => $status['id']));
        } else $result = $wpdb->insert(self::$table_name_status_ip, $status);

        if ($result != null)
            return true;
        else return false;
    }

     /**
     * Send a formated html email to user with an advanced HTML template to a user_email.
     *
     * This function sends a fotmated email in an html template to the user_email with the $body_title and $body_content
     *
     * @param string $user_email The email to send informatios.
     * @param string $subject The subject of the email.
     * @param string $body_title The main title of the email body. 
     * @param string $body_content The main content of the email body.
     * 
     * @return void
     */
    function send_formated_html_email_to_user($user_email,$subject, $body_title, $body_content) {

        // Email headers
        $headers = array('Content-Type: text/html; charset=UTF-8');

        // HTML Email template
        $email_template = '
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f9f9f9;
                        margin: 0;
                        padding: 20px;
                    }
                    .email-container {
                        background-color: #ffffff;
                        max-width: 600px;
                        margin: 20px auto;
                        padding: 20px;
                        border-radius: 8px;
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    }
                    .email-header {
                        background-color: #2f4f80;
                        color: #ffffff;
                        padding: 10px;
                        text-align: center;
                        border-radius: 8px 8px 0 0;
                    }
                    .email-body {
                        padding: 20px;
                        color: #000000;
                        line-height: 1.6;
                    }
                    .email-footer {
                        margin-top: 20px;
                        font-size: 12px;
                        color: #777777;
                        text-align: center;
                    }
                    .button {
                        display: inline-block;
                        padding: 10px 20px;
                        margin-top: 20px;
                        background-color: #2f4f80;
                        color: #ffffff;
                        text-decoration: none;
                        border-radius: 5px;
                    }
                    .email-body a {
                        display: inline-block;
                        padding: 10px 20px;
                        margin-top: 20px;
                        background-color: #2f4f80;
                        color: #ffffff;
                        text-decoration: none;
                        border-radius: 5px;
                    }
                    .button:hover {
                        background-color: #005a87;
                    }
                    .gsmtc-footer a {
                        text-decoration: none;
                    }
                </style>
            </head>
            <body>
                <div class="email-container">
                    <div class="email-header">
                        <h1>'.$body_title.'</h1>
                    </div>
                    <div class="email-body">
                        <p>' . nl2br($body_content) . '</p>
                        <a href="' . home_url() . '">Visit Site</a>
                    </div>
                    <div class="email-footer">
                        <p>This email was sent from your WordPress site: ' . get_bloginfo('name') . '</p>
                        <p class="gsmtc-footer">' . get_bloginfo('url') . '</p>
                    </div>
                </div>
            </body>
            </html>
        ';

        return wp_mail($user_email, $subject, $email_template, $headers);
    }

    /**
     * This method increments the count of failed access attempts for an IP.
     * 
     * @param string $ip The IP address to increment the count for. 
     * @return void
     */
    function access_failed($ip){

            // spotlight to checks if the are queries of the option in process
            $lock_time = 30; // Maximun time of bloqued in seconds
            $retry_delay = 1; // Seconds to retry the query

            // wait until the transient expires or is deleted
            while (get_transient(self::SPOTLIGHT_QUERING_BLOCKED_IPS) == 'true'){
                sleep($retry_delay);
            }
            // set the spotlight to  disabling the access to the option
            set_transient(self::SPOTLIGHT_QUERING_BLOCKED_IPS,'true',$lock_time);

            $status = $this->get_ip_status($ip);
                        
            // update the user to the current user
            $status['userLogin'] = 'Api_access';
    
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
            delete_transient(self::SPOTLIGHT_QUERING_BLOCKED_IPS);
    }

    /**
     * This method resets the count of failed access attempts for an IP.
     * 
     * @param string $ip The IP address to reset the count for. 
     * @return void
     */
    function access_success($ip){
            global $wpdb;

            // Reset ipStatus
            $wpdb->delete(self::$table_name_status_ip,array('ip' => $ip));

    }
}