<?php

global $lang, $site_config, $CURUSER;

$topic_id = (isset($_GET['topic_id']) ? intval($_GET['topic_id']) : (isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0));
//=== delete stuff from topic page
if ($topic_id > 0) {
    sql_query('DELETE FROM subscriptions WHERE topic_id = ' . sqlesc($topic_id) . ' AND user_id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    //=== ok, all done here, send them back! \o/
    header('Location: ' . $site_config['baseurl'] . '/forums.php?action=view_topic&topic_id=' . $topic_id . '&s=0');
    die();
}
//=== delete stuff from subscriptions page stolen from pdq... thanks hun \o
if (isset($_POST['remove'])) {
    $_POST['remove'] = (isset($_POST['remove']) ? $_POST['remove'] : '');
    $post_delete     = [];
    foreach ($_POST['remove'] as $somevar) {
        $post_delete[] = intval($somevar);
    }
    $post_delete  = array_unique($post_delete);
    $delete_count = count($post_delete);
    if ($delete_count > 0) {
        sql_query('DELETE FROM subscriptions WHERE id IN (' . implode(', ', $post_delete) . ') AND user_id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    } else {
        stderr($lang['gl_error'], $lang['fe_nothing_deleted']);
    }
}
//=== ok, all done here, send them back! \o/
header('Location: ' . $site_config['baseurl'] . '/forums.php?action=subscriptions');
die();
