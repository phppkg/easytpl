{{ block 'header' }}
on layout: block header - name: {{ info.name }}.
{{ endblock }}

this is an layout file.

{{ block 'body' }}
    on layout: block body - age: {{ info.age }}.
{{ endblock }}

{{ block 'footer' }}
    on layout: block footer - city: {{ info.city }}.
{{ endblock }}

