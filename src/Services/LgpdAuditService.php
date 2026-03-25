<?php
namespace Lopescte\UtilitiesForAdianti\Services;

use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;

/**
 * Class LgpdAuditService
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
class LgpdAuditService
{
    /**
     * @method registrar()
     * @description Registra o log de auditoria
     */
    public static function registrar(
        string $campo,
        string $tipo,
        string $valorOriginal,
        string $valorMascarado,
        string $origem
    ) {
        TTransaction::open('log');

        $ultimo = LgpdAuditLog::last();

        $assinatura = LgpdSignatureService::assinar(
            ['campo'=>$campo,'tipo'=>$tipo,'usuario'=>TSession::getValue('userid'),'data'=>date('c')],
            $ultimo?->assinatura
        );

        $log = new LgpdAuditLog;
        $log->usuario_id = TSession::getValue('userid');
        $log->perfil = TSession::getValue('role');
        $log->campo = $campo;
        $log->tipo_dado = $tipo;
        $log->hash_valor = hash('sha256', $valorOriginal);
        $log->valor_mascarado = $valorMascarado;
        $log->origem = $origem;
        $log->assinatura = $assinatura;
        $log->store();

        TTransaction::close();
    }
}
