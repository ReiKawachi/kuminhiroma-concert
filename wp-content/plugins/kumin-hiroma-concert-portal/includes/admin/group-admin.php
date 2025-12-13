<?php
// 直接アクセス防止。
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * コンサート・出演者の管理画面を入力専用に整えるクラス。
 */
class KHC_Group_Admin {
    /**
     * 管理画面調整を適用する投稿タイプ。
     *
     * @var string[]
     */
    private $target_post_types = [ 'group', 'concert' ];

    /**
     * 管理画面向けのフックを登録する。
     */
    public function register_admin_hooks() {
        add_filter( 'use_block_editor_for_post_type', [ $this, 'disable_block_editor' ], 10, 2 );
        add_action( 'admin_head-post.php', [ $this, 'hide_title_and_editor' ] );
        add_action( 'admin_head-post-new.php', [ $this, 'hide_title_and_editor' ] );
        add_action( 'admin_init', [ $this, 'disable_excerpt_support' ] );
        add_action( 'do_meta_boxes', [ $this, 'remove_custom_meta_boxes' ], 20, 2 );
        add_filter( 'manage_edit-group_columns', [ $this, 'add_group_columns' ] );
        add_action( 'manage_group_posts_custom_column', [ $this, 'render_group_columns' ], 10, 2 );
    }

    /**
     * 対象投稿タイプではブロックエディタを無効化する。
     *
     * @param bool   $use_block_editor デフォルトの可否。
     * @param string $post_type        対象投稿タイプ。
     * @return bool
     */
    public function disable_block_editor( $use_block_editor, $post_type ) {
        if ( in_array( $post_type, $this->target_post_types, true ) ) {
            return false;
        }

        return $use_block_editor;
    }

    /**
     * 対象投稿編集画面でタイトル入力欄と本文欄を非表示にする。
     */
    public function hide_title_and_editor() {
        $screen = get_current_screen();

        if ( ! $screen || ! in_array( $screen->post_type, $this->target_post_types, true ) ) {
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
     * 対象投稿タイプで不要なメタボックスを隠す。
     *
     * @param string $post_type 投稿タイプ。
     * @param string $context   表示コンテキスト。
     */
    public function remove_custom_meta_boxes( $post_type, $context ) {
        if ( ! in_array( $post_type, $this->target_post_types, true ) ) {
            return;
        }

        remove_meta_box( 'slugdiv', $post_type, 'normal' );
        remove_meta_box( 'postexcerpt', $post_type, 'normal' );

        $seo_box_ids = [ 'seo_meta', 'ssp_meta_box', 'ssp_metabox', 'seo_simple_pack', 'seo_simple_pack_meta_box' ];
        foreach ( $seo_box_ids as $box_id ) {
            remove_meta_box( $box_id, $post_type, 'normal' );
            remove_meta_box( $box_id, $post_type, 'advanced' );
        }

        $wpcode_box_ids = [ 'wpcode-snippets-meta-box', 'wpcode_page_scripts' ]; // 残る場合はIDを追加。
        foreach ( $wpcode_box_ids as $box_id ) {
            remove_meta_box( $box_id, $post_type, 'normal' );
            remove_meta_box( $box_id, $post_type, 'advanced' );
        }

        if ( defined( 'SWELL_VERSION' ) ) {
            global $wp_meta_boxes;

            if ( isset( $wp_meta_boxes[ $post_type ] ) ) {
                foreach ( $wp_meta_boxes[ $post_type ] as $ctx => $priorities ) {
                    foreach ( $priorities as $priority => $boxes ) {
                        foreach ( $boxes as $box_id => $box ) {
                            if ( false !== strpos( $box_id, 'swell' ) ) {
                                remove_meta_box( $box_id, $post_type, $ctx );
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * 抜粋入力を無効化する。
     */
    public function disable_excerpt_support() {
        foreach ( $this->target_post_types as $post_type ) {
            remove_post_type_support( $post_type, 'excerpt' );
        }
    }

    /**
     * 出演者一覧のカラム構成を調整する。
     *
     * @param array $columns 既存カラム。
     * @return array
     */
    public function add_group_columns( $columns ) {
        $new_columns = [];

        foreach ( $columns as $key => $label ) {
            if ( 'cb' === $key ) {
                $new_columns[ $key ] = $label;
                continue;
            }

            if ( 'title' === $key ) {
                $new_columns['title']               = $label;
                $new_columns['khc_group_genre']     = 'ジャンル';
                $new_columns['khc_group_category']  = 'カテゴリー';
                $new_columns['author']              = '担当者';
                continue;
            }

            if ( 'author' === $key ) {
                continue;
            }

            $new_columns[ $key ] = $label;
        }

        if ( ! isset( $new_columns['author'] ) ) {
            $new_columns['author'] = '担当者';
        }

        return $new_columns;
    }

    /**
     * 追加カラムの内容を描画する。
     *
     * @param string $column  カラムスラッグ。
     * @param int    $post_id 投稿ID。
     */
    public function render_group_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'khc_group_genre':
                $genre = KHC_Helpers::get_group_field_value( $post_id, 'genre' );
                echo esc_html( $genre ? $genre : '—' );
                break;
            case 'khc_group_category':
                $category = KHC_Helpers::get_group_field_value( $post_id, 'group_category' );

                if ( empty( $category ) ) {
                    echo '—';
                    break;
                }

                if ( is_array( $category ) ) {
                    $category = implode( ', ', array_filter( array_map( 'strval', $category ) ) );
                }

                echo esc_html( $category );
                break;
        }
    }
}
