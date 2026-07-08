/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

if ((typeof mncklevu.pageMeta) && mncklevu.pageMeta) {
    var klevu_page_meta = mncklevu.pageMeta;
}

if ((typeof klevu_pageCategory !== 'undefined') && klevu_pageCategory) {
    if ((typeof klevu_pageManufacturer === 'undefined') || !klevu_pageManufacturer) {
        localStorage.setItem('klv_sort_productList', 'RELEVANCE');
    }

    sessionStorage.setItem('klevu_pageCategory', klevu_pageCategory);
} else {
    localStorage.setItem('klv_sort_productList', 'RELEVANCE');
}

klevu.interactive(function() {
    var urlParams = new URLSearchParams(window.location.search);
    var currentPage = parseInt(urlParams.get('page')) || 1; // Default to page 1 if not found
    var resultsPerPage = 12; // Adjust this based on your requirements
    var startIndex = (currentPage - 1) * resultsPerPage;
    var options = {
        url: {
            landing: mncklevu.searchResultsPageFriendlyUrl + '?page=' + currentPage,
            protocolFull: klevu.settings.url.protocol + '//',
            search: mncklevu.klevuProxyUrl
        },
        localSettings: true,
        search: {
            minChars: mncklevu.searchBoxMinimalCharacterCount,
            searchBoxSelector: '#' + mncklevu.searchBoxId,
            apiKey: '', 
            infiniteScrollOffsetLanding: 400,
            start: startIndex, // Starting index for results
            count: resultsPerPage, // Number of results per page
            currentPage: currentPage // Pass the page number in the options
        },
        recs: {
            apiKey: ''
        },
        analytics: {
            apiKey:''
        },
        theme: {
            modules: {
                resultInfiniteScroll: {
                    searchResultsPage: {
                        enable: false
                    },
                    categoryPage: {
                        enable: false
                    }
                }
            }
        }
    };
    klevu(options);
});
if (typeof products_per_page !== 'undefined') {
    localStorage.setItem('klv_limits_productList', products_per_page);
}
function clickOnPaginateWithText(targetText) {
    var urlParams = new URLSearchParams(window.location.search); 
    if (parseInt(urlParams.get('page')) > 1){ 
     var klevu_last_page = parseInt(urlParams.get('page')); // Default to page 1 if not found
    } else if (parseInt(sessionStorage.getItem('klevu_last_page')) > 1) {
     var klevu_last_page = parseInt(sessionStorage.getItem('klevu_last_page')) ; 
    }else{
     var klevu_last_page = 0;
     return ;
    } 
    // Check every 200ms until the element exists
    setTimeout(function() {
        const $links = $('a.klevuPaginate');
       
        if ($links.length > 0 && klevu_last_page > 1 ) {
        
            $links.each(function() {
                if ($(this).text().trim() === targetText) {
                var offset = products_per_page * (klevu_last_page - 1) ;
                $(this).attr('data-offset',offset);
                $('.kuResultsProducts').empty();
            	this.click(); 
            	return false; // Break out of .each loop
                }
            });
        }
    },1000); // Check every 200ms
}

// Usage
clickOnPaginateWithText(">");

const klevu_uc_userOptions = {
    priceFormatter: function (code) {
        return mncklevu.currencies[code];
    }
}

klevu_addToCartEnabled = true;

klevu({
    powerUp: {
        quick: false,
        landing: false,
        catnav: false,
        recsModule: true
    }
});

klevu.coreEvent.build({
    name: 'quickSearchOverride',
    fire: function() {
        return klevu.getGlobalSetting('flags.setRemoteConfigQuick.build', false);
    },
    maxCount: 150,
    delay: 100
});

