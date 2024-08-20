<?php
/*
Plugin Name: Heat pumps power calculator
Description: Simple calculator and correspondence plugin for heat pumps
Version: 2.0
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
        $csvFile = WP_PLUGIN_DIR . '/heat_pumps_power_calculator/heat_pumps_list.csv';
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
            $new_results = array_values($results);
            return $new_results;
        }
    }

    // Assign all the data to variables
    public function assign_variable($power, $area, $standard, $pump)
    {
        $variables = array();
    
        $variables['user'] = sanitize_text_field($_POST["cf-name"]);
        $variables['email'] = sanitize_email($_POST["cf-email"]);
        $variables['subject'] = sanitize_text_field($_POST["cf-subject"]);
        $variables['cf-message'] = esc_textarea($_POST["cf-message"]);
        $variables['area'] = sanitize_text_field($area);
        $variables['standard'] = sanitize_text_field($standard);
        $variables['power'] = sanitize_text_field($power);
    
        foreach ($pump as $row) {
            $variables['name'] = sanitize_text_field($row['Nazwa']);
            $variables['id'] = sanitize_text_field($row['Id']);
            $variables['efficiency'] = sanitize_text_field($row['Moc']);
            $variables['price'] = sanitize_text_field($row['Cena [PLN]']);
            $variables['link'] = esc_url($row['Link']);
        }
        return $variables;
    }

    // Send an e-mail both to the admin and to the user that consists of all the provided information
    public function send_mail($variables)
    {
        $template_path = WP_PLUGIN_DIR . '/heat_pumps_power_calculator/heat_pumps_template.html';
        if (!file_exists($template_path)) {
            echo 'Plik szablonu nie został znaleziony: ' . $template_path;
            return;
        }

        $template = file_get_contents($template_path);

        foreach ($variables as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }

        if ($variables['power'] <= 16 || !empty($variables['name'])){
            $subject = "Wycena pompy ciepła";
            $to = get_option('admin_email');
            $headers = "From: " . $variables['user'] . " <" . $variables['email'] . ">" . "\r\n";
            $headers .= "CC: " . $variables['email'] . "\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8";

            if (wp_mail($to, $subject, $template, $headers)) {
                echo '<div>';
                echo "<h4>Poniższe wyniki zostały wysłane na podany w formularzu email:</h4>";
                echo '</div>';
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
        echo '<br>';
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
        echo '<br>';
        echo '</p>';

        echo '<p><input type="submit" name="cf-count" value="Oblicz"></p>';
        echo '</form>';
    }
}

// Show the second formula - input fields for information about the client
function html_form_code($power = null, $area = null, $standard = null)
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
        echo '<textarea rows="5" cols="118" name="cf-message">' . (isset($_POST["cf-message"]) ? esc_attr($_POST["cf-message"]) : '') . '</textarea>';
        echo '<span class="error" style="color:red;display:none">To pole nie może być puste</span>';
        echo '</p>';

        echo '<input type="hidden" name="cf-power" value="' . esc_attr($power) . '" />';
        echo '<input type="hidden" name="cf-area" value="' . esc_attr($area) . '" />';
        echo '<input type="hidden" name="cf-standard" value="' . esc_attr($standard) . '" />';

        echo '<p><br><input type="submit" name="cf-submitted" value="Wyślij"></p>';
        echo '</form>';
    }
}

// Show the third formula - the estimated power needed to heat the building and information about the selected heat pump
function html_results_code($power, $area, $standard, $pumps)
{
    if (isset($_POST['cf-submitted']) && !isset($_POST['cf-result'])) {
        echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
        echo '<div style="color: black;">';

        echo '<p>';
        echo 'Dla powierzchni ogrzewania: <strong>' . esc_html($area) . ' m2</strong> oraz standardu wykonania: <strong>' . esc_html($standard) . ' kWh/m2</strong>,<br>';
        echo 'szacowana ilość mocy potrzebna do ogrzania domu to około: <strong>' . esc_html($power) . ' kW</strong><br>';
        echo '</p>';

        echo '<h4>';
        echo '<br>';
        echo 'Szczegóły dotyczące wybranej pompy ciepła:<br>';
        echo '</h4>';

        if ($power > 16 || empty($pumps)) {
            echo '<p>';
            echo 'Nie znaleziono odpowiedniego kotła pelletowego<br>';
            echo 'W celu doboru urządzenia prosimy o bezpośredni kontakt lub o wprowadzenie innych danych<br>';
            echo '</p>';
        } else {
            foreach ($pumps as $pump) {
                echo '<p>';
                echo 'Nazwa: <strong>' . esc_attr($pump["Nazwa"]) . '</strong><br>';
                echo 'Numer id: <strong>' . esc_attr($pump["Id"]) . '</strong><br>';
                echo 'Moc: <strong>' . esc_attr($pump["Moc"]) . ' kW </strong><br>';
                echo 'Cena od: <strong>' . esc_attr($pump["Cena [PLN]"]) . ' zł brutto (8% VAT) </strong><br>';
                echo 'Strona producenta: <strong>' . esc_attr($pump["Link"]) . '</strong><br>';
                echo '</p>';
            }

            echo '<h5>';
            echo '<br>';
            echo 'Kalkulacja Zawiera:<br>';
            echo '</h5>';

            echo '<p style="font-size:10px; line-height: 2;">';
            echo '- Dostarczenie materiałów niezbędnych do instalacji<br>';
            echo '- Montaż wspornika gruntowego<br>';
            echo '- Montaż jednostki zewnętrznej<br>';
            echo '- Wykonanie niezbędnych otworów w ścianach<br>';
            echo '- Przeprowadzenie instalacji freonowej (pompy split)<br>';
            echo '- Montaż jednostki wewnętrznej<br>';
            echo '- Połączenie jednostek, wykonanie próby szczelności<br>';
            echo '- Montaż zbiornika ciepłej wody użytkowej wraz z niezbędną armaturą i zabezpieczeniami<br>';
            echo '- Montaż armatury i zabezpieczeń<br>';
            echo '- Instalacja wykonana w stali zaciskanej z izolacją kauczukową<br>';
            echo '- Montaż filtra magnetycznego<br>';
            echo '- Połączenie pompy ciepła z instalacja CO bezpośrednio<br>';
            echo '- Wykonanie instalacji zasilającej i sterującej<br>';
            echo '- Napełnienie układu oraz jego odpowietrzenie<br>';
            echo '- Pierwsze uruchomienie w przypadku pompy ciepła Viessmann<br>';
            echo '- Szkolenie w ramach obsługi urządzenia<br>';
            echo '</p>';

            echo '<h5>';
            echo '<br>';
            echo 'Dodatkowe opcje:<br>';
            echo '</h5>';

            echo '<p style="font-size:10px; line-height: 2;">';
            echo '- Montaż zbiornika buforowego<br>';
            echo '- Montaż grup pompowych oraz mieszających<br>';
            echo '- Płukanie instalacji<br>';
            echo '</p>';
            }
        echo '</div>';
        echo '</form>';
    }
}

// Launch the program
function cf_shortcode()
{
    ob_start();
    $result = new Calculator();
    $power = $result->calculate_power($_POST['cf-area'], $_POST['cf-standard']);
    $pump_info = $result->get_pump_info($power);
    $area = $_POST['cf-area'];
    $standard = $_POST['cf-standard'];

    if (isset($_POST['cf-count']) && !isset($_POST['cf-submitted'])) {
        html_form_code($power, $area, $standard);
    } else if (isset($_POST['cf-submitted'])) {
        $variable = $result->assign_variable($power, $area, $standard, $pump_info);
        $result->send_mail($variable);
        html_results_code($power, $area, $standard, $pump_info);
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

add_shortcode('heat_pumps_power_calculator', 'cf_shortcode');
?>
