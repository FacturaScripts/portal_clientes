<?php

/*
 * This file is part of facturacion_base
 * Copyright (C) 2017  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_model('cliente.php');
require_model('cliente_propiedad.php');

/**
 * Description of portada_clientes
 *
 * @author Carlos García Gómez
 */
class portada_clientes extends fs_controller {
   
   public $class_name;
   
   /**
    * Si el cliente ha hecho login, aquí estarán los datos del cliente.
    * @var type 
    */
   public $cliente;

   public function __construct($name = '', $title = 'home', $folder = '', $admin = FALSE, $shmenu = FALSE, $important = FALSE) {
      if ($name == '') {
         /// valores para este controlador, no para cuando se hereda de este
         $name = __CLASS__;
         $title = 'Portada clientes';
         $folder = 'admin';
      }
      
      $this->class_name = $name;
      parent::__construct($name, $title, $folder, $admin, $shmenu, $important);
   }

   protected function public_core() {
      $this->login_cliente();
   }

   protected function login_cliente() {
      $cli0 = new cliente();
      $cprop = new cliente_propiedad();

      $login_ok = FALSE;
      if (isset($_POST['user_cli']) AND isset($_POST['pass_cli'])) {
         /**
          * Si tenemos datos del formulario, comprobamos.
          */
         $cliente = $cli0->get($_POST['user_cli']);
         if ($cliente) {
            $propiedades = $cprop->array_get($cliente->codcliente);
            if (isset($propiedades['password']) AND $propiedades['password'] == sha1($_POST['pass_cli'])) {
               $login_ok = TRUE;
               $this->cliente = $cliente;
               /**
                * Usamos logkey para que el cliente lo guarde en cookie y comparar.
                */
               $propiedades['logkey'] = $this->random_string(30);
               if( $cprop->array_save($cliente->codcliente, $propiedades) ) {
                  setcookie('user_cli', $cliente->codcliente, time()+FS_COOKIES_EXPIRE);
                  setcookie('logkey_cli', $propiedades['logkey'], time()+FS_COOKIES_EXPIRE);
                  $this->new_message('Sesión iniciada correctamente.');
               }
            } else {
               $this->new_error_msg('Contraseña incorrecta.');
            }
         } else {
            $this->new_error_msg('Cliente no encontrado.');
         }
      } else if( isset($_GET['logout_cli']) ) {
         $this->logout_cli();
      } else if( isset($_COOKIE['user_cli']) AND isset($_COOKIE['logkey_cli']) ) {
         /**
          * Usamos user_cli (codcliente) y logkey para identificar al usuario y saber si ha
          * iniciado sesión en este ordenador.
          */
         $cliente = $cli0->get($_COOKIE['user_cli']);
         if ($cliente) {
            $propiedades = $cprop->array_get($cliente->codcliente);
            if (isset($propiedades['logkey']) AND $propiedades['logkey'] == $_COOKIE['logkey_cli']) {
               $login_ok = TRUE;
               $this->cliente = $cliente;
            }
         }
      }
      
      if($login_ok) {
         $this->template = 'pclientes_public/'.__CLASS__;
         $this->cargar_extensiones();
      }
   }
   
   private function logout_cli() {
      $this->cliente = FALSE;
      $this->template = 'login/default';
      
      setcookie('user_cli', '', time()-FS_COOKIES_EXPIRE);
      setcookie('logkey_cli', '', time()-FS_COOKIES_EXPIRE);
      $this->new_message('Sesión cerrada correctamente.');
   }
   
   private function cargar_extensiones() {
      $this->extensions = array();
      
      $fsext = new fs_extension();
      foreach($fsext->all() as $ext) {
         if( in_array($ext->to, array(NULL, $this->class_name)) ) {
            $this->extensions[] = $ext;
         }
      }
   }

}
