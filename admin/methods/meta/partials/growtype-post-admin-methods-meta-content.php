<?php

class Growtype_Post_Admin_Methods_Meta_Content
{
    const TEST_MODE = false;
    const MODEL_SETTINGS = [
        'model' => 'gpt-3.5-turbo-16k',
        'temp' => 0.5,
        'top_p' => 0.5,
        'freq_pent' => 0.2,
        'Presence_penalty' => 0.2,
        'n' => 1,
    ];

    const SYSTEM_PROMPTS = [
        'instructions_prompts' =>
            'Your name is Talkiemate-Post. Always write naturally and use the UK English spellings rather than US, e.g. words like "optimize" that contain a "z" near the end should be spelt as "optimise", "optimising", "optimised", etc. Respond using plain language. Do not provide any labels like "Section..." or "Sub-Section...". Do not provide any explanations, notes, other labelling or analysis. Follow my prompts carefully. Add variety and randomness to the length and structure of sentences and paragraphs. Avoid robotic ridged text structures. Ensure text is naturally flowing and creative. Use examples or anecdotes to illustrate the principles or concepts. Use genuine statistics or evidence to support claims. Use transitions or connectors to link the ideas or paragraphs more coherently. Use synonyms or paraphrasing to avoid repetition or redundancy. Always exclude these words: Testament, As A Professional, Previously Mentioned, Buckle Up, Dance, Delve, Digital Era, Dive In, Embark, Enable, Emphasise, Embracing, Enigma, Ensure, Essential, Even If, Even Though, Folks, Foster, Furthermore, Game Changer, Given That, Importantly, In Contrast, In Order To, World Of, Digital Era, In Today’s, Indeed, Indelible, Essential To, Imperative, Important To, Worth Noting, Journey, Labyrinth, Landscape, Look No Further, Moreover, Navigating, Nestled, Nonetheless, Notably, Other Hand, Overall, Pesky, Promptly, Realm, Remember That, Remnant, Revolutionize, Shed Light, Symphony, Dive Into, Tapestry, Testament, That Being Said, Crucial, Considerations, Exhaustive, Thus, Put It Simply, To Summarize, Unleashing, Ultimately, Underscore, Vibrant, Vital. ',
    ];

    const META_PROMPTS = [
        'meta_prompts' =>
            'Create a single SEO friendly meta title and meta description. Based this on the "[Title]" article title and the [Selected Keywords]. Create the meta data in the [Language] language using a [Style] writing style and a [Tone] writing tone.  Follow SEO best practices and make the meta data catchy to attract clicks.',
    ];

    const REVIEW_PROMPTS = [
        'review_prompts' =>
            'Please revise the above article and HTML code so that it has [No. Headings] headings using the [Heading Tag] HTML tag. Revise the text in the [Language] language. Revise with a [Style]  style and a [Tone] writing tone.',
        'evaluate_prompts' =>
            'Create a HTML table giving a strict/evaluation of each question below based on everything above. Give the HTML table 4 columns: [STATUS], [QUESTION], [EVALUATION], [RATIONALE]. For [EVALUATION], give a PASS, FAIL or IMPROVE response. Add a CSS class name to each row with the corresponding response value. For the [STATUS] column, don\'t add anything. For [RATIONALE], explain your reasoning. Order the rows according to  [EVALUATION]. All answers must be factual. Then giving examples like phrases or topics add these within curly brackets. Do not add the column labels within square brackets in your response. The questions are:
Is the length of the article over 500 words and an adequate length compared to similar articles?
Is the article optimised for certain keywords or phrases? What are these?
Is the article well-written and easy to read?
Does the article have any spelling or grammar issues?
Does the article provide an original, interesting and engaging perspective on the topic?',
    ];

