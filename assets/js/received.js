var msj;
var storeTom;
var tableF1;

$(document).ready(function() {
    orders();
    
    $('#reports').on('hidden.bs.modal', function () {
        $('#reportes').removeClass('is-invalid').val('0');
    });
});

$(document).on('change', '#filter', function() {
    if ($(this).prop('checked')) {
        $('#generate').addClass('d-none');
        $('#next').removeClass('d-none').prop('disabled', false);
    } else {
        $('#generate').removeClass('d-none');
        $('#next').addClass('d-none').prop('disabled', true);
    }
});

$(document).on('change', '#store', function() {
    $('.ts-wrapper').removeClass('was-validate');
});

$(document).on('keydown', '#form input, #form2 input', function(event) {
    if(event.key === "Enter") {
    event.preventDefault();
    }
});

$(document).on('change', '#dateOption', function() {
    let $option = $('#dateOption');
    let $dateRange = $('#rangeDate');
    $option.removeClass('is-invalid');
    $('#startDate, #finalDate').val('');

    if ($option.val() === '5') {
        $dateRange.removeClass('d-none');
    } else if (!$dateRange.hasClass('d-none')) {
        $dateRange.addClass('d-none');
    }
});

$(document).on('input', '#startDate, #finalDate, #initial_date, #final_date', function() {
    $(this).removeClass('is-invalid');
});

function orders() {

    tableF1 = $('#myTable1').DataTable({
        ajax: '../src/controllers/received.ajax.php?opc=2',
        info: false,
        lengthChange: false,
        paging: false,
        ordering: false,
        fixedHeader: true,
        scrollY: 345,
        columnDefs: [
            {className: 'text-center', targets: [2, 3, 4, 5, 6, 7, 8]}
        ]
    });
}

