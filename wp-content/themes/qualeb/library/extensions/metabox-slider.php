<?php

/**
 * Add Metabox for Slider
 *
 * @package Qualeb
 * @since Qualeb 1.0
 *
 -------------------------------------
 */
 
/*-------------------------------------------------------------------------------------*/
/*		1.	Create Array
/*-------------------------------------------------------------------------------------*/

$prefix = 'dm_';

$dm_slider_meta_box = array(
	'id' => 'slide-options',
	'title' => 'Slide Options',
	'page' => 'slider_items',
	'context' => 'normal',
	'priority' => 'high',
	'fields' => array(
		array(
			'name' => 'Link',
			'id' => $prefix . 'slide_link',
			'type' => 'text',
			'desc' => "An optional link for this slide. Enter full address including (http://).",
		)
	)
);

add_action('admin_menu', 'dm_slider_meta_box');

/*-------------------------------------------------------------------------------------*/
/*		2.	Create meta box
/*-------------------------------------------------------------------------------------*/

function dm_slider_meta_box() {
	error_reporting(E_ALL ^ E_NOTICE);
	global $dm_slider_meta_box;
		add_meta_box($dm_slider_meta_box['id'], $dm_slider_meta_box['title'], 'dm_slider_text_show_fields', $dm_slider_meta_box['page'], $dm_slider_meta_box['context'], $dm_slider_meta_box['priority']);
}

/*-------------------------------------------------------------------------------------*/
/*		3.	Show fields in metabox
/*-------------------------------------------------------------------------------------*/

function dm_slider_text_show_fields() {
	global $dm_slider_meta_box, $post;

	echo '<input type="hidden" name="slider_text_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';
	echo '<table class="form-table">';

	foreach ($dm_slider_meta_box['fields'] as $field) {
		// get current post meta data
		
		$meta = get_post_meta($post->ID, $field['id'], true);
		
		echo '<tr>',
				'<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
				'<td>';
		switch ($field['type']) {
			case 'text':
				echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" />',
					'<br />', $field['desc'];
				break;
			case 'textarea':
				echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="4" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>',
					'<br />', $field['desc'];
				break;
			case 'select':
				echo '<select name="', $field['id'], '" id="', $field['id'], '">';
				foreach ($field['options'] as $option) {
					echo '<option', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>';
				}
				echo '</select>';
				break;
			case 'radio':
				foreach ($field['options'] as $option) {
					echo '<input type="radio" name="', $field['id'], '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'], '<br />';
				}
				break;
			case 'checkbox':
				echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' />';
				break;
		}
		echo 	'<td>',
			'</tr>';
	}
	
	echo '</table>';
}

add_action('save_post', 'slider_text_save_data');

/*-------------------------------------------------------------------------------------*/
/*		4.	Save data from meta box
/*-------------------------------------------------------------------------------------*/

function slider_text_save_data($post_id) {
	global $dm_slider_meta_box;
	
	// Nonce verification
	if (!wp_verify_nonce(isset($_POST['slider_text_meta_box_nonce']), basename(__FILE__))) {
		return $post_id;
	}

	// Autosaving
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post_id;
	}

	// Permissions Checking
	if ('page' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id)) {
			return $post_id;
		}
	} elseif (!current_user_can('edit_post', $post_id)) {
		return $post_id;
	}
	
	foreach ($dm_slider_meta_box['fields'] as $field) {
		$old = get_post_meta($post_id, $field['id'], true);
		$new = $_POST[$field['id']];
		
		if ($new && $new != $old) {
			update_post_meta($post_id, $field['id'], $new);
		} elseif ('' == $new && $old) {
			delete_post_meta($post_id, $field['id'], $old);
		}
	}
	}
	
// Thanks to http://www.deluxeblogtips.com/2010/04/how-to-create-meta-box-wordpress-post.html
?>