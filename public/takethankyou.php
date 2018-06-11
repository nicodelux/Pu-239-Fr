<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();
global $CURUSER, $site_config, $cache, $session;

$lang = array_merge(load_language('global'), load_language('takerate'));
if (!mkglobal('id')) {
    die();
}
$id = (int) $id;
if (!is_valid_id($id)) {
    stderr('Error', 'Bad Id');
}
if (!isset($CURUSER)) {
    stderr('Error', 'Your not logged in');
}
$res = sql_query('SELECT 1, thanks, comments FROM torrents WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_assoc($res);
if (!$arr) {
    stderr('Error', 'Torrent not found');
}
$res1 = sql_query('SELECT 1 FROM thankyou WHERE torid=' . sqlesc($id) . ' AND uid =' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
$row  = mysqli_fetch_assoc($res1);
if ($row) {
    stderr('Error', 'You already thanked.');
}
$text  = ':thankyou:';
$newid = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS['___mysqli_ston']))) ? false : $___mysqli_res);
sql_query('INSERT INTO thankyou (uid, torid, thank_date) VALUES (' . sqlesc($CURUSER['id']) . ', ' . sqlesc($id) . ", '" . TIME_NOW . "')")                                                            or sqlerr(__FILE__, __LINE__);
sql_query('INSERT INTO comments (user, torrent, added, text, ori_text) VALUES (' . sqlesc($CURUSER['id']) . ', ' . sqlesc($id) . ", '" . TIME_NOW . "', " . sqlesc($text) . ',' . sqlesc($text) . ')') or sqlerr(__FILE__, __LINE__);
sql_query('UPDATE torrents SET thanks = thanks + 1, comments = comments + 1 WHERE id = ' . sqlesc($id))                                                                                                or sqlerr(__FILE__, __LINE__);
$update['thanks']   = ($arr['thanks'] + 1);
$update['comments'] = ($arr['comments'] + 1);
$cache->update_row('torrent_details_' . $id, [
    'thanks'   => $update['thanks'],
    'comments' => $update['comments'],
], $site_config['expires']['torrent_details']);
$cache->delete('latest_comments_');
if ($site_config['seedbonus_on'] == 1) {
    sql_query('UPDATE users SET seedbonus = seedbonus + ' . sqlesc($site_config['bonus_per_comment']) . ' WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $update['seedbonus'] = ($CURUSER['seedbonus'] + $site_config['bonus_per_comment']);
    $cache->update_row('user' . $CURUSER['id'], [
        'seedbonus' => $update['seedbonus'],
    ], $site_config['expires']['user_cache']);
}
$session->set('is-success', "Your 'Thank you' has been registered!");
header("Refresh: 0; url=details.php?id=$id");
