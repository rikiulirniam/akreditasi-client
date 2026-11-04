@extends('layouts.app')

@section('meta_title', 'Perbarui Kegiatan')
@section('title', 'Penelitian & PKM')

@section('subtitle')
    <a href="{{ route('penelitian-pkm.index') }}" class="hover:underline">Penelitian</a> &rsaquo; Perbarui Kegiatan
@endsection

@section('content')
    @include('penelitian-pkm._form', [
        'judul' => 'Perbarui Penelitian',
        'action' => route('penelitian-pkm.update', $kegiatan['id_kegiatan']),
        'method' => 'PUT',
    ])
@endsection