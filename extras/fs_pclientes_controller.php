<?php

/*
 * This file is part of FacturaScripts
 * Copyright (C) 2017  Francesc Pineda Segarra  francesc.pineda@x-netdigital.com
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

/**
 * Tarea: https://www.facturascripts.com/comm3/index.php?page=community_item&id=5099
 *
 * @author Francesc Pineda Segarra <francesc.pineda@x-netditigal.com>
 */
require_model('cliente.php');

require_once FS_PATH . 'plugins/portal_clientes/vendor/ircmaxell/password-compat/lib/password.php';

class fs_pclientes_controller extends fs_controller {

   /**
    * TRUE si el usuario tiene permisos para eliminar en la página.
    * @var type 
    */
   public $allow_delete;

   /**
    * El cliente del portal.
    * @var type 
    */
   public $cliente;

   /**
    * Nombre de la clase del controlador que hereda de este.
    * @var type 
    */
   private $controller_name;

   public function __construct($name = '', $title = 'home', $folder = '', $admin = FALSE, $shmenu = TRUE, $important = FALSE) {
      /**
       * Nos guardamos el nombre de la clase, de la clase hija.
       */
      $this->controller_name = $name;

      parent::__construct($name, $title, $folder, $admin, $shmenu, $important);
   }

   /**
    * Código que se ejecutará en la parte pública
    */
   protected function public_core() {
      if (filter_input(INPUT_GET, 'exit')) {
         // Si el cliente ha pulsado en cerrar sesión, destruimos la sesión
         if($this->check_portal_session()) {
            $this->portal_session_destroy();
         }
      } else if (!$this->login_cliente()) {
         // Si no tenemos a un cliente logeado, lo enviamos a logearse
         //header('Location: ' . FS_PATH . 'index.php?page=pclientes_login');
         $this->template = 'public/pclientes_login';
      } else {
         // Si ya está logeado lo enviamos al panel de cliente
         header('Location: ' . FS_PATH . 'index.php?page=pclientes_panel');
      }
   }

   /**
    * Código que se ejecutará en la parte privada
    */
   protected function private_core() {
      /// ¿El usuario tiene permiso para eliminar en esta página?
      $this->allow_delete = $this->user->allow_delete_on($this->controller_name);
   }

   /**
    * Si se obtiene el $_POST o se encuentra la sesión se debe asignar a la 
    * propiedad cliente de la clase el cliente que ha hecho login. Si los datos 
    * son correctos. Así podemos saber que se ha hecho login porque cliente es 
    * distinto de FALSE.
    */
   private function login_cliente() {
      $this->cliente = FALSE;
      
      if ($this->portal_session_exists()) {
         // Si ya está logeado
         $this->start_portal_session();
         $cliente = new cliente();
         $cliente = $cliente->get_by_cifnif($_SESSION['login_cliente']);
         if ($cliente) {
            $this->cliente = $cliente;
         }
         return $this->cliente;
      } else {
         // Si no está logeado
         $cifnif = filter_input(INPUT_POST, 'username');
         if ($cifnif) {
            // Si envia por post el login
            $cliente = new cliente();
            $cliente = $cliente->get_by_cifnif($cifnif);
            if ($cliente) {
               // Si el cliente existe
               $password = filter_input(INPUT_POST, 'password');
               if (password_verify($password, $cliente->password)) {
                  // Si la contraseña coincide
                  $this->start_portal_session();
                  $_SESSION['login_cliente'] = $cliente->cifnif;
                  $this->cliente = $cliente;
               }
            }
         }
         return $this->cliente;
         ;
      }
   }

// Sesiones basadas en: https://www.formget.com/login-form-in-php/

   /**
    * Inicia la sesión de cliente con acceso al portal
    */
   private function start_portal_session() {
      session_start();
      // USar cookies si es necesario
   }

   /**
    * Comprueba si hay sesión de cliente y sino envía a la portada
    */
   private function check_portal_session() {
      session_start();
      // USar cookies si es necesario
      if (!isset($_SESSION['login_cliente'])) {
         header('Location: ' . FS_PATH . 'index.php');
      }
   }

   /**
    * Comprueba si existe la sesión de cliente
    */
   private function portal_session_exists() {
      if (!isset($_SESSION['login_cliente'])) {
         return FALSE;
      } else {
         return TRUE;
      }
   }

   /**
    * Cierra la sesión de cliente
    */
   private function portal_session_destroy() {
      if (session_destroy()) {
         header('Location: ' . FS_PATH . 'index.php');
      }
   }

}
