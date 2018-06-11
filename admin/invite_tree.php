<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'pager_new.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $site_config, $lang;

$lang    = array_merge($lang, load_language('ad_invite_tree'));
$HTMLOUT = '';
//=== if we got here from a members page, get their info... if not, ask for a username to get the info...
$id = (isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0));
if ($id !== 0) {
    $rez_user = sql_query('SELECT username, warned, suspended, enabled, donor, invitedby FROM users WHERE id = ' . sqlesc($id));
    $arr_user = mysqli_fetch_assoc($rez_user);
    //=== start the page
    $HTMLOUT .= '<h1>' . htmlsafechars($arr_user['username']) . (substr($arr_user['username'], -1) === 's' ? '\'' : '\'s') . ' ' . $lang['invite_head'] . '</h1>
        <p>' . ($arr_user['invitedby'] == 0 ? '<a title="' . htmlsafechars($arr_user['username']) . ' ' . $lang['invite_open'] . '">' . $lang['invite_up'] . '</a>' : '<a href="' . $site_config['baseurl'] . '/staffpanel.php?tool=invite_tree&amp;action=invite_tree&amp;really_deep=1&amp;id=' . (int)$arr_user['invitedby'] . '" title="go up one level">' . $lang['invite_up'] . '</a>') . ' | 
        | <a href="' . $site_config['baseurl'] . '/staffpanel.php?tool=invite_tree&amp;action=invite_tree' . (isset($_GET['deeper']) ? '' : '&amp;deeper=1') . '&amp;id=' . $id . '" title=" ' . $lang['invite_click'] . ' ' . (isset($_GET['deeper']) ? $lang['invite_shrink'] : $lang['invite_expand']) . ' ' . $lang['invite_this'] . ' ">' . $lang['invite_expand_tree'] . '</a> | 
        | <a href="' . $site_config['baseurl'] . '/staffpanel.php?tool=invite_tree&amp;action=invite_tree&amp;really_deep=1&amp;id=' . $id . '" title="' . $lang['invite_click_more'] . '">' . $lang['invite_expand_more'] . '</a></p>';
    $HTMLOUT .= '<table class="main" width="750px" border="0">
        <tr><td class="embedded">';
    //=== members invites
    $rez_invited = sql_query('SELECT id, username, email, uploaded, downloaded, status, warned, suspended, enabled, donor, email, ip, class, chatpost, leechwarn, pirate, king FROM users WHERE invitedby = ' . sqlesc($id) . ' ORDER BY added');
    if (mysqli_num_rows($rez_invited) < 1) {
        $HTMLOUT .= $lang['invite_none'];
    } else {
        $HTMLOUT .= '<table width="100%" border="1">
        <tr><td class="colhead"><span style="font-weight: bold;">' . $lang['invite_username'] . '</span></td>
        <td class="colhead"><span style="font-weight: bold;">' . $lang['invite_email'] . '</span></td>
        <td class="colhead"><span style="font-weight: bold;">' . $lang['invite_uploaded'] . '</span></td>
        <td class="colhead"><span style="font-weight: bold;">' . $lang['invite_downloaded'] . '</span></td>
        <td class="colhead"><span style="font-weight: bold;">' . $lang['invite_ratio'] . '</span></td>
        <td class="colhead"><span style="font-weight: bold;">' . $lang['invite_status'] . '</span></td></tr>';
        while ($arr_invited = mysqli_fetch_assoc($rez_invited)) {
            $deeper = '';
            //=== if  deeper get the invitees invitees
            if (isset($_GET['deeper']) || isset($_GET['really_deep'])) {
                $rez_invited_deeper = sql_query('SELECT id, username, email, uploaded, downloaded, status, warned, suspended, enabled, donor, email, ip, class, chatpost, leechwarn, pirate, king FROM users WHERE invitedby = ' . sqlesc($arr_invited['id']) . ' ORDER BY added');
                if (mysqli_num_rows($rez_invited_deeper) > 0) {
                    $deeper .= '<tr><td   colspan="6"><span style="font-weight: bold;">' . htmlsafechars($arr_invited['username']) . (substr($arr_invited['username'], -1) === 's' ? '\'' : '\'s') . '' . $lang['invite_invites'] . '</span><br>
                        <div><table width="95%" border="1">
                        <tr><td class="colhead"><span style="font-weight: bold;">' . $lang['invite_username'] . '</span></td>
                        <td class="colhead"><span style="font-weight: bold;">' . $lang['invite_email'] . '</span></td>
                        <td class="colhead"><span style="font-weight: bold;">' . $lang['invite_uploaded'] . '</span></td>
                        <td class="colhead"><span style="font-weight: bold;">' . $lang['invite_downloaded'] . '</span></td>
                        <td class="colhead"><span style="font-weight: bold;">' . $lang['invite_ratio'] . '</span></td>
                        <td class="colhead"><span style="font-weight: bold;">' . $lang['invite_status'] . '</span></td></tr>';
                    while ($arr_invited_deeper = mysqli_fetch_assoc($rez_invited_deeper)) {
                        $really_deep = '';
                        //=== if  really_deep get the invitees invitees invitees
                        if (isset($_GET['really_deep'])) {
                            $rez_invited_really_deep = sql_query('SELECT id, username, email, uploaded, downloaded, status, warned, suspended, enabled, donor, email, ip, class, chatpost, leechwarn, pirate, king FROM users WHERE invitedby = ' . sqlesc($arr_invited_deeper['id']) . ' ORDER BY added');
                            if (mysqli_num_rows($rez_invited_really_deep) > 0) {
                                $really_deep .= '<tr><td  colspan="6"><span style="font-weight: bold;">' . htmlsafechars($arr_invited_deeper['username']) . (substr($arr_invited_deeper['username'], -1) === 's' ? '\'' : '\'s') . ' Invites:</span><br>
                                        <div><table width="95%" border="1">
                                        <tr><td class="colhead"><span style="font-weight: bold;">' . $lang['invite_username'] . '</span></td>
                                        <td class="colhead"><span style="font-weight: bold;">' . $lang['invite_email'] . '</span></td>
                                        <td class="colhead"><span style="font-weight: bold;">' . $lang['invite_uploaded'] . '</span></td>
                                        <td class="colhead"><span style="font-weight: bold;">' . $lang['invite_downloaded'] . '</span></td>
                                        <td class="colhead"><span style="font-weight: bold;">' . $lang['invite_ratio'] . '</span></td>
                                        <td class="colhead"><span style="font-weight: bold;">' . $lang['invite_status'] . '</span></td></tr>';
                                while ($arr_invited_really_deep = mysqli_fetch_assoc($rez_invited_really_deep)) {
                                    $really_deep .= '<tr><td>' . ($arr_invited_really_deep['status'] === 'pending' ? htmlsafechars($arr_invited_really_deep['username']) : format_username($arr_invited_really_deep) . '<br>' . $arr_invited_really_deep['ip']) . '
                                            </td><td>' . htmlsafechars($arr_invited_really_deep['email']) . '</td>
                                            <td>' . mksize($arr_invited_really_deep['uploaded']) . '</td>
                                            <td>' . mksize($arr_invited_really_deep['downloaded']) . '</td>
                                            <td>' . member_ratio($arr_invited_really_deep['uploaded'], $arr_invited_really_deep['downloaded']) . '</td>
                                            <td>' . ($arr_invited_really_deep['status'] === 'confirmed' ? '<span style="color: green;">' . $lang['invite_confirmed'] . '</span></td></tr>' : '<span style="color: red;">' . $lang['invite_pending'] . '</span></td></tr>');
                                }
                                $really_deep .= '</td></tr></table></div>';
                            }
                        }
                        $deeper .= '<tr><td >' . ($arr_invited_deeper['status'] === 'pending' ? htmlsafechars($arr_invited_deeper['username']) : format_username($arr_invited_deeper) . '<br>' . $arr_invited_deeper['ip']) . '</td>
    `                        <td >' . htmlsafechars($arr_invited_deeper['email']) . '</td>
                            <td >' . mksize($arr_invited_deeper['uploaded']) . '</td>
                            <td >' . mksize($arr_invited_deeper['downloaded']) . '</td>
                            <td >' . member_ratio($arr_invited_deeper['uploaded'], $arr_invited_deeper['downloaded']) . '</td>
                            <td >' . ($arr_invited_deeper['status'] === 'confirmed' ? '<span style="color: green;">' . $lang['invite_confirmed'] . '</span></td></tr>' : '<span style="color: red;">' . $lang['invite_pending'] . '</span></td></tr>');
                    }
                    $deeper .= (isset($_GET['really_deep']) ? $really_deep . '</table></div>' : '</td></tr></table></div>');
                }
            }
            $HTMLOUT .= '<tr><td>' . ($arr_invited['status'] === 'pending' ? htmlsafechars($arr_invited['username']) : format_username($arr_invited) . '<br>' . $arr_invited['ip']) . '</td>
            <td>' . htmlsafechars($arr_invited['email']) . '</td>
            <td>' . mksize($arr_invited['uploaded']) . '</td>
            <td>' . mksize($arr_invited['downloaded']) . '</td>
            <td>' . member_ratio($arr_invited['uploaded'], $arr_invited['downloaded']) . '</td>
            <td>' . ($arr_invited['status'] === 'confirmed' ? '<span style="color: green;">' . $lang['invite_confirmed'] . '</span></td></tr>' : '<span style="color: red;">' . $lang['invite_pending'] . '</span></td></tr>');
            $HTMLOUT .= $deeper;
        }
        $HTMLOUT .= '</table>';
    }
    $HTMLOUT .= '</td></tr></table>';
} else {
    //=== ok, that was fun, but if no ID we can search members to see their invite trees \\o\o/o//
    $id = '';
    //=== search members
    $search = isset($_GET['search']) ? strip_tags(trim($_GET['search'])) : '';
    $class  = isset($_GET['class']) ? $_GET['class'] : '-';
    $letter = '';
    $q      = '';
    if ($class == '-' || !ctype_digit($class)) {
        $class = '';
    }
    if ($search != '' || $class) {
        $query = 'username LIKE ' . sqlesc("%$search%") . ' AND status=\'confirmed\'';
        if ($search) {
            $q = 'search=' . htmlsafechars($search);
        }
    } else {
        $letter = isset($_GET['letter']) ? trim((string) $_GET['letter']) : '';
        if (strlen($letter) > 1) {
            die();
        }
        if ($letter == '' || strpos('abcdefghijklmnopqrstuvwxyz0123456789', $letter) === false) {
            $letter = '';
        }
        $query = 'username LIKE ' . sqlesc("$letter%") . ' AND status=\'confirmed\'';
        $q     = 'letter=' . $letter;
    }
    if (ctype_digit($class)) {
        $query .= ' AND class=' . sqlesc($class);
        $q     .= ($q ? '&amp;' : '') . 'class=' . $class;
    }
    //=== start the page
    $HTMLOUT .= '<h1>' . $lang['invite_search'] . '</h1>
            <form method="get" action="staffpanel.php?tool=invite_tree&amp;search=1&amp;">
            <input type="hidden" name="action" value="invite_tree"/>
            <input type="text" size="30" name="search" value="' . $search . '"/>
            <select name="class">
            <option value="-">' . $lang['invite_any'] . '</option>';
    for ($i = 0;; ++$i) {
        if ($c = get_user_class_name($i)) {
            $HTMLOUT .= '<option value="' . $i . '"' . (ctype_digit($class) && $class == $i ? ' selected' : '') . '>' . $c . '</option>';
        } else {
            break;
        }
    }
    $HTMLOUT .= '</select>
            <input type="submit" value="' . $lang['invite_btn'] . '" class="button is-small" />
            </form>
            <br><br>';
    $aa = range('0', '9');
    $bb = range('a', 'z');
    $cc = array_merge($aa, $bb);
    unset($aa, $bb);
    $HTMLOUT .= '<div>';
    $count = 0;
    foreach ($cc as $L) {
        $HTMLOUT .= ($count == 10) ? '<br><br>' : '';
        if (!strcmp($L, $letter)) {
            $HTMLOUT .= ' <span class="button is-small" style="background:orange;">' . strtoupper($L) . '</span>';
        } else {
            $HTMLOUT .= ' <a href="' . $site_config['baseurl'] . '/staffpanel.php?tool=invite_tree&amp;action=invite_tree&amp;letter=' . $L . '"><span class="button is-small">' . strtoupper($L) . '</span></a>';
        }
        ++$count;
    }
    $HTMLOUT .= '</div><br>';
    //=== get stuff for the pager
    $page               = isset($_GET['page']) ? (int) $_GET['page'] : 0;
    $perpage            = isset($_GET['perpage']) ? (int) $_GET['perpage'] : 20;
    $res_count          = sql_query('SELECT COUNT(id) FROM users WHERE ' . $query);
    $arr_count          = mysqli_fetch_row($res_count);
    $count              = ($arr_count[0] > 0 ? $arr_count[0] : 0);
    list($menu, $LIMIT) = pager_new($count, $perpage, $page, 'staffpanel.php?tool=invite_tree&amp;action=invite_tree');
    $HTMLOUT .= ($arr_count[0] > $perpage) ? '' . $menu . '<br><br>' : '<br><br>';
    if ($arr_count[0] > 0) {
        $res = sql_query('SELECT users.*, countries.name, countries.flagpic FROM users FORCE INDEX ( username ) LEFT JOIN countries ON country = countries.id WHERE ' . $query . ' ORDER BY username ' . $LIMIT);
        $HTMLOUT .= '<table border="1">

            <tr><td class="colhead">' . $lang['invite_search_user'] . '</td>
            <td class="colhead">' . $lang['invite_search_reg'] . '</td>
            <td class="colhead">' . $lang['invite_search_la'] . '</td>
            <td class="colhead">' . $lang['invite_search_class'] . '</td>
            <td class="colhead">' . $lang['invite_search_country'] . '</td>
            <td class="colhead">' . $lang['invite_search_edit'] . '</td></tr>';
        while ($row = mysqli_fetch_assoc($res)) {
            $country = ($row['name'] != null) ? '<td style="padding: 0;"><img src="' . $site_config['pic_baseurl'] . 'flag/' . (int) $row['flagpic'] . '" alt="' . htmlsafechars($row['name']) . '" /></td>' : '<td>---</td>';
            $HTMLOUT .= '<tr><td>' . format_username($row) . '</td>
        <td>' . get_date($row['added'], '') . '</td><td>' . get_date($row['last_access'], '') . '</td>
        <td>' . get_user_class_name($row['class']) . '</td>
        ' . $country . '
        <td>
        <a href="' . $site_config['baseurl'] . '/staffpanel.php?tool=invite_tree&amp;action=invite_tree&amp;id=' . (int) $row['id'] . '" title="' . $lang['invite_search_look'] . '"><span class="button is-small">' . $lang['invite_search_view'] . '</span></a></td></tr>';
        }
        $HTMLOUT .= '</table>';
    } else {
        $HTMLOUT .= $lang['invite_search_none'];
    }
    $HTMLOUT .= ($arr_count[0] > $perpage) ? '<br>' . $menu . '' : '<br><br>';
}
echo stdhead($lang['invite_stdhead']) . $HTMLOUT . stdfoot();
