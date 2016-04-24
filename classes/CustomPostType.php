<?php

namespace ExtraContent;

if (!class_exists('\ExtraContent\CustomPostType')) {

	class CustomPostType {

		const POST_TYPE_NAME = 'Extra Content Item';
		const POST_TYPE_SLUG = 'exco-cpt';

		protected function __construct() {
			$this->registerHookCallbacks();
		}

		public function init() {
		}

		public function registerHookCallbacks() {
			add_action('init', __CLASS__ . '::registerPostType');
			add_action('save_post', __CLASS__ . '::savePost', 10, 2);
			add_filter('is_protected_meta', __CLASS__ . '::isProtectedMeta', 10, 3);
			add_filter('default_hidden_meta_boxes', __CLASS__ . '::defaultHiddenMetaBoxes', 10, 2);

			add_action('init', array($this, 'init'));

			add_shortcode('exco-insert', __CLASS__ . '::handleInsertShortcode');
		}

		public static function defaultHiddenMetaBoxes($hidden, $screen) {
			//error_log('$screen=' . print_r($screen, true));
			if (isset($screen->post_type) && $screen->post_type == self::POST_TYPE_SLUG) {
				$hidden = array_diff($hidden, array('slugdiv'));
			}
			return $hidden;
		}

		public static function registerPostType() {
			if (!post_type_exists(self::POST_TYPE_SLUG)) {
				$post_type_params = self::getPostTypeParams();
				$post_type = register_post_type(self::POST_TYPE_SLUG, $post_type_params);
				//var_dump($post_type_params, $post_type);

				if (is_wp_error($post_type)) {
					add_notice(__METHOD__.' error: ' . $post_type->get_error_message(), 'error');
				}
			}
		}

		protected static function getPostTypeParams() {
			$labels = array(
				'name'               => self::POST_TYPE_NAME . 's',
				'singular_name'      => self::POST_TYPE_NAME,
				'add_new'            => 'Add New',
				'add_new_item'       => 'Add New ' . self::POST_TYPE_NAME,
				'edit'               => 'Edit',
				'edit_item'          => 'Edit ' .    self::POST_TYPE_NAME,
				'new_item'           => 'New ' .     self::POST_TYPE_NAME,
				'view'               => 'View ' .    self::POST_TYPE_NAME . 's',
				'view_item'          => 'View ' .    self::POST_TYPE_NAME,
				'search_items'       => 'Search ' .  self::POST_TYPE_NAME . 's',
				'not_found'          => 'No ' .      self::POST_TYPE_NAME . 's found',
				'not_found_in_trash' => 'No ' .      self::POST_TYPE_NAME . 's found in Trash',
				'parent'             => 'Parent ' .  self::POST_TYPE_NAME,
			);

			$post_type_params = array(
				'labels'               => $labels,
				'singular_label'       => self::POST_TYPE_NAME,
				'public'               => false,
				'exclude_from_search'  => true,
				'publicly_queryable'   => false,
				'show_ui'              => true,
				'show_in_menu'         => true,
				'show_in_admin_bar'    => false,
				'register_meta_box_cb' => __CLASS__ . '::addMetaBoxes',
				'taxonomies'           => array('category', 'post_tag'),
				'menu_position'        => 20,
				'menu_icon'            => 'dashicons-layout',
				'hierarchical'         => false,
				'capability_type'      => 'post',
				'has_archive'          => false,
				'rewrite'              => false,
				'query_var'            => false,
				'supports'             => array('title', 'editor', 'revisions'),
			);
			return $post_type_params;
		}

		public static function addMetaBoxes() {
			add_meta_box(
				'exco_settings-meta-box',
				'Settings',
				__CLASS__ . '::settingsMetaBox',
				self::POST_TYPE_SLUG,
				'side',
				'high'
			);
		}

		public static function settingsMetaBox($post, $box) {
			if ($box['id'] !== 'exco_settings-meta-box') {
				return;
			}
			$location = get_post_meta($post->ID, 'exco_location', true);
			?>
<div class="inside">
	<label for="exco_settings_location"><h3>Location:</h3></label>
	<fieldset id="exco_settings_location">
		<input id="exco_settings_location_top" name="exco_settings_location" type="radio" value="top"<?php checked($location, 'top'); ?>>
		<label for="exco_settings_location_top">Top</label><br>
		<input id="exco_settings_location_bottom" name="exco_settings_location" type="radio" value="bottom"<?php checked($location, 'bottom'); ?>>
		<label for="exco_settings_location_bottom">Bottom</label><br>
		<input id="exco_settings_location_both" name="exco_settings_location" type="radio" value="both"<?php checked($location, 'both'); ?>>
		<label for="exco_settings_location_both">Both</label><br>
		<input id="exco_settings_location_none" name="exco_settings_location" type="radio" value="none"<?php checked($location, 'none'); ?>>
		<label for="exco_settings_location_none">None (disabled)</label>
	</fieldset>
</div>
			<?php
		}

		public static function savePost($post_id, $revision) {
			global $post;

			if (isset($_GET['action']) && in_array($_GET['action'], ['trash', 'untrash','restore'])) {
				return;
			}

			if (!$post || $post->post_type != self::POST_TYPE_SLUG || !current_user_can('edit_post', $post_id)) {
				return;
			}

			if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || $post->post_status == 'auto-draft') {
				return;
			}

			if (isset($_POST['exco_settings_location'])) {
				update_post_meta($post_id, 'exco_location', $_POST['exco_settings_location']);
			} else {
				delete_post_meta($post_id, 'exco_location');
			}

		}

		public static function isProtectedMeta($protected, $meta_key, $meta_type) {
			switch($meta_key) {
				case 'exco_location':
					$protected = true;
					break;
			}
			return $protected;
		}

		public static function handleInsertShortcode() {
			return 'Here be shortcode';
		}

		public static function getInstance() {
			static $inst = null;
			if ($inst === null) {
				$inst = new \ExtraContent\CustomPostType();
			}
			return $inst;
		}

	}

}