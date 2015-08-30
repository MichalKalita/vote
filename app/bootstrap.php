<?php

require __DIR__ . '/../vendor/autoload.php';

\Tracy\Debugger::$showLocation = TRUE;

function barDump($var, $title = NULL, $options = NULL) {
	\Tracy\Debugger::barDump($var, $title, $options);
}

$configurator = new Nette\Configurator;

$configurator->setDebugMode('194.228.13.179'); // enable for your remote IP
$configurator->enableDebugger(__DIR__ . '/../log', 'log@lydragos.cz');

$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
		->addDirectory(__DIR__)
		->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

return $container;
