<?php
if (!defined('ABSPATH')) exit;

add_action('wp_footer', function () {
    if (is_admin()) return;

    // 🔐 Cargar configuración
    $api_base  = rtrim(get_option('ccm_api_url'), '/');
    $api_token = get_option('ccm_api_token');

    if (!$api_base || !$api_token) return;

    $endpoint = esc_url($api_base . '/registro/formulario/store');
    $token    = esc_js($api_token);
    ?>
    <!-- 👾 Form Tracker activo -->
    <script>
    console.log("✅ Script de form-tracker.php cargado");

    document.addEventListener('DOMContentLoaded', () => {
        const dominio = window.location.hostname;

        function rastrearFormulario(form) {
            if (form.getAttribute('data-ccm-tracked') === 'processed') return;
            form.setAttribute('data-ccm-tracked', 'processed');
            console.log("📄 Formulario detectado para tracking");

            // 👂 Capturar botón de envío
            form.querySelectorAll('button[type="submit"]').forEach(boton => {
                boton.addEventListener('click', function () {
                    console.log("🚨 Click en botón de envío");

                    const formData = new FormData(form);
                    const entries = [...formData.entries()];
                    const dataString = entries.map(([k, v]) => `${k}: ${v}`).join(' | ');

                    fetch('https://api.ipify.org?format=json')
                        .then(res => res.json())
                        .then(ipdata => {
                            const payload = {
                                DATA: dataString,
                                DOMINIO_ORIGEN: dominio,
                                IP_ORIGEN: ipdata.ip
                            };

                            console.log("🛰️ Enviando a:", "<?php echo $endpoint; ?>");
                            console.log("📦 Payload:", payload);

                            return fetch("<?php echo $endpoint; ?>", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "Authorization": "Bearer <?php echo $token; ?>"
                                },
                                body: JSON.stringify(payload)
                            });
                        })
                        .then(res => res.json())
                        .then(res => {
                            console.log("📤 Registro enviado a API:", res);
                            if (res?.data?.ESTADO === "KO") {
                                console.warn("⚠️ Registro duplicado:", res?.data?.DESCRIPCION);
                            } else {
                                console.log("✅ Registro exitoso:", res?.data?.DESCRIPCION);
                            }
                        })
                        .catch(err => {
                            console.error("❌ Error en envío a API:", err);
                        });
                }, { once: true }); // Se asegura de ejecutar una sola vez
            });
        }

        // ⏳ Procesar formularios ya cargados
        setTimeout(() => {
            document.querySelectorAll('form').forEach(rastrearFormulario);
        }, 1000);

        // 👁️ Observar nuevos formularios (popups, AJAX, etc.)
        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1 && node.tagName === 'FORM') {
                        rastrearFormulario(node);
                    } else if (node.nodeType === 1) {
                        node.querySelectorAll('form').forEach(rastrearFormulario);
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });

        console.log("📋 Form tracker activo en:", dominio);
    });
    </script>
    <?php
});
