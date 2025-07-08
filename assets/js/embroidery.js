var tableF3;

$(document).ready(function() {
    $('#active2').addClass('active');
    table_emb();

    $('#day, #night').on('change', function() {
        $('#day').prop('disabled', $('#night').is(':checked'));
        $('#night').prop('disabled', $('#day').is(':checked'));
    });

    $('#initial_date, #final_date').on('input', function() {
        $(this).removeClass('is-invalid');
    });
});

function table_emb() {

    tableF3 = $('#BF3').DataTable({
        ajax: '../src/controllers/embroidery.ajax.php?opc=1',
        info: false,
        lengthChange: false,
        searching: false,
        paging: false,
        ordering: false,
        columnDefs: [
            {className: "dt-center", targets: [1, 2, 3, 4]}
        ]
    });

    tableF3.on('draw', function() {
        //Funcion para limpiar y convertir valores a numero
        let parseNumber = val => {
            if (!val) return 0;
            let clean = val.replace(/,/g, '');
            return parseFloat(clean) || 0;
        };

        //Funcion para formatear con separador de miles
        let formatt = val => Intl.NumberFormat('en-US').format(val);

        //Sumar columnas con converison segura
        let totals = [1, 2, 3, 4].map(index => {
            return tableF3
            .data()
            .toArray()
            .reduce((acc, row) => {
                let val = $('<div>').html(row[index]).text();
                return acc + parseNumber(val);
            }, 0);
        });

        //insertar los totales en el footer
        totals.forEach((total, i) => {
            tableF3.columns(i + 1).footer().to$().html(formatt(total));
        });
    });
    
}

function selectOption() {

    let selectDate = $('#select_option').val();
  
    if (selectDate === '7') {
        $('#range').show();
        $('#select_option').removeClass('is-invalid');
    } else {
        $('#range').hide();
        $('#initial_date, #final_date').val('');
        $('#initial_date, #final_date, #select_option').removeClass('is-invalid');
    }
}

function loadData() {

    let selectOption = $('#select_option').val();
    let shift = $('input[name="shift"]:checked').val();
    let date1 = $('#initial_date').val();
    let date2 = $('#final_date').val();

    tableF3.ajax.url(`../src/controllers/embroidery.ajax.php?opc=1&opcion=${selectOption}&date1=${date1}&date2=${date2}&shift=${shift}`).load();
}

function sendData() {

    let selectOption = $('#select_option').val();
    let date1 = $('#initial_date').val();
    let date2 = $('#final_date').val();

    if (!selectOption) {
        $('#select_option').addClass('is-invalid');
    } else if (selectOption == '7') {
        if (date1 && date2) {
            if (date1 > date2) {
                new Notify ({
                    status: 'error',
                    title: 'Error Date',
                    text: 'Incorrect date order',
                    effect: 'slide',
                    speed: 250,
                    showIcon: true,
                    showCloseButton: true,
                    autoclose: true,
                    autotimeout: 3000,
                    type: 'outline',
                    position: 'right top',
                });
                $('#initial_date, #final_date').val('');
            } else {
                loadData();
            }    
        } else {
            $('#initial_date, #final_date').addClass('is-invalid');
        }
    } else {
        loadData();
    }
}

function clean() {

    $('input[name="shift"]').prop('disabled', false).prop('checked', false);
    $('#select_option').val('0');
    selectOption();
    tableF3.ajax.url('../src/controllers/embroidery.ajax.php?opc=1').load();
}