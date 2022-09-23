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
    __experimentalNumberControl as NumberControl,
} from '@wordpress/components';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import {useBlockProps, ColorPalette, InspectorControls} from '@wordpress/block-editor';

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

    const updateShortcode = (attribute_key, val, inputType) => {

        console.log(attribute_key, val, inputType, 'attribute_key, val, inputType')

        if (inputType === 'custom') {
            setAttributes({[attribute_key]: val.selectedItem.value})
        } else {
            setAttributes({[attribute_key]: val})
        }

        let shortcodeTag = '[growtype_posts';
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

            // console.log(element, 'element')
        })

        shortcodeTag += ']';

        setAttributes({shortcode: shortcodeTag})
    };

    console.log(attributes, 'attributes - editing block')
    console.log(Object.entries(attributes), 'attributes length')

    if (Object.entries(attributes).length === 0 || attributes.shortcode === '') {
        attributes.shortcode = '[growtype_posts]'
    }

    return (
        <div {...blockProps}>
            <InspectorControls key="setting">
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
                                    }
                                ]}
                                onChange={(val) => updateShortcode('preview_style', val)}
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
                                checked={attributes.post_link}
                                onChange={(val) => updateShortcode('post_link', val)}
                            />
                        </PanelRow>
                        <PanelRow>
                            <TextControl
                                label={__('Parent ID', 'growtype-post')}
                                onChange={(val) => updateShortcode('id', val)}
                                value={attributes.id}
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
                                checked={attributes.slider}
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
            <TextControl
                value={attributes.shortcode}
                onChange={(val) => setAttributes({shortcode: val})}
            />
        </div>
    );
}