klevu.coreEvent.attach('quickSearchOverride', {
    name: 'attachToQuickSearchOverride',
    fire: function() {
        klevu.search.quick.getScope().chains.request.control.addAfter('initRequest', {
            name: 'modifyQuickQuery',
            fire: function(data, scope) {
                klevu.search.modules.addOverrideToQuery(data, scope);
            }
        });

        klevu.search.quick.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customKlevuQuickTemplateBase'),
            'klevuTemplateBase',
            true
        );

        klevu.search.quick.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customKlevuQuickAutoSuggestions'),
            'klevuQuickAutoSuggestions',
            true
        );

        klevu.search.quick.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customKlevuQuickProductBlockTitleHeader'),
            'klevuQuickProductBlockTitleHeader',
            true
        );

        klevu.search.quick.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customKlevuQuickProducts'),
            'klevuQuickProducts',
            true
        );

        klevu.search.quick.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customKlevuQuickProductBlock'),
            'klevuQuickProductBlock',
            true
        );

        klevu.search.quick.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customSearchResultProductVATLabelQuick'),
            'searchResultProductVATLabelQuick',
            true
        );

        klevu.search.quick.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customKlevuTemplateNoResultFoundQuick'),
            'noResultsFoundQuick',
            true
        );

        klevu({
            powerUp: {
                quick: true
            }
        });
    }
});

klevu.coreEvent.build({
    name: 'landingPageOverride',
    fire: function() {
        return klevu.getGlobalSetting('flags.setRemoteConfigLanding.build', false);
    },
    maxCount: 150,
    delay: 100
});

klevu.coreEvent.attach('landingPageOverride', {
    name: 'attachToLandingPageOverride',
    fire: function() {
        klevu.search.landing.getScope().chains.request.build.add({
            name: 'increaseFilterLimit',
            fire: function (data, scope) {
                klevu.each(data.request.current.recordQueries, function (key, query) {
                    query.filters.filtersToReturn.options.limit = 1000;
                });
            }
        });

        klevu.search.landing.getScope().chains.request.control.addAfter('initRequest', {
            name: 'modifyLandingQuery',
            fire: function(data, scope) {
                klevu.search.modules.addOverrideToQuery(data, scope);
            }
        });

        klevu.search.landing.getScope().translator.addTranslation('<b>%s</b> productList', mncklevu.translations.tabResults.productList);
        klevu.search.landing.getScope().translator.addTranslation('<b>%s</b> contentList', mncklevu.translations.tabResults.contentList);
        klevu.search.landing.getScope().translator.addTranslation('price', mncklevu.translations.filter.price);

        klevu.search.landing.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customKlevuLandingTemplateTabResults'),
            'tab-results',
            true
        );

        klevu.search.landing.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customKlevuLandingTemplateResults'),
            'results',
            true
        );

        klevu.search.landing.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customKuFilterTagsTemplate'),
            'kuFilterTagsTemplate',
            true
        );

        klevu.search.landing.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customKlevuLandingTemplateFilters'),
            'filters',
            true
        );

        klevu.search.landing.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customKlevuLandingTemplateSortBy'),
            'sortBy',
            true
        );

        klevu.search.landing.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customKuTemplateLandingResultsViewSwitch'),
            'kuTemplateLandingResultsViewSwitch',
            true
        );

        klevu.search.landing.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customKlevuLandingTemplateProductBlock'),
            'productBlock',
            true
        );

        klevu.search.landing.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customSearchResultProductVATLabel'),
            'searchResultProductVATLabel',
            true
        );

        klevu.search.landing.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customLandingSearchResultProductStock'),
            'landingProductStock',
            true
        );

        klevu.search.landing.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customLandingPageProductAddToCart'),
            'landingPageProductAddToCart',
            true
        );

        klevu.search.landing.getScope().template.setHelper('getSortBy', function(e) {
            var t = klevu.getSetting(klevu.settings, 'settings.storage');
            var a = '';

            switch (t.sort.getElement(e) == e ? 'RELEVANCE' : t.sort.getElement(e)) {
                case 'RELEVANCE':        a = mncklevu.sortByOptions.RELEVANCE;        break;
                case 'PRICE_ASC':        a = mncklevu.sortByOptions.PRICE_ASC;        break;
                case 'PRICE_DESC':       a = mncklevu.sortByOptions.PRICE_DESC;       break;
                case 'NAME_ASC':         a = mncklevu.sortByOptions.NAME_ASC;         break;
                case 'NAME_DESC':        a = mncklevu.sortByOptions.NAME_DESC;        break;
                case 'NEW_ARRIVAL_DESC': a = mncklevu.sortByOptions.NEW_ARRIVAL_DESC; break;

                default:                 a = mncklevu.sortByOptions.RELEVANCE;
            }

            return a;
        });

        // // Load more button - Begin
        // klevu.search.landing.getScope().chains.template.events.add({
        //     name: 'hideLoadMoreButtonForWindowScroll',
        //     fire: function(data, scope) {
        //         return true;
        //     }
        // });

        // window.onscroll = function(e) {
        //     if (window.innerHeight + Math.ceil(window.pageYOffset) >= document.body.offsetHeight - 50) {
        //         var hasAlreadyTriggered = klevu.getObjectPath(
        //             klevu.search.landing.getScope().data,
        //             'context.triggeredFromInfiniteScroll'
        //         );

        //         if (!hasAlreadyTriggered) {
        //             return true;
        //         }
        //     }
        // };
        // // Load more button - End

        try {
            klevu.search.landing.getScope().chains.template.events.add({
                name: 'setBodyMinHeight',
                fire: function(data, scope) {
                    document.body.style.minHeight = `0`;
                    const currentDocumentHeight = document.documentElement.scrollHeight;
                    document.body.style.minHeight = `${currentDocumentHeight}px`;
                }
            });
        } catch (error) {
            console.log(error);
        }

        try {
            klevu.search.landing.getScope().chains.events.keyUp.remove({
                name: 'scrollToTop'
            });
        } catch (error) {
            console.log(error);
        }

        klevu({
            powerUp: {
                landing: true
            }
        });
    }
});

