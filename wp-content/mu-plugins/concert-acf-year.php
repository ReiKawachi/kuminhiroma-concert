<?php
/**
 * 強制的に開催年度選択肢を当年〜当年+2の3つに制御するMUプラグイン。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'khcp_mu_prepare_concert_fiscal_year_choices' ) ) {
    /**
     * ACFの開催年度セレクトを描画直前に上書きする。
     *
     * @param array $field ACFフィールド設定。
     * @return array
     */
    function khcp_mu_prepare_concert_fiscal_year_choices( $field ) {
        $timezone     = wp_timezone();
        $current_year = (int) wp_date( 'Y', current_time( 'timestamp' ), $timezone );

        $field['choices'] = [];

        for ( $offset = 0; $offset <= 2; $offset++ ) {
            $year                      = $current_year + $offset;
            $field['choices'][ $year ] = (string) $year;
        }

        if ( ! empty( $field['value'] ) && ! isset( $field['choices'][ $field['value'] ] ) ) {
            $field['choices'][ $field['value'] ] = (string) $field['value'];
        }

        $field['default_value'] = $current_year;

        return $field;
    }
}

add_filter( 'acf/prepare_field/name=concert_fiscal_year', 'khcp_mu_prepare_concert_fiscal_year_choices', 1000 );

add_action( 'acf/init', function() {
    if ( ! function_exists( 'acf_get_field' ) ) {
        return;
    }

    $field = acf_get_field( 'concert_fiscal_year' );

    if ( is_array( $field ) && ! empty( $field['key'] ) ) {
        add_filter( 'acf/prepare_field/key=' . $field['key'], 'khcp_mu_prepare_concert_fiscal_year_choices', 1000 );
    }
});
