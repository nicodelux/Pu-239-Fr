<?php

global $lang;

$page     = $colour     = $arr_quote     = $extension_error     = $size_error     = '';
$topic_id = (isset($_GET['topic_id']) ? intval($_GET['topic_id']) : (isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0));
if (!is_valid_id($topic_id)) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}
$res = sql_query('SELECT t.topic_name, t.locked, f.min_class_read, f.min_class_write, f.id AS real_forum_id, s.id AS subscribed_id FROM topics AS t LEFT JOIN forums AS f ON t.forum_id = f.id LEFT JOIN subscriptions AS s ON s.topic_id = t.id WHERE ' . ($CURUSER['class'] < UC_STAFF ? 't.status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? 't.status != \'deleted\'  AND' : '')) . ' t.id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_assoc($res);
//=== stop them, they shouldn't be here lol
if ('yes' == $arr['locked']) {
    stderr($lang['gl_error'], $lang['fe_this_topic_is_locked']);
}
if ($CURUSER['class'] < $arr['min_class_read'] || $CURUSER['class'] < $arr['min_class_write']) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}
if ('no' == $CURUSER['forum_post'] || 'yes' == $CURUSER['suspended']) {
    stderr($lang['gl_error'], $lang['fe_your_no_post_right']);
}
$quote      = (isset($_GET['quote_post']) ? intval($_GET['quote_post']) : 0);
$key        = (isset($_GET['key']) ? intval($_GET['key']) : 0);
$body       = (isset($_POST['body']) ? $_POST['body'] : '');
$post_title = strip_tags((isset($_POST['post_title']) ? $_POST['post_title'] : ''));
$icon       = htmlsafechars((isset($_POST['icon']) ? $_POST['icon'] : ''));
$bb_code    = (isset($_POST['bb_code']) && 'no' == $_POST['bb_code'] ? 'no' : 'yes');
$subscribe  = ((isset($_POST['subscribe']) && 'yes' == $_POST['subscribe']) ? 'yes' : ((!isset($_POST['subscribe']) && $arr['subscribed_id'] > 0) ? 'yes' : 'no'));
$topic_name = htmlsafechars($arr['topic_name']);
$anonymous  = (isset($_POST['anonymous']) && '' != $_POST['anonymous'] ? 'yes' : 'no');
//== if it's a quote
if (0 !== $quote && '' == $body) {
    $res_quote = sql_query('SELECT p.body, p.staff_lock, p.anonymous, p.user_id, u.username FROM posts AS p LEFT JOIN users AS u ON p.user_id = u.id WHERE p.id=' . sqlesc($quote)) or sqlerr(__FILE__, __LINE__);
    $arr_quote = mysqli_fetch_array($res_quote);
    //=== if member exists, then add username, and then link back to post that was quoted with date :-D
    //==Anonymous
    if ('yes' == $arr_quote['anonymous']) {
        $quoted_member = ('' == $arr_quote['username'] ? '' . $lang['pr_lost_member'] . '' : '' . $lang['fe_anonymous'] . '');
    } else {
        $quoted_member = ('' == $arr_quote['username'] ? '' . $lang['pr_lost_member'] . '' : htmlsafechars($arr_quote['username']));
    }
    //==
    $body = '[quote=' . $quoted_member . ($quote > 0 ? ' | post=' . $quote : '') . ($key > 0 ? ' | key=' . $key : '') . ']' . htmlsafechars($arr_quote['body']) . '[/quote]';
    if (0 != $arr_quote['staff_lock']) {
        stderr($lang['gl_error'], '' . $lang['pr_this_post_is_staff_locked_nomod_nodel'] . '');
    }
}
if (isset($_POST['button']) && 'Post' == $_POST['button']) {
    //=== make sure they are posting something
    if ('' === $body) {
        stderr($lang['gl_error'], $lang['fe_no_body_txt']);
    }
    $ip = ('' == $CURUSER['ip'] ? htmlsafechars($_SERVER['REMOTE_ADDR']) : $CURUSER['ip']);
    sql_query('INSERT INTO `posts` (`topic_id`, `user_id`, `added`, `body`, `icon`, `post_title`, `bbcode`, `ip` , `anonymous`) VALUES (' . sqlesc($topic_id) . ', ' . sqlesc($CURUSER['id']) . ', ' . TIME_NOW . ', ' . sqlesc($body) . ', ' . sqlesc($icon) . ', ' . sqlesc($post_title) . ', ' . sqlesc($bb_code) . ', ' . sqlesc($ip) . ', ' . sqlesc($anonymous) . ')') or sqlerr(__FILE__, __LINE__);
    clr_forums_cache($arr['real_forum_id']);
    $cache->delete('forum_posts_' . $CURUSER['id']);
    $post_id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS['___mysqli_ston']))) ? false : $___mysqli_res);
    sql_query('UPDATE topics SET last_post=' . sqlesc($post_id) . ', post_count = post_count + 1 WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE `forums` SET post_count = post_count + 1 WHERE id =' . sqlesc($arr['real_forum_id'])) or sqlerr(__FILE__, __LINE__);
    sql_query('UPDATE usersachiev SET forumposts = forumposts + 1 WHERE userid = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    if (1 == $site_config['autoshout_on']) {
        $message = $CURUSER['username'] . ' ' . $lang['pr_replied_to_topic'] . " [url={$site_config['baseurl']}/forums.php?action=view_topic&topic_id=$topic_id&page=last]{$topic_name}[/url]";
        if (!in_array($arr['real_forum_id'], $site_config['staff_forums'])) {
            autoshout($message);
            $cache->delete('shoutbox_');
        }
    }
    if (1 == $site_config['seedbonus_on']) {
        sql_query('UPDATE users SET seedbonus = seedbonus+' . sqlesc($site_config['bonus_per_post']) . ' WHERE id = ' . sqlesc($CURUSER['id']) . '') or sqlerr(__FILE__, __LINE__);
        $update['seedbonus'] = ($CURUSER['seedbonus'] + $site_config['bonus_per_post']);
        $cache->update_row('userstats_' . $CURUSER['id'], [
            'seedbonus' => $update['seedbonus'],
        ]);
        $cache->update_row('user_stats_' . $CURUSER['id'], [
            'seedbonus' => $update['seedbonus'],
        ]);
    }
    if ('yes' == $subscribe && $arr['subscribed_id'] < 1) {
        sql_query('INSERT INTO `subscriptions` (`user_id`, `topic_id`) VALUES (' . sqlesc($CURUSER['id']) . ', ' . sqlesc($topic_id) . ')') or sqlerr(__FILE__, __LINE__);
    } elseif ('no' == $subscribe && $arr['subscribed_id'] > 0) {
        sql_query('DELETE FROM `subscriptions` WHERE `user_id`= ' . sqlesc($CURUSER['id']) . ' AND  `topic_id` = ' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
    }
    // === PM subscribed members
    $res_sub = sql_query('SELECT user_id FROM subscriptions WHERE topic_id =' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
    while ($row = mysqli_fetch_assoc($res_sub)) {
        $res_yes = sql_query('SELECT subscription_pm, username FROM users WHERE id = ' . sqlesc($row['user_id'])) or sqlerr(__FILE__, __LINE__);
        $arr_yes = mysqli_fetch_array($res_yes);
        $msg     = '' . $lang['pr_hey_there'] . "!!! \n " . $lang['pr_a_thread_you_subscribed_to'] . ': ' . htmlsafechars($arr['topic_name']) . ' ' . $lang['pr_has_had_a_new_post'] . "!\n click [url={$site_config['baseurl']}/forums.php?action=view_topic&amp;topic_id={$topic_id}&page=last][b]" . $lang['pr_here'] . '[/b][/url] ' . $lang['pr_to_read_it'] . "!\n\n" . $lang['pr_to_view_your_subscriptions_or_unsubscribe'] . " [url={$site_config['baseurl']}/forums.php?action=subscriptions][b]" . $lang['pr_here'] . "[/b][/url].\n\nCheers.";
        if ('yes' == $arr_yes['subscription_pm'] && $row['user_id'] != $CURUSER['id']) {
            sql_query("INSERT INTO messages (sender, subject, receiver, added, msg) VALUES(0, '" . $lang['pr_new_post_in_subscribed_thread'] . "!', " . sqlesc($row['user_id']) . ", '" . TIME_NOW . "', " . sqlesc($msg) . ')') or sqlerr(__FILE__, __LINE__);
        }
    }
    // ===end
    //=== stuff for file uploads
    if ($CURUSER['class'] >= $min_upload_class) {
        //=== make sure file is kosher
        foreach ($_FILES['attachment']['name'] as $key => $name) {
            if (!empty($name)) {
                $size = intval($_FILES['attachment']['size'][$key]);
                $type = $_FILES['attachment']['type'][$key];
                //=== make sure file is kosher
                $extension_error = $size_error = 0;
                //=== get rid of spaces
                $name = str_replace(' ', '_', $name);
                //=== allowed file types (2 checks) but still can't really trust it
                $accepted_file_types = [
                    'application/zip',
                    'application/x-zip',
                    'application/rar',
                    'application/x-rar',
                ];
                $accepted_file_extension = strrpos($name, '.');
                $file_extension          = strtolower(substr($name, $accepted_file_extension));
                //===  make sure the name is only alphanumeric or _ or -
                $name = preg_replace('#[^a-zA-Z0-9_-]#', '', $name); // hell, it could even be 0_0 if it wanted to!
                switch (true) {
                    case $size > $max_file_size:
                        $size_error = ($size_error + 1);
                        break;

                    case !in_array($file_extension, $accepted_file_extension) && false == $accepted_file_extension:
                        $extension_error = ($extension_error + 1);
                        break;

                    case 0 === $accepted_file_extension:
                        $extension_error = ($extension_error + 1);
                        break;

                    case !in_array($type, $accepted_file_types):
                        $extension_error = ($extension_error + 1);
                        break;

                    default:
                        //=== woohoo passed all our silly tests but just to be sure, let's mess it up a bit ;)
                        //=== get rid of the file extension
                        $name      = substr($name, 0, -strlen($file_extension));
                        $upload_to = $upload_folder . $name . '(id-' . $post_id . ')' . $file_extension;
                        //===plop it into the DB all safe and snuggly
                        sql_query('INSERT INTO `attachments` (`post_id`, `user_id`, `file`, `file_name`, `added`, `extension`, `size`) VALUES ( ' . sqlesc($post_id) . ', ' . sqlesc($CURUSER['id']) . ', ' . sqlesc($name . '(id-' . $post_id . ')' . $file_extension) . ', ' . sqlesc($name) . ', ' . TIME_NOW . ', ' . ('.zip' === $file_extension ? '\'zip\'' : '\'rar\'') . ', ' . $size . ')') or sqlerr(__FILE__, __LINE__);
                        copy($_FILES['attachment']['tmp_name'][$key], $upload_to);
                        chmod($upload_to, 0777);
                }
            }
        }
    } //=== end attachment stuff
    header('Location: forums.php?action=view_topic&topic_id=' . $topic_id . ('' === $extension_error ? '' : '&ee=' . $extension_error) . ('' === $size_error ? '' : '&se=' . $size_error) . '&page=' . $post_id . '#' . $post_id);
    die();
}
$HTMLOUT .= '<table class="main" width="750px" border="0" cellspacing="0" cellpadding="0">
   	 <tr><td class="embedded">
	<h1>' . $lang['pr_reply_in_topic'] . ' "<a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '">' . htmlsafechars($arr['topic_name'], ENT_QUOTES) . '</a>"</h1>
	 ' . (isset($_POST['button']) && $_POST['button'] == '' . $lang['fe_preview'] . '' ? '
	<table width="80%" border="0" cellspacing="5" cellpadding="5">
	<tr><td class="forum_head" colspan="2"><span style="font-weight: bold;">' . $lang['fe_preview'] . '</span></td></tr>
	<tr><td width="80" valign="top">' . avatar_stuff($CURUSER) . '</td>
	<td valign="top" align="left" >' . ('yes' == $bb_code ? format_comment($body) : format_comment_no_bbcode($body)) . '</td>
	</tr></table><br><br>' : '') . '
	<form method="post" action="' . $site_config['baseurl'] . '/forums.php?action=post_reply&amp;topic_id=' . $topic_id . '&amp;page=' . $page . '" enctype="multipart/form-data">
	<table width="80%" border="0" cellspacing="0" cellpadding="5">
	<tr><td align="left" colspan="2">' . $lang['fe_compose'] . '</td></tr>
	<tr><td align="right" ><span style="white-space:nowrap; font-weight: bold;">' . $lang['fe_icon'] . '</span></td>
	<td align="left" >
	<table>
  <tr>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/smile1.gif" alt="' . $lang['fe_smile'] . '" title="' . $lang['fe_smile'] . '" /></td>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/grin.gif" alt="' . $lang['fe_smilee_grin'] . '" title="' . $lang['fe_smilee_grin'] . '" /></td>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/tongue.gif" alt="' . $lang['fe_smilee_tongue'] . '" title="' . $lang['fe_smilee_tongue'] . '" /></td>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/cry.gif" alt="' . $lang['fe_smilee_cry'] . '" title="' . $lang['fe_smilee_cry'] . '" /></td>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/wink.gif" alt="' . $lang['fe_smilee_wink'] . '" title="' . $lang['fe_smilee_wink'] . '" /></td>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/rolleyes.gif" alt="' . $lang['fe_smilee_roll_eyes'] . '" title="' . $lang['fe_smilee_roll_eyes'] . '" /></td>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/blink.gif" alt="' . $lang['fe_smilee_blink'] . '" title="' . $lang['fe_smilee_blink'] . '" /></td>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/bow.gif" alt="' . $lang['fe_smilee_bow'] . '" title="' . $lang['fe_smilee_bow'] . '" /></td>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/clap2.gif" alt="' . $lang['fe_smilee_clap'] . '" title="' . $lang['fe_smilee_clap'] . '" /></td>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/hmmm.gif" alt="' . $lang['fe_smilee_hmm'] . '" title="' . $lang['fe_smilee_hmm'] . '" /></td>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/devil.gif" alt="' . $lang['fe_smilee_devil'] . '" title="' . $lang['fe_smilee_devil'] . '" /></td>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/angry.gif" alt="' . $lang['fe_smilee_angry'] . '" title="' . $lang['fe_smilee_angry'] . '" /></td>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/shit.gif" alt="' . $lang['fe_smilee_shit'] . '" title="' . $lang['fe_smilee_shit'] . '" /></td>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/sick.gif" alt="' . $lang['fe_smilee_sick'] . '" title="' . $lang['fe_smilee_sick'] . '" /></td>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/tease.gif" alt="' . $lang['fe_smilee_tease'] . '" title="' . $lang['fe_smilee_tease'] . '" /></td>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/love.gif" alt="' . $lang['fe_smilee_love'] . '" title="' . $lang['fe_smilee_love'] . '" /></td>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/ohmy.gif" alt="' . $lang['fe_smilee_oh_my'] . '" title="' . $lang['fe_smilee_oh_my'] . '" /></td>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/yikes.gif" alt="' . $lang['fe_smilee_yikes'] . '" title="' . $lang['fe_smilee_yikes'] . '" /></td>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/spider.gif" alt="' . $lang['fe_smilee_spider'] . '" title="' . $lang['fe_smilee_spider'] . '" /></td>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/wall.gif" alt="' . $lang['fe_smilee_wall'] . '" title="' . $lang['fe_smilee_wall'] . '" /></td>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/idea.gif" alt="' . $lang['fe_smilee_idea'] . '" title="' . $lang['fe_smilee_idea'] . '" /></td>
	<td  valign="middle"><img src="' . $site_config['pic_baseurl'] . 'smilies/question.gif" alt="' . $lang['fe_smilee_question'] . '" title="' . $lang['fe_smilee_question'] . '" /></td>
	</tr>

	<tr>
	<td  valign="middle"><input type="radio" name="icon" value="smile1"' . ('smile1' == $icon ? ' checked="checked"' : '') . ' /></td>
	<td  valign="middle"><input type="radio" name="icon" value="grin"' . ('grin' == $icon ? ' checked="checked"' : '') . ' /></td>
	<td  valign="middle"><input type="radio" name="icon" value="tongue"' . ('tongue' == $icon ? ' checked="checked"' : '') . ' /></td>
	<td  valign="middle"><input type="radio" name="icon" value="cry"' . ('cry' == $icon ? ' checked="checked"' : '') . ' /></td>
	<td  valign="middle"><input type="radio" name="icon" value="wink"' . ('wink' == $icon ? ' checked="checked"' : '') . ' /></td>
	<td  valign="middle"><input type="radio" name="icon" value="rolleyes"' . ('rolleyes' == $icon ? ' checked="checked"' : '') . ' /></td>
	<td  valign="middle"><input type="radio" name="icon" value="blink"' . ('blink' == $icon ? ' checked="checked"' : '') . ' /></td>
	<td  valign="middle"><input type="radio" name="icon" value="bow"' . ('bow' == $icon ? ' checked="checked"' : '') . ' /></td>
	<td  valign="middle"><input type="radio" name="icon" value="clap2"' . ('clap2' == $icon ? ' checked="checked"' : '') . ' /></td>
	<td  valign="middle"><input type="radio" name="icon" value="hmmm"' . ('hmmm' == $icon ? ' checked="checked"' : '') . ' /></td>
	<td  valign="middle"><input type="radio" name="icon" value="devil"' . ('devil' == $icon ? ' checked="checked"' : '') . ' /></td>
	<td  valign="middle"><input type="radio" name="icon" value="angry"' . ('angry' == $icon ? ' checked="checked"' : '') . ' /></td>
	<td  valign="middle"><input type="radio" name="icon" value="shit"' . ('shit' == $icon ? ' checked="checked"' : '') . ' /></td>
	<td  valign="middle"><input type="radio" name="icon" value="sick"' . ('sick' == $icon ? ' checked="checked"' : '') . ' /></td>
	<td  valign="middle"><input type="radio" name="icon" value="tease"' . ('tease' == $icon ? ' checked="checked"' : '') . ' /></td>
	<td  valign="middle"><input type="radio" name="icon" value="love"' . ('love' == $icon ? ' checked="checked"' : '') . ' /></td>
	<td  valign="middle"><input type="radio" name="icon" value="ohmy"' . ('ohmy' == $icon ? ' checked="checked"' : '') . ' /></td>
	<td  valign="middle"><input type="radio" name="icon" value="yikes"' . ('yikes' == $icon ? ' checked="checked"' : '') . ' /></td>
	<td  valign="middle"><input type="radio" name="icon" value="spider"' . ('spider' == $icon ? ' checked="checked"' : '') . ' /></td>
	<td  valign="middle"><input type="radio" name="icon" value="wall"' . ('wall' == $icon ? ' checked="checked"' : '') . ' /></td>
	<td  valign="middle"><input type="radio" name="icon" value="idea"' . ('idea' == $icon ? ' checked="checked"' : '') . ' /></td>
	<td  valign="middle"><input type="radio" name="icon" value="question"' . ('question' == $icon ? ' checked="checked"' : '') . ' /></td>
	</tr>
	</table>
	</td></tr>	
	<tr><td align="right" ><span style="white-space:nowrap; font-weight: bold;">' . $lang['fe_title'] . '</span></td>
	<td align="left" ><input type="text" maxlength="120" name="post_title" value="' . $post_title . '" class="w-100" /> [ optional ]</td></tr>
	<tr><td align="right" ><span style="white-space:nowrap; font-weight: bold;">' . $lang['fe_bbcode'] . '</span></td>
	<td align="left" >
	<input type="radio" name="bb_code" value="yes"' . ('yes' == $bb_code ? ' checked="checked"' : '') . ' /> ' . $lang['fe_yes_enable'] . ' ' . $lang['fe_bbcode_in_post'] . ' 
	<input type="radio" name="bb_code" value="no"' . ('no' == $bb_code ? ' checked="checked"' : '') . ' /> ' . $lang['fe_no_disable'] . ' ' . $lang['fe_bbcode_in_post'] . ' 
	</td></tr>
	<tr><td align="right" valign="top" ><span style="white-space:nowrap; font-weight: bold;">' . $lang['fe_body'] . '</span></td>
	<td align="left" >' . BBcode($body) . $more_options . '
	</td></tr>
	<tr><td colspan="2" >
   Anonymous post : <input type="checkbox" name="anonymous" value="yes" /><br>
   <img src="' . $site_config['pic_baseurl'] . 'forums/subscribe.gif" alt="+" title="+" /> ' . $lang['fe_subscrib_to_tread'] . ' 
	<input type="radio" name="subscribe" value="yes"' . ('yes' == $subscribe ? ' checked="checked"' : '') . ' />yes 
	<input type="radio" name="subscribe" value="no"' . ('no' == $subscribe ? ' checked="checked"' : '') . ' />no<br>
	<input type="submit" name="button" class="button" value="' . $lang['fe_preview'] . '"  />
	<input type="submit" name="button" class="button_tiny" value="' . $lang['fe_post'] . '" onmouseover="this.className=\'button_tiny_hover\'" onmouseout="this.className=\'button_tiny\'" />
	</td></tr>
	</table></form>';
//=== get last ten posts
$res_posts = sql_query('SELECT p.id AS post_id, p.user_id, p.added, p.body, p.icon, p.post_title, p.bbcode, p.anonymous, u.id, u.username, u.class, u.donor, u.suspended, u.chatpost, u.leechwarn, u.pirate, u.king, u.warned, u.enabled, u.avatar, u.offensive_avatar FROM posts AS p LEFT JOIN users AS u ON p.user_id = u.id WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? 'p.status != \'deleted\' AND' : '')) . ' topic_id=' . sqlesc($topic_id) . ' ORDER BY p.id DESC LIMIT 0, 10') or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= '<br><span>' . $lang['fe_last_ten_posts_in_reverse_order'] . '</span>
	<table border="0" cellspacing="5" cellpadding="10" width="90%">';
