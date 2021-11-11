# EasyTpl

[![License](https://img.shields.io/packagist/l/phppkg/easytpl.svg?style=flat-square)](LICENSE)
[![Php Version](https://img.shields.io/badge/php-%3E=8.0-brightgreen.svg?maxAge=2592000)](https://packagist.org/packages/phppkg/easytpl)
[![GitHub tag (latest SemVer)](https://img.shields.io/github/tag/phppkg/easytpl)](https://github.com/phppkg/easytpl)
[![Actions Status](https://github.com/phppkg/easytpl/workflows/Unit-Tests/badge.svg)](https://github.com/phppkg/easytpl/actions)

⚡️ Simple and fastly template engine for PHP

## Features

- it's simple and fastly
- simple echo syntax. eg: `{{= $var }}` `{{ $var }}` `{{ echo $var }}`
- chained access array value syntax. eg: `{{ $arr.0 }}` `{{ $map.name }}` `{{ $map.user.name }}`
- support php builtin string function as filters. eg: `{{ $var | ucfirst }}`
- support all control syntax. such as `if,elseif,else;foreach;for;switch`
- support add custom filters.
- support add custom directive.

## Install

**composer**

```bash
composer require phppkg/easytpl
```

## Quick start

```php
use PhpPkg\EasyTpl\EasyTemplate;

$t = new EasyTemplate();
$t->renderString($tplCode);
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

In template can use:

```php

{{ include('part/header.tpl', ['title' => 'My world']) }}

```

### Filters

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
{{ $currentUser->name | ucfirst | substr:0,1 }}
{{ getName() | ucfirst | substr:0,1 }}
```

**Passing variables as filter parameters**:

```php
{{
    $suffix = '￥';
}}

{{ '12.75' | add_suffix:$suffix }} // 12.75￥
```

**Built-in functionality**:

```php
{{ 'This is a title' | slug }} // this-is-a-title
{{ 'This is a title' | title }} // This Is A Title
{{ 'foo_bar' | studly }} // FooBar
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

## License

[MIT](LICENSE)
