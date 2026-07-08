{**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
<script type="template/klevu" id="customKlevuQuickTemplateBase">
    <div class="klevu-fluid kuPreventDocumentClick">
        <div id="klevuSearchingArea" class="klevuQuickSearchingArea">
            <div ku-container data-container-id="ku_quick_main_container" data-container-role="main">
                <% if(data.suggestions.autosuggestion && (data.suggestions.autosuggestion.length> 0)) { %>
                    <header ku-container data-container-id="ku_quick_main_header_container" data-container-role="header">
                        <section ku-block data-block-id="ku_quick_main_header_site_navigation">
                            <div class="klevuSuggestionsBlock">                            
                                <%=helper.render('klevuQuickAutoSuggestions',scope) %>
                            </div>
                        </section>
                    </header>
                <% } %>

                <% if(data.showQuickFacetedLayoutCharLimitMessage == true) { %>
                    <% var quickFacetedLayoutMessage = (data.quickFacetedLayoutMinCharsTextValue) ? data.quickFacetedLayoutMinCharsTextValue : ""; %>
                    <% if(quickFacetedLayoutMessage != "") { %>
                        <span class="kuFacetedLayoutMinCharText"><%= helper.translate(quickFacetedLayoutMessage) %></span>
                    <% } %>
                <% } else { %>
                    <% if(data.query.productList) { %>               
                        <%= helper.render('klevuQuickProducts',scope) %>
                    <% } %>
                <% } %>
            </div>
            <div class="kuClearBoth"></div>
        </div>
    </div>
</script>

<script type="template/klevu" id="customKlevuQuickAutoSuggestions">
    <% if(data.suggestions.autosuggestion) { %>
        <% if(data.suggestions.autosuggestion.length> 0 ) { %>
            <% var queryParam = klevu.getSetting(klevu, "settings.url.queryParam"); %>
            <div class="klevuAutoSuggestionsWrap klevuAutosuggestions">
                <div class="klevuSuggestionHeading">
                    <span class="klevuHeadingText"> {l s='Search suggestions' mod='mncklevu'}</span>
                </div>
                <ul>
                    <% helper.each(data.suggestions.autosuggestion,function(key,suggestion){ %>
                        <li tabindex="-1"><a target="_self" href="<%=helper.buildUrl(data.settings.landingUrl, queryParam , helper.stripHtml(suggestion.suggest))%>" data-content="<%=helper.stripHtml(suggestion.suggest) %>" class="klevu-track-click"> <%=suggestion.suggest %> </a></li>
                    <% }); %>
                </ul>
            </div>
        <% } %>
    <% } %>
</script>

<script type="template/klevu" id="customKlevuQuickProductBlockTitleHeader">
    <div class="klevuSuggestionHeading">
        <span class="klevuHeadingText">{l s='Product matches' mod='mncklevu'}</span>
    </div>
</script>

<script type="template/klevu" id="customKlevuQuickProducts">
    <% if(data.query.productList) { %>
        <% if(data.query.productList.result.length > 0 ) { %>
            <div ku-container data-container-id="ku_quick_main_content_container" data-container-role="content" data-content="productList" >
                <section ku-container data-container-id="ku_quick_main_content_center" data-container-position="center" data-container-role="center">
                    <header ku-block data-block-id="ku_quick_result_header"></header>
                    <div ku-block data-block-id="ku_quick_result_items">
                        <div class="klevuResultsBlock">
                            <%=helper.render('klevuQuickProductBlockTitleHeader',scope,data) %>
                            <div class="klevuQuickSearchResults klevuMeta productList" data-section="productList" id="productList" data-result-view="list">
                                <div class="kuQuickResultsListContainer">
                                    <ul>
                                        <% helper.each(data.query.productList.result,function(key,product){ %>
                                            <%=helper.render('klevuQuickProductBlock',scope,data,product) %>
                                        <% }); %>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="klevuProductsViewAll">
                            <% var queryParam = klevu.getSetting(klevu, "settings.url.queryParam"); %>
                            <a href="<%=helper.buildUrl(data.settings.landingUrl, queryParam ,helper.stripHtml(data.settings.term))%>"
                                target="_parent">{l s='View all results' mod='mncklevu'}</a>
                        </div>
                    </div>
                </section>
            </div>
        <% } else { %>
            <div ku-container data-container-id="ku_quick_main_content_container" data-container-role="content">
                <section ku-container data-container-id="ku_quick_main_content_center" data-container-position="center"
                    data-container-role="center">
                    <div ku-block data-block-id="ku_quick_no_result_items">
                        <%=helper.render('noResultsFoundQuick',scope) %>
                    </div>
                </section>
            </div>
        <% } %>
    <% } %>
</script>

<script type="template/klevu" id="customKlevuQuickProductBlock">
    <% 
        var updatedProductName = dataLocal.name;
        if(klevu.search.modules.kmcInputs.base.getSkuOnPageEnableValue()) {
            if(klevu.dom.helpers.cleanUpSku(dataLocal.sku)) {
                updatedProductName += klevu.dom.helpers.cleanUpSku(dataLocal.sku);
            }
        }
        var productNameAr = '';
        if (typeof dataLocal.additionalDataToReturn !== 'undefined') {
            var additionalData = JSON.parse(dataLocal.additionalDataToReturn);
            productNameAr = additionalData.mncklevu.name_ar;
        }
    %>
    <li ku-product-block class="klevuProduct" data-id="<%=dataLocal.id%>">
        <a target="_self" href="<%=dataLocal.url%>" data-id="<%=dataLocal.id%>"  class="klevuQuickProductInnerBlock trackProductClick kuTrackRecentView">
            <div class="klevuProductItemTop">
                <div class="klevuQuickImgWrap">
                    <img src="<%=dataLocal.image%>" origin="<%=dataLocal.image%>" onerror="klevu.dom.helpers.cleanUpProductImage(this)" alt="<%=updatedProductName%>" />
                </div>
            </div>
            <div class="klevuProductItemBottom">
                <div class="klevuQuickProductDescBlock">
                    <div title="<%= updatedProductName %>" class="klevuQuickProductName"> <%= updatedProductName %> </div>
                    <div title="<%= productNameAr %>" class="klevuQuickProductName klevuQuickProductNameAr"> <%= productNameAr %> </div>
                    <% if(klevu.search.modules.kmcInputs.base.getShowPrices()) { %>
                        <div class="klevuQuickProductPrice">
                            <div>
                                <%
                                    var kuTotalVariants = klevu.dom.helpers.cleanUpPriceValue(dataLocal.totalVariants);
                                    var kuStartPrice = klevu.dom.helpers.cleanUpPriceValue(dataLocal.startPrice,dataLocal.currency);
                                    var kuSalePrice = klevu.dom.helpers.cleanUpPriceValue(dataLocal.salePrice,dataLocal.currency);
                                    var kuPrice = klevu.dom.helpers.cleanUpPriceValue(dataLocal.price,dataLocal.currency);
                                %>
                                <% if(!Number.isNaN(kuTotalVariants) && !Number.isNaN(kuStartPrice)) { %>                                
                                    <div class="klevuQuickSalePrice kuStartPrice">
                                        <span class="klevuQuickPriceGreyText">{l s='Starting at' mod='mncklevu'}</span>
                                        <span><%=helper.processCurrency(dataLocal.currency,parseFloat(dataLocal.startPrice))%></span>
                                    </div>
                                <% } else if(!Number.isNaN(kuSalePrice) && !Number.isNaN(kuPrice) && (kuPrice > kuSalePrice)){ %>
                                    <span class="klevuQuickOrigPrice">
                                        <%= helper.processCurrency(dataLocal.currency,parseFloat(dataLocal.price)) %>
                                    </span>
                                    <span class="klevuQuickSalePrice klevuQuickSpecialPrice">
                                        <%=helper.processCurrency(dataLocal.currency,parseFloat(dataLocal.salePrice))%>
                                    </span>
                                <% } else if(!Number.isNaN(kuSalePrice)) { %>
                                    <span class="klevuQuickSalePrice">
                                        <%= helper.processCurrency(dataLocal.currency,parseFloat(dataLocal.salePrice)) %>
                                    </span>
                                <% } else if(!Number.isNaN(kuPrice)) { %>
                                    <span class="klevuQuickSalePrice">
                                        <%= helper.processCurrency(dataLocal.currency,parseFloat(dataLocal.price)) %>
                                    </span>
                                <% } %>
                                <%=helper.render('searchResultProductVATLabelQuick', scope, data, dataLocal) %>
                            </div>
                        </div>
                    <% } %>
                </div>
            </div>
        </a>
    </li>
</script>

<script type="template/klevu" id="customSearchResultProductVATLabelQuick">
	<%
        var caption = klevu.search.modules.kmcInputs.base.getVatCaption();
        var vatCaption = caption;
    %>
    <div class="kuCaptionVat"><%= vatCaption %></div>
</script>

<script type="template/klevu" id="customKlevuTemplateNoResultFoundQuick">
    <div class="kuQuickSearchNoRecordFound">
        <div class="kuQuickNoResults">
            <div class="kuQuickNoResultsInner">
                <div class="kuQuickNoResultsMessage">
                    <%=data.noResultsFoundMsg %>
                </div>                
            </div>
        </div>
    </div>
</script>

<div id="klevu_search" class="search_widget_block">
    <div class="search_widget">
        <form method="get" action="javascript:void(0);" class="search_widget_form">
            <div class="search_widget_form_inner input-group round_item js-parent-focus input-group-with-border">
                <input
                    type="text"
                    id="{$search_box_id}"
                    name="q"
                    class="form-control search_widget_text js-child-focus"
                    placeholder="{l s='Search by Title, Author, Keyword...' mod='mncklevu'}"
                />
                <span class="input-group-btn">
                    <button class="btn btn-search btn-less-padding btn-spin search_widget_btn link_color icon_btn" type="submit">
                        <i class="fto-search-1"></i>
                    </button>
                </span>
            </div>
        </form>
    </div>
</div>
