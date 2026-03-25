<?php
/**
 * Atualiza a versão do composer.json durante o release automático.
 * Uso: php .github/scripts/update-version.php 1.2.3
 */

if ($argc < 2) {
    fwrite(STDERR, "Uso: php update-version.php <versão>\n");
    exit(1);
}

$novaVersao = $argv[1];
$caminho = __DIR__ . '/../../composer.json';

if (!file_exists($caminho)) {
    fwrite(STDERR, "composer.json não encontrado em $caminho\n");
    exit(1);
}

$composer = json_decode(file_get_contents($caminho), true);
$composer['version'] = $novaVersao;

file_put_contents(
    $caminho,
    json_encode($composer, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL
);

echo "composer.json atualizado para versão {$novaVersao}\n";
