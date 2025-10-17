<?php
require __DIR__ . '/skeleton/vendor/autoload.php';
$kernel = new App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$twig = $container->get('twig');
$loader = $twig->getLoader();
if (method_exists($loader, 'getNamespaces')) {
    var_export($loader->getNamespaces());
} else {
    echo "no getNamespaces\n";
}

