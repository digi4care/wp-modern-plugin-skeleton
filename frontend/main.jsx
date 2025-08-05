import React from 'react';
import {createRoot} from 'react-dom/client';
import App from './App.jsx';
import './index.css';

document.addEventListener('DOMContentLoaded', () => {
    const rootElement = document.getElementById('xpub-settings-root');
    if (rootElement) {
        const root = createRoot(rootElement);
        root.render(
            <React.StrictMode>
                <App/>
            </React.StrictMode>
        );
    } else {
        console.error('React root element not found');
    }
});
