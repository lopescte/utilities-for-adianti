<?php
namespace Lopescte\UtilitiesForAdianti\Traits;

use Lopescte\UtilitiesForAdianti\Services\LgpdRbacService;
use Lopescte\UtilitiesForAdianti\Services\LgpdMaskService;
use Lopescte\UtilitiesForAdianti\Services\LgpdAuditService;
use Lopescte\UtilitiesForAdianti\Services\LgpdSignatureService;

/**
 * Trait LgpdDatagridTrait
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
trait LgpdDatagridTrait
{
    protected array $lgpdCampos = [];
    protected bool $perfilRbac = FALSE;
    protected bool $auditLog = FALSE;

    /**
     * @method definirCamposLgpd()
     * @array campos
     */
    protected function definirCamposLgpd(array $campos)
    {
        $this->lgpdCampos = $campos;
    }

    /**
     * Definir o perfil RBAC
     * @method setPerfilRbac()
     * @description Ativa a validação de perfil RBAC
     */
    protected function setPerfilRbac()
    {
        $this->perfilRbac = TRUE;
    }
    
    /**
     * @method setAuditLog()
     * @description Ativa o log de auditoria
     */
    protected function setAuditLog()
    {
        $this->auditLog = TRUE;
    }
    
    /**
     * @method aplicarLgpd()
     * @description Aplica o mascaramento LGPD no Datagrid
     */
    protected function aplicarLgpd($datagrid)
    {
        foreach ($datagrid->getColumns() as $col) {
            $campo = $col->getName();
            if (!isset($this->lgpdCampos[$campo])) {
                continue;
            }
            $cfg = $this->lgpdCampos[$campo];
            $col->setTransformer(function ($valor) use ($campo, $cfg) {
                if($this->perfilRbac === TRUE)
                {
                    if (LgpdRbacService::podeVerCompleto($campo)) {
                        return $valor;
                    }
                }

                $mascarado = LgpdMaskService::aplicarTodas($valor, $cfg['inicio'] ?? 2, $cfg['fim'] ?? 2, $cfg['char'] ?? '*');
                
                if($this->auditLog === TRUE)
                {
                    LgpdAuditService::registrar($campo, $cfg['tipo'], $valor, $mascarado, 'datagrid');
                }
                return $mascarado;
            });
        }
    }
}
