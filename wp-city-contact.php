<?php
/**
 * Plugin Name: City Contact Manager
 * Plugin URI:  https://example.com
 * Description: Plugin para gestionar contactos según ciudad.
 * Version:     1.0.0
 * Author:      Tu Nombre
 * License:     GPL-2.0+
 */

if (!defined('ABSPATH')) exit; // Seguridad



// Definir constantes del plugin
define('WP_CITY_CONTACT_PATH', plugin_dir_path(__FILE__));
define('WP_CITY_CONTACT_URL', plugin_dir_url(__FILE__));
define('API_TOKEN', 'GKkZFKgPxStYDk9ogF9DxsXVFN9JR0Sj5g8WMZutOqHp4FtYY2LM3ZdVE0Eq');

// Incluir archivos principales
require_once WP_CITY_CONTACT_PATH . 'includes/menu.php';
require_once WP_CITY_CONTACT_PATH . 'includes/api-handler.php';
require_once WP_CITY_CONTACT_PATH . 'includes/tab-selector.php';
require_once WP_CITY_CONTACT_PATH . 'includes/tab-management.php';
require_once WP_CITY_CONTACT_PATH . 'includes/ajax-management.php';
require_once WP_CITY_CONTACT_PATH . 'includes/ajax-save-numbers.php';
require_once WP_CITY_CONTACT_PATH . 'includes/tab-call-selector.php';
require_once WP_CITY_CONTACT_PATH . 'includes/tab-api.php';
require_once WP_CITY_CONTACT_PATH . 'includes/form-tracker.php';
require_once WP_CITY_CONTACT_PATH . 'includes/tab-update-checker.php';







// Cargar CSS y JS globales
add_action('admin_enqueue_scripts', function ($hook) {
    // Solo cargar en el tab principal del plugin
    if ($hook !== 'toplevel_page_wp-city-contact') return;

    wp_enqueue_style('city-contact-style', WP_CITY_CONTACT_URL . 'assets/style.css');
    wp_enqueue_script('city-contact-script', WP_CITY_CONTACT_URL . 'assets/script.js', [], time(), true);

    $ciudad_actual = get_option('ccm_ciudad_actual');
    if (!is_string($ciudad_actual)) {
        $ciudad_actual = '';
    }

    wp_localize_script('city-contact-script', 'cityContactAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'ciudad_actual' => $ciudad_actual
    ]);
});


// Cargar el JS del tab de administración de números
function wp_city_contact_load_management_js($hook) {
    if ($hook === 'city-contact_page_wp-city-contact-management') {
        wp_enqueue_script('management-js', WP_CITY_CONTACT_URL . 'assets/management.js', ['jquery'], null, true);

        wp_localize_script('management-js', 'cityContactAjax', [
            'ajax_url' => admin_url('admin-ajax.php')
        ]);
    }
}
add_action('admin_enqueue_scripts', 'wp_city_contact_load_management_js');



add_action('wp_footer', function () {
    if (is_admin()) return;

    $selector = get_option('ccm_call_button_selector');
    $telefonos_por_ciudad = get_option('ccm_telefonos_por_ciudad', []);
    $ciudad = get_option('ccm_ciudad_actual', '');

    if (!$selector || !$ciudad) return;

    $selector_js = json_encode($selector);
    $ciudad_js = json_encode($ciudad);
    $ajax_url = admin_url('admin-ajax.php');

    echo "<script id='ccm-phone-replacer'>
(function(){
    const ciudad = $ciudad_js;
    const selector = $selector_js;
    const dominio = window.location.hostname;
    const ajaxUrl = '$ajax_url';

    const keyNum = `ccm_numero_\${dominio}`;
    const keyCiudad = `ccm_ciudad_\${dominio}`;

    let numeroGuardado = localStorage.getItem(keyNum);
    let ciudadGuardada = localStorage.getItem(keyCiudad);

    function setNumeroLocal(numero) {
        localStorage.setItem(keyNum, numero);
        localStorage.setItem(keyCiudad, ciudad);
        numeroGuardado = numero;
        ciudadGuardada = ciudad;
    }

    function reemplazarBotones(numero) {
        const botones = document.querySelectorAll(selector);
        console.log('🔁 Reemplazando en', botones.length, 'botones');
        botones.forEach(btn => {
            if (btn.tagName === 'A') {
                btn.href = 'tel:' + numero;
            }
            btn.textContent = numero;

            btn.addEventListener('click', () => {
                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'ccm_registrar_llamada',
                        contacto: numero,
                        dominio: dominio
                    })
                })
                .then(res => res.json())
                .then(data => console.log('📞 Registro enviado:', data))
                .catch(err => console.error('❌ Error al registrar llamada:', err));
            }, { once: true });
        });
    }

    function asignarDesdeAPI(intentos = 0) {
        fetch(ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'get_city_contacts',
                city: ciudad
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data.phones?.length) {
                const nuevo = data.data.phones[0];

                if (numeroGuardado === nuevo && intentos < 2) {
                    console.warn('🔁 Número repetido. Reintentando...');
                    asignarDesdeAPI(intentos + 1);
                } else {
                    setNumeroLocal(nuevo);
                    reemplazarBotones(nuevo);
                }
            } else {
                console.error('❌ No se pudo obtener teléfono.');
            }
        })
        .catch(err => console.error('❌ Error API:', err));
    }

    function verificarYAsignar() {
        if (numeroGuardado && ciudadGuardada === ciudad) {
            reemplazarBotones(numeroGuardado);
        } else {
            asignarDesdeAPI();
        }
    }

    // ▶️ Arrancar
    verificarYAsignar();

    // 👁️‍🗨️ Observar nuevos botones en DOM (popups, AJAX)
  // 👁️‍🧠 Observador solo si aparece un nuevo botón en un popup o contenido dinámico
const observer = new MutationObserver((mutationsList) => {
    for (const mutation of mutationsList) {
        // Buscamos si se agregó un nodo que contenga el botón con nuestro selector
        for (const node of mutation.addedNodes) {
            if (!(node instanceof HTMLElement)) continue;

            // Buscar botón dentro del nodo insertado
            const nuevoBoton = node.matches(selector) ? node : node.querySelector(selector);
            if (nuevoBoton && numeroGuardado) {
                console.log('⚡ Botón dinámico detectado en popup. Reemplazando...');
                reemplazarBotones(numeroGuardado);
                return; // no sigas iterando más
            }
        }
    }
});

observer.observe(document.body, {
    childList: true,
    subtree: true
});



})();
</script>";




});
