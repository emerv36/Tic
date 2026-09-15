<?php
/**
 * Webhook de entrada para integración de personal (SIGE -> TIC)
 * Recibe eventos de forma asíncrona y los encola en sige_personal_inbox.
 */
require_once(__DIR__ . '/developer/Controller/personalEventReceiverController.php');

$controller = new PersonalEventReceiverController();
$controller->handleRequest();
