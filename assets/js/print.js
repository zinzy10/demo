$(document).ready(function() {
    $('#active2').addClass('active');
    tables_prints();
  
    $('input[name="checkbox"]').on('change', function() {
        $('input[name="checkbox"]').removeClass('is-invalid');
    });
  
    $('#day, #night').on('change', function() {
        $('#day').prop('disabled', $('#night').is(':checked'));
        $('#night').prop('disabled', $('#day').is(':checked'));
    });
    
    $('#initial_date, #final_date').on('input', function() {
        $(this).removeClass('is-invalid');
    });
});

function tables_prints() {
  
    grandTotals = $('#total').DataTable({
        ajax: '../src/controllers/print.ajax.php?opc=3',
        info: false,
        lengthChange: false,
        searching: false,
        paging: false,
        ordering: false,
        columnDefs: [
            {className: "dt-center", targets: "_all"}
        ]
    });
  
    tableF1 = $('#MF1').DataTable({
        ajax: '../src/controllers/print.ajax.php?opc=1',
        info: false,
        lengthChange: false,
        searching: false,
        paging: false,
        ordering: false,
        columnDefs: [
            {className: "dt-center", targets: [1, 2, 3, 4]}
        ]
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
  
function clean() {
    
    $('input[name="checkbox"], input[name="shift"]').prop('checked', false).prop('disabled', false).removeClass('is-invalid');
    $('#select_option').removeClass('is-invalid').val('0');
    selectOption();
    refresh();

    Swal.fire({
        title: ' ',
        text: 'Please wait...',
        timer: 1200,
        timerProgressBar: true,
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}
  
function refresh() {
  
    let tablesf1 = $('#MF1').DataTable();
    let tableTotal = $('#total').DataTable();
  
    tablesf1.ajax.url('../src/controllers/print.ajax.php?opc=1').load();
    tableTotal.ajax.url('../src/controllers/print.ajax.php?opc=3').load();
}
  
function sendData() {
  
    let selectOption = $('#select_option').val();
    let radios = $('input[name="checkbox"]:checked').val();
    let date1 = $('#initial_date').val();
    let date2 = $('#final_date').val();
    let isValid = true;
  
    if (!selectOption && !radios) {
        $('#select_option').addClass('is-invalid');
        $('input[type="radio"]').addClass('is-invalid');
        isValid = false;
    } else {
        if (!selectOption) {
            $('#select_option').addClass('is-invalid');
            isValid = false;
        }
        if (!radios) {
            $('input[type="radio"]').addClass('is-invalid');
            isValid = false;
        }
        if (selectOption === '7') {
            if (!date1 || !date2) {
                $('#initial_date, #final_date').addClass('is-invalid');
                isValid = false;
            } else {
                if (date1 > date2) {
                    new Notify({
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
                    isValid = false;
                }
            }
        }
    }
  
    if  (!isValid) return;
    
    Swal.fire({
        title: 'Processing data',
        text: 'Please wait...',
        timer: 1500,
        timerProgressBar: true,
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    loadData('MF1', 1, 4);
    loadData('total', 3, 6);
}
  
function loadData(tableId, url, urlAll) {
  
    let selectOption = $('#select_option').val();
    let table = $(`#${tableId}`).DataTable();
    let radios = $('input[name="checkbox"]:checked').val();
    let shift = $('input[name="shift"]:checked').val();
    let date1 = $('#initial_date').val();
    let date2 = $('#final_date').val();
    let opc = (radios === 'all') ? url : urlAll;
  
    table.ajax.url(`../src/controllers/print.ajax.php?opc=${opc}&radios=${radios}&opcion=${selectOption}&date1=${date1}&date2=${date2}&shift=${shift}`).load();
}