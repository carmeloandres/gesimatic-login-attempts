<?php

namespace GesimaticLoginAttempts\Api;


use Gesimatic\Api\ActionInterface;
use Gesimatic\Api\Controllers\AdminController;
use Gesimatic\Api\Base\CommonResponse;
use Gesimatic\Core\OptionManager;

use GesimaticLoginAttempts\Core\Config;

/**
 * Class Setup
 *
 * This class contains the code necessary to manage the data from get_login_attempts_settings api request.
 *
 * @package gesimatic-login-attempts
 */
class GetLoginAttemptsSettings implements ActionInterface{

    /**
     * To validate 
     * 
     * This method perfoms the necesaria actions to validate data.
     * 
     */
    public static function validate($params){

        // There are not parameters to validate, then return true
    
            return true;
    }

    /**
     * To handle 
     * 
     * This method perfoms the necesaria actions to handle data, to perform the request.
     * 
     */
    public static function handle($validated){

        if ($validated){
    
            $settings = OptionManager::get(Config::OPTION_SETTINGS, Config::DEFAULT_SETTINGS);

            $data['success'] = true;
            $data['message'] = 'Settings retrieved successfully';

            if (is_array($settings)){
                $data['settings'] = $settings;
                return $data;
            }
            else {
                $data['settings'] = Config::DEFAULT_SETTINGS;
                return $data;
            }
        }
    }

}