$(document).ready(function() {
    table_user();
});

function table_user() {

    let disabled = $('#disabled').val();

    userTable = $('#users').DataTable({
        ajax: '../src/controllers/members.ajax.php?opc=1',
        info: false,
        lengthChange: false,
        paging: false,
        ordering: false,
        columnDefs: [
            {className: "text-start", targets: [0]},
            {
                targets: [5, 6, 7, 8, 9],
                render: function(data, type, row, meta) {
                    if (type === 'display') {
                        let permiso = "";
                        if (meta.col === 5) permiso = 'acces';
                        else if (meta.col === 6) permiso = 'us_admin';
                        else if (meta.col === 7) permiso = 'edit';
                        else if (meta.col === 8) permiso = 'us_view';
                        else if (meta.col === 9) permiso = 'production';
                        return '<div class="form-switch text-center"><input type="checkbox" class="form-check-input cursor-pointer" data-id="' + row[0] + '" data-permiso="' + permiso + '" ' + (data == 1 ? 'checked' : '') + ' ' + disabled + '></div>';
                    }
                    return data;
                }
            }
        ]
    });

    $('#users').on('change', '.form-check-input', function() {
        let user = $(this).data('id');
        let permit = $(this).data('permiso');
        let value = $(this).prop('checked') ? 1 : 0;

        $.ajax({
            type: 'POST',
            url: '../src/controllers/members.ajax.php?opc=2',
            data: {user:user, permit:permit, value:value},
            success: function(res) {
                if (res == 0) {
                    notify('error', 'Error Permission', "Don't have permission");
                    userTable.ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    });
}

function validField(txt) {
    return /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(txt.trim());
}

function add_user() {

    let tableUser = $('#users').DataTable();
    let name = $('#name').val();
    let lastname = $('#lastName').val();
    let user = $('#username').val();
    let pass = $('#password').val();
    let correo = $('#email').val();
    let email = correo + "@factory1mfg.com";
    let puesto = $('#puesto').val();
    let isValid = true;

    if (!name || !lastname || !user || !pass || !correo || !puesto) {
        notify('error', 'Empty Fields', 'Complete all the fields.');
        return;
    }

    ['name', 'lastName', 'puesto'].forEach(function(campo) {
        let value = $('#' + campo).val().trim();
        if (!validField(value)) {
            $('#' + campo + '-msg').html('✖ Only letters').css('color', 'red');
            isValid = false
        } else {
            $('#' + campo + '-msg').html('');
        }
    });

    if (user) {
        $.post('../src/controllers/members.ajax.php?opc=5', {username:user}, function(res) {
            if (res == 1) {
                $('#username-msg').html('✖ User not available').css('color', 'red');
                isValid = false;
            } else {
                $('#username-msg').html('');
            }
        });
    }

    if (!isValid) return

    $.ajax({
        url: '../src/controllers/members.ajax.php?opc=3',
        method: 'POST',
        data: {name:name, lastname:lastname, username:user, password:pass, email:email, puesto:puesto},
        success: function(res) {
            if (res == 1) {
                notify('success', '', 'User added successfully');
                $('#addUser').modal('hide').find('input, select').val('');
                $('#username-msg').html('');
                tableUser.ajax.reload();
            } else {
                notify('error', 'Error Permission', "Don't have permission");
            }
        },
        error: function(xhr, status, error) {
          console.log(xhr);
        }
    });
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