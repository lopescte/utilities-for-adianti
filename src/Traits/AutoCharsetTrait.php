<?php
namespace Lopescte\UtilitiesForAdianti\Traits;

use Exception;
use InvalidArgumentException;

/**
 * Trait AdiantiCharsetTrait
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
trait AutoCharsetTrait
{
    protected string $charset = 'utf8';

    protected function getFieldLabel(string $field): string
    {
        if (isset($this->form)) {
            $fields = $this->form->getFields();
            
            if (isset($fields[$field])) {
                return $fields[$field]->getLabel() ?? $field;
            }
        }

        return $field; // fallback
    }
    
    protected function validateCharset(array $data, array $ignore = []): void
    {
        foreach ($data as $field => $value) {

            if (
                in_array($field, $ignore, true) ||
                !is_string($value)
            ) {
                continue;
            }

            if (self::hasInvalidChars($value, $this->charset)) {
                
                $label = $this->getFieldLabel($field);
                
                throw new Exception(
                    "O campo <b>*{$label}*</b> contém caracteres não suportados pelo banco de dados."
                );
            }
        }
    }
    
    public static function hasInvalidChars(string $text, string $charset): bool
    {
        // UTF-8 inválido de origem
        if (!mb_check_encoding($text, 'UTF-8')) {
            return true;
        }

        // Caracteres de controle (sempre inválidos)
        if (preg_match('/[\x{0000}-\x{0008}\x{000B}\x{000C}\x{000E}-\x{001F}\x{007F}]/u', $text)) {
            return true;
        }

        switch (strtolower($charset)) {

            case 'latin1':
                // Qualquer caractere fora do Latin-1
                return preg_match('/[^\x{0000}-\x{00FF}]/u', $text);

            case 'utf8':
                // Bloqueia caracteres 4 bytes (U+10000+)
                return preg_match('/[\x{10000}-\x{10FFFF}]/u', $text);

            case 'utf8mb4':
                // Unicode completo permitido
                return false;

            default:
                throw new InvalidArgumentException("Charset <b>*{$charset}*</b> não suportado.");
        }
    }
}
