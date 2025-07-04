$("#enviar").on("click", function() {

var form_data = new FormData();

    // Obtener los archivos seleccionados
    var file_data = $("#archivo").prop("files");

    // Comprobar que al menos un archivo fue seleccionado
    if(file_data.length > 0){

        var allowed_types = ["application/pdf", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"];
        var valid_files = true;

        // Verificar que cada archivo sea del tipo permitido
        for (var i = 0; i < file_data.length; i++) {
            if (allowed_types.indexOf(file_data[i].type) === -1) {
                valid_files = false;
                break;
            }
            form_data.append("files[]", file_data[i]);
        }

        if (valid_files) {
            var submitBtn = document.getElementById("#enviar");

            $("#enviar").prop("disabled", true);

            $.ajax({
                url:"../docs/envio.php",
                data: form_data,
                type: 'POST',
                contentType: false,
                processData: false,
                success: function(response){
                    console.log(response);
                    swal.fire({position: 'top', icon: 'success', title: 'Successful sending', showConfirmButton: false, timer: 2000 });
                    $("#archivo").val(null);
                },
                complete: function() {
                    $("#enviar").prop("disabled", false);
                }
            });
        } else {
            swal.fire({position: 'top', icon: 'error', title: 'Select PDF and Excel files only', showConfirmButton: false, timer: 2000 });
        }

    } else {
        swal.fire({position: 'top', icon: 'error', title: 'Enter a file', showConfirmButton: false, timer: 2000 });
    }
});