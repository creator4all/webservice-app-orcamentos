<?php
namespace App\Utils;

class PDFGenerator {
    private function __construct() 
    {
    }
    
    /**
     * Gera uma carta senha em PDF para um gestor
     * @param array $dados Dados do gestor (nome, email, senha)
     * @param string $directory Diretório onde o PDF será salvo
     * @return string|false Caminho do arquivo salvo ou false em caso de erro
     */
    public static function gerarCartaSenha($dados, $directory) {
        if (!extension_loaded('gd')) {
            return false;
        }

        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                return false;
            }
        }
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Carta Senha</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .content { margin-bottom: 40px; }
                .footer { text-align: center; font-size: 12px; margin-top: 50px; }
                table { width: 100%; border-collapse: collapse; }
                table, th, td { border: 1px solid #ddd; }
                th, td { padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Multimídia Parceiro (B2B)</h1>
                <h2>Carta Senha de Acesso</h2>
            </div>
            
            <div class="content">
                <p>Prezado(a) <strong>' . htmlspecialchars($dados['nome_gestor']) . '</strong>,</p>
                
                <p>Bem-vindo ao Aplicativo Multimídia Parceiro (B2B). Abaixo estão suas credenciais de acesso:</p>
                
                <table>
                    <tr>
                        <th>Usuário (Email)</th>
                        <td>' . htmlspecialchars($dados['email']) . '</td>
                    </tr>
                    <tr>
                        <th>Senha</th>
                        <td>' . htmlspecialchars($dados['senha']) . '</td>
                    </tr>
                </table>
                
                <p><strong>IMPORTANTE:</strong> Por motivos de segurança, recomendamos que você altere sua senha após o primeiro acesso.</p>
            </div>
            
            <div class="footer">
                <p>Este documento é confidencial e destinado exclusivamente ao usuário indicado.</p>
                <p>Gerado em: ' . date('d/m/Y H:i:s') . '</p>
            </div>
        </body>
        </html>';
        
        $filename = 'carta_senha_' . uniqid() . '.pdf';
        $filePath = $directory . '/' . $filename;
        
        $tempHtmlFile = $directory . '/temp_' . uniqid() . '.html';
        file_put_contents($tempHtmlFile, $html);
        
        if (shell_exec('which wkhtmltopdf')) {
            $command = 'wkhtmltopdf "' . $tempHtmlFile . '" "' . $filePath . '"';
            shell_exec($command);
            unlink($tempHtmlFile);
            
            if (file_exists($filePath)) {
                return str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath);
            }
        } else {
            $filename = 'carta_senha_' . uniqid() . '.html';
            $filePath = $directory . '/' . $filename;
            file_put_contents($filePath, $html);
            
            return str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath);
        }
        
        return false;
    }
}
