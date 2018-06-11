<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
global $CURUSER;

$lang = array_merge(load_language('global'), load_language('blackjack'));
if ($CURUSER['class'] < UC_POWER_USER) {
    stderr($lang['bj_sorry'], $lang['bj_you_must_be_pu']);
    exit;
}
/**
 * @param $res
 * @param $frame_caption
 *
 * @return string
 */
function bjtable($res, $frame_caption)
{
    global $lang;
    $htmlout = '';
    $htmlout .= begin_frame($frame_caption, true);
    $htmlout .= begin_table();
    $htmlout .= "<tr>
    <td class='colhead'>Rank</td>
    <td class='colhead'>{$lang['bj_user']}</td>
    <td class='colhead has-text-right'>{$lang['bj_wins']}</td>
    <td class='colhead has-text-right'>{$lang['bj_losses']}</td>
    <td class='colhead has-text-right'>{$lang['bj_games']}</td>
    <td class='colhead has-text-right'>{$lang['bj_percentage']}</td>
    <td class='colhead has-text-right'>{$lang['bj_win_loss']}</td>
    </tr>";
    $num = 0;
    while ($a = mysqli_fetch_assoc($res)) {
        ++$num;
        //==Calculate Win %
        $win_perc = number_format(($a['wins'] / $a['games']) * 100, 1);
        //==Add a user's +/- statistic
        $plus_minus = $a['wins'] - $a['losses'];
        if ($plus_minus >= 0) {
            $plus_minus = mksize(($a['wins'] - $a['losses']) * 100 * 1024 * 1024);
        } else {
            $plus_minus = '-';
            $plus_minus .= mksize(($a['losses'] - $a['wins']) * 100 * 1024 * 1024);
        }
        $htmlout .= "<tr><td>$num</td><td>" . format_username($a['id']) . '</td>' . "<td class='has-text-right'>" . number_format($a['wins'], 0) . '</td>' . "<td class='has-text-right'>" . number_format($a['losses'], 0) . '</td>' . "<td class='has-text-right'>" . number_format($a['games'], 0) . '</td>' . "<td class='has-text-right'>$win_perc</td>" . "<td class='has-text-right'>$plus_minus</td>" . "</tr>\n";
    }
    $htmlout .= end_table();
    $htmlout .= end_frame();

    return $htmlout;
}

$HTMLOUT  = '';
$mingames = 10;
$HTMLOUT .= '<br>';
$res = sql_query('SELECT id, username, bjwins AS wins, bjlosses AS losses, bjwins + bjlosses AS games FROM users WHERE bjwins + bjlosses > ' . sqlesc($mingames) . ' ORDER BY games DESC LIMIT 10') or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= bjtable($res, "{$lang['bj_most']} {$lang['bj_games_played']}");
$HTMLOUT .= '<br><br>';
//==Highest Win %
$res = sql_query('SELECT id, username, bjwins AS wins, bjlosses AS losses, bjwins + bjlosses AS games, bjwins / (bjwins + bjlosses) AS winperc FROM users WHERE bjwins + bjlosses > ' . sqlesc($mingames) . ' ORDER BY winperc DESC LIMIT 10') or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= bjtable($res, "{$lang['bj_highest_win_per']}");
$HTMLOUT .= '<br><br>';
//==Highest Win %
$res = sql_query('SELECT id, username, bjwins AS wins, bjlosses AS losses, bjwins + bjlosses AS games, bjwins - bjlosses AS winnings FROM users WHERE bjwins + bjlosses > ' . sqlesc($mingames) . ' ORDER BY winnings DESC LIMIT 10') or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= bjtable($res, "{$lang['bj_most_credit_won']}");
$HTMLOUT .= '<br><br>';
$res = sql_query('SELECT id, username, bjwins AS wins, bjlosses AS losses, bjwins + bjlosses AS games, bjlosses - bjwins AS losings FROM users WHERE bjwins + bjlosses > ' . sqlesc($mingames) . ' ORDER BY losings DESC LIMIT 10') or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= bjtable($res, "{$lang['bj_most_credit_loss']}");
$HTMLOUT .= '<br><br>';
echo stdhead($lang['bj_blackjack_stats']) . $HTMLOUT . stdfoot();
