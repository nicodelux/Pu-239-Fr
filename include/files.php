<?php

function get_file_name($file)
{
    global $site_config;

    $style = get_stylesheet();
    switch ($file) {
        case 'css':
            return "{$site_config['baseurl']}/css/1/css_7261fa94.css";
        case 'chat_css_trans':
            return "{$site_config['baseurl']}/css/1/chat_trans_0edcff2e.css";
        case 'chat_css_uranium':
            return "{$site_config['baseurl']}/css/1/chat_uranium_f462a1a7.css";
        case 'checkport_js':
            return "{$site_config['baseurl']}/js/1/checkport_dd06d98b.js";
        case 'browse_js':
            return "{$site_config['baseurl']}/js/1/browse_09a435c8.js";
        case 'chat_js':
            return "{$site_config['baseurl']}/js/1/chat_f33880b7.js";
        case 'chat_log_js':
            return "{$site_config['baseurl']}/js/1/chat_log_efed3aa0.js";
        case 'index_js':
            return "{$site_config['baseurl']}/js/1/index_c73226cb.js";
        case 'captcha2_js':
            return "{$site_config['baseurl']}/js/1/captcha2_2c3de5ae.js";
        case 'upload_js':
            return "{$site_config['baseurl']}/js/1/upload_10ca99a2.js";
        case 'request_js':
            return "{$site_config['baseurl']}/js/1/request_4bbb71bf.js";
        case 'acp_js':
            return "{$site_config['baseurl']}/js/1/acp_22d19c79.js";
        case 'userdetails_js':
            return "{$site_config['baseurl']}/js/1/userdetails_20514b1d.js";
        case 'details_js':
            return "{$site_config['baseurl']}/js/1/details_7864bc8d.js";
        case 'forums_js':
            return "{$site_config['baseurl']}/js/1/forums_2ff1ac95.js";
        case 'staffpanel_js':
            return "{$site_config['baseurl']}/js/1/staffpanel_801ab346.js";
        case 'js':
            return "{$site_config['baseurl']}/js/1/js_9676b8f5.js";
        default:
            return null;
    }
}