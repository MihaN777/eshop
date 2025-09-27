<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Page 2</title>
</head>

<body class="antialiased">
    <h1>flash message: {{ flash()->get()?->message()  }}</h1>
    <h1>flash class: {{ flash()->get()?->class()  }}</h1>
</body>
</html>
