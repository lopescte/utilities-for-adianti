<?php
namespace Lopescte\UtilitiesForAdianti\SqlException;

use Adianti\Core\AdiantiCoreTranslator;
use Adianti\Database\TTransaction;

use PDO;
use PDOException;
use RuntimeException;
use Throwable;

class SqlErrorCatalog
{
    protected static ?array $catalog = null;

    public static function getMessage(
        Throwable $e, 
        ?string $locale = null, 
        ?string $driver = null, 
        ?string $jsonFile = null,
        $form = null
    ): string
    {
        $catalog = self::loadCatalog($jsonFile);

        $locale = self::normalizeLocale($locale ?: self::detectLocale($catalog));
        $driver = self::normalizeDriverName($driver ?: self::detectDriver());

        $context = self::extractContext($e);
        $context = self::resolveFieldLabel($context, $form);
        
        $template = self::findTemplate($catalog, $driver, $locale, $context);

        return self::replacePlaceholders($template, $context);
    }

    public static function getTechnicalDetails(Throwable $e, ?string $driver = null): array
    {
        $driver = $driver ?: self::detectDriver();

        $details = [
            'exception_class' => get_class($e),
            'driver'          => $driver,
            'message'         => $e->getMessage(),
            'file'            => $e->getFile(),
            'line'            => $e->getLine(),
        ];

        if ($e instanceof PDOException) {
            $errorInfo = $e->errorInfo ?? [];
            $details['sqlstate']     = isset($errorInfo[0]) ? (string) $errorInfo[0] : null;
            $details['driver_code']  = isset($errorInfo[1]) ? (string) $errorInfo[1] : null;
            $details['driver_error'] = isset($errorInfo[2]) ? (string) $errorInfo[2] : null;
        }

        return $details;
    }
    
    protected static function resolveFieldLabel(array $context, $form = null): array
    {
        if (empty($context['field']) || !$form) {
            return $context;
        }

        $fieldName = $context['field'];

        $label = self::tryGetFieldLabel($form, $fieldName);

        if (!empty($label)) {
            $context['field'] = $label;
        }

        return $context;
    }
    
    protected static function tryGetFieldLabel($form, string $fieldName): ?string
    {
        try {
            if (!method_exists($form, 'getField')) {
                return null;
            }

            $field = $form->getField($fieldName);

            if (!$field) {
                return null;
            }

            if (method_exists($field, 'getLabel')) {
                $label = $field->getLabel();

                if (!empty($label)) {
                    return trim(strip_tags((string) $label));
                }
            }

            if (property_exists($field, 'label') && !empty($field->label)) {
                return trim(strip_tags((string) $field->label));
            }
        } catch (\Throwable $e) {
        }

        return null;
    }

    protected static function findTemplate(array $catalog, string $driver, string $locale, array $context): string
    {
        $drivers = $catalog['drivers'] ?? [];
        $defaultDriver = $catalog['default_driver'] ?? 'default';

        $chains = [];
        if (isset($drivers[$driver])) {
            $chains[] = $drivers[$driver];
        }
        if ($driver !== $defaultDriver && isset($drivers[$defaultDriver])) {
            $chains[] = $drivers[$defaultDriver];
        }

        foreach ($chains as $driverMap) {
            if (!empty($context['driver_code']) && isset($driverMap['driver_codes'][(string) $context['driver_code']])) {
                return self::resolveLocalizedMessage($driverMap['driver_codes'][(string) $context['driver_code']], $locale, $catalog);
            }
        }

        foreach ($chains as $driverMap) {
            if (!empty($context['sqlstate']) && isset($driverMap['sqlstate'][(string) $context['sqlstate']])) {
                return self::resolveLocalizedMessage($driverMap['sqlstate'][(string) $context['sqlstate']], $locale, $catalog);
            }
        }

        $messageBase = mb_strtolower(($context['driver_error'] ?? '') . ' ' . ($context['raw_message'] ?? ''));

        foreach ($chains as $driverMap) {
            if (!empty($driverMap['message_contains']) && is_array($driverMap['message_contains'])) {
                foreach ($driverMap['message_contains'] as $needle => $localizedMessage) {
                    if (mb_stripos($messageBase, mb_strtolower($needle)) !== false) {
                        return self::resolveLocalizedMessage($localizedMessage, $locale, $catalog);
                    }
                }
            }
        }

        return self::resolveLocalizedMessage(
            $catalog['default_message'] ?? ['pt' => 'Ocorreu um erro ao processar os dados.'],
            $locale,
            $catalog
        );
    }

