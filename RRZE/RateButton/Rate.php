<?php

namespace RRZE\RateButton;

defined('ABSPATH') || exit;

class Rate
{
    public function __construct()
    {
        add_action('wp_ajax_rrze_rate_process', [$this, 'rate_process']);
        add_action('wp_ajax_nopriv_rrze_rate_process', [$this, 'rate_process']);
    }

    public function rate_process()
    {
        $post_ID = isset($_POST['id']) ? absint($_POST['id']) : 0;
        $rate_status = isset($_POST['status']) ? absint($_POST['status']) : 0;
        $nonce_token = isset($_POST['nonce']) ? $_POST['nonce'] : '';

        if (! wp_verify_nonce($nonce_token, 'rrze-rate-btn' . $post_ID)) {
            wp_send_json_error(__('Error: Can not verify nonce!', 'rrze-ratebutton'));
        }

        if (empty($post_ID)) {
            wp_send_json_error(__('Error: The variable post_ID is empty!', 'rrze-ratebutton'));
        }

        $meta_key = '_rrze_rating';
        $meta_value = get_post_meta($post_ID, $meta_key, true);
        $count = empty($meta_value) ? 0 : $meta_value;

        $cookie_name = 'rrze_rated_';

        $data = [
            "id" => $post_ID,
            "count" => $count,
            "type" => 'process',
            "meta_key" => $meta_key,
            "cookie_name" => $cookie_name
        ];

        $response = [];

        switch ($rate_status) {
            case 1:
                $response = [
                    'content' => $this->getContent($data)
                ];
                break;
            default:
                $response = [
                    'content' => null
                ];
        }

        wp_send_json_success($response);
    }

    public function getContent(array $data)
    {
        return $this->byCookie($data);
    }

    protected function byCookie(array $data)
    {
        extract($data);
        $output = '';

        if ($type == 'post') {
            if (! isset($_COOKIE[$cookie_name . $id])) {
                $output = $this->get_template($data, 1);
            } else {
                $output = $this->get_template($data, 2);
            }
        } elseif ($type == 'process') {
            if (! isset($_COOKIE[$cookie_name . $id])) {
                ++$count;
                setcookie($cookie_name . $id, time(), 3153600000, '/');
            }

            $output = $this->number_format($this->update_meta_data($id, $meta_key, $count));
        }

        return $output;
    }

    protected function update_meta_data($id, $meta_key, $data)
    {
        update_post_meta($id, $meta_key, $data);
        update_postmeta_cache([$id]);

        return $data;
    }

    protected function number_format($value)
    {
        $value = absint($value);
        $plus = $value ? '+' : '';
        if ($value >= 1000) {
            $new_value = number_format_i18n(round($value / 1000, 2), 2) . 'k' . $plus;
        } else {
            $new_value = $value . $plus;
        }
        return $new_value;
    }

    protected function get_template(array $data, $status)
    {
        $button_class = 'rrze-rate-btn';

        $button_class .= ' rrze-rate-image';

        $button_class .= ' rrze-rate-btn-' . $data['id'];

        $container_class = 'rrze-rate-container';

        switch ($status) {
            case 1:
                $container_class .= ' rrze-rate-is-not-rated';
                break;
            case 2:
                $container_class .= ' rrze-rate-is-already-rated';
        }

        $counter = '<span class="count-box">'. $this->number_format($data['count']) .'</span>';

        $tpl_data = [
            "ID" => $data['id'],
            "counter" => $counter,
            "status" => $status,
            "style" => $data['style'],
            "container_class" => $container_class,
            "button_class" => $button_class
        ];

        return $this->set_template($tpl_data);
    }

    protected function set_template(array $data)
    {
        ob_start();
        extract($data); ?>
        <div class="rrze-rate-wrap <?php echo $style; ?>">
            <div class="<?php echo $container_class; ?>">
                <button type="button"
                    data-rrze-rate-id="<?php echo $ID; ?>"
                    data-rrze-rate-style="<?php echo $style; ?>"
                    data-rrze-rate-nonce="<?php echo wp_create_nonce('rrze-rate-btn' . $ID); ?>"
                    data-rrze-rate-status="<?php echo $status; ?>" class="<?php echo $button_class; ?>"></button><?php echo $counter; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

}
