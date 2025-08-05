import SettingsPage from './components/SettingsPage.jsx';

export default function App() {
    const {
        nonce = '',
        actionUrl = '',
        restUrl = '',
        restNonce = ''
    } = window.xpubSettings || {};

    return (
        <SettingsPage
            nonce={nonce}
            actionUrl={actionUrl}
            restUrl={restUrl}
            restNonce={restNonce}
        />
    );
}
