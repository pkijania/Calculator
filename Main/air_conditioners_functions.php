<?php
// Return specific air conditioner based on provided data
class AirConditionersCalculator
{
    var $air_conditioners_power;
    var $width;
    var $length;
    var $height;
    var $number_of_people;
    var $number_of_agd_devices;
    var $attic;
    var $position_of_window;
    var $instalation_length;

    // Calculate estimated power needed to cool the building based on the provided data
    public function calculate_needed_power($width, $length, $height, $number_of_people, $number_of_agd_devices, $attic, $position_of_window)
    {
        $this->width = $width;
        $this->length = $length;
        $this->height = $height;
        $this->number_of_people = $number_of_people;
        $this->number_of_agd_devices = $number_of_agd_devices;
        $this->attic = $attic;
        $this->position_of_window = $position_of_window;
        $this->air_conditioners_power = (($this->width * $this->length * $this->height) * 40 + $this->number_of_people * 100 + $this->number_of_agd_devices * 150) / 1000;
        if ($this->attic){
            $this->air_conditioners_power = $this->air_conditioners_power * 1.4;
        }
        if ($this->position_of_window){
            $this->air_conditioners_power = $this->air_conditioners_power * 1.1;
        }
        return $this->air_conditioners_power;
    }

    // Calculate additional cost for furrowing and instalation materials
    public function calculate_additional_costs($instalation_length)
    {
        $this->instalation_length = $instalation_length;
        $instalation_cost = $this->instalation_length * 83;
        return $instalation_cost;
    }

