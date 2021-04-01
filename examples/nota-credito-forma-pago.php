<?php

declare(strict_types=1);

use Greenter\Model\Response\BillResult;
use Greenter\Model\Sale\Cuota;
use Greenter\Model\Sale\Document;
use Greenter\Model\Sale\FormaPagos\FormaPagoCredito;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Ws\Services\SunatEndpoints;

require __DIR__ . '/../vendor/autoload.php';

$util = Util::getInstance();

$note = new Note();
$note
    ->setUblVersion('2.1')
    ->setTipoDoc('07')
    ->setSerie('FF01')
    ->setCorrelativo('124')
    ->setFechaEmision(new DateTime())
    // Forma de Pago
    ->setFormaPago(new FormaPagoCredito(236))
    ->setCuotas([
        (new Cuota())
        ->setMonto(236)
        ->setFechaPago(new DateTime('+5days'))
    ])
    ->setTipDocAfectado('01') // Tipo Doc: Factura
    ->setNumDocfectado('F001-111') // Factura: Serie-Correlativo
    ->setCodMotivo('13') // Catalogo. 09: Ajustes – montos y/o fechas de pago
    ->setDesMotivo('DEVOLUCION POR ITEM')
    ->setTipoMoneda('PEN')
    ->setGuias([/* Guias (Opcional) */
        (new Document())
        ->setTipoDoc('09')
        ->setNroDoc('0001-213')
    ])
    ->setCompany($util->shared->getCompany())
    ->setClient($util->shared->getClient())
    ->setMtoOperGravadas(200)
    ->setMtoIGV(36)
    ->setTotalImpuestos(36)
    ->setMtoImpVenta(236)
    ;

$detail1 = new SaleDetail();
$detail1
    ->setCodProducto('C023')
    ->setUnidad('NIU')
    ->setCantidad(2)
    ->setDescripcion('PROD 1')
    ->setMtoBaseIgv(100)
    ->setPorcentajeIgv(18.00)
    ->setIgv(18)
    ->setTipAfeIgv('10')
    ->setTotalImpuestos(18)
    ->setMtoValorVenta(100)
    ->setMtoValorUnitario(50)
    ->setMtoPrecioUnitario(56);

$detail2 = new SaleDetail();
$detail2
    ->setCodProducto('C02')
    ->setUnidad('NIU')
    ->setCantidad(2)
    ->setDescripcion('PROD 2')
    ->setMtoBaseIgv(100)
    ->setPorcentajeIgv(18.00)
    ->setIgv(18)
    ->setTipAfeIgv('10')
    ->setTotalImpuestos(18)
    ->setMtoValorVenta(100)
    ->setMtoValorUnitario(50)
    ->setMtoPrecioUnitario(56);

$legend = new Legend();
$legend->setCode('1000')
    ->setValue('SON DOSCIENTOS TREINTA Y SEIS CON 00/100 SOLES');

$note->setDetails([$detail1, $detail2])
    ->setLegends([$legend]);

// Envio a SUNAT.
$see = $util->getSee(SunatEndpoints::FE_BETA);

$res = $see->send($note);
$util->writeXml($note, $see->getFactory()->getLastXml());

if (!$res->isSuccess()) {
    echo $util->getErrorResponse($res->getError());
    exit();
}

/**@var $res BillResult*/
$cdr = $res->getCdrResponse();
$util->writeCdr($note, $res->getCdrZip());

$util->showResponse($note, $cdr);
