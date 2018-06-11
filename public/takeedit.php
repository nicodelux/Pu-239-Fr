<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'function_memcache.php';
global $CURUSER, $site_config, $cache, $session;

/*
 *
 */
define('MIN_CLASS', UC_STAFF);
/*
 *
 */
define('NFO_SIZE', 65535);
check_user_status();
$lang                = array_merge(load_language('global'), load_language('takeedit'), load_language('details'));
$torrent_cache       = $torrent_txt_cache       = '';
$possible_extensions = [
    'nfo',
    'txt',
];
if (!mkglobal('id:name:body:type')) {
    $session->set('is-warning', 'Id, descr, name or type is missing');
    header("Location: {$_SERVER['HTTP_REFERER']}");
    die();
}
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if (!is_valid_id($id)) {
    $session->set('is-warning', $lang['takedit_no_data']);
    header("Location: {$_SERVER['HTTP_REFERER']}");
    die();
}
/**
 * @Function valid_torrent_name
 * @Notes    only safe characters are allowed..
 * @Begin
 *
 * @param $torrent_name
 *
 * @return bool
 */
function valid_torrent_name($torrent_name)
{
    $allowedchars = 'abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789.-_[]*()';
    for ($i = 0; $i < strlen($torrent_name); ++$i) {
        if (strpos($allowedchars, $torrent_name[$i]) === false) {
            return false;
        }
    }

    return true;
}

/*
 *
 * @Function is_valid_url
 * @Begin
 */
if (!function_exists('is_valid_url')) {
    /**
     * @param $link
     *
     * @return int
     */
    function is_valid_url($link)
    {
        return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $link);
    }
}
/**
 * @Function is_valid_url
 * @End
 */
