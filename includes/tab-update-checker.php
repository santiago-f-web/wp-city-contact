<?php
if (!defined('ABSPATH')) exit;

function wp_city_contact_update_tab() {
    $update_available = false;
    $plugin_slug = 'city-contact-manager/wp-city-contact.php'; // Cambia si el path del plugin cambia
    // Se actualizo
    // Forzar chequeo si el usuario lo solicitó
    if (isset($_POST['ccm_check_update']) && check_admin_referer('ccm_check_update_action', 'ccm_nonce')) {
        wp_clean_plugins_cache(true);
        $update_plugins = get_site_transient('update_plugins');

        if (!empty($update_plugins->response[$plugin_slug])) {
            $update_available = true;
            echo '<div class="notice notice-success"><p>🚀 ¡Actualización disponible!</p></div>';
        } else {
            echo '<div class="notice notice-info"><p>✅ El plugin está actualizado.</p></div>';
        }
    }

    // Procesar actualización manual
    if (isset($_POST['ccm_run_update']) && check_admin_referer('ccm_update_now_action', 'ccm_nonce_update')) {
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        $upgrader = new Plugin_Upgrader();
        $upgrader->upgrade($plugin_slug);
        echo '<div class="notice notice-success"><p>🛠️ Actualización ejecutada. Recarga la página.</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>📦 Actualizador del Plugin</h1>

        <form method="post">
            <?php wp_nonce_field('ccm_check_update_action', 'ccm_nonce'); ?>
            <p><input type="submit" class="button button-secondary" name="ccm_check_update" value="🔍 Buscar actualizaciones"></p>
        </form>

        <?php if ($update_available): ?>
            <form method="post" style="margin-top:20px;">
                <?php wp_nonce_field('ccm_update_now_action', 'ccm_nonce_update'); ?>
                <p><input type="submit" class="button button-primary" name="ccm_run_update" value="⬇️ Actualizar ahora"></p>
            </form>
        <?php endif; ?>
    </div>
    <?php
}