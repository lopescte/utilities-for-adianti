<?php
namespace Lopescte\UtilitiesForAdianti\SqlException;

use Adianti\Database\TTransaction;
use PDOException;
use Throwable;

class SqlExceptionHandler
{
    public static function handle(
        Throwable $e,
        ?string $jsonFile = null,
        ?string $locale = null,
        $form = null
    ): void
    {
        try {
            if (TTransaction::get()) {
                TTransaction::rollback();
            }
        } catch (Throwable $rollbackException) {
        }

        $message = self::getMessage($e, $jsonFile, $locale, $form);

        try {
            $details = SqlErrorCatalog::getTechnicalDetails($e);
            error_log('[SQL_EXCEPTION] ' . json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } catch (Throwable $logException) {
            error_log($e->getMessage());
        }

        new TMessage('error', $message);
    }

    public static function getMessage(
        Throwable $e,
        ?string $jsonFile = null,
        ?string $locale = null,
        $form = null
    ): string
    {
        // Se for erro de banco  traduz
        if ($e instanceof PDOException) {
            return SqlErrorCatalog::getMessage($e, $locale, null, $jsonFile, $form);
        }
    
        // Se tiver errorInfo (alguns drivers encapsulam)
        if (property_exists($e, 'errorInfo')) {
            return SqlErrorCatalog::getMessage($e, $locale, null, $jsonFile, $form);
        }
    
        // Caso contrário  mantém mensagem original
        return $e->getMessage();
    }
}