<?php
if (!defined('ABSPATH')) exit;

class CCM_Github_Updater {
    private $plugin_file;
    private $plugin_slug;
    private $github_api;
    private $access_token;

    public function __construct($plugin_file) {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = plugin_basename($plugin_file);
        $this->github_api = 'https://api.github.com/repos/santiago-f-web/wp-city-contact';
        $this->access_token = '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'; // 🔐 PEGÁ ACÁ TU TOKEN

        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_update']);
        add_filter('plugins_api', [$this, 'plugin_info'], 10, 3);
    }

    public function check_for_update($transient) {
        if (empty($transient->checked)) return $transient;

        $current_version = get_plugin_data($this->plugin_file)['Version'];
        $response = wp_remote_get("{$this->github_api}/releases/latest", [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress',
                'Authorization' => 'token ' . $this->access_token
            ]
        ]);

        if (is_wp_error($response)) return $transient;

        $data = json_decode(wp_remote_retrieve_body($response));
        if (!$data || empty($data->zipball_url)) return $transient;

        $remote_version = ltrim($data->tag_name, 'v');
        $zip_url = $data->zipball_url . '?access_token=' . $this->access_token;

        if (version_compare($current_version, $remote_version, '<')) {
            $transient->response[$this->plugin_slug] = (object)[
                'slug' => dirname($this->plugin_slug),
                'plugin' => $this->plugin_slug,
                'new_version' => $remote_version,
                'url' => $data->html_url,
                'package' => $zip_url
            ];
        }

        return $transient;
    }

    public function plugin_info($res, $action, $args) {
        if ($action !== 'plugin_information' || $args->slug !== dirname($this->plugin_slug)) {
            return false;
        }

        $response = wp_remote_get("{$this->github_api}/releases/latest", [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress',
                'Authorization' => 'token ' . $this->access_token
            ]
        ]);

        if (is_wp_error($response)) return false;

        $data = json_decode(wp_remote_retrieve_body($response));
        return (object)[
            'name' => 'City Contact Manager',
            'slug' => dirname($this->plugin_slug),
            'version' => ltrim($data->tag_name, 'v'),
            'author' => '<a href="https://github.com/santiago-f-web">Santiago Fernández</a>',
            'homepage' => $data->html_url,
            'download_link' => $data->zipball_url . '?access_token=' . $this->access_token
        ];
    }
}