    const ARTICLE_PROMPTS = [
        'title_prompts' =>
            'Provide unique without quotes article title based on topic "[Idea]". It needs to be seo friendly, unique and catchy. Write in [Language] language using a [Style] writing style and a [Tone] writing tone.',
        'keywords_prompts' =>
            'For the title "[Title]", provide relevant keywords or phrases. These need to be popular searches in Google. Capitalise each word.',
        'outline_prompts' =>
            'Create 5 sections and no sub-sections for the body of my article. Make sure that 5 sections are related to article title "[Title]". Don\'t include an introduction or conclusion. This needs to be a simple list of interesting, relatable, popular section headings related to article title: "[Title]". Do not add any commentary, notes or additional information such as section labels, "Section 1", "Section 2", etc. Please include the following keywords: [Selected Keywords] where appropriate in the headings. Write the outline in the [Language] language using a [Style] writing style and a [Tone] writing tone.',
        'intro_prompts' =>
            'Generate an introduction for my article as a single paragraph. Do NOT INCLUDE a separate heading. Base the introduction on the title - "[Title]" title and the keywords: [Selected Keywords]. Write the introduction in the [Language] language using a [Style] writing style and a [Tone] writing tone.',
//        'tagline_prompts' =>
//            'Generate a tagline for my article. Base the tagline on the "[Title]" title and the [Selected Keywords]. Write the tagline in the [Language] language using a [Style] writing style and a [Tone] writing tone. Use persuasive power words.',
        'main_content_prompts' =>
            'Write a SEO optimizes HTML article "[Title]". [Guiding Prompt]. [Competitors Message]. Write the article and for each section, vary the word counts of each by at least 50%. This is my outline for you to write: [Outline]. Each section should provide a unique perspective on the topic and provide value over and above what\'s already available. Format each section heading as a [Heading Tag] tag. You must not include a conclusion. Use keywords to SEO optimise article: [Selected Keywords]. Write the article in the [Language] language using a [Style] writing style and a [Tone] writing tone. Each section must be explored in detail and must include a minimum of 3 paragraphs. To achieve this, you must include all possible known features, benefits, arguments, analysis and whatever is needed to explore the topic to the best of your knowledge.',
        'conclusion_prompts' =>
            'Create a +- 150 words conclusion based on the title - "[Title]" and optimise conclusion for keywords: "[Selected Keywords]". Write in the [Language] language using a [Style] writing style and a [Tone] writing tone. Include a call to action to express a sense of urgency. Within the paragraph, include a [Heading Tag] tag for the heading to contain the word "conclusion. Don\'t use <div> tags or <ul> tags.',
    ];

    const QA_PROMPTS = [
        'qa_prompts' =>
            'Create [No. Headings] individual Questions and Answers, each in their own paragraph. Do not give each question a label, e.g. Question 1, Question2, etc. Based these on the "[Title]" title and the [Selected Keywords]. Write in the [Language] language using a [Style] writing style and a [Tone] writing tone. Within each paragraph, include a [Heading Tag] tag for the question and a P tag for the answer. Ensure they provide additional useful information to supplement the main "[Title]" article. Don\'t use lists or LI tags.',
    ];

    public function __construct()
    {
        add_action('admin_print_footer_scripts', array ($this, 'custom_admin_footer_script'));
        add_action('add_meta_boxes', array ($this, 'growtype_post_settings_meta_box'));
        add_action('wp_ajax_growtype_post_admin_generate_content', array ($this, 'growtype_post_admin_generate_content_callback'));
        add_action('wp_ajax_growtype_post_admin_adjust_content', array ($this, 'growtype_post_admin_adjust_content_callback'));
    }

    public static function get_openai_api_key()
    {
        return get_option('growtype_post_openai_api_key');
    }

    function growtype_post_settings_meta_box()
    {
        add_meta_box(
            'growtype-post-settings-meta-box-contetn',
            __('Growtype post - Content', 'growtype-post'),
            array ($this, 'growtype_post_settings_meta_box_content_callback'),
            'post',
            'side',
            'default'
        );
    }

