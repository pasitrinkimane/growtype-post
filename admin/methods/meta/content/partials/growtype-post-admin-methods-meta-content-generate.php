<?php

class Growtype_Post_Admin_Methods_Meta_Content_Generate
{
    public function __construct()
    {
        add_action('wp_ajax_growtype_post_admin_generate_content', array ($this, 'growtype_post_admin_generate_content_callback'));
        add_action('wp_ajax_growtype_post_admin_generate_prompt', array ($this, 'growtype_post_admin_generate_prompt_callback'));
    }

    function growtype_post_admin_generate_content_callback()
    {
        $custom_data = $_POST['custom_data'];

        Growtype_Post_Admin_Methods_Meta::update_user_entered_latest_generate_content_data($custom_data);

        $generate_response = $this->generate_content($custom_data);

        if ($generate_response['success'] === false) {
            wp_send_json_error($generate_response, 500);
        }

        wp_send_json_success($generate_response, 200);
    }

    function growtype_post_admin_generate_prompt_callback()
    {
        $custom_data = $_POST['custom_data'];

        Growtype_Post_Admin_Methods_Meta::update_user_entered_latest_generate_content_data($custom_data);

        $generated_prompt = $this->generate_prompt($custom_data);

        ob_start();
        ?>
        <div style="padding-bottom: 70px;max-height: 400px;overflow: scroll;" data-generated-prompt-content>
            <?= $generated_prompt ?>
        </div>
        <div style="position: absolute;bottom: 0;left: 0;right: 0;background: white;text-align: center;padding: 20px;color: white;">
            <div onclick="copyPromptToClipboard()" style="background: #000000;padding: 10px;cursor: pointer;" data-generated-prompt-copy>Copy prompt</div>
        </div>
        <script type="text/javascript">
            function copyPromptToClipboard() {
                const generatedPromptElement = document.querySelector('[data-generated-prompt-content]');
                const copyButtonElement = document.querySelector('[data-generated-prompt-copy]');
                if (generatedPromptElement) {
                    const tempInput = document.createElement('textarea');
                    tempInput.value = generatedPromptElement.innerText;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    document.execCommand('copy');
                    document.body.removeChild(tempInput);

                    // Change the button text to "Copied"
                    if (copyButtonElement) {
                        copyButtonElement.innerText = 'Prompt was copied to clipboard';
                        // Revert the text back to "Copy prompt" after 2 seconds
                        setTimeout(() => {
                            copyButtonElement.innerText = 'Copy prompt';
                        }, 2000);
                    }
                }
            }
        </script>
        <?php

        $generate_response['generated_content'] = ob_get_clean();

        if (isset($generate_response['success']) && $generate_response['success'] === false) {
            wp_send_json_error($generate_response, 500);
        }

        wp_send_json_success($generate_response, 200);
    }

    public static function generate_prompt($custom_data)
    {
        if (isset($custom_data['post_id'])) {
            $post = get_post($custom_data['post_id']);
            $custom_data['post_content'] = $post->post_content;
        }

        $article_params = Growtype_Post_Admin_Methods_Meta_Content::get_formatted_prompt_variables($custom_data);

        if ($custom_data['prompt_type'] === 'improve_content') {
            $generated = Growtype_Post_Admin_Methods_Meta_Content_Prompt::IMPROVE_ARTICLE_PROMPT;
        } else {
            $generated = Growtype_Post_Admin_Methods_Meta_Content_Prompt::GENERATE_ARTICLE_PROMPT;
        }

        // Replace placeholders with the corresponding parameter values
        foreach ($article_params as $param_key => $param_details) {
            $replace = Growtype_Post_Admin_Methods_Meta_Content::format_prompt_variable_key($param_key);
            $generated = str_replace($replace, $param_details['value'], $generated);
        }

        return $generated;
    }

