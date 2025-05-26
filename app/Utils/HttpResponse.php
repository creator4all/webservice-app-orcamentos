<?php

class HttpResponse {
    public static function http_response(int $statusCodeHttp, string $message = '', array $data = [], string $justificativa = ''): array {
        if(count($data) > 0){
            return [
                'statusCodeHttp' => $statusCodeHttp,
                'mensagem' => $message,
                'data' => $data,
            ];
        } else if($justificativa != '') {
            return [
                'statusCodeHttp' => $statusCodeHttp,
                'mensagem' => $message,
                'justificativa' => $justificativa,
            ];
        }
        return [
            'status' => $statusCodeHttp,
            'message' => $message,
        ];
    }

    public static function created(string $message = '', array $data = []): array {
        return self::http_response(201, $message, $data);
    }
}