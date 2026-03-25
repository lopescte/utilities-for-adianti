<?php
namespace Lopescte\UtilitiesForAdianti\Traits;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

/**
 * Trait AdiantiS3FileSaveTrait
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
trait S3FileSaveTrait
{
    private $s3_config = [];
    private $s3_client;

    /**
     * Configura o acesso ao S3 dinamicamente ou por variáveis de ambiente
     */
    public function configureS3(array $config = [])
    {
        $this->s3_config = [
            'region' => $config['region'] ?? $_ENV['AWS_REGION'] ?? 'sa-east-1',
            'bucket' => $config['bucket'] ?? $_ENV['AWS_BUCKET'] ?? '',
            'key'    => $config['key'] ?? $_ENV['AWS_ACCESS_KEY_ID'] ?? '',
            'secret' => $config['secret'] ?? $_ENV['AWS_SECRET_ACCESS_KEY'] ?? '',
            'acl'    => $config['acl'] ?? 'public-read',
        ];

        $this->s3_client = new S3Client([
            'version'     => 'latest',
            'region'      => $this->s3_config['region'],
            'credentials' => [
                'key'    => $this->s3_config['key'],
                'secret' => $this->s3_config['secret'],
            ],
        ]);
    }
    
    /**
     * Instancia a S3
     */
    private function getS3Client()
    {
        if (!$this->s3_client) {
            $this->configureS3();
        }
    
        if (!$this->s3_client) {
            throw new Exception('S3Client não foi inicializado.');
        }
    
        return $this->s3_client;
    }

    /**
     * Salva arquivo único (campo TFile) no S3 e atualiza o objeto
     */
    public function saveFileToBucket($object, $data, $field, $s3Path = 'uploads')
    {
        $file = json_decode(urldecode($data->$field));
        
        if (isset($file->fileName)) {
          
            $pk = $object->getPrimaryKey();      
            $class = get_class($object);
            $localPath = $file->fileName;

            try {
                if (!empty($file->delFile))
                {
                    $delFile = urldecode($file->delFile);
                    $object->$field = '';
                    $file->fileName = '';

                    $result = $this->getS3Client()->deleteObject([
                        'Bucket'     => $this->s3_config['bucket'],
                        'Key'        => $this->extractKeyFromUrl($delFile),
                    ]);
                }
                if (!empty($file->newFile) && file_exists($localPath)) {
                    $s3Key = trim($s3Path . '/' . $object->$pk . '/' . basename($file->fileName), '/');
                    
                    $result = $this->getS3Client()->putObject([
                        'Bucket'     => $this->s3_config['bucket'],
                        'Key'        => $s3Key,
                        'SourceFile' => $localPath,
                        'ACL'        => $this->s3_config['acl'],
                    ]);
    
                    @unlink($localPath);
                    $object->$field = $result['ObjectURL'];
                    $data->$field = $result['ObjectURL'];
                }
                $class::where($pk, '=', $object->$pk)->set($field, $object->$field)->update();

            } catch (AwsException $e) {
                throw new Exception("Erro ao salvar arquivo no S3: " . $e->getAwsErrorMessage());
            }
        }
    }

    /**
     * Salva múltiplos arquivos (campo TMultiFile) no S3 e atualiza o objeto
     */
    public function saveFilesToBucket($object, $data, $input_name, $model_files, $file_field, $foreign_key, $target_path = 'uploads')
    {
        $pk = $object->getPrimaryKey();
    
        $files_form    = [];
        $target_path  .= '/' . $object->$pk;
        $target_path   = str_replace('//', '/', $target_path);
        $final_objects = [];
    
        if (isset($data->$input_name) && $data->$input_name) {
            foreach ($data->$input_name as $key => $info_file) {
                // No TMultiFile, cada item costuma vir como JSON urlencoded
                if (is_string($info_file)) {
                    $dados_file = json_decode(urldecode($info_file));
                } elseif (is_array($info_file)) {
                    $dados_file = (object) $info_file;
                } else {
                    $dados_file = $info_file;
                }
    
                if (!empty($dados_file->fileName)) {
                    $source_file = $dados_file->fileName;
    
                    // No S3 não há pasta física; o path vira parte da key
                    $target_file = $target_path . '/' . basename($dados_file->fileName);
                    $target_file = str_replace('//', '/', $target_file);
                    $target_file = str_replace('tmp/', '', $target_file);
    
                    $file_form = [];
                    $file_form['delFile']  = false;
                    $file_form['idFile']   = (!empty($dados_file->idFile)) ? $dados_file->idFile : null;
                    $file_form['fileName'] = $dados_file->fileName;
    
                    // Exclusão
                    if (!empty($dados_file->delFile)) {
                        $file_form['delFile'] = true;
    
                        if (!empty($dados_file->idFile)) {
                            $file = $model_files::find($dados_file->idFile);
    
                            if ($file) {
                                if (!empty($file->$file_field)) {
                                    $keyToDelete = $this->extractKeyFromUrl($file->$file_field);

                                    $this->getS3Client()->deleteObject([
                                        'Bucket' => $this->s3_config['bucket'],
                                        'Key'    => $keyToDelete,
                                    ]);
                                }
    
                                $file->delete();
                            }
                        }
                    }
                    // Arquivo existente, mantido
                    else if (!empty($dados_file->idFile)) {
                        $existing = $model_files::find($dados_file->idFile);
    
                        if ($existing) {
                            $final_objects[] = $existing;
    
                            $file_form['fileName'] = $existing->$file_field;
                        }
                    }
    
                    // Novo Arquivo
                    if (!empty($dados_file->newFile)) {
                        if (file_exists($source_file)) {
                            $result = $this->getS3Client()->putObject([
                                'Bucket'     => $this->s3_config['bucket'],
                                'Key'        => $target_file,
                                'SourceFile' => $source_file,
                                'ACL'        => $this->s3_config['acl'],
                            ]);
    
                            @unlink($source_file);
    
                            $model_file = new $model_files;
                            $model_file->$file_field   = $result['ObjectURL'];
                            $model_file->$foreign_key  = $object->$pk;
                            $model_file->store();
    
                            $final_objects[] = $model_file;
    
                            $pk_detail = $model_file->getPrimaryKey();
                            $file_form['idFile']   = $model_file->$pk_detail;
                            $file_form['fileName'] = $result['ObjectURL'];
                        }
                    }
    
                    if ($file_form && !$file_form['delFile']) {
                        $files_form[] = $file_form;
                    }
                }
            }
    
            $data->$input_name = $files_form;
        }
    
        return $final_objects;
    }
    
    private function extractKeyFromUrl($url)
    {
        $parts = parse_url($url);
        return ltrim($parts['path'], '/');
    }
}
