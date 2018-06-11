<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $lang;

$lang          = array_merge($lang, load_language('ad_rep_ad'));
$input         = array_merge($_GET, $_POST);
$input['mode'] = isset($input['mode']) ? $input['mode'] : '';
$now_date      = '';
$reputationid  = 0;
$time_offset   = 0;
$a             = explode(',', gmdate('Y,n,j,G,i,s', TIME_NOW + $time_offset));
$now_date      = [
    'year'    => $a[0],
    'mon'     => $a[1],
    'mday'    => $a[2],
    'hours'   => $a[3],
    'minutes' => $a[4],
    'seconds' => $a[5],
];
switch ($input['mode']) {
    case 'modify':
        show_level();
        break;

    case 'add':
        show_form('new');
        break;

    case 'doadd':
        do_update('new');
        break;

    case 'edit':
        show_form('edit');
        break;

    case 'doedit':
        do_update('edit');
        break;

    case 'doupdate':
        do_update();
        break;

    case 'dodelete':
        do_delete();
        break;

    case 'list':
        view_list();
        break;

//    case 'dolist':
//        do_list();
//        break;

    case 'editrep':
        //show_form_rep('edit');
        show_form_rep();
        break;

    case 'doeditrep':
        do_edit_rep();
        break;

    case 'dodelrep':
        do_delete_rep();
        break;

    default:
        show_level();
        break;
}
function show_level()
{
    global $lang;
    $title = $lang['rep_ad_show_title'];
    $html  = "<p>{$lang['rep_ad_show_html1']}<br>{$lang['rep_ad_show_html2']}</p><br>";
    $query = sql_query('SELECT * FROM reputationlevel ORDER BY minimumreputation ASC');
    if (!mysqli_num_rows($query)) {
        do_update('new');

        return;
    }
    $css = "style='font-weight: bold;color: #fff;background-color: #1E1E1E; padding: 5px;'";
    $html .= "<h2>{$lang['rep_ad_show_head']}</h2>";
    $html .= "<p><span class='button is-small'><a href='staffpanel.php?tool=reputation_ad&amp;mode=list'>{$lang['rep_ad_show_comments']}</a></span></p><br>";
    $html .= "<form action='staffpanel.php?tool=reputation_ad' name='show_rep_form' method='post'>
                <input name='mode' value='doupdate' type='hidden' />";
    $html .= "<table><tr>
        <td width='5%' $css>{$lang['rep_ad_show_id']}</td>
        <td width='60%'$css>{$lang['rep_ad_show_level']}</td>
        <td width='20%' $css>{$lang['rep_ad_show_min']}</td>
        <td width='15%' $css>{$lang['rep_ad_show_controls']}</td></tr>";
    while ($res = mysqli_fetch_assoc($query)) {
        $html .= "<tr>\n" . '    <td>#' . $res['reputationlevelid'] . "</td>\n" . "    <td>{$lang['rep_ad_show_user']} <b>" . htmlsafechars($res['level']) . "</b></td>\n" . "    <td><input type='text' name='reputation[" . $res['reputationlevelid'] . "]' value='" . $res['minimumreputation'] . "' size='12' /></td>\n" . "    <td><span class='button is-small'><a href='staffpanel.php?tool=reputation_ad&amp;mode=edit&amp;reputationlevelid=" . $res['reputationlevelid'] . "'>{$lang['rep_ad_show_edit']}</a></span>&#160;<span class='button is-small'><a href='staffpanel.php?tool=reputation_ad&amp;mode=dodelete&amp;reputationlevelid=" . $res['reputationlevelid'] . "'>{$lang['rep_ad_show_del']}</a></span></td>\n" . "</tr>\n";
    }
    $html .= "<tr><td colspan='3'>
                    <input type='submit' value='{$lang['rep_ad_show_update']}' accesskey='s' class='button is-small' /> 
                    <input type='reset' value='{$lang['rep_ad_show_reset']}' accesskey='r' class='button is-small' /></td>
                    <td><span class='button is-small'><a href='staffpanel.php?tool=reputation_ad&amp;mode=add'>{$lang['rep_ad_show_add']}</a></span>
                    </td></tr>";
    $html .= '</table>';
    $html .= '</form>';
    html_out($html, $title);
}

