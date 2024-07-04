<?php
/*
Plugin Name: Power calculator
Description: Simple calculator and correspondence plugin
Version: 1.0
Author: Przemysław Kijania
Author URI: https://przemyslawkijania.pl/
*/

function html_calculation_code()
{
    if (!isset( $_POST['cf-submitted']) && !isset( $_POST['cf-count'])) {
        echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';

        echo '<p>';
        echo 'Powierzchnia ogrzewania [m2] (required) <br/>';
        echo '<input type="text" name="cf-power" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( $_POST["cf-power"] ) ? esc_attr( $_POST["cf-power"] ) : '' ) . '" size="40" />';
        echo '</p>';

        echo '<p>';
        echo 'Standard wykonania [kWh/m2 rok] (required) <br/>';
        echo '<input type="text" name="cf-standard" value="' . ( isset( $_POST["cf-standard"] ) ? esc_attr( $_POST["cf-standard"] ) : '' ) . '" size="40" />';
        echo '</p>';

        echo '<p><input type="submit" name="cf-count" value="Oblicz"></p>';
        echo '</form>';
    }
}

function html_form_code()
{
    if ( isset( $_POST['cf-count'])) {
        echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';

        echo '<p>';
        echo 'Your Name (required) <br/>';
        echo '<input type="text" name="cf-name" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( $_POST["cf-name"] ) ? esc_attr( $_POST["cf-name"] ) : '' ) . '" size="40" />';
        echo '</p>';

        echo '<p>';
        echo 'Your Email (required) <br/>';
        echo '<input type="email" name="cf-email" value="' . ( isset( $_POST["cf-email"] ) ? esc_attr( $_POST["cf-email"] ) : '' ) . '" size="40" />';
        echo '</p>';

        echo '<p>';
        echo 'Subject (required) <br/>';
        echo '<input type="text" name="cf-subject" pattern="[a-zA-Z ]+" value="' . ( isset( $_POST["cf-subject"] ) ? esc_attr( $_POST["cf-subject"] ) : '' ) . '" size="40" />';
        echo '</p>';

        echo '<p>';
        echo 'Your Message (required) <br/>';
        echo '<textarea rows="10" cols="35" name="cf-message">' . ( isset( $_POST["cf-message"] ) ? esc_attr( $_POST["cf-message"] ) : '' ) . '</textarea>';
        echo '</p>';

        echo '<p><input type="submit" name="cf-submitted" value="Send"></p>';
        echo '</form>';
    }
}

class calculator
{
    function deliver_mail()
    {
        // if the submit button is clicked, send the email
        if (isset( $_POST['cf-submitted'])) {

            // sanitize form values
            $name    = sanitize_text_field( $_POST["cf-name"] );
            $email   = sanitize_email( $_POST["cf-email"] );
            $subject = sanitize_text_field( $_POST["cf-subject"] );
            $message = esc_textarea( $_POST["cf-message"] );

            // get the blog administrator's email address
            $to = get_option('admin_email');

            $headers = "From: $name <$email>" . "\r\n";
                    
            // If email has been process for sending, display a success message
            if (wp_mail($to, $subject, $message, $headers)) 
            {
                echo '<div>';
                echo '<p>Thanks for contacting me, expect a response soon.</p>';
                echo '</div>';
            } 
            else 
            {
                echo 'An unexpected error occurred';
            }
        }
    }

    var $pump_power;
    var $area;
    var $standard;

    function calculate_power($area, $standard)
    {
        // Wyliczenie szacowanej mocy potrzebnej do ocieplenia budynku
        $this->area = $area;
        $this->standard = $standard;
        $pump_power = ($this->area * $this->standard)/2000;
        //

        // Szczegółowe informacje na temat modeli pomp ciepła
        $pumps_models = [
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
        //

        // Moce pomp potrzebne do obliczeń
        $pump_one = 8.4;
        $pump_two = 3.4;
        $pump_three = 6.5;
        $pump_four = 2.1;
        $pumps_array = array($pump_one, $pump_two, $pump_three, $pump_four);
        //

        // Sprawdzanie, która pompa będzie odpowiednia do ocieplenia budynku
        $list = array();
        foreach ($pumps_array as &$pump_number)
        {
            if ($pump_number > $pump_power)
            {
                array_unshift($list, $pump_number - $pump_power);
            }
        }
        if (empty($list))
        {
            $pump = 'Brak odpowiedniej pompy ciepła';
        }
        else
        {
            $pump = min($list) + $pump_power;
            // Wyświetlenie szczegółowych informacji na temat wybranej pompy ciepła
            $search_pumps = ["power" => $pump];
            $results = array_filter($pumps_models, function ($pumps_models) use ($search_pumps) {
                return count(array_intersect_assoc($search_pumps, $pumps_models)) == count($search_pumps);
            });
            echo '<pre>'; print_r($results); echo '</pre>';
        }
        //
        return $pump;
    }
}
$power = "";

add_action( 'wp_mail_failed', 'onMailError', 10, 1 );
function onMailError( $wp_error ) {
	echo "<pre>";
    print_r($wp_error);
    echo "</pre>";
}    

function cf_shortcode()
{
    ob_start();
    $result = new calculator();
    if(isset($_POST['cf-count']))
    {   
        $power = $result->calculate_power($_POST['cf-power'],$_POST['cf-standard']);
    }
	$result->deliver_mail();
    html_calculation_code();
	html_form_code();

	return ob_get_clean();
}

add_shortcode( 'power_calculator', 'cf_shortcode' );
?>
