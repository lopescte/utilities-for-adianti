# :cloud: S3 File Save Trait

Trait responsável por realizar o **upload e gerenciamento de arquivos no AWS S3**, integrando-se aos componentes `TFile` e `TMultiFile` do **Adianti Framework**.

---

## :book: Visão Geral

A `S3FileSaveTrait` permite substituir o armazenamento local de arquivos por um armazenamento em nuvem utilizando o **Amazon S3**, oferecendo maior escalabilidade, segurança e disponibilidade.

A trait suporta tanto:

- upload de arquivo único (`TFile`)
- upload de múltiplos arquivos (`TMultiFile`)

---

## :rocket: Objetivo

- integrar upload de arquivos diretamente com AWS S3;
- eliminar dependência de armazenamento local;
- padronizar o gerenciamento de arquivos;
- facilitar exclusão, atualização e versionamento de arquivos.

---

## :gear: Como Utilizar

Carregue a trait na classe:
```php
class MinhaClasse extends TPage
{
    use S3FileSaveTrait;
}
```
    
Dentro do construtor, faça a configuração dinamicamente ou via variáveis de ambiente.

**Variáveis suportadas**
```env
AWS_REGION=sa-east-1
AWS_BUCKET=seu-bucket
AWS_ACCESS_KEY_ID=sua-chave
AWS_SECRET_ACCESS_KEY=seu-segredo
```
**Configuração manual**
```php
$this->configureS3([
    'region' => 'sa-east-1',
    'bucket' => 'seu-bucket',
    'key'    => 'sua-chave',
    'secret' => 'seu-segredo',
    'acl'    => 'public-read'
]);
```

---

## :file_folder: Upload de Arquivo Único (TFile)
```php
$this->saveFileToBucket($object, $data, 'campo_arquivo', 'uploads');
```
### Parâmetros

| Parâmetro | Descrição |
|---|---|
| `$object` | Objeto ativo (Active Record) |
| `$data` | Dados do formulário |
| `$field` | Nome do campo |
| `$s3Path` | Caminho base no bucket |

### Funcionamento

- faz upload do arquivo para o S3;
- remove o arquivo local temporário;
- atualiza o campo do objeto com a URL do S3;
- permite exclusão de arquivos existentes.

---

## :open_file_folder: Upload Múltiplo (TMultiFile)
```php
$this->saveFilesToBucket(
    $object,
    $data,
    'campo_multifile',
    ModelArquivo::class,
    'arquivo_url',
    'foreign_key',
    'uploads'
);
```
### Parâmetros

| Parâmetro | Descrição |
|---|---|
| `$object` | Objeto principal |
| `$data` | Dados do formulário |
| `$input_name` | Nome do campo multifile |
| `$model_files` | Model dos arquivos |
| `$file_field` | Campo que armazena a URL |
| `$foreign_key` | Chave estrangeira |
| `$target_path` | Caminho no bucket |

### Funcionalidades

- upload de múltiplos arquivos;
- remoção automática de arquivos excluídos;
- persistência em tabela auxiliar;
- manutenção de arquivos existentes;
- retorno dos objetos persistidos.

---

## :shield: Segurança e Boas Práticas

- utilize credenciais via `.env` sempre que possível;
- evite expor chaves diretamente no código;
- defina corretamente a ACL (`private` ou `public-read`);
- valide arquivos antes do upload;
- controle permissões de acesso aos arquivos.

---

## :warning: Considerações Importantes

- o S3 não possui diretórios físicos; o caminho faz parte da *key*;
- URLs retornadas podem ser armazenadas diretamente no banco;
- exclusões devem ser tratadas com cautela;
- o bucket deve estar previamente configurado.

---

## :white_check_mark: Vantagens

- armazenamento escalável;
- alta disponibilidade;
- integração transparente com Adianti;
- redução de uso de disco local;
- melhor organização de arquivos.

---

## :link: Navegação

- [Voltar ao Guia](StartGuide.md)
