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
      if (!$this->login_cliente()) {
         $this->template = 'public/pclientes_login';
      } else {
         
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
      
   }

// Sesiones basadas en: https://www.formget.com/login-form-in-php/

   /**
    * Inicia la sesión de cliente con acceso al portal
    */
   function start_portal_session() {
      session_start();
      /*
        if (isset($_SESSION['login_user'])) {
        header('Location: ' . FS_PATH . 'index.php?page=pclientes_panel');
        }
       */
   }

   /**
    * Comprueba si hay sesión de cliente y sino envía a la portada
    */
   function check_portal_session() {
      session_start();
      if (!isset($_SESSION['login_cliente'])) {
         header('Location: ' . FS_PATH . 'index.php');
      }
   }

   /**
    * Comprueba si existe la sesión de cliente
    */
   function portal_session_exists() {
      if (!isset($_SESSION['login_cliente'])) {
         return FALSE;
      } else {
         return TRUE;
      }
   }

   /**
    * Cierra la sesión de cliente
    */
   function portal_session_destroy() {
      if (session_destroy()) {
         header('Location: ' . FS_PATH . 'index.php');
      }
   }

}
