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
             'status_ips' =>  __('Status ips','gesimatic-login-attempts'),
             'hide_status_ips' =>  __('Hide status ips','gesimatic-login-attempts'),
             'show_status_ips' =>  __('Show status ips','gesimatic-login-attempts'),
             'this_plugin_temporarily_stores_the_IP' =>  __('This plugin temporarily stores the IP addresses of failed login attempts to prevent brute-force attacks. IPs are automatically deleted after a successful login. We recommend including this information in your Privacy Policy.','gesimatic-login-attempts'),
             'reset_ip' =>  __('Reset ip','gesimatic-login-attempts'),
             'unlock_ip' =>  __('Unlock ip','gesimatic-login-attempts'),
             'reset' =>  __('Reset','gesimatic-login-attempts'),
             'unlock' =>  __('Unlock','gesimatic-login-attempts'),
             'all_states' =>  __('All states','gesimatic-login-attempts'),
             'enabled' =>  __('enabled','gesimatic-login-attempts'),
             'blocked' =>  __('blocked','gesimatic-login-attempts'),
             'getting_the_information' =>  __('Getting the information.. Please wait','gesimatic-login-attempts'),
             'the_information_has_been_obtained' =>  __('The information has been obtained correctly','gesimatic-login-attempts'),
             'are_you_sure_to_apply_the_action' =>  __('Are you sure to apply the %1$d action.','gesimatic-login-attempts'),
             'performing_the_action' =>  __('Performing the action.. Please wait','gesimatic-login-attempts'),
             'the_action_has_been_performed_correctly' =>  __('The action has been performed correctly','gesimatic-login-attempts'),
             'the_action_has_not_been_performed_correctly' =>  __('The action has not been performed correctly','gesimatic-login-attempts'),
             'user_login' =>  __('User login','gesimatic-login-attempts'),
             'attempts' =>  __('Attempts','gesimatic-login-attempts'),
             'last_attempts' =>  __('Last attempt','gesimatic-login-attempts'),
             'next_lock_period' =>  __('Next lock period','gesimatic-login-attempts'),
             'state' =>  __('State','gesimatic-login-attempts'),
             'action' =>  __('Action','gesimatic-login-attempts'),
             'blocked_until' =>  __('Blocked until','gesimatic-login-attempts'),
             'there_are_no_status_ips_to_show' =>  __('There are no status ips to show','gesimatic-login-attempts'),
             'min' =>  __('min','gesimatic-login-attempts'),
             'bloqued' =>  __('bloqued','gesimatic-login-attempts'),
             'bulk_actions' =>  __('Bulk actions','gesimatic-login-attempts'),
             'apply' =>  __('Apply','gesimatic-login-attempts'),
             'filter' =>  __('Filter','gesimatic-login-attempts'),
             'enable_disbles_the_sending' =>  __('Enable / Disables the sending of alert emails in the user login','gesimatic-login-attempts'),
             'disabled' =>  __('Disabled','gesimatic-login-attempts'),
             'items' =>  __('items','gesimatic-login-attempts'),
             'current_page' =>  __('Current page','gesimatic-login-attempts'),
             'of' =>  __('of','gesimatic-login-attempts'),
            );

        return $output;

    }
}
