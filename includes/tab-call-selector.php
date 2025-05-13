<?php
if (!defined('ABSPATH')) exit;

function wp_city_contact_script_tab() {
    $selector_guardado = get_option('ccm_call_button_selector', '');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ccm_selector_nonce']) && wp_verify_nonce($_POST['ccm_selector_nonce'], 'guardar_selector')) {
        if (current_user_can('manage_options')) {
            $nuevo_selector = sanitize_text_field($_POST['ccm_selector']);
            update_option('ccm_call_button_selector', $nuevo_selector);
            echo '<div class="notice notice-success is-dismissible"><p>✅ Selector guardado correctamente.</p></div>';
        }
    }
    ?>
    <div class="wrap">
        <h1>📲 Selector de Botones de Llamada</h1>
        <p>Define cómo identificar los botones que deben modificar su número telefónico:</p>

        <form method="post">
            <?php wp_nonce_field('guardar_selector', 'ccm_selector_nonce'); ?>
            <label for="ccm_selector"><strong>Selector CSS:</strong></label><br>
            <input type="text" name="ccm_selector" id="ccm_selector" class="regular-text" value="<?php echo esc_attr($selector_guardado); ?>" required />
            <p class="description">Ejemplos: <code>.btn-llamar</code>, <code>a.llamada</code>, <code>[data-call]</code>, <code>#boton-llamar</code></p>

            <p><input type="submit" class="button button-primary" value="💾 Guardar Selector"></p>
        </form>
    </div>
    
    <hr>
<h2>🔍 Vista previa del selector</h2>
<p>Puedes verificar si el selector coincide con algún botón en esta página del admin (por ejemplo, si tienes botones personalizados).</p>

<button type="button" class="button" id="ccm-test-selector">🔬 Probar Selector</button>
<p id="ccm-test-result" style="margin-top:10px;"></p>

<script>
document.getElementById('ccm-test-selector')?.addEventListener('click', function () {
    const selector = document.getElementById('ccm_selector')?.value;
    if (!selector) return;

    let matched;
    try {
        matched = document.querySelectorAll(selector);
    } catch (e) {
        document.getElementById('ccm-test-result').innerHTML = "❌ Selector inválido: " + e.message;
        return;
    }

    if (matched.length === 0) {
        document.getElementById('ccm-test-result').innerHTML = "⚠️ No se encontró ningún elemento con ese selector.";
    } else {
        document.getElementById('ccm-test-result').innerHTML = "✅ Se encontraron <strong>" + matched.length + "</strong> elemento(s) con el selector.";
    }
});
</script>

    <?php
}
