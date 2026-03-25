<?php
namespace Lopescte\UtilitiesForAdianti\Traits;

use Adianti\Database\TRecord;
use Adianti\Database\TTransaction;
use Adianti\Validator\TMaxLengthValidator;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TField;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Form\TText;
use Adianti\Widget\Form\THtmlEditor;
use Adianti\Widget\Base\TScript;
use Adianti\Wrapper\BootstrapFormBuilder;

use PDO;
use Exception;
use InvalidArgumentException;

/**
 * Trait AdiantiAutoFormConstraintsTrait
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
trait AutoFormConstraintsTrait
{
    protected static array $schemaCache = [];

    protected function normalizeForm($form): TForm|BootstrapFormBuilder
    {
        if (!is_object($form)) {
            throw new InvalidArgumentException('Objeto de formulário inválido');
        }
    
        if (!method_exists($form, 'getFields')) {
            throw new InvalidArgumentException('O formulário informado não possui o método getFields()');
        }
    
        return $form;
    }
    
    protected function getFormFields($form): array
    {
        $form = $this->normalizeForm($form);
    
        $fields = $form->getFields();
    
        return is_array($fields) ? $fields : [];
    }

    protected function resolveTableName(string $tableOrRecordClass): string
    {
        if (class_exists($tableOrRecordClass) && is_subclass_of($tableOrRecordClass, TRecord::class)) {
            if (!defined($tableOrRecordClass . '::TABLENAME')) {
                throw new Exception("A classe {$tableOrRecordClass} não possui a constante TABLENAME");
            }

            return constant($tableOrRecordClass . '::TABLENAME');
        }

        return $tableOrRecordClass;
    }

    protected function resolvePrimaryKey(string $tableOrRecordClass): ?string
    {
        if (class_exists($tableOrRecordClass) && is_subclass_of($tableOrRecordClass, TRecord::class)) {
            if (defined($tableOrRecordClass . '::PRIMARYKEY')) {
                return constant($tableOrRecordClass . '::PRIMARYKEY');
            }
        }

        return null;
    }

    protected function getTableSchemaMeta(
        string $tableOrRecordClass,
        ?string $schema = null,
        ?string $database = null,
        int $cacheTtl
    ): array
    {
        $table = $this->resolveTableName($tableOrRecordClass);
    
        $cacheKey = $this->getSchemaCacheKey($table, $schema, $database);

        if (isset(self::$schemaCache[$cacheKey])) {
            return self::$schemaCache[$cacheKey];
        }
        
        if ($cached = $this->getPersistentSchemaCache($cacheKey, $cacheTtl)) {
            return self::$schemaCache[$cacheKey] = $cached;
        }
    
        $openedHere = false;
    
        if (!TTransaction::get()) {
            if (empty($database)) {
                throw new Exception(
                    'Nenhuma transação está aberta e nenhuma conexão foi informada em $database'
                );
            }
    
            TTransaction::openFake($database);
            $openedHere = true;
        }
    
        try {
            $conn = TTransaction::get();
    
            if (!$conn) {
                throw new Exception('Não foi possível obter conexão ativa com o banco');
            }
    
            $driver = $conn->getAttribute(PDO::ATTR_DRIVER_NAME);
    
            switch ($driver) {
                case 'mysql':
                    $sql = "
                        SELECT
                            c.COLUMN_NAME,
                            c.DATA_TYPE,
                            c.CHARACTER_MAXIMUM_LENGTH,
                            c.IS_NULLABLE,
                            c.COLUMN_DEFAULT,
                            c.EXTRA,
                            CASE
                                WHEN k.COLUMN_NAME IS NOT NULL THEN 1
                                ELSE 0
                            END AS IS_PRIMARY
                        FROM INFORMATION_SCHEMA.COLUMNS c
                        LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
                               ON k.TABLE_SCHEMA = c.TABLE_SCHEMA
                              AND k.TABLE_NAME = c.TABLE_NAME
                              AND k.COLUMN_NAME = c.COLUMN_NAME
                              AND k.CONSTRAINT_NAME = 'PRIMARY'
                        WHERE c.TABLE_SCHEMA = DATABASE()
                          AND c.TABLE_NAME = ?
                    ";
                    $params = [$table];
                    break;
    
                case 'pgsql':
                    $sql = "
                        SELECT
                            c.column_name AS COLUMN_NAME,
                            c.data_type AS DATA_TYPE,
                            c.character_maximum_length AS CHARACTER_MAXIMUM_LENGTH,
                            c.is_nullable AS IS_NULLABLE,
                            c.column_default AS COLUMN_DEFAULT,
                            '' AS EXTRA,
                            CASE
                                WHEN tc.constraint_type = 'PRIMARY KEY' THEN 1
                                ELSE 0
                            END AS IS_PRIMARY
                        FROM information_schema.columns c
                        LEFT JOIN information_schema.key_column_usage kcu
                               ON kcu.table_schema = c.table_schema
                              AND kcu.table_name = c.table_name
                              AND kcu.column_name = c.column_name
                        LEFT JOIN information_schema.table_constraints tc
                               ON tc.table_schema = kcu.table_schema
                              AND tc.table_name = kcu.table_name
                              AND tc.constraint_name = kcu.constraint_name
                        WHERE c.table_name = ?
                          AND c.table_schema = COALESCE(?, current_schema())
                    ";
                    $params = [$table, $schema];
                    break;
    
                case 'sqlsrv':
                    $sql = "
                        SELECT
                            c.COLUMN_NAME,
                            c.DATA_TYPE,
                            c.CHARACTER_MAXIMUM_LENGTH,
                            c.IS_NULLABLE,
                            c.COLUMN_DEFAULT,
                            CASE
                                WHEN COLUMNPROPERTY(
                                    OBJECT_ID(c.TABLE_SCHEMA + '.' + c.TABLE_NAME),
                                    c.COLUMN_NAME,
                                    'IsIdentity'
                                ) = 1 THEN 'identity'
                                ELSE ''
                            END AS EXTRA,
                            CASE
                                WHEN EXISTS (
                                    SELECT 1
                                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
                                    INNER JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
                                            ON tc.CONSTRAINT_NAME = k.CONSTRAINT_NAME
                                           AND tc.TABLE_SCHEMA = k.TABLE_SCHEMA
                                           AND tc.TABLE_NAME = k.TABLE_NAME
                                    WHERE tc.CONSTRAINT_TYPE = 'PRIMARY KEY'
                                      AND k.TABLE_NAME = c.TABLE_NAME
                                      AND k.COLUMN_NAME = c.COLUMN_NAME
                                      AND k.TABLE_SCHEMA = c.TABLE_SCHEMA
                                ) THEN 1
                                ELSE 0
                            END AS IS_PRIMARY
                        FROM INFORMATION_SCHEMA.COLUMNS c
                        WHERE c.TABLE_NAME = ?
                    ";
    
                    if ($schema) {
                        $sql .= " AND c.TABLE_SCHEMA = ?";
                        $params = [$table, $schema];
                    } else {
                        $params = [$table];
                    }
                    break;
    
                default:
                    throw new Exception("Driver não suportado: {$driver}");
            }
    
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
    
            $meta = [];
    
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $name = $row['COLUMN_NAME'];
    
                $meta[$name] = [
                    'name'       => $name,
                    'type'       => strtolower((string) ($row['DATA_TYPE'] ?? '')),
                    'max_length' => isset($row['CHARACTER_MAXIMUM_LENGTH']) ? (int) $row['CHARACTER_MAXIMUM_LENGTH'] : null,
                    'nullable'   => strtoupper((string) ($row['IS_NULLABLE'] ?? 'YES')) === 'YES',
                    'default'    => $row['COLUMN_DEFAULT'] ?? null,
                    'extra'      => strtolower((string) ($row['EXTRA'] ?? '')),
                    'primary'    => !empty($row['IS_PRIMARY']),
                ];
            }
    
            self::$schemaCache[$cacheKey] = $meta;
            $this->setPersistentSchemaCache($cacheKey, $meta);
            
            return $meta;
        }
        finally {
            if ($openedHere) {
                TTransaction::close();
            }
        }
    }

    protected function isTextualColumn(array $columnMeta): bool
    {
        $type = $columnMeta['type'] ?? '';

        return in_array($type, [
            'char',
            'varchar',
            'tinytext',
            'text',
            'mediumtext',
            'longtext',
            'character varying',
            'character',
            'nvarchar',
            'nchar',
        ], true);
    }

    protected function isAutoGeneratedColumn(array $columnMeta): bool
    {
        $extra   = strtolower((string) ($columnMeta['extra'] ?? ''));
        $default = strtolower((string) ($columnMeta['default'] ?? ''));

        if (str_contains($extra, 'auto_increment') || str_contains($extra, 'identity')) {
            return true;
        }

        if (str_contains($default, 'nextval(') || str_contains($default, 'identity')) {
            return true;
        }

        return false;
    }

    protected function isSystemGeneratedDefault(array $columnMeta): bool
    {
        $default = strtolower(trim((string) ($columnMeta['default'] ?? '')));

        if ($default === '') {
            return false;
        }

        return
            str_contains($default, 'current_timestamp') ||
            str_contains($default, 'getdate()') ||
            str_contains($default, 'sysdatetime()') ||
            str_contains($default, 'now()');
    }

    protected function isRequiredColumn(array $columnMeta): bool
    {
        $nullable = (bool) ($columnMeta['nullable'] ?? true);
        $default  = $columnMeta['default'] ?? null;
        $primary  = (bool) ($columnMeta['primary'] ?? false);

        if ($primary) {
            return false;
        }

        if ($this->isAutoGeneratedColumn($columnMeta)) {
            return false;
        }

        if ($this->isSystemGeneratedDefault($columnMeta)) {
            return false;
        }

        return !$nullable && $default === null;
    }

    protected function isFieldEditable(TField $field): bool
    {
        if (method_exists($field, 'getEditable')) {
            return (bool) $field->getEditable();
        }

        if (property_exists($field, 'editable')) {
            return (bool) $field->editable;
        }

        return true;
    }

    protected function isFieldHidden(TField $field): bool
    {
        return $field instanceof THidden;
    }

    protected function shouldIgnoreFieldAutomatically(
        TField $field,
        string $name,
        array $columnMeta,
        ?string $recordPrimaryKey = null,
        bool $ignoreNonEditable = true,
        bool $ignoreHidden = true,
        bool $ignorePrimaryKey = true
    ): bool {
        if ($ignoreHidden && $this->isFieldHidden($field)) {
            return true;
        }

        if ($ignoreNonEditable && !$this->isFieldEditable($field)) {
            return true;
        }

        if ($ignorePrimaryKey) {
            if (!empty($columnMeta['primary'])) {
                return true;
            }

            if ($recordPrimaryKey && $name === $recordPrimaryKey) {
                return true;
            }
        }

        return false;
    }

    protected function buildDefaultLabel(string $fieldName): string
    {
        return ucfirst(str_replace('_', ' ', $fieldName));
    }

    protected function resolveFieldLabel(
        TField $field,
        string $fieldName,
        array $labels = [],
        bool $setFieldLabelWhenMissing = false
    ): string {
        $label = $labels[$fieldName] ?? null;

        if (!$label && method_exists($field, 'getLabel')) {
            $label = $field->getLabel();
        }

        if (!$label) {
            $label = $this->buildDefaultLabel($fieldName);
        }

        if ($setFieldLabelWhenMissing && method_exists($field, 'setLabel')) {
            $current = method_exists($field, 'getLabel') ? $field->getLabel() : null;
            if (empty($current)) {
                $field->setLabel($label);
            }
        }

        return $label;
    }
    
    protected function applyRequiredVisualMarkers(array $fieldNames, bool $markRequiredLabel): void
    {
        if (!$markRequiredLabel) {
            return;
        }
        
        if (empty($fieldNames)) {
            return;
        }
    
        $jsonFields = json_encode(array_values(array_unique($fieldNames)), JSON_UNESCAPED_UNICODE);
    
        TScript::create("setTimeout(function() {
            
            function isFieldDisabled(input) {
                if (!input) return true;
        
                if (input.disabled || input.readOnly) return true;
        
                if (
                    input.classList.contains('tcombo_disabled') ||
                    input.classList.contains('input_disabled') ||
                    input.classList.contains('tfield_disabled')
                ) {
                    return true;
                }
        
                let parent = input;
        
                while (parent) {
                    if (
                        parent.classList &&
                        (
                            parent.classList.contains('tcombo_disabled') ||
                            parent.classList.contains('input_disabled') ||
                            parent.classList.contains('tfield_disabled')
                        )
                    ) {
                        return true;
                    }
        
                    parent = parent.parentElement;
                }
        
                return false;
            }
            
            const fields = {$jsonFields};
        
            fields.forEach(function(fieldName) {
                const input = document.querySelector('[name=\"' + fieldName + '\"]');
                if (!input || isFieldDisabled(input)) {
                    return;
                }
        
                let label = null;
        
                if (input.id) {
                    label = document.querySelector('label[for=\"' + input.id + '\"]');
                }
        
                let group =
                    input.closest('.form-group') ||
                    input.closest('[class*=\"col-sm-\"]') ||
                    input.closest('[class*=\"col-md-\"]') ||
                    input.closest('[class*=\"col-lg-\"]') ||
                    input.closest('[class*=\"col-xl-\"]') ||
                    input.parentElement;
        
                if (!label && group) {
                    const labels = group.querySelectorAll('label, .control-label');
        
                    if (labels.length === 1) {
                        label = labels[0];
                    } else if (labels.length > 1) {
                        label = Array.from(labels).sort(function(a, b) {
                            const aRect = a.getBoundingClientRect();
                            const bRect = b.getBoundingClientRect();
                            const inputRect = input.getBoundingClientRect();
                    
                            const da = Math.abs(aRect.top - inputRect.top) + Math.abs(aRect.left - inputRect.left);
                            const db = Math.abs(bRect.top - inputRect.top) + Math.abs(bRect.left - inputRect.left);
                    
                            return da - db;
                        })[0];
                    }
                }
        
                if (label && !label.dataset.requiredMarked) {
                    label.insertAdjacentHTML('beforeend', ' <span style=\"color:red\">*</span>');
                    /*label.style.color = 'red';*/
                    label.dataset.requiredMarked = '1';
                }
        
                input.classList.add('adianti-auto-required');
        
                if (group) {
                    group.classList.add('adianti-auto-required-group');
                }
            });
        }, 0);");
    }
    
    protected function appendRequiredFooter($form)
    {
        $formName = $form->getName();
        
        TScript::create("
        setTimeout(function() {
            const form = document.querySelector('form[name=\"{$formName}\"]');
        
            if (!form) return;
            
            const panelBody = form.querySelector('.panel-body');
            if (!panelBody) return;
    
            if (panelBody.querySelector('.adianti-required-footer')) return;
        
            const div = document.createElement('div');
            div.id = 'adianti-required-footer';
            div.style.color = 'red';
            div.style.padding = '10px';
        
            div.innerText = 'Campos marcados com (*) são obrigatórios.';
        
            panelBody.appendChild(div);
        }, 0);
        ");
    }

    protected function applyFieldMaxLength(TField $field, int $maxLength): void
    {
        if ($maxLength <= 0) {
            return;
        }
    
        // TEntry: tentar usar o método do widget
        if ($field instanceof TEntry) {
            try {
                $field->setMaxLength((int) $maxLength);
            } catch (\Throwable $e) {
                $field->setProperty('maxlength', (int) $maxLength);
            }
            return;
        }
    
        // TText: mais seguro aplicar direto como propriedade HTML
        if ($field instanceof TText) {
            $field->setProperty('maxlength', (int) $maxLength);
        }
    }

    protected function applyFieldMaxLengthValidator(TField $field, string $label, int $maxLength): void
    {
        if ($maxLength <= 0) {
            return;
        }
    
        if ($field instanceof TEntry || $field instanceof TText || $field instanceof THtmlEditor) {
            $field->addValidation($label, new TMaxLengthValidator, [(int) $maxLength]);
        }
    }

    protected function applyFieldRequiredValidator(TField $field, string $label, bool $setHtmlRequired = true): void
    {
        $field->addValidation($label, new TRequiredValidator());

        if ($setHtmlRequired) {
            $field->setProperty('required', 'required');
        }
    }

    protected function applyAutoConstraintsToForm(
        $form,
        string $tableOrRecordClass,
        array $labels = [],
        array $ignoreRequired = [],
        array $ignoreMaxLength = [],
        array $ignoreMaxLengthValidator = [],
        ?string $schema = null,
        ?string $database = null,
        bool $setHtmlRequired = true,
        bool $applyMaxLengthHtml = true,
        bool $applyMaxLengthValidator = true,
        bool $ignoreNonEditable = true,
        bool $ignoreHidden = true,
        bool $ignorePrimaryKey = true,
        bool $markRequiredLabel = true,
        bool $setFieldLabelWhenMissing = true,
        int $cacheTtl = 86400
    ): void {
        $form             = $this->normalizeForm($form);
        $meta             = $this->getTableSchemaMeta($tableOrRecordClass, $schema, $database, $cacheTtl);
        $recordPrimaryKey = $this->resolvePrimaryKey($tableOrRecordClass);

        $requiredFieldNames = [];
        
        foreach ($this->getFormFields($form) as $field) {
            if (!($field instanceof TField)) {
                continue;
            }

            $name = $field->getName();

            if (!$name || !isset($meta[$name])) {
                continue;
            }

            $columnMeta = $meta[$name];

            if (
                $this->shouldIgnoreFieldAutomatically(
                    field: $field,
                    name: $name,
                    columnMeta: $columnMeta,
                    recordPrimaryKey: $recordPrimaryKey,
                    ignoreNonEditable: $ignoreNonEditable,
                    ignoreHidden: $ignoreHidden,
                    ignorePrimaryKey: $ignorePrimaryKey
                )
            ) {
                continue;
            }

            $baseLabel = $this->resolveFieldLabel(
                field: $field,
                fieldName: $name,
                labels: $labels,
                setFieldLabelWhenMissing: $setFieldLabelWhenMissing
            );

            // required
            if (!in_array($name, $ignoreRequired, true) && $this->isRequiredColumn($columnMeta)) {
                $this->applyFieldRequiredValidator($field, $baseLabel, $setHtmlRequired);
                $requiredFieldNames[] = $name;
            }

            // maxlength
            if ($this->isTextualColumn($columnMeta) && !empty($columnMeta['max_length']) && (int) $columnMeta['max_length'] > 0) {
                $maxLength = (int) $columnMeta['max_length'];

                if ($applyMaxLengthHtml && !in_array($name, $ignoreMaxLength, true)) {
                    $this->applyFieldMaxLength($field, $maxLength);
                }

                if ($applyMaxLengthValidator && !in_array($name, $ignoreMaxLengthValidator, true)) {
                    $this->applyFieldMaxLengthValidator($field, $baseLabel, $maxLength);
                }
            }
        }
        if(!empty($requiredFieldNames) && $markRequiredLabel === true){
            $this->applyRequiredVisualMarkers($requiredFieldNames, $markRequiredLabel);
        }
        
        if($markRequiredLabel === true){
            $this->appendRequiredFooter($form);
        }
    }

    protected function clearAutoFormConstraintsCache(?string $tableOrRecordClass = null, ?string $schema = null): void
    {
        if ($tableOrRecordClass === null) {
            self::$schemaCache = [];
            return;
        }

        $table = $this->resolveTableName($tableOrRecordClass);
        $cacheKey = ($schema ?: '_default') . '.' . $table;

        unset(self::$schemaCache[$cacheKey]);
    }
    
    protected function getSchemaCacheKey(string $table, ?string $schema = null, ?string $database = null): string
    {
        return md5(($database ?: '_current') . '|' . ($schema ?: '_default') . '|' . $table);
    }
    
    protected function getSchemaCacheFile(string $cacheKey): string
    {
        $dir = 'app/database/cache';
    
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
    
        return $dir . '/schema_' . $cacheKey . '.php';
    }
    
    protected function getPersistentSchemaCache(string $cacheKey, int $ttl = 3600): ?array
    {
        $file = $this->getSchemaCacheFile($cacheKey);
    
        if (!file_exists($file)) {
            return null;
        }
    
        if ((time() - filemtime($file)) > $ttl) {
            @unlink($file);
            return null;
        }
    
        $data = include $file;
    
        return is_array($data) ? $data : null;
    }
    
    protected function setPersistentSchemaCache(string $cacheKey, array $data): void
    {
        $file = $this->getSchemaCacheFile($cacheKey);
    
        $export = var_export($data, true);
        file_put_contents($file, "<?php\nreturn {$export};\n");
    }
}