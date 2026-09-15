var modu = [];
$(document).ready(function () {
    $("#btn-guardar").attr('disabled',true);
       combobox('bRol','Homero/AsingarMenuPerfiles/loadRol','Seleccione...');

       /*////////////////////////////////////////////////////////////////*/
       $("#bRol").change(function(){
         modu=[];
         if($("#bRol").val()!=''){
           loadmenus();
         }else{
           $("#tbl_moduloshabilitados tbody").html('<tr><td colspan="3" class="center">Sin resultados</td></tr>');
           $("#tbl_modulosasignados tbody").html('<tr><td colspan="3" class="center">Sin resultados</td></tr>');
           $("#btn-guardar").attr('disabled',true);
         }
       });

});

function loadmenus(){
       $("#tbl_moduloshabilitados tbody").html('<tr><td colspan="3" class="center">Cargado...</td></tr>');
       $("#tbl_modulosasignados tbody").html('<tr><td colspan="3" class="center">Cargado...</td></tr>');
       $.ajax({
           url : "Homero/AsingarMenuPerfiles/loadmodulos",
           type : "POST",
           data : {'codrol':$("#bRol").val()},
           dataType : "JSON",
           success : function (json){
               if(json.success == true){
                   asignados = '';
                   var i=0;
                   if(json.asignados.length != 0){
                       $.each(json.asignados, function (key, data) {
                           asignados += '<tr id="'+data.codmenu+'" name="'+data.codmenu+'">';
                           asignados += '<td align="center">'+data.codmenu+'</td>';
                           asignados += '<td align="center">'+data.nombre_menu+'</td>';
                           asignados += '<td style="width:50px;" align="center">';
                           asignados += '    <input name="menu" value="'+data.codmenu+'" type="checkbox" checked>';
                           asignados += '</td>';
                           asignados += '</tr>';
                           i++;
                           modu.push(data.codmenu);
                          // console.log(modu);
                       });
                   }else{
                       asignados = '<tr><td colspan="3" class="center">Sin resultados</td></tr>';
                   }
                   $("#tbl_modulosasignados tbody").html(asignados);

                   noasignados = '';
                   var j=0;
                   if(json.noasignados.length != 0){
                       $.each(json.noasignados, function (key, data) {
                           noasignados += '<tr id="'+data.codmenu+'" name="'+data.codmenu+'">';
                           noasignados += '<td align="center">'+data.codmenu+'</td>';
                           noasignados += '<td align="center">'+(data.nivel==1 ? '<b>'+data.nombre_menu+'</b>' : data.nombre_menu)+'</td>';
                           noasignados += '<td style="width:50px;" align="center">';
                           noasignados += '    <input name="menu" value="'+data.codmenu+'" type="checkbox">';
                           noasignados += '</td>';
                           noasignados += '</tr>';
                           j++;
                       });
                   }else{
                       noasignados = '<tr><td colspan="3" class="center">Sin resultados</td></tr>';
                   }
                   $("#tbl_moduloshabilitados tbody").html(noasignados);
               }
           }, complete: function(){

               $('input[name=menu]').click(function() {    
                 if($(this).is(":checked"))
                 {        
                   var tr = $(this).parents("tr").appendTo("#tbl_modulosasignados tbody");
                   modu.push($(this).val());
                   console.log(modu);
                 }else{
                   var index = modu.indexOf($(this).val());        
                   modu.splice(index, 1);        
                   var tr=$(this).parents("tr").appendTo("#tbl_moduloshabilitados tbody"); 
                 }
               });
               $("#btn-guardar").attr('disabled',false);
           }
       });
   }
   

   function fGuardarConfig(){

       $.ajax({
           url : "Homero/AsingarMenuPerfiles/updateConfigRolmenu",
           type : "POST",
           data : "codrol="+$("#bRol").val()+"&menus="+modu,
           dataType : "JSON",
           success : function (json){
               if(json.success == true){
                   modu=[];
                   toastr.success("Cambios realizados correctamente");
                   loadmenus();
                   reloadmenu();
               }else{
                   toastr.error(json.mensaje);
               }
           }
       });

   }
//funcion para mostrar el modal del manual de asignar menú perfiles
 function infoGrupos(){
    $("#modalInformacion").modal("show");
}
//funcion para mostrar el modal del manual de asignar menú perfiles