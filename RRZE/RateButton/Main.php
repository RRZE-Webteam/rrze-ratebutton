<?php

namespace RRZE\RateButton;

defined('ABSPATH') || exit;

class Main
{
    public $plugin_basename;

    public function __construct($plugin_basename)
    {
        $this->plugin_basename = $plugin_basename;

        $shortcode = new Shortcode($this);
    }

}