function sendData() {

    let selectOption = $('#select_option').val();
    let date1 = $('#initial_date').val();
    let date2 = $('#final_date').val();
    let $selectDate = $('#select_option');

    if (!selectOption) {
        $selectDate.addClass('is-invalid');
    } else if (selectOption == '7') {
        if (date1 && date2) {
            if (date1 > date2) {
                notify('error', 'Error Date', 'Incorrect date order');
                $('#initial_date, #final_date').removeClass('is-invalid').val('');
            } else {
                msj = Swal.fire({
                        title: 'Processing data',
                        text: 'Please wait...',
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                });
                ordersReceived();
            }    
        } else {
            $('#initial_date, #final_date').addClass('is-invalid');
        }
    } else {
        msj = Swal.fire({
                title: 'Processing data',
                text: 'Please wait...',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            ordersReceived();
    }
}

function clean() {

    $('input[name="checkbox"]').each(function() {
        $(this).prop('checked', false);
    });

    $('#select_option').val('0');
    selectOption();
    tableF1.ajax.url('../src/controllers/received.ajax.php?opc=2').load();
    Swal.fire({
        title: ' ',
        text: 'Please wait...',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        }
    });
}

function ordersReceived() {

    let selectOption = $('#select_option').val();
    let date1 = $('#initial_date').val();
    let date2 = $('#final_date').val();
    let ids = $('.checkBoxes:checked').map((_, element) => element.id).get().filter(id => id !== 'select_all');
    
    tableF1.ajax.url(`../src/controllers/received.ajax.php?opc=1&opcion=${selectOption}&date1=${date1}&date2=${date2}&ids=${JSON.stringify(ids)}`).load();

    tableF1.on('xhr', function(e, settings, json) {
        if (json.data.length === 0) {
            msj.close();
            // notify('error', 'Error Data', 'There is no data available');
        }
    });
}

function selectOption() {

    let $selectDate = $('#select_option');
    let $dateRange = $('#range');
  
      if ($selectDate.val() === '7') {
        $dateRange.show();
        $selectDate.removeClass('is-invalid');
      } else {
        $dateRange.hide();
        $selectDate.removeClass('is-invalid');
        $('#initial_date, #final_date').removeClass('is-invalid').val('');
      }
}

function checkBox() {

    $('#select_all').change(function() {
        $('input[name="checkbox"]').prop('checked', this.checked);
    });
}

function validateForm() {

    let clientValue = $('#clients').val();

    if (!clientValue) {
        $('.ts-wrapper').addClass('was-validate');
        return false;
    } else {
        $('.ts-wrapper').removeClass('was-validate');
    }

    return true;
}

function validation() {

    let option = $('#options').val();
    let store = $('#store').val();
    let date1 = $('#startDate').val();
    let date2 = $('#finalDate').val();
    let dateOption = $('#dateOption').val();
    let isValid = true;

    if (!option) {
        $('#options').addClass('is-invalid');
        isValid = false;
    } else {
        if (!store) {
            $('.ts-wrapper').addClass('was-validate');
            isValid = false;
        }
        if (!dateOption) {
            $('#dateOption').addClass('is-invalid');
            isValid = false;
        }
        if (dateOption === '5') {
            if (!date1 || !date2) {
                $('#startDate, #finalDate').addClass('is-invalid');
                isValid = false;
            } else {
                if (date1 > date2) {
                    notify('error', 'Error Date', 'Incorrect date order');
                    $('#startDate, #finalDate').val('');
                    isValid = false;
                }
            }
        }
    }

    if (!isValid) return;

    if (!$('#next').prop('disabled')) {
        $.ajax({
            url: '../src/controllers/received.ajax.php?opc=5',
            type: 'POST',
            data: {client:store, d1:date1, d2:date2, dateOpc:dateOption, opc:option},
            success: function (res) {
                $('#listEdit').children().not('#legend').remove();
                $('#listEdit').append(res);
                $('#pdf').modal('hide');
                $('#dataTable').modal('show');
            },
            error: function (xhr, status, error) {
                console.log(xhr);
            },
        });
        return;
    }

    $('#generate').html(`<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                         <span role="status">Loading...</span>`).prop('disabled', true);
    $('#back').prop('disabled', true);
    renderPDF();
}

function alert() {

    let list = $('#reportes').val();
    let list2 = $('#clients').val();
    
    if (list) {
        $('#reportes').removeClass('is-invalid');
    }

    if (list2) {
        $('.ts-wrapper').removeClass('was-validate');
    }
}

function dataOption() {

    let option = $('#options').val();
    $('#options').removeClass('is-invalid');
    $('#selectHide, #date').removeClass('d-none');
    if($('#selectHide .ts-wrapper').length == 0) {
        storeTom = new TomSelect('#store',{
            create: false
        });
    }

    $.ajax({
        url: '../src/controllers/received.ajax.php?opc=4',
        method: 'POST',
        data: {option:option},
        success: function(res) {
            $('#store').html(res);
            $('#store').val(null).trigger('change');
            storeTom.clear();
            storeTom.clearOptions();
            storeTom.sync();
        },
        error: function(xhr, status, error) {
            console.log(xhr, status);
        }
    });
}

function renderPDF() {
    
    let option = $('#options').val();
    let store = $('#store').val();
    let dateOption = $('#dateOption').val();
    let date1 = $('#startDate').val();
    let date2 = $('#finalDate').val();

    $.ajax({
        url: '../src/controllers/summary.pdf.php',
        type: 'POST',
        data: {options:option, store:store, dateOpc:dateOption, dateIni:date1, dateFin:date2},
        xhrFields: {
            responseType: 'blob'
        },
        success: function (res) {

            let blob = new Blob([res], { type: 'application/pdf' });
            let link = URL.createObjectURL(blob);

            window.open(link, '_blank');
            $('#back').prop('disabled', false);
            $('#generate').prop('disabled', false).html('Generate PDF');
        },
        error: function (xhr, status, error) {
            console.log(xhr);
        },
    });
}

function editPDF() {

    let option = $('#options').val();
    let store = $('#store').val();
    let dateOption = $('#dateOption').val();
    let date1 = $('#startDate').val();
    let date2 = $('#finalDate').val();
    let check = $('.clientsBox:checked').map((_, element) => element.id).get();

    if(check.length === 0) {
        notify('info', '', 'Please select at least one option');
        return;
    }

    $('#previous').prop('disabled', true);
    $('#generatePDF').html(`<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
        <span role="status">Loading...</span>`).prop('disabled', true);

    $.ajax({
        url: '../src/controllers/summary.pdf.php',
        type: 'POST',
        data: {options:option, store:store, dateOpc:dateOption, dateIni:date1, dateFin:date2, checkClient:check},
        xhrFields: {
            responseType: 'blob'
        },
        success: function (res) {

            let blob = new Blob([res], { type: 'application/pdf' });
            let link = URL.createObjectURL(blob);

            window.open(link, '_blank');
            $('#previous').prop('disabled', false);
            $('#generatePDF').html('Generate PDF').prop('disabled', false);
        },
        error: function (xhr, status, error) {
            console.log(xhr);
        },
    });
}

function reports() {

    let list = $('#reportes').val();

    switch (list) {
        case '1': 
            $.get('../src/controllers/received.ajax.php?opc=3', (res) => {
                
                $('#changes-report').html(`<div class="modal-header">
                    <h5 id="uploadModalLabel" class="modal-title fw-bold">Full Package Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                </div>
                <form id="form" action="../src/controllers/historical.pdf.php" method="post" target="_blank" onsubmit="return validateForm()">
                    <div class="modal-body">
                        <div class="form-group col-12">
                            <select id="clients" name="clients" placeholder="Select an Option" class="form-select form-select-sm" onchange="alert()">
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary fw-bold" data-bs-target="#reports" data-bs-toggle="modal">Previous</button>
                        <button type="submit" class="btn btn-sm btn-primary fw-bold">Generate PDF</button>
                    </div>
                </form>`);
                $('#clients').html(res);
                $('#clients').val(null).trigger('change');
                new TomSelect("#clients",{
                    create: false
                });
                $('#reports').modal('hide');
                $('#pdf').modal('show');
            });
        break;

        case '2': $('#changes-report').html(`<div class="modal-header">
                                    <h5 id="uploadModalLabel" class="modal-title fw-bold">Summary Report</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                                </div>
                                <form id="form2">
                                    <div class="modal-body">
                                        <form>
                                        <div class="form-group col-10 col-sm-8">
                                            <select id="options" name="options" class="form-select form-select-sm" onchange="dataOption()">
                                                <option selected disabled value="0">Select an Option</option>
                                                <option value="1">Client</option>
                                                <option value="2">Store</option>
                                            </select>
                                        </div>
                                        <div id="selectHide" class="form-group col-10 col-sm-8 mt-3 d-none">
                                            <select id="store" name="store" placeholder="Select an Option" class="form-select form-select-sm"></select>
                                        </div>
                                        <div id="date" class="form-group col-10 col-sm-8 mt-3 d-none">
                                            <select id="dateOption" class="form-select form-select-sm" aria-label="select a date option">
                                                <option selected disabled value="0">Select a date option</option>
                                                <option value="1">This Month</option>
                                                <option value="2">Last Month</option>
                                                <option value="3">This Year</option>
                                                <option value="4">Last Year</option>
                                                <option value="5">Date Range</option>
                                            </select>
                                        </div>
                                        <div id="rangeDate" class="col-10 col-sm-8 mt-3 d-none">
                                            <div class="input-group input-group-sm my-3">
                                                <span class="input-group-text">
                                                    <i class="fa-regular fa-calendar fa-lg me-1"></i>Initial Date
                                                </span>
                                                <input type="date" id="startDate" name="startDate" class="form-control form-control-sm" max="2025-06-23">
                                            </div>
                                            <div class="input-group input-group-sm mt-3">
                                                <span class="input-group-text">
                                                    <i class="fa-regular fa-calendar fa-lg me-1"></i>Final Date
                                                </span>
                                                <input type="date" id="finalDate" name="finalDate" class="form-control form-control-sm" max="2025-06-23">
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <input class="form-check-input" type="checkbox" name="checkbox" id="filter" value="1">
                                            <label class="form-check-label" for="filter">Modify table or data (optional)</label>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" id="back" class="btn btn-sm btn-secondary fw-bold" data-bs-target="#reports" data-bs-toggle="modal">Previous</button>
                                        <button type="button" id="next" class="btn btn-sm btn-primary fw-bold d-none" onclick="validation()" disabled>Next</button>
                                        <button type="button" id="generate" class="btn btn-sm btn-primary fw-bold" onclick="validation()">Generate PDF</button>
                                    </div>
                                </form>`);
            $('#reports').modal('hide');
            $('#pdf').modal('show');
        break;
        
        case '3': $('#changes-report').html(`<div class="modal-header">
                                    <h5 id="uploadModalLabel" class="modal-title fw-bold">Upload Orders</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label for="file" class="form-label">Select File</label>
                                        <input type="file" name="file" id="file" class="form-control form-control-sm mt-2 radius-lg">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" id="back" class="btn btn-sm btn-secondary fw-bold" data-bs-target="#reports" data-bs-toggle="modal">Previous</button>
                                    <button type="button" id="generate" class="btn btn-sm btn-primary fw-bold">Download File</button>
                                </div>`);
        break;

        default: 
            $('#reportes').addClass('is-invalid');
    }
}

function notify(status, title, txt) {
    new Notify ({
        status: status,
        title: title,
        text: txt,
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