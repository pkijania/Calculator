<?php
/*
Plugin Name: Power calculator
Description: Simple calculator and correspondence plugin
Version: 1.0
Author: Przemysław Kijania
Author URI: https://przemyslawkijania.pl/
*/

class Calculator
{
    var $pump_power;
    var $area;
    var $standard;

    public function calculate_power($area, $standard)
    {
        $this->area = $area;
        $this->standard = $standard;
        $this->pump_power = ($this->area * $this->standard) / 2000;
        return $this->pump_power;
    }

    private function get_pump_models()
    {
        return [
            1 => [
                "name" => "Viessmann",
                "id" => 2109,
                "power" => 8.4,
                "price" => 5000,
            ],
            2 => [
                "name" => "Viessmann",
                "id" => 2110,
                "power" => 3.4,
                "price" => 3000,
            ],
            3 => [
                "name" => "Viessmann",
                "id" => 2111,
                "power" => 6.5,
                "price" => 4500,
            ],
            4 => [
                "name" => "Viessmann",
                "id" => 2112,
                "power" => 2.1,
                "price" => 2600,
            ],
        ];
    }

    private function get_pumps_array($pumps_models)
    {
        $pumps_array = [];
        foreach ($pumps_models as $pump) {
            $pumps_array[] = $pump['power'];
        }
        return $pumps_array;
    }

    private function find_suitable_pump($pumps_array, $pump_power)
    {
        $list = [];
        foreach ($pumps_array as &$pump_number) {
            if ($pump_number > $pump_power) {
                array_unshift($list, $pump_number - $pump_power);
            }
        }

        if (empty($list)) {
            return 'Brak odpowiedniej pompy ciepła';
        } else {
            return min($list) + $pump_power;
        }
    }

    public function get_pump_info($pump_power)
    {
        $pumps_models = $this->get_pump_models();
        $pumps_array = $this->get_pumps_array($pumps_models);
        $suitable_pump_power = $this->find_suitable_pump($pumps_array, $pump_power);

        if ($suitable_pump_power === 'Brak odpowiedniej pompy ciepła') {
            return $suitable_pump_power;
        } else {
            $search_pumps = ["power" => $suitable_pump_power];
            $results = array_filter($pumps_models, function ($pumps_models) use ($search_pumps) {
                return count(array_intersect_assoc($search_pumps, $pumps_models)) == count($search_pumps);
            });
            return $results;
        }
    }

    public function deliver_mail()
    {
        // If the submit button is clicked, send the email
        if (isset($_POST['cf-submitted'])) {

            // Sanitize form values
            $name = sanitize_text_field($_POST["cf-name"]);
            $email = sanitize_email($_POST["cf-email"]);
            $subject = sanitize_text_field($_POST["cf-subject"]);
            $message = esc_textarea($_POST["cf-message"]);

            // Get the blog administrator's email address
            $to = get_option('admin_email');

            $headers = "From: $name <$email>" . "\r\n";

            // If email has been process for sending, display a success message
            if (wp_mail($to, $subject, $message, $headers)) {
                echo '<div>';
                echo '<p>Thanks for contacting me, expect a response soon.</p>';
                echo '</div>';
            } else {
                echo 'An unexpected error occurred';
            }
        }
    }
}

function html_calculation_code()
{
    if (!isset($_POST['cf-submitted']) && !isset($_POST['cf-count'])) {
        echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';

        echo '<p>';
        echo 'Powierzchnia ogrzewania [m2] (wymagane) <br/>';
        echo '<input type="text" name="cf-power" pattern="[0-9]+(\.[0-9]{1,2})?" value="' . (isset($_POST["cf-power"]) ? esc_attr($_POST["cf-power"]) : '') . '" size="40" />';
        echo '</p>';

        echo '<p>';
        echo 'Standard wykonania [kWh/m2 rok] (wymagane) <br/>';
        echo '<input type="text" name="cf-standard" pattern="[0-9]+(\.[0-9]{1,2})?" value="' . (isset($_POST["cf-standard"]) ? esc_attr($_POST["cf-standard"]) : '') . '" size="40" />';
        echo '</p>';

        echo '<p><input type="submit" name="cf-count" value="Oblicz"></p>';
        echo '</form>';
    }
}

function html_form_code()
{
    if (isset($_POST['cf-count'])) {
        echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';

        echo '<p>';
        echo 'Twoje imię (wymagane) <br/>';
        echo '<input type="text" name="cf-name" pattern="[a-zA-Z ]+" value="' . (isset($_POST["cf-name"]) ? esc_attr($_POST["cf-name"]) : '') . '" size="40" />';
        echo '</p>';

        echo '<p>';
        echo 'Twój Email (wymagane) <br/>';
        echo '<input type="email" name="cf-email" value="' . (isset($_POST["cf-email"]) ? esc_attr($_POST["cf-email"]) : '') . '" size="40" />';
        echo '</p>';

        echo '<p>';
        echo 'Temat (wymagane) <br/>';
        echo '<input type="text" name="cf-subject" pattern="[a-zA-Z ]+" value="' . (isset($_POST["cf-subject"]) ? esc_attr($_POST["cf-subject"]) : '') . '" size="40" />';
        echo '</p>';

        echo '<p>';
        echo 'Twoja wiadomość (wymagane) <br/>';
        echo '<textarea rows="10" cols="35" name="cf-message">' . (isset($_POST["cf-message"]) ? esc_attr($_POST["cf-message"]) : '') . '</textarea>';
        echo '</p>';

        echo '<p><input type="submit" name="cf-submitted" value="Send"></p>';
        echo '</form>';
    }
}

function cf_shortcode()
{
    ob_start();
    $result = new Calculator();

    if (isset($_POST['cf-count'])) {
        $power = $result->calculate_power($_POST['cf-power'], $_POST['cf-standard']);
        $pump_info = $result->get_pump_info($power);

        echo 'Szacowana ilość mocy potrzebna do ogrzania domu to: ' . $power . ' kW<br>';
        echo 'Odpowiednia pompa ciepła: <br>';
        echo '<pre>';
        print_r($pump_info);
        echo '</pre>';
    }

    $result->deliver_mail();
    html_calculation_code();
    html_form_code();

    return ob_get_clean();
}

add_action('wp_mail_failed', 'onMailError', 10, 1);
function onMailError($wp_error)
{
    echo "<pre>";
    print_r($wp_error);
    echo "</pre>";
}

add_shortcode('power_calculator', 'cf_shortcode');
?>
