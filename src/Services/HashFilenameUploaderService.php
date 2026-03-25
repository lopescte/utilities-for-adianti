<?php
namespace Lopescte\UtilitiesForAdianti\Services;

use Adianti\Core\AdiantiCoreTranslator;
use Adianti\Core\AdiantiApplicationConfig;
use Adianti\Service\AdiantiUploaderService;

use Exception;

/**
 * Class HashFilenameUploaderService
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
class HashFilenameUploaderService extends AdiantiUploaderService
{
    function show($param)
    {
        $ini  = AdiantiApplicationConfig::get();
        $seed = APPLICATION_NAME . ( !empty($ini['general']['seed']) ? $ini['general']['seed'] : 's8dkld83kf73kf094' );
        $block_extensions = ['php', 'php3', 'php4', 'phtml', 'pl', 'py', 'jsp', 'asp', 'htm', 'shtml', 'sh', 'cgi', 'htaccess'];
        
        $folder = 'tmp/';
        $response = array();
        if (isset($_FILES['fileName']))
        {
            $file = $_FILES['fileName'];
            
            if( $file['error'] === 0 && $file['size'] > 0 )
            {
                $path = $folder.$file['name'];
                
                // check blocked file extension, not using finfo because file.php.2 problem
                foreach ($block_extensions as $block_extension)
                {
                    if (strpos(strtolower($file['name']), ".{$block_extension}") !== false)
                    {
                        $response = array();
                        $response['type'] = 'error';
                        $response['msg']  = AdiantiCoreTranslator::translate('Extension not allowed');
                        echo json_encode($response);
                        return;
                    }
                }
                
                if (!empty($param['extensions']))
                {
                    $name = $param['name'];
                    $extensions = unserialize(base64_decode( $param['extensions']), ['allowed_classes' => false]);
                    $hash = md5("{$seed}{$name}".base64_encode(serialize($extensions)));
                    
                    if ($hash !== $param['hash'])
                    {
                        $response = array();
                        $response['type'] = 'error';
                        $response['msg']  = AdiantiCoreTranslator::translate('Hash error');
                        echo json_encode($response);
                        return;
                    }
                    
                    // check allowed file extension
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    
                    if (!in_array(strtolower($ext),  $extensions))
                    {
                        $response = array();
                        $response['type'] = 'error';
                        $response['msg']  = AdiantiCoreTranslator::translate('Extension not allowed');
                        echo json_encode($response);
                        return;
                    }
                }
                
                if (is_writable($folder) )
                {
                    $newFile = bin2hex(random_bytes(24)).'.'.strtolower($ext);
                    $finalPath = $folder . $newFile;
                    
                    if( move_uploaded_file( $file['tmp_name'], $finalPath ) )
                    {
                        $response['type'] = 'success';
                        $response['fileName'] = $newFile;
                    }
                    else
                    {
                        $response['type'] = 'error';
                        $response['msg'] = '';
                    }
                }
                else
                {
                    $response['type'] = 'error';
                    $response['msg']  = AdiantiCoreTranslator::translate('Permission denied') . ": {$path}";
                }
                echo json_encode($response);
            }
            else
            {
                $response['type'] = 'error';
                $response['msg']  = AdiantiCoreTranslator::translate('Server has received no file') . '. ' . AdiantiCoreTranslator::translate('Check the server limits') .  '. ' . AdiantiCoreTranslator::translate('The current limit is') . ' ' . self::getMaximumFileUploadSizeFormatted();
                echo json_encode($response);
            }
        }
        else
        {
            $response['type'] = 'error';
            $response['msg']  = AdiantiCoreTranslator::translate('Server has received no file') . '. ' . AdiantiCoreTranslator::translate('Check the server limits') .  '. ' . AdiantiCoreTranslator::translate('The current limit is') . ' ' . self::getMaximumFileUploadSizeFormatted();
            echo json_encode($response);
        }
    }
}
