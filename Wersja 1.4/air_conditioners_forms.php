<?php
// Display all formulas on the screen
// Show the first formula - short description and a slider for data input
function html_calculation_forms()
{
    if (!isset($_POST['cf-count']) && !isset($_POST['cf-submitted']) && !isset($_POST['cf-result'])) {
        echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post" onsubmit="return validateForm()">';

        echo '<h4>';
        echo 'Proszę wprowadzić odpowiednie dane: <br/>';
        echo '</h4>';

        echo '<p>';
        echo 'Szerokość pomieszczenia [m] <br/>';
        echo '<input type="range" name="width" min="1" max="20" value="' . (isset($_POST["width"]) ? esc_attr($_POST["width"]) : '1') . '" size="40" oninput="this.nextElementSibling.value = this.value" />';
        echo '<output>' . (isset($_POST["width"]) ? esc_attr($_POST["width"]) : '1') . '</output>';
        echo '</p>';

        echo '<p>';
        echo 'Długość pomieszczenia [m] <br/>';
        echo '<input type="range" name="length" min="1" max="20" value="' . (isset($_POST["length"]) ? esc_attr($_POST["length"]) : '1') . '" size="40" oninput="this.nextElementSibling.value = this.value" />';
        echo '<output>' . (isset($_POST["length"]) ? esc_attr($_POST["length"]) : '1') . '</output>';
        echo '</p>';

        echo '<p>';
        echo 'Wysokość pomieszczenia [m] <br/>';
        echo '<input type="range" name="height" min="1" max="5" step="0.1" value="' . (isset($_POST["height"]) ? esc_attr($_POST["height"]) : '1') . '" size="40" oninput="this.nextElementSibling.value = this.value" />';
        echo '<output>' . (isset($_POST["height"]) ? esc_attr($_POST["height"]) : '1') . '</output>';
        echo '</p>';

        echo '<p>';
        echo 'Ilość domowników [os] <br/>';
        echo '<input type="range" name="number_of_people" min="1" max="10" value="' . (isset($_POST["number_of_people"]) ? esc_attr($_POST["number_of_people"]) : '1') . '" size="40" oninput="this.nextElementSibling.value = this.value" />';
        echo '<output>' . (isset($_POST["number_of_people"]) ? esc_attr($_POST["number_of_people"]) : '1') . '</output>';
        echo '</p>';

        echo '<p>';
        echo 'Ilość urządzeń agd [szt] <br/>';
        echo '<input type="range" name="number_of_agd_devices" min="1" max="10" value="' . (isset($_POST["number_of_agd_devices"]) ? esc_attr($_POST["number_of_agd_devices"]) : '1') . '" size="40" oninput="this.nextElementSibling.value = this.value" />';
        echo '<output>' . (isset($_POST["number_of_agd_devices"]) ? esc_attr($_POST["number_of_agd_devices"]) : '1') . '</output>';
        echo '</p>';

        echo '<p>';
        echo 'Długość instalacji [m] <br/>';
        echo '<input type="range" name="instalation_length" min="3" max="15" step="1" value="' . (isset($_POST["instalation_length"]) ? esc_attr($_POST["instalation_length"]) : '3') . '" size="40" oninput="this.nextElementSibling.value = this.value" />';
        echo '<output>' . (isset($_POST["instalation_length"]) ? esc_attr($_POST["instalation_length"]) : '3') . '</output>';
        echo '<br>';
        echo '</p>';

        echo '<p style="text-align: left;">';
        echo 'Preferowane marki urządzeń: <br/>';
        echo '</p>';

        echo '<p style="text-align: left;">';
        echo 'GREE ' . '<input type="checkbox" name="gree_device" value="GREE"' . (isset($_POST["gree_device"]) ? esc_attr($_POST["gree_device"]) : '') . '" size="40" /> <br>';
        echo '</p>';

        echo '<p style="text-align: left;">';
        echo 'LG ' . '<input type="checkbox" name="lg_device" value="LG"' . (isset($_POST["lg_device"]) ? esc_attr($_POST["lg_device"]) : '') . '" size="40" /> <br>';
        echo '</p>';

        echo '<p style="text-align: left;">';
        echo 'PANASONIC ' . '<input type="checkbox" name="panasonic_device" value="PANASONIC"' . (isset($_POST["panasonic_device"]) ? esc_attr($_POST["panasonic_device"]) : '') . '" size="40" /> <br>';
        echo '<br>';
        echo '</p>';

        echo '<p style="text-align: left;">';
        echo 'Dodatkowe opcje: <br/>';
        echo '</p>';     

        echo '<p style="text-align: left;">';
        echo 'Poddasze <input type="checkbox" name="attic" value="true"' . (isset($_POST["attic"]) ? esc_attr($_POST["attic"]) : '') . '" size="40" />';
        echo '</p>';

        echo '<p style="text-align: left;">';
        echo 'Pozycja okna na południe lub zachód <input type="checkbox" name="position_of_window" value="true"' . (isset($_POST["position_of_window"]) ? esc_attr($_POST["position_of_window"]) : '') . '" size="40" /> <br>';
        echo '<br>';
        echo '</p>';

        echo '<p>';
        echo 'Dodatkowo płatne bruzdowanie ściany: <br/>';
        echo '</p>';

        echo '<p>';
        echo 'Bez przygotowania ściany <br/>';
        echo '<input type="radio" id="use_nothing" name="wall_material" value="true"' . (!isset($_POST["use_nothing"]) ? 'checked' : '') . ' />';
        echo '<input type="hidden" id="nothing_value" name="nothing_value" value="0">';
        echo '</p>';

        echo '<p>';
        echo 'Korytko <br/>';
        echo '<input type="radio" id="use_trough" name="wall_material" value="true"' . (isset($_POST["use_trough"]) ? 'checked' : '') . ' />';
        echo '</p>';

        echo '<p>';
        echo 'Długość ściany [mb] <br/>';
        echo '<input type="range" id="trough_length" name="trough_length" min="1" max="10" value="' . (isset($_POST["trough_length"]) ? esc_attr($_POST["trough_length"]) : '1') . '" ' . (!isset($_POST["use_trough"]) ? 'disabled' : '') . ' oninput="document.getElementById(\'trough_output\').value = this.value" />';
        echo '<output id="trough_output">' . (isset($_POST["trough_length"]) ? esc_attr($_POST["trough_length"]) : '1') . '</output>';
        echo '</p>';

        echo '<p>';
        echo 'Beton <br/>';
        echo '<input type="radio" id="use_concrete" name="wall_material" value="true"' . (isset($_POST["use_concrete"]) ? 'checked' : '') . ' />';
        echo '</p>';

        echo '<p>';
        echo 'Długość ściany [mb] <br/>';
        echo '<input type="range" id="concrete_length" name="concrete_length" min="1" max="10" value="' . (isset($_POST["concrete_length"]) ? esc_attr($_POST["concrete_length"]) : '1') . '" ' . (!isset($_POST["use_concrete"]) ? 'disabled' : '') . ' oninput="document.getElementById(\'concrete_output\').value = this.value" />';
        echo '<output id="concrete_output">' . (isset($_POST["concrete_length"]) ? esc_attr($_POST["concrete_length"]) : '1') . '</output>';
        echo '</p>';

        echo '<p>';
        echo 'Pustak/cegła <br/>';
        echo '<input type="radio" id="use_bricks" name="wall_material" value="true"' . (isset($_POST["use_bricks"]) ? 'checked' : '') . ' />';
        echo '</p>';

        echo '<p>';
        echo 'Długość ściany [mb] <br/>';
        echo '<input type="range" id="brick_length" name="brick_length" min="1" max="10" value="' . (isset($_POST["brick_length"]) ? esc_attr($_POST["brick_length"]) : '1') . '" ' . (!isset($_POST["use_bricks"]) ? 'disabled' : '') . ' oninput="document.getElementById(\'brick_output\').value = this.value" />';
        echo '<output id="brick_output">' . (isset($_POST["brick_length"]) ? esc_attr($_POST["brick_length"]) : '1') . '</output>';
        echo '<br>';
        echo '</p>';

        echo '<p><input type="submit" name="cf-count" value="Oblicz"></p>';
        echo '</form>';
    }
}

