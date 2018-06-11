<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $site_config, $lang, $cache;

$lang    = array_merge($lang, load_language('ad_warn'));
$HTMLOUT = '';
/**
 * @param $x
 *
 * @return int
 */
function mkint($x)
{
    return (int) $x;
}

$stdfoot = [
    'js' => [
    ],
];
$this_url = $_SERVER['SCRIPT_NAME'];
$do = isset($_GET['do']) && $_GET['do'] === 'disabled' ? 'disabled' : 'warned';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $r     = isset($_POST['ref']) ? $_POST['ref'] : $this_url;
    $_uids = isset($_POST['users']) ? array_map('mkint', $_POST['users']) : 0;
    if ($_uids == 0 || count($_uids) == 0) {
        stderr($lang['warn_stderr'], $lang['warn_stderr_msg']);
    }
    $valid = [
        'unwarn',
        'disable',
        'delete',
    ];
    $act = isset($_POST['action']) && in_array($_POST['action'], $valid) ? $_POST['action'] : false;
    if (!$act) {
        stderr('Err', $lang['warn_stderr_msg1']);
    }
    if ($act === 'delete' && $CURUSER['class'] >= UC_SYSOP) {
        $res_del = sql_query('SELECT id, username, added, downloaded, uploaded, last_access, class, donor, warned, enabled, status FROM users WHERE id IN (' . join(',', $_uids) . ') ORDER BY username DESC');
        if (mysqli_num_rows($res_del) != 0) {
            $count = mysqli_num_rows($res_del);
            while ($arr_del = mysqli_fetch_assoc($res_del)) {
                $userid = $arr_del['id'];
                $res    = sql_query('DELETE FROM users WHERE id=' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
                $cache->delete('user' . $userid);
                write_log("User: {$arr_del['username']} Was deleted by " . $CURUSER['username'] . ' Via Warn Page');
            }
        } else {
            stderr($lang['warn_stderr'], $lang['warn_stderr_msg2']);
        }
    }
    if ($act === 'disable') {
        if (sql_query("UPDATE users SET enabled='no', modcomment=CONCAT(" . sqlesc(get_date(TIME_NOW, 'DATE', 1) . $lang['warn_disabled_by'] . $CURUSER['username'] . "\n") . ',modcomment) WHERE id IN (' . join(',', $_uids) . ')')) {
            foreach ($_uids as $uid) {
                $cache->update_row('user' . $uid, [
                    'enabled' => 'no',
                ], $site_config['expires']['user_cache']);
            }
            $d = mysqli_affected_rows($GLOBALS['___mysqli_ston']);
            header('Refresh: 2; url=' . $r);
            stderr($lang['warn_stdmsg_success'], $d . $lang['warn_stdmsg_user'] . ($d > 1 ? 's' : '') . $lang['warn_stdmsg_disabled']);
        } else {
            stderr($lang['warn_stderr'], $lang['warn_stderr_msg3']);
        }
    } elseif ($act === 'unwarn') {
        $sub  = $lang['warn_removed'];
        $body = $lang['warn_removed_msg'] . $CURUSER['username'] . $lang['warn_removed_msg1'];
        $pms  = [];
        foreach ($_uids as $id) {
            $cache->update_row('user' . $id, [
                'warned' => 0,
            ], $site_config['expires']['user_cache']);
            $pms[] = '(0,' . $id . ',' . sqlesc($sub) . ',' . sqlesc($body) . ',' . sqlesc(TIME_NOW) . ')';
            $cache->increment('inbox_' . $id);
        }
        if (!empty($pms) && count($pms)) {
            $g  = sql_query('INSERT INTO messages(sender,receiver,subject,msg,added) VALUE ' . join(',', $pms))                                                                                                                             or ($q_err = ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
            $q1 = sql_query("UPDATE users SET warned='0', modcomment=CONCAT(" . sqlesc(get_date(TIME_NOW, 'DATE', 1) . $lang['warn_removed_msg'] . $CURUSER['username'] . "\n") . ',modcomment) WHERE id IN (' . join(',', $_uids) . ')')   or ($q2_err = ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
            if ($g && $q1) {
                header('Refresh: 2; url=' . $r);
                stderr($lang['warn_stdmsg_success'], count($pms) . $lang['warn_stdmsg_user'] . (count($pms) > 1 ? 's' : '') . $lang['warn_stdmsg_unwarned']);
            } else {
                stderr($lang['warn_stderr'], $lang['warn_stderr_msgq1'] . $q_err . $lang['warn_stderr_msgq2'] . $q2_err);
            }
        }
    }
    exit;
}
switch ($do) {
    case 'disabled':
        $query = "SELECT id,username, class, downloaded, uploaded, IF(downloaded>0, round((uploaded/downloaded),2), '---') AS ratio, disable_reason, added, last_access FROM users WHERE enabled='no' ORDER BY last_access DESC ";
        $title = $lang['warn_disable_title'];
        $link  = "<a href=\"staffpanel.php?tool=warn&amp;action=warn&amp;?do=warned\">{$lang['warn_warned_users']}</a>";
        break;

    case 'warned':
        $query = "SELECT id, username, class, downloaded, uploaded, IF(downloaded>0, round((uploaded/downloaded),2), '---') AS ratio, warn_reason, warned, added, last_access FROM users WHERE warned>='1' ORDER BY last_access DESC, warned DESC ";
        $title = $lang['warn_warned_title'];
        $link  = "<a href=\"staffpanel.php?tool=warn&amp;action=warn&amp;do=disabled\">{$lang['warn_disabled_users']}</a>";
        break;
}
$g     = sql_query($query) or print (is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false);
$count = mysqli_num_rows($g);
$HTMLOUT .= begin_main_frame();
$HTMLOUT .= begin_frame($title . "&#160;[<font class=\"small\">{$lang['warn_total']}" . $count . $lang['warn_total_user'] . ($count > 1 ? $lang['warn_total_user_plural'] : '') . '</font>] - ' . $link);
if ($count == 0) {
    $HTMLOUT .= stdmsg($lang['warn_hey'], $lang['warn_hey_msg'] . strtolower($title));
} else {
    $HTMLOUT .= "<form action='staffpanel.php?tool=warn&amp;action=warn' method='post'>
                <table width='600' style='border-collapse:separate;'>
                <tr>
                        <td class='colhead' width='100%' >{$lang['warn_user']}</td>
                        <td class='colhead' nowrap='nowrap'>{$lang['warn_ratio']}</td>
                        <td class='colhead' nowrap='nowrap'>{$lang['warn_class']}</td>
                        <td class='colhead' nowrap='nowrap'>{$lang['warn_ltacces']}</td>
                        <td class='colhead' nowrap='nowrap'>{$lang['warn_joined']}</td>
                        <td class='colhead' nowrap='nowrap'><input type='checkbox' name='checkall' /></td>
                </tr>";
    while ($a = mysqli_fetch_assoc($g)) {
        $tip = ($do === 'warned' ? $lang['warn_for'] . $a['warn_reason'] . '<br>' . $lang['warn_till'] . get_date($a['warned'], 'DATE', 1) . ' - ' . mkprettytime($a['warned'] - TIME_NOW) : $lang['warn_disabled_for'] . $a['disable_reason']);
        $HTMLOUT .= "<tr>
                                  <td width='100%'><a href='userdetails.php?id=" . (int) $a['id'] . "' onmouseover=\"Tip('($tip)')\" onmouseout=\"UnTip()\">" . htmlsafechars($a['username']) . "</a></td>
                                  <td nowrap='nowrap'>" . (float) $a['ratio'] . "<br><font class='small'><b>{$lang['warn_down']}</b>" . mksize($a['downloaded']) . "&#160;<b>{$lang['warn_upl']}</b> " . mksize($a['uploaded']) . "</font></td>
                                  <td nowrap='nowrap'>" . get_user_class_name($a['class']) . "</td>
                                  <td nowrap='nowrap'>" . get_date($a['last_access'], 'LONG', 0, 1) . "</td>
                                  <td nowrap='nowrap'>" . get_date($a['added'], 'DATE', 1) . "</td>
                                  <td nowrap='nowrap'><input type='checkbox' name='users[]' value='" . (int) $a['id'] . "' /></td>
                                </tr>";
    }
    $HTMLOUT .= "<tr>
                        <td colspan='6' class='colhead'>
                                <select name='action'>
                                        <option value='unwarn'>{$lang['warn_unwarn']}</option>
                                        <option value='disable'>{$lang['warn_disable']}</option>
                                        <option value='delete' " . ($CURUSER['class'] < UC_SYSOP ? 'disabled' : '') . ">{$lang['warn_delete']}</option>
                                </select>
                                &raquo;
                                <input type='submit' value='Apply' />
                                <input type='hidden' value='" . htmlsafechars($_SERVER['REQUEST_URI']) . "' name='ref' />
                        </td>
                        </tr>
                        </table>
                        </form>";
}
$HTMLOUT .= end_frame();
$HTMLOUT .= end_main_frame();
echo stdhead($title) . $HTMLOUT . stdfoot($stdfoot);
