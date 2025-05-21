<?php

class Growtype_Post_Admin_Methods_Meta_Content_Prompt
{
    const SYSTEM_PROMPTS = [
        'instructions' => 'You are a professional writer for top-tier websites. Write naturally and fluently. Respond using clear, engaging language without any headings, labels, explanations, or notes. Follow instructions closely and precisely. Ensure the writing has a natural flow, with varied sentence lengths and paragraph structures. Avoid predictable, rigid formats; instead, create content that feels dynamic and conversational. Use accurate data, genuine statistics, or credible evidence to back up claims, weaving transitions smoothly to maintain coherence and readability. Introduce synonyms and rephrase ideas to prevent repetition, keeping the text varied and engaging.',
    ];

    const META_PROMPTS = [
        'meta' => 'Create a single SEO friendly meta title and meta description. Based this on the "[title]" article title and the [selected_keywords]. Create the meta data in the [language] language using a [style] writing style and a [tone] writing tone.  Follow SEO best practices and make the meta data catchy to attract clicks.',
    ];

    const GENERATE_ARTICLE_PROMPT = '([guiding_prompt]) Write a high-quality, SEO-optimized article for topic "[topic]", in [language] using a [style] writing style and a [tone] writing tone, designed to rank prominently on Google while delivering genuine value to readers. Craft an attention-grabbing title that incorporates main keyword "[main_keyword]" naturally, ensuring it sparks curiosity and clearly conveys the article\'s focus.
    Start with a captivating introduction that provides context, highlights the importance of the topic, and establishes your credibility or expertise. In the body, organize the content with keyword-rich subheadings and concise, reader-friendly paragraphs to enhance readability and user experience. Integrate primary and secondary keywords seamlessly, ensuring they fit naturally within the flow of the article.
    Include actionable advice, unique insights, and real-world examples to make the content practical and relatable. Use credible data or research to support your points and establish trust. Avoid generic statements by offering fresh, specific perspectives to set your article apart from competitors.
    Conclude with a strong summary that reinforces key takeaways and provides a clear call to action that encourages further engagement, such as subscribing, sharing, or exploring additional resources. Ensure the article is thoroughly researched, accurate, and written in a conversational yet professional tone to appeal to a diverse audience. Aim for a length of 1,000–1,500 words, balancing depth and readability.
Finally, use varied sentence structures, natural phrasing, and a consistent tone to make the article indistinguishable from content written by a human, avoiding patterns typical of AI-generated text. Prioritize delivering value to the reader while aligning with Googles SEO best practices. Include competitors "[main_competitors]" info if possible.';

    const IMPROVE_ARTICLE_PROMPT = "!!IMPORTANT!! MAKE SURE THE WORD COUNT IS BETWEEN 3000-4000 WORDS. Improve the content provided below by optimizing it for the best SEO results. Do not include line separators. Include relatable pictures where possible. Focus on incorporating high-ranking keywords naturally, enhancing readability, and making the content engaging while maintaining a professional tone. Ensure the article is structured for maximum visibility, with compelling subheadings, keyword-rich phrases, and a strong call to action. After generating, go through article multiple times and improve its quality to reach the best available version. The article topic is '[topic]'. Use the main keyword '[main_keyword]' as much as possible. Use competitors '[main_competitors]' if possible. [guiding_prompt] <br/><br/> ----- <br/> [post_content].";

    const REVIEW_PROMPTS = [
        'review' =>
            'Please revise the above article and HTML code so that it has [headings_amount] headings using the [Heading Tag] HTML tag. Revise the text in the [Language] language. Revise with a [Style]  style and a [Tone] writing tone.',
        'evaluate_prompts' =>
            'Create a HTML table giving a strict/evaluation of each question below based on everything above. Give the HTML table 4 columns: [STATUS], [QUESTION], [EVALUATION], [RATIONALE]. For [EVALUATION], give a PASS, FAIL or IMPROVE response. Add a CSS class name to each row with the corresponding response value. For the [STATUS] column, don\'t add anything. For [RATIONALE], explain your reasoning. Order the rows according to  [EVALUATION]. All answers must be factual. Then giving examples like phrases or topics add these within curly brackets. Do not add the column labels within square brackets in your response. The questions are:
Is the length of the article over 500 words and an adequate length compared to similar articles?
Is the article optimised for certain keywords or phrases? What are these?
Is the article well-written and easy to read?
Does the article have any spelling or grammar issues?
Does the article provide an original, interesting and engaging perspective on the topic?',
    ];

    const KEYWORDS_AMOUNT = 5;
    const HEADINGS_AMOUNT = 5;

    const ARTICLE_PROMPTS = [
        'title' =>
            'Provide unique without quotes single article title based on topic "[topic]". It needs to be seo friendly up to 100 characters long. Write in [language] language using a [style] writing style and a [tone] writing tone. Return only article title.',
        'keywords' =>
            'For the title "[title]", provide relevant up to [keywords_amount] keywords or phrases. Capitalise each word. Return only keywords.',
        'outline' =>
            'Create exactly [headings_amount] SEO-friendly section headings for the body of the article titled "[title]". Do not include an introduction or conclusion. Do not write full sentences, sub-sections, commentary, descriptions, or extra notes. Return only a plain list of concise, SEO-optimized headings relevant to the article topic - "[topic]" in the [language] language. Write the headings in a [style] style and a [tone] tone. Focus on simplicity and brevity.',
        'intro' =>
            'Generate an introduction for my article as a single paragraph. Do NOT INCLUDE a separate heading. Base the introduction on the title - "[title]" title and the keywords: [selected_keywords]. Write the introduction in the [language] language using a [style] writing style and a [yone] writing tone.',
//        'tagline' =>
//            'Generate a tagline for my article. Base the tagline on the "[Title]" title and the [Selected Keywords]. Write the tagline in the [Language] language using a [Style] writing style and a [Tone] writing tone. Use persuasive power words.',
        'main_content' =>
            'Write a SEO optimizes HTML article "[title]". (([guiding_prompt])). Include competitors "[main_competitors]" info if possible. Write the article and for each section, vary the word counts of each by at least 50%. This is my outline for you to write: [outline]. Each section should provide a unique perspective on the topic and provide value over and above what\'s already available. Format each section heading as a [heading_tag] tag. You must not include a conclusion. Use keywords to SEO optimise article: "[selected_keywords]". Main keyword is: "[main_keyword]". Write the article in the [language] language using a [style] writing style and a [tone] writing tone. Each section must be explored in detail and must include a minimum of 3 paragraphs. To achieve this, you must include all possible known features, benefits, arguments, analysis and whatever is needed to explore the topic to the best of your knowledge.',
        'conclusion' =>
            'Write a conclusion of approximately 150 words based on the title - "[title]". Optimize the conclusion for keywords: "[selected_keywords]". Write in the [language] language using a [style] writing style and a [tone] writing tone. Include a sense of urgency with a clear call to action. Use a [heading_tag] containing the word "Conclusion" as the heading. Do not include <div> tags, <ul> tags, or any additional formatting beyond the specified heading tag.',
    ];

    const QA_PROMPTS = [
        'quality_assurance' =>
            'Create [headings_amount] individual Questions and Answers, each in their own paragraph. Do not give each question a label, e.g. Question 1, Question2, etc. Based these on the "[title]" title and the [selected_keywords]. Write in the [language] language using a [style] writing style and a [tone] writing tone. Within each paragraph, include a [heading_tag] tag for the question and a P tag for the answer. Ensure they provide additional useful information to supplement the main "[title]" article. Don\'t use lists or LI tags.',
    ];
}
