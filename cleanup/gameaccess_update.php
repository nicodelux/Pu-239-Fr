<?php
/**
 * @param $data
 *
 * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
 */
function gameaccess_update($data)
{
    global $site_config, $queries, $cache;

    set_time_limit(1200);
    ignore_user_abort(true);

    $res         = sql_query('SELECT id, modcomment FROM users WHERE game_access > 1 AND game_access < ' . TIME_NOW) or sqlerr(__FILE__, __LINE__);
    $msgs_buffer = $users_buffer = [];
    if (mysqli_num_rows($res) > 0) {
        $dt      = TIME_NOW;
        $subject = 'Games ban expired.';
        $msg     = "Your Games ban has expired and has been auto-removed by the system.\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $modcomment     = $arr['modcomment'];
            $modcomment     = get_date($dt, 'DATE', 1) . " - Games ban Removed By System.\n" . $modcomment;
            $modcom         = sqlesc($modcomment);
            $msgs_buffer[]  = '(0,' . $arr['id'] . ',' . $dt . ', ' . sqlesc($msg) . ', ' . sqlesc($subject) . ' )';
            $users_buffer[] = '(' . $arr['id'] . ', \'1\', ' . $modcom . ')';
            $cache->update_row('user' . $arr['id'], [
                'game_access' => 1,
                'modcomment'  => $modcomment,
            ], $site_config['expires']['user_cache']);
            $cache->increment('inbox_' . $arr['id']);
        }
        $count = count($users_buffer);
        if ($count > 0) {
            sql_query('INSERT INTO messages (sender, receiver, added, msg, subject) VALUES ' . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
            sql_query('INSERT INTO users (id, game_access, modcomment) VALUES ' . implode(', ', $users_buffer) . ' ON DUPLICATE KEY UPDATE game_access = VALUES(game_access), modcomment = VALUES(modcomment)') or sqlerr(__FILE__, __LINE__);
        }
        if ($data['clean_log']) {
            write_log('Cleanup - Removed Game ban from ' . $count . ' members');
        }
        unset($users_buffer, $msgs_buffer, $count);
    }
    if ($data['clean_log'] && $queries > 0) {
        write_log("Games Possible Cleanup: Completed using $queries queries");
    }
}
