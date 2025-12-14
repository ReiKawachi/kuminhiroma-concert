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
      class-activator.php
      admin/
        group-admin.php
        concert-list.php
      hooks/
        concert-hooks.php
        group-hooks.php
```

## 機能概要（MVP）
- カスタム投稿タイプ：
  - concert（コンサート回）
  - group（出演者）
  - report（レポート）
- タクソノミー：
  - fiscal_year（年度）— concert に紐付け
- フロント側のショートコード出力は WPCode 等の外部実装に委ねており、プラグイン側にはショートコード登録やテンプレートを持たせていません。

## ACFフィールドと自動計算
- `concert_fiscal_year`（開催年度／4月始まり。4〜12月はその年度、1〜3月は年度+1で日付計算。管理画面の選択肢はサイトの現在年から+2年までを自動生成し、範囲外の既存値も一時的に選択肢へ追加されます）
- `concert_month`（開催月）
- `held_date`（開催日）：保存時に年度・月から第3土曜日を自動計算して上書き。保存形式は `Ymd` 固定。管理画面では読み取り専用で表示されます。
- `slot1_group` / `slot2_group`（出演枠）：`group`投稿を参照。未設定でも表示エラーになりません。
- `concert_note`（公開用備考）
- `concert_admin_note`（非公開備考）
- `group_name`（出演団体名。保存時にタイトル・スラッグへ反映）

`concert`投稿のタイトルは保存時に `held_date` から自動整形され、`【YYYY年n月j日】出演者1 ／ 出演者2` の形式になります（出演者が片方のみの場合は日付＋該当出演者のみ）。スラッシュ前後は半角スペース固定です。
`concert_fiscal_year` の選択肢は描画直前（`acf/prepare_field`）で当年〜当年+2に強制上書きします。

`held_date` は管理画面で編集不可（確認用のみ）とし、今後も ACF フィールドとして運用する前提です。`concert_fiscal_year` と `concert_month` は4月始まりの年度として解釈され、例えば 2027年2月の開催は 2026年度として扱います。タイトルは自動計算された開催日と出演者名から整形され、スラッグは既存値を保持します。

`group`投稿はブロックエディタを無効化し、タイトル／本文を非表示にした上で、`group_name` を元にタイトル・スラッグを自動生成します（どちらも団体名ベース）。SWELLのカスタムコード系メタボックスは、テーマが有効な場合のみ安全に非表示化します。出演者一覧では「ジャンル（ACF: genre）」「カテゴリー（紐付くタクソノミー）」「担当者（contact_name）」カラムを表示します。

## 使い方
1. プラグインを有効化する。
2. ACFフィールドグループ（`concert_fiscal_year` / `concert_month` / `held_date` / `slot1_group` / `slot2_group` / `concert_note` / `concert_admin_note` など）をコンサート投稿タイプに紐付ける。
3. コンサート投稿を保存すると、年度・月から開催日（第3土曜日）が自動算出され `held_date` に反映されます。
4. 管理画面のコンサート一覧には「開催年度」「開催月」で絞り込めるフィルターが追加されています（標準の「すべての日付」ドロップダウンは非表示）。また、同一覧には「開催年度」「開催月」「開催日」のカラムが追加され、開催日は `Ymd` 形式の保存値を基に「YYYY年n月j日」で表示されます（デフォルトソートは開催日昇順、meta_value_num）。

## 今後の追加予定
- スケジュール一覧表示
- 出演者情報の一覧・詳細表示
- レポート一覧／アーカイブ
- CSVインポートによる出演者・スケジュール管理
