$(document).ready(function() {
    remove();
	show();
	cursor();
	rememberUser();
	focus();
	$('#cancel1, #cancel2, #cancel3, #cancel4').on('click', function () {
        $('#newPass, #confirmPass').val('').removeClass('is-invalid');
    });
});

function changes() {

	let $newPass = $('#newPass');
	let $confirmPass = $('#confirmPass');

	if (/\s/.test($newPass.val())) {
        notify('error', 'Error Password', "Password can't contain spaces");
        $newPass.add($confirmPass).val('');
		$newPass.focus();
        return;
    }

	if (!$newPass.val() || !$confirmPass.val()) {
        $newPass.add($confirmPass).val('').addClass('is-invalid');
		$newPass.focus();
	} else if ($newPass.val() !== $confirmPass.val()) {
        notify('error', 'Erorr Password', 'The password is no the same');
        $newPass.add($confirmPass).val('');
		$newPass.focus();
	} else if ($newPass.val() === $confirmPass.val()){
		if ($newPass.val().length < 8 ){
            notify('error', 'Error Password', 'The password is less than 8 characters');
            $newPass.add($confirmPass).val('');
			$newPass.focus();
		} else {
            $('#changePass').modal('hide');
            $('#confirm').modal('show');
		}
	}
}

function confirm() {

	let user = $('#user').val();
    let newPass = $('#newPass').val();

    $.ajax({
        data:{pass:newPass, user:user},
        type: 'POST',
        url:'src/controllers/validation.ajax.php?opc=1'
    })
    .done(function(res){
		notify('success', '', 'Successful password change');
        $('#confirm').modal('hide');
        $('#newPass, #confirmPass, #pass').val('');
		$('#pass').focus();
    })
    .fail(function(){
        console.log('error');
    });
}

function remove() {

	$('#newPass').on('input', function() {
        $('#newPass').removeClass('is-invalid');
    });

    $('#confirmPass').on('input', function() {
        $('#confirmPass').removeClass('is-invalid');
    });
}

function show() {

	let $input = $('#pass');
	let $newPass = $('#newPass');
	let $confirm = $('#confirmPass');
	let $eye = $('#showPass');
	let $eye2 = $('#showPass1');

	$eye.on('click', function() {
		if ($eye.hasClass('fa-eye-slash')) {
			$eye.removeClass('fa-eye-slash').addClass('fa-eye');
			$input.attr('type', 'text');
		} else {
			$eye.removeClass('fa-eye').addClass('fa-eye-slash');
			$input.attr('type', 'password');
		}
	});

	$eye2.on('click', function() {
		if ($eye2.hasClass('fa-eye-slash')) {
        	$eye2.removeClass('fa-eye-slash').addClass('fa-eye');
            $newPass.add($confirm).attr('type', 'text');
    	} else {
        	$eye2.removeClass('fa-eye').addClass('fa-eye-slash');
            $newPass.add($confirm).attr('type', 'password');
    	}
	});
}

function sendData(){

    let username = $('#user').val();
    let password = $('#pass').val();

    $('#sign').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin fa-xl"></i>');
    if (username && password) {

        $.ajax({
            data:{opcion:'login',username:username, password:password},
            type:'POST',
            url:'src/controllers/validation.ajax.php?opc=2'
        })
        .done(function(res){
            if(res == 0){
				notify('warning', 'Error Sign In', 'Incorrect user or password');
                $('#sign').prop('disabled', false).html('Sign In');
                $('#pass').val('').focus();
            } else if (res == 1) {
                window.location.href = 'pages/home_production.php';
                // $('#sign').prop('disabled', false).html('Sign In');
			} else if (res == 2) {
				window.location.href = 'pages/home.php';
                // $('#sign').prop('disabled', false).html('Sign In');
			} else {
                $('#sign').prop('disabled', false).html('Sign In');
                $('#changePass').modal('show');
                $('#changePass').on('shown.bs.modal', function () {
                    $('[data-bs-toggle="popover"]').popover();
					$('#newPass').focus();
                });
            }
        })
        .fail(function(){
            console.log('error');
        });
    } else if (!username) {
		notify('warning', 'Error Sign In', 'Enter your user and password');
		$('#user').focus();
        $('#sign').prop('disabled', false).html('Sign In');
    } else {
		notify('warning', 'Error Sign In', 'Enter your user and password');
		$('#pass').focus();
        $('#sign').prop('disabled', false).html('Sign In');
	}

    let rememberMe = $('#rememberMe').is(':checked');

    if (rememberMe) {
        localStorage.setItem('username', username);
    } else {
        localStorage.removeItem('username');
    }
}

$('#pass, #user').on('keypress', function (event) {
    
    if (event.which === 13) {
        event.preventDefault();
        sendData();
    }
});

function rememberUser() {

    let savedUsername = localStorage.getItem('username');

    if (savedUsername) {
        $('#user').val(savedUsername);
        $('#rememberMe').prop('checked', true);
    }
}

function cursor() {

    let user = localStorage.getItem('username');
    let $usernameInput = $('#user');
    let $passwordInput = $('#pass');
	let $div = $('.input-pass');

    if (!user) {
        $usernameInput.focus();
        $('.input-user').addClass('focused');
    } else {
        $passwordInput.focus();
		$div.addClass('focused');
    }
}

function focus() {
	
    let $user = $('#user');
	let $pass = $('#pass');
	let $div = $('.input-pass');

	$pass.on('focus', function() {
		$div.addClass('focused');
	}).on('blur', function() {
		$div.removeClass('focused');
	});

    $user.on('focus', function() {
		$user.addClass('focused');
	}).on('blur', function() {
		$user.removeClass('focused');
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

function demo() {
    $('#demo').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin fa-xl"></i>');
    
    $.ajax({
        url: "src/controllers/validation.ajax.php?opc=2",
        type: "POST",
        data: { opcion: 'login', user: 'demo'},
        dataType: 'json',
        success: function (res) {
            if (res == 0) {
                notify('warning', 'Error Sign In', 'Incorrect user or password');
            } else if (res == 2) {
                window.location.href = 'pages/home.php';
            }
        },
        error: function (xhr, status, error) {
            console.log(xhr);
        },
    });
}