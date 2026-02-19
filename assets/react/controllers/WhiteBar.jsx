import React from 'react';

export default function WhiteBar({ children }) {
    return (
        <div className="bg-white w-full py-[1px] px-[1px] shadow-sm">
            {children}
        </div>
    );
}
