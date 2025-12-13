<?php
// 直接アクセス防止。
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ACF連携の保存処理や管理画面調整をまとめるクラス。
 */
class KHC_ACF_Hooks {
    /**
     * 再帰的な保存呼び出しを防ぐフラグ。
     *
     * @var bool
     */
    private static $is_processing = false;

    /**
     * ACF関連のフックを登録する。
     */
    public function register_hooks() {
        add_action( 'acf/save_post', [ $this, 'update_held_date_on_save' ], 20 );
        add_action( 'save_post_concert', [ $this, 'update_held_date_on_save' ], 20 );
        add_filter( 'acf/load_field/name=held_date', [ $this, 'make_held_date_readonly' ] );
        add_filter( 'acf/prepare_field/name=concert_fiscal_year', [ $this, 'prepare_concert_fiscal_year_choices' ], 1000 );
        $this->register_concert_fiscal_year_key_filter();
    }

    /**
     * 保存時に開催日を自動計算して保存する。
     *
     * @param int|string $post_id 保存対象の投稿ID。
     */
    public function update_held_date_on_save( $post_id ) {
        if ( self::$is_processing ) {
            return;
        }

        $resolved_post_id = $this->normalize_post_id( $post_id );

        if ( ! $resolved_post_id || 'concert' !== get_post_type( $resolved_post_id ) ) {
            return;
        }

        if ( wp_is_post_autosave( $resolved_post_id ) || wp_is_post_revision( $resolved_post_id ) ) {
            return;
        }

        $fiscal_year = KHC_Helpers::get_field_value( $resolved_post_id, 'fiscal_year' );
        $month       = KHC_Helpers::get_field_value( $resolved_post_id, 'month' );

        if ( '' === $fiscal_year || '' === $month ) {
            return;
        }

        $held_date_value = KHC_Helpers::build_held_date_value( (int) $fiscal_year, (int) $month, $resolved_post_id );

        if ( ! $held_date_value ) {
            return;
        }

        self::$is_processing = true;

        if ( function_exists( 'update_field' ) ) {
            update_field( KHC_Helpers::FIELD_KEYS['held_date'], $held_date_value, $resolved_post_id );
        } else {
            update_post_meta( $resolved_post_id, KHC_Helpers::FIELD_KEYS['held_date'], $held_date_value );
        }

        self::$is_processing = false;
    }

    /**
     * held_date フィールドを管理画面で編集不可にする。
     *
     * @param array $field ACFフィールド設定。
     * @return array
     */
    public function make_held_date_readonly( $field ) {
        $field['readonly'] = true;
        $field['disabled'] = true;

        return $field;
    }

    /**
     * 開催年度セレクトの選択肢を当年＋2年まで動的に設定する。
     * 既存値が範囲外の場合は表示崩れを防ぐために一時的に選択肢へ追加する。
     *
     * @param array $field ACFフィールド設定。
     * @return array
     */
    public function prepare_concert_fiscal_year_choices( $field ) {
        $now_timezone = wp_timezone();
        $current_year = (int) wp_date( 'Y', current_time( 'timestamp' ), $now_timezone );

        $field['choices'] = [];

        for ( $offset = 0; $offset <= 2; $offset++ ) {
            $year                     = $current_year + $offset;
            $field['choices'][ $year ] = (string) $year;
        }

        if ( ! empty( $field['value'] ) && ! isset( $field['choices'][ $field['value'] ] ) ) {
            $field['choices'][ $field['value'] ] = (string) $field['value'];
        }

        $field['default_value'] = $current_year;

        return $field;
    }

    /**
     * 開催年度フィールドのキー取得に成功した場合、キー指定の load_field でも処理を適用する。
     */
    private function register_concert_fiscal_year_key_filter() {
        if ( ! function_exists( 'acf_get_field' ) ) {
            return;
        }

        $field = acf_get_field( KHC_Helpers::FIELD_KEYS['fiscal_year'] );

        if ( ! is_array( $field ) || empty( $field['key'] ) ) {
            return;
        }

        add_filter( 'acf/prepare_field/key=' . $field['key'], [ $this, 'prepare_concert_fiscal_year_choices' ], 1000 );
    }

    /**
     * acf/save_post から渡される文字列IDを整数に揃える。
     *
     * @param int|string $post_id ACFが渡すポストID表現。
     * @return int|null
     */
    private function normalize_post_id( $post_id ) {
        if ( is_numeric( $post_id ) ) {
            return (int) $post_id;
        }

        if ( is_string( $post_id ) && 0 === strpos( $post_id, 'post_' ) ) {
            return (int) str_replace( 'post_', '', $post_id );
        }

        return null;
    }
}
