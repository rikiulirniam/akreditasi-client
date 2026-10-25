<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ReviewerService
{
    /**
     * @return array<int, array<string, mixed>>|null
     */
    public function getDummyProjects(): ?array
    {
        $file = resource_path('reviewer-dummy-data.php');

        if (! is_file($file)) {
            return null;
        }

        $projects = require $file;

        if (! is_array($projects) || ! array_is_list($projects) || count(array_filter($projects, 'is_array')) !== count($projects)) {
            throw new \UnexpectedValueException('File data dummy reviewer harus berisi daftar project.');
        }

        return $projects;
    }

    public function getProjects(): Response
    {
        return $this->http()->get($this->apiUrl().'/reviewer/projects');
    }

    protected function http(): PendingRequest
    {
        $request = Http::acceptJson()->timeout(20);
        $token = (string) session('access_token');

        if ($token !== '') {
            $request = $request->withToken($token);
        }

        return $request;
    }

    protected function apiUrl(): string
    {
        $baseUrl = rtrim((string) config('services.akreditasi.base_url'), '/');

        return str_ends_with($baseUrl, '/api') ? $baseUrl : $baseUrl.'/api';
    }
}
