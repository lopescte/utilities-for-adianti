<?php
namespace Lopescte\UtilitiesForAdianti\Services;

/**
 * Class LgpdMaskService
 *
 * @category   library
 * @package    lopescte\utilities-for-adianti
 * @url        https://github.com/lopescte/utilities-for-adianti
 * @author     Marcelo Lopes <lopes.cte@gmail.com>
 * @copyright  Copyright (c) 2026 Reis & Lopes Assessoria e Sistemas. (https://www.reiselopes.com.br)
 * @license    http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license    https://opensource.org/licenses/MIT MIT
 * @license    http://www.gnu.org/licenses/gpl.txt GPLv3+
 */
class LgpdMaskService
{
    /**
     * @method aplicar()
     * @array campos
     */
    public static function aplicarTodas(string $conteudo, int $mostrarInicio = 2, int $mostrarFim = 2, string $char = '*'): string
    {
        // CPF
        $conteudo = preg_replace_callback(
            '/\b\d{3}[.\s-]?\d{3}[.\s-]?\d{3}[.\s-]?\d{2}\b/',
            fn($m) => self::mascararCPF($m[0], $mostrarInicio, $mostrarFim, true, $char),
            $conteudo
        );
    
        // Telefones
        $conteudo = preg_replace_callback(
            '/\b(\(?\d{2}\)?\s?)?\d{4,5}-?\d{4}\b/',
            fn($m) => self::mascararTelefone($m[0], $mostrarInicio, $mostrarFim, $char),
            $conteudo
        );
    
        // E-mails
        $conteudo = preg_replace_callback(
            '/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i',
            fn($m) => self::mascararEmail($m[0], $mostrarInicio, $mostrarFim, $char),
            $conteudo
        );
    
        return $conteudo;
    }
    
    /**
     * @method mascararCPF()
     * @array campos
     */
    public static function mascararCPF(
        string $texto,
        int $mostrarInicio = 2,
        int $mostrarFim = 2,
        bool $validar = true,
        string $char = '*'
    ): string {
        $regex = '/\b\d{3}[.\s]?\d{3}[.\s]?\d{3}[-\s]?\d{2}\b/';
    
        return preg_replace_callback($regex, function ($matches) use ($mostrarInicio, $mostrarFim, $validar, $char) {
            return self::isCPF($matches[0], $mostrarInicio, $mostrarFim, $validar, $char);
        }, $texto);
    }
    
    /**
     * @method mascararTelefone()
     * @array campos
     */
     
    public static function mascararTelefone(
        string $texto,
        int $mostrarInicio = 2,
        int $mostrarFim = 2,
        string $char = '*'
    ): string {
        $regex = '/(?:\+?55\s?)?(?:\(?\d{2}\)?\s?)?\d{4,5}[-\s]?\d{4}\b/';
    
        return preg_replace_callback($regex, function ($matches) use ($mostrarInicio, $mostrarFim, $char) {
            return self::isTelefone($matches[0], $mostrarInicio, $mostrarFim, $char);
        }, $texto);
    }
    
    /**
     * @method isTelefone()
     * @array campos
     */
    private static function isTelefone(
        string $telefone,
        int $mostrarInicio = 2,
        int $mostrarFim = 2,
        string $char = '*'
    ): string {
        $numerico = preg_replace('/\D/', '', $telefone);

        if (!in_array(strlen($numerico), [10, 11])) {
            return $telefone;
        }
    
        $mascarado = self::mascararValor($numerico, $mostrarInicio, $mostrarFim, $char);
    
        if (preg_match('/[()\-\s]/', $telefone)) {
            if (strlen($mascarado) === 11) {
                return '(' . substr($mascarado, 0, 2) . ') ' .
                       substr($mascarado, 2, 5) . '-' .
                       substr($mascarado, 7, 4);
            }
    
            return '(' . substr($mascarado, 0, 2) . ') ' .
                   substr($mascarado, 2, 4) . '-' .
                   substr($mascarado, 6, 4);
        }
    
        return $mascarado;
    }
    
    /**
     * @method mascararEmail()
     * @array campos
     */
    public static function mascararEmail(
        string $texto,
        int $mostrarInicio = 2,
        int $mostrarFim = 0,
        string $char = '*',
        bool $mascararDominio = false
    ): string {
        $regex = '/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/';
    
        return preg_replace_callback($regex, function ($matches) use ($mostrarInicio, $mostrarFim, $char, $mascararDominio) {
            return self::isEmail($matches[0], $mostrarInicio, $mostrarFim, $char, $mascararDominio);
        }, $texto);
    }
    
