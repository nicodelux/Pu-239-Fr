<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
dbconn();

global $CURUSER, $site_config;

if (!$CURUSER) {
    get_template();
} else {
    header("Location: {$site_config['baseurl']}/index.php");
    die();
}
$stdfoot = [
    'js' => [
    ],
];
$lang = array_merge(load_language('global'), load_language('login'));
$left = $total = '';

/**
 * @return string
 */
function left()
{
    global $site_config, $fluent;

    $ip    = getip();
    $count = $fluent->from('failedlogins')
        ->select(null)
        ->select('COUNT(*) AS count')
        ->where('INET6_NTOA(ip) = ?', $ip)
        ->fetch('count');

    $left = $site_config['failedlogins'] - $count;
    if ($left <= 2) {
        $left = "
        <span>{$left}</span>";
    } else {
        $left = "
        <span>{$left}</span>";
    }

    return $left;
}

$HTMLOUT = '';
if (!empty($_GET['returnto'])) {
    $returnto = htmlsafechars($_GET['returnto']);
}
if (!isset($_GET['nowarn'])) {
    $HTMLOUT .= "
        <div class='half-container has-text-centered portlet'>
            <div class='margin20'>
                <h3>{$lang['login_error']}</h3>
                <h3>{$lang['login_cookies']}</h3>
                <h3>{$lang['login_cookies1']}</h3>
                <h3>
                    <b>[{$site_config['failedlogins']}]</b> {$lang['login_failed']}<br>{$lang['login_failed_1']}<b> " . left() . " </b> {$lang['login_failed_2']}
                </h3>
            </div>";
}
$got_ssl = isset($_SERVER['HTTPS']) && (bool)$_SERVER['HTTPS'] == true ? true : false;
$HTMLOUT .= "
            <form class='form-inline table-wrapper' method='post' action='takelogin.php'>
                <table class='table table-bordered'>
                    <tr class='no_hover'>
                        <td class='rowhead'>{$lang['login_username']}</td>
                        <td>
                            <input type='text' class='w-100' name='username' />" . ($got_ssl ? "
                            <input type='hidden' name='use_ssl' value='" . ($got_ssl ? 1 : 0) . "' id='ssl' />" : '') . "
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td class='rowhead'>{$lang['login_password']}</td>
                        <td><input type='password' class='w-100' name='password' /></td>
                    </tr>";

if (!empty($_ENV['RECAPTCHA_SITE_KEY'])) {
    $HTMLOUT .= "
                    <tr>
                        <td colspan='2'>
                            <div class='g-recaptcha level-center' data-theme='dark' data-sitekey='{$_ENV['RECAPTCHA_SITE_KEY']}'></div>
                        </td>
                    </tr>";
}
$HTMLOUT .= "
                    <tr class='no_hover'>
                        <td colspan='2' class='has-text-centered'>
                            <span class='has-text-centered margin5'>
                                <input name='submitme' type='submit' value='Login' class='button is-small' />
                            </span>";

if (isset($returnto)) {
    $HTMLOUT .= "
                            <input type='hidden' name='returnto' value='" . htmlsafechars($returnto) . "' />";
}
$HTMLOUT .= "           </td>
                    </tr>
                    <tr class='no_hover'>
                        <td colspan='2' class='has-text-centered'>
                            <span class='has-text-centered margin5'>
                                <label for='remember' class='level-item tooltipper' title='Keep me logged in'>Remember Me?
                                    <input type='checkbox' name='remember' value='1' id='remember' class='left10' />
                                </label>
                            </span>
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td colspan='2'>
                            <span class='level is-flex is-wrapped margin5'>
                                <span class='tab'>{$lang['login_signup']}</span>
                                <span class='tab'>{$lang['login_forgot']}</span>
                                <span class='tab'>{$lang['login_forgot_1']}</span>
                            </span>
                        </td>
                    </tr>
                </table>
            </form>
        </div>";

echo stdhead("{$lang['login_login_btn']}", true) . $HTMLOUT . stdfoot($stdfoot);
