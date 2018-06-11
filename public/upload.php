<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once CACHE_DIR . 'subs.php';
check_user_status();
global $CURUSER, $site_config;
$lang    = array_merge(load_language('global'), load_language('upload'));
$stdfoot = [
    'js' => [
        get_file_name('upload_js'),
    ],
];
$HTMLOUT = $offers = $subs_list = $request = $descr = '';
if ($CURUSER['class'] < UC_UPLOADER || $CURUSER['uploadpos'] === 0 || $CURUSER['uploadpos'] > 1 || $CURUSER['suspended'] === 'yes') {
    stderr($lang['upload_sorry'], $lang['upload_no_auth']);
}
$res_request = sql_query('SELECT id, request_name FROM requests WHERE filled_by_user_id = 0 ORDER BY request_name ASC') or sqlerr(__FILE__, __LINE__);
$request     = '
    <tr>
    <td><span>Requête:</span></td>
    <td>
        <select name="request">
        <option class="body" value="0"> Requêtes </option>';
if ($res_request) {
    while ($arr_request = mysqli_fetch_assoc($res_request)) {
        $request .= '<option class="body" value="' . (int) $arr_request['id'] . '">' . htmlsafechars($arr_request['request_name']) . '</option>';
    }
} else {
    $request .= '<option class="body" value="0">Aucune requête</option>';
}
$request .= '</select><span>&nbsp&nbspSi vous remplissez une requête, veuillez la sélectionner ici afin que le membre intéressé puisse être notifié.</span></td>
    </tr>';
//=== offers list if member has made any offers
$res_offer = sql_query('SELECT id, offer_name
                        FROM offers
                        WHERE offered_by_user_id = ' . sqlesc($CURUSER['id']) . " AND status = 'approved'
                        ORDER BY offer_name ASC") or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($res_offer) > 0) {
    $offers = '
    <tr>
    <td><span>My Offers:</span></td>
    <td>
    <select name="offer">
    <option class="body" value="0">My Offers</option>';
    $message = '<option class="body" value="0">Your have no approved offers yet</option>';
    while ($arr_offer = mysqli_fetch_assoc($res_offer)) {
        $offers .= '<option class="body" value="' . (int) $arr_offer['id'] . '">' . htmlsafechars($arr_offer['offer_name']) . '</option>';
    }
    $offers .= '</select> If you are uploading one of your offers, please select it here so interested members will be notified.</td>
    </tr>';
}
$HTMLOUT .= "
    <form id='upload_form' name='upload_form' enctype='multipart/form-data' action='./takeupload.php' method='post'>
    <input type='hidden' name='MAX_FILE_SIZE' value='{$site_config['max_torrent_size']}' />
    <h1 class='has-text-centered'>Envoyer un torrent</h1>
    <p class='top10 has-text-centered'>L'adresse de l'announce est:<br><input type='text' class='has-text-centered w-100 top10' readonly='readonly' value='{$site_config['announce_urls'][0]}' onclick='select()' /></p>";
$HTMLOUT .= "<table class='table table-bordered table-striped top20 bottom20'>
    <tr>
    <td class='rowhead'>{$lang['upload_imdb_url']}</td>
    <td><input type='text' name='url' class='w-100' /><br>{$lang['upload_imdb_tfi']}{$lang['upload_imdb_rfmo']}</td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_isbn']}</td>
    <td><input type='text' name='isbn' class='w-100' /><br>{$lang['upload_isbn_details']}</td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_poster']}</td>
    <td><input type='text' name='poster' class='w-100' /><br>{$lang['upload_poster1']}</td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_youtube']}</td>
    <td><input type='text' name='youtube' class='w-100' /><br>({$lang['upload_youtube_info']})</td>
    </tr>
    <tr>
    <td class='rowhead'><b>{$lang['upload_bitbucket']}</b></td>
    <td>
    <iframe src='imgup.html'></iframe>
    <br>{$lang['upload_bitbucket_1']}
    </td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_torrent']}</td>
    <td>
        <input type='file' name='file' id='torrent' onchange='getname()' class='inputfile' />
    </td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_name']}</td>
    <td><input type='text' id='name' name='name' class='w-100' /><br>({$lang['upload_filename']})</td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_tags']}</td>
    <td><input type='text' name='tags' class='w-100' /><br>({$lang['upload_tag_info']})</td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_small_description']}</td>
    <td><input type='text' name='description' class='w-100' maxlength='120' /><br>({$lang['upload_small_descr']})</td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_nfo']}</td>
    <td><input type='file' name='nfo' /><br>  ({$lang['upload_nfo_info']})</td>
    </tr>
    <tr>
    <td class='rowhead'>{$lang['upload_description']}</td>
    <td>" . BBcode() . "
    <br>({$lang['upload_html_bbcode']})</td>
    </tr>";
