<?php

$retrofitResponse = new \Tebru\Retrofit\Http\Response($response, 'raw', $this->deserializerAdapter, array());
$return = $retrofitResponse;
$returnEvent = new \Tebru\Retrofit\Event\ReturnEvent($return);
$this->eventDispatcher->dispatch('retrofit.return', $returnEvent);
return $returnEvent->getReturn();
