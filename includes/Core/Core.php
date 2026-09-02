<?php

namespace GesimaticLoginAttempts\Core;

use Gesimatic\Core\OptionManager;
use GesimaticLoginAttempts\Admin\Admin;
use GesimaticLoginAttempts\Core\Config;
use GesimaticLoginAttempts\Repositories\LoginAttemptsRepository;
use GesimaticLoginAttempts\Security\Security;

/**
 * Class Core
 *
 * This class contains the code necessary to manage the necesary function hooks.
 *
 * @package GesimaticLoginAttempts\Core.
 */
class Core {

    /**
     * Property to store an instance of itself.
     *
     * @var
     */
    private static $instance;

    /**
     * Array to store all the modules.
     *
     * @var array
     */
    protected array $modules = [];

    /**
     * Array to store dinamicaly the instances of each class when they are required.
     *
     * @var array
     */
    protected array $instances = [];

    /**
     * To get an instance of this object to prevent duplicity
     * 
     * @param void
     * @return self
     */
    public static function instance(): self {
        // if this object not exists
        if (self::$instance === null) {
            // creates a new object, executing the contructor
            self::$instance = new self();
        }
        return self::$instance;
    }

   /**
     * Class constructor.
     *
     * Sets the value of the properties, adds the actions necessary for the class operation
     */    
    function __construct()
    {
        //call to parent constructor
//        parent::__construct();

        // Loads the code using this hook
        add_action('plugins_loaded', [$this, 'init'], 0);

        // To register the gesimatic-login-attempts admin page
//        add_action('admin_menu',[$this,'register_admin_page']);

        // to load the smtp admin assets
//        add_action('admin_enqueue_scripts',[$this,'admin_enqueue_assets'], 10, 1);

        // Gesimatic menu highlighting using CSS/JS
//        add_action( 'admin_head', [ $this, 'force_menu_highlight' ] );

        // adding the login attempts to gesimatic admin page
/*        add_filter( 'gesimatic_admin_tabs', function( $tabs ) {
                $tabs['gesimatic-login-attempts'] = esc_html__( 'Login attempts', 'gesimatic-login-attempts' );
            return $tabs;
        });
*/

        // adding protection to all plugins that requires the ip is not blocked
        add_filter('gesimatic_is_ip_blocked',function ($blocked, $ip) {
            return Security::is_ip_blocked($ip);
        },10,2);

        // enabling acces failure to all gesimatic apis
        add_action('gesimatic_api_permissions_failure',[$this,'access_failed'],10,1);

        // enabling acces succes to all gesimatic apis
        add_action('gesimatic_api_permissions_success',[$this,'access_success'],10,1);

        // adds the admin api actions
//        add_filter('gesimatic_admin_actions',[$this,'register_gesimatic_login_attempts_api_actions']);


        // checks if the ip is not bloqued
        add_filter('authenticate', array($this,'validate_ip'),5,3);

        // add to block the access to the ip blocked
        add_filter('login_message', array($this,'login_message'),10,1);

        // update ip status in the database
        add_action('wp_login_failed', array($this,'login_failed'),10,2);

        // Custom the error message
        add_filter('login_errors',array($this,'login_errors_message'),10,1);

        // A successful login on any site resets this IP for the entire network.
        add_action('wp_login',array($this,'loged_ip'),10,2);

    }

   /**
     * To register and init all Gesimatic modules
     * 
     * @param void
     * @return void
     */
    public function init(): void {

        $this->register_modules();

        // Register API integrations before REST routes are initialized.
        $this->get_module('api')->init();

        // Admin solo en backend
        if (is_admin()) {
            $this->get_module('admin')->init();
        }

        // Modules and hooks loaded always, to load the translations

        // Load the plugin text domain for translations
        load_plugin_textdomain(
            'gesimatic-login-attempts',
            false,
            '/gesimatic-login-attempts/languages'//Relative path to WP_PLUGIN_DIR where the .mo file resides. 
        );

    }

    protected function register_modules(): void {

        $this->modules = [
            'api'   => \GesimaticLoginAttempts\Api\Api::class,
            'admin' => \GesimaticLoginAttempts\Admin\Admin::class,
        ];
    }

   public function get_module(string $key) {

        // if the module has not an instance
        if (!isset($this->instances[$key])) {
            // Checks if it is registered, 
            if (!isset($this->modules[$key])) {
                return null;
            }
            // and then create an instance
            $this->instances[$key] = new $this->modules[$key]();
        }

        return $this->instances[$key];
    }





    /**
     * Resets the network-wide status for the authenticated IP.
     *
     * In Multisite, a successful login on any site clears the attempts and
     * blocking state for this IP across the entire network.
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

            $options = OptionManager::get(Config::OPTION_SETTINGS, Config::DEFAULT_SETTINGS);

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

        $options = OptionManager::get(Config::OPTION_SETTINGS, Config::DEFAULT_SETTINGS);


            $ip = Security::get_client_ip();
            $status = $this->get_ip_status($ip);

            // if is not bloqued send the remain attempts
            if(intval($status['attempts']) % intval($options['attempts']) != 0){ 

                $rest_attempts =intval($options['attempts']) - (intval($status['attempts']) % intval($options['attempts']));
        
                $errors =  __('Access is protected by limiting the maximun number of failed attempts, you are left','gesimatic');
                $errors .= ' <strong>'.$rest_attempts.'</strong> '.__('attempts','gesimatic');
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
        
        $ip = Security::get_client_ip();
    
        $status = $this->get_ip_status($ip);

        if(Security::is_ip_blocked($ip)){
                
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

                $options = OptionManager::get(Config::OPTION_SETTINGS, Config::DEFAULT_SETTINGS);

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

        return $message;
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

        $ip = Security::get_client_ip();

        if (Security::is_ip_blocked($ip)) {
            return;
        }

        $options = OptionManager::get(Config::OPTION_SETTINGS, Config::DEFAULT_SETTINGS);
        LoginAttemptsRepository::record_failed_attempt($ip, (string) $user, $options);
        Security::clear_request_cache($ip);
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

        $query = $wpdb->prepare("SELECT * FROM " . LoginAttemptsRepository::table_name() . " WHERE ip = %s", $ip);
        $result = $wpdb->get_row($query, ARRAY_A);
        if ($result == null){

            $options = OptionManager::get(Config::OPTION_SETTINGS, Config::DEFAULT_SETTINGS);

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
     * @param string $identifier The identifier for the endpoint where the failed access occurred. 
     * 
     * @return void
     */
    function access_failed($ip){
        $ip = Security::normalize_ip($ip);

        if ($ip === null) {
            return;
        }

        if (Security::is_ip_blocked($ip)) {
            return;
        }

        $options = OptionManager::get(Config::OPTION_SETTINGS, Config::DEFAULT_SETTINGS);
        LoginAttemptsRepository::record_failed_attempt($ip, 'Api access', $options);
        Security::clear_request_cache($ip);
    }

    /**
     * Resets the network-wide count of failed access attempts for an IP.
     *
     * In Multisite, a successful authentication on any site clears the single
     * global record, resetting attempts for every site in the network.
     * 
     * @param string $ip The IP address to reset the count for. 
     * @return void
     */
    function access_success($ip){
        $ip = Security::normalize_ip($ip);

        if ($ip === null) {
            return;
        }

        LoginAttemptsRepository::delete_by_ip($ip);
        Security::clear_request_cache($ip);
    }
}