<?php
// 直接アクセス防止。
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'khcp_calculate_third_saturday' ) ) {
    /**
     * 指定年月の第3土曜日を Ymd 形式で返す。
     *
     * @param int $year  西暦年。
     * @param int $month 月。
     * @return string|null
     */
    function khcp_calculate_third_saturday( $year, $month ) {
        try {
            $first_of_month = new DateTimeImmutable(
                sprintf( '%04d-%02d-01', (int) $year, (int) $month ),
                wp_timezone()
            );
            $third_saturday = $first_of_month->modify( 'third saturday' );

            if ( ! $third_saturday ) {
                return null;
            }

            return $third_saturday->format( 'Ymd' );
        } catch ( Exception $e ) {
            return null;
        }
    }
}

/**
 * ショートコードやテンプレートから利用する補助関数群。
 */
class KHC_Helpers {
    /**
     * 開催日を保存する際のフォーマット。
     */
    public const HELD_DATE_FORMAT = 'Ymd';

    /**
     * 開催情報に用いるACFフィールド名をまとめる。
     */
    public const FIELD_KEYS = [
        'held_date'   => 'held_date',
        'fiscal_year' => 'concert_fiscal_year',
        'month'       => 'concert_month',
        'slot1_group' => 'slot1_group',
        'slot2_group' => 'slot2_group',
        'note'        => 'concert_note',
        'admin_note'  => 'concert_admin_note',
    ];

    /**
     * 出演者管理に用いるフィールド名をまとめる。
     */
    public const GROUP_FIELD_KEYS = [
        'group_name' => 'group_name',
        'genre'      => 'genre',
        'desc'       => 'desc',
        'photo'      => 'photo',
        'songs'      => 'songs',
        'group_category' => 'group_category',
        'contact_name'   => 'contact_name',
    ];

    /**
     * コンサート全体の固定時間。
     */
    public const CONCERT_TIME = [
        'start' => '12:00',
        'end'   => '13:00',
    ];

    /**
     * 出演枠ごとの固定開始時刻。
     */
    public const PERFORMANCE_TIME = [
        'slot1_group' => '12:00',
        'slot2_group' => '12:30',
    ];

    /**
     * 今日以降で最も近いコンサート投稿を取得する。
     *
     * @return WP_Post|null
     */
    public static function get_next_concert() {
        $date_format     = self::HELD_DATE_FORMAT;
        $today           = wp_date( $date_format, time(), wp_timezone() );
        $held_date_field = self::FIELD_KEYS['held_date'];
        $orderby         = 'meta_value_num';
        $meta_type       = 'NUMERIC';

        $query = new WP_Query(
            [
                'post_type'      => 'concert',
                'posts_per_page' => 1,
                'meta_key'       => $held_date_field,
                'meta_query'     => [
                    [
                        'key'     => $held_date_field,
                        'value'   => $today,
                        'compare' => '>=',
                        'type'    => $meta_type,
                    ],
                ],
                'orderby'        => $orderby,
                'order'          => 'ASC',
            ]
        );

        if ( ! $query->have_posts() ) {
            return null;
        }

        return $query->posts[0];
    }

    /**
     * ACFまたはメタから値を取得する。
     *
     * @param int    $post_id 投稿ID。
     * @param string $key_name self::FIELD_KEYS のキー名。
     * @return mixed
     */
    public static function get_field_value( $post_id, $key_name ) {
        if ( ! isset( self::FIELD_KEYS[ $key_name ] ) ) {
            return '';
        }

        $field_key = self::FIELD_KEYS[ $key_name ];

        if ( function_exists( 'get_field' ) ) {
            return get_field( $field_key, $post_id );
        }

        return get_post_meta( $post_id, $field_key, true );
    }

    /**
     * group用のACFまたはメタから値を取得する。
     *
     * @param int    $post_id  投稿ID。
     * @param string $key_name self::GROUP_FIELD_KEYS のキー名。
     * @return mixed
     */
    public static function get_group_field_value( $post_id, $key_name ) {
        if ( ! isset( self::GROUP_FIELD_KEYS[ $key_name ] ) ) {
            return '';
        }

        $field_key = self::GROUP_FIELD_KEYS[ $key_name ];

        if ( function_exists( 'get_field' ) ) {
            return get_field( $field_key, $post_id );
        }

        return get_post_meta( $post_id, $field_key, true );
    }

    /**
     * held_date フィールドの返却形式を取得する。
     *
     * @param int|string|null $post_id 投稿ID。
     * @return string
     */
    public static function get_held_date_format( $post_id = null ) {
        if ( function_exists( 'get_field_object' ) ) {
            $field_object = get_field_object( self::FIELD_KEYS['held_date'], $post_id );

            if ( is_array( $field_object ) && ! empty( $field_object['return_format'] ) ) {
                return $field_object['return_format'];
            }
        }

        return self::HELD_DATE_FORMAT;
    }

    /**
     * 4月始まりの年度と開催月から実年を計算する。
     *
     * @param int $fiscal_year 開催年度。
     * @param int $month       開催月。
     * @return int
     */
    public static function resolve_calendar_year( $fiscal_year, $month ) {
        return ( (int) $month <= 3 ) ? ( (int) $fiscal_year + 1 ) : (int) $fiscal_year;
    }

    /**
     * 指定年月の第3土曜日を求める。
     *
     * @param int $year  西暦。
     * @param int $month 月。
     * @return DateTimeImmutable|null
     */
    public static function calculate_third_saturday( $year, $month ) {
        $held_date_value = khcp_calculate_third_saturday( (int) $year, (int) $month );

        if ( ! $held_date_value ) {
            return null;
        }

        try {
            return new DateTimeImmutable( $held_date_value, wp_timezone() );
        } catch ( Exception $e ) {
            return null;
        }
    }

    /**
     * ACFの設定に合わせた形式で開催日を組み立てる。
     *
     * @param int $fiscal_year 開催年度。
     * @param int $month       開催月。
     * @param int|string|null $post_id 参照対象の投稿ID。
     * @return string|null
     */
    public static function build_held_date_value( $fiscal_year, $month, $post_id = null ) {
        $calendar_year = self::resolve_calendar_year( $fiscal_year, $month );
        $held_date     = self::calculate_third_saturday( $calendar_year, $month );

        if ( ! $held_date ) {
            return null;
        }

        return $held_date->format( self::HELD_DATE_FORMAT );
    }

    /**
     * held_date 文字列を DateTimeImmutable に変換する。
     *
     * @param string $held_date_value ACFの返り値。
     * @param int|string|null $post_id 投稿ID。
     * @return DateTimeImmutable|null
     */
    public static function parse_held_date( $held_date_value, $post_id = null ) {
        if ( empty( $held_date_value ) ) {
            return null;
        }

        $timezone = wp_timezone();
        $date     = DateTimeImmutable::createFromFormat( self::HELD_DATE_FORMAT, $held_date_value, $timezone );

        if ( $date instanceof DateTimeImmutable ) {
            return $date;
        }

        $format   = self::get_held_date_format( $post_id );
        $fallback = DateTimeImmutable::createFromFormat( 'Y-m-d', $held_date_value, $timezone );

        if ( $fallback instanceof DateTimeImmutable ) {
            return $fallback;
        }

        return DateTimeImmutable::createFromFormat( 'Ymd', $held_date_value, $timezone );
    }
}
