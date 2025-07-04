var gid;
var goals;

$(document).ready(function() {
    $('#active').addClass('active'); /*Despliega el menu de Sales*/
    tableGoals();

    $('#tableGoals tbody').on('click', 'input[type="checkbox"]', function (e) { /*Selecciona el registro en la tabla de Goals*/
        let row = $(this).closest('tr');
        row.toggleClass('selected', $(this).prop('checked'));
    });

    $('#uploadSales').on('hidden.bs.modal', function() {
        $('#file').removeClass('is-invalid').val('');
    });

    $('#file').on('change', function() {
        $(this).removeClass('is-invalid');
    });

    $('#editSales').on('hidden.bs.modal', function() {
        $('#goals').removeClass('is-invalid').val('');
    });

    $('#goals').on('input', function() {
        $(this).removeClass("is-invalid");
    });

    $('#goals').on("keypress", function(event) {
        if (event.which === 13) {
            updateGoals();
        }
    });  
});

function tableGoals() {

    goals = $('#tableGoals').DataTable({
        ajax: '../src/controllers/sales.ajax.php?opc=6',
        pageLength: 17,
        info: false,
        lengthChange: false,
        order: [[3, 'desc']],
        columnDefs: [
            { orderable: false, targets: [0, 1] },
            { className: "text-center", targets: [2, 3, 4, 5, 6, 7, 8] }
        ]
    });

    goals.on('draw', function () {
        //Función para limpiar y convertir valores a número
        let parseNumber = val => {
            if (!val) return 0;
            let clean = val.replace(/[$,]/g, '');   //Elimina $ y comas
            return parseFloat(clean) || 0;  //Convierte a número
        };

        //Funcion para formatear como moneda
        let formatCurrency = val => new Intl.NumberFormat('en-US', {
            style: 'currency', currency: 'USD'
        }).format(val);

        //Sumar columnas con conversión segura
        let totals = [2, 3, 4, 5, 6, 7, 8].map(index => {
            return goals
            .data()
            .toArray()
            .reduce((acc, row) => {
                let cell = $('<div>').html(row[index]);
                let val = cell.find('div').length 
                    ? cell.find('div').text()  //Extrae texto del <div>
                    : cell.text();  //Usa texto plano si no hay <div>
                return acc + parseNumber(val);
            }, 0);
        });

        //Insertar valores en los footers
        totals.forEach((total, i) => {
            goals.columns(i + 2).footer().to$().html(formatCurrency(total));
        });
    });
}

function update(id) {

    gid = id;
    if ($('input[name="checkBox"]:checked').length > 0) {
        $('input[name="checkBox"]').prop('checked', false);
        $('#tableGoals tbody tr').removeClass('selected');
    }
    $('#editSales').modal('show');
    $('#editSales').on('shown.bs.modal', function() {
        $('#goals').focus();
    });
}

function updateSelect() {

    let checkIds = [];
  
    $('.check-center:checked').each(function() {
        checkIds.push($(this).attr('id'));
    });
  
    if (checkIds.length === 0) {
        notify('info', '', 'Please select at least one goal');
    } else {
        $('#editSales').modal('show');
        $('#editSales').on('shown.bs.modal', function() {
            $('#goals').focus();
        });
    }
}

function updateGoals() {

    let newGoal = $('#goals').val();
    let checkIds = [];
    $('#updateGoals').prop('disabled', true);
  
    $('input[name="checkBox"]:checked').each(function() {
        checkIds.push($(this).attr('id'));
    });

    if (checkIds.length === 0) {
        checkIds.push(gid);
    }
  
    if (!/^\d*\.?\d+$/.test(newGoal) || newGoal === '') {
        $('#goals').addClass('is-invalid').val('').focus();
        $('#updateGoals').prop('disabled', false);
    } else {
        $.ajax({
            url: '../src/controllers/sales.ajax.php?opc=9',
            method: 'POST',
            data: {ids:checkIds, value:newGoal},
            success: function(res) {
                if (res == 1) {
                    goals.ajax.reload();
                    $('#goals').val('');
                    $('#updateGoals').prop('disabled', false);
                } else {
                    notify('error', 'Erorr', "Don't have permission");
                }
                $('#editSales').modal('hide');
                $('#updateGoals').prop('disabled', false);
            },
            error: function(xhr, status, error) {
                console.log(xhr);
            }
        });
    }
}

function uploadFile() {

    let fileInput = $('#file')[0].files[0];
    let $send = $('#send');
    let $fileInput = $('#file');

    if(!fileInput) {
        $fileInput.addClass('is-invalid');
        return;
    }

    let fileName = fileInput.name;
    let fileExtension = fileName.split('.').pop().toLowerCase();
    $send.prop('disabled', true).html('<i class="fa-regular fa-spinner fa-spin fa-lg"></i>');

    if (fileExtension !== 'csv') {
        notify('error', 'File Filed', 'Select CSV files only');
        $fileInput.val('');
        $send.prop('disabled', false).html('Upload');
        return;
    }

    let formData = new FormData();
    formData.append('file', fileInput);

    $.ajax({
        url: '../src/controllers/sales.ajax.php?opc=7',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            if (res == 0) {
                notify('error', 'File filed', 'File not sent');
                $send.prop('disabled', false).html('Upload');
            } else {
                notify('success', 'File Send', 'Successful Update');
                $('#uploadSales').modal('hide');
                $send.prop('disabled', false).html('Upload');
                goals.ajax.reload();
            }
        },
        error: function(xhr, status, error) {
            console.log(error);
        }
    });
}

function notify(status, title, text) {
    new Notify ({
        status: status,
        title: title,
        text: text,
        effect: 'slide',
        speed: 250,
        showIcon: true,
        showCloseButton: true,
        autoclose: true,
        autotimeout: 3000,
        type: 'outline',
        position: 'right top',
    });
}