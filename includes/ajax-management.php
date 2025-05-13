<?php
if (!defined('ABSPATH')) exit;

// 📦 Configuración centralizada
require_once WP_CITY_CONTACT_PATH . 'includes/config.php';

// Obtener todos los contactos de todas las ciudades
add_action('wp_ajax_get_all_city_contacts', function () {
    error_log("🔥 Entrando en get_all_city_contacts()");

    $ciudades = wp_city_contact_get_cities();
    error_log("🧠 Ciudades recibidas: " . print_r($ciudades, true));

    if (empty($ciudades)) {
        error_log("⚠️ No hay ciudades recibidas");
        wp_send_json_success([]);
        return;
    }

    $results = [];

    foreach ($ciudades as $ciudad) {
        $endpoint = rtrim(CCM_API_BASE, '/') . '/ciudad/contacto/search';

        $res = wp_remote_post($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . CCM_API_TOKEN,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode(['CIUDAD' => $ciudad])
        ]);

        if (!is_wp_error($res)) {
            $data = json_decode(wp_remote_retrieve_body($res), true);
            if (!empty($data['data']['RESULTADO'])) {
                $numeros = explode(',', $data['data']['RESULTADO']);
                foreach ($numeros as $num) {
                    $results[] = ['ciudad' => $ciudad, 'numero' => trim($num)];
                }
            }
        }
    }

    wp_send_json_success($results);
});

// Agregar relación ciudad-contacto
add_action('wp_ajax_add_city_contact', function () {
    $ciudad = sanitize_text_field($_POST['ciudad']);
    $contacto = sanitize_text_field($_POST['contacto']);

    $body = ['RELACIONES' => [[
        'CIUDAD' => $ciudad,
        'CONTACTO' => $contacto
    ]]];

    $endpoint = rtrim(CCM_API_BASE, '/') . '/ciudad/contacto/store';

    $res = wp_remote_post($endpoint, [
        'headers' => [
            'Authorization' => 'Bearer ' . CCM_API_TOKEN,
            'Content-Type' => 'application/json'
        ],
        'body' => json_encode($body)
    ]);

    $data = json_decode(wp_remote_retrieve_body($res), true);
    wp_send_json($data);
});

// Eliminar relación ciudad-contacto
add_action('wp_ajax_delete_city_contact', function () {
    $ciudad = sanitize_text_field($_POST['ciudad']);
    $contacto = sanitize_text_field($_POST['contacto']);

    $body = ['RELACIONES' => [[
        'CIUDAD' => $ciudad,
        'CONTACTO' => $contacto
    ]]];

    $endpoint = rtrim(CCM_API_BASE, '/') . '/ciudad/contacto/delete';

    $res = wp_remote_post($endpoint, [
        'headers' => [
            'Authorization' => 'Bearer ' . CCM_API_TOKEN,
            'Content-Type' => 'application/json'
        ],
        'body' => json_encode($body)
    ]);

    $data = json_decode(wp_remote_retrieve_body($res), true);
    wp_send_json($data);
});

// Editar relación ciudad-contacto: borrar + crear
add_action('wp_ajax_edit_city_contact', function () {
    $ciudad = sanitize_text_field($_POST['ciudad']);
    $old = sanitize_text_field($_POST['old']);
    $new = sanitize_text_field($_POST['new']);

    // Eliminar anterior
    $_POST['contacto'] = $old;
    do_action('wp_ajax_delete_city_contact');

    // Agregar nuevo
    $_POST['contacto'] = $new;
    do_action('wp_ajax_add_city_contact');
});
