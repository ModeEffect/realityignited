import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, Disabled, ToggleControl, TextControl, SelectControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
const { downloads, categories, tags } = mpdlBlocks;

export default function Edit({ attributes, setAttributes }) {
  const blockProps = useBlockProps();
  const {
    download,
    isList,
    listBy,
    limit,
    category,
    tag,
    showDescription
  } = attributes;

  return (
    <div {...blockProps}>
      <InspectorControls>
        <PanelBody
          title={ __('MemberPress Download', 'memberpress-downloads') }
          description={__(
            'Display a MemberPress download or list of downloads.',
            'memberpress-downloads'
          )}
          initialOpen={true}
        >
          <ToggleControl
            label={ __( 'Display a Downloads list?', 'memberpress-downloads' ) }
            checked={ isList }
            onChange={ () => setAttributes( { isList: !isList } ) }
          />
          {( ! isList &&
            <SelectControl
              label={__(
                'Select a Download',
                'memberpress-downloads'
              )}
              value={ download }
              options={[
                {
                  label: __('-- Select a Download', 'memberpress-downloads'),
                  value: ""
                },
                ...downloads
              ]}
              onChange={ download => setAttributes( { download } ) }
            />
          )}
          {( isList &&
            <div>
              <SelectControl
                label={__(
                  'List by:',
                  'memberpress-downloads'
                )}
                value={ listBy }
                options={ [
                  { label: __( 'All Files', 'memberpress-downloads' ), value: 'all' },
                  { label: __( 'Category', 'memberpress-downloads' ), value: 'category' },
                  { label: __( 'Tag', 'memberpress-downloads' ), value: 'tag' },
                ] }
                onChange={ listBy => setAttributes( { listBy } ) }
              />
              {( 'category' == listBy &&
                <SelectControl
                  label={__(
                    'Download Category:',
                    'memberpress-downloads'
                  )}
                  value={ category }
                  options={[
                    {
                      label: __('-- Select a Category', 'memberpress-downloads'),
                      value: ""
                    },
                    ...categories
                  ]}
                  onChange={ category => setAttributes( { category } ) }
                />
              )}
              {( 'tag' == listBy &&
                <SelectControl
                  label={__(
                    'Download Tag:',
                    'memberpress-downloads'
                  )}
                  value={ tag }
                  options={[
                    {
                      label: __('-- Select a Tag', 'memberpress-downloads'),
                      value: ""
                    },
                    ...tags
                  ]}
                  onChange={ tag => setAttributes( { tag } ) }
                />
              )}
              <TextControl
                label={ __( 'Limit:', 'memberpress-downloads' ) }
                help={ __( 'Leave empty to show all downloads.', 'memberpress-downloads' ) }
                className="mp-downloads-list-limit-input"
                value={ limit }
                type="number"
                onChange={ limit => setAttributes( { limit } ) }
              />
            </div>
          )}
          <ToggleControl
            label={ __( 'Display description?', 'memberpress-downloads' ) }
            checked={ showDescription }
            onChange={ () => setAttributes( { showDescription: !showDescription } ) }
          />
        </PanelBody>
      </InspectorControls>

      <Disabled>
        <ServerSideRender
          block="memberpress/memberpress-download"
          attributes={{
            download,
            isList,
            listBy,
            limit,
            category,
            tag,
            showDescription }}
        />
      </Disabled>
    </div>
  );

}