$s    = "<select name='type'>\n<option value='0'>({$lang['upload_choose_one']})</option>\n";
$cats = genrelist();
foreach ($cats as $row) {
    $s .= "<option value='" . (int) $row['id'] . "'>" . htmlsafechars($row['name']) . "</option>\n";
}
$s       .= "</select>\n";
$HTMLOUT .= "<tr>
    <td class='rowhead'>{$lang['upload_type']}</td>
    <td>$s</td>
    </tr>";
$HTMLOUT   .= $offers;
$HTMLOUT   .= $request;
$subs_list .= "
        <div class='level-center'>";
foreach ($subs as $s) {
    $subs_list .= "
            <div class='w-15 margin10 tooltipper bordered level-center' title='" . htmlsafechars($s['name']) . "'>
                <span class='has-text-centered'>
                    <input name='subs[]' type='checkbox' value='{$s['id']}' />
                    <image class='sub_flag' src='{$s['pic']}' alt='" . htmlsafechars($s['name']) . "' />
                </span>
                <span class='has-text-centered'>" . htmlsafechars($s['name']) . '</span>
            </div>';
}
$subs_list .= '
        </div>';

$HTMLOUT .= tr('Subtitiles', $subs_list, 1);
$rg = "<select name='release_group'>\n<option value='none'>Aucun</option>\n<option value='p2p'>P2P</option>\n<option value='scene'>Scene</option>\n</select>\n";
$HTMLOUT .= tr('Release Type', $rg, 1);
$HTMLOUT .= tr("{$lang['upload_anonymous']}", "<div class='flex'><input type='checkbox' name='uplver' value='yes' /><span>  {$lang['upload_anonymous1']}</span></div>", 1);
if ($CURUSER['class'] === UC_MAX) {
    $HTMLOUT .= tr("{$lang['upload_comment']}", "<div class='flex'><input type='checkbox' name='allow_commentd' value='yes' /><span>  {$lang['upload_discom1']}</span></div>", 1);
}
$HTMLOUT .= tr('Strip ASCII', "<div class='flex'><input type='checkbox' name='strip' value='strip' /><span><a href='http://en.wikipedia.org/wiki/ASCII_art' target='_blank'>  C'est quoi ?</a></span></div>", 1);
if ($CURUSER['class'] >= UC_UPLOADER && !XBT_TRACKER) {
    $HTMLOUT .= "<tr>
    <td class='rowhead'>Freeleech (Staus Uploadeur)</td>
    <td>
    <select name='free_length'>
    <option value='0'>Pas de freeleech</option>
    <option value='42'>Gratuit pour 1 jour</option>
    <option value='1'>Gratuit pour 1 semaine</option>
    <option value='2'>Gratuit pour 2 semaines</option>
    <option value='4'>Gratuit pour 4 semaines</option>
    <option value='8'>Gratuit pour 8 semaines</option>
    <option value='255'>Illimité</option>
    </select></td>
    </tr>";
    $HTMLOUT .= "<tr>
    <td class='rowhead'>Silverleech (Staus Uploadeur)</td>
    <td>
    <select name='half_length'>
    <option value='0'>Pas de demi freeleech</option>
    <option value='42'>Demi téléchargement pour 1 jour</option>
    <option value='1'>Demi téléchargement pour 1 semaine</option>
    <option value='2'>Demi téléchargement pour 2 semaines</option>
    <option value='4'>Demi téléchargement pour 4 semaines</option>
    <option value='8'>Demi téléchargement pour 8 semaines</option>
    <option value='255'>Illimité</option>
    </select></td>
    </tr>";
}
if (XBT_TRACKER) {
    $HTMLOUT .= tr('Freeleech', "<div class='flex'><input type='checkbox' name='freetorrent' value='1' /><span>Check this to make this torrent freeleech</span></div>", 1);
}

$genres = [
    'Films',
    'Musiques',
    'Jeux',
    'Applications',
];

$HTMLOUT .= "
    <tr>
        <td class='rowhead'><b>Genre</b></td>
        <td>
            <div class='flex-grid'>";

