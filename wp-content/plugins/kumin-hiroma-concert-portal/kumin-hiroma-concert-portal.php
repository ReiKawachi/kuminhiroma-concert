<?php
/**
 * Plugin Name: Kumin Hiroma Concert Portal
 * Plugin URI: https://example.com/
 * Description: 横浜市戸塚区「区民広間コンサート」の告知・運営管理を行うカスタムプラグイン。
 * Version: 0.1.0
 * Author: Kumin Hiroma Concert Team
 * License: GPL2
 */

// 直接アクセス防止。
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// クラスファイルを一元読み込み。
require_once plugin_dir_path( __FILE__ ) . 'includes/class-posttypes.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-taxonomies.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-helpers.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-acf-hooks.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-shortcodes.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-activator.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/group-admin.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/admin/concert-list.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/hooks/concert-hooks.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/hooks/group-hooks.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/shortcodes/next-concert.php';

/**
 * プラグイン初期化フック設定。
 */
function khc_init_plugin() {
    $posttypes  = new KHC_Posttypes();
    $taxonomies = new KHC_Taxonomies();
    $shortcodes = new KHC_Shortcodes();
    $group_admin = new KHC_Group_Admin();
    $concert_list_admin = new KHC_Concert_List_Admin();
    $concert_hooks = new KHC_Concert_Hooks();
    $group_hooks   = new KHC_Group_Hooks();
    $acf_hooks     = new KHC_ACF_Hooks();
    $next_concert_shortcode = new KHC_Next_Concert_Shortcode();

    add_action( 'init', [ $posttypes, 'register_post_types' ] );
    add_action( 'init', [ $taxonomies, 'register_taxonomies' ] );
    add_action( 'init', [ $shortcodes, 'register_shortcodes' ] );
    $concert_hooks->register_hooks();
    $group_hooks->register_hooks();
    $group_admin->register_admin_hooks();
    $concert_list_admin->register_hooks();
    $acf_hooks->register_hooks();
    $next_concert_shortcode->register();
}
add_action( 'plugins_loaded', 'khc_init_plugin' );

register_activation_hook( __FILE__, [ 'KHC_Activator', 'activate' ] );
