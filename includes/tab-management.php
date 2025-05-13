<?php
if (!defined('ABSPATH')) exit;

function wp_city_contact_management_tab() {
    ?>
    <div class="wrap" id="wp-city-contact-admin">
        <h1>📞 Gestión de Contactos por Ciudad</h1>

        <table class="widefat fixed striped" id="city-contact-table">
            <thead>
                <tr>
                    <th>Ciudad</th>
                    <th>Número</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="contact-table-body">
                <tr><td colspan="3">Cargando datos...</td></tr>
            </tbody>
        </table>

        <h2 style="margin-top: 40px;">➕ Añadir nuevo contacto</h2>
        <form id="add-contact-form">
            <table class="form-table">
                <tr>
                    <th><label for="ciudad">Ciudad</label></th>
                    <td><input type="text" id="ciudad" name="ciudad" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="contacto">Número</label></th>
                    <td><input type="text" id="contacto" name="contacto" class="regular-text" required></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input type="submit" class="button button-primary" value="Agregar">
                    </td>
                </tr>
            </table>
        </form>

        <div id="management-message" style="margin-top: 20px;"></div>
    </div>
    <?php
}
