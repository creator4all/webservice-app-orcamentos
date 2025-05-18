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
    
    /**
     * Salva um arquivo de imagem enviado via upload e retorna o caminho relativo
     * @param \Psr\Http\Message\UploadedFileInterface $uploadedFile Arquivo enviado
     * @param string $directory Diretório onde a imagem será salva
     * @param string $filename Nome do arquivo (opcional)
     * @param int $maxSize Tamanho máximo em bytes (padrão 5MB)
     * @return string|false Caminho do arquivo salvo ou false em caso de erro
     */
    public static function saveUploadedImage($uploadedFile, $directory, $filename = null, $maxSize = 5242880) {
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                return false;
            }
        }
        
        if ($uploadedFile->getSize() > $maxSize) {
            return false;
        }
        
        $mimeType = $uploadedFile->getClientMediaType();
        if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
            return false;
        }
        
        $extension = ($mimeType === 'image/png') ? 'png' : 'jpg';
        
        if (empty($filename)) {
            $filename = uniqid('img_') . '.' . $extension;
        } else {
            $filename = basename($filename);
            if (!str_ends_with(strtolower($filename), ".{$extension}")) {
                $filename .= ".{$extension}";
            }
        }
        
        $filePath = $directory . '/' . $filename;
        
        try {
            $uploadedFile->moveTo($filePath);
            return str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Valida se o arquivo enviado é uma imagem JPG ou PNG válida
     * @param \Psr\Http\Message\UploadedFileInterface $uploadedFile Arquivo enviado
     * @return bool True se for uma imagem válida (JPG ou PNG)
     */
    public static function validateImage($uploadedFile) {
        $mimeType = $uploadedFile->getClientMediaType();
        return in_array($mimeType, ['image/jpeg', 'image/png']);
    }
}
