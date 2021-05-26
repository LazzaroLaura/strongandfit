<?php
/**
 * Plugin Name: StrongAndFit
 */

use StrongAndFit\Api;
use StrongAndFit\Plugin;

require __DIR__ . '/static-vendor/autoload.php';

$strongAndFit = new Plugin();

$api = new Api();