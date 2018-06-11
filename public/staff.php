<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
global $site_config, $fluent;

$lang    = array_merge(load_language('global'), load_language('staff'));
$stdhead = [
    'css' => [
    ],
];
$support = $mods = $admin = $sysop = [];
$htmlout = $firstline = '';
$query   = $fluent->from('users')
    ->select(null)
    ->select('users.id')
    ->select('users.class')
    ->select('users.perms')
    ->select('users.last_access')
    ->select('users.support')
    ->select('users.supportfor')
    ->select('users.country')
    ->select('countries.flagpic')
    ->select('countries.name as flagname')
    ->leftJoin('countries ON countries.id = users.country')
    ->where('users.class >= ? OR users.support = ?', UC_STAFF, 'yes')
    ->where('users.status = ?', 'confirmed')
    ->orderBy('username');

foreach ($query as $arr2) {
    if ($arr2['support'] === 'yes') {
        $support[] = $arr2;
    }
    if ($arr2['class'] == UC_MODERATOR) {
        $mods[] = $arr2;
    }
    if ($arr2['class'] == UC_ADMINISTRATOR) {
        $admin[] = $arr2;
    }
    if ($arr2['class'] == UC_SYSOP) {
        $sysop[] = $arr2;
    }
}

/**
 * @param $staff_array
 * @param $staffclass
 *
 * @return null|string
 *
 * @throws \MatthiasMullie\Scrapbook\Exception\Exception
 * @throws \MatthiasMullie\Scrapbook\Exception\ServerUnhealthy
 */
function DoStaff($staff_array, $staffclass)
{
    global $site_config;

    if (empty($staff_array)) {
        return null;
    }
    $htmlout = $body = '';
    $dt      = TIME_NOW - 180;

    $htmlout .= "
                <h2 class='left10 top20'>{$staffclass}</h2>";
    foreach ($staff_array as $staff) {
        $body .= '
                    <tr>';
        $flagpic  = !empty($staff['flagpic']) ? "{$site_config['pic_baseurl']}flag/{$staff['flagpic']}" : '';
        $flagname = !empty($staff['flagname']) ? $staff['flagname'] : '';
        $body .= '
                        <td>' . format_username($staff['id']) . "</td>
                        <td><img src='{$site_config['pic_baseurl']}staff/" . ($staff['last_access'] > $dt && $staff['perms'] < bt_options::PERMS_STEALTH ? 'online.png' : 'offline.png') . "' height='16' alt='' /></td>" . "
                        <td><a href='{$site_config['baseurl']}/pm_system.php?action=send_message&amp;receiver=" . (int) $staff['id'] . '&amp;returnto=' . urlencode($_SERVER['REQUEST_URI']) . "'><img src='{$site_config['pic_baseurl']}mailicon.png' class='tooltipper' title='Personal Message' alt='' /></a></td>" . "
                        <td><img src='$flagpic' alt='" . htmlsafechars($flagname) . "' /></td>
                    </tr>";
    }

    return $htmlout . main_table($body);
}

$htmlout .= DoStaff($sysop, 'Sysops');
$htmlout .= DoStaff($admin, 'Administrator');
$htmlout .= DoStaff($mods, 'Moderators');
$dt = TIME_NOW - 180;
if (!empty($support)) {
    $body = '';
    foreach ($support as $a) {
        $flagpic  = !empty($staff['flagpic']) ? "{$site_config['pic_baseurl']}flag/{$staff['flagpic']}" : '';
        $flagname = !empty($staff['flagname']) ? $staff['flagname'] : '';
        $body .= '
                <tr>
                    <td>' . format_username($a['id']) . "</td>
                    <td><img src='{$site_config['pic_baseurl']}/staff/" . ($a['last_access'] > $dt ? 'online.png' : 'offline.png') . "' alt='' /></td>
                    <td><a href='{$site_config['baseurl']}pm_system.php?action=send_message&amp;receiver=" . (int) $a['id'] . "'><img src='{$site_config['pic_baseurl']}mailicon.png' class='tooltipper' title='{$lang['alt_pm']}' alt='' /></a></td>
                    <td><img src='$flagpic' alt='" . htmlsafechars($flagname) . "' /></td>
                    <td>" . htmlsafechars($a['supportfor']) . '</td>
                </tr>';
    }
    $htmlout .= "
            <h2 class='left10 top20'>{$lang['header_fls']}</h2>";
    $heading = "
                    <tr>
                        <th class='staff_username' colspan='5'>{$lang['text_first']}<br><br></th>
                    </tr>
                    <tr>
                        <th class='staff_username'>{$lang['first_name']}</th>
                        <th>{$lang['first_active']}</th>
                        <th>{$lang['first_contact']}</th>
                        <th>{$lang['first_lang']}</th>
                        <th>{$lang['first_supportfor']}</th>
                    </tr>";
    $htmlout .= main_table($body, $heading);
}
echo stdhead('Staff', true, $stdhead) . wrapper($htmlout) . stdfoot();