klevu.coreEvent.build({
    name: 'categoryPageOverride',
    fire: function() {
        return klevu.getGlobalSetting('flags.setRemoteConfigCatnav.build', false);
    },
    maxCount: 150,
    delay: 100
});

klevu.coreEvent.attach('categoryPageOverride', {
    name: 'attachToCategoryPageOverride',
    fire: function() {
        klevu.search.catnav.getScope().chains.request.build.add({
            name: 'increaseFilterLimit',
            fire: function (data, scope) {
                klevu.each(data.request.current.recordQueries, function (key, query) {
                    query.filters.filtersToReturn.options.limit = 1000;
                });
            }
        });

        klevu.search.catnav.getScope().chains.request.control.addAfter('initRequest', {
            name: 'modifyCatnavQuery',
            fire: function(data, scope) {
                klevu.search.modules.addOverrideToQuery(data, scope);
            }
        });

        klevu.search.catnav.getScope().translator.addTranslation('price', mncklevu.translations.filter.price);

        klevu.search.catnav.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customKlevuLandingTemplateResults'),
            'results',
            true
        );

        klevu.search.catnav.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customKlevuLandingTemplateResultsHeadingTitle'),
            'klevuLandingTemplateResultsHeadingTitle',
            true
        );

        klevu.search.catnav.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customKuFilterTagsTemplate'),
            'kuFilterTagsTemplate',
            true
        );

        klevu.search.catnav.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customKlevuLandingTemplateFilters'),
            'filters',
            true
        );

        klevu.search.catnav.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customKlevuLandingTemplateSortBy'),
            'sortBy',
            true
        );

        klevu.search.catnav.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customKuTemplateLandingResultsViewSwitch'),
            'kuTemplateLandingResultsViewSwitch',
            true
        );

        klevu.search.catnav.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customKlevuLandingTemplateProductBlock'),
            'productBlock',
            true
        );

        klevu.search.catnav.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customSearchResultProductVATLabel'),
            'searchResultProductVATLabel',
            true
        );

        klevu.search.catnav.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customLandingSearchResultProductStock'),
            'landingProductStock',
            true
        );

        klevu.search.catnav.getScope().template.setTemplate(
            klevu.dom.helpers.getHTML('#customLandingPageProductAddToCart'),
            'landingPageProductAddToCart',
            true
        );

        if ((typeof klevu_pageManufacturer !== 'undefined') && klevu_pageManufacturer) {
            klevu.search.catnav.getScope().template.setHelper('getSortBy', function(e) {
                var t = klevu.getSetting(klevu.settings, 'settings.storage');
                var a = '';
    
                switch (t.sort.getElement(e) == e ? 'RELEVANCE' : t.sort.getElement(e)) {
                    case 'RELEVANCE':        a = mncklevu.sortByOptions.RELEVANCE;        break;
                    case 'PRICE_ASC':        a = mncklevu.sortByOptions.PRICE_ASC;        break;
                    case 'PRICE_DESC':       a = mncklevu.sortByOptions.PRICE_DESC;       break;
                    case 'NAME_ASC':         a = mncklevu.sortByOptions.NAME_ASC;         break;
                    case 'NAME_DESC':        a = mncklevu.sortByOptions.NAME_DESC;        break;
                    case 'NEW_ARRIVAL_DESC': a = mncklevu.sortByOptions.NEW_ARRIVAL_DESC; break;
    
                    default:                 a = mncklevu.sortByOptions.RELEVANCE;
                }
    
                return a;
            });
        } else {
            klevu.search.catnav.getScope().template.setHelper('getSortByCustom', function(e) {
                var t = klevu.getSetting(klevu.settings, 'settings.storage');
                var a = '';
    
                switch (t.sort.getElement(e) == e ? 'RELEVANCE' : t.sort.getElement(e)) {
                    case 'RELEVANCE':        a = mncklevu.sortByOptions.RELEVANCE;        break;
                    case 'PRICE_ASC':        a = mncklevu.sortByOptions.PRICE_ASC;        break;
                    case 'PRICE_DESC':       a = mncklevu.sortByOptions.PRICE_DESC;       break;
                    case 'NAME_ASC':         a = mncklevu.sortByOptions.NAME_ASC;         break;
                    case 'NAME_DESC':        a = mncklevu.sortByOptions.NAME_DESC;        break;
                    case 'NEW_ARRIVAL_DESC': a = mncklevu.sortByOptions.NEW_ARRIVAL_DESC; break;
    
                    default:                 a = mncklevu.sortByOptions.RELEVANCE;
                }
    
                return a;
            });
        }

        try {
            klevu.search.catnav.getScope().chains.template.events.add({
                name: 'setBodyMinHeight',
                fire: function(data, scope) {
                    document.body.style.minHeight = `0`;
                    const currentDocumentHeight = document.documentElement.scrollHeight;
                    document.body.style.minHeight = `${currentDocumentHeight}px`;
                }
            });
        } catch (error) {
            console.log(error);
        }

        try {
            klevu.search.catnav.getScope().chains.events.keyUp.remove({
                name: 'scrollToTop'
            });
        } catch (error) {
            console.log(error);
        }

        klevu({
            powerUp: {
                catnav: true
            }
        });
    }
});

