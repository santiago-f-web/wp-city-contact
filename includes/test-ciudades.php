<?php
if (!defined('ABSPATH')) exit;

// Requiere la configuración de URL/token
require_once __DIR__ . '/config.php';

// Headers para ver el resultado en navegador limpio
header('Content-Type: text/plain');

// 🔁 Obtener lista de ciudades
$response = wp_remote_post(rtrim($ccm_api_url, '/') . '/ciudad/search', [
    'headers' => [
        'Authorization' => 'Bearer ' . $ccm_api_token,
        'Content-Type'  => 'application/json'
    ],
    'body' => json_encode([])
]);

if (is_wp_error($response)) {
    echo "❌ Error al obtener ciudades\n";
    exit;
}

$ciudades_data = json_decode(wp_remote_retrieve_body($response), true);

if (empty($ciudades_data['data'])) {
    echo "❌ No se encontraron ciudades\n";
    exit;
}

echo "✅ Ciudades encontradas: " . count($ciudades_data['data']) . "\n\n";

foreach ($ciudades_data['data'] as $ciudad) {
    $nombre = $ciudad['CIUDAD'];

    // 🔍 Buscar contactos por ciudad
    $contacto_res = wp_remote_post(rtrim($ccm_api_url, '/') . '/ciudad/contacto/search', [
        'headers' => [
            'Authorization' => 'Bearer ' . $ccm_api_token,
            'Content-Type'  => 'application/json'
        ],
        'body' => json_encode(['CIUDAD' => $nombre])
    ]);

    if (!is_wp_error($contacto_res)) {
        $contacto_data = json_decode(wp_remote_retrieve_body($contacto_res), true);
        $telefonos = $contacto_data['data']['RESULTADO'] ?? '';
        echo "📍 $nombre: " . ($telefonos ?: 'Sin contactos') . "\n";
    } else {
        echo "⚠️ Error consultando ciudad: $nombre\n";
    }
}