// Show the second formula - input fields for information about the client
function html_data_forms($power = null, $width = null, $length = null, $height = null, $number_of_people = null, $number_of_agd_devices = null, $attic, $position_of_window, $gree_device, $lg_device, $panasonic_device, $instalation_length, $concrete_length, $brick_length, $trough_length, $nothing_value)
{
    if (isset($_POST['cf-count']) && !isset($_POST['cf-result']) && !isset($_POST['cf-submitted'])) {
        echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post" onsubmit="return validateForm()">';

        echo '<h4>';
        echo 'Chcesz poznać szacowaną moc i cenę klimatyzatora? Wprowadź swoje dane, wyniki zostaną przesłane drogą mailową oraz pojawią się na stronie. <br/>';
        echo '</h4>';

        echo '<p>';
        echo 'Twoje imię i nazwisko (wymagane) <br/>';
        echo '<input type="text" name="cf-name" value="' . (isset($_POST["cf-name"]) ? esc_attr($_POST["cf-name"]) : '') . '" size="40" required />';
        echo '<span class="error" style="color:red;display:none">To pole nie może być puste</span>';
        echo '</p>';

        echo '<p>';
        echo 'Twój email (wymagane) <br/>';
        echo '<input type="email" name="cf-email" value="' . (isset($_POST["cf-email"]) ? esc_attr($_POST["cf-email"]) : '') . '" size="40" required />';
        echo '<span class="error" style="color:red;display:none">To pole nie może być puste</span>';
        echo '</p>';

        echo '<p>';
        echo 'Twój nr telefonu (wymagane) <br/>';
        echo '<input type="tel" name="cf-telephone" placeholder="123 456 789" pattern="[0-9]{3} [0-9]{3} [0-9]{3}" value="' . (isset($_POST["cf-telephone"]) ? esc_attr($_POST["cf-telephone"]) : '') . '" size="40" required />';
        echo '<span class="error" style="color:red;display:none">To pole nie może być puste</span>';
        echo '</p>';

        echo '<p>';
        echo 'Twoja miejscowość (wymagane) <br/>';
        echo '<input type="text" name="cf-location" value="' . (isset($_POST["cf-location"]) ? esc_attr($_POST["cf-location"]) : '') . '" size="40" required />';
        echo '<span class="error" style="color:red;display:none">To pole nie może być puste</span>';
        echo '</p>';
        
        echo '<p>';
        echo 'Dodatkowe informacje <br/>';
        echo '<textarea rows="5" cols="118" name="cf-message">' . (isset($_POST["cf-message"]) ? esc_attr($_POST["cf-message"]) : '') . '</textarea>';
        echo '<span class="error" style="color:red;display:none">To pole nie może być puste</span>';
        echo '</p>';

        echo '<input type="hidden" name="cf-power" value="' . esc_attr($power) . '" />';
        echo '<input type="hidden" name="width" value="' . esc_attr($width) . '" />';
        echo '<input type="hidden" name="length" value="' . esc_attr($length) . '" />';
        echo '<input type="hidden" name="height" value="' . esc_attr($height) . '" />';
        echo '<input type="hidden" name="number_of_people" value="' . esc_attr($number_of_people) . '" />';
        echo '<input type="hidden" name="number_of_agd_devices" value="' . esc_attr($number_of_agd_devices) . '" />';
        echo '<input type="hidden" name="attic" value="' . esc_attr($attic) . '" />';
        echo '<input type="hidden" name="position_of_window" value="' . esc_attr($position_of_window) . '" />';
        echo '<input type="hidden" name="gree_device" value="' . esc_attr($gree_device) . '" />';
        echo '<input type="hidden" name="lg_device" value="' . esc_attr($lg_device) . '" />';
        echo '<input type="hidden" name="panasonic_device" value="' . esc_attr($panasonic_device) . '" />';
        echo '<input type="hidden" name="instalation_length" value="' . esc_attr($instalation_length) . '" />';
        echo '<input type="hidden" name="concrete_length" value="' . esc_attr($concrete_length) . '" />';
        echo '<input type="hidden" name="brick_length" value="' . esc_attr($brick_length) . '" />';
        echo '<input type="hidden" name="trough_length" value="' . esc_attr($trough_length) . '" />';
        echo '<input type="hidden" name="nothing_value" value="' . esc_attr($nothing_value) . '" />';

        echo 'brak ' . $nothing_value . '<br>';
        echo 'korytko ' . $trough_length . '<br>';
        echo 'beton ' . $concrete_length . '<br>';
        echo 'cegła ' . $brick_length . '<br>';

        echo '<p><br><input type="submit" name="cf-submitted" value="Wyślij"></p>';
        echo '</form>';
    }
}

