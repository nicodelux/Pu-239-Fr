<?php

global $site_config;

$lang = [
    //index
    'index_announce'        => 'Dernières nouvelles',
    'index_latest'          => 'Derniers torrents',
    'index_latest_comments' => 'Dernier commentaires',
    'index_ajaxchat'        => 'Nt4 Shoutbox',
    'index_active_irc'      => 'Utilisateurs irc actif',
    'index_active'          => 'Utilisateurs actifs',
    'index_active24'        => 'Utilisateurs actifs sur les dernières 24hrs',
    'index_most24'          => 'Le plus visité en 24 heures était',
    'index_member24'        => "Membres",
    'index_noactive'        => 'Désolé - Aucun utilisateur actif actuellement',
    'index_disclaimer'      => 'Avertissement',
    'index_donations'       => 'Donation',
    'index_serverload'      => 'Charge du erveur',
    'index_pu-239'          => 'Pu-239 Forum',
    'index_pu-239_git'      => 'Pu-239 Repo',
    'index_serverscene'     => 'Server Scene',
    'index_birthday'        => 'Anniversaire des membres',
    'index_trivia'          => 'Jeu du Trivia',
    //News
    'news_title'  => 'Nouvelles récentes',
    'news_link'   => 'Page des nouvelles',
    'news_edit'   => 'Edition',
    'news_delete' => 'Supprimer',
    //latest torrents
    'latesttorrents_title'       => $site_config['latest_torrents_limit'] . ' derniers torrents',
    'latesttorrents_type'        => 'Type',
    'latesttorrents_name'        => 'Nom',
    'latesttorrents_seeders'     => 'Seedeurs',
    'latesttorrents_leechers'    => 'Leecheurs',
    'latesttorrents_no_torrents' => 'Aucun torrent trouvé',
    // last 5
    'last5torrents_title'       => '5 Derniers torrents',
    'last5torrents_type'        => 'Type',
    'last5torrents_name'        => 'Nom',
    'last5torrents_seeders'     => 'Seedeurs',
    'last5torrents_leechers'    => 'Leecheurs',
    'last5torrents_no_torrents' => 'Aucun torrent trouvé',
    // top 5
    'top5torrents_title'       => ' Top 5 torrents',
    'top5torrents_type'        => 'Type',
    'top5torrents_name'        => 'Nom',
    'top5torrents_seeders'     => 'Seedeurs',
    'top5torrents_leechers'    => 'Leecheurs',
    'top5torrents_no_torrents' => 'Aucun torrent trouvé',
    //Change log
    'clog_title'  => 'Change log',
    'clog_link'   => 'Log Page',
    'clog_edit'   => 'Editer',
    'clog_delete' => 'Supprimer',
    //latest forum posts
    'latestposts_title'       => $site_config['latest_posts_limit'] . ' Latest Forum Posts',
    'latestposts_topic_title' => 'Sujet',
    'latestposts_replies'     => 'Réponses',
    'latestposts_views'       => 'Vu',
    'latestposts_last_post'   => 'Dernier&#160;message',
    'latestposts_posted_at'   => 'Posté&#160;le',
    'latestposts_no_posts'    => 'Aucun message trouvé',
    //Stats
    'index_stats_title'         => 'Statistiques',
    'index_stats_regged'        => 'Membre enregistré',
    'index_stats_online'        => 'Membre en ligne',
    'index_stats_uncon'         => 'Membre non comfirmer',
    'index_stats_donor'         => 'Donateur',
    'index_stats_topics'        => 'Sujet forum',
    'index_stats_torrents'      => 'Torrents',
    'index_stats_posts'         => 'Messages forum',
    'index_stats_newtor'        => 'Torrent posté aujourd\'hui',
    'index_stats_newtor_month'  => 'Torrents posté ce mois',
    'index_stats_peers'         => 'Pairs',
    'index_stats_unconpeer'     => 'Pairs non connectable',
    'index_stats_seeders'       => 'Seedeurs',
    'index_stats_unconratio'    => 'Non connectable ratio (%)',
    'index_stats_leechers'      => 'Leecheurs',
    'index_stats_slratio'       => 'Seedeur/leecheur ratio (%)',
    'index_stats_gender_na'     => 'Sexe non renseigner',
    'index_stats_gender_male'   => 'Hommes',
    'index_stats_gender_female' => 'Femmes',
    'index_stats_powerusers'    => 'Super utilisateur',
    'index_stats_banned'        => 'Désactivé',
    'index_stats_uploaders'     => 'Uploadeurs',
    'index_stats_moderators'    => 'Moderateurs',
    'index_stats_admin'         => 'Administrateurs',
    'index_stats_sysops'        => 'Sysops',
    //disclaimer
    'foot_disclaimer' => "Attention: Aucun des fichiers présentés ici n'est hébergé sur ce serveur. Les liens sont fournis uniquement par les utilisateurs de ce site.
L'administrateur de ce site (% s) ne peut être tenu pour responsable de ce que ses utilisateurs publient, ni de toute autre action de ses utilisateurs.
Vous ne pouvez pas utiliser ce site pour distribuer ou télécharger du matériel si vous n'avez pas les droits légaux de le faire.
Il est de votre responsabilité d'adhérer à ces conditions.",
    //last24
    'index_last24_nousers' => 'Aucun&#160;membre&#160;actif&#160;sur&#160;les&#160;dernières&#160;15&#160;minutes.',
    'index_last24_list'    => '&#160;-&#160;La&#160;liste&#160;est&#160;mise&#160;à&#160;jour&#160;une&#160;fois&#160;par&#160;heure',
    'index_last24_during'  => ' ont visité NT4 au cours des dernières 24 heures',
    'index_last24_most'    => 'Le plus visité en 24 heures était ',
    'index_last24_on'      => ' on ',
    //global show hide
    'index_hide_show'  => '[Cacher/Afficher]',
    'index_click_more' => 'Cliquez pour plus d\'informations',
    //irc users
    'index_irc_days'    => 'jours',
    'index_irc_hrs'     => 'hrs',
    'index_irc_min'     => 'minutes',
    'index_irc_nousers' => 'Aucun membre actif sur les 15 dernières minutes.',
    //birthday users
    'index_birthday_no' => 'Aucun membre ne fête son anniversaire aujourd\'hui.',
    //active users
    'index_active_users_no' => 'Aucun membre actif sur les 15 dernières minutes.',
    //advertise
    'index_advertise_t' => 'NT4',
    //announcement
    'index_ann_title' => 'Announcement',
    'index_ann_click' => 'Cliquez ',
    'index_ann_here'  => 'içi',
    'index_ann_clear' => ' pour effacer cette nouvelle.',
    //forum_posts
    'index_fposts_anonymous' => 'Anonyme',
    'index_fposts_unknow'    => 'Inconnu',
    'index_fposts_system'    => 'System',
    'index_fposts_sticky'    => 'Epinlglé',
    'index_fposts_stickyt'   => 'Sujet épinglé',
    'index_fposts_locked'    => 'Fermé',
    'index_fposts_lockedt'   => 'Sujet fermé',
    'index_fposts_in'        => 'dans ',
    //Christmas gift
    'index_christmas_gift' => 'Christmas Gift',
    //ie user
    'index_ie_warn'     => 'Warning - Internet Explorer Browser',
    'index_ie_not'      => ' It appears as though you are running Internet Explorer, this site was <b>NOT</b> intended to be viewed with internet explorer and chances are it will not look right and may not even function correctly.',
    'index_ie_suggest'  => ' suggests that you ',
    'index_ie_bhappy'   => 'browse happy',
    'index_ie_consider' => ' and consider switching to one of the many better alternatives.',
    'index_ie_firefox'  => 'Get Firefox!',
    'index_ie_get'      => 'Get a SAFER browser !',
    ///Latest Torrents
    'index_ltst_name'     => 'Nom:',
    'index_ltst_added'    => 'Ajouté le:',
    'index_ltst_size'     => 'Taille:',
    'index_ltst_seeder'   => 'Seedeurs:',
    'index_ltst_leecher'  => 'Leecheurs:',
    'index_ltst_uploader' => 'Uploadeur:',
    //Latest Member
    'index_lmember' => 'Dernier membre',
    'index_wmember' => 'Bienvenue à toi ',
    //movie of the week
    'index_mow_title'    => 'Films de la semaine',
    'index_mow_type'     => 'Type',
    'index_mow_name'     => 'Nom',
    'index_mow_snatched' => 'A été pris',
    'index_mow_seeder'   => 'Seedeurs',
    'index_mow_leecher'  => 'Leecheurs',
    'index_mow_no'       => 'Aucun film trouvé!',
    //news
    'index_news_title' => 'Ajouté / Editer',
    'index_news_ed'    => 'Editer news',
    'index_news_del'   => 'Supprimer news',
    'index_news_added' => '&#160;-&#160;Ajouté par ',
    'index_news_anon'  => 'Anonymous',
    'index_news_not'   => 'Bienvenue sur notre tout nouveau site!',
    'index_news_txt'   => '&#160;-&#160;',
    //torrent freak
    'index_torr_freak' => ' Nouvelle de Torrent Freak',
];
