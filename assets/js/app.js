let currentPage = 'dashboard';

function loadContent(section,page) {

    if (currentPage === page) return;

    $.ajax({
        url: `../pages/${page}.php`,
        method: 'POST',
        dataType:'json',
        success: function(data) {

            if (data.per) {
                new Notify ({
                    status: 'error',
                    title: "Don't have permission",
                    text: "",
                    effect: 'slide',
                    speed: 250,
                    showIcon: true,
                    showCloseButton: true,
                    autoclose: true,
                    autotimeout: 3000,
                    type: 'outline',
                    position: 'right top',
                });
            } else {
                $('div ul li a').removeClass('active bg-orange');
                let activeMenu = $(`div ul li a[data-page="${page}"]`);
                activeMenu.addClass('active bg-orange');
            
                $('#page').html(`<ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                            <li class="breadcrumb-item">
                                <a href="home.php" class="text-dark">
                                    <i class="fad fa-house fa-md" title="Home"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item text-capitalize text-dark">${section}</li>
                        </ol>
                        <h6 class="fw-bold mb-0 text-capitalize" aria-current="page">${page}</h6>`);
                $('#changes_data').empty();
                $('#changes_data').append(data.html);
                currentPage = page;
            }
        },
        error: function(error) {
            console.error('error al cargar', error);
        }
    });
}

function initializeScripts(){

    $('body').find('script').each(function(){ $(this).remove(); });    

    var nScripts = $('body').find('script');
        nScripts.each(function() {    
    
        script = document.createElement('script');
        script.src = $(this).attr('src');
        document.body.appendChild(script);    
    });    
 }