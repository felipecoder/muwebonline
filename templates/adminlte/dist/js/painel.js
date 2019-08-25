$(document).ready(function() {
	$.getJSON('dist/js/countries.json', function (data) {

        var items = [];
        var options = '<option value="" selected disabled>escolha um país</option>';    

        $.each(data, function (key, val) {
            options += '<option value="' + val.value + '">' + val.text + '</option>';
            });                 
        $("#countrieslist").html(options);

    });

    $.getJSON('dist/js/states.json', function (data) {

        var items = [];
        var options = '<option value="" selected disabled>escolha um estado</option>';    

        $.each(data, function (key, val) {
            options += '<option value="' + val.sigla + '">' + val.nome + '</option>';
            });                 
        $("#stateslist").html(options);

    });
});