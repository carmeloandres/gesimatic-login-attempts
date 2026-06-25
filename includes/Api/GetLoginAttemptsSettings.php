<?php

namespace GesimaticLoginAttempts\Api;

use Gesimatic\Api\Controllers\AdminController;
use Gesimatic\Api\Base\CommonResponse;

use GesimaticLoginAttempts\Core\Setup;

/**
 * Class Setup
 *
 * This class contains the code necessary to manage the data from get_login_attempts_settings api request.
 *
 * @package gesimatic-login-attempts
 */
class GetLoginAttemptsSettings extends Setup{

    /**
     * To validate 
     * 
     * This method perfoms the necesaria actions to validate data.
     * 
     */
    public static function validate($params){

//        error_log ('GetLoginAttemptsSettings validate, $params: '.var_export($params,true));

        // check if acction is as expected
        if(isset($params['action']) && ($params['action'] === 'get_login_attempts_settings')){
            return true;
        } else return false;
    }

    /**
     * To handle 
     * 
     * This method perfoms the necesaria actions to handle data, to perform the request.
     * 
     */
    public static function handle($validated){

        if ($validated){
    
            $settings = get_option(self::OPTION_SETTINGS,self::DEFAULT_SETTINGS);

        if (is_array($settings))
                return new \WP_REST_Response($settings, 200);
            else return new \WP_REST_Response(self::DEFAULT_SETTINGS, 200);
        } else return CommonResponse::error();

    }


}