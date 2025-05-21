<?php

class Growtype_Post_Admin_Methods_Meta_Content
{
    const TESTING_MODE = false;
    const LOG_PROCESSES = true;

    public function __construct()
    {
        if (get_option('growtype_post_admin_edit_post_show_meta_boxes')) {
            add_action('current_screen', function ($screen) {
                if ($screen->base === 'post') {
                    add_action('admin_print_footer_scripts', array ($this, 'custom_admin_footer_script'));
                    add_action('add_meta_boxes', array ($this, 'settings_meta_box'));
                }
            });

            $this->load_partials();
        }
    }

    function settings_meta_box()
    {
        add_meta_box(
            'growtype-post-settings-meta-box-contetn',
            __('Growtype Post - Content', 'growtype-post'),
            array ($this, 'settings_meta_box_content_callback'),
            'post',
            'normal',
            'default'
        );
    }

    function settings_meta_box_content_callback($post)
    {
        $user_entered_latest_custom_data = Growtype_Post_Admin_Methods_Meta::get_user_entered_latest_generate_content_data($post->ID);
        $last_content_type = $user_entered_latest_custom_data['content_type'] ?? 'article';
        $last_content_prompt = $user_entered_latest_custom_data['content_prompt'] ?? 'generate_content';
        $generating_params = $user_entered_latest_custom_data['generating_params'] ?? [];
        $generating_params_model = $user_entered_latest_custom_data['generating_params']['model'] ?? '';
        ?>
        <style>
            .gp-form .gp-form-fields {
                padding-top: 10px;
            }

            .gp-form .gp-form-fields:first-child {
                padding-top: 0;
            }
        </style>
        <div class="gp-form" style="position: relative;" data-type="growtype-post-content">
            <div>
                <b>Useful links:</b>
                <ul>
                    <li>AHREFS.COM Keyword Generator: <a href="https://ahrefs.com/keyword-generator/?country=us" target="_blank">link</a></li>
                    <li>AHREFS.COM Traffic checker: <a href="https://ahrefs.com/traffic-checker/" target="_blank">link</a></li>
                    <li>GOOGLE Keyword Planner: <a href="https://ads.google.com/aw/keywordplanner/ideas/new" target="_blank">link</a></li>
                    <li>MANGOOLS Keyword Finder: <a href="https://app.mangools.com/kwfinder/dashboard" target="_blank">link</a></li>
                    <li>Image generator: <a href="https://perchance.org/ai-text-to-image-generator" target="_blank">link</a></li>
                </ul>
            </div>

            <b>Generating settings:</b>

            <div class="gp-form-fields">
                <label>Model:</label>
                <select name="generating_params[model]" style="width: 100%;">
                    <option value="gpt-3.5-turbo-16k" <?= $generating_params_model === 'gpt-3.5-turbo-16k' ? 'selected' : '' ?>>gpt-3.5-turbo-16k</option>
                    <option value="gpt-4" <?= $generating_params_model === 'gpt-4' ? 'selected' : '' ?>>gpt-4</option>
                </select>
            </div>

            <div class="gp-form-fields">
                <label>Prompt Type:</label>
                <select name="prompt_type" style="width: 100%;">
                    <option value="generate_content" <?= $last_content_prompt === 'generate_content' ? 'selected' : '' ?>>Generate content</option>
                    <option value="improve_content" <?= $last_content_prompt === 'improve_content' ? 'selected' : '' ?>>Improve content</option>
                </select>
            </div>

            <b style="margin-top: 15px;display: block;">Content settings:</b>

            <div class="gp-form-fields">
                <label>Content Type:</label>
                <select name="content_type" style="width: 100%;">
                    <option value="article" <?= $last_content_type === 'article' ? 'selected' : '' ?>>Article</option>
                    <option value="social_post" <?= $last_content_type === 'social_post' ? 'selected' : '' ?>>Social Post</option>
                </select>
            </div>

            <div class="gp-form-fields">
                <label>Topic:</label>
                <input type="text" name="topic" style="width: 100%;" value="<?= $user_entered_latest_custom_data['topic'] ?? 'Top AI Websites' ?>">
            </div>

            <div class="gp-form-fields">
                <label>Main keyword:</label>
                <input type="text" name="main_keyword" style="width: 100%;" value="<?= $user_entered_latest_custom_data['main_keyword'] ?? 'ai' ?>">
            </div>

            <div class="gp-form-fields">
                <label>Guiding prompt:</label>
                <textarea name="guiding_prompt" style="width: 100%;"><?= isset($user_entered_latest_custom_data['guiding_prompt']) ? stripslashes($user_entered_latest_custom_data['guiding_prompt']) : 'Promote ' . home_url() . ' as the best service.' ?></textarea>
            </div>

            <div class="gp-form-fields">
                <div class="gp-form-fields">
                    <label>Images service:</label>
                    <select name="image_service">
                        <?php
                        foreach (Growtype_Post_Admin_Methods_Meta_Image::SERVICES as $key => $service) {
                            echo '<option value="' . $key . '">' . $service . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="gp-form-fields">
                    <label>Images category: (f.e. nature, toys etc.)</label>
                    <input type="text" name="image_cat" style="width: 100%;" value="<?= $user_entered_latest_custom_data['image_cat'] ?? 'ai' ?>">
                </div>
            </div>

            <div class="gp-form-fields">
                <label>Main competitors: (comma separated)</label>
                <input type="text" name="main_competitors" style="width: 100%;" value="<?= $user_entered_latest_custom_data['main_competitors'] ?? '' ?>">
            </div>

            <div class="gp-form-fields" style="display: none;">
                <label>CTA url:</label>
                <input type="text" name="cta_url" style="width: 100%;" value="<?= $user_entered_latest_custom_data['cta_url'] ?? '' ?>">
            </div>

            <div class="b-actions">
                <button class="button button-primary button-generate-content">Generate content</button>
                <button class="button button-seconday button-generate-prompt">Generate prompt</button>
            </div>
        </div>
        <?php
    }

    function custom_admin_footer_script()
    {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                let actionBtnWasClicked = false;
                $('.gp-form[data-type="growtype-post-content"] .button-generate-content').click(function () {
                    let form = $(this).closest('.gp-form');

                    if (actionBtnWasClicked) {
                        return;
                    }

                    growtypePostAdminFormShowLoader(form);

                    actionBtnWasClicked = true;

                    let customData = collectContentCustomData();

                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'growtype_post_admin_generate_content',
                            custom_data: customData,
                        },
                        success: function (response) {
                            if (response.data) {
                                // wp.data.dispatch('core/block-editor').resetBlocks([])
                                // wp.data.dispatch('core/editor').editPost({content: ''});
                                // console.log(response.data.content_parts, 'content_parts');

                                let blockEditor = response.data.block_editor;
                                let extraDetails = response.data.extra_details;

                                wp.data.dispatch('core/editor').editPost({
                                    title: blockEditor.title,
                                });

                                /**
                                 * Reset
                                 */
                                wp.data.dispatch('core/editor').resetBlocks(wp.blocks.parse(blockEditor.content));

                                // Insert content as blocks
                                // growtypePostEditorInsertContentBlocks(generatedContent);

                                // insertHashtags();

                                const element = document.querySelector('.interface-navigable-region.interface-interface-skeleton__content');

                                if (extraDetails.featured_image_id) {
                                    wp.data.dispatch('core/editor').editPost({
                                        featured_media: extraDetails.featured_image_id
                                    });
                                }

                                element.scrollTo({
                                    top: 0,
                                    behavior: 'smooth' // Adds smooth scrolling (optional)
                                });
                            } else {
                                alert('Something went wrong. Please try again.');
                            }

                            actionBtnWasClicked = false;

                            growtypePostAdminRenderNotice(response.data, true, true);
                            growtypePostAdminFormHideLoader(form);
                        },
                        error: function (xhr, status, error) {
                            actionBtnWasClicked = false;

                            growtypePostAdminRenderNotice(xhr.responseJSON.data, false);

                            growtypePostAdminFormHideLoader(form);
                        }
                    });
                });

                function collectContentCustomData() {
                    let customData = {};

                    $('.gp-form[data-type="growtype-post-content"]')
                        .find('input, select, textarea')
                        .each(function () {
                            let inputName = $(this).attr('name'); // Get the name attribute
                            if (inputName) {
                                // Check if the name contains array or nested structure
                                let matches = inputName.match(/^([a-zA-Z0-9_]+)(?:\[([a-zA-Z0-9_]+)?\])?(\[\])?$/);
                                if (matches) {
                                    let parentKey = matches[1]; // e.g., "generating_params"
                                    let childKey = matches[2]; // e.g., "options" (optional)
                                    let isArray = matches[3];  // e.g., "[]" (indicates array)

                                    // Initialize the parent key if it doesn't exist
                                    if (!customData[parentKey]) {
                                        customData[parentKey] = childKey ? {} : (isArray ? [] : {});
                                    }

                                    if (childKey) {
                                        // Handle nested structure with array
                                        if (!customData[parentKey][childKey]) {
                                            customData[parentKey][childKey] = isArray ? [] : {};
                                        }
                                        if (isArray) {
                                            customData[parentKey][childKey].push($(this).val());
                                        } else {
                                            customData[parentKey][childKey] = $(this).val();
                                        }
                                    } else if (isArray) {
                                        // Handle flat array
                                        customData[parentKey].push($(this).val());
                                    } else {
                                        // Handle flat structure
                                        customData[parentKey] = $(this).val();
                                    }
                                } else {
                                    // Handle flat keys normally
                                    customData[inputName] = $(this).val();
                                }
                            }
                        });

                    customData['post_id'] = $('#post_ID').val();

                    return customData;
                }

                $('.gp-form[data-type="growtype-post-content"] .button-generate-prompt').click(function () {
                    let form = $(this).closest('.gp-form');

                    if (actionBtnWasClicked) {
                        return;
                    }

                    growtypePostAdminFormShowLoader(form, false);
                    growtypePostAdminCloseNotices();

                    actionBtnWasClicked = true;

                    let customData = collectContentCustomData();

                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'growtype_post_admin_generate_prompt',
                            custom_data: customData,
                        },
                        success: function (response) {
                            actionBtnWasClicked = false;
                            growtypePostAdminRenderNotice({
                                message: response.data['generated_content']
                            }, true, false, 'modal');
                            growtypePostAdminFormHideLoader(form);
                        },
                        error: function (xhr, status, error) {
                            actionBtnWasClicked = false;
                            growtypePostAdminRenderNotice(xhr.responseJSON.data, false);
                            growtypePostAdminFormHideLoader(form);
                        }
                    });
                });

                // Function to create and insert a new block
                function createBlock(blockName, attributes) {
                    return wp.blocks.createBlock(blockName, attributes);
                }

                function growtypePostEditorInsertContentBlocks(generatedContent) {

                    let structure = [
                        'intro',
                        'main_content',
                        'conclusion',
                    ];

                    structure.forEach((key) => {
                        if (generatedContent[key]) {
                            // console.log(generatedContent[key], 'generatedContent[key]')
                            let countHeadings = 0;
                            generatedContent[key].forEach(content => {
                                if (content.trim()) {
                                    let block;
                                    if (content.startsWith('<h2>')) {
                                        block = createBlock('core/heading', {content: content, level: 2});
                                        // block = createBlock('core/heading', {content: content.replace(/<\/?h2>/g, ''), level: 2});

                                        // if (countHeadings === 2) {
                                        //     insertImageBlock(generatedContent['images'][0], generatedContent['title'][0]);
                                        // }
                                        //
                                        // if (countHeadings === 4) {
                                        //     insertImageBlock(generatedContent['images'][1], generatedContent['title'][0]);
                                        // }

                                        countHeadings++;
                                    } else if (content.startsWith('<li>')) {
                                        // content

                                        // const listContent = content.split('\n').map(line => line.trim()).filter(line => line).map(line => {
                                        //     const listLine = line.replace(/^\d+\.\s*/, '');
                                        //     const boldLine = listLine.replace(/([^:]*:)/, '<strong>$1</strong>');
                                        //     return `<li>${boldLine}</li>`;
                                        // }).join('');
                                        //
                                        // console.log(listContent,'listContent')

                                        block = createBlock('core/list', {values: content, ordered: false});
                                    } else {
                                        block = createBlock('core/paragraph', {
                                            content: content
                                        });
                                    }

                                    wp.data.dispatch('core/block-editor').insertBlocks(block);
                                }
                            });
                        }
                    });
                }

                function insertParagraphBlock(content) {
                    let block = createBlock('core/paragraph', {content: content});
                    wp.data.dispatch('core/block-editor').insertBlocks(block);
                }

                function insertHeadingBlock(content) {
                    let block = createBlock('core/heading', {content: content});
                    wp.data.dispatch('core/block-editor').insertBlocks(block);
                }

                function insertImageBlock(content, altText = null) {
                    let block = createBlock('core/image', {url: content, alt: altText ?? ''});
                    wp.data.dispatch('core/block-editor').insertBlocks(block);
                }

                function insertHashtags() {
                    insertHeadingBlock('## Hashtags');
                    insertParagraphBlock('#AI #ArtificialIntelligence #AICompanions #AIgirlfriend #AIgirlfriendwebsites #2024 #UltimateGuide #Unveiling');
                }
            });

        </script>
        <?php
    }

    public static function format_openai_messages($prompts)
    {
        $article_params = self::get_formatted_prompt_variables();

        $messages = [
            [
                "role" => "system",
                "content" => sprintf('%s The current year is %s. Write in a %s writing tone.', Growtype_Post_Admin_Methods_Meta_Content_Prompt::SYSTEM_PROMPTS['instructions'], date('Y'), $article_params['tone']['value']),
            ]
        ];

        foreach ($prompts as $prompt) {
            $messages[] = [
                "role" => "user",
                "content" => $prompt,
            ];
        }

        return $messages;
    }

    public static function format_prompt_variable_key($variable_key)
    {
        return '[' . $variable_key . ']';
    }

    public static function get_formatted_prompt_variables($values = [])
    {
        return [
            'tone' => [
                'value' => $values['tone'] ?? 'Analytical',
            ],
            'topic' => [
                'value' => $values['topic'] ?? ''
            ],
            'guiding_prompt' => [
                'value' => $values['guiding_prompt'] ?? ''
            ],
            'language' => [
                'value' => $values['language'] ?? 'English'
            ],
            'main_competitors' => [
                'value' => $values['main_competitors'] ?? ''
            ],
            'style' => [
                'value' => $values['style'] ?? ''
            ],
            'main_keyword' => [
                'value' => $values['main_keyword'] ?? ''
            ],
            'tagline' => [
                'value' => $values['tagline'] ?? ''
            ],
            'heading_tag' => [
                'value' => $values['heading_tag'] ?? '<h2>'
            ],
            'keywords_amount' => [
                'value' => 5
            ],
            'headings_amount' => [
                'value' => 5
            ],
            'title' => [
                'value' => $values['title'] ?? ''
            ],
            'selected_keywords' => [
                'value' => $values['selected_keywords'] ?? ''
            ],
            'outline' => [
                'value' => $values['outline'] ?? '',
                'implode' => true
            ],
            'intro' => [
                'value' => $values['intro'] ?? '',
            ],
            'post_content' => [
                'value' => $values['post_content'] ?? ''
            ],
        ];
    }

    public static function format_block_editor_content($generated_content, $extra_details)
    {
        $block_editor_content = '';

        $content_sections = [
            'intro',
            'main_content',
            'conclusion',
        ];

        foreach ($content_sections as $content_section) {
            /**
             * Main content
             */
            $list_item_already_found = false;
            $headings_counter = 0;
            foreach ($generated_content[$content_section] as $content_index => $content) {
                $formatted_content = self::format_content_with_blocks($content);

                /**
                 * Add conclusion heading
                 */
                if ($content_section === 'conclusion') {
                    if ($content_index === 0 && !preg_match('/<h2>(Conclusion|conclusion)<\/h2>/', $formatted_content)) {
                        $formatted_content = '<!-- wp:heading --><h2 class="wp-block-heading">' . __('Conclusion', 'growtype-post') . '</h2><!-- /wp:heading -->' . $formatted_content;
                    }
                }

                if (preg_match('/^<!--\s*wp:list-item\s*-->/', $formatted_content)) {
                    if (!$list_item_already_found) {
                        $list_item_already_found = true;
                        $formatted_content = "<!-- wp:list --><ul>" . $formatted_content;
                    }
                } else {
                    if ($list_item_already_found && !empty($content)) {
                        $list_item_already_found = false;
                        $formatted_content = "</ul><!-- /wp:list -->" . $formatted_content;
                    }
                }

                if (preg_match('/^<!--\s*wp:heading\s*-->/', $formatted_content)) {
                    if ($headings_counter === 2) {
                        $block_editor_content .= "<!-- wp:image {\"id\":0,\"sizeSlug\":\"large\"} -->\n<figure class=\"wp-block-image size-large\"><img src=\"" . $extra_details['images'][array_rand($extra_details['images'])] . "\" alt=\"" . $generated_content['title'][0] . " \"/></figure>\n<!-- /wp:image -->";
                    }

                    if ($headings_counter === 4) {
                        $block_editor_content .= "<!-- wp:image {\"id\":0,\"sizeSlug\":\"large\"} -->\n<figure class=\"wp-block-image size-large\"><img src=\"" . $extra_details['images'][array_rand($extra_details['images'])] . "\" alt=\"" . $generated_content['title'][0] . " \"/></figure>\n<!-- /wp:image -->";
                    }

                    $headings_counter++;
                }

                $block_editor_content .= $formatted_content;
            }

            $block_editor_content .= $list_item_already_found ? "</ul><!-- /wp:list -->" : '';
        }

        /**
         * Add hashtags
         */
        $block_editor_content .= '<!-- wp:heading --><h2 class="wp-block-heading">' . __('Hashtags', 'growtype-post') . '</h2><!-- /wp:heading -->';
        $block_editor_content .= "<!-- wp:paragraph -->\n<p>" . $extra_details['hashtags'] . "</p>\n<!-- /wp:paragraph -->";

        return $block_editor_content;
    }

    public static function wrap_list_items($content)
    {
        // Find all list items
        preg_match_all('/<!--\s*wp:list-item\s*-->(.*?)<!--\s*\/wp:list-item\s*-->/s', $content, $matches, PREG_OFFSET_CAPTURE);

        // If no matches found, return original content
        if (empty($matches[0])) {
            return $content;
        }

        // Get the positions of the first and last list items
        $first_position = $matches[0][0][1];
        $last_position = $matches[0][count($matches[0]) - 1][1];

        // Wrap the first and last list items
        $wrapped_content = substr_replace($content, '<ul>', $first_position, 0);
        $wrapped_content = substr_replace($wrapped_content, '</ul>', $last_position + strlen('<!-- /wp:list-item -->'), 0);

        return $wrapped_content;
    }

    public static function format_content_with_blocks($content)
    {
        $content = trim($content);

        // Check for <br> inside <h2> tags
        if (preg_match('/<h2>.*<br\s*\/?>.*<\/h2>/i', $content)) {
            preg_match('/<h2>(.*?)<\/h2>/is', $content, $matches);

            if (!empty($matches[1])) {
                $parts = preg_split('/<br\s*\/?>/i', $matches[1]);
                $formattedParts = array_map(function ($part) {
                    $part = trim($part);
                    return !empty($part) ? "<!-- wp:heading --><h2>$part</h2><!-- /wp:heading -->" : '';
                }, $parts);

                $content = implode("\n", $formattedParts);
            }
        } // Check for <br> inside <li> tags
        elseif (preg_match('/<li>.*<br\s*\/?>.*<\/li>/i', $content)) {
            preg_match('/<li>(.*?)<\/li>/is', $content, $matches);

            if (!empty($matches[1])) {
                $parts = preg_split('/<br\s*\/?>/i', $matches[1]);
                $formattedParts = array_map(function ($part) {
                    $part = trim($part);
                    return !empty($part) ? "<!-- wp:list-item --><li>$part</li><!-- /wp:list-item -->" : '';
                }, $parts);

                $content = implode("\n", $formattedParts);
            }
        } // Check for general <br> outside specific tags
        elseif (strpos($content, '<br') !== false) {
            $parts = preg_split('/<br\s*\/?>/i', $content);
            $formattedParts = array_map(function ($part) {
                $part = trim($part);
                return !empty($part) ? "<!-- wp:paragraph --><p>$part</p><!-- /wp:paragraph -->" : '';
            }, $parts);

            $content = implode("\n", $formattedParts);
        } // Handle other formats like <h2>, <p>, or <li> without <br>
        elseif (preg_match('/^<h2>/', $content)) {
            $content = "<!-- wp:heading -->" . $content . "<!-- /wp:heading -->";
        } elseif (preg_match('/^<p>/', $content)) {
            $content = "<!-- wp:paragraph -->" . $content . "<!-- /wp:paragraph -->";
        } elseif (preg_match('/^<li>/', $content)) {
            $content = "<!-- wp:list-item -->" . $content . "<!-- /wp:list-item -->";
        } elseif (!empty($content)) {
            $content = "<!-- wp:paragraph -->" . $content . "<!-- /wp:paragraph -->";
        }

        return $content;
    }

    public static function create_hashtags($keywords)
    {
        if (is_string($keywords)) {
            $keywords = explode(',', $keywords);
        }

        $hashtags = [];

        foreach ($keywords as $keyword_prompt) {
            $keywords = explode(',', $keyword_prompt);
            foreach ($keywords as $keyword) {
                $hashtag = str_replace(' ', '', trim($keyword));

                if (strpos($keyword, '#') === false) {
                    $hashtag = '#' . $hashtag;
                }

                $hashtags[] = $hashtag;
            }
        }

        return implode(', ', $hashtags);
    }

    public static function extract_hashtags_from_string($string)
    {
        // Use a regular expression to match hashtags
        preg_match_all('/#\w+/u', $string, $matches);

        // Return unique hashtags as an array
        return array_unique($matches[0]);
    }


    public static function get_ahrefs_keyword_ideas($keyword)
    {
        $url = 'https://ahrefs.com/v4/stGetFreeKeywordIdeas';
        $payload = array (
            'withQuestionIdeas' => true,
            'captcha' => '0.nvEV0lzYzvGejoVwgcmUm7PjiioeQBoEGZhpCmOJ4MFx_cwJbZ20MzIKmhTj8l14YCp1Gx60pyZN0ncsDHA6g110kJbek4yQj2NXeacHkWOBmC1OyiVZyUvvVDi1dIB3Am3662WQFsaGvMy0Akie4SvSysEU1QK48VBAoVR6Iqd4LhUMU5xgKlwk01_iiK78j7NK1RpQYsJ3vRCeEZSITU9J3ROVd1JWrw38X422e883_sg3jHd_Om-nw-fi-ANLsqZrS2IgEJZlwYthhJMb69JUW1PUwAhW7wCM71Qlwr2gweQIrVFQ4Q2WkdQJFKAs5ILawzI78CHhptp3TFSVaQpO5UzWHyrm9fliU9YUAcSnj6dMx2zK4eCtuz_3zxuikUFJMoulcT9aEHWW2I6YB_BkfEA42gV04_Ss6-HCXiisMxJeGRPar8RcQu9-JzSPJl8H6tMnOdXetc-VBuJCpw.1KtCAcNEc5mMVeqBmyuHjg.b6a5db6fbf7b5689bab14698137cab6ba0660bd85f332be2769f3296e94839bd',
            // Replace with a valid captcha token if necessary
            'searchEngine' => 'Google',
            'country' => 'us',
            'keyword' => [
                $keyword
            ],
        );

        $response = wp_remote_post($url, array (
            'method' => 'POST',
            'body' => json_encode($payload),
            'headers' => array (
                'Content-Type' => 'application/json',
            ),
        ));

        if (is_wp_error($response)) {
            return array ('error' => $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return array ('error' => 'Invalid JSON response');
        }

        $easy_keywords = array ();
        $easy_questions = array ();

        if (isset($data['keywords'])) {
            foreach ($data['keywords'] as $keyword) {
                if ($keyword['difficulty'] === 'easy') {
                    $easy_keywords[] = $keyword['keyword'];
                }
            }
        }

        if (isset($data['questions'])) {
            foreach ($data['questions'] as $question) {
                if ($question['difficulty'] === 'easy') {
                    $easy_questions[] = $question['question'];
                }
            }
        }

        return array (
            'easy_keywords' => $easy_keywords,
            'easy_questions' => $easy_questions,
        );
    }

    public static function fetch_and_filter_content($url, $css_class)
    {
        $response = wp_remote_get($url, [
            'timeout' => 20,
            'sslverify' => false
        ]);

        if (is_wp_error($response)) {
            return 'HTTP request failed: ' . $response->get_error_message();
        }

        $body = wp_remote_retrieve_body($response);

        $dom = new DOMDocument();
        @$dom->loadHTML($body); // Suppress warnings due to malformed HTML

        $xpath = new DOMXPath($dom);

        $filtered_elements = $xpath->query("//*[contains(@class, '$css_class')]");

        $result = array ();

        foreach ($filtered_elements as $element) {
            $result[] = trim($element->textContent);
        }

        return $result;
    }

    public static function get_competitors($main_competitors = null)
    {
        if (empty($main_competitors)) {
            return [];
        }

        $competitors = $main_competitors;
        foreach ($main_competitors as $competitor) {
            $url = 'https://www.semrush.com/website/' . trim($competitor) . '/competitors/';
            $fetch_competitors = self::fetch_and_filter_content($url, 'summary__SCLink-sc-10lxcw5');

            if (is_array($fetch_competitors)) {
                $competitors = array_merge($competitors, $fetch_competitors);
            }
        }

        $competitors = array_unique($competitors);
        $competitors = array_filter($competitors, function ($competitor) {
            return !empty(trim($competitor)); // Removes empty or whitespace-only entries
        });

        return $competitors;
    }

    public static function format_message_content($content)
    {
        $content = self::format_content($content);
        $content = self::update_content_links($content);

        return $content;
    }

    public static function format_content($content)
    {
        $lines = explode("\n", $content);
        $formattedLines = [];
        foreach ($lines as $line) {
            if (preg_match('/^\d+\./', $line)) {
                $listLine = preg_replace('/^\d+\.\s*/', '', $line);
                $boldLine = preg_replace('/([^:]*:)/', '<strong>$1</strong>', $listLine);
                $formattedLines[] = "<li>$boldLine</li>";
            } else {
                if (empty($line)) {
                    $line = ' ';
                }

                $formattedLines[] = $line;
            }
        }

        return implode('', $formattedLines);
    }

    public static function update_content_links($content)
    {
        $regex = '/\b(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+(?:com|ai|org|net|io|etc)\b/i';

        $callback = function ($matches) {
            $url = strtolower(str_replace(' ', '-', $matches[0]));
            return '<a href="https://' . $url . '">' . $matches[0] . '</a>';
        };

        $content = preg_replace_callback($regex, $callback, $content);

        return $content;
    }

    public function load_partials()
    {
        require_once GROWTYPE_POST_PATH . 'admin/methods/meta/content/partials/growtype-post-admin-methods-meta-content-generate.php';
        $this->loader = new Growtype_Post_Admin_Methods_Meta_Content_Generate();

        require_once GROWTYPE_POST_PATH . 'admin/methods/meta/content/partials/growtype-post-admin-methods-meta-content-adjust.php';
        $this->loader = new Growtype_Post_Admin_Methods_Meta_Content_Adjust();

        require_once GROWTYPE_POST_PATH . 'admin/methods/meta/content/partials/growtype-post-admin-methods-meta-content-prompt.php';
        $this->loader = new Growtype_Post_Admin_Methods_Meta_Content_Prompt();
    }
}