/**
 * @param string $type
 */
function show_form($type = 'edit')
{
    global $input, $lang;
    $html = $lang['rep_ad_form_html'];
    if ($type === 'edit') {
        $query = sql_query('SELECT * FROM reputationlevel WHERE reputationlevelid=' . intval($input['reputationlevelid'])) or sqlerr(__LINE__, __FILE__);
        if (!$res = mysqli_fetch_assoc($query)) {
            stderr($lang['rep_ad_form_error'], $lang['rep_ad_form_error_msg']);
        }
        $title = $lang['rep_ad_form_title'];
        $html .= "<br><span style='font-weight:normal;'>" . htmlsafechars($res['level']) . " ({$lang['rep_ad_form_id']}{$res['reputationlevelid']})</span><br>";
        $button = $lang['rep_ad_form_btn'];
        $extra  = "<input type='button' class='button is-small' value='{$lang['rep_ad_form_back']}' accesskey='b' class='button is-small' onclick='javascript:history.back(1)' />";
        $mode   = 'doedit';
    } else {
        $title  = $lang['rep_ad_form_add_title'];
        $button = $lang['rep_ad_form_add_btn'];
        $mode   = 'doadd';
        $extra  = "<input type='button' value='{$lang['rep_ad_form_back']}' accesskey='b' class='button is-small' onclick='javascript:history.back(1)' />";
    }
    $css      = "style='font-weight: bold;color: #fff;background-color: #0055A4;padding: 5px;'";
    $replevid = isset($res['reputationlevelid']) ? $res['reputationlevelid'] : '';
    $replevel = isset($res['level']) ? $res['level'] : '';
    $minrep   = isset($res['minimumreputation']) ? $res['minimumreputation'] : '';
    $html .= "<form action='staffpanel.php?tool=reputation_ad' name='show_rep_form' method='post'>
                <input name='reputationlevelid' value='{$replevid}' type='hidden' />
                <input name='mode' value='{$mode}' type='hidden' />";
    $html .= "<h2>$title</h2><table width='500px'><tr>
        <td width='67%' $css>&#160;</td>
        <td width='33%' $css>&#160;</td></tr>";
    $html .= "<tr><td>{$lang['rep_ad_form_desc']}<div class='desctext'>{$lang['rep_ad_form_descr']}</div></td>";
    $html .= "<td><input type='text' name='level' value=\"{$replevel}\" size='35' maxlength='250' /></td></tr>";
    $html .= "<tr><td>{$lang['rep_ad_form_min']}<div>{$lang['rep_ad_form_option']}</div></td>";
    $html .= "<td><input type='text' name='minimumreputation' value=\"{$minrep}\" size='35' maxlength='10' /></td></tr>";
    $html .= "<tr><td colspan='2'><input type='submit' value='$button' accesskey='s' class='button is-small' /> <input type='reset' value='{$lang['rep_ad_show_reset']}' accesskey='r' class='button is-small' /> $extra</td></tr>";
    $html .= '</table>';
    $html .= '</form>';
    html_out($html, $title);
}

/////////////////////////////////////
//    Update rep function
/////////////////////////////////////
/**
 * @param string $type
 */
