<?php

require_once INCL_DIR . 'user_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $site_config, $lang, $cache;

$lang    = array_merge($lang, load_language('ad_freeusers'));
$HTMLOUT = '';
$remove  = (isset($_GET['remove']) ? (int) $_GET['remove'] : 0);
if ($remove) {
    if (empty($remove)) {
        die($lang['freeusers_wtf']);
    }
    $res         = sql_query('SELECT id, username, class FROM users WHERE free_switch != 0 AND id = ' . sqlesc($remove)) or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $msg = sqlesc($lang['freeusers_msg'] . $CURUSER['username'] . $lang['freeusers_period']);
        while ($arr = mysqli_fetch_assoc($res)) {
            $modcomment     = sqlesc(get_date(TIME_NOW, 'DATE', 1) . $lang['freeusers_mod1'] . $CURUSER['username'] . " \n");
            $msgs_buffer[]  = '(0,' . $arr['id'] . ',' . TIME_NOW . ', ' . sqlesc($msg) . ', \'' . $lang['freeusers_msg_buffer'] . '\')';
            $users_buffer[] = '(' . $arr['id'] . ',0,' . $modcomment . ')';
            $msgs_ids[]     = $arr['id'];
            $usernames[]    = $arr['username'];
        }
        if (count($msgs_buffer) > 0) {
            sql_query('INSERT INTO messages (sender,receiver,added,msg,subject) VALUES ' . implode(', ', $msgs_buffer))                                                                                                          or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO users (id, free_switch, modcomment) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE KEY UPDATE free_switch = VALUES(free_switch), modcomment=concat(VALUES(modcomment),modcomment)') or sqlerr(__FILE__, __LINE__);
            foreach ($usernames as $username) {
                write_log("{$lang['freeusers_log1']} $remove ($username) {$lang['freeusers_log2']} $CURUSER[username]");
            }
            foreach ($msgs_ids as $msg_id) {
                $cache->delete('user' . $msg_id['id']);
                $cache->increment('inbox_' . $msg_id['id']);
            }
        }
    } else {
        die($lang['freeusers_fail']);
    }
}
$res2  = sql_query('SELECT id, username, class, free_switch FROM users WHERE free_switch != 0 ORDER BY username ASC') or sqlerr(__FILE__, __LINE__);
$count = mysqli_num_rows($res2);
$HTMLOUT .= "<h1>{$lang['freeusers_head']} ($count)</h1>";
if ($count == 0) {
    $HTMLOUT .= '<p><b>' . $lang['freeusers_nothing'] . '</b></p>';
} else {
    $HTMLOUT .= "<table width='50%'>
          <tr><td class='colhead'>{$lang['freeusers_username']}</td><td class='colhead'>{$lang['freeusers_class']}</td>
          <td class='colhead'>{$lang['freeusers_expires']}</td><td class='colhead'>{$lang['freeusers_remove']}</td></tr>";
    while ($arr2 = mysqli_fetch_assoc($res2)) {
        $HTMLOUT .= "<tr><td><a href='userdetails.php?id=" . (int) $arr2['id'] . "'>" . htmlsafechars($arr2['username']) . '</a></td><td>' . get_user_class_name($arr2['class']);
        if ($arr2['class'] > UC_ADMINISTRATOR && $arr2['id'] != $CURUSER['id']) {
            $HTMLOUT .= "</td><td>{$lang['freeusers_until']}" . get_date($arr2['free_switch'], 'DATE') . ' 
(' . mkprettytime($arr2['free_switch'] - TIME_NOW) . "{$lang['freeusers_togo']})" . "</td><td><span class='has-text-danger'>{$lang['freeusers_notallowed']}</span></td>
</tr>";
        } else {
            $HTMLOUT .= "</td><td>{$lang['freeusers_until']}" . get_date($arr2['free_switch'], 'DATE') . ' 
(' . mkprettytime($arr2['free_switch'] - TIME_NOW) . "{$lang['freeusers_togo']})" . "</td>
<td><a href='staffpanel.php?tool=freeusers&amp;action=freeusers&amp;remove=" . (int) $arr2['id'] . "' onclick=\"return confirm('{$lang['freeusers_confirm']}')\">{$lang['freeusers_rem']}</a></td></tr>";
        }
    }
    $HTMLOUT .= '</table>';
}
echo stdhead($lang['freeusers_stdhead']) . $HTMLOUT . stdfoot();
die();