    /**
     * @method mascararNome()
     * @array campos
     */
    public static function mascararNome(
        string $nome,
        int $mostrarInicio = 2,
        int $mostrarFim = 2,
        string $charMascara = '*'
    ): string {
    
        $partes = preg_split('/\s+/', trim(mb_strtoupper($nome)));
        $quantidade = count($partes);
    
        if ($quantidade === 0) {
            return $nome;
        }
    
        $resultado = [];
    
        foreach ($partes as $indice => $parte) {
            $tamanho = mb_strlen($parte);
    
            if ($indice === 0) {
                // Primeiro nome: mostra só o início
                $inicio = mb_substr($parte, 0, min($mostrarInicio, $tamanho));
                $resultado[] = $inicio . str_repeat($charMascara, max(0, $tamanho - mb_strlen($inicio)));
            } elseif ($indice === $quantidade - 1) {
                // Último sobrenome: mostra só o fim
                $fim = mb_substr($parte, -min($mostrarFim, $tamanho));
                $resultado[] = str_repeat($charMascara, max(0, $tamanho - mb_strlen($fim))) . $fim;
            } else {
                // Nomes do meio: mascara tudo
                $resultado[] = str_repeat($charMascara, $tamanho);
            }
        }
    
        return implode(' ', $resultado);
    }
    
    /**
     * @method validarCPF()
     * @array campos
     */
    private static function isCPF(
        string $cpf,
        int $mostrarInicio = 2,
        int $mostrarFim = 0,
        bool $validar = true,
        string $char = '*'
    ): string {
        $numerico = preg_replace('/\D/', '', $cpf);
    
        if ($validar && !self::validarCPF($numerico)) {
            return $cpf;
        }
    
        $mascarado = self::mascararValor($numerico, $mostrarInicio, $mostrarFim, $char);
    
        if (str_contains($cpf, '.')) {
            return substr($mascarado, 0, 3) . '.' .
                   substr($mascarado, 3, 3) . '.' .
                   substr($mascarado, 6, 3) . '-' .
                   substr($mascarado, 9, 2);
        }
    
        return $mascarado;
    }
    
    /**
     * @method mascararEmail()
     * @array campos
     */
    private static function isEmail(
        string $email,
        int $mostrarInicio = 2,
        int $mostrarFim = 2,
        string $char = '*',
        bool $mascararDominio = false
    ): string {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }
    
        [$usuario, $dominio] = explode('@', $email, 2);
    
        $usuarioMascarado = self::mascararValor($usuario, $mostrarInicio, $mostrarFim, $char);
    
        $partesDominio = explode('.', $dominio);
        $dominioBase = array_shift($partesDominio);
    
        $dominioMascarado = self::mascararValor($dominioBase, $mostrarInicio, $mostrarFim, $char);
        
        $dominioFinal = ($mascararDominio === true) ? $dominioMascarado : $dominioBase;
        
        return $usuarioMascarado . '@' . $dominioFinal . '.' . implode('.', $partesDominio);
    }
    
    /**
     * @method validarCPF()
     * @array campos
     */
    private static function validarCPF(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);
    
        if (strlen($cpf) !== 11 || preg_match('/^(\\d)\\1{10}$/', $cpf)) {
            return false;
        }
    
        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($i = 0; $i < $t; $i++) {
                $soma += $cpf[$i] * (($t + 1) - $i);
            }
            $digito = ((10 * $soma) % 11) % 10;
            if ($cpf[$t] != $digito) {
                return false;
            }
        }
    
        return true;
    }
    
    /**
     * @method mascararValor()
     * @array campos
     */
    private static function mascararValor(
        string $valor,
        int $mostrarInicio = 2,
        int $mostrarFim = 2,
        string $charMascara = '*'
    ): string {
        $tamanho = mb_strlen($valor);

        if ($tamanho === 0) {
            return $valor;
        }
    
        // Evita overflow
        if (($mostrarInicio + $mostrarFim) >= $tamanho) {
            return $valor;
        }
    
        $inicio = $mostrarInicio > 0
            ? mb_substr($valor, 0, $mostrarInicio)
            : '';
    
        $fim = $mostrarFim > 0
            ? mb_substr($valor, -$mostrarFim)
            : '';
    
        $qtdMascara = $tamanho - ($mostrarInicio + $mostrarFim);
    
        return $inicio
            . str_repeat($charMascara, $qtdMascara)
            . $fim;
    }
}
