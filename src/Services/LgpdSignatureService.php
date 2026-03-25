<?php
namespace Lopescte\UtilitiesForAdianti\Services;

/**
 * Class LgpdSignatureService
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
class LgpdSignatureService
{
    /**
     * @method assinar()
     * @description Assina o log
     */
    public static function assinar(array $dados, ?string $hashAnterior): string
    {
        return hash('sha512', json_encode($dados) . $hashAnterior);
    }
}
