<?php

global $CURUSER, $site_config, $lang, $cache;

$dt                   = TIME_NOW - 180;
$keys['user_friends'] = 'user_friends_' . $id;
$users_friends        = $cache->get($keys['user_friends']);
if ($users_friends === false || is_null($users_friends)) {
    $fr = sql_query('SELECT f.friendid AS uid, f.userid AS userid, u.last_access, u.id, u.ip, u.avatar, u.username, u.class, u.donor, u.title, u.warned, u.enabled, u.chatpost, u.leechwarn, u.pirate, u.king, u.downloaded, u.uploaded, u.perms FROM friends AS f LEFT JOIN users AS u ON f.friendid = u.id WHERE userid = ' . sqlesc($id) . ' ORDER BY username ASC LIMIT 100') or sqlerr(__FILE__, __LINE__);
    while ($user_friends = mysqli_fetch_assoc($fr)) {
        $users_friends[] = $user_friends;
    }
    $cache->set($keys['user_friends'], $users_friends, 0);
}
if (!empty($users_friends) && count($users_friends) > 0) {
    $user_friends = "<table width='100%' class='main' >\n" . "<tr><td class='colhead' width='20'>{$lang['userdetails_avatar']}</td><td class='colhead'>{$lang['userdetails_username']}" . ($CURUSER['class'] >= UC_STAFF ? $lang['userdetails_fip'] : '') . "</td><td class='colhead'>{$lang['userdetails_uploaded']}</td>" . ($site_config['ratio_free'] ? '' : "<td class='colhead'>{$lang['userdetails_downloaded']}</td>") . "<td class='colhead'>{$lang['userdetails_ratio']}</td><td class='colhead'>{$lang['userdetails_status']}</td></tr>\n";
    if ($users_friends) {
        foreach ($users_friends as $a) {
            $avatar = (($user['opt1'] & user_options::AVATARS) ? ($a['avatar'] === '' ? '<img src="' . $site_config['pic_baseurl'] . 'forumicons/default_avatar.gif"  width="40" alt="default avatar" />' : '<img src="' . image_proxy($a['avatar']) . '" alt="avatar"  width="40" />') : '');
            $status           = "<img style='vertical-align: middle;' src='{$site_config['pic_baseurl']}" . ($a['last_access'] > $dt && $a['perms'] < bt_options::PERMS_STEALTH ? 'online.png' : 'offline.png') . "' alt='' />";
            $user_stuff       = $a;
            $user_stuff['id'] = (int) $a['id'];
            $user_friends .= "<tr><td style='padding: 0; border: none' width='40px'>" . $avatar . '</td><td>' . format_username($user_stuff['id']) . '<br>' . ($CURUSER['class'] >= UC_STAFF ? '' . htmlsafechars($a['ip']) . '' : '') . "</td><td  style='padding: 1px'>" . mksize($a['uploaded']) . '</td>' . ($site_config['ratio_free'] ? '' : "<td  style='padding: 1px'>" . mksize($a['downloaded']) . '</td>') . "<td  style='padding: 1px'>" . member_ratio($a['uploaded'], $site_config['ratio_free'] ? '0' : $a['downloaded']) . "</td><td  style='padding: 1px'>" . $status . "</td></tr>\n";
        }
        $user_friends .= '</table>';
        $HTMLOUT      .= "<tr><td class='rowhead'>{$lang['userdetails_friends']}</td><td width='99%'><a href=\"javascript: klappe_news('a6')\"><img src='{$site_config['pic_baseurl']}plus.png' id='pica6" . (int) $a['uid'] . "' alt='{$lang['userdetails_hide_show']}' title='{$lang['userdetails_hide_show']}' /></a><div id='ka6' style='display: none;'><br>$user_friends</div></td></tr>";
    } else {
        if (empty($users_friends)) {
            $HTMLOUT .= "<tr><td colspan='2'>{$lang['userdetails_no_friends']}</td></tr>";
        }
    }
}

