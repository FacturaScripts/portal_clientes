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

require_once 'plugins/facturacion_base/extras/fbase_controller.php';
require_model('cliente.php');
require_model('cliente_propiedad.php');

/**
 * Description of portal_clientes
 *
 * @author Carlos García Gómez
 */
class portal_clientes extends fbase_controller {

    public $cliente;
    public $cliente_propiedades;
    public $resultados;

    public function __construct() {
        parent::__construct(__CLASS__, 'Portal clientes', 'admin', FALSE, FALSE);
    }

    protected function private_core() {
        $this->share_extensions();
        $this->check_portal_config();

        if (isset($_REQUEST['cod'])) {
            $this->cargar_cliente();
        } else {
            $this->buscar();
        }
    }

    private function share_extensions() {
        $fsext = new fs_extension();
        $fsext->name = 'tab_portal';
        $fsext->from = __CLASS__;
        $fsext->to = 'ventas_clientes';
        $fsext->type = 'tab';
        $fsext->text = '<i class="fa fa-globe" aria-hidden="true"></i><span class="hidden-xs"> Portal web</span>';
        $fsext->save();

        $fsext2 = new fs_extension();
        $fsext2->name = 'btn_cliente';
        $fsext2->from = __CLASS__;
        $fsext2->to = 'ventas_cliente';
        $fsext2->type = 'tab';
        $fsext2->text = '<i class="fa fa-globe" aria-hidden="true"></i><span class="hidden-xs">&nbsp; Portal web</span>';
        $fsext2->save();
    }

    /**
     * Comprobamos la configuración para que sea correcta.
     */
    private function check_portal_config() {
        if (FS_HOMEPAGE != 'portada_clientes') {
            $GLOBALS['config2']['homepage'] = 'portada_clientes';

            $file = fopen('tmp/' . FS_TMP_NAME . 'config2.ini', 'w');
            if ($file) {
                foreach ($GLOBALS['config2'] as $i => $value) {
                    if (is_numeric($value)) {
                        fwrite($file, $i . " = " . $value . ";\n");
                    } else {
                        fwrite($file, $i . " = '" . $value . "';\n");
                    }
                }
                fclose($file);
            }

            $this->new_message('Configuración actualizada correctamente.');
        }
    }

    private function buscar() {
        $this->resultados = array();

        if ($this->db->table_exists('cliente_propiedades')) {
            $sql = "SELECT * FROM clientes WHERE codcliente IN (SELECT codcliente FROM cliente_propiedades WHERE name = 'password')"
                    . " ORDER BY codcliente ASC";

            $data = $this->db->select($sql);
            if ($data) {
                foreach ($data as $d) {
                    $this->resultados[] = new cliente($d);
                }
            }
        }
    }

    private function cargar_cliente() {
        $this->template = 'portal_clientes_cliente';

        $cli0 = new cliente();
        $this->cliente = $cli0->get($_REQUEST['cod']);

        if ($this->cliente) {
            $this->modificar_cliente();

            $cprop = new cliente_propiedad();
            $this->cliente_propiedades = $cprop->array_get($this->cliente->codcliente);
        } else {
            $this->new_error_msg('Cliente no encontrado.');
        }
    }

    private function modificar_cliente() {
        if (isset($_POST['pass_cli'])) {
            /// guardar contraseña
            $this->cliente_propiedades['password'] = sha1($_POST['pass_cli']);

            $cprop = new cliente_propiedad();
            if ($cprop->array_save($this->cliente->codcliente, $this->cliente_propiedades)) {
                $this->new_message('Datos guardados correctamente.');
            } else {
                $this->new_error_msg('Error al guardar los datos.');
            }
        } else if (isset($_GET['quitar'])) {
            /// eliminar contraseña
            $cprop = new cliente_propiedad();
            $cprop->codcliente = $this->cliente->codcliente;
            $cprop->name = 'password';
            if ($cprop->delete()) {
                $this->new_message('Acceso eliminado correctamente.');
            } else {
                $this->new_error_msg('Error al eliminar el acceso.');
            }
        }
    }

}
