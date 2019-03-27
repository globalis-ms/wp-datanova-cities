<?php

namespace Globalis\WP\Datanova;

class Cities
{
    private static $instance = null;

    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function hooks()
    {
        $functions = [
            'get_cities' => 'getCitiesAjax',
            'get_cities_names' => 'getCitiesNamesAjax',
            'is_city_valid' => 'isCityValidAjax',
        ];
        foreach ($functions as $ajax_name => $callback) {
            add_action('wp_ajax' . '_datanova_' . $ajax_name, [$this, $callback]);
            add_action('wp_ajax_nopriv' . '_datanova_' . $ajax_name, [$this, $callback]);
        }
    }

    public function getCities($postcode)
    {
        global $wpdb;

        $query = $wpdb->prepare("
            SELECT `ville`, `code_postal`, `code_insee`
            FROM `" . Database::instance()->getTable() . "`
            WHERE `code_postal` = %s
            ORDER BY `ville`
        ", $postcode);

        return $wpdb->get_results($query, ARRAY_A);
    }

    public function getCitiesAjax()
    {
        if (empty($_POST['postcode'])) {
            wp_send_json_error('Missing POST parameter: postcode');
            exit;
        }

        wp_send_json_success($this->getCities($_POST['postcode']));

        exit;
    }

    public function getCitiesNames($postcode)
    {
        $citiesNames = [];
        $results = self::instance()->getCities($postcode);

        foreach ($results as $result) {
            $citiesNames[] = $result['ville'];
        }

        return $citiesNames;
    }

    public function getCitiesNamesAjax()
    {
        if (empty($_POST['postcode'])) {
            wp_send_json_error('Missing POST parameter: postcode');
            exit;
        }

        wp_send_json_success($this->getCitiesNames($_POST['postcode']));

        exit;
    }

    public function isCityValid($name, $postcode)
    {
        global $wpdb;

        $query = $wpdb->prepare("
            SELECT `ville`, `code_postal`, `code_insee`
            FROM `" . Database::instance()->getTable() . "`
            WHERE `ville` = %s AND `code_postal` = %s
        ", $name, $postcode);

        $result = $wpdb->get_results($query, ARRAY_A);

        return is_array($result) && count($result) >= 1;
    }

    public function isCityValidAjax()
    {
        if (empty($_POST['name'])) {
            wp_send_json_error('Missing POST parameter: name');
            exit;
        }

        if (empty($_POST['postcode'])) {
            wp_send_json_error('Missing POST parameter: postcode');
            exit;
        }

        wp_send_json_success($this->isCityValid($_POST['name'], $_POST['postcode']));

        exit;
    }
}
