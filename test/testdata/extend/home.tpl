{{ extends('layouts/layout.tpl') }}

{{# will replace to layout.body block #}}
{{ block 'body' }}
on home: block body;
{{ endblock }}

{{ block 'footer' }}
on home: block footer;
{{ endblock; }}
