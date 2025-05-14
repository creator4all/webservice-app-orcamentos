<?php
namespace App\Controller;

use App\Model\PasswordResetTokenModel;
use App\DAO\PasswordResetTokenDAO;
use App\DAO\UsuarioDAO;
use App\Utils\EmailService;
use App\Utils\InputSanitizer;
use App\Utils\Validator;
use App\Utils\TokenUtils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \DateTime;

class PasswordResetController extends Controller {
    const TOKEN_EXPIRATION_MINUTES = 30;
    const OTP_LENGTH = 6;
    
    /**
     * Handle the password recovery request
     */
    public function recoverPassword(Request $request, Response $response, $args) {
        return $this->encapsular_response(function($request, $response, $args) {
            $data = $request->getParsedBody();
            $data = is_array($data) ? $data : [];
            $data = InputSanitizer::sanitizeArray($data);
            
            $validation = Validator::validate($data, ['email']);
            if (!$validation['valido']) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Campos obrigatórios não fornecidos.',
                    'erros' => $validation['erros']
                ];
            }
            
            if (!InputSanitizer::validateEmail($data['email'])) {
                return [
                    'statusCodeHttp' => 200,
                    'status' => 'sucesso',
                    'mensagem' => 'Se o e-mail estiver cadastrado, você receberá instruções para recuperação de senha.'
                ];
            }
            
            $userDAO = new UsuarioDAO();
            $user = $userDAO->buscarPorEmail($data['email']);
            
            if (!$user) {
                return [
                    'statusCodeHttp' => 200,
                    'status' => 'sucesso',
                    'mensagem' => 'Se o e-mail estiver cadastrado, você receberá instruções para recuperação de senha.'
                ];
            }
            
            $token = TokenUtils::generateOTP(self::OTP_LENGTH);
            
            $expiresAt = new DateTime();
            $expiresAt->modify('+' . self::TOKEN_EXPIRATION_MINUTES . ' minutes');
            
            $tokenModel = new PasswordResetTokenModel([
                'usuarios_id' => $user->idUsuarios,
                'token' => $token,
                'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                'used' => false
            ]);
            
            $tokenDAO = new PasswordResetTokenDAO();
            
            $tokenDAO->invalidarTokensAnteriores($user->idUsuarios);
            
            $result = $tokenDAO->criar($tokenModel);
            
            if (!$result) {
                error_log("Failed to create password reset token for user ID: {$user->idUsuarios}");
                return [
                    'statusCodeHttp' => 200,
                    'status' => 'sucesso',
                    'mensagem' => 'Se o e-mail estiver cadastrado, você receberá instruções para recuperação de senha.'
                ];
            }
            
            $emailSent = EmailService::enviarEmailRecuperacaoSenha($user->email, $user->nome, $token);
            
            if (!$emailSent) {
                error_log("Failed to send password reset email to: {$user->email}");
            }
            
            return [
                'statusCodeHttp' => 200,
                'status' => 'sucesso',
                'mensagem' => 'Se o e-mail estiver cadastrado, você receberá instruções para recuperação de senha.'
            ];
        }, $request, $response, $args);
    }
    
    /**
     * Reset password using OTP code
     */
    public function resetPassword(Request $request, Response $response, $args) {
        return $this->encapsular_response(function($request, $response, $args) {
            $data = $request->getParsedBody();
            $data = is_array($data) ? $data : [];
            $data = InputSanitizer::sanitizeArray($data);
            
            $validation = Validator::validate($data, ['token', 'password', 'password_confirmation']);
            if (!$validation['valido']) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Campos obrigatórios não fornecidos.',
                    'erros' => $validation['erros']
                ];
            }
            
            if (!preg_match('/^\d{' . self::OTP_LENGTH . '}$/', $data['token'])) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Formato de token inválido.',
                ];
            }
            
            if (!InputSanitizer::validatePassword($data['password'])) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'A senha não atende aos requisitos mínimos de segurança.',
                ];
            }
            
            if ($data['password'] !== $data['password_confirmation']) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'As senhas não coincidem.',
                ];
            }
            
            $tokenDAO = new PasswordResetTokenDAO();
            $token = $tokenDAO->buscarPorToken($data['token']);
            
            if (!$token) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Token inválido ou expirado.',
                ];
            }
            
            if ($token->isExpired()) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Token expirado.',
                ];
            }
            
            if ($token->isUsed()) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Token já utilizado.',
                ];
            }
            
            $userDAO = new UsuarioDAO();
            $user = $userDAO->buscarPorId($token->usuarios_id);
            
            if (!$user) {
                return [
                    'statusCodeHttp' => 400,
                    'mensagem' => 'Usuário não encontrado.',
                ];
            }
            
            $user->password = password_hash($data['password'], PASSWORD_DEFAULT);
            $userUpdated = $userDAO->atualizarSenha($user);
            
            if (!$userUpdated) {
                return [
                    'statusCodeHttp' => 500,
                    'mensagem' => 'Erro ao atualizar senha.',
                ];
            }
            
            $tokenDAO->marcarComoUsado($token);
            
            return [
                'statusCodeHttp' => 200,
                'status' => 'sucesso',
                'mensagem' => 'Senha atualizada com sucesso.',
            ];
        }, $request, $response, $args);
    }
}
