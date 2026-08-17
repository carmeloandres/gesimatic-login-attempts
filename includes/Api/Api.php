<?php

namespace Gesimatic\Api;

use GesimaticLoginAttempts\Api\DoLoginAttemptsStatusIpsAction;
use GesimaticLoginAttempts\Api\GetLoginAttemptsPagination;
use GesimaticLoginAttempts\Api\GetLoginAttemptsSettings;
use GesimaticLoginAttempts\Api\GetLoginAttemptsStatusIps;
use GesimaticLoginAttempts\Api\SetLoginAttemptsSettings;

class Api {

    public function init(): void {

        // adds the admin api actions
        add_filter('gesimatic_admin_actions',[$this,'register_gesimatic_login_attempts_api_actions']);

    }

    /**
     * Registers the gesimatic login attempts api actions
     * 
     * @param array 
     * @return array
     */
    public function register_gesimatic_login_attempts_api_actions($actions){

        $new_actions = $actions;

        $new_actions['get-login-attempts-settings'] = GetLoginAttemptsSettings::class;              

        $new_actions['set-login-attempts-settings'] = SetLoginAttemptsSettings::class;              

        $new_actions['get-login-attempts-status-ips'] = GetLoginAttemptsStatusIps::class;

        $new_actions['get-login-attempts-pagination'] = GetLoginAttemptsPagination::class;

        $new_actions['do-login-attempts-status-ips'] = DoLoginAttemptsStatusIpsAction::class;

//        error_log ('Gesimatic-login-attempts Core register_gesimatic_login_attempts_api_actions(), $new_actions: '.var_export($new_actions,true));

        return $new_actions;
    }



}