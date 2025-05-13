<?php
if (!defined('ABSPATH')) exit;

function wp_city_contact_selector_tab() {
    $ciudad_actual = get_option('ccm_ciudad_actual', '');
    $telefonos_por_ciudad = get_option('ccm_telefonos_por_ciudad', []);
    $telefonos_mostrados = [];

    if ($ciudad_actual && isset($telefonos_por_ciudad[$ciudad_actual])) {
        $telefonos_mostrados = $telefonos_por_ciudad[$ciudad_actual];
    }

    // 🔁 Verificar si hay que sincronizar desde API (una vez cada 24h)
    $last_sync = get_option('ccm_last_sync', 0);
    $ahora = time();

    if ($ciudad_actual && ($ahora - $last_sync > 86400)) {
        $endpoint = rtrim($GLOBALS['ccm_api_url'], '/') . '/ciudad/contacto/search';
        $response = wp_remote_post($endpoint, [
            'body' => json_encode(['CIUDAD' => $ciudad_actual]),
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $GLOBALS['ccm_api_token']
            ]
        ]);

        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if ($data['resultado'] === true && $data['data']['ESTADO'] === 'OK') {
                $telefonos_por_ciudad[$ciudad_actual] = explode(',', $data['data']['RESULTADO']);
                update_option('ccm_telefonos_por_ciudad', $telefonos_por_ciudad);
                update_option('ccm_last_sync', $ahora);
                $telefonos_mostrados = $telefonos_por_ciudad[$ciudad_actual];
            }
        }
    }
    ?>

    <div class="wrap">
        <h1>📍 Seleccionar Ciudad</h1>

        <?php if ($ciudad_actual): ?>
            <p><strong>Ciudad seleccionada actualmente:</strong> <?php echo esc_html($ciudad_actual); ?></p>
            <?php if (!empty($telefonos_mostrados)): ?>
                <p><strong>Teléfonos guardados:</strong></p>
                <ul>
                    <?php foreach ($telefonos_mostrados as $tel): ?>
                        <li><?php echo esc_html($tel); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p><em>No hay teléfonos guardados para esta ciudad.</em></p>
            <?php endif; ?>
        <?php else: ?>
            <p><em>No se ha seleccionado ninguna ciudad todavía.</em></p>
        <?php endif; ?>

        <hr>

        <label for="city">Seleccione una ciudad:</label>
        <select id="city">
            <option>Cargando ciudades...</option>
        </select>

        <button id="search-contact">Buscar</button>

        <h2>Números disponibles</h2>
        <ul id="phone-list"></ul>

        <button id="save-selection" style="display:none;">Guardar Selección</button>

        <hr style="margin: 40px 0;">

        <h2>📋 Todas las ciudades desde la API</h2>
        <?php
        // 🔄 Obtener listado de ciudades reales
        $ciudades = [];
        $telefonos_api = [];

        $ciudad_response = wp_remote_post($GLOBALS['ccm_api_url'] . 'ciudad/search', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $GLOBALS['ccm_api_token']
            ],
            'body' => json_encode([])
        ]);

        if (is_wp_error($ciudad_response)) {
            echo '<p style="color:red;">❌ Error al consultar la API de ciudades.</p>';
            return;
        }

        $ciudad_data = json_decode(wp_remote_retrieve_body($ciudad_response), true);
        if (!$ciudad_data['resultado'] || !isset($ciudad_data['data'])) {
            echo '<p style="color:red;">⚠️ No se pudieron cargar ciudades desde la API.</p>';
            return;
        }

        foreach ($ciudad_data['data'] as $ciudad) {
            $nombre = $ciudad['CIUDAD'];
            $ciudades[] = $nombre;

            // Obtener teléfonos de cada ciudad
            $res = wp_remote_post($GLOBALS['ccm_api_url'] . 'ciudad/contacto/search', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $GLOBALS['ccm_api_token']
                ],
                'body' => json_encode(['CIUDAD' => $nombre])
            ]);

            if (!is_wp_error($res)) {
                $data = json_decode(wp_remote_retrieve_body($res), true);
                if (!empty($data['data']['RESULTADO'])) {
                    $telefonos_api[$nombre] = explode(',', $data['data']['RESULTADO']);
                } else {
                    $telefonos_api[$nombre] = [];
                }
            } else {
                $telefonos_api[$nombre] = ['Error de conexión'];
            }
        }

        echo '<table class="widefat striped">';
        echo '<thead><tr><th>Ciudad</th><th>Teléfonos</th></tr></thead><tbody>';
        foreach ($telefonos_api as $ciudad => $telefonos) {
            echo '<tr>';
            echo '<td>' . esc_html($ciudad) . '</td>';
            echo '<td>' . (!empty($telefonos) ? implode('<br>', array_map('esc_html', $telefonos)) : '<em>Sin teléfonos</em>') . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        ?>
    </div>
<?php } ?>
