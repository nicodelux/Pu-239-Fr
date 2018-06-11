<?php

global $site_config;

$lang = [
    //index
    'index_announce'        => 'Anun&#355;uri',
    'index_latest'          => 'Ultimele torente',
    'index_latest_comments' => 'Latest Comments',
    'index_ajaxchat'        => 'AJAX Chat',
    'index_active_irc'      => 'Utilizatori activi pe irc',
    'index_active'          => 'Utilizatori activi',
    'index_active24'        => 'Cei mai multi utilizatori &#238;n 24h',
    'index_most24'          => 'Cele mai multe vizite &#238;n 24h',
    'index_member24'        => 'Membri',
    'index_noactive'        => 'Ne pare r&#259;u - Momentan nu sunt utilizatori activi ',
    'index_disclaimer'      => 'Termeni de utilizare',
    'index_donations'       => 'Doneaz&#259;',
    'index_serverload'      => 'Solicitare Server',
    'index_pu-239'          => 'Pu-239 Forum',
    'index_pu-239_git'      => 'Pu-239 Repo',
    'index_serverscene'     => 'Server Scene',
    'index_birthday'        => 'Anivers&#259;ri membri',
    'index_trivia'          => 'Trivia Game',
    //News
    'news_title'  => 'Ultimele &#351;tiri',
    'news_link'   => 'Pagin&#259; cu &#351;tiri',
    'news_edit'   => 'Editeaz&#259;',
    'news_delete' => '&#350;terge',
    //latest torrents
    'latesttorrents_title'       => $site_config['latest_torrents_limit'] . ' ultimele torente',
    'latesttorrents_type'        => 'Tip',
    'latesttorrents_name'        => 'Nume',
    'latesttorrents_seeders'     => 'Seederi',
    'latesttorrents_leechers'    => 'Leecheri',
    'latesttorrents_no_torrents' => 'Nu s-au g&#259;sit torente',
    // last 5
    'last5torrents_title'       => 'Ultimele 5 torente',
    'last5torrents_type'        => 'Tip',
    'last5torrents_name'        => 'Nume',
    'last5torrents_seeders'     => 'Seederi',
    'last5torrents_leechers'    => 'Leecheri',
    'last5torrents_no_torrents' => 'Nu s-au g&#259;sit torente',
    // top 5
    'top5torrents_title'       => ' Top 5 torente',
    'top5torrents_type'        => 'Tip',
    'top5torrents_name'        => 'Nume',
    'top5torrents_seeders'     => 'Seederi',
    'top5torrents_leechers'    => 'Leecheri',
    'top5torrents_no_torrents' => 'Nu s-au g&#259;sit torente',
    //Change log
    'clog_title'  => 'Change log',
    'clog_link'   => 'Pagin&#259; Log',
    'clog_edit'   => 'Editeaz&#259;',
    'clog_delete' => '&#350;terge',
    //latest forum posts
    'latestposts_title'       => $site_config['latest_posts_limit'] . ' Latest Forum Posts',
    'latestposts_topic_title' => 'Titlu&#160;subiect',
    'latestposts_replies'     => 'R&#259;spunsuri',
    'latestposts_views'       => 'Vizualiz&#259;ri',
    'latestposts_last_post'   => 'Ultima &#160;Postare',
    'latestposts_posted_at'   => 'Postat&#160;la',
    'latestposts_no_posts'    => 'Nu s-au g&#259;sit post&#259;ri',
    //Stats
    'index_stats_title'         => 'Statistici',
    'index_stats_regged'        => 'Utilizatori &#238;nregistra&#355;i',
    'index_stats_online'        => 'Utilizatori online',
    'index_stats_uncon'         => 'Utilizatori neconfirma&#355;i',
    'index_stats_donor'         => 'Donori',
    'index_stats_topics'        => 'Subiecte pe forum',
    'index_stats_torrents'      => 'Torente',
    'index_stats_posts'         => 'Post&#259;ri pe forum',
    'index_stats_newtor'        => 'Torente noi ad&#259;ugate azi',
    'index_stats_newtor_month'  => 'Torente noi ad&#259;ugate luna aceasta',
    'index_stats_peers'         => 'Peeri',
    'index_stats_unconpeer'     => 'Peeri neconectabili',
    'index_stats_seeders'       => 'Seederi',
    'index_stats_unconratio'    => 'Ratie neconectabili (%)',
    'index_stats_leechers'      => 'Leecheri',
    'index_stats_slratio'       => 'Ra&#355;ie Seeder/leecher (%)',
    'index_stats_gender_na'     => 'Gen neselectat',
    'index_stats_gender_male'   => 'Masculin',
    'index_stats_gender_female' => 'Feminin',
    'index_stats_powerusers'    => 'Utilizatori Power',
    'index_stats_banned'        => 'Dezactiva&#355;i',
    'index_stats_uploaders'     => 'Uploaderi',
    'index_stats_moderators'    => 'Moderatori',
    'index_stats_admin'         => 'Administratori',
    'index_stats_sysops'        => 'Sysopi',
    //disclaimer
    'foot_disclaimer' => 'Acest site este privat! Nici unul din fi&#351;ierele care apar aici nu se afl&#259; pe acest server. Link-urile sunt furnizate pe proprie r&#259;spundere de c&#259;tre utilizatorii &#238;nregistra&#355;i. 
Administratorul site-ului (%s)  nu poate fi tras la r&#259;spundere pentru ceea ce posteaz&#259; sau pentru ac&#355;iunile desf&#259;&#351;urate de c&#259;tre utilizatorii site-ului. 
V&#259; este interzis s&#259; folosi&#355;i acest site pentru a distribui orice fel de material (date) dac&#259; nu ave&#355;i drepturi legale asupra de&#355;inerii &#351;i transmiterii lui.
Este obliga&#355;ia dumneavoastra s&#259; v&#259; conforma&#355;i acestor termeni de utilizare. ',
    //last24
    'index_last24_nousers' => 'Nici&#160;un&#160;utilizator&#160;activ&#160;&#238;n&#160;ultimele&#160;15&#160;minute.',
    'index_last24_list'    => '&#160;-&#160;Lista&#160;este&#160;actualizat&#259;&#160;la fiecare or&#259;',
    'index_last24_during'  => ' - &#238;n ultimele 24 de ore',
    'index_last24_most'    => 'Cele mai multe vizite &#238;n 24 de ore au fost de la&#160;',
    'index_last24_on'      => ' la data de ',
    //global show hide
    'index_hide_show'  => '[Ascunde/Arat&#259;]',
    'index_click_more' => 'Clic pentru mai multe informa&#355;ii',
    //irc users
    'index_irc_days'    => 'zile',
    'index_irc_hrs'     => 'ore',
    'index_irc_min'     => 'minute',
    'index_irc_nousers' => 'Nici un utilizator activ pe IRC &#238;n ultimele 15 minute.',
    //birthday users
    'index_birthday_no' => 'Nu sunt anivers&#259;ri ast&#259;zi.',
    //active users
    'index_active_users_no' => 'Nici un utilizator activ &#238;n ultimele 15 minute.',
    //advertise
    'index_advertise_t' => 'Pu-239',
    //announcement
    'index_ann_title' => 'Anun&#355',
    'index_ann_click' => 'Clic ',
    'index_ann_here'  => 'aici',
    'index_ann_clear' => ' pentru a &#351;terge acest anunt.',
    //forum_posts
    'index_fposts_anonymous' => 'Anonim',
    'index_fposts_unknow'    => 'Necunoscut',
    'index_fposts_system'    => 'System',
    'index_fposts_sticky'    => 'Important',
    'index_fposts_stickyt'   => 'Subiect important',
    'index_fposts_locked'    => '&#206;nchis',
    'index_fposts_lockedt'   => 'Topic &#238;nchis',
    'index_fposts_in'        => '&#238;n ',
    //Christmas gift
    'index_christmas_gift' => 'Cadoul de Cr&#259;ciun',
    //ie user
    'index_ie_warn'     => 'Avertisment - Browser Internet Explorer',
    'index_ie_not'      => ' Se pare ca de&#351;i folose&#351;ti Internet Explorer, acest site <b>NU</b> a fost conceput pentru a fi vizualizat cu Internet Explorer &#351;i sunt &#351;anse ca unele func&#355;ii s&#259; nu fie afi&#351;ate corect.',
    'index_ie_suggest'  => ' i&#351;i suger&#259;m  ',
    'index_ie_bhappy'   => 'ca s&#259; ai o navigare c&#226;t mai pl&#259;cut&#259;',
    'index_ie_consider' => ' s&#259; schimbi browserul cu unul din alternative.',
    'index_ie_firefox'  => 'Ob&#355;ine Firefox!',
    'index_ie_get'      => 'Ob&#355;ine un browser mai sigur !',
    ///Latest Torrents
    'index_ltst_name'     => 'Nume:',
    'index_ltst_added'    => 'Added:',
    'index_ltst_size'     => 'Size:',
    'index_ltst_seeder'   => 'Seederi:',
    'index_ltst_leecher'  => 'Leecheri:',
    'index_ltst_uploader' => 'Uploader:',
    //Latest Member
    'index_lmember' => 'Ultimul membru',
    'index_wmember' => 'Bun venit noului nostru membru ',
    //movie of the week
    'index_mow_title'    => 'Filmul S&#259;pt&#259;m&#226;nii',
    'index_mow_type'     => 'Tip',
    'index_mow_name'     => 'Nume',
    'index_mow_snatched' => 'Luat',
    'index_mow_seeder'   => 'Seederi',
    'index_mow_leecher'  => 'Leecheri',
    'index_mow_no'       => 'Nu s-a setat nici un film al s&#259;pt&#259;m&#226;nii!',
    //news
    'index_news_title' => 'Adaug&#259; / Editeaz&#259;',
    'index_news_ed'    => 'Editeaz&#259; &#351;tiri',
    'index_news_del'   => 'Sterge &#351;tiri',
    'index_news_added' => '&#160;-&#160;Ad&#259;ugat de ',
    'index_news_anon'  => 'Anonim',
    'index_news_not'   => 'Nu avem nici o &#351;tire &#238;n prezent :-P',
    'index_news_txt'   => '&#160;-&#160;',
    //torrent freak
    'index_torr_freak' => ' &#350;tiri de pe Torrent Freak',
];
