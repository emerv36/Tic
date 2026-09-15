<div class="content-wrapper">
  <div class="col-xs-12">
    <div class="box">
      <div class="box-body">
        <div class="row">
          <div class="col-md-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-primary">
              <div class="inner">
                <h3>
                  <spn id="Totalinscritos"></span>
                </h3>
                <input type="hidden" name="fecha" id="fecha" value="<?php echo date('Y-m-d'); ?>">

                <p>Nuevos Registros</p>
                <p>Hoy(<?php echo date('Y-m-d') ?>)</p>
              </div>
              <div class="icon">
                <i class="fa fa-address-card-o"></i>
              </div>

            </div>
          </div>
          <!-- ./col -->
          <div class="col-md-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-primary">
              <div class="inner">
                <h3><span id="TotalProceso"></span></h3>
                <p>Total Carnet</p>
                <p>En Estado Proceso</p>
              </div>
              <div class="icon">
                <i class="fa fa-gear fa-spin"></i>
              </div>

            </div>
          </div>
          <!-- ./col -->
          <div class="col-md-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-primary">
              <div class="inner">
                <h3>
                  <spn id="TotalRealizado"></span>
                </h3>
                <p>Total Carnet</p>
                <p>En estado Realizado</p>
                <p>
              </div>
              <div class="icon">
                <i class="fa fa-handshake-o"></i>
              </div>

            </div>
          </div>
          <!-- ./col -->
          <div class="col-md-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-primary">
              <div class="inner">
                <h3>
                  <spn id="TotalEntregado"></span>
                </h3>
                <p>Total Carnet</p>
                <p>En estado Entregado</p>
              </div>
              <div class="icon">
                <i class=" 	fa fa-group"></i>
              </div>

            </div>
          </div>
          <!-- ./col -->
        </div>
      </div>
    </div>
  </div>

  <div class="col-xs-12">
    <div class="box">
      <div class="box-body">
        <div class="row">
          <div class="col-md-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-primary">
              <div class="inner">
                <h3>
                  <spn id="TotalRegistros"></span>
                </h3>

                <p>Total Registros </p>
                <p>Carnetización </p>
              </div>
              <div class="icon">
                <i class="fa fa-edit"></i>
              </div>

            </div>
          </div>
          <!-- ./col -->
          <div class="col-md-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-primary">
              <div class="inner">
                <h3><span id="TotalConChip"></span></h3>
                <p>Total Registros</p>
                <p>Carnet con Chip</p>
              </div>
              <div class="icon">
                <i class="fa fa-microchip"></i>
              </div>

            </div>
          </div>
          <!-- ./col -->
          <div class="col-md-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-primary">
              <div class="inner">
                <h3><span id="TotalSinChip"></span></h3>
                <p>Total Registros</p>
                <p>Carnet Sin Chip</p>
                <p>
              </div>
              <div class="icon">
                <i class="fa fa-microchip"></i>
              </div>

            </div>
          </div>
          <!-- ./col -->
          <div class="col-md-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-primary">
              <div class="inner">
                <h3>
                  <spn id="TotalCarnetFuncionario"></span>
                </h3>
                <p>Total Registros</p>
                <p>Carnet Funcionarios</p>
              </div>
              <div class="icon">
                <i class="		fa fa-drivers-license"></i>
              </div>

            </div>
          </div>
          <!-- ./col -->
        </div>
      </div>
    </div>
  </div>

  <div class="col-xs-12">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-body">
          <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#op1">Carnetización Agrupada</a></li>
            <li><a data-toggle="tab" href="#op2">Carnetización por sede</a></li>

          </ul>

          <div class="tab-content">
            <div id="op1" class="tab-pane fade in active">
              <div class=" col-xs-12 col-md-12">

                <div class="card">
                  <div class="card-header border-0">
                    <h4 class="card-title">Carnetización</h4>

                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <label>Filtrar por Estado</label>
                      <select id="filtroestado" name="filtroestado" class="form-control">
                        <option value="">Seleccione</option>
                        <option value="1">EN PROCESO</option>
                        <option value="2">REALIZADO</option>
                        <option value="3">ENTREGADO</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <div style="margin-top: 25px;">
                        <button id="btnfiltroestado" class="btn btn-primary btn-block" onclick="CarnetizacionAgrupada();">Todos los estados</button>
                      </div>
                    </div>
                  </div>
                  <div class="card-body table-responsive p-0">
                    <table class="table table-striped table-valign-middle" id="tbl_carnet_agrupados">
                      <thead>
                        <tr>
                          <th>No</th>
                          <th>Sede</th>
                          <th>Programa</th>
                          <th>Estado</th>
                          <th>Chip</th>
                          <th>Total</th>
                        </tr>
                      </thead>
                      <tbody>

                      </tbody>
                      <tfoot class="bg-primary">
                        <td colspan="5">Total:</td>
                        <td id="tot"></td>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
              <div class="col-xs-12">
                <div class="box">
                  <div class="box-body">
                    <div id="GraficoMatricula"></div>
                  </div>
                </div>
              </div>
            </div>



            <div id="op2" class="tab-pane fade">

              <div class=" col-xs-12 col-md-12">

                <div class="card">
                  <div class="card-header border-0">
                    <h4 class="card-title">Carnetización por sede </h4>

                  </div>
                  <div class="card-body table-responsive p-0">
                    <table id="tbl_poblacion_sede" class="table table-striped table-valign-middle">
                      <thead>
                        <tr>
                          <th>No</th>
                          <th>Sede</th>
                          <th>Total</th>
                        </tr>
                      </thead>
                      <tbody>

                      </tbody>
                      <tfoot class="bg-primary">
                        <td colspan="2">Total:</td>
                        <td id="totsede"></td>
                      </tfoot>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


</div>
</div>
<script src="javascripts/gestionardasboardadmision.js"></script>