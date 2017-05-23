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
      session_start();
      $cliente = new cliente();

      if (filter_input(INPUT_GET, 'exit')) {
         $this->cliente = $cliente->get($_SESSION['login_cliente']);
         $this->cliente->logout();
         $this->cliente = FALSE;
         $this->redirect("pclientes_portada");
      } else if (filter_input(INPUT_POST, 'username') && filter_input(INPUT_POST, 'password')) {
         // Si se reciben los datos para iniciar sesión
         $username = filter_input(INPUT_POST, 'username');
         $password = filter_input(INPUT_POST, 'password');
         if (!$password) {
            $this->new_error_msg('El nombre de usuario no puede estar vacío.');
         } else if (!$password) {
            $this->new_error_msg('La contraseña no puede estar vacía.');
         } else {
            // Sino nos falta ningñún dato, probamos a logear al cliente
            if ($cliente->login($username, $password)) {
               $this->cliente = $cliente;
               $this->redirect("pclientes_panel");
            } else {
               $this->cliente = FALSE;
               $this->new_error_msg('Usuario y/o contraseña incorrectos.');
            }
         }
      } else if (isset($_SESSION['login_cliente'])) {
         // Si existe sesión
         $this->cliente = $cliente->get($_SESSION['login_cliente']);
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
    * Función apra redirigir a un controlador concreto
    * 
    * @param type $url
    */
   public function redirect($url) {
      header('Location: ' . FS_PATH . 'index.php?page=' . $url);
   }

}
