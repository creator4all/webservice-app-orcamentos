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

    protected function db() {
        return $this->container->get('db');
    }

    public function validar(string|array $itens) {
        if(!$this->requestAtual) {
            echo "Você chamou o método validar() fora do encapsular_response, isto não é permitido";
            return;
        }
        $data = $this->requestAtual->getParsedBody();

        if(is_array($itens)) {
            $itens = $itens;
        } else {
            $itens = [$itens];
        }

        if(json_last_error() != JSON_ERROR_NONE) {
            throw new ValidacaoException("O json enviado está inválido");
        }
        if(empty($data)){
            throw new ValidacaoException("Você não enviou nada no body do request");
        }
        
        $itens_faltando = array_values(
            array_filter(
                $itens,
                function ($item) use ($data) {
                    return !array_key_exists($item, $data);
                }
            )
        );
        if(empty($itens_faltando)) {
            return true;
        } else {
            $mensagem_erro;
            if(count($itens_faltando) == 1) {
                $mensagem_erro = "Está faltando na sua solicitação o parâmetro " . $itens_faltando[0];
            } else {
                $mensagem_erro = "Está faltando os seguintes parâmetros na sua solicitação: " . join(", ", $itens_faltando);
            }
            throw new ValidacaoException($mensagem_erro);
        }

    }

    protected function log(string $collection, string $codigo, $adicional, bool $esconder_body, $usuario = null) {
        if(!$this->requestAtual) {
            echo "Você chamou o método log() fora do encapsular_response, isto não é permitido";
            return;
        }
        $mongodb = $this->container->get('mongo');
        $usuario_logado = $this->requestAtual->getAttribute('usuario');
        try {
            LogService::gravar_log(
                $mongodb,
                $usuario ?? $usuario_logado,
                $this->requestAtual,
                $collection,
                $codigo,
                $adicional,
                $esconder_body
            );
        } catch (\Exception $e) {
            LogService::gerar_log($e->getMessage());
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