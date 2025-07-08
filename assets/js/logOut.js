function logOut(){

    $.ajax({
    data:{opcion:'logOut'},
    type:'POST',
    url:'../src/controllers/validation.ajax.php?opc=2'
    })
    .done(function(res){
        console.log(res);
        window.location.href = res;
    })
    .fail(function(){
        console.log("error");
    });
}