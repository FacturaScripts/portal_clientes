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

class contacto extends fs_controller {

   public $page_description;

   public function __construct() {
      parent::__construct(__CLASS__, 'Contacto', 'Portal', FALSE, FALSE);
      /* Usado para el meta description */
      $this->page_description = 'Página de contacto con la empresa';
   }

   /**
    * Código que se ejecutará en la parte pública
    */
   protected function public_core() {
      start_portal_session();
      
      $this->template = 'public/contacto';
   }

}
