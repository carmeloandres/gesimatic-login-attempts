<?php

namespace GesimaticLoginAttempts\Admin;

use Gesimatic\Core\Config;
use GesimaticLoginAttempts\Translations\Translations;

/**
 * Class AdminMenu.
 * 
 * @package GesimaticLoginAttempts\Admin.
*/
class Admin {

    /**
     * Hook suffix of the registered administration page.
     *
     * @var string|false
     */
    private $page_hook = false;

    public function init(): void
    {

        if (is_multisite()) {
            add_action('network_admin_menu', [$this, 'register_admin_page']);
            add_action('network_admin_enqueue_scripts', [$this, 'admin_enqueue_assets'], 10, 1);
        } else {
            add_action('admin_menu', [$this, 'register_admin_page']);
            add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_assets'], 10, 1);
        }
        // Gesimatic menu highlighting using CSS/JS
        add_action( 'admin_head', [ $this, 'force_menu_highlight' ] );
        // adding the smtp to gesimatic admin page
        add_filter( 'gesimatic_admin_tabs', function( $tabs ) {
            $tabs['gesimatic-login-attempts'] = esc_html__( 'Login attempts', 'gesimatic-login-attempts' );
            return $tabs;
        });

    }

    /**
     * Register the gesimatic-login-atttempts module page.
     */
    public function register_admin_page(): void
    {
            
        $this->page_hook = add_submenu_page(
            'non_existent_parent',
            __('Gesimatic Login Attempts', 'gesimatic-login-attempts'),
            __('Gesimatic Login Attempts', 'gesimatic-login-attempts'),
            $this->get_required_capability(),
            'gesimatic-login-attempts',
            [$this, 'show_admin_page']
        );
    }

    /**
     * Loads the Admin Gesimatic assets only in the gesimatic admin page.
     *
     * @param string $hook The name of the current page in the admin.
     */
    public function admin_enqueue_assets($hook): void
    {

        if ($this->page_hook === false || $this->page_hook !== $hook) {
            return;
        }

        if (!$this->current_user_can_manage()) {
            return;
        }
        
        wp_enqueue_style(
            'gesimatic-login-attempts-admin-css',                               
            GESIMATIC_LOGIN_ATTEMPTS_URL . 'assets/css/gesimatic-login-attempts-admin.min.css', 
            array(),                                             
            GESIMATIC_LOGIN_ATTEMPTS_VERSION                                    
        );

        wp_enqueue_script(
            'gesimatic-login-attempts-admin-js',
            GESIMATIC_LOGIN_ATTEMPTS_URL . 'assets/js/gesimatic-login-attempts-admin.js',
            array(),                                            
            GESIMATIC_LOGIN_ATTEMPTS_VERSION,                                    
            true                                             
        );

        // Get the roles through the wp_roles object
        global $wp_roles;

        // Get role names
        $roles = array();
        foreach($wp_roles->roles as $rol_slug => $rol_info){
            $roles[$rol_slug] = translate_user_role($rol_info['name']);
        }

        wp_localize_script(
            'gesimatic-login-attempts-admin-js',
            'gesimaticLoginAttemptsAdmin',
            array(
                "restUrl" => rest_url( Config::ROUTE_NAMESPACE_GESIMATIC_ADMIN ),
                "nonce" => wp_create_nonce( 'wp_rest' ),
                "availableRoles" => $roles,
                "isMultisite" => is_multisite(),
                "translations" => Translations::admin_translations()
            )
        );
    }

    /**
     * Show admin page.
     */
    public function show_admin_page(): void
    {
        // Get the roles through the wp_roles object
        global $wp_roles;

        if (!$this->current_user_can_manage()) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.','gesimatic-login-attempts' ) );
        }

        do_action( 'gesimatic_admin_header', 'gesimatic-login-attempts' );
        
        ?>

                <div id="gesimatic-login-attempts-admin"><?php esc_html_e( 'There has been an error, the component did not render.', 'gesimatic-login-attempts' ); ?></div>
            </div>
        <?php
    }

    /**
     * Returns the capability required for the current installation.
     */
    private function get_required_capability(): string
    {
        return is_multisite() ? 'manage_network_options' : 'manage_options';
    }

    /**
     * Checks whether the current user can manage this module.
     */
    private function current_user_can_manage(): bool
    {
        if (is_multisite()) {
            return is_network_admin()
                && is_super_admin()
                && current_user_can('manage_network_options');
        }

        return current_user_can('manage_options');
    }

    /**
     * Force highlighting of the main menu using CSS/JS
     * when on a hidden modular page.
     */
    public function force_menu_highlight() {
        global $plugin_page;

        if ( 'gesimatic-login-attempts' !== $plugin_page ) {
            return;
        }

        // The main menu slug (id="toplevel_page_gesimatic")
        // WordPress usually adds "toplevel_page_" to your main menu slug.
        $parent_id = 'toplevel_page_gesimatic'; 

        ?>
        <style>
            /* We highlight the main menu */
            #<?php echo $parent_id; ?> {
                background-color: #f0f0f1; /* Background color active */
            }            
             #<?php echo $parent_id; ?> .wp-menu-image:before {
                color: #72aee6 !important; /* Color of the blue icon of WP */
            }
            #<?php echo $parent_id; ?> .wp-menu-name::after {
                content: "";
                position: absolute;
                top: 7px; /* WP standard settings */
                right: 0;
                border-top: 10px solid transparent;
                border-bottom: 10px solid transparent;
                border-right: 10px solid #f0f0f1; /* Background color of the content area */
                display: block !important;
            }
            /* We ensure that the container allows for absolute positioning of the after. */
            #<?php echo $parent_id; ?> .wp-menu-name {
                position: relative;
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var parentMenu = document.getElementById('<?php echo $parent_id; ?>');
                if (parentMenu) {
                    parentMenu.classList.add('wp-has-current-submenu', 'wp-menu-open', 'current');
                    parentMenu.classList.remove('wp-not-current-submenu');
                    
                    // If you have submenus, we can also highlight the "Home" link.
//                    var firstSub = parentMenu.querySelector('ul li a');
//                    if (firstSub) firstSub.classList.add('current');
                }
            });
        </script>
        <?php
    }

    
}
    