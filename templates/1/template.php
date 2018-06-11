<?php

/**
 * @param string $title
 * @param null   $stdhead
 *
 * @return string
 *
 * @throws Exception
 */
function stdhead($title = '', $stdhead = null)
{
    require_once INCL_DIR . 'bbcode_functions.php';
    global $CURUSER, $site_config, $lang, $free, $querytime, $BLOCKS, $CURBLOCK, $mood, $session;
//  dd($GLOBALS);
    if (!$site_config['site_online']) {
        die('Site is down for maintenance, please check back again later... thanks<br>');
    }
    if (empty($title)) {
        $title = $site_config['site_name'];
    } else {
        $title = $site_config['site_name'] . ' :: ' . htmlsafechars($title);
    }
    $css_incl = '';
    if (!empty($stdhead['css'])) {
        foreach ($stdhead['css'] as $CSS) {
            $css_incl .= "
    <link rel='stylesheet' href='{$CSS}' />";
        }
    }

    $body_class = 'background-16 h-style-9 text-9 skin-2';
    $htmlout    = "<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <title>{$title}</title>
    <link rel='alternate' type='application/rss+xml' title='Latest Torrents' href='{$site_config['baseurl']}/rss.php?torrent_pass={$CURUSER['torrent_pass']}' />
    <link rel='apple-touch-icon' sizes='180x180' href='{$site_config['baseurl']}/apple-touch-icon.png' />
    <link rel='icon' type='image/png' sizes='32x32' href='{$site_config['baseurl']}/favicon-32x32.png' />
    <link rel='icon' type='image/png' sizes='16x16' href='{$site_config['baseurl']}/favicon-16x16.png' />
    <link rel='manifest' href='{$site_config['baseurl']}/manifest.json' />
    <link rel='mask-icon' href='{$site_config['baseurl']}/safari-pinned-tab.svg' color='#5bbad5' />
    <meta name='theme-color' content='#fff'>
    <link rel='stylesheet' href='" . get_file_name('css') . "' />
    {$css_incl}
    <style>#mlike{cursor:pointer;}</style>
    <script>
        function resizeIframe(obj) {
            obj.style.height = 0;
            obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
        }
    </script>
</head>
<body class='{$body_class}'>
    <script>
        var theme = localStorage.getItem('theme');
        if (theme) {
            document.body.className = theme;
        }
    </script>
    <div id='container' class='container'>
        <div class='page-wrapper'>";
    if ($CURUSER) {
        $htmlout .= navbar();
        $htmlout .= "
            <div id='logo' class='logo columns level is-marginless'>
                <div class='column'>
                    <h1>NT4</h1>
                    <p class='description left20'><i><font color='yellow'>Bienvenue sur NT4</font></i></p>
                </div>
            </div>";
        $htmlout .= platform_menu();
        $htmlout .= "
            <div id='base_globelmessage'>
                <div class='top5 bottom5'>
                    <ul class='level-center tags'>";

        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_REPORTS && $BLOCKS['global_staff_report_on']) {
            require_once BLOCK_DIR . 'global/report.php';
        }
        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_UPLOADAPP && $BLOCKS['global_staff_uploadapp_on']) {
            require_once BLOCK_DIR . 'global/uploadapp.php';
        }
        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_HAPPYHOUR && $BLOCKS['global_happyhour_on'] && !XBT_TRACKER) {
            require_once BLOCK_DIR . 'global/happyhour.php';
        }
        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_STAFF_MESSAGE && $BLOCKS['global_staff_warn_on']) {
            require_once BLOCK_DIR . 'global/staffmessages.php';
        }
        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_NEWPM && $BLOCKS['global_message_on']) {
            require_once BLOCK_DIR . 'global/message.php';
        }
        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_DEMOTION && $BLOCKS['global_demotion_on']) {
            require_once BLOCK_DIR . 'global/demotion.php';
        }
        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_FREELEECH && $BLOCKS['global_freeleech_on'] && !XBT_TRACKER) {
            require_once BLOCK_DIR . 'global/freeleech.php';
        }
        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_CRAZYHOUR && $BLOCKS['global_crazyhour_on'] && !XBT_TRACKER) {
            require_once BLOCK_DIR . 'global/crazyhour.php';
        }
        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_BUG_MESSAGE && $BLOCKS['global_bug_message_on']) {
            require_once BLOCK_DIR . 'global/bugmessages.php';
        }
        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_FREELEECH_CONTRIBUTION && $BLOCKS['global_freeleech_contribution_on']) {
            require_once BLOCK_DIR . 'global/freeleech_contribution.php';
        }
        require_once BLOCK_DIR . 'global/lottery.php';

        $htmlout .= '
                    </ul>
                </div>
            </div>';
    }

    $htmlout .= "
        <div id='base_content' class='bg-05'>
            <div class='inner-wrapper bg-04'>";

    $index_array = [
        '/',
        '/index.php',
        '/login.php',
    ];

    if ($CURUSER && !in_array($_SERVER['REQUEST_URI'], $index_array)) {
        $htmlout .= "
                <div class='container is-fluid portlet padding20 bg-00 round10'>
                    <nav class='breadcrumb' aria-label='breadcrumbs'>
                        <ul>
                            " . breadcrumbs() . '
                        </ul>
                    </nav>
                </div>';
    }

    foreach ($site_config['notifications'] as $notif) {
        if (($messages = $session->get($notif)) != false) {
            foreach ($messages as $message) {
                $message = !is_array($message) ? format_comment($message) : "<a href='{$message['link']}'>" . format_comment($message['message']) . '</a>';
                $htmlout .= "
                <div class='notification $notif has-text-centered size_6'>
                    <button class='delete'></button>$message
                </div>";
            }
            $session->unset($notif);
        }
    }

    return $htmlout;
}

