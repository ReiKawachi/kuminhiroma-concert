<?php
// 直接アクセス防止。
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * タクソノミー登録を担当するクラス。
 */
class KHC_Taxonomies {
    /**
     * タクソノミー登録をまとめて実行する。
     */
    public function register_taxonomies() {
        $this->register_fiscal_year_taxonomy();
    }

    /**
     * コンサートの年度を管理するタクソノミー。
     */
    private function register_fiscal_year_taxonomy() {
        $labels = [
            'name'              => '年度',
            'singular_name'     => '年度',
            'search_items'      => '年度を検索',
            'all_items'         => 'すべての年度',
            'edit_item'         => '年度を編集',
            'update_item'       => '年度を更新',
            'add_new_item'      => '新しい年度を追加',
            'new_item_name'     => '新しい年度名',
            'menu_name'         => '年度',
            'view_item'         => '年度を表示',
            'popular_items'     => '人気の年度',
            'separate_items_with_commas' => '複数ある場合はカンマで区切ってください',
            'add_or_remove_items'        => '年度を追加または削除',
            'choose_from_most_used'      => 'よく使われる年度から選択',
        ];

        register_taxonomy(
            'fiscal_year',
            [ 'concert' ],
            [
                'labels'            => $labels,
                'hierarchical'      => false,
                'public'            => true,
                'show_admin_column' => true,
                'show_in_rest'      => true,
                'rewrite'           => [ 'slug' => 'fiscal-year' ],
            ]
        );
    }
}
