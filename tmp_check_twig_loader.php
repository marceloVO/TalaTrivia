<?php
require __DIR__ . '/skeleton/vendor/autoload.php';
$kernel = new App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
$loader = null;
if ($container->has('twig.loader.native_filesystem')) {
    $loader = $container->get('twig.loader.native_filesystem');
} elseif ($container->has('twig.loader')) {
    $loader = $container->get('twig.loader');
}
if (!$loader) {
    echo "no loader service\n";
    exit(1);
}
if (method_exists($loader, 'getNamespaces')) {
    var_export($loader->getNamespaces());
} else {
    echo "loader has no getNamespaces\n";
}

