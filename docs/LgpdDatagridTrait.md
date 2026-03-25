# :gear: Lgpd Datagrid Trait

Trait responsável por aplicar **mascaramento automático de dados sensíveis em datagrids**, auxiliando na conformidade com a **LGPD (Lei Geral de Proteção de Dados)**.

---

## :book: Visão Geral

A `LgpdDatagridTrait` foi projetada para simplificar a anonimização de dados exibidos em componentes de listagem, especialmente no **Adianti Framework**.

Com ela, é possível aplicar mascaramento de forma automática em colunas específicas de um `TDataGrid`, sem a necessidade de tratamento manual em cada campo.

---

## :rocket: Objetivo

- automatizar a proteção de dados sensíveis em listagens;
- reduzir código repetitivo;
- centralizar regras de mascaramento;
- garantir padronização na exibição de informações.

---

## :package: Integração

A trait utiliza internamente os métodos da classe:

- [`LgpdMaskService`](LgpdMaskService.md)

---

## :gear: Como Utilizar

1. Carregue a trait em sua sua classe antes do construtor dela:

```php
class MinhaClasse extends TPage
{
    protected $form;
     
    // Exemplo de carregamento da trait
    use LgpdDatagridTrait;
      
    public function __construct( $param )
    {
    
    }
}
```
2. Após a criação do datagrid em sua classe, defina as colunas onde deverão ser aplicadas as restrições:

```php
// creates a Datagrid
$this->datagrid = new TDataGrid; 

$this->definirCamposLgpd([
            'meu_campo' => ['inicio' => 3, 'fim' => 2, 'char' => '*']
        ]);

$this->aplicarLgpd($this->datagrid);
```

---
## :bulb: Funcionamento

Durante a renderização do datagrid:

- A trait intercepta os valores das colunas configuradas
- Aplica o método correspondente do [LgpdMaskService](LgpdMaskService.md)
- Retorna o valor mascarado para exibição

---
## :shield: Tipos de Mascaramento Suportados

| Tipo     | Método aplicado      |
| -------- | -------------------- |
| CPF      | `mascararCPF()`      |
| Telefone | `mascararTelefone()` |
| E-mail   | `mascararEmail()`    |

> Não é possível aplicar o mascaramento automático de nomes que a classe [LgpdMaskService](LgpdMaskService.md#mascararnome) permite.

---
## :warning: Considerações Importantes

- o mascaramento ocorre apenas na **camada de apresentação**
- os dados originais permanecem intactos no banco
- recomenda-se utilizar junto com controle de acesso
- não substitui políticas de segurança mais robustas

---
## :white_check_mark: Vantagens

- implementação rápida
- código mais limpo
- reaproveitamento de lógica
- maior conformidade com LGPD
- fácil manutenção

---
## :link: Navegação

- [Voltar ao Guia](StartGuide.md)