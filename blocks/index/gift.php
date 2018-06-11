<?php

global $CURUSER, $site_config, $lang;

$Christmasday = mktime(0, 0, 0, 12, 25, date('Y'));
$today        = mktime(date('G'), date('i'), date('s'), date('m'), date('d'), date('Y'));
if (($CURUSER['opt1'] & user_options::GOTGIFT) && $today != $Christmasday) {
    $HTMLOUT .= "
    <a id='gift-hash'></a>
    <fieldset id='gift' class='header'>
        <legend class='flipper has-text-primary'><i class='fa icon-up-open size_3' aria-hidden='true'></i>{$lang['index_christmas_gift']}</legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00 has-text-centered'>
                <a href='{$site_config['baseurl']}/gift.php?open=1'>
                    <img src='{$site_config['pic_baseurl']}gift.png' class='tooltipper image_48' alt='{$lang['index_christmas_gift']}' title='{$lang['index_christmas_gift']}' />
                </a>
            </div>
        </div>
    </fieldset>";
}
