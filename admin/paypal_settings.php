<?php

require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang, $cache, $session;

$lang = array_merge($lang, load_language('ad_paypal_settings'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $update = [];
    foreach ($site_config['paypal_config'] as $c_name => $c_value) {
        if (isset($_POST[$c_name]) && $_POST[$c_name] != $c_value) {
            $update[] = '(' . sqlesc($c_name) . ',' . sqlesc(is_array($_POST[$c_name]) ? join('|', $_POST[$c_name]) : $_POST[$c_name]) . ')';
        }
    }
    if (sql_query('INSERT INTO paypal_config(name,value) VALUES ' . join(',', $update) . ' ON DUPLICATE KEY UPDATE value = VALUES(value)')) {
        $cache->delete('paypal_settings_');
        $session->set('is-success', 'Update Successful');
    } else {
        $session->set('is-warning', $lang['paypal_saved']);
    }
}

$pconf = sql_query('SELECT * FROM paypal_config') or sqlerr(__FILE__, __LINE__);
while ($ac = mysqli_fetch_assoc($pconf)) {
    $site_config['paypal_config'][$ac['name']] = $ac['value'];
}

$HTMLOUT .= "<h2><b><i>{$lang['paypal_global_title']}</i></b></h2>
<form action='staffpanel.php?tool=paypal_settings' method='post'>
<table width='100%' >
<tr><td width='50%' class='table'>{$lang['paypal_donate']}</td><td class='table'>{$lang['paypal_yes']}<input class='table' type='radio' name='enable' value='1' " . ($site_config['paypal_config']['enable'] ? 'checked=\'checked\'' : '') . " />{$lang['paypal_no']}<input class='table' type='radio' name='enable' value='0' " . (!$site_config['paypal_config']['enable'] ? 'checked=\'checked\'' : '') . " /></td></tr>
<tr><td width='50%' class='table'>{$lang['paypal_email']}</td><td class='table'><input type='text' name='email' size='25' value='" . htmlsafechars($site_config['paypal_config']['email']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['paypal_currency']}</td><td class='table'><input type='text' name='currency' size='4' value='" . htmlsafechars($site_config['paypal_config']['currency']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['paypal_user_pm']}</td><td class='table'><input type='text' name='staff' size='2' value='" . htmlsafechars($site_config['paypal_config']['staff']) . "' /></td></tr>
<tr><td width='50%' class='table'>{$lang['paypal_sandbox']}</td><td class='table'><input type='text' name='sandbox' size='9' value='" . htmlsafechars($site_config['paypal_config']['sandbox']) . "' /></td></tr>
<tr><td colspan='2' class='table'><input type='submit' value='{$lang['paypal_apply']}' /></td></tr>
</table><br><br>
<h2><b><i>{$lang['paypal_array_one']}</i></b>{$lang['paypal_title']}</h2>
<table width='100%' >
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_one']}</i></b>{$lang['paypal_amount']}" . $site_config['paypal_config']['currency'] . "{$lang['paypal_donated']}</td><td class='table'><input type='text' name='gb_donated_1' size='3' value='" . htmlsafechars($site_config['paypal_config']['gb_donated_1']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_one']}</i></b>{$lang['paypal_vip']}</td><td class='table'><input type='text' name='vip_dur_1' size='2' value='" . htmlsafechars($site_config['paypal_config']['vip_dur_1']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_one']}</i></b>{$lang['paypal_donor_status']}</td><td class='table'><input type='text' name='donor_dur_1' size='2' value='" . htmlsafechars($site_config['paypal_config']['donor_dur_1']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_one']}</i></b>{$lang['paypal_freelech']}</td><td class='table'><input type='text' name='free_dur_1' size='2' value='" . htmlsafechars($site_config['paypal_config']['free_dur_1']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_one']}</i></b>{$lang['paypal_amount_gb']}</td><td class='table'><input type='text' name='up_amt_1' size='4' value='" . htmlsafechars($site_config['paypal_config']['up_amt_1']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_one']}</i></b>{$lang['paypal_amount_karma']}</td><td class='table'><input type='text' name='kp_amt_1' size='4' value='" . htmlsafechars($site_config['paypal_config']['kp_amt_1']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_one']}</i></b>{$lang['paypal_amount_invite']}</td><td class='table'><input type='text' name='inv_amt_1' size='4' value='" . htmlsafechars($site_config['paypal_config']['inv_amt_1']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_one']}</i></b>{$lang['paypal_donor_until']}</td><td class='table'><input type='text' name='duntil_dur_1' size='2' value='" . htmlsafechars($site_config['paypal_config']['duntil_dur_1']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_one']}</i></b>{$lang['paypal_immunity']}</td><td class='table'><input type='text' name='imm_dur_1' size='2' value='" . htmlsafechars($site_config['paypal_config']['imm_dur_1']) . "' /></td></tr>
</table><br>
<h2><b><i>{$lang['paypal_array_two']}</i></b>{$lang['paypal_title']}</h2>
<table width='100%' >
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_two']}</i></b>{$lang['paypal_amount']}" . $site_config['paypal_config']['currency'] . "{$lang['paypal_donated']}</td><td class='table'><input type='text' name='gb_donated_2' size='3' value='" . htmlsafechars($site_config['paypal_config']['gb_donated_2']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_two']}</i></b>{$lang['paypal_vip']}</td><td class='table'><input type='text' name='vip_dur_2' size='2' value='" . htmlsafechars($site_config['paypal_config']['vip_dur_2']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_two']}</i></b>{$lang['paypal_donor_status']}</td><td class='table'><input type='text' name='donor_dur_2' size='2' value='" . htmlsafechars($site_config['paypal_config']['donor_dur_2']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_two']}</i></b>{$lang['paypal_freelech']}</td><td class='table'><input type='text' name='free_dur_2' size='2' value='" . htmlsafechars($site_config['paypal_config']['free_dur_2']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_two']}</i></b>{$lang['paypal_amount_gb']}</td><td class='table'><input type='text' name='up_amt_2' size='4' value='" . htmlsafechars($site_config['paypal_config']['up_amt_2']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_two']}</i></b>{$lang['paypal_amount_karma']}</td><td class='table'><input type='text' name='kp_amt_2' size='4' value='" . htmlsafechars($site_config['paypal_config']['kp_amt_2']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_two']}</i></b>{$lang['paypal_amount_invite']}</td><td class='table'><input type='text' name='inv_amt_2' size='4' value='" . htmlsafechars($site_config['paypal_config']['inv_amt_2']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_two']}</i></b>{$lang['paypal_donor_until']}</td><td class='table'><input type='text' name='duntil_dur_2' size='2' value='" . htmlsafechars($site_config['paypal_config']['duntil_dur_2']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_two']}</i></b>{$lang['paypal_immunity']}</td><td class='table'><input type='text' name='imm_dur_2' size='2' value='" . htmlsafechars($site_config['paypal_config']['imm_dur_2']) . "' /></td></tr></table><br>
<h2><b><i>{$lang['paypal_array_three']}</i></b>{$lang['paypal_title']}</h2>
<table width='100%' >
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_three']}</i></b>{$lang['paypal_amount']}" . $site_config['paypal_config']['currency'] . "{$lang['paypal_donated']}</td><td class='table'><input type='text' name='gb_donated_3' size='3' value='" . htmlsafechars($site_config['paypal_config']['gb_donated_3']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_three']}</i></b>{$lang['paypal_vip']}</td><td class='table'><input type='text' name='vip_dur_3' size='2' value='" . htmlsafechars($site_config['paypal_config']['vip_dur_3']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_three']}</i></b>{$lang['paypal_donor_status']}</td><td class='table'><input type='text' name='donor_dur_3' size='2' value='" . htmlsafechars($site_config['paypal_config']['donor_dur_3']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_three']}</i></b>{$lang['paypal_freelech']}</td><td class='table'><input type='text' name='free_dur_3' size='2' value='" . htmlsafechars($site_config['paypal_config']['free_dur_3']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_three']}</i></b>{$lang['paypal_amount_gb']}</td><td class='table'><input type='text' name='up_amt_3' size='4' value='" . htmlsafechars($site_config['paypal_config']['up_amt_3']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_three']}</i></b>{$lang['paypal_amount_karma']}</td><td class='table'><input type='text' name='kp_amt_3' size='4' value='" . htmlsafechars($site_config['paypal_config']['kp_amt_3']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_three']}</i></b>{$lang['paypal_amount_invite']}</td><td class='table'><input type='text' name='inv_amt_3' size='4' value='" . htmlsafechars($site_config['paypal_config']['inv_amt_3']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_three']}</i></b>{$lang['paypal_donor_until']}</td><td class='table'><input type='text' name='duntil_dur_3' size='2' value='" . htmlsafechars($site_config['paypal_config']['duntil_dur_3']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_three']}</i></b>{$lang['paypal_immunity']}</td><td class='table'><input type='text' name='imm_dur_3' size='2' value='" . htmlsafechars($site_config['paypal_config']['imm_dur_3']) . "' /></td></tr>
</table><br>
<h2><b><i>{$lang['paypal_array_four']}</i></b>{$lang['paypal_title']}</h2>
<table width='100%' >
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_four']}</i></b>{$lang['paypal_amount']}" . $site_config['paypal_config']['currency'] . "{$lang['paypal_donated']}</td><td class='table'><input type='text' name='gb_donated_4' size='3' value='" . htmlsafechars($site_config['paypal_config']['gb_donated_4']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_four']}</i></b>{$lang['paypal_vip']}</td><td class='table'><input type='text' name='vip_dur_4' size='2' value='" . htmlsafechars($site_config['paypal_config']['vip_dur_4']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_four']}</i></b>{$lang['paypal_donor_status']}</td><td class='table'><input type='text' name='donor_dur_4' size='2' value='" . htmlsafechars($site_config['paypal_config']['donor_dur_4']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_four']}</i></b>{$lang['paypal_freelech']}</td><td class='table'><input type='text' name='free_dur_4' size='2' value='" . htmlsafechars($site_config['paypal_config']['free_dur_4']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_four']}</i></b>{$lang['paypal_amount_gb']}</td><td class='table'><input type='text' name='up_amt_4' size='4' value='" . htmlsafechars($site_config['paypal_config']['up_amt_4']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_four']}</i></b>{$lang['paypal_amount_karma']}</td><td class='table'><input type='text' name='kp_amt_4' size='4' value='" . htmlsafechars($site_config['paypal_config']['kp_amt_4']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_four']}</i></b>{$lang['paypal_amount_invite']}</td><td class='table'><input type='text' name='inv_amt_4' size='4' value='" . htmlsafechars($site_config['paypal_config']['inv_amt_4']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_four']}</i></b>{$lang['paypal_donor_until']}</td><td class='table'><input type='text' name='duntil_dur_4' size='2' value='" . htmlsafechars($site_config['paypal_config']['duntil_dur_4']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_four']}</i></b>{$lang['paypal_immunity']}</td><td class='table'><input type='text' name='imm_dur_4' size='2' value='" . htmlsafechars($site_config['paypal_config']['imm_dur_4']) . "' /></td></tr>
</table><br>
<h2><b><i>{$lang['paypal_array_five']}</i></b>{$lang['paypal_title']}</h2>
<table width='100%' >
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_five']}</i></b>{$lang['paypal_amount']}" . $site_config['paypal_config']['currency'] . "{$lang['paypal_donated']}</td><td class='table'><input type='text' name='gb_donated_5' size='3' value='" . htmlsafechars($site_config['paypal_config']['gb_donated_5']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_five']}</i></b>{$lang['paypal_vip']}</td><td class='table'><input type='text' name='vip_dur_5' size='2' value='" . htmlsafechars($site_config['paypal_config']['vip_dur_5']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_five']}</i></b>{$lang['paypal_donor_status']}</td><td class='table'><input type='text' name='donor_dur_5' size='2' value='" . htmlsafechars($site_config['paypal_config']['donor_dur_5']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_five']}</i></b>{$lang['paypal_freelech']}</td><td class='table'><input type='text' name='free_dur_5' size='2' value='" . htmlsafechars($site_config['paypal_config']['free_dur_5']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_five']}</i></b>{$lang['paypal_amount_gb']}</td><td class='table'><input type='text' name='up_amt_5' size='4' value='" . htmlsafechars($site_config['paypal_config']['up_amt_5']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_five']}</i></b>{$lang['paypal_amount_karma']}</td><td class='table'><input type='text' name='kp_amt_5' size='4' value='" . htmlsafechars($site_config['paypal_config']['kp_amt_5']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_five']}</i></b>{$lang['paypal_amount_invite']}</td><td class='table'><input type='text' name='inv_amt_5' size='4' value='" . htmlsafechars($site_config['paypal_config']['inv_amt_5']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_five']}</i></b>{$lang['paypal_donor_until']}</td><td class='table'><input type='text' name='duntil_dur_5' size='2' value='" . htmlsafechars($site_config['paypal_config']['duntil_dur_5']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_five']}</i></b>{$lang['paypal_immunity']}</td><td class='table'><input type='text' name='imm_dur_5' size='2' value='" . htmlsafechars($site_config['paypal_config']['imm_dur_5']) . "' /></td></tr>
</table><br>
<h2><b><i>{$lang['paypal_array_six']}</i></b>{$lang['paypal_title']}</h2>
<table width='100%' >
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_six']}</i></b>{$lang['paypal_amount']}" . $site_config['paypal_config']['currency'] . "{$lang['paypal_donated']}</td><td class='table'><input type='text' name='gb_donated_6' size='3' value='" . htmlsafechars($site_config['paypal_config']['gb_donated_6']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_six']}</i></b>{$lang['paypal_vip']}</td><td class='table'><input type='text' name='vip_dur_6' size='2' value='" . htmlsafechars($site_config['paypal_config']['vip_dur_6']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_six']}</i></b>{$lang['paypal_donor_status']}</td><td class='table'><input type='text' name='donor_dur_6' size='2' value='" . htmlsafechars($site_config['paypal_config']['donor_dur_6']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_six']}</i></b>{$lang['paypal_freelech']}</td><td class='table'><input type='text' name='free_dur_6' size='2' value='" . htmlsafechars($site_config['paypal_config']['free_dur_6']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_six']}</i></b>{$lang['paypal_amount_gb']}</td><td class='table'><input type='text' name='up_amt_6' size='4' value='" . htmlsafechars($site_config['paypal_config']['up_amt_6']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_six']}</i></b>{$lang['paypal_amount_karma']}</td><td class='table'><input type='text' name='kp_amt_6' size='4' value='" . htmlsafechars($site_config['paypal_config']['kp_amt_6']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_six']}</i></b>{$lang['paypal_amount_invite']}</td><td class='table'><input type='text' name='inv_amt_6' size='4' value='" . htmlsafechars($site_config['paypal_config']['inv_amt_6']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_six']}</i></b>{$lang['paypal_donor_until']}</td><td class='table'><input type='text' name='duntil_dur_6' size='2' value='" . htmlsafechars($site_config['paypal_config']['duntil_dur_6']) . "' /></td></tr>
<tr><td width='50%' class='table'><b><i>{$lang['paypal_array_six']}</i></b>{$lang['paypal_immunity']}</td><td class='table'><input type='text' name='imm_dur_6' size='2' value='" . htmlsafechars($site_config['paypal_config']['imm_dur_6']) . "' /></td></tr>
<tr><td colspan='2' class='table'><input type='submit' value='{$lang['paypal_apply']}'' /></td></tr>
</table>
</form>";
echo stdhead($lang['paypal_stdhead']) . $HTMLOUT . stdfoot();
