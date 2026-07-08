/*
 * ISC License
 *
 * Copyright (c) 2025 idnovate.com
 * idnovate is a Registered Trademark & Property of idnovate.com, innovación y desarrollo SCP
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
 * REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
 * INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
 * LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
 * OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
 * PERFORMANCE OF THIS SOFTWARE.
 *
 * @author    idnovate
 * @copyright 2025 idnovate.com
 * @license   https://www.isc.org/licenses/ https://opensource.org/licenses/ISC ISC License
 */

!function ($) {

  "use strict"; // jshint ;_;


 /* TAB CLASS DEFINITION
  * ==================== */
  var Tab = function (element) {
    this.element = $(element)
  }

  Tab.prototype = {

    constructor: Tab

  , show: function () {
      var $this = this.element
        , $ul = $this.closest('ul:not(.dropdown-menu)')
        , selector = $this.attr('data-target')
        , previous
        , $target
        , e

      if (!selector) {
        selector = $this.attr('href')
        selector = selector && selector.replace(/.*(?=#[^\s]*$)/, '') // strip for ie7
      }

      if ( $this.parent('li').hasClass('active') ) return

      previous = $ul.find('.active:last a')[0]

      e = $.Event('show', {
        relatedTarget: previous
      })

      $this.trigger(e)

      if (e.isDefaultPrevented()) return

      $target = $(selector)

      this.activate($this.parent('li'), $ul)
      this.activate($target, $target.parent(), function () {
        $this.trigger({
          type: 'shown'
        , relatedTarget: previous
        })
      })
    }

  , activate: function ( element, container, callback) {
      var $active = container.find('> .active')
        , transition = callback
            && $.support.transition
            && $active.hasClass('fade')

      function next() {
        $active
          .removeClass('active')
          .find('> .dropdown-menu > .active')
          .removeClass('active')

        element.addClass('active')

        if (transition) {
          element[0].offsetWidth // reflow for transition
          element.addClass('in')
        } else {
          element.removeClass('fade')
        }

        if ( element.parent('.dropdown-menu') ) {
          element.closest('li.dropdown').addClass('active')
        }

        callback && callback()
      }

      transition ?
        $active.one($.support.transition.end, next) :
        next()

      $active.removeClass('in')
    }
  }


 /* TAB PLUGIN DEFINITION
  * ===================== */
  var old = $.fn.tab

  $.fn.tab = function ( option ) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('tab')
      if (!data) $this.data('tab', (data = new Tab(this)))
      if (typeof option == 'string') data[option]()
    })
  }

  $.fn.tab.Constructor = Tab


 /* TAB NO CONFLICT
  * =============== */
  $.fn.tab.noConflict = function () {
    $.fn.tab = old
    return this
  }


 /* TAB DATA-API
  * ============ */
  $(document).on('click.tab.data-api', '[data-toggle="tab"], [data-toggle="pill"]', function (e) {
    e.preventDefault()
    $(this).tab('show')
  })

}(window.jQuery);

(function($){
    $.createTabs = function(){
        // Create tab block
        if ($('#content').find('form [id^="fieldset"]').length) {
          blockTab = '<div class="col-lg-2"><div id="module-tabs" class="list-group">';
          $.each($('#content').find('form [id^="fieldset"]'), function() {
              heading = $(this).find('.panel-heading, legend');
              blockTab += '<a href="#'+$(this).attr('id')+'" class="list-group-item" data-toggle="tab">'+heading.html()+'</a>';
              $(this).addClass('tab-pane');
          });
          blockTab += '</div></div>';

          // Add content
          $('#content').addClass('row');
          $('#content').find('form')/*.wrap("<div class='row'></div>")*/.before(blockTab).addClass('col-lg-10 tab-content');

          // Remove <br>
          $('#content').find('form > br').remove();

          // Display first tab
          $('#content').find('#module-tabs a:first').tab('show').addClass('active');

          // Toggle panel
          $("#content").find(".list-group-item").on('click', function() {
              var el = $(this).parent().closest('.list-group').children('.active');
              if (el.hasClass('active')) {
                  el.removeClass('active');
              }
              $(this).addClass('active');
          });

          $('#content').find('form').after($('<div class="clearfix">'));

          // Custom
          $('#module-tabs').parent().show();
          $('div.ui-widget-header').css('height', '25px');
          $('div.form-group div.col-lg-2').show();
        }

        // Javascript to enable link to tab
        var url = document.location.toString();
        if (url.match('#')) {
            $('#module-tabs a[href=#'+url.split('#')[1]+']').click();
        }

        // Change hash for page-reload
        $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
            // window.location.hash = e.target.hash;
            history.pushState(null,null,e.target.hash);
        });
    };
})(jQuery);

// Fire function
$(document).ready(function() {
    if (location.hash) {
      setTimeout(function() {

        window.scrollTo(0, 0);
      }, 1);
    }

    $.createTabs();
});
