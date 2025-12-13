<?php
// 直接アクセス防止。
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * カスタム投稿タイプ登録を担当するクラス。
 */
class KHC_Posttypes {
    /**
     * 各カスタム投稿タイプを登録する。
     */
    public function register_post_types() {
        $this->register_concert_post_type();
        $this->register_group_post_type();
        $this->register_report_post_type();
    }

    /**
     * コンサート回を表す投稿タイプ。
     */
    private function register_concert_post_type() {
        $labels = [
            'name'               => 'コンサート',
            'singular_name'      => 'コンサート',
            'add_new'            => '新規追加',
            'add_new_item'       => '新規コンサートを追加',
            'edit_item'          => 'コンサートを編集',
            'new_item'           => '新規コンサート',
            'view_item'          => 'コンサートを表示',
            'search_items'       => 'コンサートを検索',
            'not_found'          => 'コンサートが見つかりません',
            'not_found_in_trash' => 'ゴミ箱にコンサートはありません',
            'all_items'          => 'コンサート一覧',
            'archives'           => 'コンサートアーカイブ',
        ];

        register_post_type(
            'concert',
            [
                'labels'       => $labels,
                'public'       => true,
                'show_in_rest' => true,
                'has_archive'  => true,
                'menu_icon'    => 'dashicons-microphone',
                'rewrite'      => [ 'slug' => 'kumin-concert' ],
                'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
            ]
        );
    }

    /**
     * 出演者を表す投稿タイプ。
     */
    private function register_group_post_type() {
        $labels = [
            'name'               => '出演者',
            'singular_name'      => '出演者',
            'add_new'            => '新規追加',
            'add_new_item'       => '新規出演者を追加',
            'edit_item'          => '出演者を編集',
            'new_item'           => '新規出演者',
            'view_item'          => '出演者を表示',
            'search_items'       => '出演者を検索',
            'not_found'          => '出演者が見つかりません',
            'not_found_in_trash' => 'ゴミ箱に出演者はありません',
            'all_items'          => '出演者一覧',
            'archives'           => '出演者アーカイブ',
        ];

        register_post_type(
            'group',
            [
                'labels'       => $labels,
                'public'       => true,
                'show_in_rest' => true,
                'has_archive'  => true,
                'menu_icon'    => 'dashicons-groups',
                'rewrite'      => [ 'slug' => 'kumin-group' ],
                'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
            ]
        );
    }

    /**
     * レポートを表す投稿タイプ。
     */
    private function register_report_post_type() {
        $labels = [
            'name'               => 'レポート',
            'singular_name'      => 'レポート',
            'add_new'            => '新規追加',
            'add_new_item'       => '新規レポートを追加',
            'edit_item'          => 'レポートを編集',
            'new_item'           => '新規レポート',
            'view_item'          => 'レポートを表示',
            'search_items'       => 'レポートを検索',
            'not_found'          => 'レポートが見つかりません',
            'not_found_in_trash' => 'ゴミ箱にレポートはありません',
            'all_items'          => 'レポート一覧',
            'archives'           => 'レポートアーカイブ',
        ];

        register_post_type(
            'report',
            [
                'labels'       => $labels,
                'public'       => true,
                'show_in_rest' => true,
                'has_archive'  => true,
                'menu_icon'    => 'dashicons-media-document',
                'rewrite'      => [ 'slug' => 'kumin-report' ],
                'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
            ]
        );
    }
}
