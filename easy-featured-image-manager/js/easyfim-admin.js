jQuery(document).ready(function($) {
    $('body').on('click', '.easyfim-change-image', function(e) {
        e.preventDefault();

        var button = $(this);
        var postId = button.data('post-id');
        var frame = wp.media({
            title: 'Select or Upload Featured Image',
            button: {
                text: 'Set Featured Image'
            },
            multiple: false
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $.post(ajaxurl, {
                action: 'easyfim_set_featured_image',
                post_id: postId,
                attachment_id: attachment.id,
                _wpnonce: easyfimVars.nonce
            }, function(response) {
                if (response.success) {
                    button.parent().html(response.data.thumbnail);
                } else {
                    alert('Error: ' + response.data.message);
                }
            });
        });

        frame.open();
    });

    $('body').on('click', '.easyfim-remove-image', function(e) {
        e.preventDefault();

        var button = $(this);
        var postId = button.data('post-id');
        $.post(ajaxurl, {
            action: 'easyfim_remove_featured_image',
            post_id: postId,
            _wpnonce: easyfimVars.nonce
        }, function(response) {
            if (response.success) {
                button.parent().html(response.data.thumbnail);
            } else {
                alert('Error: ' + response.data.message);
            }
        });
    });
   
    $('body').on('click', 'button.editinline', function() {
        var post_id = $(this).closest('tr').attr('id').replace('post-', '');
        $('.easyfim_post_id').val(post_id);
    });

    $('body').on('click', 'button.save', function() {
        $('.easyfim_post_id').val('');
    });
});