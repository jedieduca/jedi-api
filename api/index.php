<?php
use Classes\DB\MySQL;
//Inicia a configuração das URLs
configuraCORS();

// 2. Responder ao Preflight (OPTIONS)
// O navegador envia isso antes do POST real para verificar permissões
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

//Inicia o sistema em si
require_once 'bootstrap.php'; // Registra autoload e constantes

try {
    // O RotasUtil analisa a URL para saber qual recurso foi pedido
    $request = \Util\RotasUtil::getRotas();

    //Chamamos o request validator para direcionar o request
    $validator = new \Validator\RequestValidator($request);
    $resultado = $validator->processarRequest();

    //pega o json que já é enviado e só mostra ele
    echo json_encode($resultado);

} catch (\Throwable $e) {
    // Captura qualquer erro de banco ou validação e retorna como JSON
    echo json_encode($request);
    http_response_code(400);
    echo json_encode([
        'erro' => $e->getMessage()
    ]);
}

/**
 * @return void
 */
function configuraCORS()
{
    $URLsPermitidas = [
        'http://localhost:3000',
        'https://seu-front-producao.com',
        'https://jedieduca.vercel.app',
        'http://app.jedieduca.com.br/'
    ];

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array($origin, $URLsPermitidas, true)) {
        header("Access-Control-Allow-Origin: $origin");
        header("Vary: Origin");
    } else {
        // Caso esteja testando em outros ambientes, descomente a linha abaixo:
        header("Access-Control-Allow-Origin: *");
    }

    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization, X-Requested-With');
    header('Access-Control-Max-Age: 86400');
    header('Content-Type: application/json; charset=utf-8');
}