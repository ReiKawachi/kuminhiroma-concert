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
      admin/
        group-admin.php
      hooks/
        concert-hooks.php
        group-hooks.php
      shortcodes/
        next-concert.php
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
  - `[khc_next_concert]` 今日以降で最も近いコンサート回を1件表示（既存互換）
  - `[next_concert_html]` 今日以降で最も近いコンサート回を1件表示

## ACFフィールドと自動計算
- `concert_fiscal_year`（開催年度／4月始まり。4〜12月はその年度、1〜3月は年度+1で日付計算）
- `concert_month`（開催月）
- `held_date`（開催日）：保存時に年度・月から第3土曜日を自動計算して上書き。ACFの返り値形式（Ymd or Y-m-d）に合わせて保存します。管理画面では読み取り専用で表示されます。
- `slot1_group` / `slot2_group`（出演枠）：`group`投稿を参照。未設定でも表示エラーになりません。
- `concert_note`（公開用備考）
- `concert_admin_note`（非公開備考）
- `group_name`（出演団体名。保存時にタイトル・スラッグへ反映）

`held_date` は管理画面で編集不可（確認用のみ）とし、今後も ACF フィールドとして運用する前提です。`concert_fiscal_year` と `concert_month` は4月始まりの年度として解釈され、例えば 2027年2月の開催は 2026年度として扱います。

`group`投稿はブロックエディタを無効化し、タイトル／本文を非表示にした上で、`group_name` を元に`【YYYY年n月j日】団体名`の形式でタイトルを自動生成します（スラッグも団体名から生成）。SWELLのカスタムコード系メタボックスは、テーマが有効な場合のみ安全に非表示化します。

## 使い方
1. プラグインを有効化する。
2. ACFフィールドグループ（`concert_fiscal_year` / `concert_month` / `held_date` / `slot1_group` / `slot2_group` / `concert_note` / `concert_admin_note` など）をコンサート投稿タイプに紐付ける。
3. コンサート投稿を保存すると、年度・月から開催日（第3土曜日）が自動算出され `held_date` に反映されます。
4. 投稿や固定ページにショートコード `[khc_next_concert]` または `[next_concert_html]` を挿入すると、次回開催予定が表示されます。開催時間は固定で 12:00–13:00、出演枠は 12:00– / 12:30– が表示されます。

## 今後の追加予定
- スケジュール一覧表示
- 出演者情報の一覧・詳細表示
- レポート一覧／アーカイブ
- CSVインポートによる出演者・スケジュール管理
