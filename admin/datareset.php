<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'function_memcache.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $site_config, $lang, $cache;

$lang    = array_merge($lang, load_language('ad_datareset'));
$HTMLOUT = '';
/**
 * @param $tid
 */
function deletetorrent($tid)
{
    global $site_config, $CURUSER, $cache;

    sql_query('DELETE peers.*, files.*, comments.*, snatched.*, thanks.*, bookmarks.*, coins.*, rating.*, torrents.* FROM torrents 
                 LEFT JOIN peers ON peers.torrent = torrents.id
                 LEFT JOIN files ON files.torrent = torrents.id
                 LEFT JOIN comments ON comments.torrent = torrents.id
                 LEFT JOIN thanks ON thanks.torrentid = torrents.id
                 LEFT JOIN bookmarks ON bookmarks.torrentid = torrents.id
                 LEFT JOIN coins ON coins.torrentid = torrents.id
                 LEFT JOIN rating ON rating.torrent = torrents.id
                 LEFT JOIN snatched ON snatched.torrentid = torrents.id
                 WHERE torrents.id =' . sqlesc($tid)) or sqlerr(__FILE__, __LINE__);
    unlink("{$site_config['torrent_dir']}/$tid.torrent");
    $cache->delete('MyPeers_' . $CURUSER['id']);
}

/**
 * @param $tid
 */
function deletetorrent_xbt($tid)
{
    global $site_config, $CURUSER, $cache;

    sql_query('UPDATE torrents SET flags = 1 WHERE id = ' . sqlesc($tid)) or sqlerr(__FILE__, __LINE__);
    sql_query('DELETE files.*, comments.*, xbt_files_users.*, thanks.*, bookmarks.*, coins.*, rating.*, torrents.* FROM torrents 
                 LEFT JOIN files ON files.torrent = torrents.id
                 LEFT JOIN comments ON comments.torrent = torrents.id
                 LEFT JOIN thanks ON thanks.torrentid = torrents.id
                 LEFT JOIN bookmarks ON bookmarks.torrentid = torrents.id
                 LEFT JOIN coins ON coins.torrentid = torrents.id
                 LEFT JOIN rating ON rating.torrent = torrents.id
                 LEFT JOIN xbt_files_users ON xbt_files_users.fid = torrents.id
                 WHERE torrents.id =' . sqlesc($tid) . ' AND flags=1') or sqlerr(__FILE__, __LINE__);
    unlink("{$site_config['torrent_dir']}/$tid.torrent");
    $cache->delete('MyPeers_XBT_' . $CURUSER['id']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tid = (isset($_POST['tid']) ? (int) $_POST['tid'] : 0);
    if ($tid == 0) {
        stderr($lang['datareset_stderr'], $lang['datareset_stderr1']);
    }
    if (get_row_count('torrents', 'where id=' . sqlesc($tid)) != 1) {
        stderr($lang['datareset_stderr'], $lang['datareset_stderr2']);
    }
    $q1 = sql_query('SELECT s.downloaded AS sd , t.id AS tid, t.name,t.size, u.username,u.id AS uid,u.downloaded AS ud FROM torrents AS t LEFT JOIN snatched AS s ON s.torrentid = t.id LEFT JOIN users AS u ON u.id = s.userid WHERE t.id =' . sqlesc($tid)) or sqlerr(__FILE__, __LINE__);
    while ($a = mysqli_fetch_assoc($q1)) {
        $newd           = ($a['ud'] > 0 ? $a['ud'] - $a['sd'] : 0);
        $new_download[] = '(' . $a['uid'] . ',' . $newd . ')';
        $tname          = htmlsafechars($a['name']);
        $msg            = $lang['datareset_hey'] . htmlsafechars($a['username']) . "\n";
        $msg .= $lang['datareset_looks'] . htmlsafechars($a['name']) . $lang['datareset_nuked'];
        $msg .= $lang['datareset_down'] . mksize($a['sd']) . $lang['datareset_downbe'] . mksize($newd) . "\n";
        $pms[] = '(0,' . sqlesc($a['uid']) . ',' . TIME_NOW . ',' . sqlesc($msg) . ')';
        $cache->update_row('user' . $a['uid'], [
            'downloaded' => $new_download,
        ], $site_config['expires']['curuser']);
    }
    //==Send the pm !!
    sql_query('INSERT INTO messages (sender, receiver, added, msg) VALUES ' . join(',', array_map('sqlesc', $pms))) or sqlerr(__FILE__, __LINE__);
    //==Update user download amount
    sql_query('INSERT INTO users (id,downloaded) VALUES ' . join(',', array_map('sqlesc', $new_download)) . ' ON DUPLICATE KEY UPDATE downloaded = VALUES(downloaded)') or sqlerr(__FILE__, __LINE__);
    if (XBT_TRACKER) {
        deletetorrent_xbt($tid);
    } else {
        deletetorrent($tid);
        remove_torrent_peers($tid);
    }
    write_log($lang['datareset_torr'] . $tname . $lang['datareset_wdel'] . htmlsafechars($CURUSER['username']) . $lang['datareset_allusr']);
    header('Refresh: 3; url=staffpanel.php?tool=datareset');
    stderr($lang['datareset_stderr'], $lang['datareset_pls']);
} else {
    $HTMLOUT .= begin_frame();
    $HTMLOUT .= "<form action='staffpanel.php?tool=datareset&amp;action=datareset' method='post'>
    <fieldset>
    <legend>{$lang['datareset_reset']}</legend>
 <table width='500' style='border-collapse:collapse'>
        <tr><td nowrap='nowrap'>{$lang['datareset_tid']}</td><td width='100%'><input type='text' name='tid' size='20' /></td></tr>
        <tr><td style='background:#990033; color:#CCCCCC;' colspan='2'>
            <ul>
                    <li>{$lang['datareset_tid_info']}</li>
                    <li>{$lang['datareset_info']}</li>
                    <li>{$lang['datareset_info1']}</b></li>
                </ul>
            </td></tr>
            <tr><td colspan='2'><input type='submit' value='{$lang['datareset_repay']}' /></td></tr>
        </table>
    </fieldset>
    </form>";
    $HTMLOUT .= end_frame();
    echo stdhead($lang['datareset_stdhead']) . $HTMLOUT . stdfoot();
}
