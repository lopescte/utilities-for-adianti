# :link: TBreadCrumbWithLink

Componente de navegação que estende o `TBreadCrumb` do Adianti Framework, permitindo a criação de **breadcrumbs com links clicáveis e integração dinâmica via XML**.

---

## :book: Visão Geral

A classe `TBreadCrumbWithLink` foi desenvolvida para oferecer uma navegação hierárquica mais flexível e interativa dentro de aplicações Adianti.

Diferente do breadcrumb padrão, esta implementação:

- permite definir links clicáveis para cada item;
- suporta definição dinâmica de caminhos via XML;
- possibilita controle do item ativo;
- oferece integração com controladores da aplicação.

---

## :rocket: Objetivo

- melhorar a navegação entre páginas;
- permitir breadcrumbs dinâmicos;
- integrar navegação com estrutura de menu XML;
- oferecer maior flexibilidade que o `TBreadCrumb` padrão.

---

## :gear: Como Utilizar

 1. Instanciação simples

```php
$breadcrumb = new TBreadCrumbWithLink();
$breadcrumb->addHome();
$breadcrumb->addItem('Dashboard', 'DashboardView');
$breadcrumb->addItem('Relatórios', null, true);
```

---

 2. Uso via método estático

```php
$breadcrumb = TBreadCrumbWithLink::create([
    'Dashboard',
    'Relatórios',
    'Detalhes'
]);
```

---

 3. Definir controller da Home

```php
TBreadCrumbWithLink::setHomeController('DashboardView');
```

---

## :hammer_and_wrench: Adicionando Itens

```php
$breadcrumb->addItem($label, $controller, $isLast);
```

**Parâmetros**

| Parâmetro | Descrição |
|---|---|
| `$label` | Texto exibido no breadcrumb |
| `$controller` | Classe do controller para navegação |
| `$isLast` | Define se o item é o último (ativo) |

---

## :file_folder: Renderização via XML

A classe permite montar o breadcrumb automaticamente a partir de um arquivo XML de menu.

```php
$breadcrumb->renderFromXML('menu.xml', 'MinhaClasseController');
```

---

**Funcionamento:**

- lê a estrutura do menu XML;
- identifica o caminho até o controller atual;
- gera automaticamente os itens do breadcrumb;
- define o último item como ativo.

---

## :construction: Controle de Navegação

**Home**

- adiciona automaticamente um item "Home";
- pode redirecionar para um controller específico.

**Links**

- cada item pode conter link para outro controller;
- links são gerados via `engine.php`.

---

## :warning: Considerações Importantes

- depende da estrutura do XML de menu;
- o controller informado deve existir no XML;
- o último item não recebe link (estado ativo);
- utiliza o padrão de navegação do Adianti (`engine.php`).

---

## :white_check_mark: Vantagens

- navegação dinâmica baseada em XML;
- breadcrumbs clicáveis;
- integração com Adianti Framework;
- reutilização de lógica;
- maior controle de navegação.

---

## :sparkles: Diferenciais

- suporte a navegação dinâmica;
- controle de item ativo;
- integração com menu XML;
- customização de links.

---

## :link: Navegação

- [StartGuide](StartGuide.md)