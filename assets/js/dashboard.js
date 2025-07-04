$(document).ready( function () {
    session();
    toggle();
    navbarBlur();
    scrollFunction();
    show();
    removes();

    $('.dropdown-profile').on('click', function(event) {
        event.stopPropagation();
    });

    $('#changePass').on('shown.bs.modal', function () {
        $('[data-bs-toggle="popover"]').popover();
        $('#newPass').focus();
    });

    $('#cancel1, #cancel2, #cancel3, #cancel4').on('click', function () {
        $('#newPass, #confirmPass').val('').removeClass('is-invalid');
    });
});

function toggle() {

    let $sidenav = $('#nBar');
    let $toggleIcon = $('#toggleIcon');
    let $closeIcon = $('#closeIcon');
    let $toggleClose = $('#toggleClose');
    let $toggleOpen = $('#toggleOpen');
  
    $toggleIcon.on('click', function () {
        let isOpen = $sidenav.toggleClass('sidenav-show').hasClass('sidenav-show');
        $toggleOpen.toggleClass('show-toggle', isOpen).toggleClass('hide-toggle', !isOpen);
        $toggleClose.toggleClass('show-toggle', !isOpen).toggleClass('hide-toggle', isOpen);
    });
  
    $closeIcon.on('click', function () {
      $sidenav.removeClass('sidenav-show');
      $toggleOpen.addClass('hide-toggle').removeClass('show-toggle');
      $toggleClose.addClass('show-toggle').removeClass('hide-toggle');
    });
}

function navbarBlur() {

    let $navbar = $('#barBlur');
    let navbarScrollActive = $navbar.data('scroll') === true;
    let scrollDistance = 5;
    let classes = 'blur shadow-blur';

    if (!navbarScrollActive || !$navbar.length) return;

    function onScroll() {
        let scrollPos = $(window).scrollTop();
        if (scrollPos > scrollDistance) {
            $navbar.addClass(classes).removeClass('shadow-none');
        } else {
            $navbar.removeClass(classes).addClass('shadow-none');
        }
    }

    $(window).off('scroll.navbarBlur').on('scroll.navbarBlur', debounce(onScroll, 10));

    onScroll();
}

function scrollFunction() {

    let $myButton = $('#back_top');
    let scrollDistance = 20;

    function onScroll() {
        let scrollPos = $(window).scrollTop();
        $myButton.toggle(scrollPos > scrollDistance);
    }

    $(window).off('scroll.backToTop').on('scroll.backToTop', debounce(onScroll, 10));

    $myButton.off('click.backToTop').on('click.backToTop', backToTop);
}

function backToTop() {

    $('html, body').scrollTop(0);
}

function debounce(func, wait, immediate) {

    let timeout;
    return function(...args) {
        const context = this;
        clearTimeout(timeout);
        if (immediate && !timeout) {
            func.apply(context, args);
        }
        timeout = setTimeout(() => {
            if (!immediate) func.apply(context, args);
        }, wait);
    };
}

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

    let newPass = $('#newPass').val();

    $.ajax({
        data:{pass:newPass},
        type: 'POST',
        url:'../src/controllers/validation.ajax.php?opc=1'
    })
    .done(function(res){
        notify('success', '', 'Successful password change');
        $('#confirm').modal('hide');
        $('#newPass, #confirmPass').val('');
    })
    .fail(function(){
        console.log("error");
    });
}

function show() {

	let $newPass = $('#newPass');
	let $confirm = $('#confirmPass');
	let $eye = $('#showPass1');

	$eye.on('click', function() {
		if ($eye.hasClass('fa-eye-slash')) {
        	$eye.removeClass('fa-eye-slash').addClass('fa-eye');
            $newPass.add($confirm).attr('type', 'text');
    	} else {
        	$eye.removeClass('fa-eye').addClass('fa-eye-slash');
            $newPass.add($confirm).attr('type', 'password');
    	}
	});
}

function removes() {

    $('#newPass').on('input', function() {
        $('#newPass').removeClass('is-invalid');
    });

    $('#confirmPass').on('input', function() {
        $('#confirmPass').removeClass('is-invalid');
    });
}

function session() {
    
    let admin = $('#admin').val();

    if (admin === '1') {
        $('#session_register').DataTable({
            ajax: '../src/controllers/members.ajax.php?opc=4',
            info: false,
            lengthChange: false,
            ordering: false,
            pageLength: 10,
            pagingType: 'simple_numbers',
            searching: false,
            columnDefs: [
                {className: "dt-center", targets: [3]}
            ]
        });
    }
}

function session_reload() {
    
    let register = $('#session_register').DataTable();

    register.ajax.reload();
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