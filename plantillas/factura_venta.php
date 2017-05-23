<?php
$id_doc = filter_input(INPUT_GET, 'id');
$doc0 = new factura_cliente();
$documento = $doc0->get($id_doc);
$per0 = new cliente();

/* Solo un cliente o un usuario de FS pueden ver las facturas*/
if (!$this->user->nick) {
   if ($documento->cifnif != $_SESSION['login_cliente']) {
      die("No puedes ver las facturas de otros usuarios");
   }
}

$persona = $per0->get($documento->codcliente);

$lineas_documento = $documento->get_lineas();
$lineas_iva_documento = $documento->get_lineas_iva($lineas_documento);
?>

<!-- Plantilla de impresión -->

<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

      <style>
         html{
            margin: 0;
            font-size: 10pt;
         }
         body {
            font-family: "helvetica", sans-serif;
         }
         hr {
            page-break-after: always;
            border: 0;
            margin: 0;
            padding: 0;
         }
         header {
            position: fixed;
            height: 60mm;
            top: 5mm;
            left: 0mm;
            right: 0mm;
            text-align: center;
            width: 100%;
            margin: 70mm 8mm 2mm 8mm;
            /* Mostrar color para distinguir zona */
            /*
            background-color: lightyellow;
            */
         }
         footer {
            position: fixed;
            height: 35mm;
            bottom: 5mm;
            left: 0mm;
            right: 0mm;
            text-align: center;
            width: 100%;
            margin: 45mm 8mm 2mm 8mm;
            /* Mostrar color para distinguir zona */
            /*
            background-color: lightblue;
            */
         }
         main {
            text-align: center;
            width: 100%;
         }
         .page-number {
            text-align: right;
         }
         .page-number:before {
            content: "Página " counter(page) " de " counter(pages);
         }
         table {
            width:  99%;
            border-spacing: 0px;
            margin-left: auto;
            margin-right: auto;
         }
         th, td {
            padding: 5px;
         }
         /* Tabla con bordes en la tabla */
         .table-bordered {
            padding: 0;
            border-collapse: collapse;
            border: solid 2px black;
         }
         .table-bordered-header {
            padding: 0;
            border-collapse: collapse;
            border: solid 2px black;
         }
         .table-bordered-header,
         .table-bordered-header tr,
         .table-bordered-header td {
            border-top: solid 1px black;
         }
         .table-bordered-header>thead>tr>th {
            background: black;
            color: white;
         }
         .table-bordered-header>tbody tr:nth-child(even) {
            background: #CCC
         }
         /* Tabla sin bordes en la tabla */
         .table-borderless, th, td  {
            border: none;
         }
         .table-middle{
            vertical-align: middle;
         }
         .text-center{
            text-align: center;
         }
         .text-left{
            text-align: left;
         }
         .text-right{
            text-align: right;
         }
         .text-nowrap{
            white-space: nowrap;
         }
      </style>
   </head>
   <body>

      <div class="container-fluid">
         <div class="head">
            <!-- Cabecera del documento con los datos de la empresa -->
            <section>
               <table class='table-borderless text-left' align='center' valign='top'>
                  <tr>
                     <td style='width: 30%;'>
                        <?php
                        echo "<strong>" . $this->empresa->nombre . "</strong><br/>";
                        echo "<b>" . ucfirst(FS_CIFNIF) . ":</b> " . $this->empresa->cifnif . "<br/>";
                        echo $this->empresa->direccion . "<br/>";

                        if ($this->empresa->apartado) {
                           echo "<b>" . ucfirst(FS_APARTADO) . "</b>: " . $this->empresa->apartado . "<br/>";
                        }

                        if ($this->empresa->codpostal) {
                           echo "<b>CP:</b> " . $this->empresa->codpostal;
                        }

                        echo $this->empresa->ciudad . "<br/>";

                        if ($this->empresa->provincia) {
                           echo "(" . $this->empresa->provincia . ")";
                        }

                        if ($documento->codpais != $this->empresa->codpais) {
                           $pais0 = new pais();
                           $pais = $pais0->get($this->empresa->codpais);
                           if ($pais) {
                              echo ' - ' . $pais->nombre;
                           }
                        }
                        ?>
                     </td>
                     <td class='text-center' style='width: 40%;'>
                        <?php
                        if ($this->empresa->web) {
                           echo "<b>Web:</b> " . $this->empresa->web . "<br/>";
                        }

                        if ($this->empresa->telefono) {
                           echo "<b>Tel:</b> " . $this->empresa->telefono . "<br/>";
                        }

                        if ($this->empresa->fax) {
                           echo "<b>Fax:</b> " . $this->empresa->fax . "<br/>";
                        }
                        ?>
                     </td>
                     <td class='text-right' style='width: 30%;'>
                        <?php
                        echo "<h3>" . ucfirst($nombre_documento) . "</h3>";
                        echo "<b>" . $documento->codigo . "</b><br/>";
                        echo "<b>Fecha:</b> " . $documento->fecha . "<br/>";
                        ?>
                     </td>
                  </tr>
               </table>

               <br>

               <table class='table-borderless text-left' align='center'>
                  <thead>
                     <tr>
                        <th class='text-left' style='width: 40%;'>
                           <?php
                           if ($documento->envio_direccion) {
                              echo "Dirección de envío";
                           }
                           ?>
                        </th>
                        <th class='text-left' style='width: 20%;'>

                        </th>
                        <th class='text-left' style='width: 40%;'>
                           <?php
                           if (!$documento->envio_direccion) {
                              echo "Dirección de facturación";
                           } else {
                              echo "Dirección de envío";
                           }
                           ?>
                        </th>
                     </tr>
                  </thead>
                  <tbody>
                     <tr>
                        <td class='text-left' style='width: 40%;'>
                           <?php
                           if ($documento->envio_direccion) {
                              echo "<strong>" . $documento->envio_nombre . " " . $documento->envio_apellidos . "</strong><br/>";

                              echo $documento->envio_direccion . "<br/>";

                              if ($documento->envio_apartado) {
                                 echo ucfirst(FS_APARTADO) . ': ' . $documento->envio_apartado . "<br/>";
                              }

                              if ($documento->envio_codpostal) {
                                 echo '<b>CP:</b> ' . $documento->envio_codpostal;
                              }

                              echo $documento->envio_ciudad;

                              if ($documento->envio_provincia) {
                                 echo ' (' . $documento->envio_provincia . ') ';
                              }

                              if ($documento->envio_codpais != $this->empresa->codpais) {
                                 $pais0 = new pais();
                                 $pais = $pais0->get($documento->envio_codpais);
                                 if ($pais) {
                                    echo $pais->nombre;
                                 }
                              }
                           }
                           ?>
                        </td>
                        <td class='text-left' style='width: 20%;'>

                        </td>
                        <td class='text-left' style='width: 40%;'>
                           <?php
                           if (!$documento->envio_direccion) {
                              echo "<strong>" . $documento->nombrecliente . "</strong><br/>";

                              echo $documento->direccion . "<br/>";

                              if ($documento->apartado) {
                                 echo ucfirst(FS_APARTADO) . ': ' . $documento->apartado . "<br/>";
                              }

                              if ($documento->codpostal) {
                                 echo '<b>CP:</b> ' . $documento->codpostal;
                              }

                              $documento->ciudad;

                              if ($documento->provincia) {
                                 echo ' (' . $documento->provincia . ')';
                              }
                              if ($documento->codpais != $this->empresa->codpais) {
                                 $pais0 = new pais();
                                 $pais = $pais0->get($documento->codpais);
                                 if ($pais) {
                                    echo ' ' . $pais->nombre;
                                 }
                              }
                           } else {
                              echo "<strong>" . $documento->envio_nombre . " " . $documento->envio_apellidos . "</strong><br/>";

                              echo $documento->envio_direccion . "<br/>";

                              if ($documento->envio_apartado) {
                                 echo ucfirst(FS_APARTADO) . ': ' . $documento->envio_apartado . "<br/>";
                              }

                              if ($documento->envio_codpostal) {
                                 echo '<b>CP:</b> ' . $documento->envio_codpostal;
                              }

                              echo $documento->envio_ciudad;

                              if ($documento->envio_provincia) {
                                 echo ' (' . $documento->envio_provincia . ') ';
                              }

                              if ($documento->envio_codpais != $this->empresa->codpais) {
                                 $pais0 = new pais();
                                 $pais = $pais0->get($documento->envio_codpais);
                                 if ($pais) {
                                    echo $pais->nombre;
                                 }
                              }
                           }
                           ?>
                        </td>
                     </tr>
                  </tbody>
               </table>
            </section>
         </div>

         <!-- Cuerpo del documento con los datos del documento -->
         <div class="main">
            <section>
               <div class='table-wrapper'>
                  <table class='table-bordered-header' align='center'>
                     <thead>
                        <tr>
                           <th class='text-center' style='width: 47.5%;'>
                              REF + DESCRIPCIÓN
                           </th>
                           <th class='text-center' style='width: 7.5%;'>
                              CANT
                           </th>
                           <th class='text-center' style='width: 12.5%;'>
                              PVP
                           </th>
                           <th class='text-center' style='width: 10%;'>
                              DTO
                           </th>
                           <th class='text-center' style='width: 7.55%;'>
                              <?= FS_IVA ?>
                           </th>
                           <th class='text-center' style='width: 12.5%;'>
                              IMPORTE
                           </th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php
                        foreach ($lineas_documento as $linea_documento) {
                           echo "<tr>";
                           echo "   <td style='width: 47.5%;'><b>" . $linea_documento->referencia . "</b> " . $linea_documento->descripcion . "</td>";
                           echo "   <td class='text-right text-nowrap' style='width: 7.5%;'>";
                           echo $this->show_numero($linea_documento->cantidad, 2);
                           echo "   </td>";
                           echo "   <td class='text-right text-nowrap' style='width: 12.5%;'>";
                           echo $this->show_precio($linea_documento->pvpunitario, $documento->coddivisa, TRUE, FS_NF0_ART);
                           echo "   </td>";
                           echo "   <td  class='text-right text-nowrap'style='width: 10%;'>";
                           if ($linea_documento->dtopor != 0) {
                              echo $this->show_numero($linea_documento->dtopor) . " %";
                           }
                           echo "   </td>";
                           echo "   <td class='text-right text-nowrap' style='width: 7.5%;'>";
                           echo $linea_documento->iva . " %";
                           echo "   </td>";
                           echo "   <td class='table-middle text-right text-nowrap' style='width: 12.5%;'>";
                           echo $this->show_precio($linea_documento->pvptotal, $documento->coddivisa);
                           echo "   </td>";
                           echo "</tr>";
                        }
                        ?>
                     </tbody>
                  </table>
               </div>
            </section>
         </div>

         <div class="footer">
            <!-- Pie del documento con los totales, IVAs, RE e IRFP -->
            <section>
               <?php
               foreach ($lineas_iva_documento as $linea) {
                  /* Leemos los impuesto de las lineas */
                  $imp0 = new impuesto();
                  $impuesto = $imp0->get($linea->codimpuesto);
                  if ($impuesto) {
                     $tipos_iva['iva' . $linea->iva] = $impuesto->iva . '%';
                  } else {
                     $tipos_iva['iva' . $linea->iva] = $linea->iva . '%';
                  }
                  $subtotales_iva['iva' . $linea->iva] = $this->show_precio($linea->totaliva, $documento->coddivisa);

                  /* Leemos el recargo de equivalencia de las lineas */
                  if ($linea->totalrecargo != 0) {
                     $tipos_recargo['recargo' . $linea->recargo] = $linea->recargo . "%";
                     $subtotales_recargo['recargo' . $linea->totalrecargo] .= $this->show_precio($linea->totalrecargo, $documento->coddivisa);
                  }
               }
               foreach ($lineas_documento as $linea) {
                  /* Leemos el irpf de las lineas */
                  if ($documento->totalirpf != 0) {
                     $tipos_irpf['irpf'] = '<b>' . $linea->irpf . '%</b>';
                     $subtotales_irpf['irpf'] = $this->show_precio($linea->irpf, $documento->coddivisa);
                  }
               }
               ?>
               <table class='table-borderless text-left' align='center'>
                  <tr>
                     <td style='width: 70%;'>
                        <table class='table-bordered text-left' align='center'>
                           <thead>
                              <tr>
                                 <th class='text-center' style='width: 25%;'>
                                    NETO
                                 </th>
                                 <th class='text-center' style='width: 25%;' colspan='2'>
                                    <?= FS_IVA ?>
                                 </th>
                                 <th class='text-center' style='width: 25%;' colspan='2'>
                                    R.E.
                                 </th>
                                 <th class='text-center' style='width: 25%;' colspan='2'>
                                    <?= FS_IRPF ?>
                                 </th>
                              </tr>
                           </thead>
                           <tbody>
                              <tr>
                                 <td class='table-middle text-center text-nowrap'>
                                    <?php
                                    /* NETO */
                                    echo $this->show_precio($documento->neto, $documento->coddivisa);
                                    ?>
                                 </td>
                                 <td class='text-right text-nowrap' style='width: 7.5%;'>
                                    <?php
                                    /* TIPOS DE IVA */
                                    $count = 0;
                                    foreach ($tipos_iva as $tipo_iva) {
                                       echo $tipo_iva . "<br/>";
                                       $count++;
                                    }
                                    /* Para cuadrar estilo visual */
                                    if ($count <= 3) {
                                       for ($i = 0; 4 - $count > $i; $i++) {
                                          echo "<br/>";
                                       }
                                    }
                                    ?>
                                 </td>
                                 <td class='text-right text-nowrap' style='width: 17.5%;'>
                                    <?php
                                    /* IMPORTES IVA */
                                    $count = 0;
                                    foreach ($subtotales_iva as $subtotal_iva) {
                                       echo $subtotal_iva . "<br/>";
                                       $count++;
                                    }
                                    /* Para cuadrar estilo visual */
                                    if ($count <= 3) {
                                       for ($i = 0; 4 - $count > $i; $i++) {
                                          echo "<br/>";
                                       }
                                    }
                                    ?>
                                 </td>
                                 <td class='text-right text-nowrap' style='width: 7.5%;'>
                                    <?php
                                    /* TIPOS DE RECARGO DE EQUIVALENCIA */
                                    if ($documento->totalrecargo != 0) {
                                       $count = 0;
                                       foreach ($tipos_recargo as $tipo_recargo) {
                                          echo $tipo_recargo . "<br/>";
                                          $count++;
                                       }
                                       /* Para cuadrar estilo visual */
                                       if ($count <= 3) {
                                          for ($i = 0; 4 - $count > $i; $i++) {
                                             echo "<br/>";
                                          }
                                       }
                                    }
                                    ?>
                                 </td>
                                 <td class='text-right text-nowrap' style='width: 17.5%;'>
                                    <?php
                                    /* TIPOS DE RECARGO DE EQUIVALENCIA */
                                    if ($documento->totalrecargo != 0) {
                                       $count = 0;
                                       foreach ($subtotales_recargo as $subtotal_recargo) {
                                          echo $subtotal_recargo . "<br/>";
                                          $count++;
                                       }
                                       /* Para cuadrar estilo visual */
                                       if ($count <= 3) {
                                          for ($i = 0; 4 - $count > $i; $i++) {
                                             echo "<br/>";
                                          }
                                       }
                                    }
                                    ?>
                                 </td>
                                 <td class='text-right text-nowrap' style='width: 7.5%;'>
                                    <?php
                                    /* TIPOS DE IRPF  */
                                    if ($documento->totalirpf != 0) {
                                       $count = 0;
                                       foreach ($tipos_irpf as $tipo_irpf) {
                                          echo $tipo_irpf . "<br/>";
                                          $count++;
                                       }
                                       /* Para cuadrar estilo visual */
                                       if ($count <= 3) {
                                          for ($i = 0; 4 - $count > $i; $i++) {
                                             echo "<br/>";
                                          }
                                       }
                                    }
                                    ?>
                                 </td>
                                 <td class='text-right text-nowrap' style='width: 17.5%;'>
                                    <?php
                                    /* SUBTOTALES DE IRPF  */
                                    if ($documento->totalirpf != 0) {
                                       $count = 0;
                                       foreach ($subtotales_irpf as $subtotal_irpf) {
                                          echo $subtotal_irpf . "<br/>";
                                          $count++;
                                       }
                                       /* Para cuadrar estilo visual */
                                       if ($count <= 3) {
                                          for ($i = 0; 4 - $count > $i; $i++) {
                                             echo "<br/>";
                                          }
                                       }
                                    }
                                    ?>
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </td>
                     <td style='width: 5%;'>

                     </td>
                     <td style='width: 25%;'>
                        <table class='table-bordered text-center' align='center'>
                           <thead>
                              <tr>
                                 <th class='text-center' style='width: 100%;'>
                                    IMPORTE TOTAL
                                 </th>
                              </tr>
                           </thead>
                           <tbody>
                              <tr>
                                 <td class='table-middle text-center text-nowrap'>
                                    <h2><?= $this->show_precio($documento->totaleuros, $documento->coddivisa) ?></h2>
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </td>
                  </tr>
               </table>
            </section>
         </div>

      </div>

   </body>
</html>
