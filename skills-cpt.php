<?php
/**
 * Plugin Name: Skills CPT
 * Description: Registra el CPT "skill" para gestionar habilidades con nivel, color, imagen y soporte para REST/GraphQL.
 * Version: 1.0.0
 * Author: SW
 * License: GPL2+
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Constantes para metadatos.
 */
const SKILLS_CPT_META_LEVEL = '_skill_level';
const SKILLS_CPT_META_COLOR = '_skill_color';

/**
 * Registra el Custom Post Type de habilidades.
 */
function skills_cpt_register_post_type()
{
    $labels = array(
        'name'               => 'Habilidades',
        'singular_name'      => 'Habilidad',
        'menu_name'          => 'Habilidades',
        'add_new'            => 'Anadir nueva',
        'add_new_item'       => 'Anadir nueva habilidad',
        'edit_item'          => 'Editar habilidad',
        'new_item'           => 'Nueva habilidad',
        'view_item'          => 'Ver habilidad',
        'all_items'          => 'Todas las habilidades',
        'search_items'       => 'Buscar habilidades',
        'not_found'          => 'No se encontraron habilidades',
        'not_found_in_trash' => 'No hay habilidades en la papelera',
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => false,
        'show_in_rest'        => true,
        'show_in_graphql'     => true,
        'graphql_single_name' => 'Skill',
        'graphql_plural_name' => 'Skills',
        'menu_icon'           => 'dashicons-star-filled',
        'supports'            => array('title', 'thumbnail', 'excerpt'),
        'rewrite'             => array('slug' => 'habilidades'),
        'menu_position'       => 25,
    );

    register_post_type('skill', $args);
}
add_action('init', 'skills_cpt_register_post_type');

/**
 * Agrega el meta box para nivel y color.
 */
function skills_cpt_add_meta_box()
{
    add_meta_box(
        'skills_cpt_details',
        'Detalles de la Habilidad',
        'skills_cpt_render_meta_box',
        'skill',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'skills_cpt_add_meta_box');

/**
 * Render del meta box.
 */
function skills_cpt_render_meta_box($post)
{
    wp_nonce_field('skills_cpt_save_meta', 'skills_cpt_meta_nonce');
    
    $level = get_post_meta($post->ID, SKILLS_CPT_META_LEVEL, true);
    $color = get_post_meta($post->ID, SKILLS_CPT_META_COLOR, true);

    if ($level === '') $level = 100;
    if ($color === '') $color = '#2563eb';
    ?>
    <p>
        <label for="skills_cpt_level"><strong>Nivel de dominio (0-100)</strong></label>
        <input
            type="number"
            id="skills_cpt_level"
            name="skills_cpt_level"
            value="<?php echo esc_attr($level); ?>"
            min="0"
            max="100"
            style="width:100%;margin-top:6px;"
        />
    </p>
    <p>
        <label for="skills_cpt_color"><strong>Color de la habilidad</strong></label>
        <input
            type="color"
            id="skills_cpt_color"
            name="skills_cpt_color"
            value="<?php echo esc_attr($color); ?>"
            style="width:100%;margin-top:6px;"
        />
    </p>
    <?php
}

/**
 * Guardado seguro de metadatos.
 */
function skills_cpt_save_meta_box($post_id)
{
    if (!isset($_POST['skills_cpt_meta_nonce']) || !wp_verify_nonce($_POST['skills_cpt_meta_nonce'], 'skills_cpt_save_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!isset($_POST['post_type']) || $_POST['post_type'] !== 'skill') {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Guardar Nivel
    if (isset($_POST['skills_cpt_level'])) {
        $level = min(100, max(0, (int) $_POST['skills_cpt_level']));
        update_post_meta($post_id, SKILLS_CPT_META_LEVEL, $level);
    }

    // Guardar Color
    if (isset($_POST['skills_cpt_color'])) {
        $color = sanitize_hex_color($_POST['skills_cpt_color']);
        if ($color) {
            update_post_meta($post_id, SKILLS_CPT_META_COLOR, $color);
        }
    }
}
add_action('save_post', 'skills_cpt_save_meta_box');

/**
 * Registro de campos en REST API.
 */
function skills_cpt_register_rest_fields()
{
    register_rest_field(
        'skill',
        'level',
        array(
            'get_callback' => function ($post_arr) {
                $val = get_post_meta($post_arr['id'], SKILLS_CPT_META_LEVEL, true);
                return $val !== '' ? (int) $val : 100;
            },
            'schema' => array('type' => 'integer'),
        )
    );

    register_rest_field(
        'skill',
        'color',
        array(
            'get_callback' => function ($post_arr) {
                $val = get_post_meta($post_arr['id'], SKILLS_CPT_META_COLOR, true);
                return $val !== '' ? $val : '#2563eb';
            },
            'schema' => array('type' => 'string'),
        )
    );
}
add_action('rest_api_init', 'skills_cpt_register_rest_fields');

/**
 * Registro de campos en WPGraphQL.
 */
function skills_cpt_register_graphql_fields()
{
    if (!function_exists('register_graphql_field')) {
        return;
    }

    register_graphql_field(
        'Skill',
        'level',
        array(
            'type'    => 'Int',
            'resolve' => function ($post) {
                $val = get_post_meta($post->ID, SKILLS_CPT_META_LEVEL, true);
                return $val !== '' ? (int) $val : 100;
            },
        )
    );

    register_graphql_field(
        'Skill',
        'color',
        array(
            'type'    => 'String',
            'resolve' => function ($post) {
                $val = get_post_meta($post->ID, SKILLS_CPT_META_COLOR, true);
                return $val !== '' ? $val : '#2563eb';
            },
        )
    );
}
add_action('graphql_register_types', 'skills_cpt_register_graphql_fields');

/**
 * Soporte para Polylang.
 */
function skills_cpt_polylang_register($post_types, $is_settings)
{
    if ($is_settings) {
        $post_types['skill'] = 'skill';
    } else {
        $post_types[] = 'skill';
    }
    return $post_types;
}
add_filter('pll_get_post_types', 'skills_cpt_polylang_register', 10, 2);

function skills_cpt_polylang_set_default($post_id, $post)
{
    if ($post->post_type !== 'skill' || !function_exists('pll_set_post_language') || !function_exists('pll_default_language')) {
        return;
    }
    if (empty(pll_get_post_language($post_id))) {
        pll_set_post_language($post_id, pll_default_language());
    }
}
add_action('save_post', 'skills_cpt_polylang_set_default', 20, 2);
