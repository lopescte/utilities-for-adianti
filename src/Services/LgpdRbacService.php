<?php
namespace Lopescte\UtilitiesForAdianti\Services;

use Adianti\Registry\TSession;

/**
 * Class LgpdRbacService
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
class LgpdRbacService
{
    /**
     * @method podeVerCompleto()
     * @description Verifica se o perfil atual é isento do mascaramento
     */
    public static function podeVerCompleto(string $campo): bool
    {
        $perfil = TSession::getValue('userroles');        
        return in_array($perfil, ['ADMIN', 'DPO']);
    }
}
