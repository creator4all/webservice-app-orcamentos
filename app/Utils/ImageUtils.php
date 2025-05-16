<?php
namespace App\Utils;

class ImageUtils {
    private function __construct() 
    {
    }
    
    /**
     * Converte uma imagem base64 para arquivo e salva no diretório especificado
     * @param string $base64Image String base64 da imagem
     * @param string $directory Diretório onde a imagem será salva
     * @param string $filename Nome do arquivo (opcional)
     * @return string|false Caminho do arquivo salvo ou false em caso de erro
     */
    public static function saveBase64Image($base64Image, $directory, $filename = null) {
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                return false;
            }
        }
        
        if (strpos($base64Image, 'data:image/jpeg;base64,') === 0) {
            $base64Image = substr($base64Image, strlen('data:image/jpeg;base64,'));
        }
        
        $imageData = base64_decode($base64Image);
        
        if (!$imageData) {
            return false;
        }
        
        if (substr($imageData, 0, 2) !== "\xFF\xD8") {
            return false;
        }
        
        if (empty($filename)) {
            $filename = uniqid('img_') . '.jpg';
        } else {
            $filename = basename($filename);
            if (pathinfo($filename, PATHINFO_EXTENSION) !== 'jpg') {
                $filename .= '.jpg';
            }
        }
        
        $filePath = $directory . '/' . $filename;
        
        if (file_put_contents($filePath, $imageData)) {
            return str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath);
        }
        
        return false;
    }
    
    /**
     * Valida se o conteúdo base64 é uma imagem JPG válida
     * @param string $base64Image String base64 da imagem
     * @return bool True se for uma imagem JPG válida
     */
    public static function validateJpgImage($base64Image) {
        if (strpos($base64Image, 'data:image/jpeg;base64,') === 0) {
            $base64Image = substr($base64Image, strlen('data:image/jpeg;base64,'));
        }
        
        $imageData = base64_decode($base64Image);
        
        if (!$imageData) {
            return false;
        }
        
        return substr($imageData, 0, 2) === "\xFF\xD8";
    }
}
