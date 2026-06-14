<?php

namespace GesimaticLoginAttempts\Api;

use Gesimatic\Api\Controllers\AdminController;
use Gesimatic\Api\Base\CommonResponse;

use GesimaticLoginAttempts\Core\Setup;

/**
 * Class Setup
 *
 * This class contains the code necessary to manage the data from set_smtp_settings api request.
 *
 * @package gesimatic-smtp
 */
class SetLoginAttemptsSettings extends Setup{
    
    /**
     * To validate 
     * 
     * This method perfoms the necesaria actions to validate data.
     * 
     */
    public static function validate($params){

//        error_log ('SetSmtpSettings validate, $params: '.var_export($params,true));

        // sets the default value
        $sanitized_params = array();

        // check if action is as expected
        if(isset($params['action']) && ($params['action'] === 'set_login_attempts_settings')){

            if(isset($params['settings'])){
                
                $settings = json_decode($params['settings']);
                if ( json_last_error() !== JSON_ERROR_NONE) return false;

                $settings = (array) $settings; // convert object $settings to array
                // if is multisite and user is super_admin try to get the updateNetwork field 
                if (function_exists( 'is_multisite' ) && is_multisite() && is_super_admin()){
                    if (isset($settings['updateNetwork']) && gettype($settings['updateNetwork']) === 'boolean' && $settings['updateNetwork'] === true )
                        $sanitized_params['updatenetwork'] = true;
                }
//                error_log ('SetLoginAttemptsSettings validate, $settings: '.var_export($settings,true));

                
                if (isset($settings['enabled']) && gettype($settings['enabled']) === 'boolean'){
                    $sanitized_params['enabled'] = $settings['enabled'];
                } else return false;
//                error_log ('SetLoginAttemptsSettings validate, $sanitized_params: '.var_export($sanitized_params,true));
                    
                // if the login attempts is enabled proceed with next fields
                if($sanitized_params['enabled'] === true){
                    // validate attempts name
                    if(isset($settings['attempts']) ){
                        $sanitized_params['attempts'] = sanitize_text_field($settings['attempts']);
                        if ( (filter_var($sanitized_params['attempts'], FILTER_VALIDATE_INT) === false) || ( 0 >= (int) $sanitized_params['attempts']) || ( 11 <= (int) $sanitized_params['attempts'])) return false;
                    }else return false;
//                    error_log ('SetLoginAttemptsSettings validate, $sanitized_params: '.var_export($sanitized_params,true));

                    // validate initialLock
                    if(isset($settings['initialLock']) ){
                        $sanitized_params['initialLock'] = sanitize_text_field($settings['initialLock']);
                        if ( (filter_var($sanitized_params['initialLock'], FILTER_VALIDATE_INT) === false) || ( 4 >= (int) $sanitized_params['initialLock']) || ( 61 <= (int) $sanitized_params['initialLock'])) return false;
                    }else return false;
//                    error_log ('SetLoginAttemptsSettings validate, $sanitized_params: '.var_export($sanitized_params,true));

                    // validate multiplier
                    if(isset($settings['multiplier']) ){
                        $sanitized_params['multiplier'] = sanitize_text_field($settings['multiplier']);
                        if ( (filter_var($sanitized_params['multiplier'], FILTER_VALIDATE_INT) === false) || ( 0 >= (int) $sanitized_params['multiplier']) || ( 11 <= (int) $sanitized_params['multiplier'])) return false;
                    }else return false;
//                    error_log ('SetLoginAttemptsSettings validate, $sanitized_params: '.var_export($sanitized_params,true));

                    // validate smtp password
                    if(isset($settings['logedInAlert']) && gettype($settings['logedInAlert']) === 'boolean'){
                        $sanitized_params['logedInAlert'] = $settings['logedInAlert'];
                    }else return false;
//                    error_log ('SetLoginAttemptsSettings validate, $sanitized_params: '.var_export($sanitized_params,true));

                    // validate triggerRoles
                    if(isset($settings['triggerRoles']) && is_array($settings['triggerRoles'])) {
                        foreach($settings['triggerRoles'] as $index => $role)
                            $sanitized_params['triggerRoles'][$index] = sanitize_text_field($settings['triggerRoles'][$index]);
                        if ( ! self::validate_trigger_roles($sanitized_params['triggerRoles'])) return false;
                    }else return false;

//                    error_log ('SetLoginAttemptsSettings validate, $sanitized_params: '.var_export($sanitized_params,true));


                } else { // $settings['enabled'] === false

                    $settings = get_option(self::OPTION_SETTINGS,self::DEFAULT_SETTINGS);

                    if (is_array($settings) && isset($settings['enabled'])){
                        $settings['enabled'] = false;
                        return $settings;
                    } else return self::DEFAULT_SETTINGS;

                } 
                
            } else return false;

        } else return false;

        return $sanitized_params;

    }

    /**
     * To validate the triggerRoles 
     * 
     * This method perfoms the necesaria actions to validate the triggerRoles.
     * 
     */
    public static function validate_trigger_roles($triggerRoles): bool{

        // Get the roles through the wp_roles object
        global $wp_roles;

        // Get role names
        $roles = array();
        foreach($wp_roles->roles as $rol_slug => $rol_info){
            $roles[$rol_slug] = translate_user_role($rol_info['name']);
        }

        $result = true;

        foreach($triggerRoles as $role){
            if ( ! array_key_exists($role, $roles))
                $result = false;
        }

        return $result;
    }


    /**
     * To handle 
     * 
     * This method perfoms the necesaria actions to handle data, to perform the request.
     * 
     */
    public static function handle($settings){

        if (is_array($settings)){

            if (isset($settings['updatenetwork'])) {
                    
                unset($settings['updateNetwork']);

                $sites = get_sites();
                foreach ($sites as $site) {
                    switch_to_blog($site->blog_id);
            
                    update_option(self::OPTION_SETTINGS, $settings);
                
                    restore_current_blog();
                }
            } else {

                update_option(self::OPTION_SETTINGS, $settings);

            }

            return CommonResponse::success();
        } else return CommonResponse::error();

    }

}
