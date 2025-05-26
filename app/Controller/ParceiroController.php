<?php
namespace App\Controller;

use App\Model\ParceiroModel;
use App\Model\UsuarioModel;
use App\Model\ParceiroAtualizacaoLogModel;
use App\Utils\InputSanitizer;
use App\Utils\CNPJValidator;
use App\Utils\ImageUtils;
use App\Utils\Validator;
use App\DAO\ParceiroDAO;
use App\DAO\UsuarioDAO;
use App\DAO\ParceiroAtualizacaoLogDAO;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ParceiroController extends Controller {
    public function insert_partner(Request $request, Response $response, array $args){
        $this->encapsular_response(function(Request $request, Response $response, $args){
            $data = $request->getParsedBody();
            $parceiroService = new ParceiroService();
            return $parceiroService->insert($data);
        }, $request, $response, $args);
    }

    public function edit_partner(Request $request, Response $response, array $args){
        $this->encapsular_response(function(Request $request, Response $response, $args){
            $data = $request->getParsedBody();
            $parceiroService = new ParceiroService();
            return $parceiroService->edit($request->getAttribute('usuario'), $data, $args);
        }, $request, $response, $args);
    }
}
