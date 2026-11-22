@extends('layouts.app')

@section('meta_title', 'Tambah Kegiatan')
@section('title', $halaman)

@section('subtitle')
    <a href="{{ route($rute . '.index') }}" class="hover:underline">{{ $halaman }}</a> &rsaquo; Tambah Kegiatan
@endsection

@section('content')
    @include('penelitian-pkm._form', [
        'judul' => 'Tambah Kegiatan',
        'action' => route($rute . '.store'),
        'method' => 'POST',
        'kegiatan' => [],
    ])
@endsection