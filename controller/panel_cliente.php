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
require_model('cliente.php');

class panel_cliente extends fs_controller {

   public $page_description;
   public $cliente;

   public function __construct() {
      parent::__construct(__CLASS__, 'Panel de cliente', 'Portal', FALSE, FALSE);
      /* Usado para el meta description */
      $this->page_description = 'Página privada de cliente';
   }

   /**
    * Código que se ejecutará en la parte pública
    */
   protected function public_core() {
      check_portal_session();
      
      $this->template = 'public/panel_cliente';
      $cliente = new \cliente();
      $this->cliente = $cliente->get_by_cifnif($_SESSION['login_user']);
   }

}
