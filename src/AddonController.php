<?php

namespace Pterodactyl\BlueprintFramework\Extensions\mcmanager;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\BlueprintFramework\Extensions\mcmanager\SpigotProvider;
use Pterodactyl\BlueprintFramework\Extensions\mcmanager\ModrinthProvider;
use Pterodactyl\BlueprintFramework\Extensions\mcmanager\PaperMCProvider;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Admin\BlueprintAdminLibrary as BlueprintExtensionLibrary;
use Pterodactyl\Repositories\Wings\DaemonFileRepository;
use Pterodactyl\Models\Server;

class AddonController extends Controller
{
    public function __construct(
        private BlueprintExtensionLibrary $blueprint,
        private DaemonFileRepository $fileRepository,
    ) {}

    protected function getProvider($provider)
    {
        switch (strtolower($provider)) {
            case 'modrinth':
                return new ModrinthProvider();
            case 'papermc':
                return new PaperMCProvider();
            case 'spigot':
            default:
                return new SpigotProvider();
        }
    }
    // List all installed addons
    public function index()
    {
        return response()->json(DB::table('installed_addons')->get());
    }

    // Search addons from a provider
    public function search(Request $request)
    {
        $query = $request->input('query', '');
        $page = $request->input('page', 1);
        $providerName = $request->input('provider', 'spigot');
        $provider = $this->getProvider($providerName);
        $results = $provider->searchAddons($query, $page);
        return response()->json($results);
    }

    // Download and install an addon from a provider
    public function store(Request $request, Server $server)
    {
        $data = $request->validate([
            'uuid' => 'required|string',
            'version' => 'required|string',
            'provider' => 'required|string',
            'resource_id' => 'required|string',
        ]);

        // Download file from provider
        $provider = $this->getProvider($data['provider']);
        $fileContents = $provider->downloadAddon($data['resource_id']);
        if (!$fileContents) {
            return response()->json(['success' => false, 'error' => 'Failed to download file'], 400);
        }

        // Store file using DaemonFileRepository
        $fileName = $data['uuid'] . '-' . $data['version'] . '.jar';
        $filePath = 'plugins/' . $fileName;
        $this->fileRepository->setServer($server)->putContent($filePath, $fileContents);

        // Calculate file hash
        $fileHash = hash('sha256', $fileContents);

        // Save to DB
        DB::table('installed_addons')->updateOrInsert(
            ['uuid' => $data['uuid']],
            [
                'version' => $data['version'],
                'provider' => $data['provider'],
                'file_hash' => $fileHash,
                'file_path' => $filePath,
            ]
        );

        return response()->json(['success' => true, 'fileHash' => $fileHash, 'filePath' => $filePath]);
    }

    // Update an existing addon record (re-download file)
    public function update(Request $request, Server $server, $uuid)
    {
        $data = $request->validate([
            'version' => 'required|string',
            'resource_id' => 'required|string',
            'provider' => 'required|string',
        ]);

        $provider = $this->getProvider($data['provider']);
        $fileContents = $provider->downloadAddon($data['resource_id']);
        if (!$fileContents) {
            return response()->json(['success' => false, 'error' => 'Failed to download file'], 400);
        }

        $fileName = $uuid . '-' . $data['version'] . '.jar';
        $filePath = 'plugins/' . $fileName;
        $this->fileRepository->setServer($server)->putContent($filePath, $fileContents);
        $fileHash = hash('sha256', $fileContents);

        DB::table('installed_addons')->where('uuid', $uuid)->update([
            'version' => $data['version'],
            'file_hash' => $fileHash,
            'file_path' => $filePath,
        ]);
        return response()->json(['success' => true, 'fileHash' => $fileHash, 'filePath' => $filePath]);
    }

    // Remove an addon and its file
    public function destroy($uuid)
    {
        $addon = DB::table('installed_addons')->where('uuid', $uuid)->first();
        if ($addon && isset($addon->file_path)) {
            Storage::disk('local')->delete($addon->file_path);
        }
        DB::table('installed_addons')->where('uuid', $uuid)->delete();
        return response()->json(['success' => true]);
    }
}