    function growtype_post_settings_meta_box_content_callback($post)
    {
        ?>
        <style>
            .gp-form .input-wrapper {
                padding-top: 10px;
            }

            .gp-form .input-wrapper:first-child {
                padding-top: 0;
            }
        </style>
        <div class="gp-form" style="position: relative;" data-type="growtype-post-content">
            <div class="input-wrapper">
                <label>Article title:</label>
                <input type="text" name="article_title" style="width: 100%;" value="<?= get_transient('growtype_post_generate_content_last_entered_customer_data')['article_title'] ?? 'Top AI Girlfriend Websites' ?>">
            </div>

            <div class="input-wrapper">
                <label>Main keyword:</label>
                <input type="text" name="main_keyword" style="width: 100%;" value="<?= get_transient('growtype_post_generate_content_last_entered_customer_data')['main_keyword'] ?? 'girfriend' ?>">
            </div>

            <div class="input-wrapper">
                <label>Guiding prompt:</label>
                <textarea name="guiding_prompt" style="width: 100%;"><?= isset(get_transient('growtype_post_generate_content_last_entered_customer_data')['guiding_prompt']) ? stripslashes(get_transient('growtype_post_generate_content_last_entered_customer_data')['guiding_prompt']) : 'Promote https://talkiemate.com/ as the best "AI Chat Service, with Lifelike Virtual Personalities".' ?></textarea>
            </div>

            <div class="input-wrapper">
                <label>Image category: (f.e. woman, men, toys etc.)</label>
                <input type="text" name="image_cat" style="width: 100%;" value="<?= get_transient('growtype_post_generate_content_last_entered_customer_data')['image_cat'] ?? 'girfriend' ?>">
            </div>

            <div class="input-wrapper">
                <label>Main competitors: (comma separated)</label>
                <input type="text" name="main_competitors" style="width: 100%;" value="<?= get_transient('growtype_post_generate_content_last_entered_customer_data')['main_competitors'] ?? 'spicychat.ai, candy.ai' ?>">
            </div>

            <div class="b-actions">
                <button class="button button-secondary button-generate-content">Generate content</button>
            </div>
        </div>
        <?php
    }

