<?php
/**
 * Plugin Name: StrongAndFit
 */

use StrongAndFit\Api;
use StrongAndFit\Plugin;

require __DIR__ . '/static-vendor/autoload.php';

$strongAndFit = new Plugin();

register_activation_hook(
    __FILE__, // fichier racine du plugin
    // appel de la méthode activate sur l'objet plugin
    [$strongAndFit, 'activate']
);

/* register_deactivation_hook(
    __FILE__,
    [$strongAndFit, 'deactivate']
); */

$api = new Api();