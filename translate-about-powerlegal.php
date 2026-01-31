<?php
/**
 * Translate "About Powerlegal" block to Ukrainian in _elementor_data.
 * Company: Адвокатське бюро «Тараса Молочко»
 *
 * Run: ddev exec "php /var/www/html/translate-about-powerlegal.php"
 * Backup DB before running.
 */

require_once __DIR__ . '/wp-load.php';

if (!defined('ABSPATH')) {
    die('Direct access not allowed');
}

$replace = [
    'ABOUT powerlegal' => 'ПРО АДВОКАТСЬКЕ БЮРО «ТАРАСА МОЛОЧКО»',
    'A Passion For Justice. The Experience For Win.' => 'Пристрасть до справедливості. Досвід заради перемоги.',
    'At Powerlegal Law Firm, we have designed a community of legal service providers (lawyers, paralegals, legal assistants, nurses and other business partners) who are passionate about providing exceptional legal services in a creative environment.' => 'В адвокатському бюро «Тараса Молочко» ми створили спільноту фахівців з надання юридичних послуг (адвокатів, параюристів, юридичних асистентів та інших партнерів), які прагнуть надавати якісні юридичні послуги в комфортному середовищі.',
    'Our culture nurtures and strives to achieve innovation, creativity, legal expertise and is client focused. Daily, we enhance our entrepreneurial environment to be flexible and supportive, allowing our lawyers' => 'Наша культура спрямована на інновації, креативність, юридичну експертизу та орієнтацію на клієнта. Щодня ми розвиваємо гнучке та підтримуюче середовище, що дає нашим адвокатам змогу ефективно представляти інтереси клієнтів.',
    'Call us 24/7. Let\'s start fighting together.' => 'Телефонуйте цілодобово. Давайте боротимось разом.',
    '+2 650-603-0553  Or  Book Appointment' => '+38 (050) 606-00-79  або  Записатися на прийом',
    '+2 650-603-0553' => '+38 (050) 606-00-79',
    'Or  Book Appointment' => 'або  Записатися на прийом',
    'Book Appointment' => 'Записатися на прийом',
    'Signature' => 'Підпис',
    'Harry Oliver,' => 'Тарас Молочко,',
    'CEO of Power Leagal Law Firm' => 'Керівник адвокатського бюро «Тараса Молочко»',
    'CEO of Power Legal Law Firm' => 'Керівник адвокатського бюро «Тараса Молочко»',
];

global $wpdb;
$rows = $wpdb->get_results(
    "SELECT meta_id, post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_data' "
    . "AND (meta_value LIKE '%ABOUT powerlegal%' OR meta_value LIKE '%Passion For Justice%' OR meta_value LIKE '%Experience For Win%' OR meta_value LIKE '%Harry Oliver%' OR meta_value LIKE '%650-603-0553%')"
);

if (empty($rows)) {
    echo "No matching _elementor_data found.\n";
    exit(0);
}

$updated = 0;
foreach ($rows as $row) {
    $new = $row->meta_value;
    $changed = false;
    foreach ($replace as $from => $to) {
        if (strpos($new, $from) !== false) {
            $new = str_replace($from, $to, $new);
            $changed = true;
        }
    }
    if (!$changed) continue;

    $r = $wpdb->update(
        $wpdb->postmeta,
        ['meta_value' => $new],
        ['meta_id' => $row->meta_id]
    );
    if ($r !== false) {
        $updated++;
        echo "Updated post_id {$row->post_id}\n";
    }
}

echo "Done. Updated $updated postmeta row(s).\n";
