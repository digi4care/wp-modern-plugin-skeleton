import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import Save from './save';
import './style.scss';

registerBlockType('wp-modern-plugin/example-block', {
  edit: Edit,
  save: Save,
});
