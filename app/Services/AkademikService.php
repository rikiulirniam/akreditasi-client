<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class AkademikService
{
    protected string $baseUrl;

    public function __construct(?string $baseUrl = null)
    {
        $this->baseUrl = rtrim($baseUrl ?? (string) config('services.akademik.base_url', 'https://akademik.fayra.my.id/api'), '/');
    }

    /**
     * Get the base URL of the Akademik API.
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Send login request to Akademik API.
     *
     * @param  string|array<string, mixed>  $emailOrCredentials
     */
    public function login(string|array $emailOrCredentials, ?string $password = null): Response
    {
        if (is_array($emailOrCredentials)) {
            $email = (string) ($emailOrCredentials['email'] ?? $emailOrCredentials['username'] ?? '');
            $password = (string) ($emailOrCredentials['password'] ?? $password ?? '');
        } else {
            $email = $emailOrCredentials;
            $password = (string) $password;
        }

        return Http::acceptJson()
            ->asJson()
            ->timeout(15)
            ->post("{$this->baseUrl}/auth/login", [
                'email' => $email,
                'password' => $password,
            ]);
    }
}
