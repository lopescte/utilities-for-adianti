## :lock: Unique Filename Uploader Service
 
Esta classe implementa um serviço de upload compatível com os componentes `TFile` e `TMultiFile`, responsável por gerenciar o armazenamento de arquivos enviados pela aplicação.

Durante o processo de upload, caso um arquivo com o mesmo nome já exista na pasta temporária, o serviço adiciona automaticamente um **sufixo numérico incremental** ao nome do arquivo, garantindo:

- a prevenção de sobrescrita de arquivos existentes;
- a preservação de versões distintas de arquivos com nomes iguais;
- maior controle e organização no armazenamento.

Essa estratégia permite manter os nomes originais dos arquivos, ao mesmo tempo em que assegura integridade e consistência no sistema de upload.

---
### :shield: A permissão

Primeiramente você vai precisar dar permissão de uso desta classe, inserindo-a nas `public_classes` de seu arquivo `application.php`:
```php
'permission' =>  [
    'public_classes' => [
      'UniqueFilenameUploaderService'
    ]
]
```
Ou preferencialmente dentro da função `hasDefaultPermissions` do arquivo `engine.php`:

```php
/**
 * Return default programs for logged users
 */
public static function hasDefaultPermissions($class)
{
    $default_permissions = ['Adianti\Base\TStandardSeek' => TRUE,
                            'LoginForm' => TRUE,
                            .....,
                            'UniqueFilenameUploaderService' => TRUE];
    
    return (isset($default_permissions[$class]) && $default_permissions[$class]);
}
```
Qualquer das duas opções vai dar a permissão necessária para o funcionamento do serviço de modo geral, em qualquer formulário que você precisar.

---
### :gear: O uso 

Basta definir o serviço autorizado no passo anterior para seu campo, e ele estará em ação ao fazer upload.

```php
$arquivo = new TFile('arquivo');

$arquivo->setService('UniqueFilenameUploaderService');
```

```php
$arquivos = new TMultiFile('arquivos');

$arquivos->setService('UniqueFilenameUploaderService');
```
---
### :link: Navegação

- [Voltar ao Guia](StartGuide.md)