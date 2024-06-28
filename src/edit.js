import {__} from '@wordpress/i18n';

/**
 * WordPress components that create the necessary UI elements for the block
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-components/
 */
import {
    TextControl,
    TextareaControl,
    Panel,
    PanelBody,
    PanelRow,
    CustomSelectControl,
    SelectControl,
    ToggleControl,
    __experimentalNumberControl as NumberControl,
    RangeControl
} from '@wordpress/components';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import {
    PlainText,
    useBlockProps,
    ColorPalette,
    InspectorControls,
    InspectorAdvancedControls
} from '@wordpress/block-editor';

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

                if (propertyKey === 'posts_per_page' || propertyKey === 'columns') {
                    propertyValue = propertyValue.toString()
                }

                if (propertyKey === 'meta_query' || propertyKey === 'tax_query') {
                    return;
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

    return (
        <div {...blockProps}>
            <InspectorControls key={'inspector'}>
                <Panel>
                    <PanelBody
                        title={__('Content settings', 'growtype-post')}
                        icon="admin-plugins"
                    >
                        <SelectControl
                            label={__('Source', 'growtype-post')}
                            help={__('Which content source should be used.', 'growtype-post')}
                            options={[
                                {
                                    label: 'Internal',
                                    value: 'internal',
                                },
                                {
                                    label: 'Wp Json',
                                    value: 'wp_json',
                                },
                                {
                                    label: 'Other',
                                    value: 'other',
                                },
                            ]}
                            value={attributes.content_source}
                            onChange={(val) => updateShortcode('content_source', val)}
                        />
                        {attributes.content_source === 'internal' ?
                            <TextControl
                                label={__('Post type', 'growtype-post')}
                                help={__('Enter which post type should be used.', 'growtype-post')}
                                onChange={(val) => updateShortcode('post_type', val)}
                                value={attributes.post_type}
                            />
                            :
                            ''
                        }
                        {attributes.content_source !== 'internal' ?
                            <TextControl
                                label={__('Url', 'growtype-post')}
                                help={__('Content url.', 'growtype-post')}
                                onChange={(val) => updateShortcode('content_url', val)}
                                value={attributes.content_url}
                            />
                            :
                            ''
                        }
                        {attributes.content_source !== 'internal' ?
                            <ToggleControl
                                label={__('Cache Url', 'growtype-post')}
                                help={
                                    attributes.content_url_cache
                                        ? 'Urs is cached.'
                                        : 'Urs is not cached.'
                                }
                                checked={attributes.content_url_cache ? true : false}
                                onChange={(val) => updateShortcode('content_url_cache', val)}
                            />
                            :
                            ''
                        }
                        <ToggleControl
                            label={__('Ajax load content', 'growtype-post')}
                            help={
                                attributes.ajax_load_content
                                    ? 'Ajax is enabled.'
                                    : 'Ajax is disabled.'
                            }
                            checked={attributes.ajax_load_content ? true : false}
                            onChange={(val) => updateShortcode('ajax_load_content', val)}
                        />
                    </PanelBody>
                    <PanelBody
                        title={__('Preview settings', 'growtype-post')}
                        icon="admin-plugins"
                    >
                        <SelectControl
                            label={__('Order', 'growtype-post')}
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
                        <SelectControl
                            label={__('Order by', 'growtype-post')}
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
                        <TextControl
                            label={__('Post in', 'growtype-post')}
                            help={__('Show only these posts. Enter ids separated by comma.', 'growtype-post')}
                            onChange={(val) => updateShortcode('post__in', val)}
                            value={attributes.post__in}
                        />
                        <ToggleControl
                            label={__('Post is a link', 'growtype-post')}
                            help={
                                attributes.post_is_a_link
                                    ? 'Post is a link.'
                                    : 'Post is not a link.'
                            }
                            checked={attributes.post_is_a_link ? true : false}
                            onChange={(val) => updateShortcode('post_is_a_link', val)}
                        />
                        <SelectControl
                            label={__('Sticky post', 'growtype-post')}
                            help={__('Sticky post visibility.', 'growtype-post')}
                            options={[
                                {
                                    label: 'Show all posts',
                                    value: 'none',
                                },
                                {
                                    label: 'Show only sticky posts',
                                    value: 'visible',
                                },
                                {
                                    label: 'Show posts without sticky',
                                    value: 'hidden',
                                },
                            ]}
                            value={attributes.sticky_post}
                            onChange={(val) => updateShortcode('sticky_post', val)}
                        />
                        <ToggleControl
                            label={__('Open post in modal window', 'growtype-post')}
                            help={
                                attributes.post_in_modal
                                    ? 'Modal is enabled.'
                                    : 'Modal is disabled.'
                            }
                            checked={attributes.post_in_modal ? true : false}
                            onChange={(val) => updateShortcode('post_in_modal', val)}
                        />
                        <ToggleControl
                            label={__('Load all posts', 'growtype-post')}
                            checked={attributes.load_all_posts}
                            onChange={(val) => updateShortcode('load_all_posts', val)}
                        />
                        {attributes.load_all_posts ?
                            <ToggleControl
                                label={__('Show "load more" posts btn', 'growtype-post')}
                                checked={attributes.show_load_more_posts_btn}
                                onChange={(val) => updateShortcode('show_load_more_posts_btn', val)}
                            />
                            :
                            ''
                        }
                        {attributes.load_all_posts && (
                            <SelectControl
                                label={__('Posts loading method', 'growtype-post')}
                                options={[
                                    {
                                        label: 'Initial',
                                        value: 'initial',
                                    },
                                    {
                                        label: 'Limited',
                                        value: 'limited',
                                    },
                                    {
                                        label: 'Ajax',
                                        value: 'ajax',
                                    }
                                ]}
                                value={attributes.loading_type}
                                onChange={(val) => updateShortcode('loading_type', val)}
                            />
                        )}
                        {(!attributes.load_all_posts || attributes.loading_type !== 'initial') && (
                            <RangeControl
                                label={__('Visible posts', 'growtype-post')}
                                value={
                                    attributes.posts_per_page
                                }
                                onChange={(val) => updateShortcode('posts_per_page', val)}
                                min={1}
                                max={50}
                            />
                        )}
                        <RangeControl
                            label={__('Columns', 'growtype-post')}
                            help={__('How many columns in grid.', 'growtype-post')}
                            value={
                                attributes.columns
                            }
                            onChange={(val) => updateShortcode('columns', val)}
                            min={1}
                            max={8}
                        />
                        <SelectControl
                            label={__('Post preview style', 'growtype-post')}
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
                                    label: 'Product',
                                    value: 'product',
                                },
                                {
                                    label: 'Custom',
                                    value: 'custom',
                                }
                            ]}
                            value={attributes.preview_style}
                            onChange={(val) => updateShortcode('preview_style', val)}
                        />
                        {attributes.preview_style === 'custom' ?
                            <TextControl
                                label={__('Custom preview style', 'growtype-post')}
                                help={__('Custom preview look.', 'growtype-post')}
                                onChange={(val) => updateShortcode('preview_style_custom', val)}
                                value={attributes.preview_style_custom}
                            />
                            :
                            ''
                        }
                        <NumberControl
                            label={__('Post Intro content length', 'growtype-post')}
                            help={__('Post preview intro content text characters amount.', 'growtype-post')}
                            isShiftStepEnabled={false}
                            onChange={(val) => updateShortcode('intro_content_length', val)}
                            value={attributes.intro_content_length}
                            min={1}
                        />
                        <ToggleControl
                            label={__('Show elements if no posts found', 'growtype-post')}
                            checked={attributes.show_if_no_posts}
                            onChange={(val) => updateShortcode('show_if_no_posts', val)}
                        />
                    </PanelBody>
                    <PanelBody
                        title={__('Terms navigation settings', 'growtype-post')}
                        icon="admin-plugins"
                    >
                        <ToggleControl
                            label="Terms navigation"
                            help={
                                attributes.terms_navigation
                                    ? 'Is active.'
                                    : 'Is disabled.'
                            }
                            checked={attributes.terms_navigation ? true : false}
                            onChange={(val) => updateShortcode('terms_navigation', val)}
                        />
                        {attributes.terms_navigation ?
                            <TextControl
                                label={__('Terms navigation taxonomy', 'growtype-post')}
                                help={__('', 'growtype-post')}
                                onChange={(val) => updateShortcode('terms_navigation_taxonomy', val)}
                                value={attributes.terms_navigation_taxonomy}
                            />
                            :
                            ''
                        }
                        {attributes.terms_navigation ?
                            <ToggleControl
                                label={__('"Show all" option visible', 'growtype-post')}
                                help={
                                    attributes.terms_navigation_show_all_option_visible
                                        ? 'Is visible.'
                                        : 'Is hidden.'
                                }
                                checked={attributes.terms_navigation_show_all_option_visible ? true : false}
                                onChange={(val) => updateShortcode('terms_navigation_show_all_option_visible', val)}
                            />
                            :
                            ''
                        }
                        {attributes.terms_navigation ?
                            <ToggleControl
                                label={__('Selections included in url', 'growtype-post')}
                                help={
                                    attributes.terms_navigation_selections_included_in_url
                                        ? 'Included'
                                        : 'Excluded'
                                }
                                checked={attributes.terms_navigation_selections_included_in_url ? true : false}
                                onChange={(val) => updateShortcode('terms_navigation_selections_included_in_url', val)}
                            />
                            :
                            ''
                        }
                        {attributes.terms_navigation ?
                            <TextControl
                                label={__('Default term selected', 'growtype-post')}
                                help={__('', 'growtype-post')}
                                onChange={(val) => updateShortcode('default_term_selected', val)}
                                value={attributes.default_term_selected}
                            />
                            :
                            ''
                        }
                    </PanelBody>
                    <PanelBody
                        title={__('Custom filters settings', 'growtype-post')}
                        icon="admin-plugins"
                    >
                        <ToggleControl
                            label="Custom filters"
                            help={
                                attributes.custom_filters
                                    ? 'Is active.'
                                    : 'Is disabled.'
                            }
                            checked={attributes.custom_filters ? true : false}
                            onChange={(val) => updateShortcode('custom_filters', val)}
                        />
                        {attributes.custom_filters ?
                            <ToggleControl
                                label={__('Search input', 'growtype-post')}
                                help={
                                    attributes.custom_filters_search_input_active
                                        ? 'Is visible.'
                                        : 'Is hidden.'
                                }
                                checked={attributes.custom_filters_search_input_active ? true : false}
                                onChange={(val) => updateShortcode('custom_filters_search_input_active', val)}
                            />
                            :
                            ''
                        }
                        {attributes.custom_filters ?
                            <TextControl
                                label={__('Included filters', 'growtype-post')}
                                help={__('', 'growtype-post')}
                                onChange={(val) => updateShortcode('custom_filters_included', val)}
                                value={attributes.custom_filters_included}
                            />
                            :
                            ''
                        }
                    </PanelBody>
                    <PanelBody
                        title={__('Pagination settings', 'growtype-post')}
                        icon="admin-plugins"
                    >
                        <ToggleControl
                            label="Pagination"
                            help={
                                attributes.pagination
                                    ? 'Is active.'
                                    : 'Is disabled.'
                            }
                            checked={attributes.pagination ? true : false}
                            onChange={(val) => updateShortcode('pagination', val)}
                        />
                    </PanelBody>
                </Panel>
            </InspectorControls>

            <InspectorAdvancedControls>
                <TextareaControl
                    label={__('Meta Query (WP_Query meta_query details)', 'growtype-post')}
                    help={<a href="https://wtools.io/convert-php-array-to-json" target="_blank">Convert array to json
                        here.</a>}
                    onChange={(val) => setAttributes({meta_query: val})}
                    value={attributes.meta_query}
                />
                <TextareaControl
                    label={__('Tax Query (WP_Query tax_query details)', 'growtype-post')}
                    help={<a href="https://wtools.io/convert-php-array-to-json" target="_blank">Convert array to json
                        here.</a>}
                    onChange={(val) => setAttributes({tax_query: val})}
                    value={attributes.tax_query}
                />
                <TextControl
                    label={__('Parent class', 'growtype-post')}
                    onChange={(val) => updateShortcode('parent_class', val)}
                    value={attributes.parent_class}
                />
                <TextControl
                    label={__('Parent ID', 'growtype-post')}
                    onChange={(val) => updateShortcode('parent_id', val)}
                    value={attributes.parent_id}
                />
            </InspectorAdvancedControls>

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
