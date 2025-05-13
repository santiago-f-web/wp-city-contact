<?php
if (!defined('ABSPATH')) exit;

function wp_city_contact_admin_menu() {
    add_menu_page(
        'Gestión de Contactos', 
        'City Contact', 
        'manage_options', 
        'wp-city-contact', 
        'wp_city_contact_selector_tab', 
        'dashicons-admin-site', 
        25
        );
    
    add_submenu_page(
        'wp-city-contact', 
        'Gestión de Números', 
        'Administrar Números', 
        'manage_options', 
        'wp-city-contact-management', 
        'wp_city_contact_management_tab'
        );
    
    add_submenu_page(
        'wp-city-contact', 
        'Selector de Botón de Llamada', 
        'Selector de Botón', 'manage_options', 
        'wp-city-contact-script', 
        'wp_city_contact_script_tab'
        );
        
     add_submenu_page(
        'wp-city-contact',
        'Ajustes de API',
        'Ajustes API',
        'manage_options',
        'wp-city-contact-api',
        'wp_city_contact_api_tab'
    );
    add_submenu_page(
        'wp-city-contact',
        'Actualización',
        'Actualizar',
        'manage_options',
        'wp-city-contact-update',
        'wp_city_contact_update_tab'
    );
    
}
add_action('admin_menu', 'wp_city_contact_admin_menu');
