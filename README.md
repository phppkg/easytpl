# EasyTpl

[![License](https://img.shields.io/github/license/phppkg/easytpl.svg?style=flat-square)](LICENSE)
[![Php Version](https://img.shields.io/badge/php-%3E=8.0-brightgreen.svg?maxAge=2592000)](https://packagist.org/packages/phppkg/easytpl)
[![GitHub tag (latest SemVer)](https://img.shields.io/github/tag/phppkg/easytpl)](https://github.com/phppkg/easytpl)
[![Actions Status](https://github.com/phppkg/easytpl/workflows/Unit-Tests/badge.svg)](https://github.com/phppkg/easytpl/actions)

⚡️ Simple and fastly template engine for PHP

> **[中文说明](README.zh-CN.md)**

## Features

- It's simple, lightweight and fastly. 
  - It is simply processed and converted into native PHP syntax
  - Compatible with PHP native syntax
- support simple echo print syntax. eg: `{{= $var }}` `{{ $var }}` `{{ echo $var }}`
- support chained access array value. eg: `{{ $arr.0 }}` `{{ $map.name }}` `{{ $map.user.name }}`
- support php builtin function as filters. eg: `{{ $var | ucfirst }}`
- support all control syntax. such as `if,elseif,else;foreach;for;switch`
- support add custom filters.
  - default builtin filters: `upper` `lower` `nl`
- support add custom directive.

## Install

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
{{ foreach($tags => $tag) }}
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

### More usage

**config template**

```php
use PhpPkg\EasyTpl\EasyTemplate;

$t = EasyTemplate::new([
    'tplDir'   => 'path/to/templates',
    'allowExt' => ['.php', '.tpl'],
]);

// do something ...
```

**chained access array**

Can use `.` to quick access array value.

```php
$arr = [
    'val0',
    'subKey' => 'val1',
];
```

Use in template:

```php
First value is: {{ $arr.0 }}
'subKey' value is: {{ $arr.subKey }}
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
{{ 'john' | ucfirst }} // John
```

**Chained usage**:

```php
{{ 'john' | ucfirst | substr:0,1 }} // J
{{ '1999-12-31' | date:'Y/m/d' }} // 1999/12/31
```

**Passing non-static values**:

```php
{{ $name | ucfirst | substr:0,1 }}
{{ $user['name'] | ucfirst | substr:0,1 }}
{{ $userObj->name | ucfirst | substr:0,1 }}
{{ getName() | ucfirst | substr:0,1 }}
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

{{ $name | upper }} // Output: INHERE
{{ $name | last3chars }} // Output: ere
{{ $name | last3chars | upper }} // Output: ERE
```

## Custom directives

You can use the directives implement some special logic.

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

Use in template:

```php

{{ include('part/header.tpl', ['title' => 'My world']) }}

```

## Dep packages

- [toolkit/fsutil](https://github.com/php-toolkit/fsutil)
- [toolkit/stdlib](https://github.com/php-toolkit/stdlib)

## License

[MIT](LICENSE)
