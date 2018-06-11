<?php

global $site_config;

$lang = [
    //upload errors
    'upload_sorry'        => 'Sorry...',
    'upload_no_auth'      => "You are not authorized to upload torrents.  (See <a href='faq.php#up'>Uploading</a> in the FAQ.)",
    'upload_announce_url' => "The tracker's announce url is",
    'upload_delete'       => 'Delete',
    //upload options
    'upload_torrent'           => '.Torrent',
    'upload_poster'            => 'Affiche',
    'upload_poster1'           => '(La largeur minimum d\'affiche devrait être 400Px, les tailles plus grandes seront mises à l\'échelle.)',
    'upload_name'              => 'Nom du Torrent',
    'upload_filename'          => 'Extrait du nom de fichier si non spécifié. <b> Veuillez utiliser des noms descriptifs.</b>',
    'upload_description'       => 'Description',
    'upload_small_descr'       => 'Courte description du fichier. Cette description est indiquée sur le Flux RSS, sur parcourir et sous le nom de torrent.',
    'upload_nfo'               => 'Fichier NFO',
    'upload_nfo_info'          => '<b>Optional.</b> Can only be viewed by power users.',
    'upload_small_description' => 'Petite description',
    'upload_html_bbcode'       => 'Le HTML n\'est <b>pas</b> autorisé.',
    'upload_choose_one'        => 'Choisir',
    'upload_anonymous'         => 'Upload annonyme',
    'upload_anonymous1'        => "Ne pas afficher mon nom d'utilisateur dans le champ: Envoyé par.",
    'upload_type'              => 'Catégorie',
    'upload_submit'            => 'Envoyer!',
    'upload_imdb_url'          => 'URL Imdb',
    'upload_isbn'              => 'ISBN',
    'upload_imdb_tfi'          => '(Pris de Imdb - ',
    'upload_imdb_rfmo'         => 'Ajouter l\'URL Imdb pour afficher les données Imdb sur les détails.)',
    'upload_isbn_details'      => '(Utilisé pour les livres, ISBN 13 ou ISBN 10, sans espaces ni tirets)',
    'upload_youtube'           => "<a href='{$site_config['anonymizer_url']}http://youtube.com' target='_blank'>Youtube</a>",
    'upload_youtube_info'      => "Lien direct vers YouTube, sera affiché sur la page de détails du torrent.<br>Le lien devrait ressembler à <b>http://www.youtube.com/watch?v=camI8yuoy8U</b>",
    //upload stdhead
    'upload_comment' => 'Autoriser les commentaires',
    'upload_discom1' => 'Cochez pour désactiver les commentaires!',
    'upload_stdhead' => 'Upload',
    //upload bitbucket
    'upload_bitbucket'   => 'Bitbucket',
    'upload_tags'        => 'Tags',
    'upload_tag_info'    => 'Plusieurs tags doivent être séparés par une virgule comme tag1, tag2',
    'upload_bitbucket_1' => '(Note* le téléchargement est géré par le bitbucket et l\'image sera hébergée sur le serveur)',
];
