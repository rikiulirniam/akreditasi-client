<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $page = (int) $request->query('page', 1);
        $roleQuery = $request->query('role');
        $params = ['page' => $page];
        if (! empty($roleQuery)) {
            $params['role'] = $roleQuery;
        }
        $response = $this->akademikService->getUserList($params);

        if ($response->status() === 401) {
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Sesi Berakhir',
                'message' => 'Sesi Anda telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $users = $response->json('data') ?? [];
        $pagination = $response->json('pagination') ?? [
            'total' => count($users),
            'per_page' => 10,
            'current_page' => $page,
            'last_page' => 1,
        ];

        $roleResponse = $this->akademikService->getRoleList(['per_page' => 100]);
        $roles = $roleResponse->json('data') ?? [];
        if (empty($roles)) {
            $roles = [
                ['name' => 'ADMIN'],
                ['name' => 'OPERATOR'],
                ['name' => 'KAPRODI'],
                ['name' => 'DEKAN'],
                ['name' => 'DOSEN'],
            ];
        }

        return view('users.index', [
            'users' => $users,
            'pagination' => $pagination,
            'roles' => $roles,
            'selectedRole' => $roleQuery ?? '',
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('users.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $domain = strtolower((string) substr(strrchr((string) $value, '@'), 1));
                    if ($domain !== 'polines.ac.id' && ! str_ends_with($domain, '.polines.ac.id')) {
                        $fail('Hanya email dengan domain *.polines.ac.id yang diizinkan.');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', 'string', 'max:100'],
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.required' => 'Role wajib dipilih.',
        ]);

        $payload = [
            'name' => trim($validated['nama']),
            'nama' => trim($validated['nama']),
            'email' => strtolower(trim($validated['email'])),
            'password' => $validated['password'],
            'role' => strtoupper(trim($validated['role'])),
        ];

        $response = $this->akademikService->createUser($payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? false)) {
            return redirect()->route('users.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $body['message'] ?? 'Pengguna berhasil ditambahkan.',
            ]);
        }

        $errorMessage = $body['message'] ?? 'Gagal menambahkan pengguna.';
        if (! empty($body['errors'])) {
            $firstError = collect($body['errors'])->flatten()->first();
            if ($firstError) {
                $errorMessage = $firstError;
            }
        }

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal',
            'message' => $errorMessage,
        ]);
    }

    public function edit(int|string $id): RedirectResponse
    {
        return redirect()->route('users.index');
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $domain = strtolower((string) substr(strrchr((string) $value, '@'), 1));
                    if ($domain !== 'polines.ac.id' && ! str_ends_with($domain, '.polines.ac.id')) {
                        $fail('Hanya email dengan domain *.polines.ac.id yang diizinkan.');
                    }
                },
            ],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'role' => ['required', 'string', 'max:100'],
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role.required' => 'Role wajib dipilih.',
        ]);

        $payload = [
            'name' => trim($validated['nama']),
            'nama' => trim($validated['nama']),
            'email' => strtolower(trim($validated['email'])),
            'role' => strtoupper(trim($validated['role'])),
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        $response = $this->akademikService->updateUser($id, $payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? false)) {
            return redirect()->route('users.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => $body['message'] ?? 'Pengguna berhasil diperbarui.',
            ]);
        }

        $errorMessage = $body['message'] ?? 'Gagal memperbarui pengguna.';
        if (! empty($body['errors'])) {
            $firstError = collect($body['errors'])->flatten()->first();
            if ($firstError) {
                $errorMessage = $firstError;
            }
        }

        return back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal',
            'message' => $errorMessage,
        ]);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $response = $this->akademikService->deleteUser($id);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? false)) {
            return redirect()->route('users.index')->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Dihapus',
                'message' => $body['message'] ?? 'Pengguna berhasil dihapus.',
            ]);
        }

        return redirect()->route('users.index')->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Menghapus',
            'message' => $body['message'] ?? 'Pengguna gagal dihapus.',
        ]);
    }
}
