/**
 * 2012 - 2025 HiPresta
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    HiPresta <support@hipresta.com>
 * @copyright HiPresta 2025
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *
 * @website   https://hipresta.com
 */
$(document).ready(function() {
    var options = {
        callback: function (value) {
            // console.log('TypeWatch callback: (' + (this.type || this.nodeName) + ') ' + value);

            if (!value || value.length < 3) {
                $('.hi-faq-search-results').html('');

                return;
            }

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: HiFaq.search_url,
                data: {
                    ajax : true,
                    action : 'searchFAQ',
                    secure_key : HiFaq.key,
                    query: value
                },
                success: function(response) {
                    $('.hi-faq-search-results').html(response.content);
                }
            });
        },
        wait: 300,
        highlight: true,
        allowSubmit: false,
        captureLength: 0
    }

    $('#hi_faq_top_search_input').typeWatch(options);

    $(document)
        .on('click', 'html', function(e){
            if (e.target.id != 'hi-faq-search-container' && $(e.target).parents('#hi-faq-search-container').length == 0) {
                $('.hi-faq-search-results').html('');
            }
        })
        .on('click', '.hi-faq-question-link', function(e) {
            e.preventDefault();

            $(this).closest('.hi-faq-item').toggleClass('hi-faq-item__active');
            $(this).closest('.hi-faq-item').find('.hi-faq-answer').slideToggle();
        })
        .on('click', '.hi-faq-feedback-dismiss', function() {
            $(this).closest('.hi-faq-feedback-block').hide();
        })
        .on('click', '.hi-faq-feedback-sad', function() {
            $(this).closest('.hi-faq-feedback-block').find('.hi-faq-feedback-wrapper').addClass('hi-module-hide');
            $(this).closest('.hi-faq-feedback-block').find('.hi-faq-feedback-form-wrapper').removeClass('hi-module-hide');
        }).on('click', '.hi-faq-feedback-good', function() {
            let $this = $(this);
            let $parent = $this.closest('.hi-faq-feedback-block');
            let idFaq = $parent.attr('data-id-faq');
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: HiFaq.mainUrl,
                data: {
                    ajax : true,
                    action : 'feedback',
                    secure_key : HiFaq.key,
                    feedback: 1,
                    idFaq: idFaq
                },
                beforeSend: function() {
                    $parent.find('.hi-faq-feedback-good').addClass('hi-faq-feedback-button-disabled');
                },
                success: function(response) {
                    $parent.find('.hi-faq-feedback-good').removeClass('hi-faq-feedback-button-disabled');

                    if (response.error) {
                        alert(response.error);
                    } else {
                        $parent.find('.hi-faq-feedback-wrapper').addClass('hi-module-hide');
                        $parent.find('.hi-faq-feedback-success-wrapper').removeClass('hi-module-hide');

                        setTimeout(function(){
                            $parent.remove();
                        }, 4000);
                    }
                },
                error: function(jqXHR, error, errorThrown) {
                    $parent.find('.hi-faq-feedback-good').removeClass('hi-faq-feedback-button-disabled');
                    
                    alert('Something went wrong, please refresh the page and try again');
                }
            });
        }).on('click', '.hi-faq-feedback-comment', function() {
            let $this = $(this);
            let $parent = $this.closest('.hi-faq-feedback-block');
            let idFaq = $parent.attr('data-id-faq');
            let comment = $parent.find('.hi-faq-comment-area').val();
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: HiFaq.mainUrl,
                data: {
                    ajax : true,
                    action : 'feedback',
                    secure_key : HiFaq.key,
                    feedback: 0,
                    idFaq: idFaq,
                    comment: comment
                },
                beforeSend: function() {
                    $parent.find('.hi-faq-feedback-comment').addClass('hi-faq-feedback-button-disabled');
                },
                success: function(response) {
                    $parent.find('.hi-faq-feedback-comment').removeClass('hi-faq-feedback-button-disabled');

                    if (response.error) {
                        alert(response.error);
                    } else {
                        $parent.find('.hi-faq-feedback-wrapper').addClass('hi-module-hide');
                        $parent.find('.hi-faq-feedback-form-wrapper').addClass('hi-module-hide');
                        $parent.find('.hi-faq-feedback-success-wrapper').removeClass('hi-module-hide');

                        setTimeout(function(){
                            $parent.remove();
                        }, 4000);
                    }
                },
                error: function(jqXHR, error, errorThrown) {
                    $parent.find('.hi-faq-feedback-comment').removeClass('hi-faq-feedback-button-disabled');
                    
                    alert('Something went wrong, please refresh the page and try again');
                }
            });
        }).on('submit', '#hi-faq-search-bar-form', function(e) {
            e.preventDefault();
        });
    
});
