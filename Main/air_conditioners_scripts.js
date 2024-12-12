// Enable or Disable concrete and brick slides based on selected radio button
document.addEventListener("DOMContentLoaded", function() {
    const nothingRadio = document.getElementById('use_nothing');
    const troughRadio = document.getElementById('use_trough');
    const troughLength = document.getElementById('trough_length');
    const concreteRadio = document.getElementById('use_concrete');
    const concreteLength = document.getElementById('concrete_length');
    const brickRadio = document.getElementById('use_bricks');
    const brickLength = document.getElementById('brick_length');
    const nothingValue = document.getElementById('nothing_value');

    if (nothingRadio && troughRadio && troughLength && concreteRadio && concreteLength && brickRadio && brickLength && nothingValue) {
        function updateSliders() {
            if (nothingRadio.checked) {
                troughLength.disabled = true;
                concreteLength.disabled = true;
                brickLength.disabled = true;
                troughLength.value = 0;
                concreteLength.value = 0;
                brickLength.value = 0;
                document.getElementById('trough_output').value = troughLength.value;
                document.getElementById('concrete_output').value = concreteLength.value;
                document.getElementById('brick_output').value = brickLength.value;
                nothingValue.value = 0;
            } else {
                nothingValue.value = 0;
                if (troughRadio.checked) {
                    troughLength.disabled = false;
                    concreteLength.disabled = true;
                    brickLength.disabled = true;
                    document.getElementById('concrete_output').value = 1;
                    document.getElementById('brick_output').value = 1;
                } else if (concreteRadio.checked) {
                    troughLength.disabled = true;
                    concreteLength.disabled = false;
                    brickLength.disabled = true;
                    document.getElementById('trough_output').value = 1;
                    document.getElementById('brick_output').value = 1;
                } else if (brickRadio.checked) {
                    troughLength.disabled = true;
                    concreteLength.disabled = true;
                    brickLength.disabled = false;
                    document.getElementById('concrete_output').value = 1;
                    document.getElementById('trough_output').value = 1;
                }
            }
        }

        nothingRadio.addEventListener('change', updateSliders);
        troughRadio.addEventListener('change', updateSliders);
        concreteRadio.addEventListener('change', updateSliders);
        brickRadio.addEventListener('change', updateSliders);

        updateSliders();
    } else {
        console.error("One or more elements are missing from the DOM.");
    }
});