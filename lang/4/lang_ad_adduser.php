<?php

global $site_config;

$lang = [
    //Misc
    'std_adduser'        => 'Ajouter un utilisateur',
    'std_err'            => 'Erreur',
    'std_success'        => 'Succès',
    'btn_okay'           => 'Ok',
    //err
    'err_username'       => 'Nom d\'utilisateur oublié ou pas assez long (min 5 caractères)',
    'err_password'       => 'Mot de passe oublié ou pas assez long (min 6 caractères)',
    'err_email'          => 'Email oublié ou Email non valide',
    'err_mysql_err'      => 'Il y a eu une erreur mysql: %s, signaler la au Sysop',
    'err_already_exists' => 'L\'utilisateur existe déjà ... redirection en cours!',
    //Texts
    'text_user_added'    => 'L\'utilisateur a été ajouté, voir son profil <a href="' . $site_config['baseurl'] . '/userdetails.php?id=%d">here</a>',
    'text_username'      => 'Nom d\'utilisateur',
    'text_password'      => 'Mot de passe',
    'text_password2'     => 'Retaper le mot de passe',
    'text_email'         => 'Email',
];
