import React from 'react';
import { Button } from '@/components/ui/button';

export default function HelloReact() {
    return (
        <div className="p-6 max-w-sm mx-auto bg-white rounded-xl shadow-lg flex items-center space-x-4 mt-10">
            <div className="shrink-0">
                <img className="h-12 w-12" src="https://upload.wikimedia.org/wikipedia/commons/a/a7/React-icon.svg" alt="React Logo" />
            </div>
            <div>
                <div className="text-xl font-medium text-black">Hello from React!</div>
                <p className="text-slate-500 mb-4">React has been successfully integrated into Boostly.</p>
                <Button>Shadcn Button</Button>
            </div>
        </div>
    );
}
