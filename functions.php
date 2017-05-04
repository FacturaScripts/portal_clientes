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

// Sesiones basadas en: https://www.formget.com/login-form-in-php/

if (!function_exists('start_portal_session')) {

   /**
    * Inicia la sesión de cliente con acceso al portal
    */
   function start_portal_session() {
      session_start();
/*
      if (isset($_SESSION['login_user'])) {
         header('Location: ' . FS_PATH . 'index.php?page=panel_cliente');
      }
  */
   }

}

if (!function_exists('check_portal_session')) {

   /**
    * Comprueba si hay sesión de cliente y sino envía a la portada
    */
   function check_portal_session() {
      session_start();
      if (!isset($_SESSION['login_user'])) {
         header('Location: ' . FS_PATH . 'index.php');
      }
   }

}

if (!function_exists('portal_session_exists')) {

   /**
    * Comprueba si existe la sesión de cliente
    */
   function portal_session_exists() {
      if (!isset($_SESSION['login_user'])) {
         return FALSE;
      } else {
         return TRUE;
      }
   }

}

if (!function_exists('portal_session_destroy')) {

   /**
    * Cierra la sesión de cliente
    */
   function portal_session_destroy() {
      if (session_destroy()) {
         header('Location: ' . FS_PATH . 'index.php');
      }
   }

}
