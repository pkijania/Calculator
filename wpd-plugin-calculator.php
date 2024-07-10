<?php
/*
Plugin Name: Power calculator
Description: Simple calculator and correspondence plugin
Version: 1.1
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

    public function deliver_mail($power, $pump_info)
    {
        if (isset($_POST['cf-submitted'])) {
            $name = sanitize_text_field($_POST["cf-name"]);
            $email = sanitize_email($_POST["cf-email"]);
            $subject = sanitize_text_field($_POST["cf-subject"]);
            $message = esc_textarea($_POST["cf-message"]);

            $message .= "\n\nSzacowana ilość mocy potrzebna do ogrzania domu to: " . $power . " kW\n";
            $message .= "Odpowiednia pompa ciepła: \n" . $pump_info;

            $to = get_option('admin_email');
            $headers = "From: $name <$email>" . "\r\n";

            if (wp_mail($to, $subject, $message, $headers)) {
                echo '<div>';
                echo '<h4>Wyniki zostały wysłane na podany email.</h4>';
                echo '</div>';
            } else {
                echo 'Wystąpił nieznany błąd';
            }
        }
    }
}

function html_calculation_code()
{
    if (!isset($_POST['cf-count']) && !isset($_POST['cf-submitted']) && !isset($_POST['cf-result'])) {
        echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';

        echo '<h4>';
        echo 'Standard wykonania | zapotrzebowanie na ciepło [kWh/m2 rok] <br/>';
        echo '</h4>';

        echo '<p>';
        echo 'Starszy dom bez ocieplenia - od 170 do 200 <br/>';
        echo 'Starszy dom z ociepleniem - od 100 do 160 <br/>';
        echo 'Dom budowany obecnie - od 70 do 90 <br/>';
        echo 'Dom energooszczędny - od 60 do 70 <br/>';
        echo 'Dom pasywny - 15 <br/>';
        echo '</p>';

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

function html_form_code($power = null, $pump_info = null)
{
    if (isset($_POST['cf-count']) && !isset($_POST['cf-result']) && !isset($_POST['cf-submitted'])) {
        echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';

        echo '<h4>';
        echo 'Chcesz poznać szacowaną moc i cenę pompy ciepła? Wprowadź swoje dane, wyniki zostaną przesłane drogą mailową oraz pojawią się na stronie. <br/>';
        echo '</h4>';

        echo '<p>';
        echo 'Twoje imię i nazwisko (wymagane) <br/>';
        echo '<input type="text" name="cf-name" pattern="[a-zA-Z ]+" value="' . (isset($_POST["cf-name"]) ? esc_attr($_POST["cf-name"]) : '') . '" size="40" />';
        echo '</p>';

        echo '<p>';
        echo 'Twój email (wymagane) <br/>';
        echo '<input type="email" name="cf-email" value="' . (isset($_POST["cf-email"]) ? esc_attr($_POST["cf-email"]) : '') . '" size="40" />';
        echo '</p>';

        echo '<p>';
        echo 'Temat (wymagane) <br/>';
        echo '<input type="text" name="cf-subject" pattern="[a-zA-Z ]+" value="' . (isset($_POST["cf-subject"]) ? esc_attr($_POST["cf-subject"]) : '') . '" size="40" />';
        echo '</p>';

        echo '<p>';
        echo 'Twoja wiadomość <br/>';
        echo '<textarea rows="10" cols="35" name="cf-message">' . (isset($_POST["cf-message"]) ? esc_attr($_POST["cf-message"]) : '') . '</textarea>';
        echo '</p>';

        echo '<input type="hidden" name="cf-power" value="' . esc_attr($power) . '" />';
        echo '<input type="hidden" name="cf-pump-info" value="' . esc_attr(json_encode($pump_info)) . '" />';

        echo '<p><input type="submit" name="cf-submitted" value="Wyślij"></p>';
    }
}

function html_results_code($power, $pump_info)
{
    if (isset($_POST['cf-submitted']) && !isset($_POST['cf-result'])) {
        echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';

        echo '<p>';
        echo 'Szacowana ilość mocy potrzebna do ogrzania domu to: ' . esc_html($power) . ' kW<br>';
        echo 'Odpowiednia pompa ciepła: <br>';
        echo '<pre>';

        print_r($pump_info);

        echo '</pre>';
        echo '</p>';

        echo '<p><input type="submit" name="cf-result" value="Wróć na początek"></p>';
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
        html_form_code($power, $pump_info);
    } else if (isset($_POST['cf-submitted'])) {
        $power = $_POST['cf-power'];
        $pump_info = $_POST['cf-pump-info'];
        $result->deliver_mail($power, $pump_info);
        html_results_code($power, $pump_info);
    } else if (isset($_POST['cf-result'])) {
        html_calculation_code();
    } else {
        html_calculation_code();
    }

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
