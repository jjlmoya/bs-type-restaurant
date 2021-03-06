<?php
/**
 * Plugin Name: Restaurant Model [Post Type]
 * Plugin URI: https://www.bonseo.es/
 * Description: Modelo de Restaurante
 * Author: jjlmoya
 * Author URI: https://www.bonseo.es/
 * Version: 1.0.0
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * @package BS
 */

if (!defined('ABSPATH')) {
	exit;
}

/** MODEL CONFIGURATION **/
require_once plugin_dir_path(__FILE__) . '/Restaurant.php';
function bs_restaurant_get_post_type()
{
	return Restaurant::getInstance('Restaurante', 'Restaurantes', "restaurante",
		array(
			"price" => array(
				"name" => "Precio",
				"value" => "price",
				"input" => "number"
			),
			"cordX" => array(
				"name" => "Coord X",
				"value" => "cordX",
				"input" => "text"
			),
			"cordY" => array(
				"name" => "Coord Y",
				"value" => "cordY",
				"input" => "text"
			),
			"affiliateLink" => array(
				"name" => "Link de Afiliados",
				"value" => "affiliateLink",
				"input" => "text"
			),
			"affiliateCTA" => array(
				"name" => "CTA Afiliación",
				"value" => "affiliateCTA",
				"input" => "text"
			)
		)
	);
}

/** END MODEL CONFIGURATION */

/** REGISTER CORE FUNCTIONS **/
function bs_restaurant_register_post_type()
{
	$model = bs_restaurant_get_post_type();
	$labels = array(
		"name" => __($model->plural, "custom-post-type-ui"),
		"singular_name" => __($model->singular, "custom-post-type-ui"),
	);

	$args = array(
		"label" => __($model->plural, "custom-post-type-ui"),
		"labels" => $labels,
		'menu_icon' => $model->icon,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"delete_with_user" => false,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array("slug" => $model->path, "with_front" => false),
		"query_var" => true,
		"supports" =>
			array("title",
				"editor",
				"thumbnail",
				"custom-fields",
				"excerpt"),
	);

	register_post_type($model->db, $args);
}

function bs_restaurant_create_custom_params()
{
	$model = bs_restaurant_get_post_type();
	foreach ($model->customFields as $customField) {
		add_action('add_meta_boxes', $model->nameSpace . '_' . $customField["value"] . '_register');
	}
}

function bs_restaurant_register($customType)
{
	$model = bs_restaurant_get_post_type();
	$customField = $model->customFields;
	$customField = $customField[$customType];
	add_meta_box(
		$model->db . '_' . $customField['value'],
		$customField['name'],
		$model->nameSpace . '_' . $customField['value'] . '_callback',
		$model->db,
		'side',
		'high'
	);

}

function bs_restaurant_callback($fieldType)
{
	$model = bs_restaurant_get_post_type();
	$customField = $model->customFields;
	$customField = $customField[$fieldType];
	$dbEntry = $model->db . '_' . $customField['value'];
	global $post;
	wp_nonce_field(basename(__FILE__), $dbEntry);
	$value = get_post_meta($post->ID, $dbEntry, true);
	echo '<input type="' . $customField['input'] . '" name="' . $dbEntry . '" value="' . esc_textarea($value) . '" class="widefat">';
}

function bs_restaurant_on_save($post_id)
{

	$model = bs_restaurant_get_post_type();

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (isset($_POST['post_type']) && $_POST['post_type'] == $model->db) {
		if (!current_user_can('edit_page', $post_id)) {
			return;
		}
	} else {
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
	}
	foreach ($model->customFields as $customField) {
		$customFieldEntry = $model->db . '_' . $customField['value'];
		if (!isset($_POST[$customFieldEntry])) {
			return;
		}
		$myValue = sanitize_text_field($_POST[$customFieldEntry]);
		update_post_meta($post_id, $customFieldEntry, $myValue);
	}
}

add_action('init', 'bs_restaurant_register_post_type');
add_action('save_post', 'bs_restaurant_on_save');
bs_restaurant_create_custom_params();


function bs_restaurant_cordX_register()
{
	bs_restaurant_register('cordX');
}

function bs_restaurant_cordX_callback()
{
	bs_restaurant_callback('cordX');
}

function bs_restaurant_cordY_register()
{
	bs_restaurant_register('cordY');
}

function bs_restaurant_cordY_callback()
{
	bs_restaurant_callback('cordY');
}

function bs_restaurant_affiliateLink_register()
{
	bs_restaurant_register('affiliateLink');
}

function bs_restaurant_affiliateLink_callback()
{
	bs_restaurant_callback('affiliateLink');
}

function bs_restaurant_affiliateCTA_register()
{
	bs_restaurant_register('affiliateCTA');
}

function bs_restaurant_affiliateCTA_callback()
{
	bs_restaurant_callback('affiliateCTA');
}

function bs_restaurant_price_register()
{
	bs_restaurant_register('price');
}

function bs_restaurant_price_callback()
{
	bs_restaurant_callback('price');
}




