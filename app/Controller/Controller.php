<?php

namespace App\Controller;

use Psr\Container\ContainerInterface;

class Controller{
    public $container;

    protected $injector;
    public $requestAtual;

    function __construct (ContainerInterface $container){
        $this->container = $container;
        // Only try to get injector if it exists in the container
        if ($container->has('injector')) {
            $this->injector = $container->get('injector');
        }
    }

    function __get($property){
        if($this->container->{$property}){
            return $this->container->{$property};
        }
    }

    protected function encapsular_response($callbackRequest, $request, $response, $args) {
        $this->requestAtual = $request;
        // Default language is Portuguese
        $idioma = 'pt_BR';

        try {
            $rawResponse = $callbackRequest($request, $response, $args);

            if (array_key_exists("statusCodeHttp", $rawResponse) && $rawResponse['statusCodeHttp'] != 200) {
                $mensagem = "Não foi possível completar sua solicitação.";

                if (isset($rawResponse['mensagem'])) {
                    $mensagem = $rawResponse['mensagem'];
                }

                if (isset($rawResponse['justificativa'])) {
                    $mensagem = [
                        "status" => "erro", 
                        "mensagem" => $rawResponse['justificativa'],
                    ];
                }

                $response->getBody()->write(json_encode($mensagem));
                return $response->withHeader('Content-Type', 'application/json')->withStatus($rawResponse['statusCodeHttp']);
            }

        } catch (\Exception $e) {
            $rawResponse = [
                "status" => "erro",
                "mensagem" => 'Erro inesperado: ' . $e->getMessage()
            ];
            if($_SERVER['HTTP_HOST'] == 'localhost') {
                $rawResponse['trace'] = $e->getTrace()[0];
            }
        }

        $response->getBody()->write(json_encode($rawResponse));
        return $response->withHeader('Content-Type', 'application/json');
    }
}