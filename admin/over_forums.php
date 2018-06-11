<?php

require_once INCL_DIR . 'html_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $lang;

$lang       = array_merge($lang, load_language('ad_over_forums'));
$HTMLOUT    = $over_forums    = $count    = $min_class_viewer    = $sorted    = '';
$main_links = "
            <div class='bottom20'>
                <ul class='level-center bg-06'>
                    <li class='altlink margin20'>
                        <a href='{$site_config['baseurl']}/staffpanel.php?tool=forum_config&amp;action=forum_config'>{$lang['ad_over_configure']}</a>
                    </li>
                    <li class='altlink margin20'>
                        <a href='{$site_config['baseurl']}/staffpanel.php?tool=forum_manage&amp;action=forum_manage'>{$lang['ad_over_manager']}</a>
                    </li>
                </ul>
            </div>
            <h1 class='has-text-centered'>{$lang['ad_over_forum']}</h1>";

$id             = (isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0));
$maxclass       = $CURUSER['class'];
$name           = strip_tags(isset($_POST['name']) ? htmlsafechars($_POST['name']) : '');
$desc           = strip_tags(isset($_POST['desc']) ? htmlsafechars($_POST['desc']) : '');
$sort           = (isset($_POST['sort']) ? intval($_POST['sort']) : 0);
$min_class_view = (isset($_POST['min_class_view']) ? intval($_POST['min_class_view']) : 0);
//=== post / get action posted so we know what to do :P
$posted_action = (isset($_GET['action2']) ? htmlsafechars($_GET['action2']) : (isset($_POST['action2']) ? htmlsafechars($_POST['action2']) : ''));
//=== add all possible actions here and check them to be sure they are ok
$valid_actions = [
    'delete',
    'edit_forum',
    'add_forum',
    'edit_forum_page',
];
$action = (in_array($posted_action, $valid_actions) ? $posted_action : 'forum');
//=== here we go with all the possibilities \\o\o/o//
switch ($action) {
    //=== delete over forum

    case 'delete':
        if (!$id) {
            stderr($lang['std_error'], $lang['std_error_id']);
        }
        sql_query('DELETE FROM over_forums WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        header('Location: staffpanel.php?tool=over_forums');
        die();
        break;
    //=== edit forum

    case 'edit_forum':
        if (!$name && !$desc && !$id) {
            stderr($lang['std_error'], $lang['std_error_form']);
        }
        $res = sql_query('SELECT sort FROM over_forums WHERE sort = ' . sqlesc($sort)) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($res) > 0) {
            stderr($lang['std_error'], $lang['std_error_select_another']);
        }
        sql_query('UPDATE over_forums SET sort = ' . sqlesc($sort) . ', name = ' . sqlesc($name) . ', description = ' . sqlesc($desc) . ', min_class_view = ' . sqlesc($min_class_view) . ' WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        header('Location: staffpanel.php?tool=over_forums');
        die();
        break;
    //=== add forum

    case 'add_forum':
        if (!$name && !$desc) {
            stderr($lang['std_error'], $lang['std_error_form']);
        }
        $res = sql_query('SELECT sort FROM over_forums WHERE sort = ' . sqlesc($sort)) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($res) > 0) {
            stderr($lang['std_error'], $lang['std_error_select_another']);
        }
        sql_query('INSERT INTO over_forums (sort, name,  description,  min_class_view) VALUES (' . sqlesc($sort) . ', ' . sqlesc($name) . ', ' . sqlesc($desc) . ', ' . sqlesc($min_class_view) . ')') or sqlerr(__FILE__, __LINE__);
        header('Location: staffpanel.php?tool=over_forums');
        die();
        break;
    //=== edit over forum stuff

    case 'edit_forum_page':
        $res = sql_query('SELECT * FROM over_forums WHERE id =' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_array($res);
            $HTMLOUT .= $main_links . '<form method="post" action="staffpanel.php?tool=over_forums&amp;action=over_forums">
            <input type="hidden" name="action2" value="edit_forum">
            <input type="hidden" name="id" value="' . $id . '">
        <table class="table table-bordered table-striped">
        <tr>
            <td colspan="2">' . $lang['ad_over_editfor'] . '' . htmlsafechars($row['name'], ENT_QUOTES) . '</td>
          </tr>
            <td><span style="font-weight: bold;">' . $lang['ad_over_name'] . '</span></td>
            <td><input name="name" type="text" class="w-100" maxlength="60" value="' . htmlsafechars($row['name'], ENT_QUOTES) . '" /></td>
          </tr>
          <tr>
            <td ><span style="font-weight: bold;">' . $lang['ad_over_description'] . '</span>  </td>
            <td><input name="desc" type="text" class="w-100" maxlength="200" value="' . htmlsafechars($row['description'], ENT_QUOTES) . '" /></td>
          </tr>
            <tr>
            <td><span style="font-weight: bold;">' . $lang['ad_over_minview'] . ' </span></td>
            <td>
            <select name="min_class_view">';
            for ($i = 0; $i <= $maxclass; ++$i) {
                $over_forums .= '<option class="body" value="' . $i . '"' . ($row['min_class_view'] == $i ? ' selected' : '') . '>' . get_user_class_name($i) . '</option>';
            }
            $HTMLOUT .= $over_forums . '</select></td></tr><tr> 
            <td><span style="font-weight: bold;">' . $lang['ad_over_sort'] . '</span></td>
            <td>
            <select name="sort">';
            $res      = mysqli_query($GLOBALS['___mysqli_ston'], 'SELECT sort FROM over_forums');
            $nr       = mysqli_num_rows($res);
            $maxclass = $nr + 1;
            for ($i = 0; $i <= $maxclass; ++$i) {
                $sorted .= '<option class="body" value="' . $i . '"' . ($row['sort'] == $i ? ' selected' : '') . '>' . $i . '</option>';
            }
            $HTMLOUT .= $sorted . '</select></td></tr>
            <tr>
                <td colspan="2" class="has-text-centered">
                <input type="submit" name="button" class="button is-small margin20" value="' . $lang['ad_over_editbut'] . '" />
                </td>
          </tr>
        </table></form>';
        }
        break;
    //=== over forum stuff

    case 'forum':
        $HTMLOUT .= $main_links;
        $heading = '
            <tr>
                <th class="has-text-centered">' . $lang['ad_over_sort1'] . '</th>
                <th>' . $lang['ad_over_name1'] . '</th>
                <th class="has-text-centered">' . $lang['ad_over_minview1'] . '</th>
                <th class="has-text-centered">' . $lang['ad_over_modify'] . '</th>
            </tr>';
        $res = sql_query('SELECT * FROM over_forums ORDER BY sort ASC') or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($res) > 0) {
            $body = '';
            while ($row = mysqli_fetch_array($res)) {
                $body .= '
            <tr>
                <td class="has-text-centered">' . (int) $row['sort'] . '</td>
            <td>
                <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=forum_view&amp;fourm_id=' . (int) $row['id'] . '">' . htmlsafechars($row['name'], ENT_QUOTES) . '</a><br>
                ' . htmlsafechars($row['description'], ENT_QUOTES) . '
            </td>
            <td class="has-text-centered">' . get_user_class_name($row['min_class_view']) . '</td>
            <td class="has-text-centered">
                <span class="level-center">
                    <span class="left10">
                        <a href="staffpanel.php?tool=over_forums&amp;action=over_forums&amp;action2=edit_forum_page&amp;id=' . $row['id'] . '" >
                            <i class="icon-edit icon"></i>
                        </a>
                    </span>
                    <span>
                        <a href="javascript:confirm_delete(\'' . $row['id'] . '\');">
                            <i class="icon-cancel icon"></i>
                        </a>
                    </span>
                </span>
            </td>
        </tr>';
            }
        }
        $HTMLOUT .= main_table($body, $heading);
        $HTMLOUT .= '
            <form method="post" action="staffpanel.php?tool=over_forums&amp;action=over_forums">
                <input type="hidden" name="action2" value="add_forum" />';
        $body = '
                <tr>
                    <td colspan="2">' . $lang['ad_over_makenew'] . '</td>
                </tr>
                <tr>
                    <td><span>' . $lang['ad_over_name'] . '</span></td>
                    <td><input name="name" type="text" class="w-100" maxlength="60" /></td>
                </tr>
                <tr>
                    <td><span>' . $lang['ad_over_description'] . '</span>  </td>
                    <td><input name="desc" type="text" class="w-100" maxlength="200" /></td>
                </tr>
                <tr>
                    <td><span>' . $lang['ad_over_minview'] . '</span></td>
                    <td>
                        <select name="min_class_view">';
        for ($i = 0; $i <= $maxclass; ++$i) {
            $min_class_viewer .= '
                            <option class="body" value="' . $i . '">' . get_user_class_name($i) . '</option>';
        }
        $body .= $min_class_viewer . '
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><span>' . $lang['ad_over_sort'] . '</span></td>
                    <td>
                        <select name="sort">';
        $res      = sql_query('SELECT sort FROM over_forums') or sqlerr(__FILE__, __LINE__);
        $nr       = mysqli_num_rows($res);
        $maxclass = $nr + 1;
        for ($i = 0; $i <= $maxclass; ++$i) {
            $sorted .= '
                            <option class="body" value="' . $i . '">' . $i . '</option>';
        }
        $body .= $sorted . '
                        </select>
                    </td>
                </tr>';
        $HTMLOUT .= main_table($body, '', 'top20') . '
                <div class="has-text-centered margin20">
                    <input type="submit" name="button" class="button is-small margin20" value="' . $lang['ad_over_makebutton'] . '" />
                </div>
           </form>';
        break;
}
$HTMLOUT .= '<script>
            /*<![CDATA[*/
            function confirm_delete(id)
            {
               if (confirm(\'Are you sure you want to delete this overforum?\'))
               {
                  self.location.href=\'staffpanel.php?tool=over_forums&action=over_forums&action2=delete&id=\'+id;
               }
            }
        /*]]>*/
    </script>';
echo stdhead($lang['ad_over_stdhead']) . wrapper($HTMLOUT) . stdfoot();
