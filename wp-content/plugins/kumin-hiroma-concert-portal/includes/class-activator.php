<?php
// 直接アクセス防止。
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * プラグイン有効化時の処理をまとめるクラス。
 */
class KHC_Activator {
    /**
     * 有効化時にリライトルールを再生成する。
     */
    public static function activate() {
        flush_rewrite_rules();
    }
}
