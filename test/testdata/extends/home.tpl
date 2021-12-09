
{{ extends('layouts/layout.tpl') }}

{{ block 'body' }}
on home: block body;
{{ endblock; }}

{{ block 'footer' }}
on home: block footer;
{{ endblock; }}
