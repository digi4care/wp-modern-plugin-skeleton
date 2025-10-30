import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

const Save = ({ attributes }) => {
  const { message = 'Hello from the frontend!' } = attributes;

  return (
    <div {...useBlockProps.save()}>
      <p className="example-message">{message}</p>
    </div>
  );
};

export default Save;
