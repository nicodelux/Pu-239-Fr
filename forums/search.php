<?php

global $lang;
$author_error = $content = $count = $count2 = $edited_by = $row_count = $over_forum_id = $selected_forums = $author_id = $content = '';
//=== get all the search stuff
$search      = (isset($_GET['search']) ? strip_tags(trim($_GET['search'])) : '');
$author      = (isset($_GET['author']) ? trim(htmlsafechars($_GET['author'])) : '');
$search_what = ((isset($_GET['search_what']) && 'body' === $_GET['search_what']) ? 'body' : ((isset($_GET['search_what']) && 'title' === $_GET['search_what']) ? 'title' : 'all'));
$search_when = (isset($_GET['search_when']) ? intval($_GET['search_when']) : 0);
$sort_by     = ((isset($_GET['sort_by']) && 'date' === $_GET['sort_by']) ? 'date' : 'relevance');
$asc_desc    = ((isset($_GET['asc_desc']) && 'ASC' === $_GET['asc_desc']) ? 'ASC' : 'DESC');
$show_as     = ((isset($_GET['show_as']) && 'posts' === $_GET['show_as']) ? 'posts' : 'list');
//=== get links for pager... must think of a better way to do this
$pager_links = '';
$pager_links .= ($search ? '&amp;search=' . $search : '');
$pager_links .= ($author ? '&amp;author=' . $author : '');
$pager_links .= ($search_what ? '&amp;search_what=' . $search_what : '');
$pager_links .= ($search_when ? '&amp;search_when=' . $search_when : '');
$pager_links .= ($sort_by ? '&amp;sort_by=' . $sort_by : '');
$pager_links .= ($asc_desc ? '&amp;asc_desc=' . $asc_desc : '');
$pager_links .= ($show_as ? '&amp;show_as=' . $show_as : '');
if ($author) {
    //=== get member info
    $res_member = sql_query('SELECT id FROM users WHERE username LIKE ' . sqlesc($author)) or sqlerr(__FILE__, __LINE__);
    $arr_member = mysqli_fetch_assoc($res_member);
    $author_id  = (int) $arr_member['id'];
    //=== if no member found
    $author_error = (0 == $author_id ? '<h1>' . $lang['sea_sorry_no_member_found_with_that_username.'] . '</h1>' . $lang['sea_please_check_the_spelling.'] . '<br>' : '');
}
//=== if searched
if ($search) {
    $search_where = ('body' === $search_what ? 'p.body' : ('title' === $search_what ? 't.topic_name, p.post_title' : 'p.post_title, p.body, t.topic_name'));
    //=== get the forum id list to check if any were selected
    $res_forum_ids = sql_query('SELECT id FROM forums') or sqlerr(__FILE__, __LINE__);
    while ($arr_forum_ids = mysqli_fetch_assoc($res_forum_ids)) {
        //$selected_forums[] = (isset($_GET["f$arr_forum_ids[id]"]) ? $arr_forum_ids['id'] : '');
        if (isset($_GET["f$arr_forum_ids[id]"])) {
            $selected_forums[] = $arr_forum_ids['id'];
        }
    }
    if (1 == count($selected_forums)) {
        $selected_forums_undone = implode(' ', $selected_forums);
        $selected_forums_undone = ' AND t.forum_id =' . $selected_forums_undone;
    } elseif (count($selected_forums) > 1) {
        $selected_forums_undone = implode(' OR t.forum_id=', $selected_forums);
        $selected_forums_undone = ' AND t.forum_id =' . $selected_forums_undone;
    }
    $AND = '';
    if ($author_id) {
        $AND .= ' AND p.user_id = ' . $author_id;
    }
    if ($search_when) {
        $AND .= ' AND p.added >= ' . (TIME_NOW - $search_when);
    }
    if ($selected_forums_undone) {
        $AND .= $selected_forums_undone;
    }
    //=== just do the minimum to get the count
    $res_count = sql_query('SELECT p.id, MATCH (' . $search_where . ') AGAINST (' . sqlesc($search) . ' IN BOOLEAN MODE) AS relevance FROM posts AS p LEFT JOIN topics AS t ON p.topic_id = t.id LEFT JOIN forums AS f ON t.forum_id = f.id WHERE MATCH (' . $search_where . ') AGAINST (' . sqlesc($search) . 'IN BOOLEAN MODE) AND ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND t.status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? 'p.status != \'deleted\' AND t.status != \'deleted\'  AND' : '')) . ' f.min_class_read <= ' . $CURUSER['class'] . $AND . ' HAVING relevance > 0.2') or sqlerr(__FILE__, __LINE__);
    $count = mysqli_num_rows($res_count);
    //=== get stuff for the pager
    $page               = isset($_GET['page']) ? (int) $_GET['page'] : 0;
    $perpage            = isset($_GET['perpage']) ? (int) $_GET['perpage'] : 10;
    list($menu, $LIMIT) = pager_new($count, $perpage, $page, 'forums.php?action=search' . (isset($_GET['perpage']) ? '&amp;perpage=' . $perpage : '') . $pager_links);
    $order_by           = ((isset($_GET['sort_by']) && 'date' === $_GET['sort_by']) ? 'p.added ' : 'relevance ');
    $ASC_DESC           = ((isset($_GET['asc_desc']) && 'ASC' === $_GET['asc_desc']) ? ' ASC ' : ' DESC ');
    //=== main search... could split it up for list / post thing, but it's only a couple of things so it seems pointless...
    $res = sql_query('SELECT p.id AS post_id, p.body, p.post_title, p.added, p.icon, p.edited_by, p.edit_reason, p.edit_date, p.bbcode, p.anonymous AS pan, t.anonymous AS tan, t.id AS topic_id, t.topic_name AS   topic_title, t.topic_desc, t.post_count, t.views, t.locked, t.sticky, t.poll_id, t.num_ratings, t.rating_sum, f.id AS forum_id, f.name AS forum_name, f.description AS forum_desc, u.id, u.username, u.class, u.donor, u.suspended, u.warned, u.enabled, u.chatpost, u.leechwarn, u.pirate, u.king,  u.title, u.avatar, u.offensive_avatar, MATCH (' . $search_where . ') AGAINST (' . sqlesc($search) . ' IN BOOLEAN MODE) AS relevance FROM posts AS p LEFT JOIN topics AS t ON p.topic_id = t.id LEFT JOIN forums AS f ON t.forum_id = f.id LEFT JOIN users AS u ON p.user_id = u.id WHERE MATCH (' . $search_where . ') AGAINST (' . sqlesc($search) . ' IN BOOLEAN MODE) AND ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND t.status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? 'p.status != \'deleted\' AND t.status != \'deleted\'  AND' : '')) . ' f.min_class_read <= ' . $CURUSER['class'] . $AND . ' HAVING relevance > 0.2 ORDER BY ' . $order_by . $ASC_DESC . $LIMIT) or sqlerr(__FILE__, __LINE__);
    //=== top and bottom stuff
    $the_top_and_bottom = '<table border="0" cellspacing="0" cellpadding="0" width="90%">
	<tr><td  valign="middle">' . (($count > $perpage) ? $menu : '') . '</td>
	</tr></table>';
    //=== nothing here? kill the page
    if (0 == $count) {
        $content .= '<br><a name="results"></a><br><table border="0" cellspacing="10" cellpadding="10" width="800px">
	<tr><tdalign="center">
	' . $lang['sea_nothing_found'] . ' 
	</td></tr>
	<tr><td>
	' . $lang['sea_please_try_again_with_a_refined_search_string.'] . '<br><br>
	</td></tr></table><br><br>';
    } else {
        //=== if show as list:
        if ('list' === $show_as) {
            $content .= ($count > 0 ? '<div style="font-weight: bold;">' . $lang['sea_search_results'] . ' ' . $count . ' </div>' : '') . '<br>' . $the_top_and_bottom . '
	<a name="results"></a>
	<table border="0" cellspacing="10" cellpadding="10" width="90%">	
	<tr>
	<td valign="middle" width="10"><img src="pic/forums/topic.gif" alt="' . $lang['fe_topic'] . '" title="' . $lang['fe_topic'] . '" /></td>
	<td valign="middle" width="10"><img src="pic/forums/topic_normal.gif" alt="' . $lang['fe_thread_icon'] . '" title="' . $lang['fe_thread_icon'] . '" /></td>
	<td align="left">' . $lang['sea_topic_post'] . '</td>
	<td align="left">' . $lang['sea_in_forum'] . '</td>
	<td>' . $lang['sea_relevance'] . '</td>
	<td width="10">' . $lang['fe_replies'] . '</td>
	<td width="10">' . $lang['fe_views'] . '</td>
	<td width="140">' . $lang['sea_date'] . '</td>
	</tr>';
            //=== lets do the loop
            while ($arr = mysqli_fetch_assoc($res)) {
                //=== change colors
                $count2 = (++$count2) % 2;
                $class  = (0 == $count2 ? 'one' : 'two');
                if ('all' === $search_what || 'title' === $search_what) {
                    $topic_title = highlightWords(htmlsafechars($arr['topic_title'], ENT_QUOTES), $search);
                    $topic_desc  = highlightWords(htmlsafechars($arr['topic_desc'], ENT_QUOTES), $search);
                    $post_title  = highlightWords(htmlsafechars($arr['post_title'], ENT_QUOTES), $search);
                } else {
                    $topic_title = htmlsafechars($arr['topic_title'], ENT_QUOTES);
                    $topic_desc  = htmlsafechars($arr['topic_desc'], ENT_QUOTES);
                    $post_title  = htmlsafechars($arr['post_title'], ENT_QUOTES);
                }
                $body        = format_comment($arr['body'], 1, 0);
                $search_post = str_replace(' ', '+', $search);
                $post_id     = (int) $arr['post_id'];
                $posts       = (int) $arr['post_count'];
                $post_text   = bubble('<img src="pic/forums/mg.gif" height="14" alt="' . $lang['fe_preview'] . '" />', $body, '' . $lang['fe_post_preview'] . '');
                $rpic        = (0 != $arr['num_ratings'] ? ratingpic_forums(round($arr['rating_sum'] / $arr['num_ratings'], 1)) : '');
                $content .= '<tr>
		<td class="' . $class . '"><img src="pic/forums/' . ($posts < 30 ? ('yes' == $arr['locked'] ? 'locked' : 'topic') : 'hot_topic') . '.gif" alt="' . $lang['fe_topic'] . '" title="' . $lang['fe_topic'] . '" /></td>
		<td class="' . $class . '">' . ('' == $arr['icon'] ? '<img src="pic/forums/topic_normal.gif" alt="' . $lang['fe_topic'] . '" title="' . $lang['fe_topic'] . '" />' : '<img src="pic/smilies/' . htmlsafechars($arr['icon']) . '.gif" alt="' . htmlsafechars($arr['icon']) . '" title="' . htmlsafechars($arr['icon']) . '" />') . '</td>
		<td align="left" valign="middle" class="' . $class . '">
		<table border="0" cellspacing="2" cellpadding="2">
		<tr>
		<td  class="' . $class . '" align="right"><span style="font-weight: bold;">' . $lang['fe_post'] . ': </span></td>
		<td  class="' . $class . '" align="left">
		<a class="altlink" href="forums.php?action=view_topic&amp;topic_id=15&amp;page=p' . (int) $arr['post_id'] . '&amp;search=' . $search_post . '#' . (int) $arr['post_id'] . '" title="' . $lang['sea_go_to_the_post'] . '">' . ('' == $post_title ? '' . $lang['fe_link_to_post'] . '' : $post_title) . '</a></td>
		<td class="' . $class . '" align="right"></td>
		</tr>
		<tr>
		<td  class="' . $class . '" align="right"><span style="font-style: italic;">by: </span></td>
		<td  class="' . $class . '" align="left">' . ('yes' == $arr['pan'] ? '<i>' . $lang['fe_anonymous'] . '</i>' : format_username($arr)) . '</td>
		<td class="' . $class . '" align="right"></td>
		</tr>
		<tr>
		<td  class="' . $class . '" align="right"><span style="font-style: italic;">' . $lang['ep_in_topic'] . ': </span></td>
		<td  class="' . $class . '" align="left"> ' . ('yes' == $arr['sticky'] ? '<img src="pic/forums/pinned.gif" alt="' . $lang['fe_pinned'] . '" title="' . $lang['fe_pinned'] . '" />' : '') . ($arr['poll_id'] > 0 ? '<img src="pic/forums/poll.gif" alt="Poll" title="Poll" />' : '') . '
		<a class="altlink" href="forums.php?action=view_topic&amp;topic_id=' . (int) $arr['topic_id'] . '" title="' . $lang['sea_go_to_topic'] . '">' . $topic_title . '</a>' . $post_text . '</td>
		<td class="' . $class . '" align="right"> ' . $rpic . '</td>
		</tr>
		<tr>
		<td class="' . $class . '" align="right"></td>
		<td  class="' . $class . '" align="left"> ' . ('' != $topic_desc ? '&#9658; <span style="font-size: x-small;">' . $topic_desc . '</span>' : '') . '</td>
		<td class="' . $class . '" align="right"></td>
		</tr>
		</table>
		</td>
		<td align="left" class="' . $class . '">
		<a class="altlink" href="forums.php?action=view_forum&amp;forum_id=' . (int) $arr['forum_id'] . '" title="' . $lang['sea_go_to_forum'] . '">' . htmlsafechars($arr['forum_name'], ENT_QUOTES) . '</a>
		' . ('' != $arr['forum_desc'] ? '<br>&#9658; <span style="font-size: x-small;">' . htmlsafechars($arr['forum_desc'], ENT_QUOTES) . '</span>' : '') . '</td>
		<td class="' . $class . '">' . round($arr['relevance'], 3) . '</td>
		<td class="' . $class . '">' . number_format($posts - 1) . '</td>
		<td class="' . $class . '">' . number_format($arr['views']) . '</td>
		<td class="' . $class . '"><span style="white-space:nowrap;">' . get_date($arr['added'], '') . '</span><br></td>
		</tr>';
            }
            $content .= '</table>' . $the_top_and_bottom . '<br>
		<form action="#" method="get">
		<span style="font-weight: bold;">' . $lang['sea_per_page'] . ':</span> <select name="box" onchange="location = this.options[this.selectedIndex].value;">
		<option value=""> ' . $lang['sea_select'] . ' </option>
		<option value="forums.php?action=search&amp;page=0&amp;perpage=5' . $pager_links . '"' . (5 == $perpage ? ' selected="selected"' : '') . '>5</option>
		<option value="forums.php?action=search&amp;page=0&amp;perpage=20' . $pager_links . '"' . (20 == $perpage ? ' selected="selected"' : '') . '>20</option>
		<option value="forums.php?action=search&amp;page=0&amp;perpage=50' . $pager_links . '"' . (50 == $perpage ? ' selected="selected"' : '') . '>50</option>
		<option value="forums.php?action=search&amp;page=0&amp;perpage=100' . $pager_links . '"' . (100 == $perpage ? ' selected="selected"' : '') . '>100</option>
		<option value="forums.php?action=search&amp;page=0&amp;perpage=200' . $pager_links . '"' . (200 == $perpage ? ' selected="selected"' : '') . '>200</option>
		</select></form><br><br>';
        }
    } //=== end if searched
} //=== end if show as list :D now lets show as posts \\o\o/o//
//=== if show as posts:
if ('posts' === $show_as) {
    //=== the top
    $content .= ($count > 0 ? '<div style="font-weight: bold;">' . $lang['sea_search_results'] . ' ' . $count . ' </div>' : '') . '<br>' . $the_top_and_bottom . '
   <a name="results"></a>
	<table border="0" cellspacing="10" cellpadding="10" width="90%">';
    //=== lets do the loop
    while ($arr = mysqli_fetch_assoc($res)) {
        //=== change colors
        $count2     = (++$count2) % 2;
        $class      = (0 == $count2 ? 'one' : 'two');
        $class_alt  = (0 == $count2 ? 'two' : 'one');
        $post_title = ('' != $arr['post_title'] ? ' <span style="font-weight: bold; font-size: x-small;">' . htmlsafechars($arr['post_title'], ENT_QUOTES) . '</span>' : 'Link to Post');
        if ('all' === $search_what || 'title' === $search_what) {
            $topic_title = highlightWords(htmlsafechars($arr['topic_title'], ENT_QUOTES), $search);
            $topic_desc  = highlightWords(htmlsafechars($arr['topic_desc'], ENT_QUOTES), $search);
            $post_title  = highlightWords($post_title, $search);
        } else {
            $topic_title = htmlsafechars($arr['topic_title'], ENT_QUOTES);
            $topic_desc  = htmlsafechars($arr['topic_desc'], ENT_QUOTES);
        }
        $post_id   = (int) $arr['post_id'];
        $posts     = (int) $arr['post_count'];
        $post_icon = ('' != $arr['icon'] ? '<img src="pic/smilies/' . htmlsafechars($arr['icon']) . '.gif" alt="icon" title="icon" /> ' : '<img src="pic/forums/topic_normal.gif" alt="Normal Topic" /> ');
        $edited_by = '';
        if ($arr['edit_date'] > 0) {
            $res_edited = sql_query('SELECT username FROM users WHERE id=' . sqlesc($arr['edited_by'])) or sqlerr(__FILE__, __LINE__);
            $arr_edited = mysqli_fetch_assoc($res_edited);
            $edited_by  = '<br><br><br><span style="font-weight: bold; font-size: x-small;">Last edited by <a class="altlink" href="member_details.php?id=' . (int) $arr['edited_by'] . '">' . htmlsafechars($arr_edited['username']) . '</a> at ' . get_date($arr['edit_date'], '') . ' GMT ' . ('' != $arr['edit_reason'] ? ' </span>[ Reason: ' . htmlsafechars($arr['edit_reason']) . ' ] <span style="font-weight: bold; font-size: x-small;">' : '');
        }
        $body        = ('yes' == $arr['bbcode'] ? highlightWords(format_comment($arr['body']), $search) : highlightWords(format_comment_no_bbcode($arr['body']), $search));
        $search_post = str_replace(' ', '+', $search);
        $content .= '<tr><td colspan="3" align="left">in: 
	<a class="altlink" href="forums.php?action=view_forum&amp;forum_id=' . (int) $arr['forum_id'] . '" title="' . sprintf($lang['sea_link_to_x'], 'Forum') . '">
	<span style="color: white;font-weight: bold;">' . htmlsafechars($arr['forum_name'], ENT_QUOTES) . '</span></a> in: 
	<a class="altlink" href="forums.php?action=view_topic&amp;topic_id=' . (int) $arr['topic_id'] . '" title="' . sprintf($lang['sea_link_to_x'], 'topic') . '"><span style="color: white;font-weight: bold;">
	' . $topic_title . '</span></a></td></tr>
	<tr><td class="forum_head" align="left" width="100" valign="middle"><a name="' . $post_id . '"></a>
	<span style="font-weight: bold;">' . $lang['sea_relevance'] . ': ' . round($arr['relevance'], 3) . '</span></td>
	<td class="forum_head" align="left" valign="middle">
	<span style="white-space:nowrap;">' . $post_icon . '<a class="altlink" href="forums.php?action=view_topic&amp;topic_id=' . $arr['topic_id'] . '&amp;page=' . $page . '#' . (int) $arr['post_id'] . '" title="Link to Post">' . $post_title . '</a>&nbsp;&nbsp;&nbsp;&nbsp; ' . $lang['fe_posted_on'] . ': ' . get_date($arr['added'], '') . ' [' . get_date($arr['added'], '', 0, 1) . ']</span></td>
	<td class="forum_head" align="right" valign="middle"><span style="white-space:nowrap;"> 
	<a href="forums.php?action=view_my_posts&amp;page=' . $page . '#top"><img src="pic/forums/up.gif" alt="' . $lang['fe_top'] . '" title="' . $lang['fe_top'] . '"/></a> 
	<a href="forums.php?action=view_my_posts&amp;page=' . $page . '#bottom"><img src="pic/forums/down.gif" alt="' . $lang['fe_bottom'] . '" title="' . $lang['fe_bottom'] . '" /></a> 
	</span></td>
	</tr>		
	<tr><td class="' . $class_alt . '" width="100px" valign="top">' . ('yes' == $arr['tan'] ? '<img style="max-width:' . $width . 'px;" src="' . $site_config['pic_baseurl'] . 'anonymous_1.jpg" alt="avatar" />' : avatar_stuff($arr)) . '<br>' . ('yes' == $arr['tan'] ? '<i>' . $lang['fe_anonymous'] . '</i>' : format_username($arr)) . ('yes' == $arr['anonymous'] || '' == $arr['title'] ? '' : '<br><span style=" font-size: xx-small;">[' . htmlsafechars($arr['title']) . ']</span>') . '<br><span style="font-weight: bold;">' . ('yes' == $arr['tan'] ? '' : get_user_class_name($arr['class'])) . '</span><br>
	</td><td class="' . $class . '" align="left" valign="top" colspan="2">' . $body . $edited_by . '</td></tr>
	<tr><td class="' . $class_alt . '" align="right" valign="middle" colspan="3"></td></tr>';
    } //=== end of while loop
    $content .= '</table>' . $the_top_and_bottom . '<br>
	<form action="#" method="get">
	<span style="font-weight: bold;">' . $lang['sea_per_page'] . ':</span> <select name="box" onchange="location = this.options[this.selectedIndex].value;">
	<option value=""> ' . $lang['sea_select'] . ' </option>
	<option value="forums.php?action=search&amp;page=0&amp;perpage=5' . $pager_links . '"' . (5 == $perpage ? ' selected="selected"' : '') . '>5</option>
	<option value="forums.php?action=search&amp;page=0&amp;perpage=20' . $pager_links . '"' . (20 == $perpage ? ' selected="selected"' : '') . '>20</option>
	<option value="forums.php?action=search&amp;page=0&amp;perpage=50' . $pager_links . '"' . (50 == $perpage ? ' selected="selected"' : '') . '>50</option>
	<option value="forums.php?action=search&amp;page=0&amp;perpage=100' . $pager_links . '"' . (100 == $perpage ? ' selected="selected"' : '') . '>100</option>
	<option value="forums.php?action=search&amp;page=0&amp;perpage=200' . $pager_links . '"' . (200 == $perpage ? ' selected="selected"' : '') . '>200</option>
	</select></form><br><br>';
}
//=== start page
$links                = '<span><a class="altlink" href="forums.php">' . $lang['fe_forums_main'] . '</a> |  ' . $mini_menu . '<br><br></span>';
$search__help_boolean = '<div id="help"style="display:none"><h1>' . $lang['sea_help_msg1'] . '</h1>
   <span style="font-weight: bold;">+</span> ' . $lang['sea_help_msg2'] . '<br><br>
   <span style="font-weight: bold;">-</span> ' . $lang['sea_help_msg3'] . '<br><br>
   ' . $lang['sea_help_msg4'] . ' <br><br>
   <span style="font-weight: bold;">*</span> ' . $lang['sea_help_msg5'] . '<br><br>
   <span style="font-weight: bold;">> <</span> ' . $lang['sea_help_msg6'] . '<br><br>
   <span style="font-weight: bold;">~</span> ' . $lang['sea_help_msg7'] . '<br><br>
   <span style="font-weight: bold;">" "</span> ' . $lang['sea_help_msg8'] . ' <br><br><span style="font-weight: bold;">( )</span> ' . $lang['sea_help_msg9'] . '<br><br></div>';
