<?php
/**
 * @param $data
 */
function lotteryclean($data)
{
    global $queries, $cache;

    set_time_limit(1200);
    ignore_user_abort(true);

    $dt             = TIME_NOW;
    $lconf          = sql_query('SELECT * FROM lottery_config') or sqlerr(__FILE__, __LINE__);
    $lottery_config = $_pms = $_userq = $uids = [];
    while ($aconf = mysqli_fetch_assoc($lconf)) {
        $lottery_config[$aconf['name']] = $aconf['value'];
    }
    if ($lottery_config['enable'] && $dt > $lottery_config['end_date']) {
        $tickets = [];
        $q       = sql_query('SELECT t.user AS uid, u.seedbonus, u.modcomment
                            FROM tickets AS t
                            LEFT JOIN users AS u ON u.id = t.user
                            ORDER BY RAND()') or sqlerr(__FILE__, __LINE__);
        while ($a = mysqli_fetch_assoc($q)) {
            $tickets[] = $a;
        }
        for ($x = 0; $x <= 1000; ++$x) {
            shuffle($tickets);
        }
        $lottery['winners']       = [];
        $lottery['total_tickets'] = count($tickets);
        for ($i = 0; $i < $lottery['total_tickets']; ++$i) {
            if (!isset($lottery['winners'][$tickets[$i]['uid']])) {
                $lottery['winners'][$tickets[$i]['uid']] = $tickets[$i];
            }
            if ($lottery_config['total_winners'] == count($lottery['winners'])) {
                break;
            }
        }
        if ($lottery_config['use_prize_fund']) {
            $lottery['total_pot'] = $lottery_config['prize_fund'];
        } else {
            $lottery['total_pot'] = $lottery['total_tickets'] * $lottery_config['ticket_amount'];
        }
        $lottery['user_pot'] = round($lottery['total_pot'] / $lottery_config['total_winners'], 2);
        $msg['subject']      = sqlesc('You have won the lottery');
        $msg['body']         = sqlesc('Congratulations, You have won : ' . number_format($lottery['user_pot']) . '. This has been added to your seedbonus total amount. Thanks for playing Lottery.');
        foreach ($lottery['winners'] as $winner) {
            $mod_comment = sqlesc("User won the lottery: {$lottery['user_pot']} at " . get_date($dt, 'LONG') . (!empty($winner['modcomment']) ? "\n" . $winner['modcomment'] : ''));
            $_userq[]    = [
                'id'         => (int) $winner['uid'],
                'seedbonus'  => (float) $winner['seedbonus'] + $lottery['user_pot'],
                'modcomment' => $mod_comment,
            ];
            $_pms[] = '(0,' . $winner['uid'] . ',' . $msg['subject'] . ',' . $msg['body'] . ',' . $dt . ')';
            $uids[] = $winner['uid'];
        }
        $lconfig_update = [
            '(\'enable\',0)',
            '(\'lottery_winners_time\',' . $dt . ')',
            '(\'lottery_winners_amount\',' . $lottery['user_pot'] . ')',
            '(\'lottery_winners\',\'' . join('|', array_keys($lottery['winners'])) . '\')',
        ];
        if (!empty($_userq) && count($_userq)) {
            foreach ($_userq as $update) {
                sql_query("UPDATE users SET seedbonus = {$update['seedbonus']}, modcomment = {$update['modcomment']} WHERE id = {$update['id']}") or sqlerr(__FILE__, __LINE__);
            }
        }
        if (!empty($_pms) && count($_pms)) {
            sql_query('INSERT INTO messages(sender, receiver, subject, msg, added) VALUES ' . join(',', $_pms)) or sqlerr(__FILE__, __LINE__);
        }
        foreach ($uids as $user_id) {
            $cache->increment('inbox_' . $user_id);
            $cache->increment('inbox_sb_' . $user_id);
            $cache->delete('user' . $user_id);
        }
        sql_query('INSERT INTO lottery_config(name,value)
                    VALUES ' . join(',', $lconfig_update) . '
                    ON DUPLICATE KEY UPDATE value=VALUES(value)') or sqlerr(__FILE__, __LINE__);
        sql_query('DELETE FROM tickets') or sqlerr(__FILE__, __LINE__);
        $cache->delete('lottery_info_');
    }

    if ($data['clean_log'] && $queries > 0) {
        write_log("Lottery Cleanup: Completed using $queries queries");
    }
}
