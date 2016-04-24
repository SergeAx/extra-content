<?php
/*
Plugin Name: Extra content
Description: Adds custom content at the end and/or beginning of selected posts
Version:     0.1
Author:      Sergey Aksenov
License:     GPL3
*/

if (!defined('ABSPATH')) {
	die('No direct access to plugin.');
}

require_once(__DIR__ . '/vendor/autoload.php');

if (class_exists('\ExtraContent\Plugin')) {
	$GLOBALS['exco'] = \ExtraContent\Plugin::getInstance();

	register_activation_hook(__FILE__, array('\ExtraContent\Plugin', 'activationHook'));
	register_deactivation_hook(__FILE__, array('\ExtraContent\Plugin', 'deactivationHook'));
}
