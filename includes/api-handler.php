<?php

if (!defined('ABSPATH')) exit;

// Configuración centralizada
require_once WP_CITY_CONTACT_PATH . 'includes/config.php';

// AJAX: Buscar contactos por ciudad
add_action('wp_ajax_get_city_contacts', 'get_city_contacts');
add_action('wp_ajax_nopriv_get_city_contacts', 'get_city_contacts');

function get_city_contacts() {
    if (!isset($_POST['city'])) {
        wp_send_json_error(['message' => 'Ciudad no especificada.']);
    }

    $city = sanitize_text_field($_POST['city']);

    $base_url = rtrim($GLOBALS['ccm_api_url'], '/');
    $token = $GLOBALS['ccm_api_token'];
    $endpoint = $base_url . '/ciudad/contacto/search';

    if (!$token || !$base_url) {
        error_log('❌ API TOKEN o BASE URL no están definidos correctamente');
        wp_send_json_error(['message' => 'API no configurada correctamente.']);
    }

    $response = wp_remote_post($endpoint, [
        'body' => json_encode(['CIUDAD' => $city]),
        'headers' => [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ]
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => 'Error en la conexión a la API.']);
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if ($data['resultado'] === true && $data['data']['ESTADO'] === 'OK') {
        wp_send_json_success(['phones' => explode(',', $data['data']['RESULTADO'])]);
    } else {
        wp_send_json_error(['message' => 'No se encontraron contactos.']);
    }
}


// Simulación de ciudades
add_action('rest_api_init', function () {
    register_rest_route('ccm/v1', '/ciudades', [
        'methods'             => 'GET',
        'callback'            => 'ccm_obtener_ciudades',
        'permission_callback' => '__return_true'
    ]);
});


function ccm_obtener_ciudades() {
    $endpoint = trailingslashit($GLOBALS['ccm_api_url']) . 'ciudad/search';

    $response = wp_remote_post($endpoint, [
        'headers' => [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $GLOBALS['ccm_api_token'],
        ],
        'body' => json_encode([]) // vacía como pide la API
    ]);

    if (is_wp_error($response)) {
        return [
            'resultado' => false,
            'mensaje' => 'Error de conexión al cargar ciudades',
            'data' => []
        ];
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!isset($data['resultado']) || !$data['resultado']) {
        return [
            'resultado' => false,
            'mensaje' => 'Respuesta inválida de la API',
            'data' => []
        ];
    }

    $ciudades = [];

    foreach ($data['data'] as $item) {
        if (isset($item['CIUDAD'])) {
            $ciudades[] = $item['CIUDAD'];
        }
    }

    return [
        'resultado' => true,
        'data' => $ciudades
    ];
}


//Envia registro de llamada
add_action('wp_ajax_nopriv_ccm_registrar_llamada', 'ccm_registrar_llamada');
add_action('wp_ajax_ccm_registrar_llamada', 'ccm_registrar_llamada');

function ccm_registrar_llamada() {
    $contacto = sanitize_text_field($_POST['contacto'] ?? '');

    if (!$contacto) {
        wp_send_json_error(['message' => 'Número no recibido']);
    }

    $ip = $_SERVER['REMOTE_ADDR'];
    $dominio = $_SERVER['HTTP_HOST'] ?? site_url();

    $body = [
        'CONTACTO'       => $contacto,
        'DOMINIO_ORIGEN' => $dominio,
        'IP_ORIGEN'      => $ip
    ];

    $response = wp_remote_post(trailingslashit($GLOBALS['ccm_api_url']) . 'registro/llamada/store', [
        'headers' => [
            'Authorization' => 'Bearer ' . $GLOBALS['ccm_api_token'],
            'Content-Type'  => 'application/json'
        ],
        'body' => json_encode($body)
    ]);

    if (is_wp_error($response)) {
        wp_send_json_error(['message' => 'Error de conexión con la API']);
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    wp_send_json_success($data);
}

