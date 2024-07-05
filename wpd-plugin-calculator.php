<?php 
$power = "";
$pump = "";
class calculator
=======
<?php
/*
Plugin Name: Power calculator
Description: Simple calculator and correspondence plugin
Version: 1.0
Author: Przemysław Kijania
Author URI: https://przemyslawkijania.pl/
*/

class Calculator
>>>>>>> Stashed changes
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

    function pump($area, $standard)
    {
        // Wyliczenie szacowanej mocy potrzebnej do ocieplenia budynku
        $this->area = $area;
        $this->standard = $standard;
        $pump_power = ($this->area * $this->standard)/2000;
        return $pump_power;
        //
    }

    function calculate_power($area, $standard)
=======
    public function calculate_power($area, $standard)
>>>>>>> Stashed changes
    {
        $this->area = $area;
        $this->standard = $standard;
        $this->pump_power = ($this->area * $this->standard) / 2000;
        return $this->pump_power;
    }

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
        echo '<pre>'; print_r($pumps_models); echo '</pre>';
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
            echo '<pre>'; print_r($results); echo '</pre>';
        }
        //
        return $pump;
    }
}

$result = new calculator();

if(isset($_POST['submit']))
{   
    $power = $result->calculate_power($_POST['n1'],$_POST['n2']);
    $pump = $result->pump($_POST['n1'],$_POST['n2']);
}
?>

<form method="post">
<table align="center">

    <tr>
        <td>Powierzchnia ogrzewania [m2]</td>
        <td><input type="text" name="n1"></td>
    </tr>

    <tr>
        <td>Standard wykonania [kWh/m2 rok]</td>
        <td><input type="text" name="n2"></td>
    </tr>

    <tr>
        <td></td>
        <td><input type="submit" name="submit" value="="></td>
    </tr>

    <tr>
        <td>Szacowana moc potrzebna do ocieplenia domu: <strong><?php echo $pump; ?><strong></td>
    </tr>

    <tr>
        <td>Odpowiednia pompa ciepła: <strong><?php echo $power; ?><strong></td>
    </tr>

</table>
</form>
