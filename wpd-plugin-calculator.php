<?php
/*
Plugin Name: Power calculator
Description: Simple calculator and correspondence plugin
Version: 1.9
Author: Przemysław Kijania
Author URI: https://przemyslawkijania.pl/
*/

class Calculator
{
    var $pump_power;
    var $area;
    var $standard;

    // Calculate estimated power needed to heat the building based on the provided data
    public function calculate_power($area, $standard)
    {
        $this->area = $area;
        $this->standard = $standard;
        $this->pump_power = ($this->area * $this->standard) / 2000;
        return $this->pump_power;
    }

    // Get all the pump models from csv file
    private function get_pump_models()
    {
        $csvFile = WP_PLUGIN_DIR . '/power-calculator/Spis_pomp.csv';
        $pumps = [];
        $index = 1;
        if (($handle = fopen($csvFile, 'r')) !== FALSE) {
            $header = fgetcsv($handle, 1000, ";");
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $pumps[$index] = [
                    "Nazwa" => $data[0],
                    "Id" => $data[1],
                    "Moc" => floatval(str_replace(',', '.', $data[2])),
                    "Cena [PLN]" => $data[3],
                    "Link" => $data[4]
                ];
                $index++;
            }
            fclose($handle);
        }
        return $pumps;
    }

    // Construct a list with powers of all heat pumps
    private function get_pumps_array($pumps_models)
    {
        $pumps_array = [];
        foreach ($pumps_models as $pump) {
            $pumps_array[] = $pump['Moc'];
        }
        return $pumps_array;
    }

    // Find a heat pump with power that will be the most suitable for the estimated power
    private function find_suitable_pump($pumps_array, $pump_power)
    {
        $list = [];
        foreach ($pumps_array as &$pump_number) {
            if ($pump_number + 1.5 > $pump_power) {
                array_unshift($list, ($pump_number + 1.5) - $pump_power);
            }
        }

        if (empty($list)) {
            return 'Brak odpowiedniej pompy ciepla';
        } else {
            return min($list) + $pump_power - 1.5;
        }
    }

    // Search for the selected heat pump in the storage of all heat pumps
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

    // Send an e-mail both to the admin and to the user that consists of all the provided information, the estimated power needed to heat the building and information about the selected heat pump
    public function deliver_mail($power, $area, $standard, $name_of_pump, $id, $efficiency, $price, $link)
    {
        if (isset($_POST['cf-submitted'])) {
            $name = sanitize_text_field($_POST["cf-name"]);
            $email = sanitize_email($_POST["cf-email"]);
            $subject = sanitize_text_field($_POST["cf-subject"]);
            $message = esc_textarea($_POST["cf-message"]);
            
            $variables = array();
            $variables['area'] = sanitize_text_field($area);
            $variables['standard'] = sanitize_text_field($standard);
            $variables['power'] = sanitize_text_field($power);
            $variables['name'] = sanitize_text_field($name_of_pump);
            $variables['id'] = sanitize_text_field($id);
            $variables['efficiency'] = sanitize_text_field($efficiency);
            $variables['price'] = sanitize_text_field($price);
            $variables['link'] = esc_url($link);
            $variables['cf-message'] = sanitize_text_field($message);

            $template_path = WP_PLUGIN_DIR . '/power-calculator/template.html';
            if (!file_exists($template_path)) {
                echo 'Plik szablonu nie został znaleziony: ' . $template_path;
            }
            $template = file_get_contents($template_path);

            foreach ($variables as $key => $value) {
                $template = str_replace('{{' . $key . '}}', $value, $template);
            }

            $message .= "\nWyniki dla wyceny pompy ciepła:";
            $message .= "\nDla powierzchni ogrzewania: " . $area . " m2 oraz standardu wykonania: " . $standard . " kWh/m2 szacowana ilość mocy potrzebna do ogrzania domu to: " . $power . " kW";
            $message .= "\nSzczegóły dotyczące wybranej pompy ciepła:";

            if ($power > 15 || empty($name_of_pump)) {
                $message .= "\nNie znaleziono odpowiedniej pompy ciepła";
                $message .= "\nW celu doboru urządzenia prosimy o bezpośredni kontakt lub o wprowadzenie innych danych";
            }
            else {
                $message .= "\nNazwa: " . $name_of_pump;
                $message .= "\nId: " . $id;
                $message .= "\nMoc: " . $efficiency . " kW";
                $message .= "\nCena: " . $price . " zł";
                $message .= "\nLink do strony producenta: ". $link;
            }

            $message .= "\nWszelkie informacje do korespondencji znajdują się pod podanym linkiem: https://sevro.pl/kontakt/";
            $subject = "Wycena pompy ciepła";

            $to = get_option('admin_email');
            $headers = "From: $name <$email>" . "\r\n";
            $headers .= "CC: $email" . "\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8";

            if (wp_mail($to, $subject, $template, $headers)) {
                echo '<div>';
                echo "<h4>Wyniki zostały wysłane na podany email.</h4>";
                echo '</div>';
            } else {
                echo 'Wystąpił nieznany błąd';
            }
        }
    }
}

