import React from 'react';
import { Menu, Transition } from '@headlessui/react';

interface DropdownProps {
    label: string;
    children: React.ReactNode;
    className?: string;
    menuAlign?: 'left' | 'right';
}

const Dropdown: React.FC<DropdownProps> = ({ label, children, className, menuAlign = 'right' }) => {
    // menuAlign: 'left' aligns menu to left edge of button, 'right' to right edge
    const menuStyle = menuAlign === 'left' ? { left: 0, right: 'auto' } : { right: 0, left: 'auto' };
    return (
        <Menu as="div" className={`relative inline-block text-left ${className || ''}`}>
            <div>
                <Menu.Button className="inline-flex justify-center w-full rounded-md border border-gray-700 shadow-sm px-4 py-2 bg-neutral-700 text-sm font-medium text-white hover:bg-neutral-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
                    {label}
                    <svg className="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fillRule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.584l3.71-3.354a.75.75 0 111.02 1.1l-4.25 3.846a.75.75 0 01-1.02 0l-4.25-3.846a.75.75 0 01.02-1.06z" clipRule="evenodd" />
                    </svg>
                </Menu.Button>
            </div>
            <Transition
                as={React.Fragment}
                enter="transition ease-out duration-100"
                enterFrom="transform opacity-0 scale-95"
                enterTo="transform opacity-100 scale-100"
                leave="transition ease-in duration-75"
                leaveFrom="transform opacity-100 scale-100"
                leaveTo="transform opacity-0 scale-95"
            >
                <Menu.Items style={menuStyle} className="absolute mt-2 w-56 rounded-md shadow-lg bg-neutral-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                    <div className="py-1">{children}</div>
                </Menu.Items>
            </Transition>
        </Menu>
    );
};

export const DropdownItem: React.FC<{ onClick?: () => void; className?: string }> = ({ onClick, children, className }) => (
    <Menu.Item>
        {({ active }) => (
            <button
                type="button"
                className={`$${active ? 'bg-neutral-700 text-white' : 'text-neutral-200'} group flex rounded-md items-center w-full px-4 py-2 text-sm ${className || ''}`}
                onClick={onClick}
            >
                {children}
            </button>
        )}
    </Menu.Item>
);

export default Dropdown;
