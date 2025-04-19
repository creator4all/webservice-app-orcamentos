<?php
require __DIR__ . '/../vendor/autoload.php';

$openapi = \OpenApi\Generator::scan([__DIR__ . '/../app']);
$json = $openapi->toJson();

// Create docs directory if it doesn't exist
if (!is_dir(__DIR__ . '/../public/docs')) {
    mkdir(__DIR__ . '/../public/docs', 0755, true);
}

// Write the OpenAPI JSON to a file
file_put_contents(__DIR__ . '/../public/docs/openapi.json', $json);

echo "OpenAPI documentation generated successfully!\n";
