<?php
// Display all formulas on the screen
// Show the first formula - short description and a slider for data input
function html_calculation_code()
{
    if (!isset($_POST['cf-count']) && !isset($_POST['cf-submitted']) && !isset($_POST['cf-result'])) {
        echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post" onsubmit="return validateForm()">';

        echo '<h4>';
        echo 'Standard wykonania | zapotrzebowanie na ciepło <br/>';
        echo '</h4>';

        echo '<p>';
        echo 'Starszy dom bez ocieplenia - od 170 do 200 [kWh/m2 rok] <br/>';
        echo 'Starszy dom z ociepleniem - od 100 do 160 [kWh/m2 rok] <br/>';
        echo 'Dom budowany obecnie - od 70 do 90 [kWh/m2 rok] <br/>';
        echo 'Dom energooszczędny - od 60 do 70 [kWh/m2 rok] <br/>';
        echo 'Dom pasywny - 15 [kWh/m2 rok] <br/>';
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

        $index = 1;

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
                echo 'Urządzenie nr <strong>' . $index . '</strong><br>';
                echo 'Nazwa: <strong>' . esc_attr($pump["Nazwa"]) . '</strong><br>';
                echo 'Numer id: <strong>' . esc_attr($pump["Id"]) . '</strong><br>';
                echo 'Moc: <strong>' . esc_attr($pump["Moc"]) . ' kW </strong><br>';
                echo 'Cena od: <strong>' . esc_attr($pump["Cena [PLN]"]) . ' zł brutto (8% VAT) </strong><br>';
                echo 'Strona producenta: <strong>' . esc_attr($pump["Link"]) . '</strong><br>';
                echo '<br>';
                echo '</p>';
                $index++;
            }
        }
        echo '</div>';
        echo '</form>';
    }
}
?>