function do_update($type = '')
{
    global $input, $lang;
    if ($type != '') {
        $level = strip_tags($input['level']);
        $level = trim($level);
        if ((strlen($input['level']) < 2) || ($level == '')) {
            stderr('', $lang['rep_ad_update_err1']);
        }
        if (strlen($input['level']) > 250) {
            stderr('', $lang['rep_ad_update_err2']);
        }
        $level    = sqlesc($level);
        $minrep   = sqlesc(intval($input['minimumreputation']));
        $redirect = '' . $lang['rep_ad_update_saved'] . ' <i>' . htmlsafechars($input['level'], ENT_QUOTES) . '</i> ' . $lang['rep_ad_update_success'] . '';
    }
    // what we gonna do?
    if ($type === 'new') {
        @sql_query("INSERT INTO reputationlevel ( minimumreputation, level ) 
                            VALUES  ($minrep, $level )");
    } elseif ($type === 'edit') {
        $levelid = intval($input['reputationlevelid']);
        if (!is_valid_id($levelid)) {
            stderr('', $lang['rep_ad_update_err3']);
        }
        // check it's a valid rep id
        $query = sql_query("SELECT reputationlevelid FROM reputationlevel WHERE 
                                    reputationlevelid = $levelid");
        if (!mysqli_num_rows($query)) {
            stderr('', $lang['rep_ad_update_err4']);
        }
        @sql_query("UPDATE reputationlevel SET minimumreputation = $minrep, level = $level 
                            WHERE reputationlevelid = $levelid");
    } else {
        $ids = $input['reputation'];
        if (is_array($ids) && count($ids)) {
            foreach ($ids as $k => $v) {
                @sql_query('UPDATE reputationlevel SET minimumreputation = ' . intval($v) . ' WHERE reputationlevelid = ' . intval($k));
            }
        } else {
            stderr('', $lang['rep_ad_update_err4']);
        }
        $redirect = $lang['rep_ad_update_save_success'];
    }
    rep_cache();
    redirect('staffpanel.php?tool=reputation_ad&amp;mode=done', $redirect);
}

//////////////////////////////////////
//    Reputaion delete
//////////////////////////////////////
function do_delete()
{
    global $input, $lang;
    if (!isset($input['reputationlevelid']) || !is_valid_id($input['reputationlevelid'])) {
        stderr('', 'No valid ID.');
    }
    $levelid = intval($input['reputationlevelid']);
    // check the id is valid within db
    $query = sql_query("SELECT reputationlevelid FROM reputationlevel WHERE reputationlevelid=$levelid");
    if (!mysqli_num_rows($query)) {
        stderr('', $lang['rep_ad_delete_no']);
    }
    // if we here, we delete it!
    @sql_query("DELETE FROM reputationlevel WHERE reputationlevelid=$levelid");
    rep_cache();
    redirect('staffpanel.php?tool=reputation_ad&amp;mode=done', $lang['rep_ad_delete_success'], 5);
}

//////////////////////////////////////
//    Reputaion edit
//////////////////////////////////////
function show_form_rep()
{
    global $input, $lang;
    if (!isset($input['reputationid']) || !is_valid_id($input['reputationid'])) {
        stderr('', $lang['rep_ad_rep_form_nothing']);
    }
    $title = $lang['rep_ad_rep_form_title'];
    $query = sql_query('SELECT r.*, p.topic_id, t.topic_name, leftfor.username AS leftfor_name, 
                    leftby.username AS leftby_name
                    FROM reputation r
                    LEFT JOIN posts p ON p.id=r.postid
                    LEFT JOIN topics t ON p.topic_id=t.id
                    LEFT JOIN users leftfor ON leftfor.id=r.userid
                    LEFT JOIN users leftby ON leftby.id=r.whoadded
                    WHERE reputationid = ' . intval($input['reputationid']));
    if (!$res = mysqli_fetch_assoc($query)) {
        stderr('', $lang['rep_ad_rep_form_erm']);
    }
    $html = "<form action='staffpanel.php?tool=reputation_ad' name='show_rep_form' method='post'>
                <input name='reputationid' value='{$res['reputationid']}' type='hidden' />
                <input name='oldreputation' value='{$res['reputation']}' type='hidden' />
                <input name='mode' value='doeditrep' type='hidden' />";
    $html .= "<h2>{$lang['rep_ad_rep_form_head']}</h2>";
    $html .= '<table>';
    $html .= "<tr><td width='37%'>{$lang['rep_ad_rep_form_topic']}</td><td width='63%'><a href='forums.php?action=viewtopic&amp;topicid={$res['topic_id']}&amp;page=p{$res['postid']}#{$res['postid']}' target='_blank'>" . htmlsafechars($res['topic_name']) . '</a></td></tr>';
    $html .= "<tr><td>{$lang['rep_ad_rep_form_left_by']}</td><td>{$res['leftby_name']}</td></tr>";
    $html .= "<tr><td>{$lang['rep_ad_rep_form_left_for']}</td><td width='63%'>{$res['leftfor_name']}</td></tr>";
    $html .= "<tr><td>{$lang['rep_ad_rep_form_comment']}</td><td width='63%'><input type='text' name='reason' value='" . htmlsafechars($res['reason']) . "' size='35' maxlength='250' /></td></tr>";
    $html .= "<tr><td>{$lang['rep_ad_rep_form_rep']}</td><td><input type='text' name='reputation' value='{$res['reputation']}' size='35' maxlength='10' /></td></tr>";
    $html .= "<tr><td colspan='2'><input type='submit' value='{$lang['rep_ad_rep_form_save']}' accesskey='s' class='button is-small' /> <input type='reset' tabindex='1' value='{$lang['rep_ad_rep_form_reset']}' accesskey='r' class='button is-small' /></td></tr>";
    $html .= '</table></form>';
    html_out($html, $title);
}

/////////////////////////////////////
//    View reputation comments function
/////////////////////////////////////
function view_list()
{
    global $now_date, $time_offset, $input, $lang;
    $title = $lang['rep_ad_view_title'];
    $html  = "<h2>{$lang['rep_ad_view_view']}</h2>";
    $html .= "<p>{$lang['rep_ad_view_page']}</p>";
    $html .= "<form action='staffpanel.php?tool=reputation_ad' name='list_form' method='post'>
                <input name='mode' value='list' type='hidden' />
                <input name='dolist' value='1' type='hidden' />";
    $html .= "<table width='500px'>";
    $html .= "<tr><td width='20%'>{$lang['rep_ad_view_for']}</td><td width='80%'><input type='text' name='leftfor' value='' size='35' maxlength='250' tabindex='1' /></td></tr>";
    $html .= "<tr><td colspan='2'><div>{$lang['rep_ad_view_for_txt']}</div></td></tr>";
    $html .= "<tr><td>{$lang['rep_ad_view_by']}</td><td><input type='text' name='leftby' value='' size='35' maxlength='250' tabindex='2' /></td></tr>";
    $html .= "<tr><td colspan='2'><div>{$lang['rep_ad_view_by_txt']}</div></td></tr>";
    $html .= "<tr><td>{$lang['rep_ad_view_start']}</td><td>
        <div>
                <span style='padding-right:5px; float:left;'>{$lang['rep_ad_view_month']}<br><select name='start[month]' tabindex='3'>" . get_month_dropdown(1) . "</select></span>
                <span style='padding-right:5px; float:left;'>{$lang['rep_ad_view_day']}<br><input type='text' name='start[day]' value='" . ($now_date['mday'] + 1) . "' size='4' maxlength='2' tabindex='3' /></span>
                <span>{{$lang['rep_ad_view_year']}}<br><input type='text' name='start[year]' value='" . $now_date['year'] . "' size='4' maxlength='4' tabindex='3' /></span>
            </div></td></tr>";
    $html .= "<tr><td class='tdrow2' colspan='2'><div class='desctext'>{{$lang['rep_ad_view_start_select']}}</div></td></tr>";
    $html .= "<tr><td>{$lang['rep_ad_view_end']}</td><td>
            <div>
                <span style='padding-right:5px; float:left;'>{$lang['rep_ad_view_month']}<br><select name='end[month]' class='textinput' tabindex='4'>" . get_month_dropdown() . "</select></span>
                <span style='padding-right:5px; float:left;'>{$lang['rep_ad_view_day']}<br><input type='text' class='textinput' name='end[day]' value='" . $now_date['mday'] . "' size='4' maxlength='2' tabindex='4' /></span>
                <span>{$lang['rep_ad_view_year']}<br><input type='text' class='textinput' name='end[year]' value='" . $now_date['year'] . "' size='4' maxlength='4' tabindex='4' /></span>
            </div></td></tr>";
    $html .= "<tr><td class='tdrow2' colspan='2'><div class='desctext'>{$lang['rep_ad_view_end_select']}</div></td></tr>";
    $html .= "<tr><td colspan='2'><input type='submit' value='{$lang['rep_ad_view_search']}' accesskey='s' class='button is-small' tabindex='5' /> <input type='reset' value='{$lang['rep_ad_view_reset']}' accesskey='r' class='button is-small' tabindex='6' /></td></tr>";
    $html .= '</table></form>';
    //echo $html; exit;
    // I hate work, but someone has to do it!
    if (isset($input['dolist'])) {
        $input['orderby'] = isset($input['orderby']) ? $input['orderby'] : '';
        $who              = isset($input['who']) ? (int) $input['who'] : 0;
        $user             = isset($input['user']) ? $input['user'] : 0;
        $first            = isset($input['page']) ? intval($input['page']) : 0;
        $cond             = $who ? 'r.whoadded=' . sqlesc($who) : '';
        $start            = isset($input['startstamp']) ? intval($input['startstamp']) : mktime(0, 0, 0, $input['start']['month'], $input['start']['day'], $input['start']['year']) + $time_offset;
        $end              = isset($input['endstamp']) ? intval($input['endstamp']) : mktime(0, 0, 0, $input['end']['month'], $input['end']['day'] + 1, $input['end']['year'])       + $time_offset;
        if (!$start) {
            $start = TIME_NOW - (3600 * 24 * 30);
        }
        if (!$end) {
            $end = TIME_NOW;
        }
        if ($start >= $end) {
            stderr($lang['rep_ad_view_err1'], $lang['rep_ad_view_err2']);
        }
        if (!empty($input['leftby'])) {
            $left_b = @sql_query('SELECT id FROM users WHERE username = ' . sqlesc($input['leftby']));
            if (!mysqli_num_rows($left_b)) {
                stderr($lang['rep_ad_view_err3'], $lang['rep_ad_view_err4'] . htmlsafechars($input['leftby'], ENT_QUOTES));
            }
            $leftby = mysqli_fetch_assoc($left_b);
            $who    = $leftby['id'];
            $cond   = 'r.whoadded=' . $who;
        }
        if (!empty($input['leftfor'])) {
            $left_f = @sql_query('SELECT id FROM users WHERE username = ' . sqlesc($input['leftfor']));
            if (!mysqli_num_rows($left_f)) {
                stderr($lang['rep_ad_view_err3'], $lang['rep_ad_view_err4'] . htmlsafechars($input['leftfor'], ENT_QUOTES));
            }
            $leftfor = mysqli_fetch_assoc($left_f);
            $user    = $leftfor['id'];
            $cond .= ($cond ? ' AND' : '') . ' r.userid=' . $user;
        }
        if ($start) {
            $cond .= ($cond ? ' AND' : '') . " r.dateadd >= $start";
        }
        if ($end) {
            $cond .= ($cond ? ' AND' : '') . " r.dateadd <= $end";
        }
        switch ($input['orderby']) {
            case 'leftbyuser':
                $order   = 'leftby.username';
                $orderby = 'leftbyuser';
                break;

            case 'leftforuser':
                $order   = 'leftfor.username';
                $orderby = 'leftforuser';
                break;

            default:
                $order   = 'r.dateadd';
                $orderby = 'dateadd';
        }
        $css          = "style='font-weight: bold;color: #fff;background-color: #0055A4;padding: 5px;'";
        $html         = "<h2>{$lang['rep_ad_view_cmts']}</h2>";
        $table_header = "<table width='80%' border='1'><tr $css>";
        $table_header .= "<td width='5%'>{$lang['rep_ad_view_id']}</td>";
        $table_header .= "<td width='20%'><a href='staffpanel.php?tool=reputation_ad&amp;mode=list&amp;dolist=1&amp;who=" . intval($who) . '&amp;user=' . intval($user) . "&amp;orderby=leftbyuser&amp;startstamp=$start&amp;endstamp=$end&amp;page=$first'>{$lang['rep_ad_view_by']}</a></td>";
        $table_header .= "<td width='20%'><a href='staffpanel.php?tool=reputation_ad&amp;mode=list&amp;dolist=1&amp;who=" . intval($who) . '&amp;user=' . intval($user) . "&amp;orderby=leftforuser&amp;startstamp=$start&amp;endstamp=$end&amp;page=$first'>{$lang['rep_ad_view_for']}</a></td>";
        $table_header .= "<td width='17%'><a href='staffpanel.php?tool=reputation_ad&amp;mode=list&amp;dolist=1&amp;who=" . intval($who) . '&amp;user=' . intval($user) . "&amp;orderby=date&amp;startstamp=$start&amp;endstamp=$end&amp;page=$first'>{$lang['rep_ad_view_date']}</a></td>";
        $table_header .= "<td width='5%'>{$lang['rep_ad_view_point']}</td>";
        $table_header .= "<td width='23%'>{$lang['rep_ad_view_reason']}</td>";
        $table_header .= "<td width='10%'>{$lang['rep_ad_view_controls']}</td></tr>";
        $html         .= $table_header;
        // do the count for pager etc
        $query = sql_query("SELECT COUNT(*) AS cnt FROM reputation r WHERE $cond");
        //echo_r($input); exit;
        $total = mysqli_fetch_assoc($query);
        if (!$total['cnt']) {
            $html .= "<tr><td colspan='7'>{$lang['rep_ad_view_none_found']}</td></tr>";
        }
        // do the pager thang!
        $deflimit = 10;
        $links    = "<span style=\"background: #F0F5FA; border: 1px solid #072A66;padding: 1px 3px 1px 3px;\">{$total['cnt']}&#160;{$lang['rep_ad_view_records']}</span>";
        if ($total['cnt'] > $deflimit) {
            require_once INCL_DIR . 'pager_functions.php';
            $links = pager_rep([
                                   'count'       => $total['cnt'],
                                   'perpage'     => $deflimit,
                                   'start_value' => $first,
                                   'url'         => 'staffpanel.php?tool=reputation_ad&amp;mode=list&amp;dolist=1&amp;who=' . intval($who) . '&amp;user=' . intval($user) . "&amp;orderby=$orderby&amp;startstamp=$start&amp;endstamp=$end",
                               ]);
        }
        // mofo query!
        $query = sql_query("SELECT r.*, p.topic_id, leftfor.id as leftfor_id, 
                                    leftfor.username as leftfor_name, leftby.id as leftby_id, 
                                    leftby.username as leftby_name 
                                    FROM reputation r 
                                    left join posts p on p.id=r.postid 
                                    left join users leftfor on leftfor.id=r.userid 
                                    left join users leftby on leftby.id=r.whoadded 
                                    WHERE $cond ORDER BY $order LIMIT $first,$deflimit");
        if (!mysqli_num_rows($query)) {
            stderr($lang['rep_ad_view_err3'], $lang['rep_ad_view_err5']);
        }
        while ($r = mysqli_fetch_assoc($query)) {
            $r['dateadd'] = date('M j, Y, g:i a', $r['dateadd']);
            $html .= "<tr><td>#{$r['reputationid']}</td>";
            $html .= "<td><a href='userdetails.php?id={$r['leftby_id']}' target='_blank'>{$r['leftby_name']}</a></td>";
            $html .= "<td><a href='userdetails.php?id={$r['leftfor_id']}' target='_blank'>{$r['leftfor_name']}</a></td>";
            $html .= "<td>{$r['dateadd']}</td>";
            $html .= "<td>{$r['reputation']}</td>";
            $html .= "<td><a href='forums.php?action=viewtopic&amp;topicid={$r['topic_id']}&amp;page=p{$r['postid']}#{$r['postid']}' target='_blank'>" . htmlsafechars($r['reason']) . '</a></td>';
            $html .= "<td><a href='staffpanel.php?tool=reputation_ad&amp;mode=editrep&amp;reputationid={$r['reputationid']}'><span class='button is-small'>{$lang['rep_ad_view_edit']}</span></a>&#160;<a href='reputation_ad.php?mode=dodelrep&amp;reputationid={$r['reputationid']}'><span class='button is-small'>{$lang['rep_ad_view_delete']}</span></a></td></tr>";
        }
        $html .= '</table>';
        $html .= "<br><div>$links</div>";
    }
    html_out($html, $title);
}

///////////////////////////////////////////////
//    Reputation do_delete_rep function
///////////////////////////////////////////////
function do_delete_rep()
{
    global $input, $lang, $site_config, $cache;

    if (!is_valid_id($input['reputationid'])) {
        stderr($lang['rep_ad_delete_rep_err1'], $lang['rep_ad_delete_rep_err2']);
    }
    // check it's a valid ID.
    $query = sql_query('SELECT reputationid, reputation, userid FROM reputation WHERE reputationid=' . intval($input['reputationid']));
    if (($r = mysqli_fetch_assoc($query)) === false) {
        stderr($lang['rep_ad_delete_rep_err3'], $lang['rep_ad_delete_rep_err4']);
    }
    $sql  = sql_query('SELECT reputation ' . 'FROM users ' . 'WHERE id = ' . sqlesc($input['reputationid'])) or sqlerr(__FILE__, __LINE__);
    $User = mysqli_fetch_assoc($sql);
    // do the delete
    sql_query('DELETE FROM reputation WHERE reputationid=' . intval($r['reputationid']));
    sql_query("UPDATE users SET reputation = (reputation-{$r['reputation']} ) WHERE id=" . intval($r['userid']));
    $update['rep'] = ($User['reputation'] - $r['reputation']);
    $cache->update_row('user' . $r['userid'], [
        'reputation' => $update['rep'],
    ], $site_config['expires']['user_cache']);
    redirect('staffpanel.php?tool=reputation_ad&amp;mode=list', $lang['rep_ad_delete_rep_success'], 5);
}

///////////////////////////////////////////////
//    Reputation do_edit_rep function
///////////////////////////////////////////////
function do_edit_rep()
{
    global $input, $lang, $site_config, $cache;

    if (isset($input['reason']) && !empty($input['reason'])) {
        $reason = str_replace('<br>', '', $input['reason']);
        $reason = trim($reason);
        if ((strlen(trim($reason)) < 2) || ($reason == '')) {
            stderr($lang['rep_ad_edit_txt'], $lang['rep_ad_edit_short']);
        }
        if (strlen($input['reason']) > 250) {
            stderr($lang['rep_ad_edit_txt'], $lang['rep_ad_edit_long']);
        }
    }
    $oldrep = intval($input['oldreputation']);
    $newrep = intval($input['reputation']);
    // valid ID?
    $query = sql_query('SELECT reputationid, reason, userid FROM reputation WHERE reputationid=' . intval($input['reputationid']));
    if ($r = mysqli_fetch_assoc($query) === false) {
        stderr($lang['rep_ad_edit_input'], $lang['rep_ad_edit_noid']);
    }
    if ($oldrep != $newrep) {
        if ($r['reason'] != $reason) {
            @sql_query('UPDATE reputation SET reputation = ' . intval($newrep) . ', reason = ' . sqlesc($reason) . ' WHERE reputationid = ' . intval($r['reputationid']));
        }
        $sql  = sql_query('SELECT reputation ' . 'FROM users ' . 'WHERE id = ' . sqlesc($input['reputationid'])) or sqlerr(__FILE__, __LINE__);
        $User = mysqli_fetch_assoc($sql);
        $diff = $oldrep - $newrep;
        @sql_query("UPDATE users SET reputation = (reputation-{$diff}) WHERE id=" . intval($r['userid']));
        $update['rep'] = ($User['reputation'] - $diff);
        $cache->update_row('user' . $r['userid'], [
            'reputation' => $update['rep'],
        ], $site_config['expires']['user_cache']);
        $cache->delete('user' . $r['userid']);
    }
    redirect('staffpanel.php?tool=reputation_ad&amp;mode=list', "{$lang['rep_ad_edit_saved']} {$r['reputationid']} {$lang['rep_ad_edit_success']}", 5);
}

///////////////////////////////////////////////
//    Reputation output function
//    $msg -> string
//    $html -> string
///////////////////////////////////////////////
/**
 * @param string $html
 * @param string $title
 */
function html_out($html = '', $title = '')
{
    global $lang;
    if (empty($html)) {
        stderr($lang['rep_ad_html_error'], $lang['rep_ad_html_nothing']);
    }
    echo stdhead($title) . $html . stdfoot();
    die();
}

/**
 * @param     $url
 * @param     $text
 * @param int $time
 */
function redirect($url, $text, $time = 2)
{
    global $site_config, $lang;

    $html = "<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <meta http-equiv='refresh' content='{$time}; url={$site_config['baseurl']}/{$url}' />
<title>{$lang['rep_ad_redirect_title']}</title>
<link rel='stylesheet' href='" . get_file_name('css') . "' />
</head>
<body>
    <div>
        <div>{$lang['rep_ad_redirect_redirect']}</div>
            <div style='padding: 8px;'>
                <div style='font-size: 12px;'>$text
                <br>
                <br>
                <a href='{$site_config['baseurl']}/{$url}'>{$lang['rep_ad_redirect_not']}</a>
            </div>
        </div>
    </div>
</body>
</html>";
    echo $html;
    exit;
}

/////////////////////////////
//    get_month worker function
/////////////////////////////
/**
 * @param int $i
 *
 * @return string
 */
function get_month_dropdown($i = 0)
{
    global $now_date, $lang;
    $return = '';
    $month  = [
        '----',
        $lang['rep_ad_month_jan'],
        $lang['rep_ad_month_feb'],
        $lang['rep_ad_month_mar'],
        $lang['rep_ad_month_apr'],
        $lang['rep_ad_month_may'],
        $lang['rep_ad_month_june'],
        $lang['rep_ad_month_july'],
        $lang['rep_ad_month_aug'],
        $lang['rep_ad_month_sept'],
        $lang['rep_ad_month_oct'],
        $lang['rep_ad_month_nov'],
        $lang['rep_ad_month_dec'],
    ];
    foreach ($month as $k => $m) {
        $return .= "\t<option value='" . $k . "'";
        $return .= (($k + $i) == $now_date['mon']) ? ' selected' : '';
        $return .= '>' . $m . "</option>\n";
    }

    return $return;
}

/////////////////////////////
//    cache rep function
/////////////////////////////
function rep_cache()
{
    global $lang;
    $query = @sql_query('SELECT * FROM reputationlevel');
    if (!mysqli_num_rows($query)) {
        stderr($lang['rep_ad_cache_cache'], $lang['rep_ad_cache_none']);
    }
    $rep_out = '<' . "?php\n\n\$reputations = [\n";
    while ($row = mysqli_fetch_assoc($query)) {
        $rep_out .= "\t{$row['minimumreputation']} => '{$row['level']}',\n";
    }
    $rep_out .= "\n];";
    file_put_contents(CACHE_DIR . 'rep_cache.php', $rep_out);
}
