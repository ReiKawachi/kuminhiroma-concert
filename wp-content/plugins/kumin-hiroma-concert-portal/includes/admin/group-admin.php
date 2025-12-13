<?php
// 直接アクセス防止。
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 出演者（group投稿タイプ）の管理画面を整えるクラス。
 */
class KHC_Group_Admin {
    /**
     * 管理画面向けのフックを登録する。
     */
    public function register_admin_hooks() {
        add_filter( 'use_block_editor_for_post_type', [ $this, 'disable_block_editor' ], 10, 2 );
        add_action( 'admin_head-post.php', [ $this, 'hide_group_title_and_editor' ] );
        add_action( 'admin_head-post-new.php', [ $this, 'hide_group_title_and_editor' ] );
        add_action( 'do_meta_boxes', [ $this, 'remove_swell_meta_boxes' ], 20, 2 );
    }

    /**
     * group投稿タイプではブロックエディタを無効化する。
     *
     * @param bool   $use_block_editor デフォルトの可否。
     * @param string $post_type        対象投稿タイプ。
     * @return bool
     */
    public function disable_block_editor( $use_block_editor, $post_type ) {
        if ( 'group' === $post_type ) {
            return false;
        }

        return $use_block_editor;
    }

    /**
     * group投稿編集画面でタイトル入力欄と本文欄を非表示にする。
     */
    public function hide_group_title_and_editor() {
        $screen = get_current_screen();

        if ( ! $screen || 'group' !== $screen->post_type ) {
            return;
        }
        ?>
        <style>
            #titlediv,
            #postdivrich {
                display: none !important;
            }
        </style>
        <?php
    }

    /**
     * SWELLが有効な場合、group投稿タイプのカスタムコード系メタボックスを隠す。
     *
     * @param string $post_type 投稿タイプ。
     * @param string $context   表示コンテキスト。
     */
    public function remove_swell_meta_boxes( $post_type, $context ) {
        if ( 'group' !== $post_type ) {
            return;
        }

        if ( ! defined( 'SWELL_VERSION' ) ) {
            return;
        }

        global $wp_meta_boxes;

        if ( empty( $wp_meta_boxes['group'] ) ) {
            return;
        }

        foreach ( $wp_meta_boxes['group'] as $ctx => $priorities ) {
            foreach ( $priorities as $priority => $boxes ) {
                foreach ( $boxes as $box_id => $box ) {
                    if ( false !== strpos( $box_id, 'swell' ) ) {
                        remove_meta_box( $box_id, 'group', $ctx );
                    }
                }
            }
        }
    }
}
