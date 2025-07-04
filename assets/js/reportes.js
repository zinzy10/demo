$(document).ready(function () {
    $('#active2').addClass('active');
    tabla1();
});
  
function mostrarTabla() {
  
    let select = $('#reports').val();
  
    if (select) {
        $('[id^="tabla"]').hide();
  
        let func = window[`tabla${select}`];
        if (typeof func === 'function') {
            func();
        }
    }
}
  
function tabla1() {
    //Table prints
    let $contenedor = $('#tabla1');
    let $tabla = $('#print');
    let $cuerpoTabla = $tabla.find('tbody');
  
    $('#mensaje').html('<i class="fa-solid fa-spinner fa-spin" style="color: #2f56b1; font-size: 2.5rem;" role="old_pending"><span class="visually-hidden"></span></i>');
  
    $.getJSON('../src/controllers/tables.ajax.php?opc=1', (data) => {
        let columna1 = data.meses || [];
        let columna2 = data.F1qty || [];
        let columna3 = data.F1qty2 || [];
        // let columna4 = data.F1qty2 || [];
  
        let totalColumna2 = 0;
        let totalColumna3 = 0;
        // let totalColumna4 = 0;
  
        $cuerpoTabla.empty();  // Limpia el tbody antes de agregar filas
  
        // Itera los datos y genera las filas
        $.each(columna1, (i, mes) => {
            let valorColumna2 = columna2[i] || '';
            let valorColumna3 = columna3[i] || '';
            // let valorColumna4 = columna4[i] || '';
  
            $cuerpoTabla.append(`
                <tr>
                    <td>${mes}</td>
                    <td class="text-center" style="background-color: #FAFAFA">${valorColumna2.toLocaleString("en-US")}</td>
                    
                    <td class="text-center">${valorColumna3.toLocaleString("en-US")}</td>
                </tr>
            `);
  
            if (valorColumna2 !== '') totalColumna2 += valorColumna2;
            if (valorColumna3 !== '') totalColumna3 += valorColumna3;
            // if (valorColumna4 !== '') totalColumna4 += valorColumna4;
        });
  
        // Agrega la fila de totales
        $cuerpoTabla.append(`
            <tr>
                <td class="fw-bold">TOTAL</td>
                <td class="text-center fw-bold" style="background-color: #FAFAFA">${totalColumna2.toLocaleString("en-US")}</td>
                
                <td class="text-center fw-bold">${totalColumna3.toLocaleString("en-US")}</td>
            </tr>
        `);
  
        $('#mensaje').empty();
        $contenedor.show();
    }).fail((xhr, status, error) => {
        console.error('Error:', status, error);
    });
}
  
function tabla2() {
    //table labels
    let $contenedor = $('#tabla2');
    let $tabla = $('#label');
    let $cuerpoTabla = $tabla.find('tbody');
  
    $('#mensaje').html('<i class="fa-solid fa-spinner fa-spin " style="color: #2f56b1; font-size: 2.5rem;" role="old_pending"><span class="visually-hidden"></span></i>');
  
    $.getJSON('../src/controllers/tables.ajax.php?opc=2', (data) => {
        let columna1 = data.meses || [];
        let columna2 = data.F1qty || [];
        let columna3 = data.F3qty || [];
        let columna4 = data.F7qty || [];
        let columna5 = data.F1qty2 || [];
        let columna6 = data.F3qty2 || [];
        let columna7 = data.F7qty2 || [];
  
        let totalColumna2 = 0;
        let totalColumna3 = 0;
        let totalColumna4 = 0;
        let totalColumna5 = 0;
        let totalColumna6 = 0;
        let totalColumna7 = 0;
  
        $cuerpoTabla.empty();
  
        $.each(columna1, (i, mes) => {
            let valorColumna2 = columna2[i] || '';
            let valorColumna3 = columna3[i] || '';
            let valorColumna4 = columna4[i] || '';
            let valorColumna5 = columna5[i] || '';
            let valorColumna6 = columna6[i] || '';
            let valorColumna7 = columna7[i] || '';
  
            $cuerpoTabla.append(`
                <tr>
                    <td>${mes}</td>
                    <td class="text-center" style="background-color: #FAFAFA">${valorColumna2.toLocaleString("en-US")}</td>
                    <td class="text-center" style="background-color: #FAFAFA">${valorColumna3.toLocaleString("en-US")}</td>
                    <td class="text-center" style="background-color: #FAFAFA">${valorColumna4.toLocaleString("en-US")}</td>
                    <td class="text-center">${valorColumna5.toLocaleString("en-US")}</td>
                    <td class="text-center">${valorColumna6.toLocaleString("en-US")}</td>
                    <td class="text-center">${valorColumna7.toLocaleString("en-US")}</td>
                </tr>
            `);
  
            // Actualizar los totales generales
            totalColumna2 += valorColumna2;
            totalColumna3 += valorColumna3;
            totalColumna4 += valorColumna4;
            if (valorColumna5 !== '') totalColumna5 += valorColumna5;
            if (valorColumna6 !== '') totalColumna6 += valorColumna6;
            if (valorColumna7 !== '') totalColumna7 += valorColumna7;
  
        });
  
        $cuerpoTabla.append(`
            <tr>
                <td class="fw-bold">TOTAL</td>
                <td class="text-center fw-bold" style="background-color: #FAFAFA">${totalColumna2.toLocaleString("en-US")}</td>
                <td class="text-center fw-bold" style="background-color: #FAFAFA">${totalColumna3.toLocaleString("en-US")}</td>
                <td class="text-center fw-bold" style="background-color: #FAFAFA">${totalColumna4.toLocaleString("en-US")}</td>
                <td class="text-center fw-bold">${totalColumna5.toLocaleString("en-US")}</td>
                <td class="text-center fw-bold">${totalColumna6.toLocaleString("en-US")}</td>
                <td class="text-center fw-bold">${totalColumna7.toLocaleString("en-US")}</td>
            </tr>
        `);
  
        $("#mensaje").empty();
        $contenedor.show();
    }).fail((xhr, status, error) => {
        console.error('Error:', status, error);
    });
}
  