/**
 * @param bool $stdfoot
 *
 * @return string
 */
function stdfoot($stdfoot = false)
{
    require_once INCL_DIR . 'bbcode_functions.php';
    global $CURUSER, $site_config, $starttime, $query_stat, $querytime, $lang, $cache, $session;

    $header    = $uptime    = $htmlfoot    = '';
    $debug     = (SQL_DEBUG && !empty($CURUSER['id']) && in_array($CURUSER['id'], $site_config['is_staff']['allowed']) ? 1 : 0);
    $queries   = !empty($query_stat) ? count($query_stat) : 0;
    $seconds   = microtime(true) - $starttime;
    $r_seconds = round($seconds, 5);
    $querytime = $querytime === null ? 0 : $querytime;

    if ($CURUSER['class'] >= UC_STAFF && $debug) {
        if ($_ENV['CACHE_DRIVER'] === 'apcu' && extension_loaded('apcu')) {
            $stats = apcu_cache_info();
            if ($stats) {
                $stats['Hits'] = number_format($stats['num_hits'] / ($stats['num_hits'] + $stats['num_misses']) * 100, 3);
                $header        = "{$lang['gl_stdfoot_querys_apcu1']}{$stats['Hits']}{$lang['gl_stdfoot_querys_mstat4']}" . number_format((100 - $stats['Hits']), 3) . $lang['gl_stdfoot_querys_mstat5'] . number_format($stats['num_entries']) . "{$lang['gl_stdfoot_querys_mstat6']}" . human_filesize($stats['mem_size']);
            }
        } elseif ($_ENV['CACHE_DRIVER'] === 'redis' && extension_loaded('redis')) {
            $client = new \Redis();
            if (!SOCKET) {
                $client->connect($_ENV['REDIS_HOST'], $_ENV['REDIS_PORT']);
            } else {
                $client->connect($_ENV['REDIS_SOCKET']);
            }
            $client->select($_ENV['REDIS_DATABASE']);
            $stats = $client->info();
            if ($stats) {
                $stats['Hits'] = number_format($stats['keyspace_hits'] / ($stats['keyspace_hits'] + $stats['keyspace_misses']) * 100, 3);
                preg_match('/keys=(\d+)/', $stats['db' . $_ENV['REDIS_DATABASE']], $keys);
                $header = "{$lang['gl_stdfoot_querys_redis1']}{$stats['Hits']}{$lang['gl_stdfoot_querys_mstat4']}" . number_format((100 - $stats['Hits']), 3) . $lang['gl_stdfoot_querys_mstat5'] . number_format($keys[1]) . "{$lang['gl_stdfoot_querys_mstat6']}{$stats['used_memory_human']}";
            }
        } elseif ($_ENV['CACHE_DRIVER'] === 'memcached' && extension_loaded('memcached')) {
            $client = new \Memcached();
            if (!count($client->getServerList())) {
                $client->addServer($_ENV['MEMCACHED_HOST'], $_ENV['MEMCACHED_PORT']);
            }
            $stats = $client->getStats();
            $stats = !empty($stats["{$_ENV['MEMCACHED_HOST']}:{$_ENV['MEMCACHED_PORT']}"]) ? $stats["{$_ENV['MEMCACHED_HOST']}:{$_ENV['MEMCACHED_PORT']}"] : null;
            if ($stats && !empty($stats['get_hits']) && !empty($stats['cmd_get'])) {
                $stats['Hits'] = number_format(($stats['get_hits'] / $stats['cmd_get']) * 100, 3);
                $header        = $lang['gl_stdfoot_querys_mstat3'] . $stats['Hits'] . $lang['gl_stdfoot_querys_mstat4'] . number_format((100 - $stats['Hits']), 3) . $lang['gl_stdfoot_querys_mstat5'] . number_format($stats['curr_items']) . "{$lang['gl_stdfoot_querys_mstat6']}" . human_filesize($stats['bytes']);
            }
        } elseif ($_ENV['CACHE_DRIVER'] === 'files') {
            $header = "{$lang['gl_stdfoot_querys_fly1']}{$_ENV['FILES_PATH']} {$lang['gl_stdfoot_querys_fly2']}" . GetDirectorySize($_ENV['FILES_PATH']);
        } elseif ($_ENV['CACHE_DRIVER'] === 'couchbase') {
            $header = $lang['gl_stdfoot_querys_cbase'];
        }

        if (!empty($query_stat)) {
            $htmlfoot .= "
                <div class='container is-fluid portlet'>
                    <a id='queries-hash'></a>
                    <fieldset id='queries' class='header'>
                        <legend class='flipper has-text-primary'><i class='fa icon-up-open size_3' aria-hidden='true'></i>{$lang['gl_stdfoot_querys']}</legend>
                        <div class='has-text-centered'>
                            <table class='table table-bordered table-striped bottom10'>
                                <thead>
                                    <tr>
                                        <th class='w-10'>{$lang['gl_stdfoot_id']}</th>
                                        <th class='w-10'>{$lang['gl_stdfoot_qt']}</th>
                                        <th>{$lang['gl_stdfoot_qs']}</th>
                                    </tr>
                                </thead>
                                <tbody>";
            foreach ($query_stat as $key => $value) {
                $querytime += $value['seconds'];
                $htmlfoot .= '
                                    <tr>
                                        <td>' . ($key + 1) . '</td>
                                        <td>' . ($value['seconds'] > 0.01 ? "<span class='has-text-red' title='{$lang['gl_stdfoot_ysoq']}'>" . $value['seconds'] . '</span>' : "<span class='has-text-green' title='{$lang['gl_stdfoot_qg']}'>" . $value['seconds'] . '</span>') . "</td>
                                        <td>
                                            <div class='text-justify pre'>" . format_comment($value['query']) . '</div>
                                        </td>
                                    </tr>';
            }

            $htmlfoot .= '
                                </tbody>
                            </table>
                        </div>
                    </fieldset>
                </div>';
        }
        $uptime = $cache->get('uptime');
        if ($uptime === false || is_null($uptime)) {
            $uptime = `uptime`;
            $cache->set('uptime', $uptime, 25);
        }
    }
    $htmlfoot .= '
                </div>
            </div>';

    if ($CURUSER) {
        $htmlfoot .= "
            <div class='container site-debug bg-05 round10 top20 bottom20'>
                <div class='level bordered bg-04'>
                    <div class='size_4 top10 bottom10'>
                        <p class='is-marginless'>{$lang['gl_stdfoot_querys_page']} " . mksize(memory_get_peak_usage()) . " in $r_seconds {$lang['gl_stdfoot_querys_seconds']}</p>
                        <p class='is-marginless'>{$lang['gl_stdfoot_querys_server']} $queries {$lang['gl_stdfoot_querys_time']}" . plural($queries) . '</p>
                        ' . ($debug ? "<p class='is-marginless'>$header</p><p class='is-marginless'>{$lang['gl_stdfoot_uptime']} $uptime</p>" : '') . "
                    </div>
                    <div class='size_4 top10 bottom10'>
                        <p class='is-marginless'>© Copyright 2018 NT4 V1.0.2 Par Nico</p>
                        <p class='is-marginless'>{$lang['gl_stdfoot_using']}{$lang['gl_stdfoot_using1']} " . show_php_version() . "</p>
                    </div>
                </div>
            </div>
            <div id='control_panel'>
                <a href='#' id='control_label'></a>
            </div>
        </div>";
    }
    $htmlfoot .= "
    </div>
    <a href='#' class='back-to-top'>
        <i class='icon-angle-circled-up' style='font-size:48px'></i>
    </a>
    <script>
        var cookie_prefix = '{$site_config['cookie_prefix']}';
        var cookie_path = '{$site_config['cookie_path']}';
        var cookie_lifetime = '{$site_config['cookie_lifetime']}';
        var cookie_domain = '{$site_config['cookie_domain']}';
        var csrf_token = '" . $session->get('csrf_token') . "';
        var x = document.getElementsByClassName('flipper');
        var i;
        for (i = 0; i < x.length; i++) {
            var id = x[i].parentNode.id;
            var el = document.getElementById(id);
            if (id && localStorage[id] === 'closed') {
                el.classList.add('no-margin');
                el.classList.add('no-padding');
                var nextSibling = x[i].nextSibling, child;
                while (nextSibling && nextSibling.nodeType !== 1) {
                    nextSibling = nextSibling.nextSibling;
                }
                nextSibling.style.display = 'none';
                child = x[i].children[0];
                child.classList.add('icon-down-open');
                child.classList.remove('icon-up-open');
            } else if (id && localStorage[id] === 'open') {
                var nextSibling = x[i].nextSibling, child;
                while (nextSibling && nextSibling.nodeType !== 1) {
                    nextSibling = nextSibling.nextSibling;
                }
                nextSibling.style.display = 'block';
                child = x[i].children[0];
                child.classList.add('icon-up-open');
                child.classList.remove('icon-down-open');
            } else {
                if (el && document.getElementById(el.children[0]) && document.getElementById(el.children[0].children[0]) && el.children[0].children[0].className === 'fa icon-down-open') {
                    el.classList.add('no-margin');
                    el.classList.add('no-padding');
                    var nextSibling = x[i].nextSibling;
                    while (nextSibling && nextSibling.nodeType !== 1) {
                        nextSibling = nextSibling.nextSibling;
                    }
                    nextSibling.style.display = 'none';
                }
            }
        }
    </script>";

    $htmlfoot .= "
    <script src='" . get_file_name('js') . "'></script>";

    if (!empty($stdfoot['js'])) {
        foreach ($stdfoot['js'] as $JS) {
            $htmlfoot .= "
    <script src='{$JS}'></script>";
        }
    }

    $htmlfoot .= '
</body>
</html>';

    $session->close();

    return $htmlfoot;
}

