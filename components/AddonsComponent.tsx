import React, { useState } from 'react';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import Dropdown, { DropdownItem } from './Dropdown';
import Input from '@/components/elements/Input';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import AddonTile from './AddonTile';
import AddonPagination, { Addon } from './AddonPagination';
import { getAllInstalledAddons, searchAddons, installAddon, updateAddon, removeAddon } from './AddonDatabase';
import { useEffect, useCallback } from 'react';
import { ServerContext } from '@/state/server';

// TODO: Hide if not a minecraft servers
const AddonsComponent = () => {

  const serverId = ServerContext.useStoreState((state) => state.server.data!.uuid);

  // Provider selection
  const providers = [
    { id: 'spigot', name: 'Spigot' },
    { id: 'modrinth', name: 'Modrinth' },
    // Add more providers as needed
  ];
  const [selectedProvider, setSelectedProvider] = useState(providers[0]);
  const [query, setQuery] = useState('');
  const [serverType, setServerType] = useState('paper');
  const [serverVersion, setServerVersion] = useState('1.20.1');

  // For dropdown display
  const serverTypes = ['paper', 'purpur', 'spigot', 'bukkit', 'fabric', 'forge', 'velocity'];
  const serverVersions = ['1.20.1', '1.19.4', '1.18.2', '1.17.1'];

  const [page, setPage] = useState(1);
  const [addons, setAddons] = useState<Addon[]>([]);
  const [pagination, setPagination] = useState({ currentPage: 1, totalPages: 1, total: 0, count: 0, perPage: 10 });
  const [loading, setLoading] = useState(false);

  const fetchAddons = useCallback(async () => {
    setLoading(true);
    try {
      const result = await searchAddons(serverId, query, page, selectedProvider.id);
      setAddons(result.items || []);
      setPagination(result.pagination || { currentPage: 1, totalPages: 1, total: 0, count: 0, perPage: 10 });
    } finally {
      setLoading(false);
    }
  }, [query, page, selectedProvider]);

  useEffect(() => {
    fetchAddons();
  }, [fetchAddons]);

  return (
    <ServerContentBlock title={'Addons'}>
      <div tw="md:flex">
        <div tw="w-full md:flex-1 md:mr-10">
          <TitledGreyBox title={'Search'} tw="mb-6 md:mb-10">
            <div tw="flex gap-4">
              <Dropdown label={selectedProvider.name} className="flex-1" menuAlign="left">
                {providers.map(p => (
                  <DropdownItem key={p.id} onClick={() => setSelectedProvider(p)}>{p.name}</DropdownItem>
                ))}
              </Dropdown>
              <Dropdown label={serverType ? serverType.charAt(0).toUpperCase() + serverType.slice(1) : 'Select Type'} className="flex-1" menuAlign="left">
                {serverTypes.map(t => (
                  <DropdownItem key={t} onClick={() => setServerType(t)}>{t.charAt(0).toUpperCase() + t.slice(1)}</DropdownItem>
                ))}
              </Dropdown>
              <Dropdown label={serverVersion} className="flex-1" menuAlign="left">
                {serverVersions.map(v => (
                  <DropdownItem key={v} onClick={() => setServerVersion(v)}>{v}</DropdownItem>
                ))}
              </Dropdown>
              <Input
                type="text"
                value={query}
                onChange={e => setQuery(e.target.value)}
                placeholder="Search for addons..."
                className="flex-1"
              />
            </div>
          </TitledGreyBox>
          <div style={{ marginTop: 24 }}>
            <AddonPagination
              data={{ items: addons, pagination }}
              onPageSelect={setPage}
              renderTile={(addon: Addon) => {
                return (
                  <AddonTile
                    key={addon.uuid}
                    {...addon}
                    onDownload={() => installAddon(serverId, { uuid: addon.uuid, provider: addon.provider })}
                    onUpdate={() => updateAddon(serverId, addon.uuid, addon.provider)}
                    onDelete={() => removeAddon(serverId, addon.uuid)}
                  />
                );
              }}
            />
          </div>
        </div>
      </div>
    </ServerContentBlock>
  );
};

export default AddonsComponent;