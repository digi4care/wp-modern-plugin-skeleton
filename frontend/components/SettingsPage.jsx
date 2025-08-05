import {useEffect} from 'react';

export default function SettingsPage({
    nonce = '',
    actionUrl = '',
    restUrl = '',
    restNonce = ''
}) {
    useEffect(() => {
        const base = restUrl.replace(/\/$/, '');

        // Example REST request
        fetch(`${base}/xpub/v1/example`, {
            headers: { 'X-WP-Nonce': restNonce },
        });

        // Example AJAX request
        const formData = new FormData();
        formData.append('action', 'xpub_dummy_action');
        formData.append('_wpnonce', nonce);
        fetch(actionUrl, {
            method: 'POST',
            body: formData,
        });
    }, [nonce, actionUrl, restUrl, restNonce]);

    return <div>Dummy Settings Page</div>;
}