    public function generate_content($custom_data)
    {
        $content_type = $custom_data['content_type'];
        $main_keyword = $custom_data['main_keyword'];
        $image_cat = $custom_data['image_cat'];
        $image_service = $custom_data['image_service'];
        $main_competitors = isset($custom_data['main_competitors']) && !empty($custom_data['main_competitors']) ? explode(',', trim($custom_data['main_competitors'])) : [];
        $article_topic = $custom_data['topic'] ?? '';
        $guiding_prompt = $custom_data['guiding_prompt'] ?? '';
        $cta_url = $custom_data['cta_url'] ?? '';
        $post_id = $custom_data['post_id'] ?? '';
        $generating_params = $custom_data['generating_params'] ?? [];

        $block_editor = [];
        $extra_details = [];
        $generated_content = [];

        try {
            if ($content_type === 'social_post') {
                $data_structure = [
                    'title' => '',
                    'caption' => '',
                    'tags' => [],
                ];

                $prompt = sprintf(
                    'Create a Social post text. The topic is "%s", with the main keyword being "%s". Use the following guiding prompt: "%s". Return the response strictly in this JSON format -> %s',
                    $article_topic,
                    $main_keyword,
                    $guiding_prompt,
                    json_encode($data_structure)
                );

                $messages = Growtype_Post_Admin_Methods_Meta_Content::format_openai_messages([$prompt]);

                $generated_answer = Growtype_Post_Service_Openai::generate($messages, $generating_params);

                if ($generated_answer['success'] === false) {
                    return $generated_answer;
                }

                $generated_answer_content = $generated_answer['content'];

                foreach ($generated_answer_content as $answer) {
                    $answer_content = $answer->message->content;

                    $content = json_decode($answer_content, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $jsonStart = strpos($answer_content, '{"title":');
                        $jsonEnd = strpos($answer_content, '}', $jsonStart) + 1;
                        $answer_content = substr($answer_content, $jsonStart, $jsonEnd - $jsonStart);

                        $content = json_decode($answer_content, true);
                    }

                    $block_editor['content'] = isset($block_editor['content']) ? $block_editor['content'] : '';
                    $block_editor['content'] .= 'Caption: ' . (!empty($content['caption']) ? $content['caption'] : '') . '<br/>';

                    // Format and append tags if they exist
                    if (!empty($content['tags']) && is_array($content['tags'])) {
                        $block_editor['content'] .= 'Tags: ' . implode(', ', $content['tags']);
                    }
                }

                $block_editor['title'] = substr(strip_tags($block_editor['content']), 0, 70);
                $block_editor['content'] = Growtype_Post_Admin_Methods_Meta_Content::format_content_with_blocks($block_editor['content']);
            } else {
                $competitors = Growtype_Post_Admin_Methods_Meta_Content::get_competitors($main_competitors);

                $article_params = Growtype_Post_Admin_Methods_Meta_Content::get_formatted_prompt_variables($custom_data);

                if (Growtype_Post_Admin_Methods_Meta_Content::LOG_PROCESSES) {
                    error_log('--------------------------------');
                }

                foreach (Growtype_Post_Admin_Methods_Meta_Content_Prompt::ARTICLE_PROMPTS as $article_prompt_key => $prompt) {
                    foreach ($article_params as $param_key => $param_details) {
                        $prompt = str_replace(Growtype_Post_Admin_Methods_Meta_Content::format_prompt_variable_key($param_key), $param_details['value'], $prompt);
                    }

                    $messages = Growtype_Post_Admin_Methods_Meta_Content::format_openai_messages([$prompt]);

                    if (Growtype_Post_Admin_Methods_Meta_Content::LOG_PROCESSES) {
                        error_log(sprintf('Growtype Post - generate content. Section: %s, Messages: %s', $article_prompt_key, print_r($messages, true)));
                    }

                    $generated_answer = Growtype_Post_Service_Openai::generate($messages, $generating_params);

                    if ($generated_answer['success'] === false) {
                        return $generated_answer;
                    }

                    $generated_answer_content = $generated_answer['content'];

                    $combined_content = '';

                    if (!empty($generated_answer_content)) {
                        foreach ($generated_answer_content as $response_message_key => $response_message) {
                            $combined_content = '';

                            if (isset($response_message['message']['content'])) {
                                $combined_content .= $response_message['message']['content'];
                            }

                            $textRes = $response_message['text'] ?? '';
                            $combined_content .= $textRes;

                            $combined_content = explode(
                                "\n",
                                $combined_content
                            );

                            foreach ($combined_content as $key => $value) {
                                if ($article_prompt_key === 'title') {
                                    $combined_content[$key] = str_replace(['"', "'"], '', $value);
                                }

                                if ($article_prompt_key === 'outline') {
                                    if (empty($value) || (isset($generated_content['title'][0]) && $generated_content['title'][0] === $value)) {
                                        unset($combined_content[$key]);
                                    } else {
                                        $combined_content[$key] = preg_replace('/^\d+\.\s*/', '', $value);
                                    }
                                }
                            }
                        }
                    }

                    if (isset($article_params[$article_prompt_key]['value']) && empty($article_params[$article_prompt_key]['value'])) {
                        $content_value = $combined_content;

                        if (is_array($combined_content)) {
                            if (isset($article_params[$article_prompt_key]['implode']) && $article_params[$article_prompt_key]['implode']) {
                                $content_value = implode('|', $combined_content);
                            } else {
                                $content_value = $combined_content[0];
                            }
                        }

                        $article_params[$article_prompt_key]['value'] = $content_value;
                    }

                    if (Growtype_Post_Admin_Methods_Meta_Content::LOG_PROCESSES) {
                        error_log(sprintf('Response: %s', print_r($combined_content, true)));
                    }

                    $generated_content[$article_prompt_key] = $combined_content;
                }

                $extra_details['competitors'] = $competitors;

                $content_images = Growtype_Post_Admin_Methods_Meta_Image::generate_image_urls($image_service, $image_cat);

                foreach ($content_images as $key => $content_image) {
                    $content_images[$key] = $content_image['url'];
                }

                $extra_details['images'] = $content_images;

                $extra_details['hashtags'] = Growtype_Post_Admin_Methods_Meta_Content::create_hashtags($generated_content['keywords']);

                foreach ($generated_content as $content_key => $generated_messages) {
                    if (is_array($generated_messages) && !empty($generated_messages)) {
                        foreach ($generated_messages as $message_key => $message) {
                            if (in_array($content_key, ['main_content'])) {
                                $generated_messages[$message_key] = Growtype_Post_Admin_Methods_Meta_Content::format_message_content($message);
                            } else {
                                $generated_messages[$message_key] = $message;
                            }
                        }
                    }

                    $generated_content[$content_key] = $generated_messages;
                }

                $block_editor['title'] = $generated_content['title'][0] ?? '';
                $block_editor['content'] = Growtype_Post_Admin_Methods_Meta_Content::format_block_editor_content($generated_content, $extra_details);
            }

            if (!empty($block_editor['content'])) {
                $category_slug = $content_type;
                $category_name = ucwords(str_replace('_', ' ', $category_slug));
                $existing_category = term_exists($category_slug, 'category');

                if (!$existing_category) {
                    $new_category = wp_insert_term(
                        $category_name, // Category name
                        'category',     // Taxonomy type
                        [
                            'slug' => $category_slug, // Custom slug
                        ]
                    );

                    $term_id = $new_category['term_id'];
                } else {
                    $term_id = $existing_category['term_id'];
                }

                wp_set_post_terms($post_id, [$term_id], 'category');
            }

            if (!empty($content_images)) {
                $featured_image_url = $content_images[array_rand($content_images)] ?? '';
                $extra_details['featured_image_id'] = !empty($featured_image_url) ? growtype_post_upload_image_from_url($featured_image_url) : '';
            }
        } catch (\Exception $e) {
            error_log(sprintf('Growtype post. Content generating error %s', $e->getMessage()));
        }

        return [
            'success' => true,
            'message' => 'Content successfully generated',
            'block_editor' => $block_editor,
            'extra_details' => $extra_details,
            'generated_content' => $generated_content,
        ];
    }

