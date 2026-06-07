<?php

namespace GesimaticLoginAttempts\Translations;

if ( ! defined( 'ABSPATH' ) ) {exit;} ;

class Translations {

    public static function admin_translations(){
        $output = array(
             'updating_information' =>  __('Updating information.. Please wait','gesimatic-login-attempts'),
             'the_information_has_been' =>  __('The information has been updated successfull','gesimatic-login-attempts'),
             'the_information_has_not_been_updated' =>  __('The information has not been updated','gesimatic-login-attempts'),
             'updating_network' =>  __('Updating network.. Please wait','gesimatic-login-attempts'),
             'the_network_has_been_updated_successfull' =>  __('The network has been updated successfull','gesimatic-login-attempts'),
             'the_network_has_not_been_updated' =>  __('The network has not been updated','gesimatic-login-attempts'),
             'access_attempts' =>  __('Access attempts','gesimatic-login-attempts'),
             'settings' =>  __('Settings','gesimatic-login-attempts'),
             'hide_settings' =>  __('Hide settings','gesimatic-login-attempts'),
             'show_settings' =>  __('Show settings','gesimatic-login-attempts'),
            );

        return $output;

    }
}
