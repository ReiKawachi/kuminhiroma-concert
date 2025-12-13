# Kumin Hiroma Concert Portal
横浜市戸塚区「区民広間コンサート」の告知／予定／レポート管理を行うWordPress向けカスタムプラグインです。SWELL環境を想定しつつ、テーマに依存しすぎない汎用構成を目指しています。

## ディレクトリ構成
```
wp-content/
  plugins/
    kumin-hiroma-concert-portal/
      kumin-hiroma-concert-portal.php
      includes/
        class-posttypes.php
        class-taxonomies.php
        class-helpers.php
        class-shortcodes.php
        class-activator.php
      templates/
        next-concert.php
      assets/
        frontend.css
```

## 機能概要（MVP）
- カスタム投稿タイプ：
  - concert（コンサート回）
  - group（出演者）
  - report（レポート）
- タクソノミー：
  - fiscal_year（年度）— concert に紐付け
- ショートコード：
  - `[khc_next_concert]` 今日以降で最も近いコンサート回を1件表示

## 仮メタキー（後にACFフィールドへ置換予定）
- `khc_held_date`（Y-m-d）
- `khc_start_time`（H:i）
- `khc_end_time`（H:i）
- `khc_venue`（会場名）
- `khc_round_no`（開催回）

これらは MVP 時点での仮フィールドであり、将来 Advanced Custom Fields で管理する前提です。

## 使い方
1. プラグインを有効化する。
2. 必要に応じてカスタム投稿タイプ「コンサート」に上記メタキーで開催情報を登録する。
3. 投稿や固定ページにショートコード `[khc_next_concert]` を挿入すると、次回開催予定が表示されます。

## 今後の追加予定
- スケジュール一覧表示
- 出演者情報の一覧・詳細表示
- レポート一覧／アーカイブ
- CSVインポートによる出演者・スケジュール管理