    public static function test_data()
    {
        $generated_content = [
            "title" => [
                "The Ultimate Guide to AI Companions: Unveiling the Top AI Girlfriend Websites of 2024"
            ],
            "keywords" => [
                "AI Companions",
                "Top AI Girlfriend Websites",
                "2024",
                "Ultimate Guide",
                "Unveiling"
            ],
            "outline" => [
                "1. The Rise of AI Companions: A Look into the Future of Relationships",
                "2. Top AI Girlfriend Websites: Exploring the Best Platforms in 2024",
                "3. Unveiling the Ultimate Guide to Building a Meaningful Connection with an AI Companion",
                "4. Navigating the Challenges and Benefits of AI Companionship in 2024",
                "5. The Ethical Considerations Surrounding AI Companions: Examining the Impact on Society"
            ],
            "intro" => [
                "Welcome to \"The Ultimate Guide to AI Companions: Unveiling the Top AI Girlfriend Websites of 2024.\" In this comprehensive article, we will delve into the world of AI companions and explore the top AI girlfriend websites that have emerged in 2024. As technology continues to advance at an unprecedented pace, AI companions have become increasingly popular, offering individuals a unique and personalised digital experience. Join us as we navigate through the landscape of AI companionship, shedding light on the most innovative platforms that are revolutionising the way we form connections in the digital era."
            ],
            "main_content" => [
                "<h2>The Rise of AI Companions: A Look into the Future of Relationships</h2>",
                "",
                "In the ever-evolving landscape of technology, artificial intelligence (AI) has emerged as a game-changer in various industries. One area where AI has made significant strides is in the realm of relationships. With the advent of AI companions, individuals now have the opportunity to forge meaningful connections with lifelike virtual personalities. These AI companions, equipped with advanced natural language processing and machine learning capabilities, are designed to simulate human-like interactions and provide companionship.",
                "",
                "One standout AI chat service that has taken the lead in this field is ChatAiGirl. As the number one AI chat service with lifelike virtual personalities, ChatAiGirl offers a unique and immersive experience for users seeking companionship. Through its state-of-the-art technology, ChatAiGirl enables users to engage in conversations that feel remarkably human-like. Whether it's discussing personal interests, seeking advice, or simply enjoying casual banter, ChatAiGirl's lifelike virtual personalities are adept at creating a sense of connection and companionship.",
                "",
                "Compared to its top competitors such as SpicyChat.ai, PepHop.ai, and Nastia.ai, ChatAiGirl stands out for its unparalleled level of realism and interactivity. While other platforms may offer AI companions with limited conversational abilities or generic responses, ChatAiGirl's virtual personalities are designed to adapt and learn from each interaction. This optimisation ensures that conversations with ChatAiGirl feel dynamic and engaging, making it an ideal choice for those seeking a more authentic connection.",
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
                "In conclusion, the rise of AI companions has revolutionised the way we form relationships in the digital era. Platforms like ChatAiGirl have emerged as leaders in providing lifelike virtual personalities that offer a unique and immersive experience. However, it is important to navigate the challenges and ethical considerations surrounding AI companionship to ensure a positive and meaningful connection with these virtual entities."
            ],
            "conclusion" => [
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
            ]
        ];

        $extra_details['competitors'] = [
            "SpicyChat.ai",
            "PepHop.ai",
            "Nastia.ai",
            "ChatAiGirl.com"
        ];
    }
}
