import React from 'react';

import Pagination from '@/components/elements/Pagination';
import { PaginatedResult } from '@/api/http';

export type Addon = {
    uuid: string;
    addonName: string;
    addonDescription?: string;
    addonVersion?: string;
    addonVersionId: string;
    addonAuthor?: string;
    iconURL?: string;
    installed?: boolean;
};



type AddonPaginationProps = {
    data: PaginatedResult<Addon>;
    onPageSelect: (page: number) => void;
    renderTile: (addon: Addon) => React.ReactNode;
};

const AddonPagination: React.FC<AddonPaginationProps> = ({ data, onPageSelect, renderTile }) => (
    <Pagination data={data} onPageSelect={onPageSelect}>
        {({ items }) => (
            <div style={{ display: 'grid', gap: 16 }}>
                {items.map((addon, idx) => renderTile(addon))}
            </div>
        )}
    </Pagination>
);

export default AddonPagination;
