<aside class="main-sidebar">
  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar">
    <!-- Sidebar user panel -->
    <div class="user-panel">
      <div class="pull-left image">
        <img  onclick="EditarFoto()" style="width: 70px; height:45px;" src="assets/images/fotoperfil/user.png" 
        class="img-circle" alt="User Image" id="imagen">
      </div>
      <div class="pull-left info">
        <p> <?php echo ucwords(strtolower($_SESSION['IN_nombre_perfil']))?>  </p>
        <input type="hidden" name="idperfil" id="idperfil" value='<?php echo $_SESSION['IN_codperfil']?>'>
        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
      </div>
    </div>
    <!-- search form -->
    </span>
    </div>
    </form>
      <!-- /.search form -->
    <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu" data-widget="tree">
    </ul>
  </section>
  <!-- /.sidebar -->
</aside>

