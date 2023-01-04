{{ extends('layouts/layout1.tpl', ['info' => $info,]) }}

{{# will replace to layout.body block #}}
{{ block 'body' }}
on home: block body - age: {{ info.age }}.
{{ endblock }}

{{ block 'footer' }}
on home: block footer - city: {{ info.city }}.
{{ endblock; }}
