# :lock: Lgpd Mask Service

Serviço para **mascaramento de dados sensíveis** em conformidade com boas práticas de privacidade e adequação à **LGPD**.

O `LgpdMaskService` pode ser utilizado diretamente em qualquer ponto da aplicação e também serve de base para integração com a [`LgpdDatagridTrait`](LgpdDatagridTrait.md), permitindo a anonimização automatizada de dados em datagrids.

---

## :book: Sumário

- [Visão Geral](#pushpin-visao-geral)
- [Recursos](#recursos)
- [Uso Estático](#uso-estatico)
- [Métodos Disponíveis](#gear-metodos-disponiveis)
  - [`mascararCPF()`](#mascararcpf)
  - [`mascararTelefone()`](#mascarartelefone)
  - [`mascararEmail()`](#mascararemail)
  - [`mascararNome()`](#mascararnome)
- [Boas Práticas](#shield-boas-praticas)
- [Veja Também](#link-veja-tambem)

---

## :pushpin: Visão Geral

Esta classe foi projetada para aplicar máscaras parciais em informações pessoais, preservando a utilidade visual dos dados sem expor integralmente seu conteúdo.

É especialmente útil em cenários como:

- exibição de dados em listagens;
- proteção de informacoes em logs e relatorios;
- anonimização parcial em interfaces administrativas;
- adequação de exibicao de dados em sistemas corporativos.

---

## :card_file_box: Recursos

- mascaramento de **CPF**, **telefone**, **e-mail** e **nome**;
- suporte a uso **estático**;
- possibilidade de personalizacao dos caracteres visíveis;
- suporte a escolha do caractere de mascaramento;
- integração com a [`LgpdDatagridTrait`](LgpdDatagridTrait.md).

---

## :label: Uso Estático

```php
$value = 'O CPF do cliente é 123.456.789-09';
echo LgpdMaskService::mascararCPF($value);
// Resultado: O CPF do cliente é 12*.***.***-09
```

---

## :gear: Métodos Disponiveis

### `mascararCPF()`

```php
public static function mascararCPF(
    string $cpf,
    int $mostrarInicio = 2,
    int $mostrarFim = 2,
    bool $validar = true,
    string $char = '*'
): string
```

Aplica máscara em números de **CPF** encontrados no conteúdo informado.

Opcionalmente, o método pode validar previamente se o valor identificado corresponde a um CPF válido antes de efetuar o mascaramento.

#### Parâmetros

| Parametro | Tipo | Obrigatório | Descrição |
|---|---|:---:|---|
| `$cpf` | `string` | Sim | Texto contendo um ou mais CPFs. |
| `$mostrarInicio` | `int` | Não | Quantidade de caracteres visíveis no início. Padrão: `2`. |
| `$mostrarFim` | `int` | Não | Quantidade de caracteres visíveis no final. Padrão: `2`. |
| `$validar` | `bool` | Não | Define se o CPF deve ser validado antes do mascaramento. Padrão: `true`. |
| `$char` | `string` | Não | Caractere usado para ocultação. Padrão: `*`. |

#### Exemplo

```php
$value = 'O CPF de John é 123.456.789-09';
echo LgpdMaskService::mascararCPF($value);
// Resultado: O CPF de John é 12*.***.***-09
```

---

### `mascararTelefone()`

```php
public static function mascararTelefone(
    string $telefone,
    int $mostrarInicio = 2,
    int $mostrarFim = 2,
    string $char = '*'
): string
```

Aplica máscara em números de **telefone** encontrados no conteúdo informado.

#### Parâmetros

| Parametro | Tipo | Obrigatório | Descrição |
|---|---|:---:|---|
| `$telefone` | `string` | Sim | Texto contendo um ou mais telefones. |
| `$mostrarInicio` | `int` | Não | Quantidade de caracteres visíveis no início. Padrão: `2`. |
| `$mostrarFim` | `int` | Não | Quantidade de caracteres visíveis no final. Padrão: `2`. |
| `$char` | `string` | Não | Caractere usado para ocultação. Padrão: `*`. |

#### Exemplo

```php
$value = 'O telefone de John é (77) 99999-9999';
echo LgpdMaskService::mascararTelefone($value);
// Resultado: O telefone de John é (77) ****-**99
```
---

### `mascararEmail()`

```php
public static function mascararEmail(
    string $email,
    int $mostrarInicio = 2,
    int $mostrarFim = 2,
    string $char = '*',
    bool $mascararDominio = false
): string
```

Aplica máscara em endereços de **e-mail** presentes no conteúdo informado.

#### Parâmetros

| Parametro | Tipo | Obrigatório | Descrição |
|---|---|:---:|---|
| `$email` | `string` | Sim | Texto contendo um ou mais E-mails. |
| `$mostrarInicio` | `int` | Não | Quantidade de caracteres visíveis no início. Padrão: `2`. |
| `$mostrarFim` | `int` | Não | Quantidade de caracteres visíveis no final. Padrão: `2`. |
| `$char` | `string` | Não | Caractere usado para ocultação. Padrão: `*`. |
| `$mascararDominio` | `bool` | Não | Define se inclui máscara também no domínio do e-mail (após o @). Padrão: `false`. |

#### Exemplo

```php
$value = 'O e-mail de John é john_doe@gmail.com';
echo LgpdMaskService::mascararEmail($value);
// Resultado: O e-mail de John é jo****oe@gmail.com
```
---

### `mascararNome()`

```php
public static function mascararNome(
    string $nome,
    int $mostrarInicio = 2,
    int $mostrarFim = 2,
    string $char = '*'
): string
```

Aplica máscara em **nomes de pessoas**.

#### Parâmetros

| Parametro | Tipo | Obrigatório | Descrição |
|---|---|:---:|---|
| `$nome` | `string` | Sim | O nome completo a ser mascarado. **Atenção**, todo o conteúdo desta variável será mascarado |
| `$mostrarInicio` | `int` | Não | Quantidade de caracteres visíveis no início. Padrão: `2`. |
| `$mostrarFim` | `int` | Não | Quantidade de caracteres visíveis no final. Padrão: `2`. |
| `$char` | `string` | Não | Caractere usado para ocultação. Padrão: `*`. |

> O retorno contendo a máscara será sempre com todas as letras em maiúsculas.

#### Exemplo

```php
$value = 'JOSÉ ROBERTO DOS SANTOS JÚNIOR';
echo LgpdMaskService::mascararNome($value);
// Resultado: JO** ******* *** ****** ****OR
```
---

## :bulb: Boas Práticas

- aplique mascaramento sempre que o dado nao precisar ser exibido integralmente;
- evite armazenar dados ja mascarados no banco;
- combine com controle de acesso;
- revise pontos de exposicao de dados.

---

## :link: Navegação

- [Voltar ao Guia](StartGuide.md)