    // Get all the air conditioners models from csv file
    private function get_air_conditioners_models()
    {
        $csvFile = WP_PLUGIN_DIR . '/air_conditioners_power_calculator/air_conditioners_list.csv';
        $air_conditioners = [];
        $index = 1;
        if (($handle = fopen($csvFile, 'r')) !== FALSE) {
            $header = fgetcsv($handle, 1000, ";");
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                $air_conditioners[$index] = [
                    "Nazwa" => $data[0],
                    "Id" => $data[1],
                    "Moc" => floatval(str_replace(',', '.', $data[2])),
                    "Cena [PLN]" => $data[3],
                    "Link" => $data[4],
                    "Marka" => $data[5]
                ];
                $index++;
            }
            fclose($handle);
        }
        return $air_conditioners;
    }

    // Construct a list with powers of all air conditioners
    private function get_air_conditioners_array($air_conditioners_models)
    {
        $air_conditioners_array = [];
        foreach ($air_conditioners_models as $air_conditioner) {
            $air_conditioners_array[] = $air_conditioner['Moc'];
        }
        return $air_conditioners_array;
    }

    // Find an air conditioner with power that will be the most suitable for the estimated power
    private function find_suitable_air_conditioner($air_conditioners_array, $air_conditioner_power)
    {
        $list = [];
        foreach ($air_conditioners_array as &$air_conditioner_number) {
            if ($air_conditioner_number > $air_conditioner_power) {
                array_unshift($list, ($air_conditioner_number) - $air_conditioner_power);
            }
        }

        if (empty($list)) {
            return 'Brak odpowiedniego klimatyzatora';
        } else {
            return min($list) + $air_conditioner_power;
        }
    }

    // Search for the selected air conditioner in the storage of all air conditioners based on calculated power and provided brand of the device
    public function get_air_conditioner_info($air_conditioner_power, $gree_device = NULL, $lg_device = NULL, $panasonic_device = NULL)
    {
        $air_conditioners_models = $this->get_air_conditioners_models();
        $air_conditioners_array = $this->get_air_conditioners_array($air_conditioners_models);
        $suitable_air_conditioner_power = $this->find_suitable_air_conditioner($air_conditioners_array, $air_conditioner_power);

        if ($suitable_air_conditioner_power === 'Brak odpowiedniego klimatyzatora') {
            return $suitable_air_conditioner_power;
        } else {
            $min_power = $suitable_air_conditioner_power - 0.4;
            $max_power = $suitable_air_conditioner_power + 0.4;
            $results = array_filter($air_conditioners_models, function ($air_conditioners_models) use ($min_power, $max_power, $gree_device, $lg_device, $panasonic_device) {
                $matches_power = isset($air_conditioners_models['Moc']) && $air_conditioners_models['Moc'] >= $min_power && $air_conditioners_models['Moc'] <= $max_power;
                $matches_brand = (!$gree_device && !$lg_device && !$panasonic_device) || 
                    (isset($air_conditioners_models['Marka']) && 
                    (($gree_device && $air_conditioners_models['Marka'] === 'GREE') || 
                    ($lg_device && $air_conditioners_models['Marka'] === 'LG') || 
                    ($panasonic_device && $air_conditioners_models['Marka'] === 'PANASONIC')));
                return $matches_power && $matches_brand;
            });
            $new_results = array_values($results);
            return $new_results;
        }
    }

    public function add_additional_costs($instalation_cost, $new_results)
    {
        foreach ($new_results as &$result) {
            $price = str_replace(' ', '', $result['Cena [PLN]']);
            $price = (float)$price;
            $price += $instalation_cost;
            $result['Cena [PLN]'] = number_format($price, 0, ',', ' ');
        }
        $results = array_values($new_results);
        return $results;
    }

    // Assign all the data to variables
    public function assign_all_variables($power, $width, $length, $height, $number_of_people, $number_of_agd_devices, $air_conditioner, $instalation_length)
    {
        $variables = array();
    
        $variables['main'] = array();

        $details = [
            "user" => sanitize_text_field($_POST["cf-name"]),
            "email" => sanitize_email($_POST["cf-email"]),
            "subject" => sanitize_text_field($_POST["cf-subject"]),
            "cf-message" => esc_textarea($_POST["cf-message"]),
            "cf-telephone" => esc_textarea($_POST["cf-telephone"]),
            "cf-location" => esc_textarea($_POST["cf-location"]),
            "instalation_length" => sanitize_text_field($instalation_length),
            "number_of_people" => sanitize_text_field($number_of_people),
            "number_of_agd_devices" => sanitize_text_field($number_of_agd_devices),
            "volume" => sanitize_text_field($width * $length),
            "height" => sanitize_text_field($height),
            "power" => sanitize_text_field($power)
        ];
        $variables['main'] = $details;

        $variables['air_conditioners'] = array();

        foreach ($air_conditioner as $index => $row) {
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
            $variables['air_conditioners'][$number] = $device;
        }
        return $variables;
    }

        // Prepare templates for client and firm e-mails messages
    public function prepare_all_templates($variables)
    {
        $client_template_path = WP_PLUGIN_DIR . '/air_conditioners_power_calculator/air_conditioners_client_template.html';
        $device_template_path = WP_PLUGIN_DIR . '/air_conditioners_power_calculator/air_conditioners_device_template.html';
        $firm_template_path = WP_PLUGIN_DIR . '/air_conditioners_power_calculator/air_conditioners_firm_template.html';

        $templates = array();
        $ac_content = '';
        foreach ($variables["air_conditioners"] as $keys => $values) {
            $device_template = file_get_contents($device_template_path);
            foreach ($values as $key => $value) {
                $device_template = str_replace('{{' . $key . '}}', $value, $device_template);
            }
            $ac_content = $ac_content . $device_template;
        }

        $client_template = str_replace('{{ac_content}}', $ac_content, file_get_contents($client_template_path));
        $firm_template = str_replace('{{ac_content}}', $ac_content, file_get_contents($firm_template_path));

        foreach ($variables["main"] as $key => $value) {
            $client_template = str_replace('{{' . $key . '}}', $value, $client_template);
            $firm_template = str_replace('{{' . $key . '}}', $value, $firm_template);
        }

        $templates = [
            "client_template" => $client_template,
            "firm_template" => $firm_template
        ];
        return $templates;
    }

    // Send an e-mail both to the admin and to the user that consists of all the provided information
    public function send_mails($variables, $templates)
    {
        $subject = "Wycena klimatyzatora - " . $variables['main']['user'];
        $first_to = $variables['main']['email'];
        $second_to = "wycena@sevro.pl";
        $headers = "From: " . $variables['main']['user'] . " <" . $variables['main']['email'] . ">" . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8";

        if ($variables['main']['power'] <= 24 || !empty($variables['main']['name'])){
            wp_mail($first_to, $subject, $templates['client_template'], $headers);
            wp_mail($second_to, $subject, $templates['firm_template'], $headers);
        }
    }
}
?>
