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
			add_filter('the_content', __CLASS__.'::theContent');
			//add_action('wp_enqueue_scripts',    __CLASS__ . '::loadResources');
			//add_action('admin_enqueue_scripts', __CLASS__ . '::loadResources');

			//add_action('init', array( $this, 'init' ));
			//add_action('init', array( $this, 'upgrade' ), 11);
		}

		public static function theContent($content) {
			global $post;
			$cache = \ExtraContent\CustomPostType::getItemsCache();
			if ($cache === false) { // No items to display at all
				return $content;
			}
			$ids = [];
			$categories = wp_get_object_terms($post->ID, 'category', ['fields' => 'ids']);
			$tags = wp_get_object_terms($post->ID, 'post_tag', ['fields' => 'ids']);
			foreach ($cache as $id => $item) {
				if (array_intersect($categories, $item['categories']) || array_intersect($tags, $item['tags'])) {
					$ids[] = $id;
				}
			}
			if (empty($ids)) { // No items found
				return $content;
			}
			$top = '';
			$bottom = '';
			$items = get_posts([
				'post_type' => \ExtraContent\CustomPostType::POST_TYPE_SLUG,
				'orderby' => 'meta_value_num',
				'meta_key' => 'exco_priority',
				'posts_per_page' => -1,
				'post_status' => 'publish',
				'include' => $ids,
			]);
			foreach ($items as $item) {
				if ($cache[$item->ID]['location'] == 'none') {
					continue;
				}
				$extra_content = $item->post_content;
				if (empty($extra_content)) {
					continue;
				}
				$extra_content = '<div class="exco_content exco_priority_'.$cache[$item->ID]['priority'].'">' . $extra_content . '</div>';
				if ($cache[$item->ID]['location'] == 'top' || $cache[$item->ID]['location'] == 'both') {
					$top .= $extra_content;
				}
				if ($cache[$item->ID]['location'] == 'bottom' || $cache[$item->ID]['location'] == 'both') {
					$bottom .= $extra_content;
				}
			}
			if (!empty($top)) {
				$content = '<div class="exco_container_top">' . $top . '</div>' . $content;
			}
			if (!empty($bottom)) {
				$content .= '<div class="exco_container_bottom">' . $bottom . '</div>';
			}
			return $content;
		}

	}

}
