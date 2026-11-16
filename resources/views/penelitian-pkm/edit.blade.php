@extends('layouts.app')

@section('meta_title', 'Perbarui Kegiatan')
@section('title', $halaman)

@section('subtitle')
    <a href="{{ route($rute . '.index') }}" class="hover:underline">{{ $halaman }}</a> &rsaquo; Perbarui Kegiatan
@endsection

@section('content')
    @include('penelitian-pkm._form', [
        'judul' => 'Perbarui ' . $halaman,
        'action' => route($rute . '.update', $kegiatan['id_kegiatan']),
        'method' => 'PUT',
    ])
@endsection