$nfoaction      = '';
$select_torrent = sql_query('SELECT name, descr, category, visible, vip, release_group, poster, url, newgenre, description, anonymous, sticky, owner, allow_comments, nuked, nukereason, filename, save_as, youtube, tags, info_hash, freetorrent FROM torrents WHERE id = ' . sqlesc($id))       or sqlerr(__FILE__, __LINE__);
$fetch_assoc    = mysqli_fetch_assoc($select_torrent)                                                                                                                                                                                                                                             or stderr('Error', 'No torrent with this ID!');
$infohash       = $fetch_assoc['info_hash'];
if ($CURUSER['id'] != $fetch_assoc['owner'] && $CURUSER['class'] < MIN_CLASS) {
    $session->set('is-danger', "You're not the owner of this torrent.");
    header("Location: {$_SERVER['HTTP_REFERER']}");
    die();
}
$updateset = $torrent_cache = $torrent_txt_cache = [];
$fname     = $fetch_assoc['filename'];
preg_match('/^(.+)\.torrent$/si', $fname, $matches);
$shortfname = $matches[1];
$dname      = $fetch_assoc['save_as'];
if ((isset($_POST['nfoaction'])) && ($_POST['nfoaction'] === 'update')) {
    if (empty($_FILES['nfo']['name'])) {
        $session->set('is-warning', 'No NFO!');
        header("Location: {$_SERVER['HTTP_REFERER']}");
        die();
    }
    if ($_FILES['nfo']['size'] == 0) {
        $session->set('is-warning', '0-byte NFO!');
        header("Location: {$_SERVER['HTTP_REFERER']}");
        die();
    }
    if (!preg_match('/^(.+)\.[' . join(']|[', $possible_extensions) . ']$/si', $_FILES['nfo']['name'])) {
        $session->set('is-warning', 'Invalid extension. <b>' . join(', ', $possible_extensions) . '</b> only!');
        header("Location: {$_SERVER['HTTP_REFERER']}");
        die();
    }
    if (!empty($_FILES['nfo']['name']) && $_FILES['nfo']['size'] > NFO_SIZE) {
        $session->set('is-warning', 'NFO is too big! Max ' . number_format(NFO_SIZE) . ' bytes!');
        header("Location: {$_SERVER['HTTP_REFERER']}");
        die();
    }
    if (@is_uploaded_file($_FILES['nfo']['tmp_name']) && @filesize($_FILES['nfo']['tmp_name']) > 0) {
        $updateset[] = 'nfo = ' . sqlesc(str_replace("\x0d\x0d\x0a", "\x0d\x0a", file_get_contents($_FILES['nfo']['tmp_name'])));
    }
    $torrent_cache['nfo'] = str_replace("\x0d\x0d\x0a", "\x0d\x0a", file_get_contents($_FILES['nfo']['tmp_name']));
} elseif ($nfoaction === 'remove') {
    $updateset[]          = "nfo = ''";
    $torrent_cache['nfo'] = '';
}
//== Make sure they do not forget to fill these fields :D
foreach ([
             $type,
             $body,
             $name,
         ] as $x) {
    if (empty($x)) {
        $session->set('is-warning', $lang['takedit_no_data']);
        header("Location: {$_SERVER['HTTP_REFERER']}");
        die();
    }
}
if (isset($_POST['youtube']) && preg_match($youtube_pattern, $_POST['youtube'], $temp_youtube)) {
    if ($temp_youtube[0] != $fetch_assoc['youtube']) {
        $updateset[] = 'youtube = ' . sqlesc($temp_youtube[0]);
    }
    $torrent_cache['youtube'] = $temp_youtube[0];
}
if (isset($_POST['name']) && (($name = $_POST['name']) != $fetch_assoc['name']) && valid_torrent_name($name)) {
    $updateset[]                  = 'name = ' . sqlesc($name);
    $updateset[]                  = 'search_text = ' . sqlesc(searchfield("$shortfname $dname"));
    $torrent_cache['search_text'] = searchfield("$shortfname $dname");
    $torrent_cache['name']        = $name;
}
if (isset($_POST['body']) && ($body = $_POST['body']) != $fetch_assoc['descr']) {
    $updateset[]                = 'descr = ' . sqlesc($body);
    $updateset[]                = 'ori_descr = ' . sqlesc($body);
    $torrent_txt_cache['descr'] = $body;
}
if (isset($_POST['description']) && ($smalldescr = $_POST['description']) != $fetch_assoc['description']) {
    $updateset[]                  = 'description = ' . sqlesc($smalldescr);
    $torrent_cache['description'] = $smalldescr;
}
if (isset($_POST['tags']) && ($tags = $_POST['tags']) != $fetch_assoc['tags']) {
    $updateset[]           = 'tags = ' . sqlesc($tags);
    $torrent_cache['tags'] = $tags;
}
if (isset($_POST['type']) && (($category = (int) $_POST['type']) != $fetch_assoc['category']) && is_valid_id($category)) {
    $updateset[]               = 'category = ' . sqlesc($category);
    $torrent_cache['category'] = $category;
}

if (($visible = (!empty($_POST['visible']) ? 'yes' : 'no')) != $fetch_assoc['visible']) {
    $updateset[]              = 'visible = ' . sqlesc($visible);
    $torrent_cache['visible'] = $visible;
}
if ($CURUSER['class'] > UC_STAFF) {
    if (isset($_POST['banned'])) {
        $updateset[]              = "banned = 'yes'";
        $_POST['visible']         = 0;
        $torrent_cache['banned']  = 'yes';
        $torrent_cache['visible'] = 0;
    } else {
        $updateset[] = "banned = 'no'";
    }
    $torrent_cache['banned'] = 'no';
}
/*
 *
 * @Custom Mods
 *
 */
