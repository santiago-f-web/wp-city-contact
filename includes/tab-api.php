<?php
if (!defined('ABSPATH')) exit;

// 🔐 Registrar las opciones antes de renderizar la pestaña
add_action('admin_init', function () {
    register_setting('ccm_api_settings', 'ccm_api_url');
    register_setting('ccm_api_settings', 'ccm_api_token');
});


function wp_city_contact_api_tab() {
    // Cargar valores actuales
    $api_url   = get_option('ccm_api_url', 'https://pre.onesat.rest.repexgroup.eu/api');
    $api_token = get_option('ccm_api_token', '');

    ?>
    <div class="wrap">
        <h1>⚙️ Ajustes de API</h1>
        <form method="post" action="options.php">
            <?php settings_fields('ccm_api_settings'); ?>
            <?php do_settings_sections('ccm_api_settings'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="ccm_api_url">URL base de la API</label></th>
                    <td>
                        <input type="text" name="ccm_api_url" id="ccm_api_url" value="<?php echo esc_attr($api_url); ?>" class="regular-text" required>
                        <p class="description">Ej: https://pre.onesat.rest.repexgroup.eu/api</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="ccm_api_token">Token de autenticación</label></th>
                    <td>
                        <input type="text" name="ccm_api_token" id="ccm_api_token" value="<?php echo esc_attr($api_token); ?>" class="regular-text" required>
                        <p class="description">Token de acceso a la API REST</p>
                    </td>
                </tr>
            </table>

            <?php submit_button('Guardar configuración'); ?>
        </form>
    </div>
    <?php
}
