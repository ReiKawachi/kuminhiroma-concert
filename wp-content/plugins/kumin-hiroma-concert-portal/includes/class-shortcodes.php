<?php
// 直接アクセス防止。
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ショートコードの登録と出力を担当するクラス。
 */
class KHC_Shortcodes {
    /**
     * ショートコードを登録する。
     */
    public function register_shortcodes() {
        add_shortcode( 'khc_next_concert', [ $this, 'render_next_concert' ] );
    }

    /**
     * 次回開催のコンサート情報を出力するショートコード。
     *
     * @return string
     */
    public function render_next_concert() {
        $next_concert = KHC_Helpers::get_next_concert();
        $plugin_dir   = dirname( __DIR__ );

        wp_enqueue_style(
            'khc-frontend',
            plugin_dir_url( $plugin_dir ) . 'assets/frontend.css',
            [],
            '0.1.0'
        );

        if ( ! $next_concert ) {
            return '<p class="c-next-concert__empty">次回のコンサート情報は準備中です。</p>';
        }

        $template_path = trailingslashit( $plugin_dir ) . 'templates/next-concert.php';

        if ( ! file_exists( $template_path ) ) {
            return '';
        }

        ob_start();
        include $template_path;
        return ob_get_clean();
    }
}
