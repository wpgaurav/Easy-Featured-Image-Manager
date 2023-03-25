<?php
/*
Plugin Name: Easy Featured Image Manager
Description: Add featured image management options in the list of posts and custom post types. Easily add, remove or replace featured images from post list.
Version: 0.0.1
Author: Gaurav Tiwari
Author URI: https://gauravtiwari.org
*/
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// Define plugin constants
define( 'EFIM_VERSION', '0.0.1' );
define( 'EFIM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EFIM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'EFIM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'EFIM_PLUGIN_FILE', __FILE__ );

/**
 * Main instance of Easy_Featured_Image_Manager.
 *
 * Returns the main instance of Easy_Featured_Image_Manager to prevent the need to use globals.
 *
 * @since  0.0.1
 * @return Easy_Featured_Image_Manager
 */
function easyfim_add_featured_image_column($columns) {
    $columns['featured_image'] = 'Featured Image';
    return $columns;
}

function easyfim_display_featured_image_column($column, $post_id) {
    if ('featured_image' === $column) {
        $thumbnail = get_the_post_thumbnail($post_id, array(100, 100));
        echo $thumbnail;
    }
}

add_filter('manage_posts_columns', 'easyfim_add_featured_image_column');
add_action('manage_posts_custom_column', 'easyfim_display_featured_image_column', 10, 2);
add_filter('manage_pages_columns', 'easyfim_add_featured_image_column');
add_action('manage_pages_custom_column', 'easyfim_display_featured_image_column', 10, 2);
function easyfim_enqueue_scripts($hook) {
    if ('edit.php' !== $hook) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_script('easyfim-admin-js', plugin_dir_url(__FILE__) . 'js/easyfim-admin.js', array('jquery'), '1.0.0', true);
    wp_enqueue_style('easyfim-admin-css', plugin_dir_url(__FILE__) . 'css/easyfim-admin.css', array(), '1.0.0');
}

add_action('admin_enqueue_scripts', 'easyfim_enqueue_scripts');
function easyfim_ajax_set_featured_image() {
    check_ajax_referer('easyfim_nonce', '_wpnonce');

    $post_id = intval($_POST['post_id']);
    $attachment_id = intval($_POST['attachment_id']);

    if ($post_id && $attachment_id) {
        update_post_meta($post_id, '_thumbnail_id', $attachment_id);
        $thumbnail = get_the_post_thumbnail($post_id, array(100, 100));
        wp_send_json_success(array('thumbnail' => $thumbnail));
    } else {
        wp_send_json_error(array('message' => 'Invalid post or attachment ID.'));
    }
}

function easyfim_ajax_remove_featured_image() {
    check_ajax_referer('easyfim_nonce', '_wpnonce');

    $post_id = intval($_POST['post_id']);

    if ($post_id) {
        delete_post_meta($post_id, '_thumbnail_id');
        $thumbnail = '<a href="#" class="easyfim-change-image" data-post-id="' . $post_id . '">Upload Image</a>';
        wp_send_json_success(array('thumbnail' => $thumbnail));
    } else {
        wp_send_json_error(array('message' => 'Invalid post ID.'));
    }
}

add_action('wp_ajax_easyfim_set_featured_image', 'easyfim_ajax_set_featured_image');
add_action('wp_ajax_easyfim_remove_featured_image', 'easyfim_ajax_remove_featured_image');
function easyfim_output_vars() {
    $vars = array(
        'nonce' => wp_create_nonce('easyfim_nonce')
    );

    echo '<script>window.easyfimVars = ' . json_encode($vars) . ';</script>';
}

add_action('admin_footer', 'easyfim_output_vars');
function easyfim_quick_edit_featured_image($column_name, $post_type) {
    if ('featured_image' === $column_name) {
        ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <label class="inline-edit-featured-image">
                    <span class="title">Featured Image</span>
                    <span class="input-text-wrap">
                        <input type="hidden" name="easyfim_post_id" class="easyfim_post_id" value="">
                        <a href="#" class="easyfim-change-image">Change Image</a>
                    </span>
                </label>
            </div>
        </fieldset>
        <?php
    }
}

add_action('quick_edit_custom_box', 'easyfim_quick_edit_featured_image', 10, 2);
