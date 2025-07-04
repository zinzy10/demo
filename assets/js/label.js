var tableF1;
var tableF8;
var tableF7;
var grandTotals;

$(document).ready(function() {
    $('#active2').addClass('active');
    table_label();

    $('#day, #night').on('change', function() {
        $('#day').prop('disabled', $('#night').is(':checked'));
        $('#night').prop('disabled', $('#day').is(':checked'));
    });

    $('#initial_date, #final_date').on('input', function() {
        $(this).removeClass('is-invalid');
    });
});

function table_label() {

    tableF1 = $('#F1').DataTable({
        ajax: '../src/controllers/label.ajax.php?opc=1',
        info: false,
        lengthChange: false,
        searching: false,
        paging: false,
        ordering: false,
        columnDefs: [
          {className: "dt-center", targets: [1, 2, 3, 4]}
        ]
    });

    tableF8 = $('#F3').DataTable({
        ajax: '../src/controllers/label.ajax.php?opc=2',
        info: false,
        lengthChange: false,
        searching: false,
        paging: false,
        ordering: false,
        columnDefs: [
          {className: "dt-center", targets: [1, 2, 3, 4]}
        ]
    });
    
    tableF7 = $('#F7').DataTable({
        ajax: '../src/controllers/label.ajax.php?opc=3',
        info: false,
        lengthChange: false,
        searching: false,
        paging: false,
        ordering: false,
        columnDefs: [
          {className: "dt-center", targets: [1, 2, 3, 4]}
        ]
    });
    
    grandTotals = $('#total').DataTable({
        ajax: '../src/controllers/label.ajax.php?opc=4',
        info: false,
        lengthChange: false,
        searching: false,
        paging: false,
        ordering: false,
        columnDefs: [
          {className: "dt-center", targets: "_all"}
        ]
    });
        
}

function selectOption() {
    
    let selectDate = $('#select_option').val();
  
    if (selectDate === '7') {
        $('#range').show();
        $('#select_option').removeClass('is-invalid');
    } else{
        $('#range').hide();
        $('#initial_date, #final_date').val('');
        $('#initial_date, #final_date, #select_option').removeClass('is-invalid');
    }
}

function loadData(tableId, opc) {

    let selectOption = $('#select_option').val();
    let table = $(`#${tableId}`).DataTable();
    let shift = $('input[name="shift"]:checked').val();
    let date1 = $('#initial_date').val();
    let date2 = $('#final_date').val();

    table.ajax.url(`../src/controllers/label.ajax.php?opc=${opc}&opcion=${selectOption}&date1=${date1}&date2=${date2}&shift=${shift}`).load();

}

function refresh() {
  
    tableF1.ajax.url('../src/controllers/label.ajax.php?opc=1').load();
    tableF8.ajax.url('../src/controllers/label.ajax.php?opc=2').load();
    tableF7.ajax.url('../src/controllers/label.ajax.php?opc=3').load();
    grandTotals.ajax.url('../src/controllers/label.ajax.php?opc=4').load();
}

function sendData() {

    let selectOption = $('#select_option').val();
    let date1 = $('#initial_date').val();
    let date2 = $('#final_date').val();
  
    if (!selectOption) {
        $('#select_option').addClass('is-invalid');
    } else if (selectOption === '7') {
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
            loadData('F1', 1);
            loadData('F3', 2);
            loadData('F7', 3);
            loadData('total', 4);
        }
      } else {
        $('#initial_date, #final_date').addClass('is-invalid');
      }  
    } else {
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
        loadData('F1', 1);
        loadData('F3', 2);
        loadData('F7', 3);
        loadData('total', 4);
    }
}

function clean() {

    $('input[name="shift"]').prop('checked', false).prop('disabled', false);
    $('#select_option').val('0');
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