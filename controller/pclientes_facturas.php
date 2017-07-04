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

require_once __DIR__ . '/portada_clientes.php';
require_model('factura_cliente.php');

/**
 * Description of pclientes_facturas
 *
 * @author Carlos García Gómez
 */
class pclientes_facturas extends portada_clientes {

    public $factura;
    public $offset;
    public $resultados;

    public function __construct() {
        parent::__construct(__CLASS__);
    }

    protected function private_core() {
        /**
         * Al activar el plugin se ejecuta el private_core() de cada controlador,
         * así que lo usamos para instalar las extensiones.
         */
        $this->share_extensions();
    }

    protected function public_core() {
        $this->share_extensions();

        /**
         * El parent ya se encarga del login y de cargar las extensiones.
         */
        parent::public_core();

        if (!$this->cliente) {
            $this->new_error_msg('Debes iniciar sesión para acceder a esta página.');
        } else if (isset($_GET['id'])) {
            $this->cargar_factura($_GET['id']);
        } else {
            /// obtenemos el listado de facturas aquí
            $this->buscar();
        }
    }

    private function share_extensions() {
        $fsext = new fs_extension();
        $fsext->name = 'menu_facturas';
        $fsext->from = __CLASS__;
        $fsext->to = NULL; /// deliberadamente a null para que esté disponible en todas las páginas
        $fsext->type = 'public_menu_link';
        $fsext->text = 'Facturas';
        $fsext->save();
    }

    private function buscar() {
        if ($this->cliente) {
            $fact0 = new factura_cliente();
            $this->resultados = $fact0->all_from_cliente($this->cliente->codcliente);
        } else {
            $this->resultados = array();
        }
    }

    private function cargar_factura($id) {
        $fact0 = new factura_cliente();
        $this->factura = FALSE;

        $factura = $fact0->get($id);
        if ($factura) {
            if ($factura->codcliente == $this->cliente->codcliente) {
                $this->factura = $factura;
                $this->template = 'pclientes_public/pclientes_facturas_factura';
            } else {
                $this->new_error_msg('No tienes permiso para ver esta factura.');
            }
        } else {
            $this->new_error_msg('Factura no encontrada.');
        }
    }

}