$search_in_forums = '<table width="100%">';
$row_count        = 0;
$res_forums       = sql_query('SELECT o_f.name AS over_forum_name, o_f.id AS over_forum_id, f.id AS real_forum_id, f.name, f.description,  f.forum_id FROM over_forums AS o_f JOIN forums AS f WHERE o_f.min_class_view <= ' . $CURUSER['class'] . ' AND f.min_class_read <=  ' . $CURUSER['class'] . ' ORDER BY o_f.sort, f.sort ASC') or sqlerr(__FILE__, __LINE__);
//=== well... let's do the loop and make the damned forum thingie!
while ($arr_forums = mysqli_fetch_assoc($res_forums)) {
    //=== if it's a forums section print it, if not, list the fourm sections in it \o/
    if ($arr_forums['over_forum_id'] != $over_forum_id && $row_count < 3) {
        while ($row_count < 3) {
            $search_in_forums .= '';
            ++$row_count;
        }
    }
    $search_in_forums .= ($arr_forums['over_forum_id'] != $over_forum_id ? '<tr>
	<td align="left" colspan="3"><span style="color: white;">' . htmlsafechars($arr_forums['over_forum_name'], ENT_QUOTES) . '</span></td></tr>' : '');
    if ($arr_forums['forum_id'] === $arr_forums['over_forum_id']) {
        $row_count = (3 == $row_count ? 0 : $row_count);
        $search_in_forums .= (0 == $row_count ? '' : '');
        ++$row_count;
        $search_in_forums .= '<tr><td align="left"><input name="f' . $arr_forums['real_forum_id'] . '" type="checkbox" ' . ($selected_forums ? 'checked="checked"' : '') . ' value="1" /><a href="forums.php?action=view_forum&amp;forum_id=' . $arr_forums['real_forum_id'] . '" class="altlink" title="' . htmlsafechars($arr_forums['description'], ENT_QUOTES) . '">' . htmlsafechars($arr_forums['name'], ENT_QUOTES) . '</a></td></tr> ' . (3 == $row_count ? '</td></tr>' : '');
    }
    $over_forum_id = $arr_forums['over_forum_id'];
}
for ($row_count = $row_count; $row_count < 3; ++$row_count) {
    $search_in_forums .= '';
}
$search_in_forums .= '<tr><td  colspan="3"><span style="font-weight: bold;">' . $lang['sea_if_none_are_selected_all_are_searched.'] . '</span></td></tr></table>';
/*
    //=== well... let's do the loop and make the damned forum thingie!
    while ($arr_forums = mysqli_fetch_assoc($res_forums))
    {
    //=== if it's a forums section print it, if not, list the fourm sections in it \o/
    if ($arr_forums['over_forum_id'] != $over_forum_id && $row_count < 3)
    {
    while ($row_count < 3)
    {
   $search_in_forums .= '<td></td>';
    ++$row_count;
    }
    }

    $search_in_forums .= ($arr_forums['over_forum_id'] != $over_forum_id ? '<tr>
    <td align="left" colspan="3"><span style="color: white;">'.htmlsafechars($arr_forums['over_forum_name'], ENT_QUOTES).'</span></td></tr>' : '');
    if ($arr_forums['forum_id'] == $arr_forums['over_forum_id'])
    {
    $row_count = ($row_count == 3 ? 0 : $row_count);
    $search_in_forums .= ($row_count == 0 ? '<tr>' : '');
    ++$row_count;
    $search_in_forums .= '<td align="left"><input name="f'.$arr_forums['real_forum_id'].'" type="checkbox" '.(in_array($arr_forums['real_forum_id'], $selected_forums) ? 'checked="checked"' : '').' value="1" />
    <a href="forums.php?action=view_forum&amp;forum_id='.$arr_forums['real_forum_id'].'" class="altlink" title="'.htmlsafechars($arr_forums['description'], ENT_QUOTES).'">'.htmlsafechars($arr_forums['name'], ENT_QUOTES).'</a> '.($row_count == 3 ? '</tr>' : '');
    }
    $over_forum_id = $arr_forums['over_forum_id'];
    }
    for ($row_count=$row_count; $row_count<3; $row_count++)
    {
    $search_in_forums .= '<td></td>';
    }

   $search_in_forums .= '<tr><td  colspan="3"><span style="font-weight: bold;">'.$lang['sea_if_none_are_selected_all_are_searched.'].'</span></a></td></tr></table>';
*/
//print $selected_forums;
//exit();
//=== this is far to much code lol this should just be html... but it was fun to do \\0\0/0//
$search_when_drop_down = '<select name="search_when">
	<option class="body" value="0"' . (0 === $search_when ? ' selected="selected"' : '') . '>' . $lang['sea_no_time_frame'] . '</option>
	<option class="body" value="604800"' . (604800 === $search_when ? ' selected="selected"' : '') . '>' . sprintf($lang['sea_x_week_ago'], '1') . '</option>
	<option class="body" value="1209600"' . (1209600 === $search_when ? ' selected="selected"' : '') . '>' . sprintf($lang['sea_x_weeks_ago'], '2') . '</option>
	<option class="body" value="1814400"' . (1814400 === $search_when ? ' selected="selected"' : '') . '>' . sprintf($lang['sea_x_weeks_ago'], '3') . '</option>
	<option class="body" value="2419200"' . (2419200 === $search_when ? ' selected="selected"' : '') . '>' . sprintf($lang['sea_x_month_ago'], '1') . '</option>
	<option class="body" value="4838400"' . (4838400 === $search_when ? ' selected="selected"' : '') . '>' . sprintf($lang['sea_x_months_ago'], '2') . '</option>
	<option class="body" value="7257600"' . (7257600 === $search_when ? ' selected="selected"' : '') . '>' . sprintf($lang['sea_x_months_ago'], '3') . '</option>
	<option class="body" value="9676800"' . (9676800 === $search_when ? ' selected="selected"' : '') . '>' . sprintf($lang['sea_x_months_ago'], '4') . '</option>
	<option class="body" value="12096000"' . (12096000 === $search_when ? ' selected="selected"' : '') . '>' . sprintf($lang['sea_x_months_ago'], '5') . '</option>
	<option class="body" value="14515200"' . (14515200 === $search_when ? ' selected="selected"' : '') . '>' . sprintf($lang['sea_x_months_ago'], '6') . '</option>
	<option class="body" value="16934400"' . (16934400 === $search_when ? ' selected="selected"' : '') . '>' . sprintf($lang['sea_x_months_ago'], '7') . '</option>
	<option class="body" value="19353600"' . (19353600 === $search_when ? ' selected="selected"' : '') . '>' . sprintf($lang['sea_x_months_ago'], '8') . '</option>
	<option class="body" value="21772800"' . (21772800 === $search_when ? ' selected="selected"' : '') . '>' . sprintf($lang['sea_x_months_ago'], '9') . '</option>
	<option class="body" value="24192000"' . (24192000 === $search_when ? ' selected="selected"' : '') . '>' . sprintf($lang['sea_x_months_ago'], '10') . '</option>
	<option class="body" value="26611200"' . (26611200 === $search_when ? ' selected="selected"' : '') . '>' . sprintf($lang['sea_x_months_ago'], '11') . '</option>
	<option class="body" value="30800000"' . (30800000 === $search_when ? ' selected="selected"' : '') . '>' . sprintf($lang['sea_x_year_ago'], '1') . '</option>
	<option class="body" value="0">' . $lang['sea_13.73_billion_years_ago'] . '</option>
	</select>';
$sort_by_drop_down = '<select name="sort_by">
	<option class="body" value="relevance"' . ('relevance' === $sort_by ? ' selected="selected"' : '') . '>' . $lang['sea_relevance'] . ' [default]</option>
	<option class="body" value="date"' . ('date' === $sort_by ? ' selected="selected"' : '') . '>' . $lang['sea_post_date'] . '</option>
	</select>';
$HTMLOUT .= '<h1>' . $lang['sea_forums'] . '</h1>' . $links . ($count > 0 ? '<h1>' . $count . ' ' . $lang['sea_search_results'] . ' ' . $lang['sea_below'] . '</h1>' : ($search ? $content : '<br>')) . '
	<form method="get" action="forums.php?"><input type="hidden" name="action" value="search" /><table border="0" cellspacing="10" cellpadding="10" width="800px">
	<tr>
	<tdalign="center" colspan="2"><span style="color: white; font-weight: bold;">' . $site_config['site_name'] . ' ' . $lang['sea_forums_search'] . '</span></td>
	</tr>
	<tr>
	<td  align="right" width="30px" valign="middle">
	<span style="font-weight: bold;white-space:nowrap;">' . $lang['sea_search_in'] . ':</span>
	</td>
	<td  align="left" valign="middle">
	<input type="radio" name="search_what" value="title" ' . ('title' === $search_what ? 'checked="checked"' : '') . ' /> <span style="font-weight: bold;">Title(s)</span> 
	<input type="radio" name="search_what" value="body" ' . ('body' === $search_what ? 'checked="checked"' : '') . ' /> <span style="font-weight: bold;">Body text</span>  
	<input type="radio" name="search_what" value="all" ' . ('all' === $search_what ? 'checked="checked"' : '') . ' /> <span style="font-weight: bold;">All</span> [ default ]</td>
	</tr>
	<tr>
	<td  align="right" width="60px" valign="middle">
	<span style="font-weight: bold;white-space:nowrap;">' . $lang['sea_search_terms'] . ':</span></td>
	<td  align="left">
	<input type="text" class="search" name="search" value="' . htmlsafechars($search) . '" /> 	
	<span style="text-align: right;">
	<a class="altlink"  title="' . $lang['sea_open_boolean_search_help'] . '"  id="help_open" style="font-weight:bold;cursor:help;"><img src="pic/forums/more.gif" alt="+" title="+" width="18" /> ' . $lang['sea_open_boolean_search_help'] . '</a> 
	<a class="altlink"  title="' . $lang['sea_close_boolean_search_help'] . '"  id="help_close" style="font-weight:bold;cursor:pointer;display:none"><img src="pic/forums/less.gif" alt="-" title="-" width="18" /> ' . $lang['sea_close_boolean_search_help'] . '</a> <br>
	</span>' . $search__help_boolean . '</td>
	</tr>
	<tr>
	<td  align="right" width="60px" valign="middle">
	<span style="font-weight: bold;white-space:nowrap;">' . $lang['sea_by_member'] . ':</span>
	</td>
	<td  align="left">' . $author_error . '
	<input type="text" class="member" name="author" value="' . $author . '" /> 	' . $lang['sea_search_only_posts_by_this_member'] . '</td>
	</tr>
	<tr>
	<td  align="right" width="60px" valign="middle">
	<span style="font-weight: bold;white-space:nowrap;">' . $lang['sea_time_frame'] . ':</span>
	</td>
	<td  align="left">' . $search_when_drop_down . ' ' . $lang['sea_how_far_back_to_search'] . '.
	</td>
	</tr>
	<tr>
	<td  align="right" width="60px" valign="middle">
	<span style="font-weight: bold;white-space:nowrap;">' . $lang['sea_sort_by'] . ':</span> 
	</td>
	<td  align="left">' . $sort_by_drop_down . '		
	<input type="radio" name="asc_desc" value="ASC" ' . ('ASC' === $asc_desc ? 'checked="checked"' : '') . ' /> <span style="font-weight: bold;">' . $lang['sea_ascending'] . '</span>  
	<input type="radio" name="asc_desc" value="DESC" ' . ('DESC' === $asc_desc ? 'checked="checked"' : '') . ' /> <span style="font-weight: bold;">' . $lang['sea_descending'] . '</span> 
	</td>
	</tr>
	<tr><td  align="right" width="60px" valign="top">
	<span style="font-weight: bold;white-space:nowrap;">' . $lang['sea_forums'] . ':</span>
	</td>
	<td  align="left">' . $search_in_forums . '		
	</td>
	</tr>
	<tr>
	<td  width="30px" colspan="2">
	<input type="radio" name="show_as" value="list" ' . ('list' === $show_as ? 'checked="checked"' : '') . ' /> <span style="font-weight: bold;">' . $lang['sea_results_as_list'] . '</span>  
	<input type="radio" name="show_as" value="posts" ' . ('posts' === $show_as ? 'checked="checked"' : '') . ' /> <span style="font-weight: bold;">' . $lang['sea_results_as_posts'] . '</span>  
	<input type="submit" name="button" class="button" value="' . $lang['gl_search'] . '"  />
	</td>
	</tr>
	</table></form><br>' . $content . $links . '<br>';
$HTMLOUT .= '
   <script type="text/javascript">
   /*<![CDATA[*/
   // set up the show / hide stuff
	$(document).ready(function()	{
	$("#help_open").click(function(){
	$("#help").slideToggle("slow", function() {
	});
	$("#help_open").hide();
	$("#help_close").show();
	});
	
	$("#help_close").click(function(){
	$("#help").slideToggle("slow", function() {
	});
	
	$("#help_close").hide();
	$("#help_open").show();
	});
   });
   /*]]>*/
   </script>';
