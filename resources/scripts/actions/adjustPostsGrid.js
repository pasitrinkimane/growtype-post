export function adjustPostsGrid(wrapper) {
    let postsAmount = wrapper.find('.growtype-post-single:visible').length;
    let wrapperId = $(wrapper).attr('id');

    if (postsAmount === 0) {
        wrapper.find('.growtype-post-container').css('--growtype-post-posts-grid-columns-count', 0);
    } else {
        let posts_grid_initial_columns_count = $(wrapper).find('.growtype-post-container').attr('data-columns');

        if (posts_grid_initial_columns_count) {
            wrapper.find('.growtype-post-container').css('--growtype-post-posts-grid-columns-count', posts_grid_initial_columns_count);
        }
    }
}
