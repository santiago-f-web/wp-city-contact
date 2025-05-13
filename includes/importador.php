<?php
if (!defined('ABSPATH')) exit;

// ⚙️ Token y Endpoint desde opciones
$api_url = rtrim(get_option('ccm_api_url'), '/') . '/ciudad/contacto/store';
$token = get_option('ccm_api_token');

// 🧱 Mapa de ciudades con múltiples teléfonos (pegalo desde el Excel si querés convertir a array)
$provincias = [
    'ALAVA' => ['945566132'],
    'ALBACETE' => ['967811252'],
    'ALICANTE' => ['965063060', '965060197', '965063312', '956064359', '966266040', '965063060'],
    'ALMERIA' => ['950936172'],
    'ASTURIAS' => ['984204592'],
    'BADAJOZ' => ['924910474'],
    'BALEARES' => ['971910474'],
    'BARCELONA' => ['933939132', '930180646', '931780231', '931727837'],
    'CACERES' => ['927090434'],
    'CADIZ' => ['956922656'],
    'CANTABRIA' => ['942941178'],
    'CASTELLON' => ['964800046'],
    'CIUDAD REAL' => ['926674792'],
    'CORUÑA' => ['881246124'],
    'CUENCA' => ['969872769'],
    'GIRONA' => ['872987089'],
    'GRANADA' => ['958998290'],
    'GUADALAJARA' => ['949480044'],
    'HUELVA' => ['959872334', '959871796'],
    'HUESCA' => ['974564424'],
    'JAEN' => ['953892064'],
    'RIOJA' => ['941899742'],
    'LAS PALMAS' => ['928091463'],
    'LEON' => ['987791914'],
    'LLEIDA' => ['973090755'],
    'LUGO' => ['982986160'],
    'MADRID' => ['910052090', '910052688', '910054020'],
    'MALAGA' => ['951204082', '951204303', '951204332', '9511239308'],
    'MURCIA' => ['86818116', '868610323', '9689744603', '968978066', '968978073', '968979274'],
    'NAVARRA' => ['948987471'],
    'OURENSE' => ['988989779'],
    'PALENCIA' => ['979698788'],
    'SALAMANCA' => ['923994683'],
    'SEGOVIA' => ['921929621'],
    'SEVILLA' => ['955314611'],
    'TARRAGONA' => ['977270869', '977279220'],
    'TENERIFE' => ['922971728'],
    'TERUEL' => ['978085078'],
    'TOLEDO' => ['978085078'],
    'VALENCIA' => ['960659486', '960627494', '960653050'],
    'VALLADOLID' => ['983440073'],
    'VIGO' => ['986166738'],
    'VIZCAYA' => ['946535092'],
    'ZAMORA' => ['980989100'],
    'ZARAGOZA' => ['876015896']
];

// 📦 Construir relaciones
$relaciones = [];

foreach ($provincias as $ciudad => $numeros) {
    foreach ($numeros as $telefono) {
        $telefono = preg_replace('/\s+/', '', $telefono); // Eliminar espacios
        $relaciones[] = [
            'CIUDAD' => strtoupper($ciudad),
            'CONTACTO' => $telefono
        ];
    }
}

$response = wp_remote_post($api_url, [
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Content-Type'  => 'application/json'
    ],
    'body' => json_encode(['RELACIONES' => $relaciones])
]);

if (is_wp_error($response)) {
    echo '❌ Error: ' . $response->get_error_message();
} else {
    echo '<pre>';
    print_r(json_decode(wp_remote_retrieve_body($response), true));
    echo '</pre>';
}
