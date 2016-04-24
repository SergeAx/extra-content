<?php

namespace ExtraContent;

if (!class_exists('\ExtraContent\Plugin')) {

	class Plugin {

		protected $customPost;
		
		protected function __construct() {
			$customPost = \ExtraContent\CustomPostType::getInstance();
			$this->registerHookCallbacks();
		}

		public static function getInstance() {
			static $inst = null;
			if ($inst === null) {
				$inst = new \ExtraContent\Plugin();
			}
			return $inst;
		}

		public static function activationHook() {
			flush_rewrite_rules();
		}

		public static function deactivationHook() {
			flush_rewrite_rules();
		}

		public static function registerHookCallbacks() {
			//add_action('wp_enqueue_scripts',    __CLASS__ . '::loadResources');
			//add_action('admin_enqueue_scripts', __CLASS__ . '::loadResources');

			//add_action('init', array( $this, 'init' ));
			//add_action('init', array( $this, 'upgrade' ), 11);
		}

	}

}
