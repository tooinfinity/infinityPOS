<?php

declare(strict_types=1);

namespace App\Http\Controllers\Clients;

use App\Actions\Clients\CreateClient;
use App\Actions\Clients\DeleteClient;
use App\Actions\Clients\UpdateClient;
use App\Data\Clients\ClientData;
use App\Data\Clients\CreateClientData;
use App\Data\Clients\UpdateClientData;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class ClientController
{
    public function index(): Response
    {
        $clients = Client::query()
            ->with('creator')
            ->latest()
            ->paginate(50);

        return Inertia::render('clients/index', [
            'clients' => ClientData::collect($clients),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('clients/create');
    }

    public function store(CreateClientData $data, CreateClient $action): RedirectResponse
    {
        $action->handle($data);

        return to_route('clients.index');
    }

    public function show(Client $client): Response
    {
        $client->load('creator');

        return Inertia::render('clients/show', [
            'client' => ClientData::from($client),
        ]);
    }

    public function edit(Client $client): Response
    {
        return Inertia::render('clients/edit', [
            'client' => ClientData::from($client),
        ]);
    }

    public function update(UpdateClientData $data, Client $client, UpdateClient $action): RedirectResponse
    {
        $action->handle($client, $data);

        return back();
    }

    public function destroy(Client $client, DeleteClient $action): RedirectResponse
    {
        $action->handle($client);

        return to_route('clients.index');
    }
}
