
// Secure API-based addon management
const apiURL = "/api/application/extensions/{identifier}";

export type InstalledAddon = {
    uuid: string;
    version: string;
    provider: string;
    friendly_name: string;
    friendly_version: string;
    fileHash: string;
    file_path?: string;
};

export async function getAllInstalledAddons(serverUuid: string): Promise<InstalledAddon[]> {
    const res = await fetch(`${apiURL}/server/${encodeURIComponent(serverUuid)}/`);
    return await res.json();
}

// Search for addons using backend provider abstraction
export async function searchAddons(serverUuid: string, query: string, page: number = 1, provider: string = 'spigot') {
    const res = await fetch(`${apiURL}/server/${encodeURIComponent(serverUuid)}/search?query=${encodeURIComponent(query)}&page=${page}&provider=${encodeURIComponent(provider)}`);
    return await res.json();
}

function getCsrfToken(): string {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return (meta && meta.getAttribute('content')) || '';
}

// Install an addon by resource_id (backend handles download)
export async function installAddon(serverUuid: string, { uuid, provider }: { uuid: string, provider: string }) {
    await fetch(`${apiURL}/server/${encodeURIComponent(serverUuid)}/`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify({ uuid, provider }),
    });
}

// Update an addon (re-download)
export async function updateAddon(serverUuid: string, uuid: string, provider: string) {
    await fetch(`${apiURL}/server/${encodeURIComponent(serverUuid)}/${uuid}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify({ provider }),
    });
}

export async function removeAddon(serverUuid: string, uuid: string) {
    await fetch(`${apiURL}/server/${encodeURIComponent(serverUuid)}/${uuid}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': getCsrfToken(),
        },
    });
}
