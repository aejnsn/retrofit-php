<?php
/*
 * Copyright (c) Nate Brunette.
 * Distributed under the MIT License (http://opensource.org/licenses/MIT)
 */

namespace Tebru\Retrofit\Generation\Listener;

use Tebru\Dynamo\Event\StartEvent;
use Tebru\Dynamo\Model\ClassModel;
use Tebru\Dynamo\Model\MethodModel;
use Tebru\Dynamo\Model\ParameterModel;
use Tebru\Dynamo\Model\PropertyModel;

/**
 * Class DynamoStartListener
 *
 * @author Nate Brunette <n@tebru.net>
 */
class DynamoStartListener
{
    /**
     * Handle the event
     *
     * @param StartEvent $event
     */
    public function __invoke(StartEvent $event)
    {
        $classModel = $event->getClassModel();
        $this->addProperties($classModel);
        $this->addConstructor($classModel);
        $this->addWait($classModel);
    }

    /**
     * Add properties to the class
     *
     * @param ClassModel $classModel
     */
    private function addProperties(ClassModel $classModel)
    {
        $baseUrl = new PropertyModel($classModel, 'baseUrl');
        $client = new PropertyModel($classModel, 'client');
        $eventDispatcher = new PropertyModel($classModel, 'eventDispatcher');
        $serializerAdapter = new PropertyModel($classModel, 'serializerAdapter');
        $deserializerAdapter = new PropertyModel($classModel, 'deserializerAdapter');

        $classModel->addProperty($baseUrl);
        $classModel->addProperty($client);
        $classModel->addProperty($eventDispatcher);
        $classModel->addProperty($serializerAdapter);
        $classModel->addProperty($deserializerAdapter);
    }

    /**
     * Create constructor
     *
     * @param ClassModel $classModel
     */
    private function addConstructor(ClassModel $classModel)
    {
        $methodModel = new MethodModel($classModel, '__construct');

        $baseUrl = new ParameterModel($methodModel, 'baseUrl', false);

        $client = new ParameterModel($methodModel, 'client', false);
        $client->setTypeHint('\Tebru\Retrofit\Adapter\HttpClientAdapter');

        $eventDispatcher = new ParameterModel($methodModel, 'eventDispatcher', false);
        $eventDispatcher->setTypeHint('\Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $serializerAdapter = new ParameterModel($methodModel, 'serializerAdapter', true);
        $serializerAdapter->setTypeHint('\Tebru\Retrofit\Adapter\SerializerAdapter');

        $deserializerAdapter = new ParameterModel($methodModel, 'deserializerAdapter', true);
        $deserializerAdapter->setTypeHint('\Tebru\Retrofit\Adapter\DeserializerAdapter');

        $methodModel->addParameter($baseUrl);
        $methodModel->addParameter($client);
        $methodModel->addParameter($eventDispatcher);
        $methodModel->addParameter($serializerAdapter);
        $methodModel->addParameter($deserializerAdapter);

        $methodBody = [
            '$this->baseUrl = $baseUrl;',
            '$this->client = $client;',
            '$this->eventDispatcher = $eventDispatcher;',
            '$this->serializerAdapter = $serializerAdapter;',
            '$this->deserializerAdapter = $deserializerAdapter;',
        ];

        $methodModel->setBody(implode($methodBody));

        $classModel->addMethod($methodModel);
    }

    /**
     * Create wait method
     *
     * @param ClassModel $classModel
     * @return null
     */
    private function addWait(ClassModel $classModel)
    {
        $reflectionClass = new \ReflectionClass($classModel->getInterface());

        if (!in_array('Tebru\Retrofit\Http\AsyncAware', $reflectionClass->getInterfaceNames(), true)) {
            return null;
        }

        $methodModel = new MethodModel($classModel, 'wait');

        $methodModel->setBody('$this->client->wait();');

        $classModel->addMethod($methodModel);
    }
}