// Show the third formula - the estimated power needed to cool the building and information about the selected air conditioner
function html_results_forms($power, $width, $length, $height, $number_of_people, $number_of_agd_devices, $air_conditioners, $instalation_length)
{
    if (isset($_POST['cf-submitted']) && !isset($_POST['cf-result'])) {
        echo '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
        echo '<div style="color: black;">';

        if ($power > 24 || empty($air_conditioners)) {
            echo '<h4>';
            echo "Wyniki kalkulacji:";
            echo '</h4>';
        } else {
            echo '<h4>';
            echo "Poniższe wyniki zostały wysłane na podany w formularzu adres e-mail:";
            echo '</h4>';
        }

        $index = 1;

        echo '<p>';
        echo 'Dla powierzchni chłodzenia: <strong>' . esc_html($width * $length) . ' m2</strong>, wysokości pokoju <strong>' . esc_html($height) . ' m</strong>,<br>';
        echo '<strong>' . esc_html($number_of_people) . '</strong> domownika(ów), ' . '<strong>' . esc_html($instalation_length) . '</strong> m instalacji '. 'oraz <strong>' . esc_html($number_of_agd_devices) . '</strong> urządzenia(ń) agd, ';
        echo 'szacowana ilość mocy potrzebna do ochłodzenia domu to około: <strong>' . esc_html($power) . ' kW</strong><br>';
        echo '</p>';

        echo '<h4>';
        echo '<br>';
        echo 'Szczegóły dotyczące wybranych klimatyzatorów:<br>';
        echo '</h4>';

        if ($power > 24 || empty($air_conditioners)) {
            echo '<p>';
            echo 'Nie znaleziono odpowiedniego klimatyzatora<br>';
            echo 'W celu doboru urządzenia prosimy o bezpośredni kontakt lub o wprowadzenie innych danych<br>';
            echo '</p>';
        } else {
            foreach ($air_conditioners as $air_conditioner) {
                echo '<p>';
                echo 'Urządzenie nr <strong>' . $index . '</strong><br>';
                echo 'Nazwa: <strong>' . esc_attr($air_conditioner["Nazwa"]) . '</strong><br>';
                echo 'Numer id: <strong>' . esc_attr($air_conditioner["Id"]) . '</strong><br>';
                echo 'Moc: <strong>' . esc_attr($air_conditioner["Moc"]) . ' kW </strong><br>';
                echo 'Cena od: <strong>' . esc_attr($air_conditioner["Cena [PLN]"]) . ' zł brutto (8% VAT) </strong><br>';
                echo 'Strona producenta: <strong>' . esc_attr($air_conditioner["Link"]) . '</strong><br>';
                echo '<br>';
                echo '</p>';
                $index++;
            }

            echo '<h5>';
            echo '<br>';
            echo 'Kalkulacja Zawiera:<br>';
            echo '</h5>';

            echo '<p style="font-size:10px; line-height: 2;">';
            echo '- Dostarczenie materiałów niezbędnych do instalacji<br>';
            echo '- Wniesienie klimatyzatora<br>';
            echo '- Montaż jednostki wewnętrznej<br>';
            echo '- Wykonanie instalacji freonowej, sterowniczej do 3m długości<br>';
            echo '- Montaż jednostki zewnętrznej na balkonie/ gruncie (wspornik podłogowy)<br>';
            echo '- Wykonanie przewiertu przez ścianę<br>';
            echo '- Grawitacyjne oprowadzenie skroplin<br>';
            echo '- Podłączenie zasilania do istniejącej instalacji elektrycznej<br>';
            echo '- Wykonanie próżni w układzie<br>';
            echo '- Uruchomienie i sprawdzenie parametrów pracy urządzenia<br>';
            echo '- Przeszkolenie inwestora<br>';
            echo '</p>';

            echo '<h5>';
            echo '<br>';
            echo 'Kalkulacja nie zawiera:<br>';
            echo '</h5>';

            echo '<p style="font-size:10px; line-height: 2;">';
            echo '- Bruzdowanie ścian (pustak / cegła / beton) - płatne dodatkowo (100 / 100 / 300 zł /netto/mb)<br>';
            echo '- Montaż instalacji w korytkach (25 zł /netto/mb)<br>';
            echo '- Gipsowania<br>';
            echo '- Malowania<br>';
            echo '- Ingerencji w instalacje elektryczną (w rozdzielnię)<br>';
            echo '- Pompki skroplin (dodatkowo płatna)<br>';
            echo '</p>';
        }
        echo '</div>';
        echo '</form>';
    }
}
?>
