<?php
// 直接アクセス防止。
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 出演者（group）の保存処理をまとめるクラス。
 */
class KHC_Group_Hooks {
    /**
     * 再入防止フラグ。
     *
     * @var bool
     */
    private static $is_processing = false;

    /**
     * group関連の保存フックを登録する。
     */
    public function register_hooks() {
        add_action( 'acf/save_post', [ $this, 'update_group_title_and_slug' ], 20 );
    }

    /**
     * ACF保存完了後にタイトルとスラッグを自動生成する。
     *
     * @param int|string $post_id 保存対象の投稿ID。
     */
    public function update_group_title_and_slug( $post_id ) {
        if ( self::$is_processing ) {
            return;
        }

        $resolved_post_id = $this->normalize_post_id( $post_id );

        if ( ! $resolved_post_id || 'group' !== get_post_type( $resolved_post_id ) ) {
            return;
        }

        if ( wp_is_post_autosave( $resolved_post_id ) || wp_is_post_revision( $resolved_post_id ) ) {
            return;
        }

        $group_name = KHC_Helpers::get_group_field_value( $resolved_post_id, 'group_name' );

        if ( empty( $group_name ) ) {
            return;
        }

        $new_title = $group_name;
        $new_slug  = sanitize_title( $group_name );

        self::$is_processing = true;
        remove_action( 'acf/save_post', [ $this, 'update_group_title_and_slug' ], 20 );

        wp_update_post(
            [
                'ID'         => $resolved_post_id,
                'post_title' => $new_title,
                'post_name'  => $new_slug,
            ]
        );

        add_action( 'acf/save_post', [ $this, 'update_group_title_and_slug' ], 20 );
        self::$is_processing = false;
    }

    /**
     * acf/save_post から渡されるIDを数値に整形する。
     *
     * @param int|string $post_id acf/save_postの引数。
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
