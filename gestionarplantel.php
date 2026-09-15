<div class="content-wrapper"><br>
    <div class="col-xs-12">
        <div class="box">
            <div class="row">
                <div class="col-md-11">
                    <div class="box-header">
                        <h1 class="box-title"><B>Información EDTH:</B></h1>
                    </div>
                </div>
             </div>
           <div class="box-body">
           <div class="row"> 
           <div class="col-md-3 col-xs-12">
           <label>Nit:</label>
             <input type="hidden" name="id_plantel" id="id_plantel" name="id_plantel" value="">    
             <input type="number" name="nit_plantel" id="nit_plantel" class="form-control" data-toggle="tooltip" title="Ingrse Nit de la institución" value="">          
           </div>
      
           <div class="col-md-6 col-xs-12">
            <label>Nombre Institución</label>
             <input type="text" name="nombre_plantel" id="nombre_plantel" class="form-control" data-toggle="tooltip" title="Ingrse nombre del plantel"  value="">          
           </div>

           <div class="col-md-3 col-xs-12">
            <label>Nombre Corto</label>
           <input type="text" name="nombre_corto" id="nombre_corto" class="form-control"  data-toggle="tooltip" title="Ingrse nombre corto" value="">          
          </div>
          </div>



        <div class="row">
       <div class="col-md-6 col-xs-12">
          <label>Dirección:</label>
          <input type="text" name="direccion_plantel" id="direccion_plantel" class="form-control" data-toggle="tooltip" title="Dirección de ubicación" value="">          
      </div>

       <div class="col-md-6 col-xs-12">
          <label>Ciudad/municipio:</label>
          <input type="text" name="ciudad_plantel" id="ciudad_plantel" class="form-control" data-toggle="tooltip" title="Ciudad de ubicación" value="">          
       </div>
    </div>   
   

    </div>
    <div clas="row">
    <div class="panel-footer">
   <button type="button" class="btn btn-primary" onclick="RegistrarPlantel();">
   Registrar Información</button>
    </div>
   </div>

</div>
</div>



<script src="javascripts/gestionarplantel.js"></script>