//== Subs
if (in_array($category, $site_config['movie_cats'])) {
    $subs                  = isset($_POST['subs']) ? implode(',', $_POST['subs']) : '';
    $updateset[]           = 'subs = ' . sqlesc($subs);
    $torrent_cache['subs'] = $subs;
}
// ==09 Sticky torrents
if (($sticky = (!empty($_POST['sticky']) ? 'yes' : 'no')) != $fetch_assoc['sticky']) {
    $updateset[] = 'sticky = ' . sqlesc($sticky);
    if ($sticky === 'yes') {
        sql_query('UPDATE usersachiev SET stickyup = stickyup + 1 WHERE userid = ' . sqlesc($fetch_assoc['owner'])) or sqlerr(__FILE__, __LINE__);
    }
    //$torrent_cache['sticky'] = $sticky;
}

if (isset($_POST['nuked']) && ($nuked = $_POST['nuked']) != $fetch_assoc['nuked']) {
    $updateset[]            = 'nuked = ' . sqlesc($nuked);
    $torrent_cache['nuked'] = $nuked;
}
if (isset($_POST['nukereason']) && ($nukereason = $_POST['nukereason']) != $fetch_assoc['nukereason']) {
    $updateset[]                 = 'nukereason = ' . sqlesc($nukereason);
    $torrent_cache['nukereason'] = $nukereason;
}

if (isset($_POST['poster']) && (($poster = $_POST['poster']) != $fetch_assoc['poster'] && !empty($poster))) {
    if (!preg_match("/^(http|https):\/\/[^\s'\"<>]+\.(jpg|gif|png)$/i", $poster)) {
        $session->set('is-warning', 'Poster MUST be in jpg, gif or png format. Make sure you include http:// in the URL.');
        header("Location: {$_SERVER['HTTP_REFERER']}");
        die();
    }
    $updateset[]             = 'poster = ' . sqlesc($poster);
    $torrent_cache['poster'] = $poster;
}

if (empty($_POST['poster']) && !empty($fetch_assoc['poster'])) {
    $updateset[]             = "poster = ''";
    $torrent_cache['poster'] = '';
}

if (isset($_POST['free_length']) && ($free_length = (int) $_POST['free_length'])) {
    if ($free_length == 255) {
        $free = 1;
    } elseif ($free_length == 42) {
        $free = (86400 + TIME_NOW);
    } else {
        $free = (TIME_NOW + $free_length * 604800);
    }
    $updateset[]           = 'free = ' . sqlesc($free);
    $torrent_cache['free'] = $free;
    write_log("Torrent $id ($name) set Free for " . ($free != 1 ? 'Until ' . get_date($free, 'DATE') : 'Unlimited') . " by $CURUSER[username]");
}
if (isset($_POST['fl']) && ($_POST['fl'] == 1)) {
    $updateset[]           = "free = '0'";
    $torrent_cache['free'] = '0';
    write_log("Torrent $id ($name) No Longer Free. Removed by $CURUSER[username]");
}
/// end freeleech mod
//==09 Set Silver on Torrent Time Based
if (isset($_POST['half_length']) && ($half_length = (int) $_POST['half_length'])) {
    if ($half_length == 255) {
        $silver = 1;
    } elseif ($half_length == 42) {
        $silver = (86400 + TIME_NOW);
    } else {
        $silver = (TIME_NOW + $half_length * 604800);
    }
    $updateset[]             = 'silver = ' . sqlesc($silver);
    $torrent_cache['silver'] = $silver;
    write_log("Torrent $id ($name) set Half leech for " . ($silver != 1 ? 'Until ' . get_date($silver, 'DATE') : 'Unlimited') . " by $CURUSER[username]");
}
if (isset($_POST['slvr']) && ($_POST['slvr'] == 1)) {
    $updateset[]             = "silver = '0'";
    $torrent_cache['silver'] = '0';
    write_log("Torrent $id ($name) No Longer Half leech. Removed by $CURUSER[username]");
}
/// end silver torrent mod
// ===09 Allowcomments
if ((isset($_POST['allow_comments'])) && (($allow_comments = $_POST['allow_comments']) != $fetch_assoc['allow_comments'])) {
    if ($CURUSER['class'] >= UC_STAFF) {
        $updateset[] = 'allow_comments = ' . sqlesc($allow_comments);
    }
    $torrent_cache['allow_comments'] = $allow_comments;
}
// ===end
//==Xbt freetorrent
if (($freetorrent = (!empty($_POST['freetorrent']) ? '1' : '0')) != $fetch_assoc['freetorrent']) {
    $updateset[]                  = 'freetorrent = ' . sqlesc($freetorrent);
    $torrent_cache['freetorrent'] = $freetorrent;
}
//==09 Imdb mod
if (isset($_POST['url']) && (($url = $_POST['url']) != $fetch_assoc['url'] && !empty($url))) {
    if (!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url)) {
        //if (!preg_match("/^(http|https):\/\/[^\s'\"<>]+\.(jpg|gif|png)$/i", $url))
        $session->set('is-warning', 'Make sure you include http:// in the URL.');
        header("Location: {$_SERVER['HTTP_REFERER']}");
        die();
    }
    $updateset[]          = 'url = ' . sqlesc($url);
    $torrent_cache['url'] = $url;
}
if (!empty($_POST['isbn']) && $_POST['isbn'] != $fetch_assoc['isbn']) {
    $isbn                  = $_POST['isbn'];
    $updateset[]           = 'isbn = ' . sqlesc($isbn);
    $torrent_cache['isbn'] = $isbn;
}

