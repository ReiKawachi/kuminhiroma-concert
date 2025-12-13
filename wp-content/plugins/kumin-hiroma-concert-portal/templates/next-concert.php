<?php
// 直接アクセス防止。
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$held_date_value = KHC_Helpers::get_field_value( $next_concert->ID, 'held_date' );
$held_date       = KHC_Helpers::parse_held_date( $held_date_value, $next_concert->ID );
$slot1_group     = KHC_Helpers::get_field_value( $next_concert->ID, 'slot1_group' );
$slot2_group     = KHC_Helpers::get_field_value( $next_concert->ID, 'slot2_group' );
$note            = KHC_Helpers::get_field_value( $next_concert->ID, 'note' );

$performers = [];

if ( $slot1_group ) {
    $performers[] = [
        'time'  => KHC_Helpers::PERFORMANCE_TIME['slot1_group'],
        'entry' => $slot1_group,
    ];
}

if ( $slot2_group ) {
    $performers[] = [
        'time'  => KHC_Helpers::PERFORMANCE_TIME['slot2_group'],
        'entry' => $slot2_group,
    ];
}
?>
<section class="l-next-concert" aria-label="次回コンサート">
  <article class="c-next-concert" aria-labelledby="khc-next-concert-title">
    <header class="c-next-concert__header">
      <p class="c-next-concert__eyebrow">次回開催</p>
      <h2 id="khc-next-concert-title" class="c-next-concert__title"><?php echo esc_html( get_the_title( $next_concert ) ); ?></h2>
    </header>
    <dl class="c-next-concert__details">
      <?php if ( $held_date ) : ?>
      <div class="c-next-concert__detail">
        <dt class="c-next-concert__label">開催日</dt>
        <dd class="c-next-concert__value">
          <time datetime="<?php echo esc_attr( $held_date->format( 'Y-m-d' ) ); ?>"><?php echo esc_html( $held_date->format( 'Y年n月j日' ) ); ?></time>
        </dd>
      </div>
      <?php endif; ?>
      <div class="c-next-concert__detail">
        <dt class="c-next-concert__label">時間</dt>
        <dd class="c-next-concert__value">
          <time datetime="<?php echo esc_attr( KHC_Helpers::CONCERT_TIME['start'] ); ?>"><?php echo esc_html( KHC_Helpers::CONCERT_TIME['start'] ); ?></time>
          <span class="u-separator">〜</span>
          <time datetime="<?php echo esc_attr( KHC_Helpers::CONCERT_TIME['end'] ); ?>"><?php echo esc_html( KHC_Helpers::CONCERT_TIME['end'] ); ?></time>
        </dd>
      </div>
    </dl>
    <?php if ( $performers ) : ?>
    <section class="c-next-concert__performers" aria-label="出演者">
      <h3 class="c-next-concert__subheading">出演</h3>
      <ul class="c-next-concert__performer-list">
        <?php foreach ( $performers as $performer ) :
            $entry      = $performer['entry'];
            $performer_id = is_object( $entry ) ? $entry->ID : (int) $entry;
            $name       = is_object( $entry ) ? $entry->post_title : get_the_title( $performer_id );
            $permalink  = get_permalink( $performer_id );
        ?>
        <li class="c-next-concert__performer">
          <div class="c-next-concert__performer-time">
            <time datetime="<?php echo esc_attr( $performer['time'] ); ?>"><?php echo esc_html( $performer['time'] ); ?></time>
          </div>
          <div class="c-next-concert__performer-body">
            <?php if ( $permalink ) : ?>
            <a class="c-next-concert__performer-name" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $name ); ?></a>
            <?php else : ?>
            <span class="c-next-concert__performer-name"><?php echo esc_html( $name ); ?></span>
            <?php endif; ?>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>
    <p class="c-next-concert__excerpt"><?php echo esc_html( get_the_excerpt( $next_concert ) ); ?></p>
    <?php if ( $note ) : ?>
    <p class="c-next-concert__note"><?php echo esc_html( $note ); ?></p>
    <?php endif; ?>
    <a class="c-next-concert__link" href="<?php echo esc_url( get_permalink( $next_concert ) ); ?>">詳細を見る</a>
  </article>
</section>