// Show the first formula - short description and a slider for data input
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
        echo 'Proszę wprowadzić odpowiednie dane: <br/>';
        echo '</h4>';

        echo '<p>';
        echo 'Powierzchnia ogrzewania [m2] <br/>';
        echo '<input type="range" name="cf-area" min="10" max="1000" value="' . (isset($_POST["cf-area"]) ? esc_attr($_POST["cf-area"]) : '10') . '" size="40" oninput="this.nextElementSibling.value = this.value" />';
        echo '<output>' . (isset($_POST["cf-area"]) ? esc_attr($_POST["cf-area"]) : '10') . '</output>';
        echo '</p>';

        echo '<p>';
        echo 'Standard wykonania [kWh/m2 rok] <br/>';
        echo '<input type="range" name="cf-standard" min="5" max="200" value="' . (isset($_POST["cf-standard"]) ? esc_attr($_POST["cf-standard"]) : '5') . '" size="40" oninput="this.nextElementSibling.value = this.value" />';
        echo '<output>' . (isset($_POST["cf-standard"]) ? esc_attr($_POST["cf-standard"]) : '5') . '</output>';
        echo '</p>';

        echo '<p><input type="submit" name="cf-count" value="Oblicz"></p>';
        echo '</form>';
    }
}

// Show the second formula - input fields for information about the client
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
        echo '<textarea rows="10" cols="100%" name="cf-message">' . (isset($_POST["cf-message"]) ? esc_attr($_POST["cf-message"]) : '') . '</textarea>';
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

// Show the third formula - the estimated power needed to heat the building and information about the selected heat pump
function html_results_code($power, $area, $standard, $name_of_pump, $id, $efficiency, $price, $link)
{
    if (isset($_POST['cf-submitted']) && !isset($_POST['cf-result'])) {
        echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
        echo 'Dla powierzchni ogrzewania: ' . esc_html($area) . ' m2 oraz standardu wykonania: ' . esc_html($standard) . ' kWh/m2 szacowana ilość mocy potrzebna do ogrzania domu to: ' . esc_html($power) . ' kW<br>';
        echo 'Szczegóły dotyczące wybranej pompy ciepła:<br>';

        if ($power > 15 || empty($name_of_pump)) {
            echo 'Nie znaleziono odpowiedniej pompy ciepła<br>';
            echo 'W celu doboru urządzenia prosimy o bezpośredni kontakt lub o wprowadzenie innych danych<br>';
        }
        else {
            echo '<p>';
            echo 'Nazwa: ' . esc_attr($name_of_pump) . '<br>';
            echo 'Numer id: ' . esc_attr($id) . '<br>';
            echo 'Moc: ' . esc_attr($efficiency) . ' kW <br>';
            echo 'Cena: ' . esc_attr($price) . ' zł <br>';
            echo 'Strona producenta: ' . esc_attr($link) . '<br>';
            echo '</p>';
            }
        echo '</form>';
    }
}

// Launch the program
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
