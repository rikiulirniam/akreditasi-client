@props([
    'id' => 'confirmDeleteModal',
    'title' => 'Hapus Akun Pengguna?',
    'message' => 'Apakah Anda yakin ingin menghapus akun pengguna ini? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan.',
    'action' => '#',
])

<x-modal.confirm-delete :id="$id" :title="$title" :message="$message" :action="$action" {{ $attributes }} />
