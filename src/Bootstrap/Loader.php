<?php

namespace Lopescte\UtilitiesForAdianti\Bootstrap;

class Loader
{
    public static function register(): void
    {
        self::aliases();
    }

    protected static function aliases(): void
    {
        $map = [
            'LgpdMaskService'                  => \Lopescte\UtilitiesForAdianti\Services\LgpdMaskService::class,
            'LgpdAuditService'                 => \Lopescte\UtilitiesForAdianti\Services\LgpdAuditService::class,
            'LgpdRbacService'                  => \Lopescte\UtilitiesForAdianti\Services\LgpdRbacService::class,
            'LgpdSignatureService'             => \Lopescte\UtilitiesForAdianti\Services\LgpdSignatureService::class,
            'UniqueFilenameUploaderService'    => \Lopescte\UtilitiesForAdianti\Services\UniqueFilenameUploaderService::class,
            'HashFilenameUploaderService'      => \Lopescte\UtilitiesForAdianti\Services\HashFilenameUploaderService::class,
            'LgpdDatagridTrait'                => \Lopescte\UtilitiesForAdianti\Traits\LgpdDatagridTrait::class,
            'AutoFormConstraintsTrait'         => \Lopescte\UtilitiesForAdianti\Traits\AutoFormConstraintsTrait::class,
            'AutoCharsetTrait'                 => \Lopescte\UtilitiesForAdianti\Traits\AutoCharsetTrait::class,
            'S3FileSaveTrait'                  => \Lopescte\UtilitiesForAdianti\Traits\S3FileSaveTrait::class,
            'TBreadCrumbWithLink'              => \Lopescte\UtilitiesForAdianti\Util\TBreadCrumbWithLink::class,
            'SqlExceptionHandler'              => \Lopescte\UtilitiesForAdianti\SqlException\SqlExceptionHandler::class
        ];

        foreach ($map as $alias => $fqcn) {    
            if (!class_exists($alias, false) 
                && !trait_exists($alias, false)
                && (class_exists($fqcn) || trait_exists($fqcn))) 
            {
                class_alias($fqcn, $alias);
            }
        }
    }
}
