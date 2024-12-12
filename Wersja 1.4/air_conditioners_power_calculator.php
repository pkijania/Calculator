<?php
/*
Plugin Name: Air conditioners power calculator
Description: Simple calculator and correspondence plugin for air conditioners
Version: 2.1
Author: Przemysław Kijania
Author URI: https://przemyslawkijania.pl/
*/

// Import rest of modules
require_once 'air_conditioners_functions.php';
require_once 'air_conditioners_forms.php';

// Launch the program
function short_code()
{
    ob_start();
    $result = new AirConditionersCalculator();
    $power = $result->calculate_needed_power($_POST['width'], $_POST['length'], $_POST['height'], $_POST['number_of_people'], $_POST['number_of_agd_devices'], $_POST['attic'], $_POST['position_of_window']);
    $instalation_cost = $result->calculate_additional_costs($_POST['instalation_length']);
    $air_conditioner_info = $result->get_air_conditioner_info($power, $_POST['gree_device'], $_POST['lg_device'], $_POST['panasonic_device']);
    $updated_air_conditioner_info = $result->add_additional_costs($instalation_cost, $air_conditioner_info);

    if (isset($_POST['cf-count']) && !isset($_POST['cf-submitted'])) {
        html_data_forms($power, $_POST['width'], $_POST['length'], $_POST['height'], $_POST['number_of_people'], $_POST['number_of_agd_devices'], $_POST['attic'], $_POST['position_of_window'], $_POST['gree_device'], $_POST['lg_device'], $_POST['panasonic_device'], $_POST['instalation_length'], $_POST['concrete_length'], $_POST['brick_length'], $_POST['trough_length'], $_POST["nothing_value"]);
    } else if (isset($_POST['cf-submitted'])) {
        $variables = $result->assign_all_variables($power, $_POST['width'], $_POST['length'], $_POST['height'], $_POST['number_of_people'], $_POST['number_of_agd_devices'], $updated_air_conditioner_info, $_POST['instalation_length']);
        $templates = $result->prepare_all_templates($variables);
        $result->send_mails($variables, $templates);
        html_results_forms($power, $_POST['width'], $_POST['length'], $_POST['height'], $_POST['number_of_people'], $_POST['number_of_agd_devices'], $updated_air_conditioner_info, $_POST['instalation_length']);
    } else {
        html_calculation_forms();
    }

    return ob_get_clean();
}

// Import JavaScript module
function enqueue_custom_form_scripts() {
    wp_enqueue_script(
        'air_conditioners_scripts',
        plugins_url('air_conditioners_scripts.js', __FILE__),
        array(),
        '2.1',
        true
    );
}

add_action('wp_enqueue_scripts', 'enqueue_custom_form_scripts');
add_shortcode('air_conditioners_power_calculator', 'short_code');
?>
