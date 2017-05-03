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

class acceso_clientes extends fs_controller {

   public $cliente;
   public $mostrar;
   public $offset;
   public $resultado;
   public $total_con_acceso;
   public $total_sin_acceso;
   public $total_busqueda;
   public $query;
   public $page_description;

   /**
    * Constructor del controlador
    */
   public function __construct() {
      parent::__construct(__CLASS__, 'Acceso clientes', 'Portal');
      /* Usado para el meta description */
      $this->page_description = 'Identificación de los clientes';
   }

   /**
    * Código que se ejecutará en la parte pública
    */
   protected function public_core() {
      $this->template = 'public/login_clientes';
      $cliente = new \cliente();

      $cifnif = filter_input(INPUT_POST, 'username');
      $this->cliente = $cliente->get_by_cifnif($cifnif);
      if ($this->cliente) {
         $password = filter_input(INPUT_POST, 'password');
         if (password_verify($password, $this->cliente->password)) {
            $this->new_message('Usuario identificado correctamente');
            // TODO: Guardar login y enviar al cliente a otra página o mostrarle datos
         } else {
            /* Por razones de seguridad no merece la pena indicar que es la contraseña lo que está mal */
            $this->new_error_msg('Usuario y/o contraseña incorrectos.');
         }
      } else {
         /* Por razones de seguridad no merece la pena indicar que es el usuario que no existe */
         $this->new_error_msg('Usuario y/o contraseña incorrectos.');
      }
   }

   /**
    * Código que se ejecutará en la parte privada
    */
   protected function private_core() {
      $this->cliente = new \cliente();

      $this->offset = filter_input(INPUT_GET, 'offset');
      if ($this->offset == '') {
         $this->offset = 0;
      }

      $codcliente = filter_input(INPUT_POST, 'codcliente');
      if ($codcliente != '') {
         $cliente = new \cliente();
         $cliente = $this->cliente->get($codcliente);
         if ($cliente) {
            $password = filter_input(INPUT_POST, 'password');
            $repeat_password = filter_input(INPUT_POST, 'repeat_password');

            if ($password === $repeat_password) {
               if (empty($password)) {
                  $cliente->password = '';
                  $cliente->telefono1 = filter_input(INPUT_POST, 'telefono1');
                  $cliente->telefono2 = filter_input(INPUT_POST, 'telefono2');
                  $cliente->email = filter_input(INPUT_POST, 'email');

                  if ($cliente->save()) {
                     $this->new_message('Se ha actualizado el cliente correctamente.');
                     if (!empty($password)) {
                        $this->new_advice('Los datos de acceso son:<br><b>Usuario:</b> ' . $cliente->cifnif . '<br><b>Contraseña:</b> ' . $password);
                     }
                  } else {
                     $this->new_error_msg('Ha ocurrido un error guardando el cliente.');
                  }
               } else {
                  if ($cliente->set_password($password)) {
                     $cliente->telefono1 = filter_input(INPUT_POST, 'telefono1');
                     $cliente->telefono2 = filter_input(INPUT_POST, 'telefono2');
                     $cliente->email = filter_input(INPUT_POST, 'email');

                     if ($cliente->save()) {
                        $this->new_message('Se ha actualizado el cliente correctamente.');
                        if (!empty($password)) {
                           $this->new_advice('Los datos de acceso son:<br><b>Usuario:</b> ' . $cliente->cifnif . '<br><b>Contraseña:</b> ' . $password);
                        }
                     } else {
                        $this->new_error_msg('Ha ocurrido un error guardando el cliente.');
                     }
                  }
               }
            } else {
               $this->new_error_msg('Las contraseñas no coinciden para el cliente con código ' . $cliente->codcliente . '.');
            }
         } else {
            $this->new_error_msg('El cliente seleccionado no existe.');
         }
      }

      $this->mostrar = filter_input(INPUT_GET, 'mostrar');
      switch ($this->mostrar) {
         case '':
         case 'con_acceso':
            $this->mostrar = 'con_acceso';
            $this->resultado = $this->cliente->get_all_with_access($this->offset);
            break;
         case 'sin_acceso':
            $this->mostrar = 'sin_acceso';
            $this->resultado = $this->cliente->get_all_without_access($this->offset);
            break;
         case 'buscar':
            $this->mostrar = 'buscar';

            $query = filter_input(INPUT_POST, 'query');
            if ($query) {
               $this->query = $query;
               $this->resultado = $this->cliente->search($this->query);
               $this->total_busqueda = count($this->resultado);
            } else {
               $this->query = '';
               $this->resultado = FALSE;
               $this->total_busqueda = '';
            }
            break;
      }

      $query = filter_input(INPUT_GET, 'query');
      if ($query) {
         $this->mostrar = 'buscar';
         $this->query = $query;
         $this->resultado = $this->cliente->search($this->query);
         $this->total_busqueda = $this->cliente->count_search($this->query);
      }

      $this->total_con_acceso = $this->cliente->count_all_with_access();
      $this->total_sin_acceso = $this->cliente->count_all_without_access();
   }

   /**
    * Devuelve la URL de la pagina cargada
    * 
    * @return type
    */
   public function url($busqueda = FALSE) {
      if ($busqueda) {
         $url = $this->url() . "&mostrar=" . $this->mostrar;
         return $url;
      } else {
         return parent::url();
      }
   }

   /**
    * Devuelve los elementos que tiene el array
    * 
    * @param type $array
    */
   public function count($array) {
      return count($array);
   }

   public function paginacion() {
      $url = $this->url(TRUE);
      $paginas = array();
      $i = 0;
      $num = 0;
      $actual = 1;
      $cercanas = 3;

      if ($this->mostrar == 'con_acceso') {
         $total = $this->total_con_acceso;
      } else if ($this->mostrar == 'sin_acceso') {
         $total = $this->total_sin_acceso;
      } else if ($this->mostrar == 'buscar') {
         $total = $this->total_busqueda;
      } else {
         $total = 0;
      }

      /// añadimos todas la página
      while ($num < $total) {
         $paginas[$i] = array(
             'url' => $url . "&offset=" . ($i * FS_ITEM_LIMIT),
             'num' => $i + 1,
             'actual' => ($num == $this->offset)
         );

         if ($num == $this->offset) {
            $actual = $i;
         }

         $i++;
         $num += FS_ITEM_LIMIT;
      }

      /// ahora descartamos
      foreach ($paginas as $j => $value) {
         $enmedio = intval($i / 2);

         /**
          * descartamos todo excepto la primera, la última, la de enmedio,
          * la actual, las X anteriores y las X siguientes
          */
         if (($j > 1 AND $j < $actual - $cercanas AND $j != $enmedio) OR ( $j > $actual + $cercanas AND $j < $i - 1 AND $j != $enmedio)) {
            unset($paginas[$j]);
         }
      }

      if (count($paginas) > 1) {
         return $paginas;
      } else {
         return array();
      }
   }

}