function tabla3() {
    //table treatment
    let $contenedor = $('#tabla3');
    let $tabla = $('#treatment');
    let $cuerpoTabla = $tabla.find('tbody');
  
    $('#mensaje').html('<i class="fa-solid fa-spinner fa-spin " style="color: #2f56b1; font-size: 2.5rem;" role="old_pending"><span class="visually-hidden"></span></i>');
  
    $.getJSON('../src/controllers/tables.ajax.php?opc=3', (data) => {
        let columna1 = data.meses || [];
        let columna2 = data.qty || [];
        let columna3 = data.qty2 || [];
  
        let totalColumna2 = 0;
        let totalColumna3 = 0;
  
        $cuerpoTabla.empty();
  
        $.each(columna1, (i, mes) => {
            let valorColumna2 = columna2[i] || '';
            let valorColumna3 = columna3[i] || '';
  
            $cuerpoTabla.append(`
                <tr>
                    <td>${mes}</td>
                    <td class="text-center">${valorColumna2.toLocaleString("en-US")}</td>
                    <td class="text-center">${valorColumna3.toLocaleString("en-US")}</td>
                </tr>
            `);
  
            totalColumna2 += valorColumna2;
            if (valorColumna3 !== '') totalColumna3 += valorColumna3;
        });
          
        $cuerpoTabla.append(`
            <tr>
                <td class="fw-bold">TOTAL</td>
                <td class="text-center fw-bold">${totalColumna2.toLocaleString("en-US")}</td>
                <td class="text-center fw-bold">${totalColumna3.toLocaleString("en-US")}</td>
            </tr>
        `);
  
        $("#mensaje").empty();
        $contenedor.show();
    }).fail((xhr, status, error) => {
        console.error('Error:', status, error);
    });
}
  
function tabla4() {
    //table embroidery
    let $contenedor = $('#tabla4');
    let $tabla = $('#embroidery');
    let $cuerpoTabla = $tabla.find('tbody');
  
    $('#mensaje').html('<i class="fa-solid fa-spinner fa-spin " style="color: #2f56b1; font-size: 2.5rem;" role="old_pending"><span class="visually-hidden"></span></i>');
  
    $.getJSON('../src/controllers/tables.ajax.php?opc=4', (data) => {
        let columna1 = data.meses || [];
        let columna2 = data.qty || [];
        let columna3 = data.qty2 || [];
  
        let totalColumna2 = 0;
        let totalColumna3 = 0;
  
        $cuerpoTabla.empty();
  
        $.each(columna1, (i, mes) => {
            let valorColumna2 = columna2[i] || '';
            let valorColumna3 = columna3[i] || '';
  
            $cuerpoTabla.append(`
                <tr>
                    <td>${mes}</td>
                    <td class="text-center">${valorColumna2.toLocaleString("en-US")}</td> 
                    <td class="text-center">${valorColumna3.toLocaleString("en-US")}</td>
                </tr>
            `);
  
            totalColumna2 += valorColumna2;
            if (valorColumna3 !== '') totalColumna3 += valorColumna3;
        });
  
        $cuerpoTabla.append(`
            <tr>
                <td class="fw-bold">TOTAL</td>
                <td class="text-center fw-bold">${totalColumna2.toLocaleString("en-US")}</td>
                <td class="text-center fw-bold">${totalColumna3.toLocaleString("en-US")}</td>
            </tr>
        `);
  
        $("#mensaje").empty();
        $contenedor.show();
    }).fail((xhr, status, error) => {
      console.error('Error:', status, error);
    });
}
  
