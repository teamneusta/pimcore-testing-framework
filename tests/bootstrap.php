<?php

declare(strict_types=1);

include dirname(__DIR__) . '/vendor/autoload.php';

Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');

Neusta\Pimcore\TestingFramework\Pimcore\BootstrapPimcore::bootstrap();
