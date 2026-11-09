@extends('layouts.app')

@section('meta_title', 'Tambah Kegiatan')
@section('title', 'Penelitian & PKM')

@section('subtitle')
    <a href="{{ route('penelitian-pkm.index') }}" class="hover:underline">Penelitian</a> &rsaquo; Tambah Kegiatan
@endsection

@section('content')
    @include('penelitian-pkm._form', [
        'judul' => 'Tambah Kegiatan',
        'action' => route('penelitian-pkm.store'),
        'method' => 'POST',
        'kegiatan' => [],
    ])
@endsection