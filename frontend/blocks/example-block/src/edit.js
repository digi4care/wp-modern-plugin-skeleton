import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';

const Edit = ({ attributes, setAttributes }) => {
  const { message = 'Hello from the editor!' } = attributes;

  return (
    <div {...useBlockProps()}>
      <TextControl
        label={__('Message', 'wp-modern-plugin')}
        value={message}
        onChange={(value) => setAttributes({ message: value })}
      />
      <p>{message}</p>
    </div>
  );
};

export default Edit;
