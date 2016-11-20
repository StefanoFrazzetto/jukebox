var sliders = $('.slider-eq');

sliders.slider({
    //Config
    range: "min",
    animate: animation_medium,
    orientation: "vertical",
    value: 50,
    min: 0,
    max: 100,
    stop: function () {
        apply_eq_values(get_eq_values());
    }
});

$('#presets').find('li').click(function () {
    var curve = $(this).attr('curve');

    curve = serialise_string(curve);

    set_eq_values(curve);

    $('#presets').find('.active').removeClass('active');

    $(this).addClass('active');

    apply_eq_values(get_eq_values());
});

function get_eq_values() {
    var values = [];

    sliders.each(function (index, sldr) {
        values.push($(sldr).slider('value'));
    });

    return values;
}

function set_eq_values(values) {
    values.forEach(function (value, index) {
        sliders.eq(index).slider('value', value);
    });
}

function apply_eq_values(values) {
    var values_string = deserialise_values(values);

    var query = 'assets/cmd/exec.php?cmd=equalizer_set&args=' + values_string + '&sudo=1';

    $.ajax(query);
}

function get_current_eq_values(callback) { // From the actual jukebox
    var query = 'assets/cmd/exec.php?cmd=equalizer_get&sudo=1';

    $.ajax(query).done(function (data) {
        data = data.replace(/\n/g, ' ');
        if (data == "") {
            error("Unable to load EQ configuration");
            return;
        }
        callback(data);
    });
}

function serialise_string(string) {
    return string.split(' ');
}

function deserialise_values(values) {
    return values.join(' ');
}

get_current_eq_values(function (string) {
    set_eq_values(serialise_string(string));
});