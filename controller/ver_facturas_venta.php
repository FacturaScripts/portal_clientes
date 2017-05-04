<?php

/**
 * @author Francesc Pineda Segarra         shawe.ewahs@gmail.com
 * @copyright 2016, Francesc Pineda Segarra. All Rights Reserved. 
 */
require_model('cliente.php');
require_model('pais.php');
require_model('factura_cliente.php');

/**
 * Description of ext_facturas_venta_html
 *
 * @author Francesc Pineda Segarra
 */
class ver_facturas_venta extends fs_controller {

   public $cliente;
   public $factura;
   public $resultado;

   /**
    * Constructor del controlador
    */
   public function __construct() {
      parent::__construct(__CLASS__, 'Imprimir factura venta en HTML', 'Portal', FALSE, FALSE);
      $this->share_extensions();
   }

   /**
    * Código que se ejecutará en la parte publica
    */
   protected function public_core() {
      check_portal_session();

      $this->resultado = FALSE;
      $this->template = FALSE;
      
      if (filter_input(INPUT_GET, 'id')) {
         $this->generar_html();
      } if (filter_input(INPUT_GET, 'cod')) {
         $this->template = 'extensions/facturas_venta_html';

         $cliente = new \cliente();
         $this->cliente = $cliente->get_by_cifnif($_SESSION['login_user']);
         
         if (filter_input(INPUT_GET, 'cod') == $this->cliente->codcliente) {
            $fac0 = new factura_cliente();
            $this->resultado = $fac0->all_from_cliente($this->cliente->codcliente);
         } else {
            $this->new_error_msg('No puedes ver las facturas de otro cliente.');
         }
      } else {
         $this->new_error_msg('No puedes acceder a esta página directamente.');
      }
   }
   
   /**
    * Código que se ejecutará en la parte privada
    */
   protected function private_core() {
      if (filter_input(INPUT_GET, 'id')) {
         $this->generar_html();
      }
   }

   /**
    * Función para añadir extensiones
    */
   public function share_extensions() {
      $extensions = array(
          array(
              'name' => __CLASS__,
              'page_from' => __CLASS__,
              'page_to' => 'panel_cliente',
              'type' => 'public_tab',
              'text' => '<span class="glyphicon glyphicon-list" aria-hidden="true"></span><span class="hidden-xs">&nbsp; Facturas</span>',
              'params' => ''
          )
      );
      foreach ($extensions as $ext) {
         $fsext = new fs_extension($ext);
         $fsext->save();
      }
   }

   private function generar_html() {
      $basePath = FS_PATH . 'plugins/portal_clientes/plantillas/';
      $templateName = "factura_venta";
      $templateFilePath = $basePath . $templateName . '.php';
      $nombre_documento = FS_FACTURA;
      
      if (file_exists($templateFilePath)) {
         /* Por el momento no se pueden usar estas variables en este generador de plantillas */
         $debug = filter_has_var(INPUT_GET, 'debug') ? true : false;
         $info = filter_has_var(INPUT_GET, 'info') ? true : false;

         ob_start();
         require_once $templateFilePath;
         $templateCode = ob_get_clean();

         echo $templateCode;
      } else {
         $this->new_error_msg('No se encuentra la plantilla de generación de la factura.');
      }
   }

}