(function(klevu) {
    klevu.extend(true, klevu.search.modules, {
        addOverrideToQuery: function(data, scope) {
            klevu.each(data.request.current.recordQueries, function(key, query) {
                klevu.setObjectPath(
                    data,
                    'localOverrides.query.' + query.id + '.settings.priceFieldSuffix',
                    mncklevu.priceFieldSuffix
                );
            });
        }
    });
})(klevu);

klevu.coreEvent.attach('setRemoteConfigRecsBaseUpdates', {
    name: 'klevuRECSCustomizationCurrency',
    fire: function() {
        klevu.recs.base.getScope().chains.search.control.add({
            name: 'productCurrencyRECS',
            fire: function (data, scope) {
                var parentScope = scope.recsScope;

                parentScope.searchObject.getScope().currency.setCurrencys({
                    [mncklevu.priceFieldSuffix]: {
                        string: mncklevu.currencies[mncklevu.priceFieldSuffix].symbol,
                        format: mncklevu.currencies[mncklevu.priceFieldSuffix].spaceBetweenAmountAndSymbol ?
                            '%s %s' : '%s%s',
                        atEnd: !mncklevu.currencies[mncklevu.priceFieldSuffix].symbolOnLeft,
                        precision: mncklevu.currencies[mncklevu.priceFieldSuffix].decimalDigits,
                        thousands: mncklevu.currencies[mncklevu.priceFieldSuffix].thousandsSeparator,
                        decimal: mncklevu.currencies[mncklevu.priceFieldSuffix].decimalSeparator,
                        grouping: 3
                    }
                });

                parentScope.searchObject.getScope().currency.mergeToGlobal();
            }
        });
    }
});
 

