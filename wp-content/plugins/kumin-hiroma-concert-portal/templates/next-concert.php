<?php
// 直接アクセス防止。
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$held_date = KHC_Helpers::get_meta_value( $next_concert->ID, 'held_date' );
$start_time = KHC_Helpers::get_meta_value( $next_concert->ID, 'start_time' );
$end_time   = KHC_Helpers::get_meta_value( $next_concert->ID, 'end_time' );
$venue      = KHC_Helpers::get_meta_value( $next_concert->ID, 'venue' );
$round_no   = KHC_Helpers::get_meta_value( $next_concert->ID, 'round_no' );
?>
<section class="l-next-concert" aria-label="次回コンサート">
  <article class="c-next-concert" aria-labelledby="khc-next-concert-title">
    <header class="c-next-concert__header">
      <p class="c-next-concert__eyebrow">次回開催</p>
      <h2 id="khc-next-concert-title" class="c-next-concert__title"><?php echo esc_html( get_the_title( $next_concert ) ); ?></h2>
      <?php if ( $round_no ) : ?>
      <p class="c-next-concert__round">第<?php echo esc_html( $round_no ); ?>回</p>
      <?php endif; ?>
    </header>
    <dl class="c-next-concert__details">
      <?php if ( $held_date ) : ?>
      <div class="c-next-concert__detail">
        <dt class="c-next-concert__label">開催日</dt>
        <dd class="c-next-concert__value">
          <time datetime="<?php echo esc_attr( $held_date ); ?>"><?php echo esc_html( date_i18n( 'Y年n月j日', strtotime( $held_date ) ) ); ?></time>
        </dd>
      </div>
      <?php endif; ?>
      <?php if ( $start_time || $end_time ) : ?>
      <div class="c-next-concert__detail">
        <dt class="c-next-concert__label">時間</dt>
        <dd class="c-next-concert__value">
          <?php if ( $start_time ) : ?>
          <time datetime="<?php echo esc_attr( $start_time ); ?>"><?php echo esc_html( $start_time ); ?></time>
          <?php endif; ?>
          <?php if ( $start_time && $end_time ) : ?>
          <span class="u-separator">〜</span>
          <?php endif; ?>
          <?php if ( $end_time ) : ?>
          <time datetime="<?php echo esc_attr( $end_time ); ?>"><?php echo esc_html( $end_time ); ?></time>
          <?php endif; ?>
        </dd>
      </div>
      <?php endif; ?>
      <?php if ( $venue ) : ?>
      <div class="c-next-concert__detail">
        <dt class="c-next-concert__label">会場</dt>
        <dd class="c-next-concert__value"><?php echo esc_html( $venue ); ?></dd>
      </div>
      <?php endif; ?>
    </dl>
    <p class="c-next-concert__excerpt"><?php echo esc_html( get_the_excerpt( $next_concert ) ); ?></p>
    <a class="c-next-concert__link" href="<?php echo esc_url( get_permalink( $next_concert ) ); ?>">詳細を見る</a>
  </article>
</section>
