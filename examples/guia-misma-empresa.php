<?php

declare(strict_types=1);

use Greenter\Model\Client\Client;
use Greenter\Model\Despatch\Despatch;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Model\Despatch\Direction;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Response\CdrResponse;
use Greenter\Ws\Services\SunatEndpoints;

require __DIR__ . '/../vendor/autoload.php';

$util = Util::getInstance();

$envio = new Shipment();
$envio
    ->setCodTraslado('04') // Cat.20 - Traslado entre establecimientos de la misma empresa
    ->setModTraslado('02') // Cat.18 - Transp. Privado
    ->setIndicador(['SUNAT_Envio_IndicadorTrasladoVehiculoM1L']) // Transp M1 y L
    ->setFecTraslado(new DateTime())
    ->setPesoTotal(12.5)
    ->setUndPesoTotal('KGM')
    ->setLlegada((new Direction('150101', 'AV LIMA'))
        ->setRuc('20123456789')
        ->setCodLocal('00002')) // Código de establecimiento anexo
    ->setPartida((new Direction('150203', 'AV ITALIA'))
        ->setRuc('20123456789')
        ->setCodLocal('00001'));

$despatch = new Despatch();
$despatch->setVersion('2022')
    ->setTipoDoc('09')
    ->setSerie('T001')
    ->setCorrelativo('123')
    ->setFechaEmision(new DateTime())
    ->setCompany($util->shared->getCompany())
    ->setDestinatario((new Client())
        ->setTipoDoc('6')
        ->setNumDoc('20123456789')
        ->setRznSocial('GREENTER SAC')) // misma empresa
    ->setEnvio($envio);

$detail = new DespatchDetail();
$detail->setCantidad(2)
    ->setUnidad('ZZ')
    ->setDescripcion('PROD 1')
    ->setCodigo('PROD1')
    ->setCodProdSunat('10101508');

$despatch->setDetails([$detail]);

// Envio a SUNAT.
$see = $util->getSee(SunatEndpoints::GUIA_BETA);

$xml = $see->getXmlSigned($despatch);
$util->writeXml($despatch, $xml);

$cdr = (new CdrResponse())
    ->setCode('-')
    ->setDescription('XML valido')
    ->setNotes([]);
$util->showResponse($despatch, $cdr);