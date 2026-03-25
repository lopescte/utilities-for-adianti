# :bookmark_tabs: Sql Exception Handler

Classe utilitária para tratamento centralizado de exceções em operações com banco de dados, com foco em aplicações desenvolvidas com o **Adianti Framework**.

O objetivo da `SqlExceptionHandler` é transformar mensagens técnicas e pouco amigáveis de exceções SQL em mensagens mais compreensíveis para o usuário final, mantendo a possibilidade de log técnico para análise e suporte.

A classe foi projetada para trabalhar em conjunto com:

- `SqlErrorCatalog`
- `sql_errors.json`

---

## :card_file_box: Recursos

- Tratamento centralizado de exceções SQL
- Suporte a **MySQL / MariaDB**, **PostgreSQL** e **SQL Server**
- Leitura de mensagens a partir de arquivo **JSON**
- Suporte a **idiomas** por catálogo
- Suporte a **placeholders** como `{field}`, `{value}`, `{constraint}`, `{table}` e `{column}`
- Tentativa de conversão automática do nome técnico da coluna para o **label do campo do formulário**
- Preservação da mensagem original quando a exceção **não for relacionada a SQL**
- Integração simples com formulários Adianti existentes

---

## :bulb: Finalidade

Em muitos casos, erros de banco retornam mensagens como:

- `SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry...`
- `Data too long for column 'nome'`
- `null value in column "cpf" violates not-null constraint`

Essas mensagens são úteis tecnicamente, mas inadequadas para exibição direta ao usuário.

A `SqlExceptionHandler` atua como camada intermediária, convertendo esses retornos em mensagens como:

- `Já existe um registro duplicado para o valor "12345678900".`
- `O valor informado excede o tamanho permitido para o campo "Nome".`
- `Há campo obrigatório não preenchido para o campo "CPF".`

---

## :pushpin: Funcionamento

A `SqlExceptionHandler` recebe uma exceção e:

1. verifica se ela está relacionada a banco de dados;
2. se for SQL, delega a interpretação ao `SqlErrorCatalog`;
3. busca a mensagem apropriada no arquivo `json`;
4. aplica placeholders;
5. tenta substituir o nome técnico da coluna pelo label do campo do formulário;
6. retorna a mensagem amigável;
7. caso a exceção não seja SQL, preserva a mensagem original.

---

## :briefcase: Métodos principais

### `handle()`

Responsável por tratar a exceção completa, incluindo rollback, geração da mensagem amigável e exibição via `TMessage`.

#### Assinatura

```php
SqlExceptionHandler::handle(
    \Throwable $e,
    ?string $jsonFile = null,
    ?string $locale = null,
    $form = null
): void
```

---

### `getMessage()`

Retorna apenas a mensagem amigável, sem exibir diretamente.

#### Assinatura

```php
SqlExceptionHandler::getMessage(
    \Throwable $e,
    ?string $jsonFile = null,
    ?string $locale = null,
    $form = null
): string
```

---

## :computer: Uso básico

```php
catch (Exception $e)
{
    $this->form->setData($this->form->getData());
    new TMessage('error', SqlExceptionHandler::getMessage($e));
}
```

---

## :computer: Uso com formulário Adianti

```php
catch (Exception $e)
{
    $this->form->setData($this->form->getData());
    new TMessage('error', SqlExceptionHandler::getMessage($e, null, null, $this->form));
}
```

---

## :bookmark_tabs: Comportamento para exceções não SQL

- exceções SQL são traduzidas
- exceções normais mantêm a mensagem original

Exemplo:

```php
throw new Exception('Campo Nome é obrigatório');
```

Resultado:

```
Campo Nome é obrigatório
```

---

## :beginner: Idiomas

- pt
- en
- es

Detecção automática via:

- AdiantiCoreTranslator
- Session
- JSON (fallback)

---

## :white_check_mark: Vantagens

- centralização do tratamento de erros
- mensagens amigáveis
- suporte multilíngue
- fácil manutenção via JSON
- não quebra validações existentes

---

## :mag_right: Limitações

- depende do conteúdo da mensagem retornada pelo banco
- nem sempre é possível identificar o campo com precisão
- retorna o label correto do campo apenas se setado no formulário com $campo->SetLabel('Minha Label')
- pode exigir mapeamento manual em alguns casos

---

---
## :link: Navegação

- [Voltar ao Guia](StartGuide.md)