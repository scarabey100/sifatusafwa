{**
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
 *}
<script>
    // Replace occurrences of YouTube and Elementor URLs in the DOM
    document.addEventListener('DOMContentLoaded', function() {
        replaceUrlsInDOM();
    });

    function replaceUrlsInDOM() {
        // YouTube replacements
        replaceStringInDOM(/youtube\.com\/embed\//g, 'youtube-nocookie.com/embed/');
        replaceStringInDOM(/youtube\.com\/s\/player\//g, 'youtube-nocookie.com/s/player/');
        replaceStringInDOM(/youtube\.com\/yts\/jsbin\//g, 'youtube-nocookie.com/yts/jsbin/');
        replaceStringInDOM(/youtube\.com\/iframe_api\//g, 'youtube-nocookie.com/iframe_api/');

        // Elementor replacements
        replaceStringInDOM(/\/data-video-id=/g, ' data-video-id-blocked=');

        // Intercept script additions
        observeScriptAdditions();
    }

    function replaceStringInDOM(pattern, replacement) {
        // Get all text nodes in the DOM
        var walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, null, false);
        var textNode;

        // Iterate through each text node
        while (textNode = walker.nextNode()) {
            // Replace occurrences of the pattern within the text node's data
            textNode.data = textNode.data.replace(pattern, replacement);
        }

        // Replace occurrences of the pattern within attribute values
        var elements = document.querySelectorAll('*');
        for (var i = 0; i < elements.length; i++) {
            var attributes = elements[i].attributes;
            for (var j = 0; j < attributes.length; j++) {
                var attribute = attributes[j];
                attribute.value = attribute.value.replace(pattern, replacement);
            }
        }
    }

    function observeScriptAdditions() {
        // Select the target node
        var target = document.body;

        // Create an observer instance
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                // Check if a script element is being added or its attributes are modified
                if (mutation.type === 'childList' || mutation.type === 'attributes') {
                    // Check if "youtube.com" appears in any property of the node
                    var addedNodes = mutation.addedNodes || [];
                    for (var i = 0; i < addedNodes.length; i++) {
                        var node = addedNodes[i];
                        checkAndReplace(node);
                    }
                }
            });
        });

        // Configuration of the observer
        var config = { childList: true, subtree: true, attributes: true };

        // Start observing the target node for configured mutations
        observer.observe(target, config);

        // Initial check for script elements
        var scripts = document.getElementsByTagName('script');
        for (var i = 0; i < scripts.length; i++) {
            checkAndReplace(scripts[i]);
        }

        // Additionally, handle script additions using jQuery
        $(document).on('DOMNodeInserted', 'script', function() {
            checkAndReplace(this);
        });
    }

    function checkAndReplace(node) {
        // Replace the src URL
        if (node.src) {
            node.src = node.src.replace(/youtube\.com\/iframe_api/g, 'youtube-nocookie.com/iframe_api');
            node.src = node.src.replace(/youtube\.com\/embed\//g, 'youtube-nocookie.com/embed/');
            node.src = node.src.replace(/youtube\.com\/s\/player\//g, 'youtube-nocookie.com/s/player/');
            node.src = node.src.replace(/youtube\.com\/yts\/jsbin\//g, 'youtube-nocookie.com/yts/jsbin/');
        }

        // Check if the node has attributes
        if (node.attributes) {
            // Iterate over the attributes
            for (var i = 0; i < node.attributes.length; i++) {
                var attribute = node.attributes[i];
                // Replace occurrences of 'youtube.com' in attribute values
                if (attribute.value.includes('youtube.com')) {
                    node.setAttribute(attribute.name, attribute.value.replace(/youtube\.com\/iframe_api\//g, 'youtube-nocookie.com/iframe_api/'));
                    node.setAttribute(attribute.name, attribute.value.replace(/youtube\.com\/embed\//g, 'youtube-nocookie.com/embed/'));
                    node.setAttribute(attribute.name, attribute.value.replace(/youtube\.com\/s\/player\//g, 'youtube-nocookie.com/s/player/'));
                    node.setAttribute(attribute.name, attribute.value.replace(/youtube\.com\/yts\/jsbin\//g, 'youtube-nocookie.com/yts/jsbin/'));
                }
                node.setAttribute(attribute.name, attribute.value.replace(/\/data-video-id=/g, ' data-video-id-blocked='));
            }
        }

    }
</script>