//==09 Anonymous torrents
if (($anonymous = (!empty($_POST['anonymous']) ? 'yes' : 'no')) != $fetch_assoc['anonymous']) {
    $updateset[]                = 'anonymous = ' . sqlesc($anonymous);
    $torrent_cache['anonymous'] = $anonymous;
}
//==09 vip tor
if (($vip = (!empty($_POST['vip']) ? '1' : '0')) != $fetch_assoc['vip']) {
    $updateset[]          = 'vip = ' . sqlesc($vip);
    $torrent_cache['vip'] = $vip;
}
//==Release group
$release_group_choices = [
    'scene' => 1,
    'p2p'   => 2,
    'none'  => 3,
];

$release_group = (isset($_POST['release_group']) ? $_POST['release_group'] : 'none');
if (isset($release_group_choices[$release_group])) {
    $updateset[] = 'release_group = ' . sqlesc($release_group);
}
$torrent_cache['release_group'] = $release_group;

$genreaction = (isset($_POST['genre']) ? $_POST['genre'] : '');

$genre = '';

if ($genreaction != 'keep') {
    if (isset($_POST['music'])) {
        $genre = implode(',', $_POST['music']);
    } elseif (isset($_POST['movie'])) {
        $genre = implode(',', $_POST['movie']);
    } elseif (isset($_POST['game'])) {
        $genre = implode(',', $_POST['game']);
    } elseif (isset($_POST['apps'])) {
        $genre = implode(',', $_POST['apps']);
    }
    $updateset[]               = 'newgenre = ' . sqlesc($genre);
    $torrent_cache['newgenre'] = $genre;
}
if (count($updateset) > 0) {
    sql_query('UPDATE torrents SET ' . implode(',', $updateset) . ' WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
}
if ($torrent_cache) {
    $cache->update_row('torrent_details_' . $id, $torrent_cache, $site_config['expires']['torrent_details']);
    $cache->delete('top5_tor_');
    $cache->delete('last5_tor_');
}
if ($torrent_txt_cache) {
    $cache->update_row('torrent_details_txt_' . $id, $torrent_txt_cache, $site_config['expires']['torrent_details_text']);
}
remove_torrent($infohash);
write_log('torrent edited - ' . htmlsafechars($name) . ' was edited by ' . (($fetch_assoc['anonymous'] == 'yes') ? 'Anonymous' : htmlsafechars($CURUSER['username'])) . '');
$cache->delete('editedby_' . $id);
//$returl = (isset($_POST['returnto']) ? '&returnto=' . urlencode($_POST['returnto']) : 'details.php?id=' . $id);

$session->set('is-success', $lang['details_success_edit']);
header("Location: {$site_config['baseurl']}/details.php?id=$id");
die();