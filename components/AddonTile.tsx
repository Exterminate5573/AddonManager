
import React from 'react';
import GreyRowBox from '@/components/elements/GreyRowBox';
import Button from '@/components/elements/Button';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faDownload, faSyncAlt, faTrash } from '@fortawesome/free-solid-svg-icons';

type AddonTileProps = {
    uuid: string;
    addonName: string;
    addonDescription?: string;
    addonVersion?: string;
    addonVersionId: string;
    addonAuthor?: string;
    iconURL?: string;
    installed?: boolean;
    upToDate?: boolean;
    onClick?: () => void;
    onDownload?: () => void;
    onUpdate?: () => void;
    onDelete?: () => void;
};

const AddonTile: React.FC<AddonTileProps> = ({ addonName, addonDescription, addonVersion, addonAuthor, iconURL, installed, upToDate, onClick, onDownload, onUpdate, onDelete }) => (
    <GreyRowBox $hoverable={!!onClick} onClick={onClick} style={{ cursor: onClick ? 'pointer' : undefined }}>
        {/* Icon */}
        <div style={{ width: 48, height: 48, marginRight: 16, display: 'flex', alignItems: 'center', justifyContent: 'center', background: '#444', borderRadius: 8 }}>
            {iconURL ? (
                <img src={iconURL} alt={addonName + ' icon'} style={{ width: 32, height: 32, objectFit: 'contain' }} />
            ) : (
                <FontAwesomeIcon icon={faDownload} style={{ color: '#888', fontSize: 24 }} />
            )}
        </div>
        {/* Info */}
        <div style={{ flex: 1 }}>
            <div style={{ fontWeight: 'bold', fontSize: '1.1em' }}>{addonName}</div>
            {addonDescription && <div style={{ color: '#bbb', fontSize: '0.95em', marginTop: 2 }}>{addonDescription}</div>}
            <div style={{ fontSize: '0.85em', marginTop: 4, color: '#888' }}>
                {addonVersion && <>Version: {addonVersion} </>}
                {addonAuthor && <>by {addonAuthor}</>}
            </div>
        </div>
        {/* Actions */}
        <div style={{ display: 'flex', gap: 8, marginLeft: 16 }}>
            {installed ? (
                <>
                    <Button
                        size={'xsmall'}
                        color={upToDate ? 'grey' : 'primary'}
                        style={!upToDate ? { fontWeight: 'bold', borderWidth: 2, borderColor: '#2563eb' } : {}}
                        onClick={e => { e.stopPropagation(); onUpdate && onUpdate(); }}
                        title="Update"
                    >
                        <FontAwesomeIcon icon={faSyncAlt} />
                    </Button>
                    <Button size={'xsmall'} color={'red'} onClick={e => { e.stopPropagation(); onDelete && onDelete(); }} title="Delete">
                        <FontAwesomeIcon icon={faTrash} />
                    </Button>
                </>
            ) : (
                <Button size={'xsmall'} color={'primary'} onClick={e => { e.stopPropagation(); onDownload && onDownload(); }} title="Download">
                    <FontAwesomeIcon icon={faDownload} />
                </Button>
            )}
        </div>
    </GreyRowBox>
);

export default AddonTile;