    protected static function resolveLocalizedMessage($value, string $locale, array $catalog): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (!is_array($value)) {
            return 'Ocorreu um erro ao processar os dados.';
        }

        if (isset($value[$locale])) {
            return (string) $value[$locale];
        }

        $defaultLocale = self::normalizeLocale($catalog['default_locale'] ?? 'pt');

        if (isset($value[$defaultLocale])) {
            return (string) $value[$defaultLocale];
        }

        foreach (['pt', 'en', 'es'] as $fallback) {
            if (isset($value[$fallback])) {
                return (string) $value[$fallback];
            }
        }

        return (string) reset($value);
    }

    protected static function replacePlaceholders(string $template, array $context): string
    {
        $vars = [
            '{field}'             => $context['field'] ?? '',
            '{value}'             => $context['value'] ?? '',
            '{constraint}'        => $context['constraint'] ?? '',
            '{table}'             => $context['table'] ?? '',
            '{column}'            => $context['column'] ?? '',
            '{field_suffix}'      => !empty($context['field']) ? ' para o campo *' . $context['field'] . '*' : '',
            '{value_suffix}'      => !empty($context['value']) ? ' para o valor *' . $context['value'] . '*' : '',
            '{constraint_suffix}' => !empty($context['constraint']) ? ' (' . $context['constraint'] . ')' : '',
            '{table_suffix}'      => !empty($context['table']) ? ' na tabela *' . $context['table'] . '*' : '',
            '{column_suffix}'     => !empty($context['column']) ? ' na coluna *' . $context['column'] . '*' : '',
        ];

        $message = strtr($template, $vars);
        $message = preg_replace('/\s+([,.!?;:])/', '$1', $message);
        $message = preg_replace('/\s{2,}/', ' ', $message);

        return trim($message);
    }

    protected static function extractContext(Throwable $e): array
    {
        $rawMessage = (string) $e->getMessage();
        $driverError = $rawMessage;
        $sqlState = null;
        $driverCode = null;

        if ($e instanceof PDOException) {
            $errorInfo = $e->errorInfo ?? [];
            $sqlState = isset($errorInfo[0]) ? (string) $errorInfo[0] : null;
            $driverCode = isset($errorInfo[1]) ? (string) $errorInfo[1] : null;
            $driverError = isset($errorInfo[2]) ? (string) $errorInfo[2] : $rawMessage;
        }

        $context = [
            'raw_message'  => $rawMessage,
            'driver_error' => $driverError,
            'sqlstate'     => $sqlState,
            'driver_code'  => $driverCode,
            'field'        => null,
            'value'        => null,
            'constraint'   => null,
            'table'        => null,
            'column'       => null,
        ];

        $search = $driverError . ' ' . $rawMessage;

        $patterns = [
            'field' => [
                '/column [\'"`]([^\'"`]+)[\'"`]/i',
                '/field [\'"`]([^\'"`]+)[\'"`]/i',
                '/for column [\'"`]([^\'"`]+)[\'"`]/i',
                '/atributo [\'"`]([^\'"`]+)[\'"`]/i',
            ],
            'value' => [
                '/Duplicate entry [\'"]([^\'"]+)[\'"]/i',
                '/Key \((.*?)\)=\((.*?)\)/i',
                '/duplicate key value is \((.*?)\)/i',
                '/valor \((.*?)\)/i'
            ],
            'constraint' => [
                '/constraint [\'"`]([^\'"`]+)[\'"`]/i',
                '/unique constraint [\'"`]([^\'"`]+)[\'"`]/i',
                '/reference constraint [\'"`]([^\'"`]+)[\'"`]/i'
            ],
            'table' => [
                '/table [\'"`]([^\'"`]+)[\'"`]/i',
                '/relation [\'"`]([^\'"`]+)[\'"`]/i'
            ],
            'column' => [
                '/column [\'"`]([^\'"`]+)[\'"`]/i'
            ],
        ];

        foreach ($patterns as $key => $regexList) {
            foreach ($regexList as $regex) {
                if (preg_match($regex, $search, $m)) {
                    if ($key === 'value' && count($m) >= 3 && !empty($m[2])) {
                        $context[$key] = trim($m[2]);
                    } elseif (!empty($m[1])) {
                        $context[$key] = trim($m[1]);
                    }
                    break;
                }
            }
        }

        return $context;
    }

    public static function detectDriver(): ?string
    {
        try {
            $conn = TTransaction::get();
            if ($conn instanceof PDO) {
                return (string) $conn->getAttribute(PDO::ATTR_DRIVER_NAME);
            }
        } catch (Throwable $e) {
        }

        return null;
    }

    protected static function detectLocale(array $catalog): string
    {
        $defaultLocale = self::normalizeLocale($catalog['default_locale'] ?? 'pt');

        try {
            if (class_exists('AdiantiCoreTranslator')) {
                foreach (['getLanguage', 'getLanguageName', 'getLocale'] as $method) {
                    if (method_exists('AdiantiCoreTranslator', $method)) {
                        $value = AdiantiCoreTranslator::$method();
                        if (!empty($value)) {
                            return self::normalizeLocale((string) $value);
                        }
                    }
                }
            }
        } catch (Throwable $e) {
        }

        if (!empty($_SESSION['lang'])) {
            return self::normalizeLocale((string) $_SESSION['lang']);
        }

        if (!empty($_SESSION['language'])) {
            return self::normalizeLocale((string) $_SESSION['language']);
        }

        return $defaultLocale;
    }

    protected static function normalizeLocale(?string $locale): string
    {
        $locale = strtolower((string) $locale);

        return match (true) {
            str_starts_with($locale, 'pt') => 'pt',
            str_starts_with($locale, 'en') => 'en',
            str_starts_with($locale, 'es') => 'es',
            default => 'pt',
        };
    }

    public static function normalizeDriverName(?string $driver): string
    {
        $driver = strtolower((string) $driver);

        return match ($driver) {
            'mysql', 'mariadb' => 'mysql',
            'pgsql', 'postgres', 'postgresql' => 'pgsql',
            'sqlsrv', 'dblib', 'mssql', 'odbc' => 'sqlsrv',
            default => $driver ?: 'default',
        };
    }

    protected static function loadCatalog(?string $jsonFile = null): array
    {
        if (self::$catalog !== null && $jsonFile === null) {
            return self::$catalog;
        }

        $jsonFile = $jsonFile ?: self::getDefaultJsonPath();

        if (!is_file($jsonFile)) {
            throw new RuntimeException('Arquivo de catálogo SQL não encontrado: ' . $jsonFile);
        }

        $content = file_get_contents($jsonFile);
        if ($content === false) {
            throw new RuntimeException('Não foi possível ler o catálogo SQL: ' . $jsonFile);
        }

        $data = json_decode($content, true);
        if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('JSON inválido em catálogo SQL: ' . json_last_error_msg());
        }

        if ($jsonFile === self::getDefaultJsonPath()) {
            self::$catalog = $data;
        }

        return $data;
    }

    protected static function getDefaultJsonPath(): string
    {
        return __DIR__ . '/../Resources/sql_errors.json';
    }
}