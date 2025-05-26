<?php

namespace App\Utils;

use App\Utils\ImageUtils;

class ParceiroUtils{
    public static function valid_permission_edit($usuario): bool
    {
        return $usuario && ($usuario['role_id'] == 1 || $usuario['role_id'] == 2);
    }

    public static function insert_logomarca($logomarca, $img_name)
    {
        $upload_dir = __DIR__ . '/../../public/uploads/logomarcas';
        $file_name = 'logo_' . $img_name . '_' . uniqid() . '.png';
        $logomarca_path = ImageUtils::saveBase64Image($logomarca, $upload_dir, $file_name);
        if (!$logomarca_path) {
            return false;
        }
        return $logomarca_path;
    }
}