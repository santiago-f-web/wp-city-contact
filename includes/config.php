<?php
if (!defined('ABSPATH')) exit;

// ⚙️ API URL desde ajustes (editable desde tab-api.php)
$GLOBALS['ccm_api_url'] = trailingslashit(get_option('ccm_api_url', 'https://onesat.rest.repexgroup.eu/api/'));

// 🔐 API TOKEN desde ajustes también
$GLOBALS['ccm_api_token'] = get_option('ccm_api_token', 'gnzjjgeRXfTaS7Hcr7Cx7u9SHwuG4Ctw60exDjmZWRMgnLZH66mocz0qwaA1');
