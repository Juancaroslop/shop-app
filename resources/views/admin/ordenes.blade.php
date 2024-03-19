@extends('adminlte::page')

@section('title', 'Productos')

@section('content_header')
    <h1>Gestión de ordenes</h1>
@stop

@section('content')
    <div id="app">
        <ordenes-component></ordenes-component>
    </div>
@stop

@section('css')
    <!-- <link rel="stylesheet" href="/css/admin_custom.css"> -->
    @vite('resources/css/app.css')
@stop

@section('js')
    <!-- <script> console.log('Hi!'); </script> -->
    @vite('resources/js/app.js')
@stop