klevu.modifyRequest('all', function (data, scope) {
    klevu.each(data.request.current.recordQueries, function (key, query) {
        klevu.search.modules.addOverrideToQuery(data, scope);
    });
});
klevu.modifyRequest("landing,catnav", function(data, scope){
    // Apply the selected page value from the current URL string            
    klevu.search.modules.addPaginationToURL.base.getAndUpdatePagination(data, scope);
});

function mncklevuCloseMobileFilterPanel() {
    var $filterWrapper = $('#search_filters_wrapper');

    $('body').removeClass('open-ku-product-filters').removeClass('open-ku-product-sorting');

    if (!$filterWrapper.hasClass('active')) {
        $('body').removeClass('hidden');
    }
}

klevu.afterTemplateRender("landing,catnav", function(data, scope){
    // Add selected page value to the URL string
    klevu.search.modules.addPaginationToURL.base.setPagination(data, scope);
    mncklevuCloseMobileFilterPanel();

});
for (let i = 0; i < localStorage.length; i++) {
    const key = localStorage.key(i);
    console.log(`${key}: ${localStorage.getItem(key)}`);
}
                      
// addPaginationToURL module
(function(klevu) {
    klevu.extend(true, klevu.search.modules, {
        addPaginationToURL: {
            base: {
		setPagination: function(data, scope, queryId) {
		    var hasPaginationEnabled = klevu.getSetting(klevu, "settings.theme.modules.pagination.searchResultsPage.enable");
		    if (hasPaginationEnabled === true) {
			var activeQueryId = klevu.getObjectPath(data, "context.activeQueryId");
			if (queryId && queryId.length) {
			    activeQueryId = queryId;
			}

			var paginationQueryParam = "page";
			var activeQueryMeta = klevu.getObjectPath(data, "template.query." + activeQueryId + ".meta");
			var paginationValuesQueryParam = 0;

			if (activeQueryMeta) {
			    var productListLimit = activeQueryMeta.noOfResults;
			    paginationValuesQueryParam = Math.ceil(activeQueryMeta.offset / productListLimit) + 1;
			}

			var searchPath = window.location.search;

			// If page is 1, remove `page` from URL (if exists)
			if (paginationValuesQueryParam === 1) {
			    var url = new URL(window.location.href);
			    if (url.searchParams.has(paginationQueryParam)) {
				url.searchParams.delete(paginationQueryParam);
				if (typeof window.history.replaceState !== "undefined") {
				    window.history.replaceState({}, "", url.pathname + url.search);
				}
			    }
			    return; // Exit after cleanup
			}

			// Else: update page number in URL
			var updatedPath = klevu.dom.helpers.updateQueryStringParameter(searchPath, paginationQueryParam, paginationValuesQueryParam);
			if (typeof window.history.replaceState !== "undefined") {
			    window.history.replaceState({}, "", updatedPath);
			} else {
			    console.log("This browser does not have the support of window.history or window.history.replaceState");
			}
            // Store current page in sessionStorage
            sessionStorage.setItem('klevu_last_page', paginationValuesQueryParam);

		    }
		},
                getAndUpdatePagination: function(data, scope, queryId) {
                    var hasAlreadyTriggered = klevu.getObjectPath(scope.kScope, "element.kScope.getAndUpdatePaginationTriggered");
                    if (hasAlreadyTriggered === true) {
                        return;
                    }
                    var matchedQueryParamId = ""
                      , matchedQueryParamValue = "";
                    var recordQueries = klevu.getObjectPath(data, "request.current.recordQueries");
                    if (recordQueries && recordQueries.length) {
                        klevu.each(recordQueries, function(key, query) {
                            if (query.id) {
                                var paginationFromURL = klevu.dom.helpers.getQueryStringValue(query.id + "PgNo");
                                if (paginationFromURL && paginationFromURL.length) {
                                    matchedQueryParamId = query.id;
                                    matchedQueryParamValue = paginationFromURL;
                                }
                            }
                        })
                    }
                    klevu.setObjectPath(scope.kScope, "element.kScope.getAndUpdatePaginationTriggered", true);
                    var activeQueryId = klevu.getObjectPath(data, "context.activeQueryId");
                    if (queryId && queryId.length) {
                        activeQueryId = queryId;
                    } else if (matchedQueryParamId.length && matchedQueryParamValue.length) {
                        activeQueryId = matchedQueryParamId;
                        var storage = klevu.getSetting(scope.kScope.settings, "settings.storage");
                        if (storage.tabs) {
                            storage.tabs.setStorage("local");
                            storage.tabs.mergeFromGlobal();
                            storage.tabs.addElement("active", activeQueryId);
                            storage.tabs.mergeToGlobal();
                        }
                    }
                    var paginationQueryParam = activeQueryId + "PgNo";
                    var paginationFromURL = klevu.dom.helpers.getQueryStringValue(paginationQueryParam);
                    paginationFromURL = Number(paginationFromURL);
                    if (paginationFromURL && paginationFromURL > 1) {
                        var recordQueries = klevu.getObjectPath(data, "request.current.recordQueries");
                        if (recordQueries.length) {
                            klevu.each(recordQueries, function(key, recordQuery) {
                                if (recordQuery.id === activeQueryId) {
                                    var limit = klevu.getObjectPath(recordQuery, "settings.limit");
                                    limit = Number(limit);
                                    if (limit > 0) {
                                        var expectedOffset = (paginationFromURL - 1) * limit;
                                        klevu.setObjectPath(data, "localOverrides.query." + activeQueryId + ".settings.offset", expectedOffset);
                                    }
                                }
                            });
                        }
                    }
                }
            },
            build: true
        }
    });
}
)(klevu);

