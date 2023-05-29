// Get location from address
const form = document.getElementById("charge_point_form");
const addBtn = document.querySelector('[name="addChargePointDetails"]');
const updateBtn = document.querySelector('[name="updateChargePointDetails"]');
const deleteBtn = document.querySelector('[name="deleteChargePointDetails"]');

// Add charge point
if (addBtn) {
    addBtn.addEventListener('click', async () => {
        form.querySelector('[name="method"]').setAttribute('value', "add");

        let geocoder = new google.maps.Geocoder();
        let address = `${form.querySelector('input[name="address1"]').value}, ${form.querySelector('input[name="address2"]').value}, ${form.querySelector('input[name="post_code"]').value}`;

        await geocoder.geocode({
            'address': address
        }).then((results) => {
            let lat = results.results[0].geometry.location.lat();
            let lng = results.results[0].geometry.location.lng();
            form.querySelector('[name="lat"]').value = parseFloat(lat);
            form.querySelector('[name="lng"]').value = parseFloat(lng);
            form.submit();
        }).catch((error) => { alert(error); });
    });
}

// Update charge point
if (updateBtn) {
    updateBtn.addEventListener('click', async () => {
        form.querySelector('[name="method"]').setAttribute('value', "update");
        let geocoder = new google.maps.Geocoder();
        let address = `${form.querySelector('input[name="address1"]').value}, ${form.querySelector('input[name="address2"]').value}, ${form.querySelector('input[name="post_code"]').value}`;
        await geocoder.geocode({
            'address': address
        }).then((results) => {
            let lat = results.results[0].geometry.location.lat();
            let lng = results.results[0].geometry.location.lng();
            form.querySelector('[name="lat"]').value = parseFloat(lat);
            form.querySelector('[name="lng"]').value = parseFloat(lng);
            form.submit();
        }).catch((error) => { alert(error); });
    });
}

// Delete charge point
if (deleteBtn) {
    deleteBtn.addEventListener('click', () => {
        form.querySelector('[name="method"]').setAttribute('value', "delete");
        form.submit();
    });
}

// Cost setter
const slider = document.getElementById('cost-slider');
noUiSlider.create(slider, {
    start: document.getElementById('cost').value,
    connect: [true, false],
    range: {
        'min': [0],
        'max': [10],
    },
    'step': 0.01,
    'tooltips': true,
});

slider.noUiSlider.on('update', function () {
    document.getElementById('cost').value = slider.noUiSlider.get(true);
});

document.getElementById('cost').addEventListener('input', function () {
    slider.noUiSlider.set([document.getElementById('cost').value]);
});