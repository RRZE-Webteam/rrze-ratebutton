<?php

namespace RRZE\RateButton;

use RRZE\RateButton\Main;

defined('ABSPATH') || exit;

class Shortcode
{
    protected $main;

    protected $rate;

    public function __construct(Main $main)
    {
        $this->main = $main;
        $this->rate = new Rate();

        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        add_shortcode('ratebutton', [$this, 'output']);
    }

    public function enqueue_scripts()
    {
        wp_register_style('rrze-rate-btn', plugins_url('assets/css/rrze-rate-btn.min.css', $this->main->plugin_basename));

        wp_register_script('rrze-rate-btn', plugins_url('assets/js/rrze-rate-btn.min.js', $this->main->plugin_basename), ['jquery'], false, true);
        wp_localize_script('rrze-rate-btn', 'rrze_rate_params', [
            'ajax_url' => admin_url('admin-ajax.php')
        ]);
    }

    public function output($atts)
    {
        $args = shortcode_atts([

        ], $atts);

        $output = '';

        $parsed_args = $this->parseArgs($args);
        if (!is_null($parsed_args)) {
            wp_enqueue_style('rrze-rate-btn');
            wp_enqueue_script('rrze-rate-btn');
            $output = $this->rate->getContent($parsed_args);
        }

        return $output;
    }

    protected function parseArgs($args = [])
    {
        $post = get_post();

        if (is_null($post)) {
            return null;
        }

        extract($args);

        $meta_key = '_rrze_rating';
        $meta_value = get_post_meta($post->ID, $meta_key, true);
        $count = empty($meta_value) ? 0 : $meta_value;

        $cookie_name = 'rrze_rated_';

        $defaults = [
            "id" => $post->ID,
            "count" => $count,
            "type" => 'post',
            "meta_key" => $meta_key,
            "cookie_name" => $cookie_name,
            "style" => 'rrze-rate-default'
        ];

        return wp_parse_args($args, $defaults);
    }
}
