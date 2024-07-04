<?php 
$power = "";
$pump = "";
class calculator
{
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
