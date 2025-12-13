<?php
// 直接アクセス防止。
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * コンサート投稿のACF連携や日付自動計算を担当するクラス。
 */
class KHC_Concert_Hooks {
    /**
     * 再帰的な保存呼び出しを防ぐフラグ。
     *
     * @var bool
     */
    private static $is_processing = false;

    /**
     * コンサート関連のフックを登録する。
     */
    public function register_hooks() {
        add_action( 'acf/save_post', [ $this, 'update_held_date_on_save' ], 20 );
        add_action( 'save_post_concert', [ $this, 'update_held_date_on_save' ], 20 );
        add_filter( 'acf/load_field/name=held_date', [ $this, 'make_held_date_readonly' ] );
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

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
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

        $current_value       = KHC_Helpers::get_field_value( $resolved_post_id, 'held_date' );
        $performer_names     = $this->build_performer_names( $resolved_post_id );
        $held_date           = KHC_Helpers::parse_held_date( $held_date_value, $resolved_post_id );
        $date_label          = $held_date instanceof DateTimeImmutable ? $held_date->format( 'Y年n月j日' ) : '';

        $new_title = $date_label ? '【' . $date_label . '】' : '';

        if ( ! empty( $performer_names ) ) {
            $new_title .= ' ' . implode( ' ', $performer_names );
        }

        $should_update_meta  = (string) $current_value !== (string) $held_date_value;
        $should_update_title = ! empty( $new_title ) && $new_title !== get_the_title( $resolved_post_id );

        if ( ! $should_update_meta && ! $should_update_title ) {
            return;
        }

        self::$is_processing = true;
        remove_action( 'acf/save_post', [ $this, 'update_held_date_on_save' ], 20 );
        remove_action( 'save_post_concert', [ $this, 'update_held_date_on_save' ], 20 );

        if ( $should_update_meta ) {
            if ( function_exists( 'update_field' ) ) {
                update_field( KHC_Helpers::FIELD_KEYS['held_date'], $held_date_value, $resolved_post_id );
            } else {
                update_post_meta( $resolved_post_id, KHC_Helpers::FIELD_KEYS['held_date'], $held_date_value );
            }
        }

        if ( $should_update_title ) {
            wp_update_post(
                [
                    'ID'         => $resolved_post_id,
                    'post_title' => $new_title,
                ]
            );
        }

        add_action( 'acf/save_post', [ $this, 'update_held_date_on_save' ], 20 );
        add_action( 'save_post_concert', [ $this, 'update_held_date_on_save' ], 20 );
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

    /**
     * タイトル整形用に出演者名を取得する。
     *
     * @param int $concert_id コンサート投稿ID。
     * @return string[]
     */
    private function build_performer_names( $concert_id ) {
        $names = [];

        foreach ( [ 'slot1_group', 'slot2_group' ] as $slot_key ) {
            $group_value = KHC_Helpers::get_field_value( $concert_id, $slot_key );
            $group_id    = null;

            if ( $group_value instanceof WP_Post ) {
                $group_id = $group_value->ID;
            } elseif ( is_numeric( $group_value ) ) {
                $group_id = (int) $group_value;
            }

            if ( ! $group_id ) {
                continue;
            }

            $group_name = KHC_Helpers::get_group_field_value( $group_id, 'group_name' );

            if ( empty( $group_name ) ) {
                $group_name = get_the_title( $group_id );
            }

            if ( ! empty( $group_name ) ) {
                $names[] = $group_name;
            }
        }

        return $names;
    }
}