$(document).on('click', '.kuProductListMobilePopUpBackdrop, .kuProductFiltersCloseButton, .kuProductSortingCloseButton', function() {
    mncklevuCloseMobileFilterPanel();
}).on('click', '.kuProductFiltersOpenButton', function() {
    $('body').addClass('open-ku-product-filters');
}).on('click', '.kuProductSortingOpenButton', function() {
    $('body').addClass('open-ku-product-sorting');
}).on('click', '.kuMobileSorting .kuMobileSort', function() {
    $('.klevuLanding .productList .kuDropSortBy .kuDropOption[data-value="' + $(this).data('value') + '"]').trigger('click');
}).on('input', '.kuFilterSearchBox', function(e) {
    var $input = $(this);
    var searchWords = $input.val().trim().replace(/[\s]+/g, ' ').toLowerCase().split(' ');

    $input.parent().find('.kufacet-text').each(function() {
        var $option = $(this);
        var optionName = $option.text().toLowerCase();
        var hide = true;

        for (var i = 0; i < searchWords.length; i++) {
            if (optionName.indexOf(searchWords[i]) !== -1) {
                hide = false;
                break;
            }
        }

        $option.closest('li').css({display: hide ? 'none' : 'inline-block'});
    });
}).on('click', '.kuFilterBox .kuShowOpt', function() {
    var $container = $(this).parent();
    var $optionList = $container.find('ul');
    
    $container.find('.kuFilterSearchBox').val('');
    $optionList.find('li').css({display: 'inline-block'});
    $optionList.scrollTop(0);
});

