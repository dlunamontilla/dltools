# Sintaxis de las plantillas

## Directivas

Ya disponibles las siguientes directivas en nuestro proyecto:

La directiva `@base` permite tener una vista como principal, aquella que podríamos utilizar en todo el proyecto.:

```blade
@base('nombre-de-la-vista')
```

Es el equivalente a la directiva `@extends` de Laravel.

La directiva `@section` y `@endsection` permite crear contenido que puede ser recuparada más tarde en la vista principal, que se invoca desde la directiva `@base`:

```blade
@section('nombre_de_la_seccion') y @endsection
```

La directiva `@print` permite recuperar el contenido definido en una sección creada con la directiva `@section('seccion')`:

```blade
@print('nombre_de_la_seccion')
```

También, la herramienta cuenta con dos tipos de sintaxis:

- `{{ $variable }}`: Muestra en pantalla la información contenida en la variable $variable con escapado de entidades HTML.

- `{!! $variable !!}`: Exactamente lo mismo que en el caso anterior, pero con la diferencia de que el código HTML se escapa, se interpreta.

### Otras directivas

- `@foreach()` y `@endforeach`: Estas directivas permiten iterar un array.

- `@for ()` y `@endfor`: Estas directiva permite iterar una cantidad de veces determinada en función de lo que ha definido el usuario programador.

- `@if @endif`, `@if@else @endif`, `@if @elseif @else @endif`: Estas directivas permiten definir estructuras condicionales.

- `@php` y `@endphp`: Esta directiva permite indicarle a la plantilla que el código fuente es PHP.

- `@json($array)`: Esta directiva permite devolver un array en formato JSON, pero con caracteres escapados y compactado.

- `@json($array, 'pretty')`: Esta directiva permite devolver un array en formato JSON, pero debidamente formateado sin escapar.
