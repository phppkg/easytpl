# EasyTpl

[![License](https://img.shields.io/github/license/phppkg/easytpl.svg?style=flat-square)](LICENSE)
[![Php Version](https://img.shields.io/badge/php-%3E=8.0-brightgreen.svg?maxAge=2592000)](https://packagist.org/packages/phppkg/easytpl)
[![GitHub tag (latest SemVer)](https://img.shields.io/github/tag/phppkg/easytpl)](https://github.com/phppkg/easytpl)
[![Actions Status](https://github.com/phppkg/easytpl/workflows/Unit-Tests/badge.svg)](https://github.com/phppkg/easytpl/actions)
[![zh-CN readme](https://img.shields.io/badge/中文-Readme-brightgreen.svg?style=for-the-badge&maxAge=2592000)](README.zh-CN.md)

⚡️ Simple and fastly template engine for PHP.

## Features

- It's simple, lightweight and fastly.
  - No learning costs, syntax like PHP template
  - It is simply processed and converted into native PHP syntax
  - Compatible with PHP native syntax
- support simple echo print syntax. eg: `{{ var }}` `{{= $var }}` `{{ $var }}` `{{ echo $var }}`
  - allow ignore prefix `$`, will auto append on compile.
- support chained access array value. eg: `{{ $arr.0 }}` `{{ $map.name }}` `{{ $map.user.name }}`
- support all control syntax. such as `if,elseif,else;foreach;for;switch`
- support php builtin function as filters. eg: `{{ $var | ucfirst }}`  `{{ date('Y-m-d') }}`
- More secure, the output will be processed automatically through `htmlspecialchars` by default
  - You can set to disable output filtering or manually use the `raw` filter
- support add custom filters.
  - default builtin filters: `upper` `lower` `nl`
- support add custom directive.
  - `EasyTemplate` built in support `layout` `include` `contents`
  - `ExtendTemplate` built in support `extends` `block` `endblock`
- support comments in templates. eg: `{{# comments ... #}}`

## Install

- Required PHP 8.0+

**composer**

```bash
composer require phppkg/easytpl
```

## Quick start

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

**Output**:

```text
My name is INHERE,
My develop tags:

- php
- go
- java
```

## More usage

The syntax is the same as the PHP native template, and the special syntax added is just to make it more convenient to use.

- `EasyTemplate` enables output filtering by default, which can be used to render view templates.
- `TextTemplate` turns off output filtering and is mainly used for text processing, code generation, etc.

### Config template

```php
use PhpPkg\EasyTpl\EasyTemplate;

$t = EasyTemplate::new([
    'tplDir'   => 'path/to/templates',
    'allowExt' => ['.php', '.tpl'],
]);

// do something ...
```

more settings：

```php
/** @var PhpPkg\EasyTpl\EasyTemplate $t */
$t->disableEchoFilter();
$t->addFilter($name, $filterFn);
$t->addFilters([]);
$t->addDirective($name, $handler);
```

### Echo variable

The following statements are the same, can be used to print out variable values

```php
{{ name }}
{{ $name }}
{{= $name }}
{{ echo $name }}
```

More:

```php
{{ $name ?: 'inhere' }}
{{ $age > 20 ? '20+' : '<= 20' }}
```

> By default, the output result will be automatically processed through `htmlspecialchars`,
> unless disabled or manually used `raw` filter

- Set to disable output filtering `$t->disableEchoFilter()`
- Disable output filtering in the template `{{ $name | raw }}`

### Chained access array

Can use `.` to quick access array value.

```php
$arr = [
    'val0',
    'subKey' => 'val1',
];
```

Use in template:

```php
First value is: {{ $arr.0 }} // val0
'subKey' value is: {{ $arr.subKey }} // val1
```

### If blocks

only `if`:

```php
{{ if ($name !== '') }}
hi, my name is {{ $name }}
{{ endif }}
```

`if else`:

```php
hi, my name is {{ $name }}
age is {{ $age }}, and
{{ if ($age >= 20) }}
 age >= 20.
{{ else }}
 age < 20.
{{ endif }}
```

`if...elseif...else`:

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

### For/Foreach blocks

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

### Add comments

The contents wrapped with `{{` and `}}` will be ignored as comments.

```php
{{# comments ... #}}{{ $name }} // inhere
```

multi lines:

```php
{{#
 this
 comments
 block
#}}{{ $name }} // inhere
```

## Use Filters

Default built-in filters: 

- `upper` - equals `strtoupper`
- `lower` - equals `strtolower`
- `nl` - append newline `\n`

### Using the filters

You can use the filters in any of your templates.

**Regular usage**:

```php
{{ 'inhere' | ucfirst }} // Inhere 
{{ 'inhere' | upper }} // INHERE
```

**Chained usage**:

```php
{{ 'inhere' | ucfirst | substr:0,2 }} // In
{{ '1999-12-31' | date:'Y/m/d' }} // 1999/12/31
```

**Passing non-static values**:

```php
{{ $name | ucfirst | substr:0,1 }}
{{ $user['name'] | ucfirst | substr:0,1 }}
{{ $userObj->name | ucfirst | substr:0,1 }}
{{ $userObj->getName() | ucfirst | substr:0,1 }}
```

**Passing variables as filter parameters**:

```php
{{
    $suffix = '￥';
}}

{{ '12.75' | add_suffix:$suffix }} // 12.75￥
```

### Custom filters

```php
use PhpPkg\EasyTpl\EasyTemplate;

$tpl = EasyTemplate::new();
// use php built function
$tpl->addFilter('upper', 'strtoupper');

// add more
$tpl->addFilters([
    'last3chars' => function (string $str): string {
        return substr($str, -3);
    },
]);
```

Use in template:

```php
{{
  $name = 'inhere';
}}

{{ $name | upper }} // INHERE
{{ $name | last3chars }} // ere
{{ $name | last3chars | upper }} // ERE
```

## Custom directives

You can use the directives implement some special logic.

>`EasyTemplate` built in support: `layout` `include` `contents`

```php
$tpl = EasyTemplate::new();
$tpl->addDirective(
    'include',
    function (string $body, string $name) {
        /** will call {@see EasyTemplate::include()} */
        return '$this->include' . $body;
    }
);
```

### Use layout

- page template `home01.tpl`

```php
{{ layout('layouts/layout01.tpl') }}

on home: block body;
```

### Use include

**Use in template**

```php
{{ include('part/header.tpl', ['title' => 'My world']) }}
```

## Extends template

New directives:

- `extends` extends a layout template file.
  - syntax: `{{ extends('layouts/main.tpl') }}`
- `block` define a new template block start.
  - syntax: `{{ block 'header' }}`
- `endblock` mark a block end.
  - syntax: `{{ endblock }}`

```php
use PhpPkg\EasyTpl\ExtendTemplate;

$et = new ExtendTemplate();
$et->display('home/index.tpl');
```

### Examples for extend

- on layout file: `layouts/main.tpl`

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

- on page file: `home/index.tpl`

```php
{{ extends('layouts/main.tpl') }}

{{ block 'body' }}
body contents in home index.
{{ endblock }}
```

**Rendered results**

```text
header contents in layout main.
body contents in home index.
footer contents in layout main.
```

## Dep packages

- [toolkit/fsutil](https://github.com/php-toolkit/fsutil)
- [toolkit/stdlib](https://github.com/php-toolkit/stdlib)

## Related

- https://github.com/twigphp/Twig
- https://github.com/nette/latte
- https://github.com/jenssegers/blade
- https://github.com/fenom-template/fenom
- https://github.com/pug-php/pug

## License

[MIT](LICENSE)
