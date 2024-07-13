<?php
/*
Plugin Name: Power calculator
Description: Simple calculator and correspondence plugin
Version: 1.6
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
                "Link" => "https://www.viessmann.pl/pl/produkty/pompy-ciepla/vitocal-222-s.html",
            ],
            2 => [
                "Nazwa" => "Panasonic",
                "Id" => 2110,
                "Moc" => 3.4,
                "Cena [PLN]" => 3000,
                "Link" => "https://www.viessmann.pl/pl/produkty/pompy-ciepla/vitocal-222-s.html",
            ],
            3 => [
                "Nazwa" => "Viessmann",
                "Id" => 2111,
                "Moc" => 6.5,
                "Cena [PLN]" => 4500,
                "Link" => "https://www.viessmann.pl/pl/produkty/pompy-ciepla/vitocal-222-s.html",
            ],
            4 => [
                "Nazwa" => "Panasonic",
                "Id" => 2112,
                "Moc" => 2.1,
                "Cena [PLN]" => 2600,
                "Link" => "https://www.viessmann.pl/pl/produkty/pompy-ciepla/vitocal-222-s.html",
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

    public function deliver_mail($power, $area, $standard, $name_of_pump, $id, $efficiency, $price, $link)
    {
        if (isset($_POST['cf-submitted'])) {
            $name = sanitize_text_field($_POST["cf-name"]);
            $email = sanitize_email($_POST["cf-email"]);
            $subject = sanitize_text_field($_POST["cf-subject"]);
            $message = esc_textarea($_POST["cf-message"]);

            $message .= "\nWyniki dla wyceny pompy ciepła:";
            if (empty($name_of_pump)) {
                $message .= '\nBrak odpowiedniej pompy ciepla';
            }
            else {
                $message .= 'Dla powierzchni ogrzewania: ' . $area . ' m2 oraz standardu wykonania: ' . $standard . ' kWh/m2 szacowana ilość mocy potrzebna do ogrzania domu to: ' . $power . ' kW';
                $message .= "\nSzczegóły dotyczące wybranej pompy ciepła";
                $message .= "\nNazwa: " . $name_of_pump;
                $message .= "\nId: " . $id;
                $message .= "\nMoc: " . $efficiency . " kW";
                $message .= "\nCena: " . $price . " zł";
                $message .= "\nLink do strony producenta: ". $link;
            }
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
        echo '<input type="range" name="cf-area" min="0" max="1000" value="' . (isset($_POST["cf-area"]) ? esc_attr($_POST["cf-area"]) : '0') . '" size="40" oninput="this.nextElementSibling.value = this.value" />';
        echo '<output>' . (isset($_POST["cf-area"]) ? esc_attr($_POST["cf-area"]) : '0') . '</output>';
        echo '<span class="error" style="color:red;display:none">To pole nie może być puste</span>';
        echo '</p>';

        echo '<p>';
        echo 'Standard wykonania [kWh/m2 rok] (wymagane) <br/>';
        echo '<input type="range" name="cf-standard" min="0" max="200" value="' . (isset($_POST["cf-standard"]) ? esc_attr($_POST["cf-standard"]) : '0') . '" size="40" oninput="this.nextElementSibling.value = this.value" />';
        echo '<output>' . (isset($_POST["cf-standard"]) ? esc_attr($_POST["cf-standard"]) : '0') . '</output>';
        echo '<span class="error" style="color:red;display:none">To pole nie może być puste</span>';
        echo '</p>';

        echo '<p><input type="submit" name="cf-count" value="Oblicz"></p>';
        echo '</form>';
    }
}

function html_form_code($power = null, $area = null, $standard = null, $name_of_pump = null, $id = null, $efficiency = null, $price = null, $link = null)
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
        echo '<input type="hidden" name="cf-area" value="' . esc_attr($area) . '" />';
        echo '<input type="hidden" name="cf-standard" value="' . esc_attr($standard) . '" />';
        echo '<input type="hidden" name="cf-div-name" value="' . esc_attr($name_of_pump) . '" />';
        echo '<input type="hidden" name="cf-div-id" value="' . esc_attr($id) . '" />';
        echo '<input type="hidden" name="cf-div-efficieny" value="' . esc_attr($efficiency) . '" />';
        echo '<input type="hidden" name="cf-div-price" value="' . esc_attr($price) . '" />';
        echo '<input type="hidden" name="cf-div-link" value="' . esc_attr($link) . '" />';

        echo '<p><input type="submit" name="cf-submitted" value="Wyślij"></p>';
        echo '</form>';
    }
}

function html_results_code($power, $area, $standard, $name_of_pump, $id, $efficiency, $price, $link)
{
    if (isset($_POST['cf-submitted']) && !isset($_POST['cf-result'])) {
        echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';

        if (empty($name_of_pump)) {
            echo 'Brak odpowiedniej pompy ciepla';
        }
        else {
            echo '<p>';
            echo 'Dla powierzchni ogrzewania: ' . esc_html($area) . ' m2 oraz standardu wykonania: ' . esc_html($standard) . ' kWh/m2 szacowana ilość mocy potrzebna do ogrzania domu to: ' . esc_html($power) . ' kW<br>';
            echo 'Szczegóły dotyczące wybranej pompy ciepła';

            echo '<pre>';
            echo '<input value="Nazwa" readonly />';
            echo '<input value="Numer id" readonly />';
            echo '<input value="Moc w kW" readonly />';
            echo '<input value="Cena w zł" readonly />';
            echo '<input value="Strona producenta" readonly />';
            echo '</pre>';

            echo '<pre>';
            echo '<input name="cf-div-name" value="' . esc_attr($name_of_pump) . '" readonly />';
            echo '<input name="cf-div-id" value="' . esc_attr($id) . '" readonly />';
            echo '<input name="cf-div-efficieny" value="' . esc_attr($efficiency) . '" readonly />';
            echo '<input name="cf-div-price" value="' . esc_attr($price) . '" readonly />';
            echo '<input name="cf-div-link" value="' . esc_attr($link) . '" readonly />';
            echo '</pre>';
            echo '</p>';
        }
        echo '</form>';
    }
}

function cf_shortcode()
{
    ob_start();
    $result = new Calculator();
    if (isset($_POST['cf-count'])) {
        $power = $result->calculate_power($_POST['cf-area'], $_POST['cf-standard']);
        $pump_info = $result->get_pump_info($power);
        $area = $_POST['cf-area'];
        $standard = $_POST['cf-standard'];

        if (is_array($pump_info) && !empty($pump_info)) {
            $pump_info = reset($pump_info);
            $name_of_pump = $pump_info["Nazwa"];
            $id = $pump_info["Id"];
            $efficiency = $pump_info["Moc"];
            $price = $pump_info["Cena [PLN]"];
            $link = $pump_info["Link"];
        } else {
            $name_of_pump = '';
            $id = '';
            $efficiency = '';
            $price = '';
            $link = '';
        }

        html_form_code($power, $area, $standard, $name_of_pump, $id, $efficiency, $price, $link);
    } else if (isset($_POST['cf-submitted'])) {
        $power = $result->calculate_power($_POST['cf-area'], $_POST['cf-standard']);
        $area = $_POST['cf-area'];
        $standard = $_POST['cf-standard'];
        $name_of_pump = $_POST['cf-div-name'];
        $id = $_POST['cf-div-id'];
        $efficiency = $_POST['cf-div-efficieny'];
        $price = $_POST['cf-div-price'];
        $link = $_POST['cf-div-link'];
        $result->deliver_mail($power, $area, $standard, $name_of_pump, $id, $efficiency, $price, $link);
        html_results_code($power, $area, $standard, $name_of_pump, $id, $efficiency, $price, $link);
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
