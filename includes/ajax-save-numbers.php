<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_ccm_guardar_telefonos', 'ccm_guardar_telefonos');

function ccm_guardar_telefonos() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'No tienes permisos']);
    }

    $ciudad = sanitize_text_field($_POST['ciudad'] ?? '');
    $telefonos_raw = $_POST['telefonos'] ?? '[]';
    $telefonos = json_decode(stripslashes($telefonos_raw), true);

    if (empty($ciudad) || !is_array($telefonos)) {
        wp_send_json_error(['message' => 'Datos inválidos']);
    }

    $actuales = get_option('ccm_telefonos_por_ciudad', []);
    $actuales[$ciudad] = array_unique($telefonos);

    update_option('ccm_telefonos_por_ciudad', $actuales);
    update_option('ccm_ciudad_actual', $ciudad); // ✅ ciudad activa
    update_option('ccm_last_sync', time()); // ✅ reinicia sincronización

    wp_send_json_success(['message' => 'Teléfonos actualizados']);
}
