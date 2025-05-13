<?php
if (!defined('ABSPATH')) exit;

function wp_city_contact_update_tab() {
    $plugin_data = get_plugin_data(WP_CITY_CONTACT_PATH . 'wp-city-contact.php');
    $current_version = $plugin_data['Version'];
    $github_repo = 'https://api.github.com/repos/santiago-f-web/wp-city-contact/releases/latest';

    // Obtener la última versión
    $response = wp_remote_get($github_repo, [
        'headers' => [
            'User-Agent' => 'WordPress/' . get_bloginfo('version'),
        ]
    ]);

    $latest_version = '';
    $zip_url = '';
    $error = false;

    if (!is_wp_error($response)) {
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $latest_version = $body['tag_name'] ?? '';
        $zip_url = $body['zipball_url'] ?? '';
    } else {
        $error = true;
    }

    ?>
    <div class="wrap">
        <h1>📦 Actualización del plugin</h1>

        <p><strong>Versión instalada:</strong> <?php echo esc_html($current_version); ?></p>

        <?php if ($error): ?>
            <p style="color:red;">❌ No se pudo conectar a GitHub para verificar actualizaciones.</p>
        <?php elseif (version_compare($latest_version, $current_version, '>')): ?>
            <p style="color:green;">
                ✅ ¡Nueva versión disponible! <strong><?php echo esc_html($latest_version); ?></strong>
            </p>
            <form method="post">
                <input type="hidden" name="ccm_update_zip_url" value="<?php echo esc_url($zip_url); ?>">
                <button type="submit" class="button button-primary">Actualizar ahora</button>
            </form>
        <?php else: ?>
            <p>🆗 El plugin está actualizado.</p>
        <?php endif; ?>
    </div>
    <?php
}

// Proceso de actualización
add_action('admin_init', function () {
    if (!current_user_can('manage_options')) return;

    if (isset($_POST['ccm_update_zip_url']) && check_admin_referer()) {
        $zip_url = esc_url_raw($_POST['ccm_update_zip_url']);

        include_once ABSPATH . 'wp-admin/includes/file.php';
        include_once ABSPATH . 'wp-admin/includes/misc.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $temp_file = download_url($zip_url);

        if (is_wp_error($temp_file)) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error"><p>Error al descargar actualización.</p></div>';
            });
            return;
        }

        $upgrader = new Plugin_Upgrader();
        $result = $upgrader->install($zip_url);

        if (is_wp_error($result)) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error"><p>Error al instalar actualización.</p></div>';
            });
        } else {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-success"><p>🎉 Plugin actualizado correctamente.</p></div>';
            });
        }
    }
});
