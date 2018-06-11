<?php

global $site_config, $lang;

$HTMLOUT .= "
    <a id='advertise-hash'></a>
    <fieldset id='advertise' class='header'>
        <legend class='flipper has-text-primary'><i class='fa icon-up-open size_3' aria-hidden='true'></i>{$lang['index_pu-239_git']}</legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00 has-text-centered'>
                <a href='{$site_config['anonymizer_url']}https://github.com/darkalchemy/Pu-239'>
                    <img src='{$site_config['pic_baseurl']}logo.png' alt='Pu-239' class='tooltipper mw-100' title='Pu-239'>
                </a>
            </div>
        </div>
    </fieldset>";