/**
 * @param      $heading
 * @param      $text
 * @param null $class
 *
 * @return string
 */
function stdmsg($heading, $text, $class = null)
{
    require_once INCL_DIR . 'html_functions.php';

    $htmlout = '';
    if ($heading) {
        $htmlout .= "
                <h2>$heading</h2>";
    }
    $htmlout .= "
                <p>$text</p>";

    return main_div($htmlout, "$class bottom20");
}

/**
 * @return string
 */
function StatusBar()
{
    global $CURUSER;
    if (!$CURUSER) {
        return '';
    }
    $StatusBar = $clock = '';
    $StatusBar .= "
                    <div id='base_usermenu' class='tooltipper-ajax right10 level-item'>
                        <span id='clock' class='has-text-white right10'>{$clock}</span>
                        " . format_username($CURUSER['id'], true, false) . '
                    </div>';

    return $StatusBar;
}

/**
 * @return string
 *
 * @throws Exception
 */
function navbar()
{
    global $site_config, $CURUSER, $lang, $fluent, $cache;

    $navbar = $panel = $user_panel = $settings_panel = $stats_panel = $other_panel = '';

    if ($CURUSER['class'] >= UC_STAFF) {
        $staff_panel = $cache->get('staff_panels_' . $CURUSER['class']);
        if ($staff_panel === false || is_null($staff_panel)) {
            $staff_panel = $fluent->from('staffpanel')
                ->where('navbar = 1')
                ->where('av_class <= ?', $CURUSER['class'])
                ->orderBy('page_name')
                ->fetchAll();
            $cache->set('staff_panels_' . $CURUSER['class'], $staff_panel, 0);
        }

        if ($staff_panel) {
            foreach ($staff_panel as $key => $value) {
                if ($value['av_class'] <= $CURUSER['class'] && $value['type'] === 'user') {
                    $user_panel .= "
                        <li class='iss_hidden'>
                            <a href='{$site_config['baseurl']}/" . htmlsafechars($value['file_name']) . "'>" . htmlsafechars($value['page_name']) . '</a>
                        </li>';
                } elseif ($value['av_class'] <= $CURUSER['class'] && $value['type'] === 'settings') {
                    $settings_panel .= "
                        <li class='iss_hidden'>
                            <a href='{$site_config['baseurl']}/" . htmlsafechars($value['file_name']) . "'>" . htmlsafechars($value['page_name']) . '</a>
                        </li>';
                } elseif ($value['av_class'] <= $CURUSER['class'] && $value['type'] === 'other') {
                    $other_panel .= "
                        <li class='iss_hidden'>
                            <a href='{$site_config['baseurl']}/" . htmlsafechars($value['file_name']) . "'>" . htmlsafechars($value['page_name']) . '</a>
                        </li>';
                }
            }

            if (!empty($user_panel)) {
                $panel .= "
                    <li class='clickable'>
                        <a id='staff_users' href='#'>[Membre]</a>
                        <ul class='ddFade ddFadeSlow'>
                            <li class='iss_hidden'>
                                <a href='{$site_config['baseurl']}/staffpanel.php'>AdminCP</a>
                            </li>
                            $user_panel
                        </ul>
                   </li>";
            }
            if (!empty($settings_panel)) {
                $panel .= "
                   <li class='clickable'>
                        <a id='staff_settings' href='#'>[Paramètres]</a>
                        <ul class='ddFade ddFadeSlow'>
                            <li class='iss_hidden'>
                                <a href='{$site_config['baseurl']}/staffpanel.php'>AdminCP</a>
                            </li>
                            $settings_panel
                        </ul>
                    </li>";
            }
            if (!empty($other_panel)) {
                $panel .= "
                    <li class='clickable'>
                        <a id='staff_other' href='#'>[Autres]</a>
                        <ul class='ddFade ddFadeSlow'>
                            <li class='iss_hidden'>
                                <a href='{$site_config['baseurl']}/staffpanel.php'>AdminCP</a>
                            </li>";
                if ($CURUSER['class'] === UC_MAX) {
                    $panel .= "
                            <li class='iss_hidden'>
                                <a href='{$site_config['baseurl']}/view_sql.php'>Adminer</a>
                            </li>";
                }
                $panel .= "
                            $other_panel
                        </ul>
                   </li>";
            }
        }
    }

    if ($CURUSER) {
        $navbar .= "
    <div class='spacer'>
        <header id='navbar' class='container'>
            <div class='contained'>
                <div class='nav_container'>
                    <div id='hamburger'><i class='icon-menu size_6 has-text-white' aria-hidden='true'></i></div>
                    <div id='close' class='top10 right10'><i class='icon-cancel size_7 has-text-white' aria-hidden='true'></i></div>
                    <div id='menuWrapper'>
                        <ul class='level'>
                            <li>
                                <a href='{$site_config['baseurl']}' class='is-flex'>
                                    <i class='icon-home size_6'></i>
                                    <span class='home'>{$site_config['site_name']}</span>
                                </a>
                            </li>
                            <li id='torrents' class='clickable'>
                                <a href='#'>{$lang['gl_torrent']}</a>
                                <ul class='ddFade ddFadeSlow'>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/browse.php'>Parcourir {$lang['gl_torrents']}</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/catalog.php'>Catalogue</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/needseed.php?needed=seeders'><span class='is-danger'>{$lang['gl_nseeds']}</span></a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/browse.php?today=1'>{$lang['gl_newtor']}</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/offers.php'>{$lang['gl_offers']}</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/requests.php'>{$lang['gl_requests']}</a></li>
                                </ul>
                            </li>
							<li id='torrents' class='clickable'>
                                <a href='#'>Envoyer un Torrent</a>
                                <ul class='ddFade ddFadeSlow'>
									" . ($CURUSER['class'] <= UC_POWER_USER ? "<li class='iss_hidden'><a href='{$site_config['baseurl']}/uploadapp.php'>{$lang['gl_uapp']}</a></li>" : "<li class='iss_hidden'><a href='{$site_config['baseurl']}/upload.php'>{$lang['gl_upload']}</a></li>") . "
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/bitbucket.php'>Uploadeur d'images</a></li>
									<li class='iss_hidden'><a href='{$site_config['baseurl']}/mytorrents.php'>Mes&nbsp;torrents&nbsp;envoyés</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/requests.php'>{$lang['gl_requests']}</a></li>
                                </ul>
                            </li>
                            <li id='general' class='clickable'>
                                <a href='#'>{$lang['gl_general']}</a>
                                <ul class='ddFade ddFadeSlow'>";
        if ($site_config['bucket_allowed'] === 1) {
            $navbar .= "
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/bitbucket.php'>{$lang['gl_bitbucket']}</a></li>";
        }
        $navbar .= "
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/faq.php'>{$lang['gl_faq']}</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/chat.php'>{$lang['gl_irc']}</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/mybonus.php'>Shop €</a></li>
                                    <li class='iss_hidden'><a href='#' onclick='radio();'>{$lang['gl_radio']}</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/getrss.php'>RSS</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/rules.php'>{$lang['gl_rules']}</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/announcement.php'>{$lang['gl_announcements']}</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/staff.php'>{$lang['gl_staff']}</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/topten.php'>{$lang['gl_stats']}</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/rsstfreak.php'>{$lang['gl_tfreak']}</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/wiki.php'>{$lang['gl_wiki']}</a></li>
                                </ul>
                            </li>
                            <li id='games' class='clickable'>
                                <a href='#'>Jeux NT4</a>
                                <ul class='ddFade ddFadeSlow'>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/arcade.php'>Arcade</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/games.php'>Casino</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/lottery.php'>Loterie</a></li>
                                </ul>
                            </li>
                            <li><a href='{$site_config['baseurl']}/donate.php'>{$lang['gl_donate']}</a></li>
                            <li id='user' class='clickable'>
                                <a href='#'>Membre</a>
                                <ul class='ddFade ddFadeSlow'>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/bookmarks.php'>Marque-pages</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/friends.php'>{$lang['gl_friends']}</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/pm_system.php'>{$lang['gl_pms']}</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/users.php'>Rechercher un membre</a></li>
									<li class='iss_hidden'><a href='{$site_config['baseurl']}/user_blocks.php'>Blocs utilisateur</a></li>
									<li class='iss_hidden'><a href='{$site_config['baseurl']}/achievementhistory.php?id={$CURUSER['id']}'>Trophées</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/usercp.php?action=security'>Changer de mot de passe</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/usercp.php?action=default'>Configuration du profil</a></li>
                                </ul>
                            </li>
                            <li>
                                <a href='#'>{$lang['gl_forums']}</a>
                                <ul class='ddFade ddFadeSlow'>
                                    <li class='iss_hidden'><a href='http://pu-239.pw'>{$lang['gl_tforums']}</a></li>
                                    <li class='iss_hidden'><a href='{$site_config['baseurl']}/forums.php'>{$lang['gl_forums']}</a></li>
                                </ul>
                            </li>
                            <li>" . ($CURUSER['class'] < UC_STAFF ? "<a href='{$site_config['baseurl']}/bugs.php?action=add'>{$lang['gl_breport']}</a>" : "<a href='{$site_config['baseurl']}/bugs.php?action=bugs'>[Bugs]</a>") . '</li>
                            <li>' . ($CURUSER['class'] < UC_STAFF ? "<a href='{$site_config['baseurl']}/contactstaff.php'>{$lang['gl_cstaff']}</a>" : "<a href='{$site_config['baseurl']}/staffbox.php'>[Messages]</a>") . "</li>
                            $panel
                            <li>
                                <a href='{$site_config['baseurl']}/logout.php' class='is-flex'>
                                    <i class='icon-logout size_6' aria-hidden='true'></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>
    </div>";
    }

    return $navbar;
}

/**
 * @return string
 */
function platform_menu()
{
    global $site_config;

    $menu = "
        <div id='platform-menu' class='container platform-menu'>
            <div class='platform-wrapper level'>
                <ul class='level-left'>" . (!$site_config['in_production'] ? "
                    <li class='left10 has-text-primary'>NT4 v1.0.2</li>" : '') . "
                </ul>
                <ul class='level-right'>" .
        StatusBar() . '
                </ul>
            </div>
        </div>';

    return $menu;
}
