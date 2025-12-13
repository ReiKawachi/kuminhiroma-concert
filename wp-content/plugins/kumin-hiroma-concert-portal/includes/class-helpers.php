<?php
// 直接アクセス防止。
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ショートコードやテンプレートから利用する補助関数群。
 */
class KHC_Helpers {
    /**
     * 開催情報に用いる仮メタキー一覧（将来ACFに置き換え予定）。
     */
    public const META_KEYS = [
        'held_date' => 'khc_held_date',
        'start_time' => 'khc_start_time',
        'end_time' => 'khc_end_time',
        'venue' => 'khc_venue',
        'round_no' => 'khc_round_no',
    ];

    /**
     * 今日以降で最も近いコンサート投稿を取得する。
     *
     * @return WP_Post|null
     */
    public static function get_next_concert() {
        $today = current_time( 'Y-m-d' );

        $query = new WP_Query(
            [
                'post_type'      => 'concert',
                'posts_per_page' => 1,
                'meta_key'       => self::META_KEYS['held_date'],
                'meta_query'     => [
                    [
                        'key'     => self::META_KEYS['held_date'],
                        'value'   => $today,
                        'compare' => '>=',
                        'type'    => 'DATE',
                    ],
                ],
                'orderby'        => 'meta_value',
                'order'          => 'ASC',
            ]
        );

        if ( ! $query->have_posts() ) {
            return null;
        }

        return $query->posts[0];
    }

    /**
     * メタキーに対応する値を取得する。
     *
     * @param int    $post_id 投稿ID。
     * @param string $key_name self::META_KEYS のキー名。
     * @return string
     */
    public static function get_meta_value( $post_id, $key_name ) {
        if ( ! isset( self::META_KEYS[ $key_name ] ) ) {
            return '';
        }

        return get_post_meta( $post_id, self::META_KEYS[ $key_name ], true );
    }
}
