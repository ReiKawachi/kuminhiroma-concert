<?php
// 直接アクセス防止。
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * コンサート一覧の管理フィルターとカラムを扱うクラス。
 */
class KHC_Concert_List_Admin {
    /**
     * 管理画面向けフックを登録する。
     */
    public function register_hooks() {
        add_action( 'restrict_manage_posts', [ $this, 'render_filters' ] );
        add_action( 'pre_get_posts', [ $this, 'filter_concert_query' ] );
        add_filter( 'manage_edit-concert_columns', [ $this, 'add_concert_columns' ] );
        add_action( 'manage_concert_posts_custom_column', [ $this, 'render_concert_columns' ], 10, 2 );
        add_filter( 'manage_edit-concert_sortable_columns', [ $this, 'make_columns_sortable' ] );
        add_filter( 'disable_months_dropdown', [ $this, 'disable_months_dropdown' ], 10, 2 );
    }

    /**
     * 開催年度・開催月フィルターのドロップダウンを表示する。
     */
    public function render_filters() {
        global $typenow;

        if ( 'concert' !== $typenow ) {
            return;
        }

        $fiscal_years = $this->get_distinct_fiscal_years();
        $year_value   = isset( $_GET['concert_fiscal_year'] ) ? absint( wp_unslash( $_GET['concert_fiscal_year'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        echo '<span class="khc-admin-filter khc-admin-filter--year" style="margin-right:12px;">';
        echo '<label for="concert_fiscal_year" class="khc-admin-filter-label" style="margin-right:6px;">' . esc_html__( '年度別', 'kumin-hiroma-concert-portal' ) . '</label>';
        echo '<select name="concert_fiscal_year" id="concert_fiscal_year">';
        echo '<option value="">' . esc_html__( 'すべて', 'kumin-hiroma-concert-portal' ) . '</option>';

        foreach ( $fiscal_years as $year ) {
            printf(
                '<option value="%1$s" %2$s>%3$s</option>',
                esc_attr( $year ),
                selected( $year_value, (int) $year, false ),
                esc_html( $year )
            );
        }

        echo '</select>';
        echo '</span>';

        $months   = $this->get_distinct_concert_months();
        $selected = isset( $_GET['concert_month'] ) ? absint( wp_unslash( $_GET['concert_month'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        echo '<span class="khc-admin-filter khc-admin-filter--month">';
        echo '<label for="concert_month" class="khc-admin-filter-label" style="margin-right:6px;">' . esc_html__( '月別', 'kumin-hiroma-concert-portal' ) . '</label>';
        echo '<select name="concert_month" id="concert_month">';
        echo '<option value="">' . esc_html__( 'すべて', 'kumin-hiroma-concert-portal' ) . '</option>';

        foreach ( $months as $month ) {
            printf(
                '<option value="%1$s" %2$s>%3$s月</option>',
                esc_attr( $month ),
                selected( $selected, (int) $month, false ),
                esc_html( $month )
            );
        }

        echo '</select>';
        echo '</span>';
    }

    /**
     * 月・年度フィルターと並び替えをクエリに適用する。
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

        $meta_query  = $query->get( 'meta_query' );
        $meta_query  = is_array( $meta_query ) ? $meta_query : [];
        $meta_filters = [];

        $fiscal_year = filter_input( INPUT_GET, 'concert_fiscal_year', FILTER_SANITIZE_NUMBER_INT );
        $month       = filter_input( INPUT_GET, 'concert_month', FILTER_SANITIZE_NUMBER_INT );

        if ( ! empty( $fiscal_year ) ) {
            $meta_filters[] = [
                'key'     => KHC_Helpers::FIELD_KEYS['fiscal_year'],
                'value'   => absint( $fiscal_year ),
                'compare' => '=',
            ];
        }

        if ( ! empty( $month ) ) {
            $meta_filters[] = [
                'key'     => KHC_Helpers::FIELD_KEYS['month'],
                'value'   => absint( $month ),
                'compare' => '=',
            ];
        }

        foreach ( $meta_filters as $filter ) {
            $meta_query[] = $filter;
        }

        if ( ! empty( $meta_filters ) ) {
            $meta_query['relation'] = isset( $meta_query['relation'] ) ? $meta_query['relation'] : 'AND';
            $query->set( 'meta_query', $meta_query );
        }

        $orderby = $query->get( 'orderby' );
        $order   = $query->get( 'order' );
        $order   = $order ? $order : 'ASC';

        if ( empty( $orderby ) ) {
            $query->set( 'meta_key', KHC_Helpers::FIELD_KEYS['held_date'] );
            $query->set( 'orderby', 'meta_value_num' );
            $query->set( 'order', 'ASC' );
            return;
        }

        switch ( $orderby ) {
            case 'concert_fiscal_year':
                $query->set( 'meta_key', KHC_Helpers::FIELD_KEYS['fiscal_year'] );
                $query->set( 'orderby', 'meta_value_num' );
                $query->set( 'order', $order );
                break;
            case 'concert_month':
                $query->set( 'meta_key', KHC_Helpers::FIELD_KEYS['month'] );
                $query->set( 'orderby', 'meta_value_num' );
                $query->set( 'order', $order );
                break;
            case 'held_date':
                $query->set( 'meta_key', KHC_Helpers::FIELD_KEYS['held_date'] );
                $query->set( 'orderby', 'meta_value_num' );
                $query->set( 'order', $order );
                break;
        }
    }

    /**
     * 開催年度・開催月・開催日カラムを追加する。
     *
     * @param array $columns 既存カラム。
     * @return array
     */
    public function add_concert_columns( $columns ) {
        $new_columns = [];

        if ( isset( $columns['taxonomy-fiscal_year'] ) ) {
            unset( $columns['taxonomy-fiscal_year'] );
        }

        foreach ( $columns as $key => $label ) {
            $new_columns[ $key ] = $label;

            if ( 'title' === $key ) {
                $new_columns['concert_fiscal_year'] = '開催年度';
                $new_columns['concert_month']       = '開催月';
                $new_columns['held_date']           = '開催日';
            }
        }

        if ( isset( $columns['date'] ) && ! isset( $new_columns['date'] ) ) {
            $new_columns['date'] = $columns['date'];
        }

        foreach ( [ 'concert_fiscal_year', 'concert_month', 'held_date' ] as $custom_column ) {
            if ( ! isset( $new_columns[ $custom_column ] ) ) {
                $new_columns[ $custom_column ] = '';
            }
        }

        return $new_columns;
    }

    /**
     * 追加カラムの内容を描画する。
     *
     * @param string $column  カラムスラッグ。
     * @param int    $post_id 投稿ID。
     */
    public function render_concert_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'concert_fiscal_year':
                $year = KHC_Helpers::get_field_value( $post_id, 'fiscal_year' );
                echo esc_html( $year ? $year : '—' );
                break;
            case 'concert_month':
                $month = KHC_Helpers::get_field_value( $post_id, 'month' );
                echo esc_html( $month ? $month . '月' : '—' );
                break;
            case 'held_date':
                $held_date_value = KHC_Helpers::get_field_value( $post_id, 'held_date' );
                $held_date       = KHC_Helpers::parse_held_date( $held_date_value, $post_id );

                if ( $held_date instanceof DateTimeImmutable ) {
                    echo esc_html( $held_date->format( 'Y年n月j日' ) );
                } else {
                    echo '—';
                }
                break;
        }
    }

    /**
     * 追加カラムをソート可能に設定する。
     *
     * @param array $columns ソート可能カラム。
     * @return array
     */
    public function make_columns_sortable( $columns ) {
        $columns['concert_fiscal_year'] = 'concert_fiscal_year';
        $columns['concert_month']       = 'concert_month';
        $columns['held_date']           = 'held_date';

        return $columns;
    }

    /**
     * コアの「すべての日付」ドロップダウンをコンサート一覧では表示しない。
     *
     * @param bool   $disable   非表示フラグ。
     * @param string $post_type 投稿タイプ。
     * @return bool
     */
    public function disable_months_dropdown( $disable, $post_type ) {
        if ( 'concert' === $post_type ) {
            return true;
        }

        return $disable;
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

    /**
     * 利用可能な開催年度を昇順で取得する。
     *
     * @return int[]
     */
    private function get_distinct_fiscal_years() {
        global $wpdb;

        $results = $wpdb->get_col( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $wpdb->prepare(
                "SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm"
                . " INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id"
                . " WHERE pm.meta_key = %s AND p.post_type = %s"
                . " ORDER BY CAST(pm.meta_value AS UNSIGNED) ASC",
                KHC_Helpers::FIELD_KEYS['fiscal_year'],
                'concert'
            )
        );

        return array_values(
            array_filter(
                array_map( 'absint', (array) $results ),
                static function ( $value ) {
                    return $value > 0;
                }
            )
        );
    }
}
