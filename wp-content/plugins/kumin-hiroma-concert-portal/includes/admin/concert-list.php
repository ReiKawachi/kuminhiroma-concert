<?php
// 直接アクセス防止。
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * コンサート一覧の管理フィルターを扱うクラス。
 */
class KHC_Concert_List_Admin {
    /**
     * 管理画面向けフックを登録する。
     */
    public function register_hooks() {
        add_action( 'restrict_manage_posts', [ $this, 'render_month_filter' ] );
        add_action( 'pre_get_posts', [ $this, 'filter_concert_query' ] );
    }

    /**
     * 開催月フィルターのドロップダウンを表示する。
     */
    public function render_month_filter() {
        global $typenow;

        if ( 'concert' !== $typenow ) {
            return;
        }

        $months     = $this->get_distinct_concert_months();
        $selected   = isset( $_GET['concert_month'] ) ? absint( wp_unslash( $_GET['concert_month'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $label_text = '開催月で絞り込み';

        echo '<label for="concert_month" class="screen-reader-text">' . esc_html( $label_text ) . '</label>';
        echo '<select name="concert_month" id="concert_month">';
        echo '<option value="">' . esc_html( $label_text ) . '</option>';

        foreach ( $months as $month ) {
            printf(
                '<option value="%1$s" %2$s>%3$s月</option>',
                esc_attr( $month ),
                selected( $selected, (int) $month, false ),
                esc_html( $month )
            );
        }

        echo '</select>';
    }

    /**
     * 月・年度フィルターをクエリに適用する。
     *
     * @param WP_Query $query メインクエリ。
     */
    public function filter_concert_query( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        $post_type = $query->get( 'post_type' );

        if ( empty( $post_type ) ) {
            global $typenow;
            $post_type = $typenow;
        }

        if ( 'concert' !== $post_type ) {
            return;
        }

        $meta_filters = [];

        $fiscal_year = filter_input( INPUT_GET, 'concert_fiscal_year', FILTER_SANITIZE_NUMBER_INT );
        $month       = filter_input( INPUT_GET, 'concert_month', FILTER_SANITIZE_NUMBER_INT );

        if ( ! empty( $fiscal_year ) ) {
            $meta_filters[] = [
                'key'   => KHC_Helpers::FIELD_KEYS['fiscal_year'],
                'value' => absint( $fiscal_year ),
            ];
        }

        if ( ! empty( $month ) ) {
            $meta_filters[] = [
                'key'   => KHC_Helpers::FIELD_KEYS['month'],
                'value' => absint( $month ),
            ];
        }

        if ( empty( $meta_filters ) ) {
            return;
        }

        $meta_query   = (array) $query->get( 'meta_query', [] );
        $meta_query[] = array_merge( [ 'relation' => 'AND' ], $meta_filters );

        $query->set( 'meta_query', $meta_query );
    }

    /**
     * データベースから利用可能な開催月を昇順で取得する。
     *
     * @return int[]
     */
    private function get_distinct_concert_months() {
        global $wpdb;

        $results = $wpdb->get_col( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $wpdb->prepare(
                "SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm"
                . " INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id"
                . " WHERE pm.meta_key = %s AND p.post_type = %s"
                . " ORDER BY CAST(pm.meta_value AS UNSIGNED) ASC",
                KHC_Helpers::FIELD_KEYS['month'],
                'concert'
            )
        );

        return array_values(
            array_filter(
                array_map( 'absint', (array) $results ),
                static function ( $value ) {
                    return $value >= 1 && $value <= 12;
                }
            )
        );
    }
}
