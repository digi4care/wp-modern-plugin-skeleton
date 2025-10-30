import {setLocaleData} from '@wordpress/i18n';
import './main.jsx';

const locale  = window ? .xpubSettings ? .locale || 'en_US';
const domain  = 'xpub-multi-channel-publisher';
const baseUrl = window ? .xpubSettings ? .translationsBaseUrl || '';

(async() => {
	try {
		const response = await fetch( `${baseUrl} / ${locale}.json` );
		if ( ! response.ok) {
			throw new Error( `Translation for ${locale} not found` );
		}
		const json = await response.json();

		const messages = json ? .locale_data ? .messages;
		if (messages) {
			setLocaleData( messages, domain );
		} else {
			console.warn( 'No locale_data.messages found!' );
		}
	} catch (err) {
		console.warn( `Could not load translation for locale "${locale}".`, err );
	}
})();
