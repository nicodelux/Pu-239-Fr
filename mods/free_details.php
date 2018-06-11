<?php

global $CURUSER, $site_config;

$is            = $fl            = '';
$isfree['yep'] = $isfree['expires'] = 0;
$freeimg       = '<img src="' . $site_config['pic_baseurl'] . 'freedownload.gif" alt="Free download" class="tooltipper" title="Free download" />';
$silverimg     = '<img src="' . $site_config['pic_baseurl'] . 'silverdownload.gif" alt="Silver Torrent" class="tooltipper" title="Silver Torrent" />';
if (isset($free)) {
    foreach ($free as $fl) {
        switch ($fl['modifier']) {
            case 1:
                $mode = 'All Torrents Free';
                break;

            case 2:
                $mode = 'All Double Upload';
                break;

            case 3:
                $mode = 'All Torrents Free and Double Upload';
                break;

            case 4:
                $mode = 'All Torrents Silver';
                break;

            default:
                $mode = 0;
        }
        $isfree['yep']     = ($fl['modifier'] != 0) && ($fl['expires'] > TIME_NOW || $fl['expires'] == 1);
        $isfree['expires'] = $fl['expires'];
    }
}
$HTMLOUT .= (($torrents['free'] != 0 || $torrents['silver'] != 0 || $CURUSER['free_switch'] != 0 || $isfree['yep']) ? '<span> Free Status ' . ($torrents['free'] != 0 ? $freeimg . '<b><span style="color: ' . $torrent['free_color'] . ';"> Torrent FREE </span></b> ' . ($torrents['free'] > 1 ? ' Expires: ' . get_date($torrents['free'], 'DATE') . '
(' . mkprettytime($torrents['free'] - TIME_NOW) . ' to go)<br>' : 'Unlimited<br>') : '') : '') . ($torrents['silver'] != 0 ? $silverimg . ' <b><font color="' . $torrent['silver_color'] . '">Torrent SILVER</font></b> ' . ($torrents['silver'] > 1 ? 'Expires: ' . get_date($torrents['silver'], 'DATE') . ' 
(' . mkprettytime($torrents['silver'] - TIME_NOW) . ' to go)<br>' : 'Unlimited<br>') : '') . ($CURUSER['free_switch'] != 0 ? $freeimg . ' <b><font color="' . $torrent['free_color'] . '">Personal FREE Status</font></b> ' . ($CURUSER['free_switch'] > 1 ? 'Expires: ' . get_date($CURUSER['free_switch'], 'DATE') . ' 
(' . mkprettytime($CURUSER['free_switch'] - TIME_NOW) . ' to go)<br>' : 'Unlimited<br>') : '') . ($isfree['yep'] ? $freeimg . ' <b><font color="' . $torrent['free_color'] . '">' . $mode . '</font></b> ' . ($isfree['expires'] != 1 ? 'Expires: ' . get_date($isfree['expires'], 'DATE') . ' 
(' . mkprettytime($isfree['expires'] - TIME_NOW) . ' to go)<br>' : 'Unlimited<br>') : '') . (($torrents['free'] != 0 || $torrents['silver'] != 0 || $CURUSER['free_switch'] != 0 || $isfree['yep']) ? '</span>' : '') . '';

