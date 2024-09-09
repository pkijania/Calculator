<?php
// Return specific heat pump based on provided data
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
    
        $variables['main'] = array();

        $details = [
            "user" => sanitize_text_field($_POST["cf-name"]),
            "email" => sanitize_email($_POST["cf-email"]),
            "subject" => sanitize_text_field($_POST["cf-subject"]),
            "cf-message" => esc_textarea($_POST["cf-message"]),
            "area" => sanitize_text_field($area),
            "standard" => sanitize_text_field($standard),
            "power" => sanitize_text_field($power)
        ];
        $variables['main'] = $details;

        $variables['pumps'] = array();

        foreach ($pump as $index => $row) {
            $number = $index + 1;
            $status = 'style="display: inline-flexbox;"';
            $name = sanitize_text_field($row['Nazwa']);
            $id = sanitize_text_field($row['Id']);
            $efficiency = sanitize_text_field($row['Moc']);
            $price = sanitize_text_field($row['Cena [PLN]']);
            $link = esc_url($row['Link']);
            $device = [
                "status" => $status,
                "name" => $name,
                "id" => $id,
                "efficiency" => $efficiency,
                "price" => $price,
                "link" => $link
            ];
            $variables['pumps'][$number] = $device;
        }
        return $variables;
    }

    // Send an e-mail both to the admin and to the user that consists of all the provided information
    public function send_mail($variables)
    {
        $master_template_path = WP_PLUGIN_DIR . '/heat_pumps_power_calculator/heat_pumps_master_template.html';
        $child_template_path = WP_PLUGIN_DIR . '/heat_pumps_power_calculator/heat_pumps_child_template.html';
        if (!file_exists($master_template_path)) {
            echo 'Plik szablonu nie został znaleziony: ' . $master_template_path;
            return;
        }

        $content = '';
        foreach ($variables["pumps"] as $key1 => $value1) {
            $template = file_get_contents($child_template_path);
            foreach ($value1 as $key => $value) {
                $template = str_replace('{{' . $key . '}}', $value, $template);
            }
            $content = $content . $template;
        }

        $master_template = file_get_contents($master_template_path);
        $template = str_replace('{{content}}', $content, $master_template);

        foreach ($variables["main"] as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }

        if ($variables['main']['power'] <= 16 || !empty($variables['main']['name'])){
            $subject = "Wycena pompy ciepła - " . $variables['main']['user'];
            $to = "wycena@sevro.pl";
            $headers = "From: " . $variables['main']['user'] . " <" . $variables['main']['email'] . ">" . "\r\n";
            $headers .= "CC: " . $variables['main']['email'] . "\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8";

            if (wp_mail($to, $subject, $template, $headers)) {
                echo '<div>';
                echo "<h4>Poniższe wyniki zostały wysłane na podany w formularzu email:</h4>";
                echo '</div>';
            }
        }
    }
}
?>
