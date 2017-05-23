<?php

/*
 * This file is part of facturacion_base
 * Copyright (C) 2013-2017  Carlos Garcia Gomez  neorazorx@gmail.com
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

if (file_exists(FS_PATH . 'plugins/facturacion_base/model/core/cliente.php')) {
   require_once FS_PATH . 'plugins/facturacion_base/model/core/cliente.php';
   require_once FS_PATH . 'plugins/portal_clientes/vendor/ircmaxell/password-compat/lib/password.php';

   /**
    * El cliente necesita una contraseña para accecder al portal_clientes
    * 
    * @author Francesc Pineda Segarra <francesc.pineda@x-netditigal.com>
    */
   class cliente extends FacturaScripts\model\cliente {

      public $password;

      public function __construct($p = FALSE) {
         parent::__construct($p);
         if ($p) {
            if (isset($p['password'])) {
               $this->password = $p['password'];
            } else {
               $this->password = NULL;
            }
         } else {
            $this->password = NULL;
         }
      }

      public function save() {
         parent::save();
         if ($this->test()) {
            if ($this->exists()) {
               /* Sólo actualizamos la contraseña si se recibe, por si el usuario modifica sus datos pero no la contraseña */
               if (!empty($this->password)) {
                  $sql = "UPDATE " . $this->table_name
                          . " SET password='" . $this->password . "'"
                          . "  WHERE codcliente = " . $this->var2str($this->codcliente) . ";";

                  return $this->db->exec($sql);
               } else {
                  return TRUE;
               }
            }
         } else {
            return FALSE;
         }
      }

      /**
       * Cifrado de la contraseña
       * Más detalles: http://php.net/manual/es/function.password-hash.php
       * 
       * @param type $password
       * @return type
       */
      public function hash_password($password) {
         return password_hash($password, PASSWORD_DEFAULT);
      }

      /**
       * Comprobar que la contraseña ocupa entre 5 y 32 caracteres
       * 
       * @param type $p
       * @return boolean
       */
      public function set_password($password = '') {
         $min_length = 5;
         $max_length = 32;

         $password = trim($password);
         if (mb_strlen($password) >= $min_length AND mb_strlen($password) <= $max_length) {
            $this->password = $this->hash_password($password);
            return TRUE;
         } else {
            $this->new_error_msg('La contraseña debe contener entre 5 y 32 caracteres para el usuario con código ' . $this->codcliente . ' <a href="index.php?page=pclientes_acceso&query=' . $this->codcliente . '">Reintentar</a>.');
            return FALSE;
         }
      }

      /**
       * Devuelve un array paginado de los clientes que pueden acceder al portal
       * 
       * @return \cliente
       */
      public function get_all_with_access($offset = 0, $limit = FS_ITEM_LIMIT) {
         $datalist = array();

         $sql = "SELECT * FROM " . $this->table_name
                 . " WHERE password IS NOT NULL OR password!='' AND debaja = FALSE"
                 . " ORDER BY codcliente ASC";
         $clientes = $this->db->select_limit($sql, $limit, $offset);
         if ($clientes) {
            foreach ($clientes as $c) {
               $datalist[] = new \cliente($c);
            }
         }

         return $datalist;
      }

      /**
       * Devuelve un array paginado de los clientes que NO pueden acceder al portal
       * 
       * @return \cliente
       */
      public function get_all_without_access($offset = 0, $limit = FS_ITEM_LIMIT) {
         $datalist = array();

         $sql = "SELECT * FROM " . $this->table_name
                 . " WHERE password IS NULL OR password='' AND debaja = FALSE"
                 . " ORDER BY codcliente ASC";
         $clientes = $this->db->select_limit($sql, $limit, $offset);
         if ($clientes) {
            foreach ($clientes as $c) {
               $datalist[] = new \cliente($c);
            }
         }

         return $datalist;
      }

      /**
       * Devuelve el número total de usuarios que tienen acceso al portal de clientes
       * @return int
       */
      public function count_all_with_access() {
         $sql = "SELECT COUNT(codcliente) as total FROM " . $this->table_name
                 . " WHERE password IS NOT NULL OR password!='' AND debaja = FALSE";
         $data = $this->db->select($sql);
         if ($data) {
            return intval($data[0]['total']);
         } else {
            return 0;
         }
      }

      /**
       * Devuelve el número total de usuarios que NO tienen acceso al portal de clientes
       * 
       * @return int
       */
      public function count_all_without_access() {
         $sql = "SELECT COUNT(codcliente) as total FROM " . $this->table_name
                 . " WHERE password IS NULL OR password='' AND debaja = FALSE";
         $data = $this->db->select($sql);
         if ($data) {
            return intval($data[0]['total']);
         } else {
            return 0;
         }
      }

      public function count_search($query) {
         $clilist = array();
         $query = mb_strtolower($this->no_html($query), 'UTF8');

         $consulta = "SELECT COUNT(codcliente) as total FROM " . $this->table_name . " WHERE debaja = FALSE";

         if (!empty($query)) {
            $consulta .= " AND";
            if (is_numeric($query)) {
               $consulta .= "(nombre LIKE '%" . $query . "%'"
                       . " OR razonsocial LIKE '%" . $query . "%'"
                       . " OR codcliente LIKE '%" . $query . "%'"
                       . " OR cifnif LIKE '%" . $query . "%'"
                       . " OR telefono1 LIKE '" . $query . "%'"
                       . " OR telefono2 LIKE '" . $query . "%'"
                       . " OR observaciones LIKE '%" . $query . "%')";
            } else {
               $buscar = str_replace(' ', '%', $query);
               $consulta .= "(lower(nombre) LIKE '%" . $buscar . "%'"
                       . " OR lower(razonsocial) LIKE '%" . $buscar . "%'"
                       . " OR lower(cifnif) LIKE '%" . $buscar . "%'"
                       . " OR lower(observaciones) LIKE '%" . $buscar . "%'"
                       . " OR lower(email) LIKE '%" . $buscar . "%')";
            }
         }

         $data = $this->db->select($consulta);
         if ($data) {
            return intval($data[0]['total']);
         } else {
            return 0;
         }
      }

   }

}