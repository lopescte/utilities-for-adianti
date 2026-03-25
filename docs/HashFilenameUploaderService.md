## :lock: Hash Filename Uploader Service

Esta classe implementa um serviço de upload compatível com os componentes `TFile` e `TMultiFile`, responsável por processar arquivos enviados pela aplicação.

Durante o processo de upload, o nome original do arquivo é substituído por um hash criptográfico único, garantindo:

- a prevenção de conflitos por nomes duplicados;
- a eliminação do risco de sobrescrita de arquivos existentes;
- maior segurança e padronização no armazenamento.

Essa abordagem assegura integridade, rastreabilidade e organização no gerenciamento de arquivos.
  
---
### :shield: A permissão

Primeiramente você vai precisar dar permissão de uso desta classe, inserindo-a nas `public_classes` de seu arquivo `application.php`:
```php
'permission' =>  [
    'public_classes' => [
      'HashFilenameUploaderService'
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
                            'HashFilenameUploaderService' => TRUE];
    
    return (isset($default_permissions[$class]) && $default_permissions[$class]);
}
```
Qualquer das duas opções vai dar a permissão necessária para o funcionamento do serviço de modo geral, em qualquer formulário que você precisar.

---

### :gear: O uso 

Basta definir o serviço autorizado no passo anterior para seu campo, e ele estará em ação ao fazer upload.

```php
$arquivo = new TFile('arquivo');

$arquivo->setService('HashFilenameUploaderService');
```

```php
$arquivos = new TMultiFile('arquivos');

$arquivos->setService('HashFilenameUploaderService');
```

---
### :link: Navegação

- [Voltar ao Guia](StartGuide.md)