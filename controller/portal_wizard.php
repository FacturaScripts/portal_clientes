<?php

/*
 * This file is part of FacturaScripts
 * Copyright (C) 2017  Francesc Pineda Segarra  shawe.ewahs@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class portal_wizard extends fs_controller {

   public function __construct() {
      parent::__construct(__CLASS__, 'Asistente portal clientes', 'Portal', FALSE, FALSE);
   }

   protected function private_core() {
      $continuar = filter_input(INPUT_POST, 'continuar');
      if ($continuar) {
         /* Activamos las páginas del plugin */
         $this->check_menu();
         /* Asignamos la página portada como página por defecto */
         $this->actualizar_config2();
         
         $URL = 'index.php?page=acceso_clientes';
         header('Location: ' . $URL);
      }
   }

   private function check_menu() {
      if (file_exists(__DIR__)) {
         /// activamos las páginas del plugin
         foreach (scandir(__DIR__) as $f) {
            if (is_string($f) AND strlen($f) > 0 AND ! is_dir($f) AND $f != __CLASS__ . '.php') {
               $page_name = substr($f, 0, -4);

               require_once __DIR__ . '/' . $f;
               $new_fsc = new $page_name();

               if (!$new_fsc->page->save()) {
                  $this->new_error_msg("Imposible guardar la página " . $page_name);
               }

               unset($new_fsc);
            }
         }
      } else {
         $this->new_error_msg('No se encuentra el directorio ' . __DIR__);
      }

      $this->load_menu(TRUE);
   }
   
      public function actualizar_config2() {
      //Configuramos la información básica para config2.ini
      $guardar = FALSE;
      $config2 = array();
      /* No hace falta indicarlas todas, sólo las diferentes */
      $config2['homepage'] = "portada";

      foreach ($GLOBALS['config2'] as $i => $value) {
         if (isset($config2[$i])) {
            $GLOBALS['config2'][$i] = htmlspecialchars($config2[$i]);
            $guardar = TRUE;
         }
      }

      if ($guardar) {
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
         $this->new_message('Datos de configuracion regional guardados correctamente.');
      }
   }

}