for ($x = 0; $x < count($genres); ++$x) {
    $HTMLOUT .= "
                <div class='flex_cell_5'>
                    <input type='radio' value='" . strtolower($genres[$x]) . "' name='genre' />
                    <span>{$genres[$x]}</span>
                </div>";
}

$HTMLOUT .= "
                <div class='flex_cell_5'>
                    <input type='radio' name='genre' value='' checked />
                    <span>Aucun</span>
                </div>
            </div>
            <label>
            <input type='hidden' class='Depends on genre being movie or genre being music' /></label>
            <div class='flex-grid'>";

$movie = [
    'Action',
	'Adulte',
	'Animations',
	'Aventure',
	'Biographie',
    'Comédie',
	'Catastrophe',
	'Documentaire',
	'Drame',
	'Espionnage',
	'Fantastique',
	'Famille',
	'Guerre',
	'Histoire',
    'Horreur',
	'Musical',
	'Parodie',
	'Policier',
	'Romance',
	'Sci-fi',
	'Suspense',
	'Thriller',
	'Western',
];
for ($x = 0; $x < count($movie); ++$x) {
    $HTMLOUT .= "
                <label>
                    <input type='checkbox' value='{$movie[$x]}' name='{movie[]}' class='DEPENDS ON genre BEING movie' />
                    <span>{$movie[$x]}</span>
                </label>";
}
$music = [
	'BO films',
	'Classique',	
    'Commercial',	
	'Electro',
    'House',
	'Jazz',
	'Latino', 
	'Lounge',
	'Métal',
	'Métal Prog',
	'Minimal',,
	'New Age',
	'New Wave',
	'Opéra',
	'Oriental',
	'Pop',
	'Punk',
	'Ragga',
	'Raï',
	'Rap, Hip-Hop',
	'Reggae',
	'Retro',
	'Rock',
	'Rock N roll',
	'Rythm\'n blues',
	'Salsa',
	'Soul',
	'Synthwave',
	'Swing',
    'Techno',
	'Traditionnel',	
	'Variétés',
	'World',
];
for ($x = 0; $x < count($music); ++$x) {
    $HTMLOUT .= "
                <label>
                    <input type='checkbox' value='{$music[$x]}' name='{music[]}' class='DEPENDS ON genre BEING music' />
                    <span>{$music[$x]}</span>
                </label>";
}
$game = [
    'Action',
    'Aventure',
    'Action-aventure',
	'Jeu de tir',
    'Jeu de rôle',
    'Réflexion',
	'Simulation',
	'Stratégie',
	'Sport',
];
for ($x = 0; $x < count($game); ++$x) {
    $HTMLOUT .= "
                <label>
                    <input type='checkbox' value='{$game[$x]}' name='{game[]}' class='DEPENDS ON genre BEING game' />
                    <span>{$game[$x]}</span>
                </label>";
}
$apps = [
    'AntiVirus',
    'Base de données',
    'Bureautique',
    'Comptabilité',
    'Education',
    'Graphisme',
    'Internet',
	'Lecteur',
	'Musique',
	'Plugins',
	'Programmation',
	'Réseau',
	'Samples',
	'Utilitaire',
	'Vidéo',
];
for ($x = 0; $x < count($apps); ++$x) {
    $HTMLOUT .= "
                <label>
                    <input type='checkbox' value='{$apps[$x]}' name='{apps[]}' class='DEPENDS ON genre BEING apps' />
                    <span>{$apps[$x]}</span>
                </label>";
}
$HTMLOUT .= '
            </td>
        </tr>';
	 
if ($CURUSER['class'] >= UC_UPLOADER && !XBT_TRACKER) {
    $HTMLOUT .= tr('Vip Torrent', "<div class='flex'><input type='checkbox' name='vip' value='1' /><span>  Si coché, seul les Vips peuvent télécharger ce torrent</span></div>", 1);
}
if ($CURUSER['class'] >= UC_USER){
     $HTMLOUT .= tr("Torrent THD Seedbox", "<input type='checkbox' name='seedbox' value='1' />Si vous cochez cette case votre torrent apparaîtra comme étant sur une Seedbox / THD", 1);
}
$HTMLOUT .= "
        <tr>
            <td colspan='2'>
                <div class='has-text-centered'>
                    <input type='submit' class='button is-small' value='{$lang['upload_submit']}' />
                </div>
            </td>
        </tr>
        </table>
        </form>";

echo stdhead($lang['upload_stdhead'], true) . wrapper($HTMLOUT) . stdfoot($stdfoot);
