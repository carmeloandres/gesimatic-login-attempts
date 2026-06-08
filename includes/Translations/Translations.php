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
             'enable_disables_login_attempts_functionality' =>  __('Enable / Disables login attempts functionality','gesimatic-login-attempts'),
             'enebled' =>  __('Enabled','gesimatic-login-attempts'),
             'disbled' =>  __('Disabled','gesimatic-login-attempts'),
             'acess_attempts_allowed' =>  __('Access attempts allowed','gesimatic-login-attempts'),
             'attempts_allowed_defore_lockout' =>  __('Attempts allowed before lockout','gesimatic-login-attempts'),
             'initial_lockout_period' =>  __('Initial lockout period','gesimatic-login-attempts'),
             'minutes_of_initial_lockout_period' =>  __('Minutes of initial lockout period','gesimatic-login-attempts'),
             'multiplier_of_the_period_of_block' =>  __('Multiplier of the period of block','gesimatic-login-attempts'),
             'multiplier_to_increase_the_blocking_period_in' =>  __('Multiplier to increase the blocking period in successive blocks, without resetting.','gesimatic-login-attempts'),
             'after_failed_access_attempts' =>  __('After %1$d failed access attempts from the same IP, access will be blocked for an initial period of %2$d minutes. If after this period another %1$d failed access attempts occur, the previous blocking period will be multiplied by %3$d and access will remain blocked during that period. This process continues until a successful login is achieved, at which point the failed access attempts for the IP are reset.','gesimatic-login-attempts'),
             'update_ettings' =>  __('Update settings','gesimatic-login-attempts'),
             'update_all_network' =>  __('Update all network','gesimatic-login-attempts'),
             'enable_disbles_the_sending' =>  __('Enable / Disables the sending of alert emails in the user login','gesimatic-login-attempts'),
             'enabled' =>  __('Enabled','gesimatic-login-attempts'),
             'disabled' =>  __('Disabled','gesimatic-login-attempts'),
            );

        return $output;

    }
}
