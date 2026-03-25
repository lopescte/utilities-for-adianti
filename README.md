# :package: Utilities For Adianti

[![Latest Stable Version](http://poser.pugx.org/lopescte/utilities-for-adianti/v)](https://packagist.org/packages/lopescte/utilities-for-adianti) [![Total Downloads](http://poser.pugx.org/lopescte/utilities-for-adianti/downloads)](https://packagist.org/packages/lopescte/utilities-for-adianti) [![PHP Version Require](http://poser.pugx.org/lopescte/utilities-for-adianti/require/php)](https://packagist.org/packages/lopescte/utilities-for-adianti) [![License](http://poser.pugx.org/lopescte/utilities-for-adianti/license)](https://packagist.org/packages/lopescte/utilities-for-adianti) [![LGPD](https://img.shields.io/badge/LGPD-data%20protection-important)](#)

## :shield: Sobre a Biblioteca

O pacote **UtilitiesForAdianti** é uma biblioteca de utilidades e complementos para serem utilizados em softwares desenvolvidos usando o **[Adianti Framework](https://adiantiframework.com.br)**.

Seu objetivo é facilitar a implementação de funções que não são nativas do Framework sem a necessidade de alteração em componentes nativos, facilitando a atualização do software para as novas versíµes do framework e do template.

---
## :rocket: Instalação

Via Composer:

```bash
composer require lopescte/utilities-for-adianti
```

Ou manualmente:

1. Clone o repositório
2. Inclua o autoload no seu projeto

```php
require 'vendor/autoload.php';
```

---
## :book: Documentação

A documentação completa está disponí­vel na pasta [`docs`](docs):

- [Guia de Introdução](docs/StartGuide.md)

---

## :briefcase: Estrutura do Projeto

```plaintext
utilities-for-adianti/
|-- src/
|   |-- bootstrap.php
|   |-- Bootstrap/
|   |   `-- Loader.php
|   |-- Model/
|   |   `-- LgpdAuditLog.php
|   |-- Services/
|   |   |-- LgpdAuditService.php
|   |   |-- LgpdMaskService.php
|   |   |-- LgpdRbacService.php
|   |   |-- LgpdSignatureService.php
|   |   |-- HashFilenameUploaderService.php
|   |   `-- UniqueFilenameUploaderService.php
|   |-- Traits/
|   |   |-- AutoCharsetTrait.php
|   |   |-- AutoFormConstraintsTrait.php
|   |   |-- LgpdDatagridTrait.php
|   |   `-- S3FileSaveTrait.php
|   `-- Util/
|   |   `-- TBreadCrumbWithLink.php
|   `-- SqlException/
|       `-- SqlExceptionHandler.php
|-- docs/
|   |-- StartGuide.md
|   |-- LgpdMaskService.md
|   `-- LgpdDatagridTrait.md
|-- composer.json
|-- CHANGELOG.md
|-- README.md
`-- LICENSE.md
```

---

## :dart: Recursos

- Mascaramento de CPF
- Mascaramento de e-mail
- Mascaramento de telefone
- Mascaramento de nomes
- Integração com Adianti Framework
- Aplicação automática em datagrids

---

## :bulb: Boas Práticas

- Mascarar dados apenas na **camada de apresentação**
- Não armazenar dados mascarados no banco
- Associar com controle de acesso e auditoria
- Revisar periodicamente pontos de exposição de dados

---

## :handshake: Contribuição

Contribuições são bem-vindas!

1. Fork o projeto
2. Crie uma branch
3. Commit suas alterações
4. Abra um Pull Request

---

## :bust_in_silhouette: Autor
* **Marcelo Lopes** - *Desenvolvedor* - [Site](https://www.reiselopes.com.br) | [Facebook](https://facebook.com/lopes.cte) | [Instagram](https://instagram.com/lopescte) | [GitHub](https://github.com/lopescte)

---

## :information_source: Suporte

Para dúvidas, sugestíµes ou contribuiçíµes, utilize o repositório do projeto.

---

## :page_facing_up: Licença

Este projeto está licenciado sob a licença [MIT](LICENSE.md).