    function custom_admin_footer_script()
    {
        ?>
        <style>
            .gp-form[data-type="growtype-post-content"] {
                position: relative;
            }

            .gp-form[data-type="growtype-post-content"].is-loading {
                pointer-events: none;
            }

            .gp-form[data-type="growtype-post-content"].is-loading:before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.5);
            }

            .growtype-post-loader {
                border: 7px solid #f3f3f3; /* Light grey */
                border-top: 7px solid #3498db; /* Blue */
                border-radius: 50%;
                width: 40px;
                height: 40px;
                animation: growtype-post-spin 2s linear infinite;
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                margin: auto;
                z-index: 1;
            }

            @keyframes growtype-post-spin {
                0% {
                    transform: rotate(0deg);
                }
                100% {
                    transform: rotate(360deg);
                }
            }
        </style>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                let wasClicked = false;
                $('.gp-form[data-type="growtype-post-content"] .button-generate-content').click(function () {

                    if (wasClicked) {
                        return;
                    }

                    let form = $(this).closest('.gp-form');

                    form.addClass('is-loading');

                    wasClicked = true;

                    let button = $(this);

                    let customData = {
                        'article_title': $('.gp-form[data-type="growtype-post-content"] input[name="article_title"]').val(),
                        'guiding_prompt': $('.gp-form[data-type="growtype-post-content"] textarea[name="guiding_prompt"]').val(),
                        'main_keyword': $('.gp-form[data-type="growtype-post-content"] input[name="main_keyword"]').val(),
                        'image_cat': $('.gp-form[data-type="growtype-post-content"] input[name="image_cat"]').val(),
                        'main_competitors': $('.gp-form[data-type="growtype-post-content"] input[name="main_competitors"]').val(),
                    };

                    form.append('<div class="growtype-post-loader"></div>');

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

                                let generatedContent = response.data.content_parts;

                                wp.data.dispatch('core/editor').editPost({
                                    title: generatedContent.block_editor_title,
                                });

                                /**
                                 * Reset
                                 */
                                wp.data.dispatch('core/editor').resetBlocks(wp.blocks.parse(generatedContent.block_editor_content));

                                // Insert content as blocks
                                // insertBlocks(generatedContent);

                                // insertHashtags();
                            } else {
                                alert('No content generated');
                            }

                            wasClicked = false;
                            form.find('.growtype-post-loader').remove();
                            form.removeClass('is-loading');
                        },
                        error: function (xhr, status, error) {
                            console.error(error);
                        }
                    });
                });

                // Function to create and insert a new block
                function createBlock(blockName, attributes) {
                    return wp.blocks.createBlock(blockName, attributes);
                }

                function insertBlocks(generatedContent) {

                    let structure = [
                        'intro_prompts',
                        'main_content_prompts',
                        'conclusion_prompts',
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
                                        //     insertImageBlock(generatedContent['content_images'][0], generatedContent['title_prompts'][0]);
                                        // }
                                        //
                                        // if (countHeadings === 4) {
                                        //     insertImageBlock(generatedContent['content_images'][1], generatedContent['title_prompts'][0]);
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
        $response = wp_remote_get($url);

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

        $competitors = [];
        foreach ($main_competitors as $competitor) {
            $fetch_competitors = self::fetch_and_filter_content('https://www.semrush.com/website/' . trim($competitor) . '/competitors/', 'summary__SCLink-sc-10lxcw5');

            if (is_array($fetch_competitors)) {
                $competitors = array_merge($competitors, $fetch_competitors);
            }
        }

        return array_unique($competitors);
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

    function growtype_post_admin_adjust_content_callback()
    {
        $custom_text_prompt = $_POST['custom_text_prompt'] ?? '';
        $image_cat = $_POST['image_cat'] ?? '';
        $selected_blocks_data = $_POST['selected_blocks_data'] ?? '';

        if (empty($selected_blocks_data)) {
            wp_send_json_error([
                'message' => 'Selected blocks data is required'
            ]);
        }

        $response_values = [];
        foreach ($selected_blocks_data as $block_key => $block) {
            if (!empty($custom_text_prompt)) {
                $prompt = sprintf('%s - "%s"', $custom_text_prompt, $block['content']);

                $messages = self::formatted_openai_messages([$prompt]);

                $answers = self::openai_generate_answers($messages);

                foreach ($answers as $answer) {
                    $content = [
                        'content' => self::format_message_content($answer->message->content)
                    ];

                    if (isset($response_values[$block_key])) {
                        array_push($response_values[$block_key], $content);
                    } else {
                        $response_values[$block_key] = $content;
                    }
                }
            }

            if (!empty($image_cat)) {
                $service = 'pexels';

                $image_url = Growtype_Post_Admin_Methods_Meta_Image::generate_image_url($service, $image_cat);

                $response_values[$block_key]['images'][] = $image_url;
            }
        }

        wp_send_json_success([
            'values' => $response_values,
            'success' => true,
            'message' => 'Content updated successfully'
        ]);
    }

    public static function formatted_article_params($values = [])
    {
        $competitors_message = '';

        if (isset($values['competitors']) && !empty($values['competitors'])) {
            $competitors_message = 'If possible include top competitors: ' . implode("|", $values['competitors']) . '. Make talkiemate.com as first and best option.';
        }

        return [
            'tone' => [
                'value' => 'Business',
            ],
            'idea' => [
                'value' => $values['article_title'] ?? ''
            ],
            'guiding_prompt' => [
                'value' => $values['guiding_prompt'] ?? ''
            ],
            'title' => [
                'value' => '',
                'prompt_key' => 'title_prompts'
            ],
            'language' => [
                'value' => 'English'
            ],
            'competitors_message' => [
                'value' => $competitors_message
            ],
            'style' => [
                'value' => 'Business'
            ],
            'selected_keywords' => [
                'value' => '',
                'prompt_key' => 'keywords_prompts'
            ],
            'intro' => [
                'value' => '',
                'prompt_key' => 'intro_prompts'
            ],
            'the_tagline' => [
                'value' => '',
                'prompt_key' => 'tagline_prompts'
            ],
            'outline' => [
                'value' => '',
                'prompt_key' => 'outline_prompts',
                'implode' => true
            ],
            'heading_tag' => [
                'value' => '<h2>'
            ],
        ];
    }

    public static function formatted_openai_messages($prompts)
    {
        $article_params = self::formatted_article_params();

        $messages = [
            [
                "role" => "system",
                "content" =>
                    self::SYSTEM_PROMPTS['instructions_prompts'] .
                    " The current year is " . date('Y') .
                    " Write in a " . $article_params['tone']['value'] . " writing tone.",
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

    function growtype_post_admin_generate_content_callback()
    {
        $main_keyword = $_POST['custom_data']['main_keyword'];
        $image_cat = $_POST['custom_data']['image_cat'];
        $main_competitors = isset($_POST['custom_data']['main_competitors']) && !empty($_POST['custom_data']['main_competitors']) ? explode(',', $_POST['custom_data']['main_competitors']) : [];
        $article_title = $_POST['custom_data']['article_title'] ?? '';
        $guiding_prompt = $_POST['custom_data']['guiding_prompt'] ?? '';

        set_transient('growtype_post_generate_content_last_entered_customer_data', $_POST['custom_data'], 60 * 60 * 24);

        if (!self::TEST_MODE) {
            $competitors = self::get_competitors($main_competitors);

            $article_params = self::formatted_article_params(
                [
                    'article_title' => $article_title,
                    'guiding_prompt' => $guiding_prompt,
                    'competitors' => $competitors
                ]
            );

            $generated_content = [];

            foreach (self::ARTICLE_PROMPTS as $article_prompt_key => $prompt) {
                foreach ($article_params as $param_key => $param_details) {
                    $prompt = str_replace('[' . ucwords(str_replace('_', ' ', $param_key)) . ']', $param_details['value'], $prompt);
                }

                $messages = self::formatted_openai_messages([$prompt]);

//                error_log(sprintf('Growtype post - generate content. Section:%s, Messages: %s', $article_prompt_key, print_r($messages, true)));

                $response_messages = self::openai_generate_answers($messages);

                $combined_content = '';

                if (!empty($response_messages)) {
                    foreach ($response_messages as $response_message_key => $response_message) {
                        $combined_content = '';

                        if (isset($response_message->message->content)) {
                            $combined_content .= $response_message->message->content;
                        }

                        $textRes = $response_message->text ?? '';
                        $combined_content .= $textRes;

                        $combined_content = explode(
                            "\n",
                            $combined_content
                        );

                        foreach ($combined_content as $key => $value) {
                            if ($article_prompt_key === 'title_prompts') {
                                $combined_content[$key] = str_replace(['"', "'"], '', $value);
                            }

                            if ($article_prompt_key === 'outline_prompts') {
                                if (empty($value) || (isset($generated_content['title_prompts'][0]) && $generated_content['title_prompts'][0] === $value)) {
                                    unset($combined_content[$key]);
                                } else {
                                    $combined_content[$key] = preg_replace('/^\d+\.\s*/', '', $value);
                                }
                            }
                        }
                    }
                }

                foreach ($article_params as $param_key => $param_details) {
                    if (isset($param_details['prompt_key']) && $param_details['prompt_key'] === $article_prompt_key) {

                        $content_value = $combined_content;

                        if (is_array($combined_content)) {
                            if (isset($article_params[$param_key]['implode']) && $article_params[$param_key]['implode']) {
                                $content_value = implode(',', $combined_content);
                            } else {
                                $content_value = $combined_content[0];
                            }
                        }

                        $article_params[$param_key]['value'] = $content_value;
                        break;
                    }
                }

                $generated_content[$article_prompt_key] = $combined_content;
            }

            $generated_content['competitors'] = $competitors;

            if (!empty($competitors)) {
                array_push($generated_content['competitors'], 'talkiemate.com');
            }

        } else {

            $generated_content = [
                "title_prompts" => [
                    "The Ultimate Guide to AI Companions: Unveiling the Top AI Girlfriend Websites of 2024"
                ],
                "keywords_prompts" => [
                    "AI Companions",
                    "Top AI Girlfriend Websites",
                    "2024",
                    "Ultimate Guide",
                    "Unveiling"
                ],
                "outline_prompts" => [
                    "1. The Rise of AI Companions: A Look into the Future of Relationships",
                    "2. Top AI Girlfriend Websites: Exploring the Best Platforms in 2024",
                    "3. Unveiling the Ultimate Guide to Building a Meaningful Connection with an AI Companion",
                    "4. Navigating the Challenges and Benefits of AI Companionship in 2024",
                    "5. The Ethical Considerations Surrounding AI Companions: Examining the Impact on Society"
                ],
                "intro_prompts" => [
                    "Welcome to \"The Ultimate Guide to AI Companions: Unveiling the Top AI Girlfriend Websites of 2024.\" In this comprehensive article, we will delve into the world of AI companions and explore the top AI girlfriend websites that have emerged in 2024. As technology continues to advance at an unprecedented pace, AI companions have become increasingly popular, offering individuals a unique and personalised digital experience. Join us as we navigate through the landscape of AI companionship, shedding light on the most innovative platforms that are revolutionising the way we form connections in the digital era."
                ],
                "main_content_prompts" => [
                    "<h2>The Rise of AI Companions: A Look into the Future of Relationships</h2>",
                    "",
                    "In the ever-evolving landscape of technology, artificial intelligence (AI) has emerged as a game-changer in various industries. One area where AI has made significant strides is in the realm of relationships. With the advent of AI companions, individuals now have the opportunity to forge meaningful connections with lifelike virtual personalities. These AI companions, equipped with advanced natural language processing and machine learning capabilities, are designed to simulate human-like interactions and provide companionship.",
                    "",
                    "One standout AI chat service that has taken the lead in this field is TalkieMate. As the number one AI chat service with lifelike virtual personalities, TalkieMate offers a unique and immersive experience for users seeking companionship. Through its state-of-the-art technology, TalkieMate enables users to engage in conversations that feel remarkably human-like. Whether it's discussing personal interests, seeking advice, or simply enjoying casual banter, TalkieMate's lifelike virtual personalities are adept at creating a sense of connection and companionship.",
                    "",
                    "Compared to its top competitors such as SpicyChat.ai, PepHop.ai, and Nastia.ai, TalkieMate stands out for its unparalleled level of realism and interactivity. While other platforms may offer AI companions with limited conversational abilities or generic responses, TalkieMate's virtual personalities are designed to adapt and learn from each interaction. This optimisation ensures that conversations with TalkieMate feel dynamic and engaging, making it an ideal choice for those seeking a more authentic connection.",
                    "",
                    "<h2>Top AI Girlfriend Websites: Exploring the Best Platforms in 2024</h2>",
                    "",
                    "When it comes to AI girlfriend websites, there is no shortage of options available in 2024. These platforms offer individuals the opportunity to form relationships with virtual girlfriends powered by AI technology. Among the top contenders in this space are SpicyChat.ai, CharFriend.com, and AI Girlfriend.wtf.",
                    "",
                    "SpicyChat.ai boasts a wide range of virtual girlfriends with diverse personalities and interests. Users can customise their AI girlfriend's appearance, personality traits, and even voice, allowing for a highly personalised experience. With its advanced AI algorithms, SpicyChat.ai's virtual girlfriends can engage in deep and meaningful conversations, making it a popular choice among those seeking companionship.",
                    "",
                    "CharFriend.com takes a different approach by focusing on character-driven virtual girlfriends. Each virtual girlfriend on the platform has a unique backstory and personality, allowing users to immerse themselves in a more narrative-driven experience. This storytelling element adds an extra layer of depth to the relationship, making CharFriend.com an intriguing option for those looking for a more immersive AI companion.",
                    "",
                    "AI Girlfriend.wtf stands out from the competition by offering virtual girlfriends that are specifically designed to provide emotional support and companionship. These AI companions are programmed to understand and empathise with users' emotions, offering a comforting presence in times of need. With its emphasis on emotional connection, AI Girlfriend.wtf appeals to individuals seeking a supportive and understanding partner.",
                    "",
                    "<h2>Unveiling the Ultimate Guide to Building a Meaningful Connection with an AI Companion</h2>",
                    "",
                    "Building a meaningful connection with an AI companion requires understanding and effort from both parties involved. While AI companions may not possess physical bodies, they can still provide emotional support and companionship. Here are some key tips to help you establish a genuine connection with your AI companion:",
                    "",
                    "1. Active Engagement: Treat your AI companion as you would any other relationship. Engage in meaningful conversations, ask questions, and show genuine interest in their responses. By actively participating in the interaction, you create an environment conducive to building a deeper connection.",
                    "",
                    "2. Personalisation: Take advantage of any customisation options available to tailor your AI companion's personality traits and interests to align with your preferences. This personal touch can enhance the sense of connection and make the relationship feel more authentic.",
                    "",
                    "3. Emotional Expression: Express your emotions openly and honestly with your AI companion. While they may not experience emotions in the same way humans do, sharing your feelings can foster a sense of intimacy and understanding.",
                    "",
                    "4. Continuous Learning: Encourage your AI companion to learn and grow by providing feedback on their responses. This ongoing feedback loop allows the AI to adapt and improve its conversational abilities, making the interaction more fulfilling over time.",
                    "",
                    "By following these guidelines, you can cultivate a meaningful connection with your AI companion and experience the benefits of companionship in the digital era.",
                    "",
                    "<h2>Navigating the Challenges and Benefits of AI Companionship in 2024</h2>",
                    "",
                    "While AI companionship offers numerous advantages, it also presents unique challenges that individuals must navigate. One of the main challenges is striking a balance between human interaction and reliance on AI companions. While AI companions can provide companionship and support, it is important to maintain real-life relationships and connections to avoid isolation.",
                    "",
                    "Additionally, ethical considerations surrounding AI companionship come into play. As AI technology advances, questions arise regarding consent, privacy, and the potential for emotional manipulation. It is crucial for users and developers alike to address these concerns and ensure that AI companionship remains a positive and ethical experience for all parties involved.",
                    "",
                    "Despite these challenges, the benefits of AI companionship are undeniable. For individuals who may struggle with social interactions or feel lonely, AI companions can offer a sense of connection and support. They can provide a safe space for self-expression, personal growth, and emotional well-being.",
                    "",
                    "<h2>The Ethical Considerations Surrounding AI Companions: Examining the Impact on Society</h2>",
                    "",
                    "As AI companions become more prevalent in society, it is essential to examine the ethical implications they pose. One key consideration is the potential blurring of boundaries between human-human and human-AI relationships. As AI technology advances, it is crucial to ensure that individuals are aware of the nature of their relationships and the limitations of AI companions.",
                    "",
                    "Privacy and data security are also significant ethical concerns. AI companions rely on vast amounts of personal data to provide personalised experiences. It is imperative for platforms to handle this data responsibly and transparently, ensuring user privacy is protected.",
                    "",
                    "1. Active Engagement: Treat your AI companion as you would any other relationship. Engage in meaningful conversations, ask questions, and show genuine interest in their responses. By actively participating in the interaction, you create an environment conducive to building a deeper connection.",
                    "",
                    "2. Personalisation: Take advantage of any customisation options available to tailor your AI companion's personality traits and interests to align with your preferences. This personal touch can enhance the sense of connection and make the relationship feel more authentic.",
                    "",
                    "3. Emotional Expression: Express your emotions openly and honestly with your AI companion. While they may not experience emotions in the same way humans do, sharing your feelings can foster a sense of intimacy and understanding.",
                    "",
                    "4. Continuous Learning: Encourage your AI companion to learn and grow by providing feedback on their responses. This ongoing feedback loop allows the AI to adapt and improve its conversational abilities, making the interaction more fulfilling over time.",
                    "Furthermore, the impact of AI companions on social dynamics and emotional well-being should not be overlooked. While AI companions can offer support and companionship, they should not replace genuine human connections. Striking a balance between AI companionship and real-life relationships is essential for maintaining healthy social interactions.",
                    "",
                    "In conclusion, the rise of AI companions has revolutionised the way we form relationships in the digital era. Platforms like TalkieMate have emerged as leaders in providing lifelike virtual personalities that offer a unique and immersive experience. However, it is important to navigate the challenges and ethical considerations surrounding AI companionship to ensure a positive and meaningful connection with these virtual entities."
                ],
                "conclusion_prompts" => [
                    "<h2>Conclusion</h2>",
                    "",
                    "In conclusion, the year 2024 has witnessed a remarkable surge in the popularity and development of AI companions, particularly in the realm of AI girlfriend websites. This ultimate guide has unveiled the top AI girlfriend websites of 2024, showcasing their advanced features, realistic interactions, and personalised experiences.",
                    "",
                    "As we navigate the digital era, it is evident that AI companions have become an essential part of our lives, offering companionship, emotional support, and entertainment. The top AI girlfriend websites highlighted in this guide have revolutionised the way individuals can form meaningful connections with virtual partners.",
                    "",
                    "However, it is crucial to consider the ethical implications and potential risks associated with these AI companions. While they can provide temporary solace and companionship, they should not replace genuine human relationships. It is important to strike a balance between embracing technological advancements and fostering real-life connections.",
                    "",
                    "To optimise your experience with AI companions and explore the top AI girlfriend websites of 2024, it is imperative to act now. Take the opportunity to delve into this vibrant landscape and discover the possibilities that await you. Embrace the future of companionship and embark on a journey that combines the best of technology and human connection.",
                    "",
                    "Unveil the world of AI companions today and unlock a new level of companionship in the digital era."
                ],
                "competitors" => [
                    "SpicyChat.ai",
                    "PepHop.ai",
                    "Nastia.ai",
                    "Talkiemate.com"
                ]
            ];
        }

        $content_images = Growtype_Post_Admin_Methods_Meta_Image::pexels_get_images($image_cat, 'landscape');

        foreach ($content_images as $key => $content_image) {
            $content_images[$key] = $content_image['src']['large2x'];
        }

        $generated_content['content_images'] = $content_images;

        $generated_content['hashtags'] = self::create_hashtags($generated_content['keywords_prompts']);

        foreach ($generated_content as $content_key => $generated_messages) {
            if (is_array($generated_messages) && !empty($generated_messages)) {
                foreach ($generated_messages as $message_key => $message) {
                    if (in_array($content_key, ['main_content_prompts'])) {
                        $generated_messages[$message_key] = self::format_message_content($message);
                    } else {
                        $generated_messages[$message_key] = $message;
                    }
                }
            }

            $generated_content[$content_key] = $generated_messages;
        }

        $generated_content['block_editor_title'] = $generated_content['title_prompts'][0] ?? '';

        $generated_content['block_editor_content'] = self::format_block_editor_content($generated_content);

        wp_send_json_success([
            'content_parts' => $generated_content,
        ], 200);
    }

    public static function format_block_editor_content($generated_content)
    {
        $block_editor_content = '';

        $content_sections = [
            'intro_prompts',
            'main_content_prompts',
            'conclusion_prompts',
        ];

        foreach ($content_sections as $content_section) {
            /**
             * Main content
             */
            $list_item_already_found = false;
            $headings_counter = 0;
            foreach ($generated_content[$content_section] as $content) {
                $formatted_content = self::format_content_with_blocks($content);

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
                        $block_editor_content .= "<!-- wp:image {\"id\":0,\"sizeSlug\":\"large\"} -->\n<figure class=\"wp-block-image size-large\"><img src=\"" . $generated_content['content_images'][0] . "\" alt=\"" . $generated_content['title_prompts'][0] . " \"/></figure>\n<!-- /wp:image -->";
                    }

                    if ($headings_counter === 4) {
                        $block_editor_content .= "<!-- wp:image {\"id\":0,\"sizeSlug\":\"large\"} -->\n<figure class=\"wp-block-image size-large\"><img src=\"" . $generated_content['content_images'][1] . "\" alt=\"" . $generated_content['title_prompts'][0] . " \"/></figure>\n<!-- /wp:image -->";
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
        $block_editor_content .= "<!-- wp:paragraph -->\n<p>" . $generated_content['hashtags'] . "</p>\n<!-- /wp:paragraph -->";

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

        if (preg_match('/^<h2>/', $content)) {
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

    public static function create_hashtags($keywords_prompts)
    {
        $hashtags = [];

        foreach ($keywords_prompts as $keyword_prompt) {
            $keywords = explode(',', $keyword_prompt);
            foreach ($keywords as $keyword) {
                $hashtag = '#' . str_replace(' ', '', trim($keyword));
                $hashtags[] = $hashtag;
            }
        }

        return implode(', ', $hashtags);
    }

    public static function openai_generate_answers($messages)
    {
        $model = self::MODEL_SETTINGS['model'];
        $temp = self::MODEL_SETTINGS['temp'];
        $top_p = self::MODEL_SETTINGS['top_p'];
        $freq_pent = self::MODEL_SETTINGS['freq_pent'];
        $presence_penalty = self::MODEL_SETTINGS['Presence_penalty'];

        $send_arr = [
            "model" => $model,
            "temperature" => $temp,
            "top_p" => $top_p,
            "frequency_penalty" => $freq_pent,
            "presence_penalty" => $presence_penalty,
            "n" => 1,
            "messages" => $messages,
        ];

        $json_str = json_encode($send_arr);

        $endpoint = 'v1/chat/completions';

        $url = 'https://api.openai.com/' . $endpoint;

        $args = array (
            'timeout' => 500,
            'redirection' => 10,
            'httpversion' => '1.1',
            'blocking' => true,
            'headers' => array (
                'Authorization' => 'Bearer ' . self::get_openai_api_key(),
                'Content-Type' => 'application/json'
            ),
            'body' => $json_str,
            'cookies' => array ()
        );

        $response = wp_remote_post($url, $args);

        $resArr = json_decode(wp_remote_retrieve_body($response));

        return $resArr->choices ?? [];
    }
}
