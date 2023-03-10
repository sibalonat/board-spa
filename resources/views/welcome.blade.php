<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf_token" content="{{ csrf_token() }}">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">


        <style>
            body {
                font-family: 'Nunito', sans-serif;
            }
        </style>
        @routes

        @vite(['resources/js/app.js', 'resources/sass/app.scss'])
    </head>
    <body class="antialiased">
        <div class="wrapper">
            <div id="app">
                <kanban-component />
            </div>
        </div>
    </body>
</html>
