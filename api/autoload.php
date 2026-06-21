<?php

spl_autoload_register(function ($classe) {
    // 1. Remove barras invertidas iniciais
    $classe = ltrim($classe, '\\');

    // 2. Tenta o caminho direto (caso venha completo: Classes\DB\MySQL)
    $caminhoDireto = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $classe) . '.php';

    // 3. Tenta o caminho relativo (caso venha curto: DB\MySQL ou Util\RotasUtil)
    $caminhoRelativo = __DIR__ . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $classe) . '.php';

    if (file_exists($caminhoDireto)) {
        require_once $caminhoDireto;

        // Se a classe foi carregada pelo caminho completo, cria um atalho (alias)
        // para o caso de algum arquivo antigo tentar chamá-la sem o "Classes\"
        $classeCurta = str_replace('Classes\\', '', $classe);
        if ($classeCurta !== $classe && !class_exists($classeCurta, false)) {
            class_alias($classe, $classeCurta);
        }
    } elseif (file_exists($caminhoRelativo)) {
        require_once $caminhoRelativo;

        // Se o sistema pediu a classe curta (ex: DB\MySQL), nós carregamos o arquivo correto.
        // Mas o arquivo tem "namespace Classes\DB;", então o PHP registrou "Classes\DB\MySQL".
        // Criamos o alias para o PHP mapear "DB\MySQL" para "Classes\DB\MySQL" dinamicamente.
        $classeCompleta = 'Classes\\' . $classe;
        if (!class_exists($classe, false) && class_exists($classeCompleta, false)) {
            class_alias($classeCompleta, $classe);
        }
    }
});