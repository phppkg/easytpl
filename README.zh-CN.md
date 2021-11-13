# EasyTpl

[![License](https://img.shields.io/github/license/phppkg/easytpl.svg?style=flat-square)](LICENSE)
[![Php Version](https://img.shields.io/badge/php-%3E=8.0-brightgreen.svg?maxAge=2592000)](https://packagist.org/packages/phppkg/easytpl)
[![GitHub tag (latest SemVer)](https://img.shields.io/github/tag/phppkg/easytpl)](https://github.com/phppkg/easytpl)
[![Actions Status](https://github.com/phppkg/easytpl/workflows/Unit-Tests/badge.svg)](https://github.com/phppkg/easytpl/actions)
[![English readme](https://img.shields.io/badge/English-Readme-brightgreen.svg?style=for-the-badge&maxAge=2592000)](README.md)

⚡️ 简单快速的 PHP 模板引擎。

## 功能特性

- 简单、轻量且快速。
  - 无学习成本
  - 仅仅简单处理并转换为原生PHP语法
  - 兼容PHP原生语法使用
- 更加简单的输出语法。 例如：`{{= $var }}` `{{ $var }}` `{{ echo $var }}`
- 支持所有控制语法。 例如 `if,elseif,else;foreach;for;switch`
- 支持链式访问数组值。 例如：`{{ $arr.0 }}` `{{ $map.name }}` `{{ $map.user.name }}`
- 更加安全，默认会自动通过 `htmlspecialchars` 将输出结果进行处理
  - 除非设置了禁用或者手动使用 `raw` 过滤器
- 支持使用PHP内置函数作为过滤器。 例如：`{{ $var | ucfirst }}`
- 支持添加自定义过滤器
  - 默认内置过滤器：`upper` `lower` `nl`
- 支持添加自定义指令，提供自定义功能
- 支持模板中添加注释。 例如: `{{# comments ... #}}`

## 安装

- 需要 PHP 8.0+

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

### 更多使用

- `EasyTemplate` 默认开启输出过滤，可用于渲染视图模板
- `TextTemplate` 则是关闭了输出过滤，主要用于文本处理，代码生成等

**配置设置**

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

**输出变量值**

下面的语句一样，都可以用于打印输出变量值

```php
{{ $name }}
{{= $name }}
{{ echo $name }}
```

> 默认会自动通过 `htmlspecialchars` 将输出结果进行处理，除非设置了禁用或者手动使用 `raw` 过滤器

- 设置禁用输出过滤 `$t->disableEchoFilter()`
- 模板中禁用输出过滤 `{{ $name | raw }}`

**快速访问数组值**

可以使用`.` 来快速访问数组值。

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

**模板中添加注释**

以 `{{# XX  #}}` 包裹的内容将会当做注释忽略

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
$tpl->addDirective(
    'include',
    function (string $body, string $name) {
        /** will call {@see EasyTemplate::include()} */
        return '$this->' . $name . $body;
    }
);
```

在模板中使用:

```php

{{ include('part/header.tpl', ['title' => 'My world']) }}

```

## Dep packages

- [toolkit/fsutil](https://github.com/php-toolkit/fsutil)
- [toolkit/stdlib](https://github.com/php-toolkit/stdlib)

## License

[MIT](LICENSE)
