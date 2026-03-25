# :abcd: Auto Charset Trait

Trait responsável por validar automaticamente os caracteres de entrada de dados, garantindo compatibilidade com o **charset configurado no banco de dados**.

---

## :book: Visão Geral

A `AutoCharsetTrait` foi desenvolvida para evitar erros comuns relacionados a charset durante operações de persistência, especialmente em bancos que não suportam determinados caracteres (como emojis ou caracteres Unicode avançados).

Ela permite validar previamente os dados recebidos, impedindo que valores inválidos sejam enviados ao banco de dados.

---

## :rocket: Objetivo

- prevenir erros de encoding no banco de dados;
- validar automaticamente caracteres inválidos;
- melhorar a integridade dos dados;
- fornecer mensagens amigáveis ao usuário final.

---

## :gear: Funcionamento

A trait analisa os dados informados e verifica:

- encoding UTF-8 válido;
- presença de caracteres de controle;
- compatibilidade com o charset definido (`latin1`, `utf8`, `utf8mb4`).

Caso identifique caracteres inválidos, uma exceção é lançada.

---

## :clipboard: Como Utilizar

---

### 1. Carregar a trait na classe

Carregue a trait na classe antes do construtor:

```php
class MinhaClasse extends TPage
{
    use AutoCharsetTrait;
}
```

---

### 2. Validar os dados

Na sua função de salvamento (onSave($param), por exemplo), faça a validação do array de dados:
```php
$this->validateCharset((array) $data);
```

---

### Ignorar campos específicos

```php
$this->validateCharset((array) $data, ['campo_html', 'descricao']);
```

---

## :hammer_and_wrench: Charset suportado

A trait suporta os seguintes charsets:

| Charset   | Descrição |
|----------|----------|
| `latin1` | Permite apenas caracteres até U+00FF |
| `utf8`   | Permite até 3 bytes (sem emojis) |
| `utf8mb4`| Suporte completo a Unicode |

---

Você pode definir o charset a ser usado:

```php
$this->charset = 'utf8';
```

---

## :bulb: Funcionamento interno

A validação ocorre em etapas:

1. Verifica se o texto está em UTF-8 válido  
2. Remove caracteres de controle inválidos  
3. Valida compatibilidade com o charset configurado  

---

## :warning: Exceções

Charset inválido:

```php
throw new InvalidArgumentException("Charset não suportado");
```

Caracteres inválidos:

```php
throw new Exception("Campo contém caracteres não suportados");
```

---

## :sparkles: Exemplo prático

```php
$data = [
    'nome' => 'João :)',
];

$this->charset = 'utf8';

$this->validateCharset((array) $data);
```

Resultado:

- erro lançado (emoji não permitido em `utf8`)

---

## :shield: Boas Práticas

- utilize `utf8mb4` sempre que possível;
- valide dados antes de persistir no banco;
- utilize labels amigáveis nos campos;
- combine com validações adicionais de formulário.

---

## :white_check_mark: Vantagens

- evita erros silenciosos de charset  
- melhora a qualidade dos dados  
- integração simples com Adianti  
- reutilização via trait  
- mensagens claras para o usuário  

---

## :link: Navegação

- [Voltar ao Guia](StartGuide.md)