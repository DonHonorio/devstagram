@extends('layouts.app')

@section('titulo')
    Página Princial
@endsection

@section('contenido')
    <x-listar-post :posts="$posts"/>
@endsection