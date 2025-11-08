<?php
$time_to = $settings['time_to'];
?>
<div class="pxl-countdown">
    <div class="pxl-countdown-container font-smooth" data-time="<?php echo esc_attr($time_to); ?>">
        <div class="time-item">
            <div class="time-item-inner">
                <div class="day inner-number"></div>
                <div class="inner-text"><?php echo esc_html__('Days', 'powerlegal') ?></div>
            </div>
        </div>
        <div class="time-item">
            <div class="time-item-inner">
                <div class="hour inner-number"></div>
                <div class="inner-text"><?php echo esc_html__('Hours', 'powerlegal') ?></div>
            </div>
        </div>
        <div class="time-item">
            <div class="time-item-inner">
                <div class="minute inner-number"></div>
                <div class="inner-text"><?php echo esc_html__('Minutes', 'powerlegal') ?></div>
            </div>
        </div>
        <div class="time-item">
            <div class="time-item-inner">
                <div class="second inner-number"></div>
                <div class="inner-text"><?php echo esc_html__('Seconds', 'powerlegal') ?></div>
            </div>
        </div>
    </div>
</div>