<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
global $CURUSER, $site_config;

$lang    = load_language('global');
$stdhead = [
    'css' => [
    ],
];
$stdfoot = [
    'js' => [
    ],
];
$HTMLOUT = '';
if ($CURUSER['class'] < UC_POWER_USER) {
    stderr('Error!', 'Sorry, power user and up only!');
}
//=== possible stuff to be $_GETting lol
$id            = (isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0));
$comment_id    = (isset($_GET['comment_id']) ? intval($_GET['comment_id']) : (isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0));
$category      = (isset($_GET['category']) ? intval($_GET['category']) : (isset($_POST['category']) ? intval($_POST['category']) : 0));
$offered_by_id = isset($_GET['offered_by_id']) ? intval($_GET['offered_by_id']) : 0;
$vote          = isset($_POST['vote']) ? intval($_POST['vote']) : 0;
$posted_action = strip_tags((isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '')));
//=== add all possible actions here and check them to be sure they are ok
$valid_actions = [
    'add_new_offer',
    'delete_offer',
    'edit_offer',
    'offer_details',
    'vote',
    'add_comment',
    'edit_comment',
    'delete_comment',
    'alter_status',
];
//=== check posted action, and if no action was posted, show the default page
$action = (in_array($posted_action, $valid_actions) ? $posted_action : 'default');
//=== top menu :D
$top_menu = '<div class="article"><div class="article_header"><a class="altlink" href="offers.php">view offers</a> || <a class="altlink" href="offers.php?action=add_new_offer">make offer</a></div>';
switch ($action) {
    case 'vote':
        //===========================================================================================//
        //==================================    let them vote on it!    ==========================================//
        //===========================================================================================//
        //=== kill if nasty
        if (!isset($id) || !is_valid_id($id) || !isset($vote) || !is_valid_id($vote)) {
            stderr('USER ERROR', 'Bad id / bad vote');
        }
        //=== see if they voted yet
        $res_did_they_vote = sql_query('SELECT vote FROM offer_votes WHERE user_id = ' . sqlesc($CURUSER['id']) . ' AND offer_id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $row_did_they_vote = mysqli_fetch_row($res_did_they_vote);
        if ($row_did_they_vote[0] == '') {
            $yes_or_no = ($vote == 1 ? 'yes' : 'no');
            sql_query('INSERT INTO offer_votes (offer_id, user_id, vote) VALUES (' . sqlesc($id) . ', ' . sqlesc($CURUSER['id']) . ', \'' . $yes_or_no . '\')')                  or sqlerr(__FILE__, __LINE__);
            sql_query('UPDATE offers SET ' . ($yes_or_no === 'yes' ? 'vote_yes_count = vote_yes_count + 1' : 'vote_no_count = vote_no_count + 1') . ' WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
            header('Location: /offers.php?action=offer_details&voted=1&id=' . $id);
            die();
        } else {
            stderr('USER ERROR', 'You have voted on this offer before.');
        }
        break;

    case 'default':
        //===========================================================================================//
        //=======================    the default page listing all the offers w/ pager         ===============================//
        //===========================================================================================//
        require_once INCL_DIR . 'bbcode_functions.php';
        require_once INCL_DIR . 'pager_new.php';
        //=== get stuff for the pager
        $count_query        = sql_query('SELECT COUNT(id) FROM offers') or sqlerr(__FILE__, __LINE__);
        $count_arr          = mysqli_fetch_row($count_query);
        $count              = $count_arr[0];
        $page               = isset($_GET['page']) ? (int) $_GET['page'] : 0;
        $perpage            = isset($_GET['perpage']) ? (int) $_GET['perpage'] : 20;
        list($menu, $LIMIT) = pager_new($count, $perpage, $page, 'offers.php?' . ($perpage == 20 ? '' : '&amp;perpage=' . $perpage));
        $main_query_res     = sql_query('SELECT o.id AS offer_id, o.offer_name, o.category, o.added, o.offered_by_user_id, o.vote_yes_count, o.vote_no_count, o.comments, o.status,
                                                    u.id, u.username, u.warned, u.suspended, u.enabled, u.donor, u.class,  u.leechwarn, u.chatpost, u.pirate, u.king,
                                                    c.id AS cat_id, c.name AS cat_name, c.image AS cat_image
                                                    FROM offers AS o
                                                    LEFT JOIN categories AS c ON o.category = c.id
                                                    LEFT JOIN users AS u ON o.offered_by_user_id = u.id
                                                    ORDER BY o.added DESC ' . $LIMIT) or sqlerr(__FILE__, __LINE__);
        if ($count = 0) {
            stderr('Error!', 'Sorry, there are no current offers!');
        }
        $HTMLOUT .= (isset($_GET['new']) ? '<h1>Offer Added!</h1>' : '') . (isset($_GET['offer_deleted']) ? '<h1>Offer Deleted!</h1>' : '') . $top_menu . '' . $menu . '<br>';
        $HTMLOUT .= '<table class="table table-bordered table-striped">
       <tr>
        <td class="colhead">Type</td>
        <td class="colhead">Name</td>
        <td class="colhead">Added</td>
        <td class="colhead">Comm</td>
        <td class="colhead">Votes</td>
        <td class="colhead">Offered By</td>
        <td class="colhead">Status</td>
    </tr>';
        while ($main_query_arr = mysqli_fetch_assoc($main_query_res)) {
            //=======change colors
=            $status = ($main_query_arr['status'] == 'approved' ? '<span>Approved!</span>' : ($main_query_arr['status'] === 'pending' ? '<span>Pending...</span>' : '<span>denied</span>'));
            $HTMLOUT .= '
    <tr>
        <td><img border="0" src="' . $site_config['pic_baseurl'] . 'caticons/' . get_category_icons() . '/' . htmlsafechars($main_query_arr['cat_image'], ENT_QUOTES) . '" alt="' . htmlsafechars($main_query_arr['cat_name'], ENT_QUOTES) . '" /></td>
        <td><a class="altlink" href="' . $site_config['baseurl'] . '/offers.php?action=offer_details&amp;id=' . $main_query_arr['offer_id'] . '">' . htmlsafechars($main_query_arr['offer_name'], ENT_QUOTES) . '</a></td>
        <td>' . get_date($main_query_arr['added'], 'LONG') . '</td>
        <td>' . number_format($main_query_arr['comments']) . '</td>
        <td>yes: ' . number_format($main_query_arr['vote_yes_count']) . '<br>
        no: ' . number_format($main_query_arr['vote_no_count']) . '</td>
        <td>' . format_username($main_query_arr) . '</td>
        <td>' . $status . '</td>
    </tr>';
        }
        $HTMLOUT .= '</table>';
        $HTMLOUT .= '' . $menu . '<br></div>';
        echo stdhead('Offers', true, $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        break;
    //===========================================================================================//
    //==============================the details page for the offer! ========================================//
    //===========================================================================================//

    case 'offer_details':
        require_once INCL_DIR . 'bbcode_functions.php';
        require_once INCL_DIR . 'pager_new.php';
        //=== kill if nasty
        if (!isset($id) || !is_valid_id($id)) {
            stderr('USER ERROR', 'Bad id');
        }
        $res = sql_query('SELECT o.id AS offer_id, o.offer_name, o.category, o.added, o.offered_by_user_id, o.vote_yes_count, o.status,
                            o.vote_no_count, o.image, o.link, o.description, o.comments,
                            u.id, u.username, u.warned, u.suspended, u.enabled, u.donor, u.class, u.uploaded, u.downloaded, u.leechwarn, u.chatpost, u.pirate, u.king,
                            c.name AS cat_name, c.image AS cat_image
                            FROM offers AS o
                            LEFT JOIN categories AS c ON o.category = c.id
                            LEFT JOIN users AS u ON o.offered_by_user_id = u.id
                            WHERE o.id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_assoc($res);
        //=== see if they voted yet
        $res_did_they_vote = sql_query('SELECT vote FROM offer_votes WHERE user_id = ' . sqlesc($CURUSER['id']) . ' AND offer_id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $row_did_they_vote = mysqli_fetch_row($res_did_they_vote);
        if ($row_did_they_vote[0] == '') {
            $vote_yes = '<form method="post" action="offers.php">
                    <input type="hidden" name="action" value="vote" />
                    <input type="hidden" name="id" value="' . $id . '" />
                    <input type="hidden" name="vote" value="1" />
                    <input type="submit" class="button is-small" value="vote yes!" />
                    </form> ~ you will be notified when this offer is filled.';
            $vote_no = '<form method="post" action="offers.php">
                    <input type="hidden" name="action" value="vote" />
                    <input type="hidden" name="id" value="' . $id . '" />
                    <input type="hidden" name="vote" value="2" />
                    <input type="submit" class="button is-small" value="vote no!" />
                    </form> ~ you are being a stick in the mud.';
            $your_vote_was = '';
        } else {
            $vote_yes      = '';
            $vote_no       = '';
            $your_vote_was = ' your vote: ' . $row_did_they_vote[0] . ' ';
        }
        $status_drop_down = ($CURUSER['class'] < UC_STAFF ? '' : '<br><form method="post" action="offers.php">
                    <input type="hidden" name="action" value="alter_status" />
                    <input type="hidden" name="id" value="' . $id . '" />
                    <select name="set_status">
                    <option class="body" value="pending"' . ($arr['status'] == 'pending' ? ' selected' : '') . '>Status: pending</option>
                    <option class="body" value="approved"' . ($arr['status'] == 'approved' ? ' selected' : '') . '>Status: approved</option>
                    <option class="body" value="denied"' . ($arr['status'] == 'denied' ? ' selected' : '') . '>Status: denied</option>
                    </select>
                    <input type="submit" class="button is-small" value="change status!" />
                    </form> ');
        //=== start page
        $HTMLOUT .= (isset($_GET['status_changed']) ? '<h1>Offer Status Updated!</h1>' : '') . (isset($_GET['voted']) ? '<h1>vote added</h1>' : '') . (isset($_GET['comment_deleted']) ? '<h1>comment deleted</h1>' : '') . $top_menu . ($arr['status'] === 'approved' ? '<span>status: approved!</span>' : ($arr['status'] === 'pending' ? '<span>status: pending...</span>' : '<span>status: denied</span>')) . $status_drop_down . '<br><br>
    <table class="table table-bordered table-striped">
    <tr>
    <td class="colhead" colspan="2"><h1>' . htmlsafechars($arr['offer_name'], ENT_QUOTES) . ($CURUSER['class'] < UC_STAFF ? '' : ' [ <a href="offers.php?action=edit_offer&amp;id=' . $id . '">edit</a> ]
    [ <a href="offers.php?action=delete_offer&amp;id=' . $id . '">delete</a> ]') . '</h1></td>
    </tr>
    <tr>
    <td>image:</td>
    <td><img src="' . strip_tags(image_proxy($arr['image'])) . '" alt="image" /></td>
    </tr>
    <tr>
    <td>description:</td>
    <td>' . format_comment($arr['description']) . '</td>
    </tr>
    <tr>
    <td>category:</td>
    <td><img border="0" src="' . $site_config['pic_baseurl'] . 'caticons/' . get_category_icons() . '/' . htmlsafechars($arr['cat_image'], ENT_QUOTES) . '" alt="' . htmlsafechars($arr['cat_name'], ENT_QUOTES) . '" /></td>
    </tr>
    <tr>
    <td>link:</td>
    <td><a class="altlink" href="' . htmlsafechars($arr['link'], ENT_QUOTES) . '"  target="_blank">' . htmlsafechars($arr['link'], ENT_QUOTES) . '</a></td>
    </tr>
    <tr>
    <td>votes:</td>
    <td>
    <span>yes: ' . number_format($arr['vote_yes_count']) . '</span> ' . $vote_yes . '<br>
    <span>no: ' . number_format($arr['vote_no_count']) . '</span> ' . $vote_no . '<br> ' . $your_vote_was . '</td>
    </tr>
    <tr>
    <td>offered by:</td>
    <td>' . format_username($arr) . ' [ ' . get_user_class_name($arr['class']) . ' ]
    ratio: ' . member_ratio($arr['uploaded'], $site_config['ratio_free'] ? '0' : $arr['downloaded']) . get_user_ratio_image(($site_config['ratio_free'] ? 1 : $arr['uploaded'] / $arr['downloaded'])) . '</td>
    </tr>
    <tr>
    <td>Report Offer</td>
    <td><form action="report.php?type=Offer&amp;id=' . $id . '" method="post">
    <input type="submit" class="button_med" value="Report This Offer" />
    For breaking the <a class="altlink" href="rules.php">rules</a></form></td>
    </tr>
    </table>';
        $HTMLOUT .= '<h1>Comments for ' . htmlsafechars($arr['offer_name'], ENT_QUOTES) . '</h1><p><a name="startcomments"></a></p>';
        $commentbar = '<p><a class="index" href="offers.php?action=add_comment&amp;id=' . $id . '">Add a comment</a></p>';
        $count      = (int) $arr['comments'];
        if (!$count) {
            $HTMLOUT .= '<h2>No comments yet</h2>';
        } else {
            //=== get stuff for the pager
            $page               = isset($_GET['page']) ? (int) $_GET['page'] : 0;
            $perpage            = isset($_GET['perpage']) ? (int) $_GET['perpage'] : 20;
            list($menu, $LIMIT) = pager_new($count, $perpage, $page, 'offers.php?action=offer_details&amp;id=' . $id, ($perpage == 20 ? '' : '&amp;perpage=' . $perpage) . '#comments');
            $subres             = sql_query('SELECT c.offer, c.id AS comment_id, c.text, c.added, c.editedby, c.editedat,
                                    u.id, u.username, u.warned, u.suspended, u.enabled, u.donor, u.class, u.avatar, u.offensive_avatar, u.title, u.leechwarn, u.chatpost, u.pirate, u.king FROM comments AS c LEFT JOIN users AS u ON c.user = u.id WHERE c.offer = ' . sqlesc($id) . ' ORDER BY c.id ' . $LIMIT) or sqlerr(__FILE__, __LINE__);
            $allrows = [];
            while ($subrow = mysqli_fetch_assoc($subres)) {
                $allrows[] = $subrow;
            }
            $HTMLOUT .= $commentbar . '<a name="comments"></a>';
            $HTMLOUT .= ($count > $perpage) ? '<p>' . $menu . '<br></p>' : '<br>';
            $HTMLOUT .= comment_table($allrows);
            $HTMLOUT .= ($count > $perpage) ? '<p>' . $menu . '<br></p>' : '<br>';
        }
        $HTMLOUT .= $commentbar;
        echo stdhead('Offer details for: ' . htmlsafechars($arr['offer_name'], ENT_QUOTES), true, $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        break;
    //===========================================================================================//
    //====================================    add new offer      ========================================//
    //===========================================================================================//

    case 'add_new_offer':
        require_once INCL_DIR . 'bbcode_functions.php';
        $offer_name = strip_tags(isset($_POST['offer_name']) ? trim($_POST['offer_name']) : '');
        $image      = strip_tags(isset($_POST['image']) ? trim($_POST['image']) : '');
        $body       = (isset($_POST['body']) ? trim($_POST['body']) : '');
        $link       = strip_tags(isset($_POST['link']) ? trim($_POST['link']) : '');
        //=== do the cat list :D
        $category_drop_down = '<select name="category" class="required"><option class="body" value="">Select Offer Category</option>';
        $cats               = genrelist();
        foreach ($cats as $row) {
            $category_drop_down .= '<option class="body" value="' . (int) $row['id'] . '"' . ($category == $row['id'] ? ' selected' : '') . '>' . htmlsafechars($row['name'], ENT_QUOTES) . '</option>';
        }
        $category_drop_down .= '</select>';
        if (isset($_POST['category'])) {
            $cat_res   = sql_query('SELECT id AS cat_id, name AS cat_name, image AS cat_image FROM categories WHERE id = ' . sqlesc($category)) or sqlerr(__FILE__, __LINE__);
            $cat_arr   = mysqli_fetch_assoc($cat_res);
            $cat_image = htmlsafechars($cat_arr['cat_image'], ENT_QUOTES);
            $cat_name  = htmlsafechars($cat_arr['cat_name'], ENT_QUOTES);
        }
        //=== if posted and not preview, process it :D
        if (isset($_POST['button']) && $_POST['button'] === 'Submit') {
            sql_query('INSERT INTO offers (offer_name, image, description, category, added, offered_by_user_id, link) VALUES
                    (' . sqlesc($offer_name) . ', ' . sqlesc($image) . ', ' . sqlesc($body) . ', ' . sqlesc($category) . ', ' . TIME_NOW . ', ' . sqlesc($CURUSER['id']) . ',  ' . sqlesc($link) . ');') or sqlerr(__FILE__, __LINE__);
            $new_offer_id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS['___mysqli_ston']))) ? false : $___mysqli_res);
            header('Location: offers.php?action=offer_details&new=1&id=' . $new_offer_id);
            die();
        }
        //=== start page
        $HTMLOUT .= '<table class="table table-bordered table-striped">
    <tr>
    <td class="embedded">
    <h1>New Offer</h1>' . $top_menu . '
    <form method="post" action="offers.php?action=add_new_offer" name="offer_form" id="offer_form">
   ' . (isset($_POST['button']) && $_POST['button'] === 'Preview' ? '<br>
     <table class="table table-bordered table-striped">
    <tr>
    <td class="colhead" colspan="2"><h1>' . htmlsafechars($offer_name, ENT_QUOTES) . '</h1></td>
    </tr>
    <tr>
    <td>image:</td>
    <td><img src="' . htmlsafechars(image_proxy($image), ENT_QUOTES) . '" alt="image" /></td>
    </tr>
    <tr>
    <td >description:</td>
    <td>' . format_comment($body) . '</td>
    </tr>
    <tr>
    <td>category:</td>
    <td><img border="0" src="' . $site_config['pic_baseurl'] . '   caticons/' . get_category_icons() . '/' . htmlsafechars($cat_image, ENT_QUOTES) . '" alt="' . htmlsafechars($cat_name, ENT_QUOTES) . '" /></td>
    </tr>
    <tr>
    <td>link:</td>
    <td><a class="altlink" href="' . htmlsafechars($link, ENT_QUOTES) . '" target="_blank">' . htmlsafechars($link, ENT_QUOTES) . '</a></td>
    </tr>
    <tr>
    <td>offered by:</td>
    <td>' . format_username($CURUSER) . ' [ ' . get_user_class_name($CURUSER['class']) . ' ]
    ratio: ' . member_ratio($CURUSER['uploaded'], $site_config['ratio_free'] ? '0' : $CURUSER['downloaded']) . get_user_ratio_image(($site_config['ratio_free'] ? 1 : $CURUSER['uploaded'] / $CURUSER['downloaded'])) . '</td>
    </tr>
    </table>
    <br>' : '') . '
    <table class="table table-bordered table-striped">
    <tr>
    <td class="colhead" colspan="2"><h1>Making a Offer</h1></td>
    </tr>
    <tr>
    <td colspan="2">Before you make an offer, <a class="altlink" href="search.php">Search</a>
    to be sure it has not yet been requested, offered, or uploaded!<br><br>
    Be sure to fill in all fields!</td>
    </tr>
    <tr>
    <td>name:</td>
    <td><input type="text"  name="offer_name" value="' . htmlsafechars($offer_name, ENT_QUOTES) . '" class="required" /></td>
    </tr>
    <tr>
    <td>image:</td>
    <td><input type="text"  name="image" value="' . htmlsafechars($image, ENT_QUOTES) . '" class="required" /></td>
    </tr>
    <tr>
    <td>link:</td>
    <td><input type="text"  name="link" value="' . htmlsafechars($link, ENT_QUOTES) . '" class="required" /></td>
    </tr>
    <tr>
    <td>category:</td>
    <td>' . $category_drop_down . '</td>
    </tr>
    <tr>
    <td>description:</td>
    <td>' . BBcode($body) . '</td>
    </tr>
    <tr>
    <td colspan="2">
    <input type="submit" name="button" class="button is-small" value="Preview" />
    <input type="submit" name="button" class="button is-small" value="Submit" /></td>
    </tr>
    </table></form>
     </td></tr></table><br>';
        echo stdhead('Add new offer.', true, $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        break;
    //===========================================================================================//
    //====================================      delete  offer      ========================================//
    //===========================================================================================

    case 'delete_offer':
        if (!isset($id) || !is_valid_id($id)) {
            stderr('Error', 'Bad ID.');
        }
        $res = sql_query('SELECT offer_name, offered_by_user_id FROM offers WHERE id =' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_assoc($res);
        if (!$arr) {
            stderr('Error', 'Invalid ID.');
        }
        if ($arr['offered_by_user_id'] !== $CURUSER['id'] && $CURUSER['class'] < UC_STAFF) {
            stderr('Error', 'Permission denied.');
        }
        if (!isset($_GET['do_it'])) {
            stderr('Sanity check...', 'are you sure you would like to delete the offer <b>"' . htmlsafechars($arr['offer_name'], ENT_QUOTES) . '"</b>? If so click
        <a class="altlink" href="offers.php?action=delete_offer&id=' . $id . '&amp;do_it=666" >HERE</a>.');
        } else {
            sql_query('DELETE FROM offers WHERE id=' . $id)             or sqlerr(__FILE__, __LINE__);
            sql_query('DELETE FROM offer_votes WHERE offer_id =' . $id) or sqlerr(__FILE__, __LINE__);
            sql_query('DELETE FROM comments WHERE offer =' . $id)       or sqlerr(__FILE__, __LINE__);
            header('Location: /offers.php?offer_deleted=1');
            die();
        }
        echo stdhead('Delete Offer.', true, $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        break;
    //===========================================================================================//
    //====================================          edit offer      ========================================//
    //===========================================================================================//

    case 'edit_offer':
        require_once INCL_DIR . 'bbcode_functions.php';
        if (!isset($id) || !is_valid_id($id)) {
            stderr('Error', 'Bad ID.');
        }
        $edit_res = sql_query('SELECT offer_name, image, description, category, offered_by_user_id, link FROM offers WHERE id =' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $edit_arr = mysqli_fetch_assoc($edit_res);
        if ($CURUSER['class'] < UC_STAFF && $CURUSER['id'] !== $edit_arr['offered_by_user_id']) {
            stderr('Error!', 'This is not your offer to edit!');
        }
        $offer_name = strip_tags(isset($_POST['offer_name']) ? trim($_POST['offer_name']) : $edit_arr['offer_name']);
        $image      = strip_tags(isset($_POST['image']) ? trim($_POST['image']) : $edit_arr['image']);
        $body       = (isset($_POST['body']) ? trim($_POST['body']) : $edit_arr['description']);
        $link       = strip_tags(isset($_POST['link']) ? trim($_POST['link']) : $edit_arr['link']);
        $category   = (isset($_POST['category']) ? intval($_POST['category']) : $edit_arr['category']);
        //=== do the cat list :D
        $category_drop_down = '<select name="category" class="required"><option class="body" value="">Select Offer Category</option>';
        $cats               = genrelist();
        foreach ($cats as $row) {
            $category_drop_down .= '<option class="body" value="' . (int) $row['id'] . '"' . ($category == $row['id'] ? ' selected' : '') . '>' . htmlsafechars($row['name'], ENT_QUOTES) . '</option>';
        }
        $category_drop_down .= '</select>';
        $cat_res   = sql_query('SELECT id AS cat_id, name AS cat_name, image AS cat_image FROM categories WHERE id = ' . sqlesc($category)) or sqlerr(__FILE__, __LINE__);
        $cat_arr   = mysqli_fetch_assoc($cat_res);
        $cat_image = htmlsafechars($cat_arr['cat_image'], ENT_QUOTES);
        $cat_name  = htmlsafechars($cat_arr['cat_name'], ENT_QUOTES);
        //=== if posted and not preview, process it :D
        if (isset($_POST['button']) && $_POST['button'] === 'Edit') {
            sql_query('UPDATE offers SET offer_name = ' . sqlesc($offer_name) . ', image = ' . sqlesc($image) . ', description = ' . sqlesc($body) . ',
                    category = ' . sqlesc($category) . ', link = ' . sqlesc($link) . ' WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
            header('Location: offers.php?action=offer_details&edited=1&id=' . $id);
            die();
        }
        //=== start page
        $HTMLOUT .= '<table class="table table-bordered table-striped">
    <tr>
    <td class="embedded">
    <h1>Edit Offer</h1>' . $top_menu . '
    <form method="post" action="offers.php?action=edit_offer" name="offer_form" id="offer_form">
    <input type="hidden" name="id" value="' . $id . '" />
    ' . (isset($_POST['button']) && $_POST['button'] === 'Preview' ? '<br>
     <table class="table table-bordered table-striped">
    <tr>
    <td class="colhead" colspan="2"><h1>' . htmlsafechars($offer_name, ENT_QUOTES) . '</h1></td>
    </tr>
    <tr>
    <td>image:</td>
    <td><img src="' . htmlsafechars(image_proxy($image), ENT_QUOTES) . '" alt="image" /></td>
    </tr>
    <tr>
    <td>description:</td>
    <td>' . format_comment($body) . '</td>
    </tr>
    <tr>
    <td>category:</td>
    <td><img border="0" src="' . $site_config['pic_baseurl'] . 'caticons/' . get_category_icons() . '/' . htmlsafechars($cat_image, ENT_QUOTES) . '" alt="' . htmlsafechars($cat_name, ENT_QUOTES) . '" /></td>
    </tr>
    <tr>
    <td>link:</td>
    <td><a class="altlink" href="' . htmlsafechars($link, ENT_QUOTES) . '" target="_blank">' . htmlsafechars($link, ENT_QUOTES) . '</a></td>
    </tr>
    </table>
    <br>' : '') . '
    <table class="table table-bordered table-striped">
    <tr>
    <td class="colhead" colspan="2"><h1>Edit Offer</h1></td>
    </tr>
    <tr>
    <td colspan="2">Be sure to fill in all fields!</td>
    </tr>
    <tr>
    <td>name:</td>
    <td><input type="text"  name="offer_name" value="' . htmlsafechars($offer_name, ENT_QUOTES) . '" class="required" /></td>
    </tr>
    <tr>
    <td class="rowhead">image:</td>
    <td><input type="text"  name="image" value="' . htmlsafechars($image, ENT_QUOTES) . '" class="required" /></td>
    </tr>
    <tr>
    <td>link:</td>
    <td><input type="text"  name="link" value="' . htmlsafechars($link, ENT_QUOTES) . '" class="required" /></td>
    </tr>
    <tr>
    <td>category:</td>
    <td>' . $category_drop_down . '</td>
    </tr>
    <tr>
    <td>description:</td>
    <td>' . BBcode($body) . '</td>
    </tr>
    <tr>
    <td colspan="2">
    <input type="submit" name="button" class="button is-small" value="Preview" />
    <input type="submit" name="button" class="button is-small" value="Edit" /></td>
    </tr>
    </table></form>
     </td></tr></table><br>';
        echo stdhead('Edit Offer.', true, $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        break;
    //===========================================================================================//
    //====================================    add comment          ========================================//
    //===========================================================================================//

    case 'add_comment':
        require_once INCL_DIR . 'bbcode_functions.php';
        require_once INCL_DIR . 'pager_new.php';
        //=== kill if nasty
        if (!isset($id) || !is_valid_id($id)) {
            stderr('USER ERROR', 'Bad id');
        }
        $res = sql_query('SELECT offer_name FROM offers WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_assoc($res);
        if (!$arr) {
            stderr('Error', 'No offer with that ID.');
        }
        if (isset($_POST['button']) && $_POST['button'] === 'Save') {
            $body = trim($_POST['body']);
            if (!$body) {
                stderr('Error', 'Comment body cannot be empty!');
            }
            sql_query('INSERT INTO comments (user, offer, added, text, ori_text) VALUES (' . sqlesc($CURUSER['id']) . ', ' . sqlesc($id) . ', ' . TIME_NOW . ', ' . sqlesc($body) . ',' . sqlesc($body) . ')') or sqlerr(__FILE__, __LINE__);
            $newid = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS['___mysqli_ston']))) ? false : $___mysqli_res);
            sql_query('UPDATE offers SET comments = comments + 1 WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
            header('Location: /offers.php?action=offer_details&id=' . $id . '&viewcomm=' . $newid . '#comm' . $newid);
            die();
        }
        $body = htmlsafechars((isset($_POST['body']) ? $_POST['body'] : ''));
        $HTMLOUT .= $top_menu . '<form method="post" action="offers.php?action=add_comment">
    <input type="hidden" name="id" value="' . $id . '"/>' . (isset($_POST['button']) && $_POST['button'] === 'Preview' ? '
    <table class="table table-bordered table-striped">
    <tr>
    <td class="colhead" colspan="2"><h1>Preview</h1></td>
    </tr>
     <tr>
    <td>' . avatar_stuff($CURUSER) . '</td>
    <td>' . format_comment($body) . '</td>
    </tr></table><br>' : '') . '
     <table class="table table-bordered table-striped">
     <tr>
    <td class="colhead" colspan="2"><h1>Add a comment to "' . htmlsafechars($arr['offer_name'], ENT_QUOTES) . '"</h1></td>
    </tr>
     <tr>
    <td><b>Comment:</b></td>
    <td>' . BBcode($body) . '   </td>
    </tr>
     <tr>
    <td colspan="2">
    <input name="button" type="submit" class="button is-small" value="Preview" />
    <input name="button" type="submit" class="button is-small" value="Save" /></td>
    </tr>
     </table></form>';
        $res     = sql_query('SELECT c.offer, c.id AS comment_id, c.text, c.added, c.editedby, c.editedat, u.id, u.username, u.warned, u.suspended, u.enabled, u.donor, u.class, u.avatar, u.offensive_avatar, u.title, u.leechwarn, u.chatpost, u.pirate, u.king FROM comments AS c LEFT JOIN users AS u ON c.user = u.id WHERE offer = ' . sqlesc($id) . ' ORDER BY c.id DESC LIMIT 5') or sqlerr(__FILE__, __LINE__);
        $allrows = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $allrows[] = $row;
        }
        if (!empty($allrows) && count($allrows)) {
            $HTMLOUT .= '<h2>Most recent comments, in reverse order</h2>';
            $HTMLOUT .= comment_table($allrows);
        }
        echo stdhead('Add a comment to "' . htmlsafechars($arr['offer_name']) . '"', true, $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        break;
    //===========================================================================================//
    //==================================    edit comment    =============================================//
    //===========================================================================================//

    case 'edit_comment':
        require_once INCL_DIR . 'bbcode_functions.php';
        if (!isset($comment_id) || !is_valid_id($comment_id)) {
            stderr('Error', 'Bad ID.');
        }
        $res = sql_query('SELECT c.*, o.offer_name FROM comments AS c LEFT JOIN offers AS o ON c.offer = o.id WHERE c.id=' . sqlesc($comment_id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_assoc($res);
        if (!$arr) {
            stderr('Error', 'Invalid ID.');
        }
        if ($arr['user'] != $CURUSER['id'] && $CURUSER['class'] < UC_STAFF) {
            stderr('Error', 'Permission denied.');
        }
        $body = htmlsafechars((isset($_POST['body']) ? $_POST['body'] : $arr['text']));
        if (isset($_POST['button']) && $_POST['button'] === 'Edit') {
            if ($body == '') {
                stderr('Error', 'Comment body cannot be empty!');
            }
            sql_query('UPDATE comments SET text=' . sqlesc($body) . ', editedat=' . TIME_NOW . ', editedby=' . sqlesc($CURUSER['id']) . ' WHERE id=' . sqlesc($comment_id)) or sqlerr(__FILE__, __LINE__);
            header('Location: /offers.php?action=offer_details&id=' . $id . '&viewcomm=' . $comment_id . '#comm' . $comment_id);
            die();
        }
        if ($CURUSER['id'] == $arr['user']) {
            $avatar = avatar_stuff($CURUSER);
        } else {
            $res_user = sql_query('SELECT avatar, offensive_avatar, view_offensive_avatar FROM users WHERE id=' . sqlesc($arr['user'])) or sqlerr(__FILE__, __LINE__);
            $arr_user = mysqli_fetch_assoc($res_user);
            $avatar   = avatar_stuff($arr_user);
        }
        $HTMLOUT .= $top_menu . '<form method="post" action="offers.php?action=edit_comment">
    <input type="hidden" name="id" value="' . $arr['offer'] . '"/>
    <input type="hidden" name="comment_id" value="' . $comment_id . '"/>
     ' . (isset($_POST['button']) && $_POST['button'] === 'Preview' ? '<table class="table table-bordered table-striped">
    <tr>
    <td class="colhead" colspan="2"><h1>Preview</h1></td>
    </tr>
     <tr>
    <td>' . $avatar . '</td>
    <td>' . format_comment($body) . '</td>
    </tr></table><br>' : '') . '
    <table class="table table-bordered table-striped">
     <tr>
    <td class="colhead" colspan="2"><h1>Edit comment to "' . htmlsafechars($arr['offer_name'], ENT_QUOTES) . '"</h1></td>
    </tr>
     <tr>
    <td><b>Comment:</b></td>
    <td>' . BBcode($body) . '</td>
    </tr>
     <tr>
    <td colspan="2">
    <input name="button" type="submit" class="button is-small" value="Preview" />
    <input name="button" type="submit" class="button is-small" value="Edit" /></td>
    </tr>
     </table></form>';
        echo stdhead('Edit comment to "' . htmlsafechars($arr['offer_name'], ENT_QUOTES) . '"', true, $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        break;
    //===========================================================================================//
    //==================================    delete comment    =============================================//
    //===========================================================================================//

    case 'delete_comment':
        if (!isset($comment_id) || !is_valid_id($comment_id)) {
            stderr('Error', 'Bad ID.');
        }
        $res = sql_query('SELECT user, offer FROM comments WHERE id=' . sqlesc($comment_id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_assoc($res);
        if (!$arr) {
            stderr('Error', 'Invalid ID.');
        }
        if ($arr['user'] != $CURUSER['id'] && $CURUSER['class'] < UC_STAFF) {
            stderr('Error', 'Permission denied.');
        }
        if (!isset($_GET['do_it'])) {
            stderr('Sanity check...', 'are you sure you would like to delete this comment? If so click
        <a class="altlink" href="offers.php?action=delete_comment&amp;id=' . (int) $arr['offer'] . '&amp;comment_id=' . $comment_id . '&amp;do_it=666" >HERE</a>.');
        } else {
            sql_query('DELETE FROM comments WHERE id=' . sqlesc($comment_id))                          or sqlerr(__FILE__, __LINE__);
            sql_query('UPDATE offers SET comments = comments - 1 WHERE id = ' . sqlesc($arr['offer'])) or sqlerr(__FILE__, __LINE__);
            header('Location: /offers.php?action=offer_details&id=' . $id . '&comment_deleted=1');
            die();
        }
        break;
    //===========================================================================================//
    //================================   alter status staff only    ==========================================//
    //===========================================================================================//

    case 'alter_status':
        if ($CURUSER['class'] < UC_STAFF) {
            stderr('Error', 'Permission denied.');
        }
        $set_status = strip_tags(isset($_POST['set_status']) ? $_POST['set_status'] : '');
        //=== add all possible status' check them to be sure they are ok
        $ok_stuff = [
            'approved',
            'pending',
            'denied',
        ];
        //=== check it
        $change_it = (in_array($set_status, $ok_stuff) ? $set_status : 'poop');
        if ($change_it === 'poop') { //=== ok, so I had a bit of fun with that *blush
            stderr('Error', 'Nice try Mr. Fancy Pants!');
        }
        //=== get torrent name :P
        $res_name = sql_query('SELECT offer_name, offered_by_user_id FROM offers WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $arr_name = mysqli_fetch_assoc($res_name);
        if ($change_it === 'approved') {
            $subject = sqlesc('Your Offer has been approved!');
            $message = sqlesc("Hi, \n An offer you made has been approved!!! \n\n Please  [url=" . $site_config['baseurl'] . '/upload.php]Upload ' . htmlsafechars($arr_name['offer_name'], ENT_QUOTES) . "[/url] as soon as possible! \n Members who voted on it will be notified as soon as you do! \n\n [url=" . $site_config['baseurl'] . '/offers.php?action=offer_details&id=' . $id . ']HERE[/url] is your offer.');
            sql_query('INSERT INTO messages (sender, receiver, added, msg, subject, saved, location)
                VALUES(0, ' . sqlesc($arr_name['offered_by_user_id']) . ', ' . TIME_NOW . ', ' . $message . ', ' . $subject . ', \'yes\', 1)') or sqlerr(__FILE__, __LINE__);
        }
        if ($change_it === 'denied') {
            $subject = sqlesc('Your Offer has been denied!');
            $message = sqlesc("Hi, \n An offer you made has been denied. \n\n  [url=" . $site_config['baseurl'] . '/offers.php?action=offer_details&id=' . $id . ']' . htmlsafechars($arr_name['offer_name'], ENT_QUOTES) . '[/url] was denied by ' . $CURUSER['username'] . '. Please contact them to find out why.');
            sql_query('INSERT INTO messages (sender, receiver, added, msg, subject, saved, location)
                VALUES(0, ' . sqlesc($arr_name['offered_by_user_id']) . ', ' . TIME_NOW . ', ' . $message . ', ' . $subject . ', \'yes\', 1)') or sqlerr(__FILE__, __LINE__);
        }
        //=== ok, looks good :D let's set that status!
        sql_query('UPDATE offers SET status = ' . sqlesc($change_it) . ' WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        header('Location: /offers.php?action=offer_details&status_changed=1&id=' . $id);
        die();
        break;
} //=== end all actions / switch
//=== functions n' stuff \o/
/**
 * @param $rows
 *
 * @return string
 */
function comment_table($rows)
{
    global $CURUSER, $site_config;
    $comment_table = '<table class="table table-bordered table-striped">
    <tr>
    <td >';
    foreach ($rows as $row) {
        //=======change colors
        $text = format_comment($row['text']);
        if ($row['editedby']) {
            $res_user = sql_query('SELECT username FROM users WHERE id=' . sqlesc($row['editedby'])) or sqlerr(__FILE__, __LINE__);
            $arr_user = mysqli_fetch_assoc($res_user);
            $text .= '<p>Last edited by <a href="' . $site_config['baseurl'] . '/userdetails.php?id=' . (int) $row['editedby'] . '">
        <b>' . htmlsafechars($arr_user['username']) . '</b></a> at ' . get_date($row['editedat'], 'DATE') . '</p>';
        }
        $top_comment_stuff = $row['comment_id'] . ' by ' . (isset($row['username']) ? format_username($row) . ($row['title'] !== '' ? ' [ ' . htmlsafechars($row['title']) . ' ] ' : ' [ ' . get_user_class_name($row['class']) . ' ]  ') : ' M.I.A. ') . get_date($row['added'], '') . ($row['id'] == $CURUSER['id'] || $CURUSER['class'] >= UC_STAFF ? '
     - [<a href="offers.php?action=edit_comment&amp;id=' . (int) $row['offer'] . '&amp;comment_id=' . (int) $row['comment_id'] . '">Edit</a>]' : '') . ($CURUSER['class'] >= UC_STAFF ? '
     - [<a href="offers.php?action=delete_comment&amp;id=' . (int) $row['offer'] . '&amp;comment_id=' . (int) $row['comment_id'] . '">Delete</a>]' : '') . ($row['editedby'] && $CURUSER['class'] >= UC_STAFF ? '
     - [<a href="comment.php?action=vieworiginal&amp;cid=' . (int) $row['id'] . '">View original</a>]' : '') . '
     - [<a href="report.php?type=Offer_Comment&amp;id_2=' . (int) $row['offer'] . '&amp;id=' . (int) $row['comment_id'] . '">Report</a>]';
        $comment_table .= '
    <table class="table table-bordered table-striped">
    <tr>
    <td colspan="2" class="colhead"># ' . $top_comment_stuff . '</td>
    </tr>
    <tr>
    <td>' . avatar_stuff($row) . '</td>
    <td>' . $text . '</td>
    </tr>
    </table><br>';
    }
    $comment_table .= '</td></tr></table>';

    return $comment_table;
}
