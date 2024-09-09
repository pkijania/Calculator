<?php
/*
Plugin Name: Heat pumps power calculator
Description: Simple calculator and correspondence plugin for heat pumps
Version: 2.0
Author: Przemysław Kijania
Author URI: https://przemyslawkijania.pl/
*/

// Import rest of modules
require_once 'heat_pumps_functions.php';
require_once 'heat_pumps_forms.php';

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

add_shortcode('heat_pumps_power_calculator', 'cf_shortcode');
?>
