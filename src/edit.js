import {__} from '@wordpress/i18n';

/**
 * WordPress components that create the necessary UI elements for the block
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-components/
 */
import {
    TextControl,
    Panel,
    PanelBody,
    PanelRow,
    CustomSelectControl,
    SelectControl,
    ToggleControl,
    __experimentalNumberControl as NumberControl
} from '@wordpress/components';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import {PlainText, useBlockProps, ColorPalette, InspectorControls} from '@wordpress/block-editor';

import {useInstanceId} from '@wordpress/compose';

import {Icon, shortcode} from '@wordpress/icons';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @param {Object}   props               Properties passed to the function.
 * @param {Object}   props.attributes    Available block attributes.
 * @param {Function} props.setAttributes Function that updates individual attributes.
 *
 * @return {WPElement} Element to render.
 */
export default function Edit({attributes, setAttributes}) {
    const blockProps = useBlockProps();
    const instanceId = useInstanceId(Edit);
    const inputId = `blocks-shortcode-input-${instanceId}`;

    const updateShortcode = (attribute_key, val, inputType) => {

        console.log(val, 'val')

        if (inputType === 'custom') {
            setAttributes({[attribute_key]: val.selectedItem.value})
        } else {
            setAttributes({[attribute_key]: val})
        }

        let shortcodeTag = '[growtype_post';
        Object.entries(attributes).map(function (element) {
            if (element[0] !== 'shortcode') {
                let propertyKey = element[0];
                let propertyValue = element[1];

                if (propertyKey === attribute_key) {
                    if (inputType === 'custom') {
                        propertyValue = val.selectedItem.value
                    } else {
                        propertyValue = val;
                    }
                }

                if (typeof propertyValue === "boolean") {
                    propertyValue = propertyValue ? 'true' : 'false'
                }

                if (propertyValue.length > 0) {
                    shortcodeTag += ' ' + propertyKey + '=' + '"' + propertyValue + '"'
                }
            }
        })

        shortcodeTag += ']';

        setAttributes({shortcode: shortcodeTag})
    };

    if (Object.entries(attributes).length === 0 || attributes.shortcode === '') {
        attributes.shortcode = '[growtype_post]'
    }

    console.log(attributes, 'attributes')
    console.log(blockProps, 'blockProps')

    return (
        <div {...blockProps}>
            <InspectorControls key={'inspector'}>
                <Panel>
                    <PanelBody
                        title={__('Main settings', 'wholesome-plugin')}
                        icon="admin-plugins"
                    >
                        <PanelRow>
                            <TextControl
                                label={__('Post type', 'growtype-post')}
                                help={__('Enter which post type should be used.', 'growtype-post')}
                                onChange={(val) => updateShortcode('post_type', val)}
                                value={attributes.post_type}
                            />
                        </PanelRow>
                        <PanelRow>
                            <SelectControl
                                label="Order"
                                help={__('How post should be ordered.', 'growtype-post')}
                                options={[
                                    {
                                        label: 'ASC',
                                        value: 'asc',
                                    },
                                    {
                                        label: 'DESC',
                                        value: 'desc',
                                    }
                                ]}
                                value={attributes.order}
                                onChange={(val) => updateShortcode('order', val)}
                            />
                        </PanelRow>
                        <PanelRow>
                            <SelectControl
                                label="Order by"
                                help={__('According to what posts to should be ordered.', 'growtype-post')}
                                options={[
                                    {
                                        label: 'Date',
                                        value: 'date',
                                    },
                                    {
                                        label: 'Menu order',
                                        value: 'menu_order',
                                    },
                                    {
                                        label: 'Name',
                                        value: 'name',
                                    },
                                ]}
                                value={attributes.orderby}
                                onChange={(val) => updateShortcode('orderby', val)}
                            />
                        </PanelRow>
                        <PanelRow>
                            <ToggleControl
                                label="Post link"
                                help={
                                    attributes.post_link
                                        ? 'Post is a link.'
                                        : 'Post is not a link.'
                                }
                                checked={attributes.post_link ? true : false}
                                onChange={(val) => updateShortcode('post_link', val)}
                            />
                        </PanelRow>
                        <PanelRow>
                            <TextControl
                                label={__('Parent class', 'growtype-post')}
                                onChange={(val) => updateShortcode('parent_class', val)}
                                value={attributes.id}
                            />
                        </PanelRow>
                        <PanelRow>
                            <TextControl
                                label={__('Parent ID', 'growtype-post')}
                                onChange={(val) => updateShortcode('parent_id', val)}
                                value={attributes.id}
                            />
                        </PanelRow>
                    </PanelBody>
                    <PanelBody
                        title={__('Preview settings', 'wholesome-plugin')}
                        icon="admin-plugins"
                    >
                        <PanelRow>
                            <TextControl
                                label={__('Columns', 'growtype-post')}
                                help={__('How many columns in grid.', 'growtype-post')}
                                onChange={(val) => updateShortcode('columns', val)}
                                value={attributes.columns}
                            />
                        </PanelRow>
                        <PanelRow>
                            <TextControl
                                label={__('Posts per page', 'growtype-post')}
                                help={__('How many posts should be returned.', 'growtype-post')}
                                onChange={(val) => updateShortcode('posts_per_page', val)}
                                value={attributes.posts_per_page}
                            />
                        </PanelRow>
                        <PanelRow>
                            <SelectControl
                                label="Post preview style"
                                help={__('How post preview should look.', 'growtype-post')}
                                options={[
                                    {
                                        label: 'Basic',
                                        value: 'basic',
                                    },
                                    {
                                        label: 'Blog',
                                        value: 'blog',
                                    },
                                    {
                                        label: 'Content',
                                        value: 'content',
                                    },
                                    {
                                        label: 'Review',
                                        value: 'review',
                                    },
                                    {
                                        label: 'Testimonial',
                                        value: 'testimonial',
                                    },
                                    {
                                        label: 'Custom',
                                        value: 'custom',
                                    }
                                ]}
                                value={attributes.preview_style}
                                onChange={(val) => updateShortcode('preview_style', val)}
                            />
                        </PanelRow>
                        {attributes.preview_style === 'custom' ?
                            <PanelRow>
                                <TextControl
                                    label={__('Custom preview style', 'growtype-post')}
                                    help={__('Custom preview look.', 'growtype-post')}
                                    onChange={(val) => updateShortcode('preview_style_custom', val)}
                                    value={attributes.preview_style_custom}
                                />
                            </PanelRow>
                            :
                            ''
                        }
                        <PanelRow>
                            <NumberControl
                                label="Intro content length"
                                help={__('Post preview intro content text characters amount.', 'growtype-post')}
                                isShiftStepEnabled={false}
                                onChange={(val) => updateShortcode('intro_content_length', val)}
                                value={attributes.intro_content_length}
                                min={1}
                            />
                        </PanelRow>
                    </PanelBody>
                    <PanelBody
                        title={__('Slider settings', 'wholesome-plugin')}
                        icon="admin-plugins"
                    >
                        <PanelRow>
                            <ToggleControl
                                label="Active"
                                help={
                                    attributes.slider
                                        ? 'Showed in a slider.'
                                        : 'Showed without slider.'
                                }
                                checked={attributes.slider ? true : false}
                                onChange={(val) => updateShortcode('slider', val)}
                            />
                        </PanelRow>
                        <PanelRow>
                            <NumberControl
                                label="Slides amount to show"
                                isShiftStepEnabled={false}
                                onChange={(val) => updateShortcode('slider_slides_amount_to_show', val)}
                                value={attributes.slider_slides_amount_to_show}
                                min={1}
                            />
                        </PanelRow>
                    </PanelBody>
                </Panel>
            </InspectorControls>

            <div {...useBlockProps({className: 'components-placeholder'})}>
                <label
                    htmlFor={inputId}
                    className="components-placeholder__label"
                >
                    <Icon icon={shortcode}/>
                    {__('Growtype post shortcode')}
                </label>
                <PlainText
                    className="blocks-shortcode__textarea"
                    id={inputId}
                    value={attributes.shortcode}
                    aria-label={__('Shortcode text')}
                    placeholder={__('Write shortcode here…')}
                    onChange={(val) => setAttributes({shortcode: val})}
                />
            </div>
        </div>
    );
}