//=== lets start the loop \o/
while ($arr = mysqli_fetch_assoc($res_posts)) {
    //=== change colors
    $colour    = (++$colour) % 2;
    $class     = (0 == $colour ? 'one' : 'two');
    $class_alt = (0 == $colour ? 'two' : 'one');
    $HTMLOUT .= '<tr><td class="forum_head" align="left" width="100" valign="middle">#
		<span style="font-weight: bold;">' . ('yes' == $arr['anonymous'] ? '<i>' . $lang['fe_anonymous'] . '</i>' : htmlsafechars($arr['username'])) . '</span></td>
	   <td class="forum_head" align="left" valign="middle"><span style="white-space:nowrap;"> ' . $lang['fe_posted_on'] . ': ' . get_date($arr['added'], '') . ' [' . get_date($arr['added'], '', 0, 1) . ']</span></td></tr>';
    $width = 100;
    if ('yes' == $arr['anonymous']) {
        if ($CURUSER['class'] < UC_STAFF && $arr['user_id'] != $CURUSER['id']) {
            $HTMLOUT .= '<tr><td class="' . $class_alt . '" width="100" valign="top"><img style="max-width:' . $width . 'px;" src="' . $site_config['pic_baseurl'] . 'anonymous_1.jpg" alt="avatar" /><br><i>' . $lang['fe_anonymous'] . '</i></td>';
        } else {
            $HTMLOUT .= '<tr><td class="' . $class_alt . '" width="100" valign="top">' . avatar_stuff($arr) . '<br><i>' . $lang['fe_anonymous'] . '</i>[' . format_username($arr['user_id']) . ']</td>';
        }
    } else {
        $HTMLOUT .= '<tr><td class="' . $class_alt . '" width="100" valign="top">' . avatar_stuff($arr) . '<br>' . format_username($arr['user_id']) . '</td>';
    }
    $HTMLOUT .= '<td class="' . $class . '" align="left" valign="top" colspan="2">' . ('yes' == $arr['bbcode'] ? format_comment($arr['body']) : format_comment_no_bbcode($arr['body'])) . '</td></tr>';
}
$HTMLOUT .= '</table>
			</td></tr></table><br><br>';
