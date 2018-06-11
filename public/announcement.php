<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
check_user_status();
global $CURUSER, $site_config, $cache;

$HTMLOUT = '';
$lang    = array_merge(load_language('global'), load_language('index'), load_language('announcement'));
$dt      = TIME_NOW;
$res     = sql_query('
        SELECT u.id, u.curr_ann_id, u.curr_ann_last_check, u.last_access, ann_main.subject AS curr_ann_subject, ann_main.body AS curr_ann_body
        FROM users AS u
        LEFT JOIN announcement_main AS ann_main ON ann_main.main_id = u.curr_ann_id
        WHERE u.id = ' . sqlesc($CURUSER['id']) . ' AND u.enabled="yes" AND u.status = "confirmed"') or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_assoc($res);

if (($row['curr_ann_id'] > 0) && ($row['curr_ann_body'] == null)) {
    $row['curr_ann_id'] = 0;
    $row['curr_ann_last_check'] = 0;
}
// If elapsed > 3 minutes, force a announcement refresh.
if (($row['curr_ann_last_check'] != 0) && (($row['curr_ann_last_check']) < ($dt - 600)) /* 10 mins **/) {
    $row['curr_ann_last_check'] = 0;
}
if (($row['curr_ann_id'] == 0) and ($row['curr_ann_last_check'] == 0)) { // Force an immediate check...
    $query = sprintf('
                SELECT m.*,p.process_id
                FROM announcement_main AS m
                LEFT JOIN announcement_process AS p ON m.main_id = p.main_id AND p.user_id = %s
                WHERE p.process_id IS NULL OR p.status = 0
                ORDER BY m.main_id ASC
                LIMIT 1', sqlesc($row['id']));
    $result = sql_query($query) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($result)) { // Main Result set exists
        $ann_row = mysqli_fetch_assoc($result);
        $query   = $ann_row['sql_query'];
        // Ensure it only selects...
        if (!preg_match('/\\ASELECT.+?FROM.+?WHERE.+?\\z/', $query)) {
            die();
        }
        // The following line modifies the query to only return the current user
        // row if the existing query matches any attributes.
        $query .= ' AND u.id = ' . sqlesc($row['id']) . ' LIMIT 1';
        $result = sql_query($query) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($result)) { // Announcement valid for member
            $row['curr_ann_id'] = (int) $ann_row['main_id'];
            // Create two row elements to hold announcement subject and body.
            $row['curr_ann_subject'] = $ann_row['subject'];
            $row['curr_ann_body']    = $ann_row['body'];
            // Create additional set for main UPDATE query.
            $add_set = 'curr_ann_id = ' . sqlesc($ann_row['main_id']);
            $cache->update_row('user' . $CURUSER['id'], [
                'curr_ann_id' => $ann_row['main_id'],
            ], $site_config['expires']['user_cache']);
            $status = 2;
        } else {
            // Announcement not valid for member...
            $add_set = 'curr_ann_last_check = ' . sqlesc($dt);
            $cache->update_row('user' . $CURUSER['id'], [
                'curr_ann_last_check' => $dt,
            ], $site_config['expires']['user_cache']);
            $status = 1;
        }
        // Create or set status of process
        if ($ann_row['process_id'] === null) {
            // Insert Process result set status = 1 (Ignore)
            $query = sprintf('INSERT INTO announcement_process (main_id, ' . 'user_id, status) VALUES (%s, %s, %s)', sqlesc($ann_row['main_id']), sqlesc($row['id']), sqlesc($status));
        } else {
            // Update Process result set status = 2 (Read)
            $query = sprintf('UPDATE announcement_process SET status = %s ' . 'WHERE process_id = %s', sqlesc($status), sqlesc($ann_row['process_id']));
        }
        sql_query($query) or sqlerr(__FILE__, __LINE__);
    } else {
        // No Main Result Set. Set last update to now...
        $add_set = 'curr_ann_last_check = ' . sqlesc($dt);
        $cache->update_row('user' . $CURUSER['id'], [
            'curr_ann_last_check' => $dt,
        ], $site_config['expires']['user_cache']);
    }
    unset($result, $ann_row);
}

if ((!empty($add_set))) {
    $add_set = (isset($add_set)) ? $add_set : '';
    sql_query("UPDATE users SET $add_set WHERE id=" . ($row['id'])) or sqlerr(__FILE__, __LINE__);
}

// Announcement Code...
$ann_subject = trim($row['curr_ann_subject']);
$ann_body    = trim($row['curr_ann_body']);
if ((!empty($ann_subject)) && (!empty($ann_body))) {
    $HTMLOUT .= "
    <div class='article'>
        <div class='article_header'>{$lang['index_announce']}</div>
        <div class='tabular'>
            <div class='tabular-row'>
                <div class='tabular-cell'><b><span class='has-text-danger'>{$lang['annouce_announcement']}: " . htmlsafechars($ann_subject) . "</span></b></div>
            </div>
            <span style='color: blue;'>" . format_comment($ann_body) . "</span>
            {$lang['annouce_click']} <a href='{$site_config['baseurl']}/clear_announcement.php'>
            <i><b>{$lang['annouce_here']}</b></i></a> {$lang['annouce_to_clr_annouce']}.
        </div>
    </div>";
} else {
    $HTMLOUT .= main_div("
        <h1>{$lang['index_announce']}</h1>
        <p>{$lang['annouce_announcement']}: {$lang['annouce_nothing_here']}</p>
        <p class='has-text-blue'>{$lang['annouce_cur_no_new_ann']}</p>", 'has-text-centered');
}
echo stdhead($lang['annouce_std_head']) . wrapper($HTMLOUT) . stdfoot();
