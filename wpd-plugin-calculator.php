<?php
/*
Plugin Name: Power calculator
Description: Simple calculator and correspondence plugin
Version: 1.4
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
                "Nazwa" => "Viessmann",
                "Id" => 2109,
                "Moc" => 8.4,
                "Cena [PLN]" => 5000,
            ],
            2 => [
                "Nazwa" => "Panasonic",
                "Id" => 2110,
                "Moc" => 3.4,
                "Cena [PLN]" => 3000,
            ],
            3 => [
                "Nazwa" => "Viessmann",
                "Id" => 2111,
                "Moc" => 6.5,
                "Cena [PLN]" => 4500,
            ],
            4 => [
                "Nazwa" => "Panasonic",
                "Id" => 2112,
                "Moc" => 2.1,
                "Cena [PLN]" => 2600,
            ],
        ];
    }

    private function get_pumps_array($pumps_models)
    {
        $pumps_array = [];
        foreach ($pumps_models as $pump) {
            $pumps_array[] = $pump['Moc'];
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
            return 'Brak odpowiedniej pompy ciepla';
        } else {
            return min($list) + $pump_power;
        }
    }

    public function get_pump_info($pump_power)
    {
        $pumps_models = $this->get_pump_models();
        $pumps_array = $this->get_pumps_array($pumps_models);
        $suitable_pump_power = $this->find_suitable_pump($pumps_array, $pump_power);

        if ($suitable_pump_power === 'Brak odpowiedniej pompy ciepla') {
            return $suitable_pump_power;
        } else {
            $search_pumps = ["Moc" => $suitable_pump_power];
            $results = array_filter($pumps_models, function ($pumps_models) use ($search_pumps) {
                return count(array_intersect_assoc($search_pumps, $pumps_models)) == count($search_pumps);
            });
            return $results;
        }
    }

    public function deliver_mail($power, $name_of_pump, $id, $efficiency, $price)
    {
        if (isset($_POST['cf-submitted'])) {
            $name = sanitize_text_field($_POST["cf-name"]);
            $email = sanitize_email($_POST["cf-email"]);
            $subject = sanitize_text_field($_POST["cf-subject"]);
            $message = esc_textarea($_POST["cf-message"]);

            $message .= "Wyniki dla wyceny pompy ciepła:";
            $message .= "\nSzacowana ilość mocy potrzebna do ogrzania domu to: " . $power . " kW";
            $message .= "\nSzczegóły dotyczące wybranej pompy ciepła";
            $message .= "\nNazwa: " . $name_of_pump;
            $message .= "\nId: " . $id;
            $message .= "\nMoc: " . $efficiency . " kW";;
            $message .= "\nCena: " . $price . " zł";
            $subject = "Wycena pompy ciepła";

            $to = get_option('admin_email');
            $headers = "From: $name <$email>" . "\r\n";
            $headers .= "CC: $email" . "\r\n";

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
        echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post" onsubmit="return validateForm()">';

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

        echo '<h4>';
        echo 'Proszę wpisać odpowiednie dane <br/>';
        echo '</h4>';

        echo '<p>';
        echo 'Powierzchnia ogrzewania [m2] (wymagane) <br/>';
        echo '<input type="text" name="cf-power" pattern="[0-9]+(\.[0-9]{1,2})?" value="' . (isset($_POST["cf-power"]) ? esc_attr($_POST["cf-power"]) : '') . '" size="40" required/>';
        echo '<span class="error" style="color:red;display:none">To pole nie może być puste</span>';
        echo '</p>';

        echo '<p>';
        echo 'Standard wykonania [kWh/m2 rok] (wymagane) <br/>';
        echo '<input type="text" name="cf-standard" pattern="[0-9]+(\.[0-9]{1,2})?" value="' . (isset($_POST["cf-standard"]) ? esc_attr($_POST["cf-standard"]) : '') . '" size="40" required/>';
        echo '<span class="error" style="color:red;display:none">To pole nie może być puste</span>';
        echo '</p>';

        echo '<p><input type="submit" name="cf-count" value="Oblicz"></p>';
        echo '</form>';
    }
}

function html_form_code($power = null, $name_of_pump = null, $id = null, $efficiency = null, $price = null)
{
    if (isset($_POST['cf-count']) && !isset($_POST['cf-result']) && !isset($_POST['cf-submitted'])) {
        echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post" onsubmit="return validateForm()">';

        echo '<h4>';
        echo 'Chcesz poznać szacowaną moc i cenę pompy ciepła? Wprowadź swoje dane, wyniki zostaną przesłane drogą mailową oraz pojawią się na stronie. <br/>';
        echo '</h4>';

        echo '<p>';
        echo 'Twoje imię i nazwisko (wymagane) <br/>';
        echo '<input type="text" name="cf-name" pattern="[a-zA-Z ]+" value="' . (isset($_POST["cf-name"]) ? esc_attr($_POST["cf-name"]) : '') . '" size="40" required />';
        echo '<span class="error" style="color:red;display:none">To pole nie może być puste</span>';
        echo '</p>';

        echo '<p>';
        echo 'Twój email (wymagane) <br/>';
        echo '<input type="email" name="cf-email" value="' . (isset($_POST["cf-email"]) ? esc_attr($_POST["cf-email"]) : '') . '" size="40" required />';
        echo '<span class="error" style="color:red;display:none">To pole nie może być puste</span>';
        echo '</p>';

        echo '<p>';
        echo 'Dodatkowe informacje <br/>';
        echo '<textarea rows="10" cols="35" name="cf-message">' . (isset($_POST["cf-message"]) ? esc_attr($_POST["cf-message"]) : '') . '</textarea>';
        echo '<span class="error" style="color:red;display:none">To pole nie może być puste</span>';
        echo '</p>';

        echo '<input type="hidden" name="cf-power" value="' . esc_attr($power) . '" />';
        echo '<input type="hidden" name="cf-div-name" value="' . esc_attr($name_of_pump) . '" />';
        echo '<input type="hidden" name="cf-div-id" value="' . esc_attr($id) . '" />';
        echo '<input type="hidden" name="cf-div-efficieny" value="' . esc_attr($efficiency) . '" />';
        echo '<input type="hidden" name="cf-div-price" value="' . esc_attr($price) . '" />';

        echo '<p><input type="submit" name="cf-submitted" value="Wyślij"></p>';
        echo '</form>';
    }
}

function html_results_code($power, $name_of_pump, $id, $efficiency, $price)
{
    if (isset($_POST['cf-submitted']) && !isset($_POST['cf-result'])) {
        echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';

        echo '<p>';
        echo 'Szacowana ilość mocy potrzebna do ogrzania domu to: ' . esc_html($power) . ' kW<br>';
        echo 'Szczegóły dotyczące wybranej pompy ciepła';

        echo '<pre>';
        echo '<input value="Nazwa" />';
        echo '<input value="Numer id"/>';
        echo '<input value="Moc w kW" />';
        echo '<input value="Cena w zł" />';
        echo '</pre>';

        echo '<pre>';
        echo '<input name="cf-div-name" value="' . esc_attr($name_of_pump) . '" />';
        echo '<input name="cf-div-id" value="' . esc_attr($id) . '" />';
        echo '<input name="cf-div-efficieny" value="' . esc_attr($efficiency) . '" />';
        echo '<input name="cf-div-price" value="' . esc_attr($price) . '" />';
        echo '</pre>';

        echo '</p>';
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

        // Pobieranie danych o odpowiedniej pompie
        if (is_array($pump_info) && !empty($pump_info)) {
            $pump_info = reset($pump_info); // Pobieranie pierwszego elementu tablicy
            $name_of_pump = $pump_info["Nazwa"];
            $id = $pump_info["Id"];
            $efficiency = $pump_info["Moc"];
            $price = $pump_info["Cena [PLN]"];
        } else {
            $name_of_pump = '';
            $id = '';
            $efficiency = '';
            $price = '';
        }

        html_form_code($power, $name_of_pump, $id, $efficiency, $price);
    } else if (isset($_POST['cf-submitted'])) {
        $power = $_POST['cf-power'];
        $pump_info = json_decode(stripslashes($_POST['cf-pump-info']), true);
        $name_of_pump = $_POST['cf-div-name'];
        $id = $_POST['cf-div-id'];
        $efficiency = $_POST['cf-div-efficieny'];
        $price = $_POST['cf-div-price'];
        $result->deliver_mail($power, $name_of_pump, $id, $efficiency, $price);
        html_results_code($power, $name_of_pump, $id, $efficiency, $price);
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