function tabla5() {
    //table shipped
    let $contenedor = $('#tabla5');
    let $tabla = $('#shipped');
    let $cuerpoTabla = $tabla.find('tbody');
  
    $('#mensaje').html('<i class="fa-solid fa-spinner fa-spin " style="color: #2f56b1; font-size: 2.5rem;" role="old_pending"><span class="visually-hidden"></span></i>');
  
    $.getJSON('../src/controllers/tables.ajax.php?opc=5', (data) => {
        let columna1 = data.meses || [];
        let columna2 = data.qty || [];
        let columna3 = data.qty2 || [];
  
        let totalColumna2 = 0;
        let totalColumna3 = 0;
  
        $cuerpoTabla.empty();
  
        $.each(columna1, (i, mes) => {
            let valorColumna2 = columna2[i] || '';
            let valorColumna3 = columna3[i] || '';
  
            $cuerpoTabla.append(`
                <tr>
                    <td>${mes}</td>
                    <td class="text-center">${valorColumna2.toLocaleString("en-US")}</td>
                    <td class="text-center">${valorColumna3.toLocaleString("en-US")}</td>
                </tr>
            `);
  
            totalColumna2 += valorColumna2;
            if (valorColumna3 !== '') totalColumna3 += valorColumna3;
        });
  
        $cuerpoTabla.append(`
            <tr>
                <td class="fw-bold">TOTAL</td>
                <td class="text-center fw-bold">${totalColumna2.toLocaleString("en-US")}</td>
                <td class="text-center fw-bold">${totalColumna3.toLocaleString("en-US")}</td>
            </tr>
        `);
  
        $("#mensaje").empty();
        $contenedor.show();
    }).fail((xhr, status, error) => {
      console.error('Error:', status, error);
    });
}
  
function tabla6() {
    //table received
    let $contenedor = $('#tabla6');
    let $tabla = $('#received');
    let $cuerpoTabla = $tabla.find('tbody');
  
    $('#mensaje').html('<i class="fa-solid fa-spinner fa-spin " style="color: #2f56b1; font-size: 2.5rem;" role="old_pending"><span class="visually-hidden"></span></i>');
  
    $.getJSON('../src/controllers/tables.ajax.php?opc=6', (data) => {
        let columna1 = data.meses || [];
        let columna2 = data.qty || [];
        let columna3 = data.qty2 || [];
  
        let totalColumna2 = 0;
        let totalColumna3 = 0;
  
        $cuerpoTabla.empty();
  
        $.each(columna1, (i, mes) => {
            let valorColumna2 = columna2[i] || '';
            let valorColumna3 = columna3[i] || '';
  
            $cuerpoTabla.append(`
                <tr>
                    <td>${mes}</td>
                    <td class="text-center">${valorColumna2.toLocaleString("en-US")}</td>
                    <td class="text-center">${valorColumna3.toLocaleString("en-US")}</td>
                </tr>
            `);
  
            totalColumna2 += valorColumna2;
            if (valorColumna3 !== '') totalColumna3 += valorColumna3;
        });
  
        $cuerpoTabla.append(`
            <tr>
                <td class="fw-bold">TOTAL</td>
                <td class="text-center fw-bold">${totalColumna2.toLocaleString("en-US")}</td>
                <td class="text-center fw-bold">${totalColumna3.toLocaleString("en-US")}</td>
            </tr>
        `);
  
        $("#mensaje").empty();
        $contenedor.show();
    }).fail((xhr, status, error) => {
      console.error('Error:', status, error);
    });
}