(function() {
    function send(params) {
        var xhr = new XMLHttpRequest();
        xhr.withCredentials = false;

        xhr.addEventListener('readystatechange', function() {
            if(this.readyState === 4) {
                console.log(this.responseText);
            }
        });

        xhr.open('GET', 'https://stats.ksearchnet.com/analytics/productTracking?' + params);
        xhr.setRequestHeader('Accept', 'application/xml, text/xml, */*; q=0.01');
        xhr.send();
    }

    if (Array.isArray(mncklevu.orderData) && mncklevu.orderData.length) {
        for (var i = 0; i < mncklevu.orderData.length; i++) {
            send(mncklevu.orderData[i]);
        }
    }
})();

if (typeof(swiper_options) === 'undefined') {
    var swiper_options = [];
}

function klevu_bindRECSTemplateElementEvents(templateData, recsKey) {
    var selector = '#product-list-' + recsKey;
    if (!$(selector).length) {
        return;
    }

    var loop = false;
    var slidesPerView = 8;
    var slidesPerGroup = 8;

    var breakpoints = {
        1440: {slidesPerView: 7, slidesPerGroup: 7},
        1200: {slidesPerView: 7, slidesPerGroup: 7},
        992: {slidesPerView: 5, slidesPerGroup: 5},
        768: {slidesPerView: 4, slidesPerGroup: 4},
        480: {slidesPerView: 3, slidesPerGroup: 3},
        379: {slidesPerView: 2, slidesPerGroup: 2}
    };

    if ($('body#product').length) {
        slidesPerGroup = 1;

        breakpoints = {
            1439: {slidesPerView: 6, loop: true},
            991: {slidesPerView: 3, loop: true},
            768: {slidesPerView: 3, loop: true},
            480: {slidesPerView: 3, loop: true},
            379: {slidesPerView: 2, loop: true}
        };
    }

    swiper_options.push({
        speed: 400,
        autoplayDisableOnInteraction: true,
        loop: loop,
        lazyLoading: true,
        onLazyImageReady: function(swiper, slide, image) {
            if ($(image).hasClass('front-image')) {
                $(image).closest('.is_lazy').removeClass('is_lazy');//also in pro-lazy.js
            }
        },
        lazyLoadingOnTransitionStart: true,
        lazyLoadingInPrevNext: true,
        lazyLoadingInPrevNextAmount: 1,//hao xiang zui xiao dou yao preload yi ping
        nextButton: selector + ' .swiper-button-outer.swiper-button-next',
        prevButton: selector + ' .swiper-button-outer.swiper-button-prev',
        freeMode: false,
        spaceBetween: 0, //new
        slidesPerView: slidesPerView,
        slidesPerGroup: slidesPerGroup,
        breakpoints: breakpoints,
        watchSlidesProgress: true,
        watchSlidesVisibility: true,
        onInit : function (swiper) {
            $(swiper.container).removeClass('swiper_loading').addClass('swiper_loaded');
            if ($(swiper.slides).length == $(swiper.slides).filter('.swiper-slide-visible').length) {
                $(swiper.params.nextButton).hide();
                $(swiper.params.prevButton).hide();
            } else {
                $(swiper.params.nextButton).show();
                $(swiper.params.prevButton).show();
            }
        },
        //temp fix, loop breaks when roundlenghts and autoplay
        roundLengths: true,
        inviewwatcher: true,
        id_st: selector + ' .products_sldier_swiper'
    });

    prestashop.emit('updatedProductListDOM');
    $('#' + recsKey).find('.kuRECSHeader .title_block_inner').attr('href', $('#' + recsKey).data('link'));
}
