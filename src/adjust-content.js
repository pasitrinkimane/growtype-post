import {TextareaControl} from "@wordpress/components";
import {createBlock} from "@wordpress/blocks";

const {addFilter} = wp.hooks;
const {__} = wp.i18n;
const {createHigherOrderComponent} = wp.compose;
const {Fragment, useState} = wp.element;
const {InspectorControls} = wp.blockEditor;
const {PanelBody, TextControl, Button, Spinner} = wp.components;

const enableCustomButtonOnBlocks = ['core/paragraph', 'core/heading'];

const {show_meta_boxes} = growtypePostAdminSettings;

/**
 * Create attributes
 */
function extendSettingsAttributes(settings, name) {
    if (!enableCustomButtonOnBlocks.includes(name)) {
        return settings;
    }

    if (settings && settings.attributes) {
        let attributes = {
            ...settings.attributes,
            customTextPrompt: {
                type: 'string',
                default: '' // Default value set to "Rewrite content"
            },
        };

        return {...settings, attributes};
    }

    return {...settings};
}

addFilter(
    'blocks.registerBlockType',
    'growtype-custom-button-block-extension/attributes',
    extendSettingsAttributes
);

/**
 * Gutenberg create Custom Button in control panel
 */
const createInspectorControls = createHigherOrderComponent((BlockEdit) => {
        return (props) => {

            if (!enableCustomButtonOnBlocks.includes(props.name)) {
                return (
                    <Fragment>
                        <BlockEdit {...props} />
                    </Fragment>
                );
            }

            const [customTextPrompt, setCustomTextPrompt] = useState('');
            const [imageCat, setImageCat] = useState('');
            const [isLoading, setIsLoading] = useState(false); // Add a loading state

            function handleSubmit() {
                if (isLoading) {
                    return; // Prevent multiple clicks
                }

                setIsLoading(true); // Start loading spinner

                const selectedBlocks = wp.data.select('core/block-editor').getSelectedBlockClientIds();

                let selectedBlocksData = {};
                selectedBlocks.map(function (blockId) {
                    const selectedBlock = wp.data.select('core/block-editor').getBlock(blockId);

                    if (selectedBlock) {
                        selectedBlocksData[blockId] = {};
                        selectedBlocksData[blockId]['content'] = selectedBlock.originalContent ?? '';
                    }
                });

                if (selectedBlocksData) {
                    wp.ajax.post('growtype_post_admin_adjust_content', {
                        selected_blocks_data: selectedBlocksData,
                        custom_text_prompt: customTextPrompt,
                        image_cat: imageCat,
                    }).done(response => {

                        console.log(response)

                        if (response.values) {
                            Object.entries(response.values).forEach(function ([key, value]) {
                                let content = value['content'] ?? '';
                                let images = value['images'] ?? [];

                                if (content) {
                                    wp.data.dispatch('core/block-editor').updateBlockAttributes(key, {
                                        content: content
                                    });
                                }

                                if (images.length > 0) {
                                    images.forEach(function (imageUrl) {
                                        let block = wp.blocks.createBlock('core/image', {url: imageUrl, alt: ''});

                                        const blockIndex = wp.data.select('core/block-editor').getBlockIndex(key);

                                        wp.data.dispatch('core/block-editor').insertBlocks(block, blockIndex + 1);
                                    });
                                }
                            });
                        }

                        growtypePostAdminRenderNotice(response, true, true)
                        setIsLoading(false); // Stop loading spinner
                    }).fail(response => {
                        growtypePostAdminRenderNotice(response.responseJSON.data, false, true)
                        setIsLoading(false);
                    });
                }
            }

            function handleCustomTextPromptChange(newValue) {
                setCustomTextPrompt(newValue);
            }

            function handleImageCatChange(newValue) {
                setImageCat(newValue);
            }

            return (
                <Fragment>
                    <BlockEdit {...props} />
                    {
                        show_meta_boxes ?
                            <InspectorControls>
                                <PanelBody
                                    title={__('Growtype Post - Content')}
                                    initialOpen={true}
                                >
                                    <TextControl
                                        label={__('Image cat', 'growtype-post')}
                                        help={__('Generate image', 'growtype-post')}
                                        onChange={(newValue) => handleImageCatChange(newValue)}
                                        value={imageCat}
                                    />
                                    <TextareaControl
                                        label={__('Custom Text Prompt:', 'growtype-post')}
                                        onChange={(newValue) => handleCustomTextPromptChange(newValue)}
                                        value={customTextPrompt}
                                    />
                                    <Button
                                        isPrimary
                                        onClick={handleSubmit}
                                        disabled={isLoading} // Disable button while loading
                                    >
                                        {isLoading ? (
                                            <Fragment>
                                                <Spinner/> {/* Show spinner */}
                                                {__('Generating...')}
                                            </Fragment>
                                        ) : (
                                            __('Generate')
                                        )}
                                    </Button>
                                </PanelBody>
                            </InspectorControls>
                            :
                            ''
                    }
                </Fragment>
            );
        };
    },
    'createInspectorControls'
);

addFilter(
    'editor.BlockEdit',
    'growtype-custom-button-block-extension/create-inspector-controls',
    createInspectorControls
);
