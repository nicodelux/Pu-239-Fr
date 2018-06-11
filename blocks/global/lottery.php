<?php

global $CURUSER, $site_config, $fluent, $cache;

if ($CURUSER) {
    $lottery_info = $cache->get('lottery_info_');
    if ($lottery_info === false || is_null($lottery_info)) {
        $lottery_info = $fluent->from('lottery_config')
            ->fetchPairs('name', 'value');

        $cache->set('lottery_info_', $lottery_info, 86400);
    }

    if ($lottery_info['enable']) {
        $htmlout .= "
    <li>
        <a href='{$site_config['baseurl']}/lottery.php'>
            <b class='button tag is-success is-small dt-tooltipper-small' data-tooltip-content='#lottery_tooltip'>
                Lottery in Progress
            </b>
            <div class='tooltip_templates'>
                <span id='lottery_tooltip'>
                    <div>
                        <div class='size_4 has-text-centered has-text-success bottom10'>Lottery Info</div>
                        <div class='level is-marginless'>
                            <span>Started at: </span><span>" . get_date($lottery_info['start_date'], 'LONG') . "</span>
                        </div>
                        <div class='level is-marginless'>
                            <span>Ends at:&#160;&#160;&#160;&#160;&#160;&#160;</span><span>" . get_date($lottery_info['end_date'], 'LONG') . "</span>
                        </div>
                        <div class='level is-marginless'>
                            <span>Remaining: </span><span>" . mkprettytime($lottery_info['end_date'] - TIME_NOW) . '</span>
                        </div>
                    </div>
                </span>
            </div>
        </a>
    </li>';
    }
}
