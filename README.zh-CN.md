# EasyTpl

[![License](https://img.shields.io/github/license/phppkg/easytpl.svg?style=flat-square)](LICENSE)
[![Php Version](https://img.shields.io/packagist/php-v/phppkg/easytpl?maxAge=2592000)](https://packagist.org/packages/phppkg/easytpl)
[![GitHub tag (latest SemVer)](https://img.shields.io/github/tag/phppkg/easytpl)](https://github.com/phppkg/easytpl)
[![Actions Status](https://github.com/phppkg/easytpl/workflows/Unit-Tests/badge.svg)](https://github.com/phppkg/easytpl/actions)
[![English readme](https://img.shields.io/badge/English-Readme-brightgreen.svg?style=for-the-badge&maxAge=2592000)](README.md)

⚡️ 简单快速的 PHP 模板引擎。

## 功能特性

- 简单、轻量且快速。
  - 无学习成本
  - 仅仅简单处理并转换为原生PHP语法
  - 兼容PHP原生语法使用
- 更加简单的输出语法。 例如：`{{ var }}` `{{= $var }}` `{{ $var }}` `{{ echo $var }}`
  - 允许忽略前缀 `$`，将在编译时自动追加
- 支持链式访问数组值。 例如：`{{ $arr.0 }}` `{{ $map.name }}` `{{ $map.user.name }}`
- 支持所有控制语法。 例如 `if,elseif,else;foreach;for;switch`
- 支持使用PHP内置函数作为过滤器。 例如：`{{ $var | ucfirst }}` `{{ date('Y-m-d') }}`
- 更加安全，默认会自动通过 `htmlspecialchars` 将输出结果进行处理
  - 除非设置了禁用或者手动使用 `raw` 过滤器
- 支持添加自定义过滤器
  - 默认内置过滤器：`upper` `lower` `nl`
- 支持添加自定义指令，提供自定义功能
  - `EasyTemplate` 支持使用布局文件. 支持指令: `layout` `include` `contents`
  - `ExtendTemplate` 提供模板继承功能. 支持指令: `extends` `block` `endblock`
- 支持模板中添加注释。 例如: `{{# comments ... #}}`

## 安装

**composer**

```bash
composer require phppkg/easytpl
```

## 快速开始

```php
use PhpPkg\EasyTpl\EasyTemplate;

$tplCode = <<<'CODE'
My name is {{ $name | strtoupper }},
My develop tags:

{{ foreach($tags as $tag) }}
- {{ $tag }}

{{ endforeach }}
CODE;

$t = new EasyTemplate();

$str = $t->renderString($tplCode, [
    'name' => 'inhere',
    'tags' => ['php', 'go', 'java'],
]);

echo $str;
```

**渲染输出**:

```text
My name is INHERE,
My develop tags:

- php
- go
- java
```

## 更多使用说明

语法跟PHP原生模板一样的，加入的特殊语法只是为了让使用更加方便。

- `EasyTemplate` 默认开启输出过滤，可用于渲染视图模板
- `TextTemplate` 则是关闭了输出过滤，主要用于文本处理，代码生成等

### 配置设置

```php
use PhpPkg\EasyTpl\EasyTemplate;

$t = EasyTemplate::new([
    'tplDir' => 'path/to/templates',
    'allowExt' => ['.php', '.tpl'],
]);

// do something ...
```

更多设置：

```php
/** @var PhpPkg\EasyTpl\EasyTemplate $t */
$t->disableEchoFilter();
$t->addFilter($name, $filterFn);
$t->addFilters([]);
$t->addDirective($name, $handler);
```

### 输出变量值

下面的语句一样，都可以用于打印输出变量值

```php
{{ name }}
{{ $name }}
{{= $name }}
{{ echo $name }}
```

更多:

```php
{{ $name ?: 'inhere' }}
{{ $age > 20 ? '20+' : '<= 20' }}
```

> 默认会自动通过 `htmlspecialchars` 将输出结果进行处理，除非设置了禁用或者手动使用 `raw` 过滤器

- 设置禁用输出过滤 `$t->disableEchoFilter()`
- 模板中禁用输出过滤 `{{ $name | raw }}`

### 快速访问数组值

可以使用 `.` 来快速访问数组值。原来的写法也是可用的，简洁写法也会自动转换为原生写法。

```php
$arr = [
    'val0',
    'subKey' => 'val1',
];
```

在模板中使用:

```php
first value is: {{ $arr.0 }} // val0
'subKey' value is: {{ $arr.subKey }} // val1
```

### If 语句块

`if` 语句:

```php
{{ if ($name !== '') }}
hi, my name is {{ $name }}
{{ endif }}
```

`if else` 语句:

```php
hi, my name is {{ $name }}
age is {{ $age }}, and
{{ if ($age >= 20) }}
 age >= 20.
{{ else }}
 age < 20.
{{ endif }}
```

`if...elseif...else` 语句:

```php
hi, my name is {{ $name }}
age is {{ $age }}, and
{{ if ($age >= 50) }}
 age >= 50.
{{ elseif ($age >= 20) }}
 age >= 20.
{{ else }}
 age < 20.
{{ endif }}
```

### For/Foreach 语句块

`foreach`:

```php
tags:

{{ foreach($tags as $tag) }}
- {{ $tag }}

{{ endforeach }}
```

with keys:

```php
tags:

{{ foreach($tags as $index => $tag) }}
{{ $index }}. {{ $tag }}

{{ endforeach }}
```

### 模板中添加注释

以 `{{#` 和 `#}}` 包裹的内容将会当做注释忽略。

```text
{{# comments ... #}}{{ $name }} // inhere
```

multi lines:

```text
{{#
 this
 comments
 block
#}}{{ $name }} // inhere
```

## 使用过滤器

默认内置过滤器:

- `upper` - 等同于 `strtoupper`
- `lower` - 等同于 `strtolower`
- `nl`    - 追加换行符 `\n`

### 过滤器使用示例

您可以在任何模板中使用过滤器。

**基本使用**:

```php
{{ 'inhere' | ucfirst }} // Inhere 
{{ 'inhere' | upper }} // INHERE
```

**链式使用**:

```php
{{ 'inhere' | ucfirst | substr:0,2 }} // In
{{ '1999-12-31' | date:'Y/m/d' }} // 1999/12/31
```

**传递非静态值**:

```php
{{ $name | ucfirst | substr:0,1 }}
{{ $user['name'] | ucfirst | substr:0,1 }}
{{ $userObj->name | ucfirst | substr:0,1 }}
{{ $userObj->getName() | ucfirst | substr:0,1 }}
```

**将变量作为过滤器参数传递**:

```php
{{
    $suffix = '￥';
}}

{{ '12.75' | add_suffix:$suffix }} // 12.75￥
```

### 自定义过滤器

```php
use PhpPkg\EasyTpl\EasyTemplate;

$tpl = EasyTemplate::new();
// use php built function
$tpl->addFilter('upper', 'strtoupper');

// 一次添加多个
$tpl->addFilters([
    'last3chars' => function (string $str): string {
        return substr($str, -3);
    },
]);
```

在模板中使用:

```php
{{
  $name = 'inhere';
}}

{{ $name | upper }} // INHERE
{{ $name | last3chars }} // ere
{{ $name | last3chars | upper }} // ERE
```

## 自定义指令

您可以使用指令实现一些特殊的逻辑。

```php
$tpl = EasyTemplate::new();
$tpl->addDirective('include', function (string $body, string $name) {
    /** will call {@see EasyTemplate::include()} */
    return '$this->include' . $body;
});
```

> TIP: 内置的 `layout`, `include` 等指令就是这样实现的。

------

## `layout` 布局模板

- page template `home01.tpl`

```php
{{ layout('layouts/layout01.tpl') }}

on home: block body;
```

### Use include

**在模板中使用**:

```php
{{ include('part/header.tpl', ['title' => 'My world']) }}
```

------

## `extend` 布局模板

新增指令:

- `extends` 定义继承一个父模板.
  - 语法: `{{ extends('layouts/main.tpl') }}`
- `block` 定义开始一个新的模板块.
  - 语法: `{{ block 'header' }}`
- `endblock` 标记一个模板块结束.
  - 语法: `{{ endblock }}`

```php
use PhpPkg\EasyTpl\ExtendTemplate;

$et = new ExtendTemplate();
$et->display('home/index.tpl');
```

### `extend` 模板使用示例

- 基础布局文件: `layouts/main.tpl`

```php
{{ block 'header' }}
header contents in layout main.
{{ endblock }}

{{ block 'body' }}
body contents in layout main.
{{ endblock }}

{{ block 'footer' }}
footer contents in layout main.
{{ endblock }}
```

- 具体页面文件: `home/index.tpl`

```php
{{ extends('layouts/main.tpl') }}

{{ block 'body' }}
body contents in home index.
{{ endblock }}
```

**渲染结果**

```text
header contents in layout main.
body contents in home index.
footer contents in layout main.
```

------

## Dep packages

- [toolkit/fsutil](https://github.com/php-toolkit/fsutil)
- [toolkit/stdlib](https://github.com/php-toolkit/stdlib)

## License

[MIT](LICENSE)
