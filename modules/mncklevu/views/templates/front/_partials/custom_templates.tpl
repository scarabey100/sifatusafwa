{**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
<script type="template/klevu" id="customKlevuLandingTemplateTabResults">
    <% var isCmsEnabled = klevu.search.modules.kmcInputs.base.getCmsEnabledValue(); %>        
    <div class="kuTabs" role="tablist" style="display:<%= isCmsEnabled ? 'block' : 'none'  %>">
        <% var selectedTab = false; %>
        <% helper.each(data.query,function(key,query){ %>
            <% if(query.tab == true) { %>
                <% if(helper.hasResults(data,query.id) || helper.hasFilters(data,query.id)) { %>
                    <a target="_self" class="kuTab<% if(!selectedTab){ selectedTab = true; %> kuTabSelected<% } %>" data-section="<%=query.id%>" role="tab" tabindex="0" aria-selected="" area-label="Products tab">
                        <%=helper.translate(query.tabText,data.query[query.id].meta.totalResultsFound)%>
                    </a>
                <% } else { %>
                    <a target="_self" class="kuTabDeactive" data-section="<%=query.id%>" role="tab" tabindex="0" aria-selected="" area-label="Products tab">
                        <%=helper.translate(query.tabText,0)%>
                    </a>
                <% } %>
            <% } %>
        <% }); %>
    </div>
</script>
<script type="template/klevu" id="customKlevuLandingTemplateResultsFilter">

</script>
<script type="template/klevu" id="customKlevuLandingTemplateResults">
 

    <div class="kuResultsListing"> 
        <div class="productList klevuMeta" data-section="productList">
            <div class="kuResultContent">
                <div class="kuResultWrap <%=(data.query.productList.filters.length == 0 )?'kuBlockFullwidth':''%>">

                    <div ku-container data-container-id="ku_landing_main_content_container" data-container-role="content">
                        <section id="search_filters_wrapper" class="col-xs-12 col-md-4 col-lg-3" ku-container data-container-id="ku_landing_main_content_left" data-container-position="left" data-container-role="left">
                            <div id="search_filters" ku-block data-block-id="ku_landing_left_facets">                                
                                <%=helper.render('filters',scope,data,"productList") %>
                            </div>
                            <div ku-block data-block-id="ku_landing_left_call_outs"></div>
                            <div ku-block data-block-id="ku_landing_left_banner"></div>
                         </section>
                        <section class="js-content-wrapper left-column col-xs-12 col-md-8 col-lg-9" ku-container data-container-id="ku_landing_main_content_center" data-container-position="center" data-container-role="center">
                            
                            <header ku-block data-block-id="ku_landing_result_header">
                                <%=helper.render('klevuLandingTemplateResultsHeadingTitle',scope,data,"productList") %>
                                <%=helper.render('filtersTop',scope,data,"productList") %>
                                <%= helper.render('kuFilterTagsTemplate',scope,data,"productList") %>
                                <% if(helper.hasResults(data,"productList")) { %>
                                    <div class="kuMobileButtonsContainer">
                                        <div class="kuMobileButton kuProductSortingOpenButton">
                                            <span>
                                                {l s='Sort' mod='mncklevu'}
                                                <svg xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32">
                                                    <path d="M30.4 9.536l-1.344-1.344-13.056 13.056-13.056-13.056-1.344 1.344 14.4 14.4z"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <!-- <div class="kuMobileButtonsDivider"></div> -->
                                        <div class="kuMobileButton kuProductFiltersOpenButton">
                                            <span>
                                                {l s='Filters' mod='mncklevu'}
                                                <svg xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500">
                                                    <rect x="171.39" y="92.27" width="278.06" height="33"/>
                                                    <rect x="390.71" y="239.07" width="58.74" height="33"/>
                                                    <rect x="64.95" y="239.07" width="219.17" height="33"/>
                                                    <path d="M117.85,177.57a69.8,69.8,0,1,1,69.8-69.8A69.88,69.88,0,0,1,117.85,177.57Zm0-106.6a36.8,36.8,0,1,0,36.8,36.8A36.84,36.84,0,0,0,117.85,71Z"/>
                                                    <path d="M333.17,325.8A69.8,69.8,0,1,1,403,256,69.88,69.88,0,0,1,333.17,325.8Zm0-106.6A36.8,36.8,0,1,0,370,256,36.84,36.84,0,0,0,333.17,219.2Z"/>
                                                    <path d="M201.09,472.17a69.8,69.8,0,1,1,69.8-69.8A69.88,69.88,0,0,1,201.09,472.17Zm0-106.6a36.8,36.8,0,1,0,36.8,36.8A36.84,36.84,0,0,0,201.09,365.57Z"/>
                                                    <rect x="253.5" y="385.87" width="195.55" height="33"/>
                                                    <rect x="64.55" y="385.87" width="83.24" height="33"/>
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                    <%=helper.render('sortBy',scope,data,"productList") %>
                                <% } %>
                                <div class="kuClearBoth"></div>
                            </header>
			   
                            <div ku-block data-block-id="ku_landing_result_items">
                                <div class="kuResults">
                                    <% if(helper.hasResults(data,"productList")) { %>
                                        <% var productIds = []; %>

                                        <% helper.each(data.query.productList.result, function(key, item) { %>
                                            <% if (item.typeOfRecord == "KLEVU_PRODUCT") { %>
                                                <% productIds.push({ 
                                                    id_product: item.klevu_price_placeholder, 
                                                    id_product_attribute: item.id 
                                                }); %>
                                            <% } %>
                                        <% }); %>
                                         <div class="kuResultsProducts js-content-wrapper left-column col-xs-12 col-md-8 col-lg-9"></div>
                                        
                                        <%  function getProducts() {
                                            $.ajax({
                                                url: ajaxUrlFront,
                                                type: 'POST',
                                                data: {
                                                    token: token,
                                                    products: productIds,
                                                    ajax: 1,
                                                    action: 'getResult',
                                                },
                                                cache: false,
                                                dataType: 'json',
                                                success: function(response) {
                                                    $(".kuResultsProducts").append(response.products);
                                                }
                                            });
                                        } %>
                                        <%  getProducts()   %>
			
                                    <% } else { %>
                                        <div class="kuNoRecordsFoundLabelTextContainer">
                                            <span class="kuNoRecordsFoundLabelText">{l s='No records found for your selection' mod='mncklevu'}</span>
                                        </div>
                                    <% } %>
                                    <div class="kuClearBoth"></div>
                                </div>
                            </div>
                            
                            <div ku-block data-block-id="ku_landing_other_items">
                                <%=helper.render('klevuLandingTemplateInfiniteScrollDown',scope,data) %>                               
                            </div>
                            <footer ku-block data-block-id="ku_landing_result_footer">
                            <%=helper.render('pagination',scope,data,"productList") %>
                            <%=helper.render('kuTemplateLandingResultsViewSwitch',scope,data,"productList") %>
                            </footer>
                            
                        </section>
                        <section ku-container data-container-id="ku_landing_main_content_right" data-container-position="right" data-container-role="right">
                            <div ku-block data-block-id="ku_landing_right_facets"></div>
                            <div ku-block data-block-id="ku_landing_right_call_outs"></div>
                            <div ku-block data-block-id="ku_landing_right_banner"></div>
                        </section>
                        <section class="kuMobileSortingContainer">
                            <% if(helper.hasResults(data,"productList")) { %>
                                <div class="kuMobileSorting" role="navigation" data-position="left" aria-label="Product Sorting" tabindex="0">
                                    <h3 class="kuMobileSortingTitleHeading">{l s='Sort by' mod='mncklevu'}</h3>
                                    <div class="kuProductSortingCloseButton">
                                        <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg"><path d="M16.68 9.18v1.64H6.52l4.66 4.68L10 16.68 3.32 10 10 3.32l1.18 1.18-4.66 4.68z" fill="currentColor" fill-rule="evenodd"></path></svg>
                                    </div>
                                    <% if ((typeof klevu_pageCategory !== 'undefined') && klevu_pageCategory && ((typeof klevu_pageManufacturer === 'undefined') || !klevu_pageManufacturer)) { %>
                                        <div class="kuMobileSort<% if (helper.getSortByCustom("productList") === mncklevu.sortByOptions.RELEVANCE) { %> kuMobileSortActive<% } %>" data-value="RELEVANCE">
                                            <span class="kuMobileSortIcon"></span>
                                            <span class="kuMobileSortText"><%=mncklevu.sortByOptions.RELEVANCE%></span>
                                        </div>
                                        <div class="kuMobileSort<% if (helper.getSortByCustom("productList") === mncklevu.sortByOptions.PRICE_ASC) { %> kuMobileSortActive<% } %>" data-value="PRICE_ASC">
                                            <span class="kuMobileSortIcon"></span>
                                            <span class="kuMobileSortText"><%=mncklevu.sortByOptions.PRICE_ASC%></span>
                                        </div>
                                        <div class="kuMobileSort<% if (helper.getSortByCustom("productList") === mncklevu.sortByOptions.PRICE_DESC) { %> kuMobileSortActive<% } %>" data-value="PRICE_DESC">
                                            <span class="kuMobileSortIcon"></span>
                                            <span class="kuMobileSortText"><%=mncklevu.sortByOptions.PRICE_DESC%></span>
                                        </div>
                                        <div class="kuMobileSort<% if (helper.getSortByCustom("productList") === mncklevu.sortByOptions.NAME_ASC) { %> kuMobileSortActive<% } %>" data-value="NAME_ASC">
                                            <span class="kuMobileSortIcon"></span>
                                            <span class="kuMobileSortText"><%=mncklevu.sortByOptions.NAME_ASC%></span>
                                        </div>
                                        <div class="kuMobileSort<% if (helper.getSortByCustom("productList") === mncklevu.sortByOptions.NAME_DESC) { %> kuMobileSortActive<% } %>" data-value="NAME_DESC">
                                            <span class="kuMobileSortIcon"></span>
                                            <span class="kuMobileSortText"><%=mncklevu.sortByOptions.NAME_DESC%></span>
                                        </div>
                                        <div class="kuMobileSort<% if (helper.getSortByCustom("productList") === mncklevu.sortByOptions.NEW_ARRIVAL_DESC) { %> kuMobileSortActive<% } %>" data-value="NEW_ARRIVAL_DESC">
                                            <span class="kuMobileSortIcon"></span>
                                            <span class="kuMobileSortText"><%=mncklevu.sortByOptions.NEW_ARRIVAL_DESC%></span>
                                        </div>
                                    <% } else { %>
                                        <div class="kuMobileSort<% if (helper.getSortBy("productList") === mncklevu.sortByOptions.RELEVANCE) { %> kuMobileSortActive<% } %>" data-value="RELEVANCE">
                                            <span class="kuMobileSortIcon"></span>
                                            <span class="kuMobileSortText"><%=mncklevu.sortByOptions.RELEVANCE%></span>
                                        </div>
                                        <div class="kuMobileSort<% if (helper.getSortBy("productList") === mncklevu.sortByOptions.PRICE_ASC) { %> kuMobileSortActive<% } %>" data-value="PRICE_ASC">
                                            <span class="kuMobileSortIcon"></span>
                                            <span class="kuMobileSortText"><%=mncklevu.sortByOptions.PRICE_ASC%></span>
                                        </div>
                                        <div class="kuMobileSort<% if (helper.getSortBy("productList") === mncklevu.sortByOptions.PRICE_DESC) { %> kuMobileSortActive<% } %>" data-value="PRICE_DESC">
                                            <span class="kuMobileSortIcon"></span>
                                            <span class="kuMobileSortText"><%=mncklevu.sortByOptions.PRICE_DESC%></span>
                                        </div>
                                        <div class="kuMobileSort<% if (helper.getSortBy("productList") === mncklevu.sortByOptions.NAME_ASC) { %> kuMobileSortActive<% } %>" data-value="NAME_ASC">
                                            <span class="kuMobileSortIcon"></span>
                                            <span class="kuMobileSortText"><%=mncklevu.sortByOptions.NAME_ASC%></span>
                                        </div>
                                        <div class="kuMobileSort<% if (helper.getSortBy("productList") === mncklevu.sortByOptions.NAME_DESC) { %> kuMobileSortActive<% } %>" data-value="NAME_DESC">
                                            <span class="kuMobileSortIcon"></span>
                                            <span class="kuMobileSortText"><%=mncklevu.sortByOptions.NAME_DESC%></span>
                                        </div>
                                        <div class="kuMobileSort<% if (helper.getSortBy("productList") === mncklevu.sortByOptions.NEW_ARRIVAL_DESC) { %> kuMobileSortActive<% } %>" data-value="NEW_ARRIVAL_DESC">
                                            <span class="kuMobileSortIcon"></span>
                                            <span class="kuMobileSortText"><%=mncklevu.sortByOptions.NEW_ARRIVAL_DESC%></span>
                                        </div>
                                    <% } %>
                                </div>
                            <% } %>
                        </section>
                        
                        <div class="kuProductListMobilePopUpBackdrop"></div>
                    </div>
                    {hook h='displayRkrSelledPlus1'} 

        <% if(data.query.contentList) { %> 
                        <div ku-container data-container-id="ku_landing_main_content_container" data-container-role="content">
                            <% if(data.query.contentList) { %>
                                <section id="search_filters_wrapper" class="col-xs-12 col-md-4 col-lg-3" ku-container data-container-id="ku_landing_main_content_left" data-container-position="left" data-container-role="left">
                                    <div id="search_filters" ku-block data-block-id="ku_landing_left_facets">
                                        
                                        <%=helper.render('filters',scope,data,"contentList") %>
                            
                                    </div>
                                    <div ku-block data-block-id="ku_landing_left_call_outs"></div>
                                    <div ku-block data-block-id="ku_landing_left_banner"></div>
                                </section>
                              <% } %> 
                            <section class="js-content-wrapper left-column col-xs-12 col-md-8 col-lg-9" ku-container data-container-id="ku_landing_main_content_center" data-container-position="center" data-container-role="center">
                                
                                <header ku-block data-block-id="ku_landing_result_header">
                                    <%=helper.render('filtersTop',scope,data,"contentList") %>
                                    <%= helper.render('kuFilterTagsTemplate',scope,data,"contentList") %>
                                    <div class="kuClearBoth"></div> 
                                </header>

                                <div ku-block data-block-id="ku_landing_result_items">
                                    <div class="kuClearBoth">aaaa</div>
                                    <div class="kuResults">
                                        <% if(helper.hasResults(data,"contentList")) { %>
                                            <ul>
                                                <% helper.each(data.query.contentList.result,function(key,item){ %>
                                                    <% if(item.typeOfRecord == "KLEVU_CMS") { %>
                                                        <%=helper.render('contentBlock',scope,data,item) %>
                                                    <% }%>
                                                <% }); %>
                                            </ul>
                                        <% } else { %>
                                            <div class="kuNoRecordsFoundLabelTextContainer">
                                                <span class="kuNoRecordsFoundLabelText">{l s='No records found for your selection' mod='mncklevu'}</span>
                                            </div>
                                        <% } %>
                                        <div class="kuClearBoth"></div>
                                    </div>
                                </div>
                                <div ku-block data-block-id="ku_landing_other_items">
                                    <%=helper.render('klevuLandingTemplateInfiniteScrollDown',scope,data) %>
                                </div>
                                <footer ku-block data-block-id="ku_landing_result_footer">
                                <%=helper.render('pagination',scope,data,"contentList") %>
                                </footer>
                                {hook h='displayRkrSelledPlus1'}
                            </section>
                            <section ku-container data-container-id="ku_landing_main_content_right" data-container-position="right" data-container-role="right">
                                <div ku-block data-block-id="ku_landing_right_facets"></div>
                                <div ku-block data-block-id="ku_landing_right_call_outs"></div>
                                <div ku-block data-block-id="ku_landing_right_banner"></div>
                            </section>
                        </div>
        
                    </div>
                </div>
            </div>
        <% } %>  

    </div>
</script>

<script type="template/klevu" id="customKlevuLandingTemplateResultsHeadingTitle">
    <%
        var totalNumberOfResults = klevu.getObjectPath(data,"query."+dataLocal+".meta.totalResultsFound");
        var searchedTerm = helper.escapeHTML(data.settings.term);
        var isCATNAV = data.settings.isCATNAV;
        if(isCATNAV){
            searchedTerm = helper.escapeHTML(data.settings.categoryPath);
        }
        var stringTemplate = '';
        if (parseInt(totalNumberOfResults) === 1) {
            if (searchedTerm && searchedTerm.length && searchedTerm != "*") {
                stringTemplate = "{l s='[1]%number%[/1] Result found for \'[2]%term%[/2]\'' tags=['<strong class=\"kuResultsNumber\">', '<span>'] mod='mncklevu'}";
            } else {
                stringTemplate = "{l s='[1]%number%[/1] Result found' tags=['<strong class=\"kuResultsNumber\">'] mod='mncklevu'}";
            }
        } else {
            if (searchedTerm && searchedTerm.length && searchedTerm != "*") {
                stringTemplate = "{l s='[1]%number%[/1] Results found for \'[2]%term%[/2]\'' tags=['<strong class=\"kuResultsNumber\">', '<span>'] mod='mncklevu'}";
            } else {
                stringTemplate = "{l s='[1]%number%[/1] Results found' tags=['<strong class=\"kuResultsNumber\">'] mod='mncklevu'}";
            }
        }
    %>
    <div class="kuResultsHeadingTitleContainer">
         <%=stringTemplate.replace('%number%', totalNumberOfResults ? totalNumberOfResults : 0).replace('%term%', klevu.dom.helpers.cleanCatogeryPath(searchedTerm)) %>
    </div>
</script>

<script type="template/klevu" id="customKuFilterTagsTemplate">
    <% 
        var filterTagsModule = data.filterTags;
        var filterTagsData = [];
        if(dataLocal && dataLocal.length){
            filterTagsData = filterTagsModule.query[dataLocal].tags;
        }
    %>
    <% if(filterTagsData.length) { %>
        <div class="kuFilterTagsContainer">        
            <% helper.each(filterTagsData, function(key,item){ 
                var filterTagItemKey = helper.escapeHTML(item.key);
                var filterTagItemLabel = helper.escapeHTML(helper.translate(item.label));
                %>
                <span 
                    class="kuFilterTag" 
                    data-key="<%= filterTagItemKey %>"
                    data-type="<%= helper.escapeHTML(item.type) %>"
                >
                    <span 
                        title="<%= filterTagItemLabel %>" 
                        data-value="<%= filterTagItemLabel %>" 
                        class="kuFilterTagKey"
                    >
                        <%= helper.translate(item.label) %>
                    </span>
                    <% helper.each(item.values, function(key,option){ %>
                        <% if(item.type === "RATING") { %>
                            <span 
                                title="<%= helper.escapeHTML(helper.translate(option)) %>" 
                                data-value="<%= helper.escapeHTML(helper.translate(option)) %>"
                                class="kuFilterTagValue">
                                <div class="klevuFacetStars">
                                    <div class="klevuFacetRating" style="width:<%=(20*Number(option))%>%;"></div>
                                </div>
                                <span>&times;</span>
                            </span>
                        <% } else { %>
                            <span 
                                title="<%= helper.escapeHTML(helper.translate(option)) %>" 
                                data-value="<%= helper.escapeHTML(helper.translate(option)) %>"
                                class="kuFilterTagValue">
                                <%= helper.translate(option) %>
                                <span>&times;</span>
                            </span>
                        <% } %>
                    <% }); %>
                </span>
            <% }); %>
            <span title="{l s='Clear all' mod='mncklevu'}" class="kuFilterTagClearAll">{l s='Clear all' mod='mncklevu'}</span>
        </div>
    <% } %>
</script>

<script type="template/klevu" id="customKlevuLandingTemplateFilters">
    <% if(data.query[dataLocal].filters.length > 0 ) { %>
        <div class="filter-button__close">
            <button class="btn">
                <i class="material-icons">close</i>
            </button>
        </div>
        <div class="kuFilters" role="navigation" data-position="left" aria-label="Product Filters" tabindex="0">
 
            <div id="search_filters_top">
                <div id="search_filters_title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="25.496" height="22.664" viewBox="140 694.668 25.496 22.664">
                        <path d="M148.367 694.668a68.145 68.145 0 0 0 .264 0c.522 0 1.035-.001 1.492.121a3.541 3.541 0 0 1 2.504 2.504c.123.457.122.97.121 1.493v.132h11.332a1.416 1.416 0 1 1 0 2.833h-11.332v.132c.001.522.002 1.035-.12 1.493a3.54 3.54 0 0 1-2.505 2.503c-.457.123-.97.122-1.492.121h-.264c-.522.001-1.036.002-1.493-.12a3.54 3.54 0 0 1-2.504-2.504c-.122-.458-.122-.971-.12-1.493v-.132h-2.834a1.416 1.416 0 0 1 0-2.833h2.833v-.132c0-.522-.001-1.036.121-1.493a3.541 3.541 0 0 1 2.504-2.504c.457-.122.97-.122 1.493-.12Zm-.578 2.841c-.152.007-.187.018-.182.017a.708.708 0 0 0-.5.5c-.002.005-.011.049-.017.182-.007.158-.008.368-.008.71v2.833c0 .342 0 .551.008.71.007.151.018.187.016.181a.708.708 0 0 0 .501.501c-.005-.001.03.01.182.017.158.007.368.007.71.007.342 0 .551 0 .71-.007.151-.007.187-.018.181-.017a.708.708 0 0 0 .501-.5c-.001.005.01-.03.017-.182.007-.159.007-.368.007-.71v-2.833c0-.342 0-.552-.007-.71-.007-.151-.018-.187-.017-.182m-2.102-.517c.158-.007.368-.008.71-.008l-.71.008Zm.71-.008c.342 0 .551 0 .71.008l-.71-.008Zm.71.008c.133.006.176.015.181.017l-.181-.017Zm.182.017Zm7.475 8.474h.263c.523 0 1.036-.002 1.493.12a3.541 3.541 0 0 1 2.504 2.505c.123.457.122.97.121 1.493v.132h2.833a1.416 1.416 0 1 1 0 2.832h-2.833v.132c0 .523.002 1.036-.12 1.493a3.54 3.54 0 0 1-2.505 2.504c-.457.123-.97.122-1.493.121h-.263c-.523 0-1.036.002-1.493-.12a3.54 3.54 0 0 1-2.504-2.505c-.123-.457-.122-.97-.121-1.493v-.132h-11.332a1.416 1.416 0 1 1 0-2.832h11.332v-.132c0-.523-.002-1.036.12-1.493a3.541 3.541 0 0 1 2.505-2.504c.457-.123.97-.122 1.493-.121Zm-.579 2.84c-.15.008-.186.019-.181.017a.709.709 0 0 0-.5.501c0-.005-.01.03-.017.181-.008.159-.008.368-.008.71v2.833c0 .343 0 .552.008.71.007.152.018.187.016.182a.708.708 0 0 0 .501.5c-.005 0 .03.01.181.017.159.008.368.008.71.008.343 0 .552 0 .71-.008.152-.007.187-.018.182-.016a.708.708 0 0 0 .5-.501c0 .005.01-.03.017-.181.008-.159.008-.368.008-.71v-2.833c0-.343 0-.552-.008-.71-.006-.152-.018-.187-.016-.182a.709.709 0 0 0-.5-.5c.004 0-.031-.01-.182-.017a17.328 17.328 0 0 0-.71-.008c-.343 0-.552 0-.71.008Z" fill="#0f1729" fill-rule="evenodd" data-name="filter-edit-svgrepo-com"></path>
                    </svg>
                    {l s='Narrow by' mod='mncklevu'}
                </div>
            </div>
            <%
                var klevuFilters_priceSliderData = false;

                helper.each(data.query[dataLocal].filters, function(key, filter) {
                    if ((filter.type == 'SLIDER') && (filter.key === 'klevu_price')) {
                        klevuFilters_priceSliderData = filter;
                    }
                });
            %>

            <% helper.each(data.query[dataLocal].filters,function(key,filter){ %>
                <% if(filter.type == "OPTIONS"){ %>
                    <% if (filter.key === 'klevu_price_placeholder') { %>
                        <% if (klevuFilters_priceSliderData) { %>
                            <div class="kuFilterBox klevuFilter" data-filter="<%=klevuFilters_priceSliderData.key%>">
                                <div class="facet-title kuFilterHead <%=(klevuFilters_priceSliderData.isCollapsed) ? 'kuExpand' : 'kuCollapse'%>">
                                    <%=helper.translate("price")%>
                                </div>
                                <div class="kuFilterNames sliderFilterNames <%=(klevuFilters_priceSliderData.isCollapsed) ? 'kuFilterCollapse' : ''%>">                           
                                    <div class="kuPriceSlider klevuSliderFilter" data-query = "<%=dataLocal%>">
                                        <div data-querykey = "<%=dataLocal%>" class="noUi-target noUi-ltr noUi-horizontal noUi-background kuSliderFilter kuPriceRangeSliderFilter<%=dataLocal%>"></div>
                                    </div>
                                </div>
                            </div>
                        <% } %>
                    <% } else if (filter.key === 'categoryPage_category') { %>
                        <% if ((typeof klevu_pageCategory !== 'undefined') && klevu_pageCategory) { %>
                            <div class="facet kuFilterBox klevuFilter <%=(filter.multiselect)?'kuMulticheck':''%>" data-filter="<%=filter.key%>" <% if(filter.multiselect){ %> data-singleselect="false" <% } else { %> data-singleselect="true"<% } %>>
                                <div class="facet-title kuFilterHead <%=(filter.isCollapsed) ? 'kuExpand' : 'kuCollapse'%>">
                                    <% var filter_label = (filter.label=="klevu_price") ? "price" : filter.label; %>
                                    <%=helper.translate(filter_label)%>
                                </div>
                                <div data-optionCount="<%= filter.options.length %>" class="kuFilterNames <%= (filter.options.length <= 5 ) ? 'kuFilterShowAll': '' %> <%=(filter.isCollapsed) ? 'kuFilterCollapse' : ''%>">
                                    <% if (filter.options.length > 5) { %>
                                        <input type="text" class="kuFilterSearchBox" value="" placeholder="{l s='Search by name' mod='mncklevu'}">
                                    <% } %>
                                    <ul>
                                        <% helper.each(filter.options,function(key,filterOption){ %>
                                            <li <% if(filterOption.selected ==true) { %> class="kuSelected"<% } %>>
                                                <a
                                                    target="_self" 
                                                    href="#" 
                                                    title="<%=helper.escapeHTML(filterOption.name)%>" 
                                                    class="klevuFilterOption<% if(filterOption.selected ==true) { %> klevuFilterOptionActive<% } %>" 
                                                    data-value="<%=helper.escapeHTML(filterOption.value)%>"
                                                    data-name="<%=helper.escapeHTML(filterOption.name)%>" 
                                                >
                                                    <span class="kuFilterIcon"></span>
                                                    <span class="kufacet-text"><%=filterOption.name%></span>
                                                    <% if(filterOption.selected ==true) { %>
                                                        <span class="kuFilterCancel">X</span>
                                                    <% } else { %>
                                                        <span class="kuFilterTotal"><%=filterOption.count%></span>
                                                    <% } %>
                                                </a>
                                            </li>
                                            
                                        <%  }); %>
                                    </ul>
                                    <% if(filter.options.length > 5 ) { %>
                                        <div class="kuShowOpt" tabindex="-1">
                                            <span class="kuFilterDot"></span><span class="kuFilterDot"></span><span class="kuFilterDot"></span>
                                        </div>
                                    <% } %>
                                </div>
                            </div>
                        <% } %>
                    <% } else { %>
                        <div class=" facet kuFilterBox klevuFilter <%=(filter.multiselect)?'kuMulticheck':''%>" data-filter="<%=filter.key%>" <% if(filter.multiselect){ %> data-singleselect="false" <% } else { %> data-singleselect="true"<% } %> <% if ((typeof klevu_pageManufacturer !== 'undefined') && klevu_pageManufacturer && (filter.key === 'manufacturer')) { %> style="display: none;"<% } %>>
                            <div class="kuFilterHead <%=(filter.isCollapsed) ? 'kuExpand' : 'kuCollapse'%>">
                                <% var filter_label = (filter.label=="klevu_price") ? "price" : filter.label; %>
                                <%=helper.translate(filter_label)%>
                            </div>
                            <div data-optionCount="<%= filter.options.length %>" class="kuFilterNames <%= (filter.options.length <= 5 ) ? 'kuFilterShowAll': '' %> <%=(filter.isCollapsed) ? 'kuFilterCollapse' : ''%>">
                                <% if (filter.options.length > 5) { %>
                                    <input type="text" class="kuFilterSearchBox" value="" placeholder="{l s='Search by name' mod='mncklevu'}">
                                <% } %>
                                <ul>
                                    <% helper.each(filter.options,function(key,filterOption){ %>
                                        <li <% if(filterOption.selected ==true) { %> class="kuSelected"<% } %>>
                                            <a
                                                target="_self" 
                                                href="#" 
                                                title="<%=helper.escapeHTML(filterOption.name)%>" 
                                                class="klevuFilterOption<% if(filterOption.selected ==true) { %> klevuFilterOptionActive<% } %>" 
                                                data-value="<%=helper.escapeHTML(filterOption.value)%>"
                                                data-name="<%=helper.escapeHTML(filterOption.name)%>" 
                                            >
                                                <span class="kuFilterIcon"></span>
                                                <span class="kufacet-text"><%=filterOption.name%></span>
                                                <% if(filterOption.selected ==true) { %>
                                                    <span class="kuFilterCancel">X</span>
                                                <% } else { %>
                                                    <span class="kuFilterTotal"><%=filterOption.count%></span>
                                                <% } %>
                                            </a>
                                        </li>
                                        
                                    <%  }); %>
                                </ul>
                                <% if(filter.options.length > 5 ) { %>
                                    <div class="kuShowOpt" tabindex="-1">
                                        <span class="kuFilterDot"></span><span class="kuFilterDot"></span><span class="kuFilterDot"></span>
                                    </div>
                                <% } %>
                            </div>
                        </div>
                    <% } %>
                <% } else if(filter.type == "SLIDER")  { %>
                    <% if (filter.key !== 'klevu_price')  { %>
                        <div class="kuFilterBox klevuFilter" data-filter="<%=filter.key%>">
                            <div class="kuFilterHead <%=(filter.isCollapsed) ? 'kuExpand' : 'kuCollapse'%>">
                                <% var filter_label = (filter.label=="klevu_price") ? "price" : filter.label; %>
                                <%=helper.translate(filter_label)%>
                            </div>
                            <div class="kuFilterNames sliderFilterNames <%=(filter.isCollapsed) ? 'kuFilterCollapse' : ''%>">                           
                                <div class="kuPriceSlider klevuSliderFilter" data-query = "<%=dataLocal%>">
                                    <div data-querykey = "<%=dataLocal%>" class="noUi-target noUi-ltr noUi-horizontal noUi-background kuSliderFilter kuPriceRangeSliderFilter<%=dataLocal%>"></div>
                                </div>
                            </div>
                        </div>
                    <% } %>
                <% } else if (filter.type == "RATING")  { %>
                    <div class="facet kuFilterBox klevuFilter <%=(filter.multiselect)?'kuMulticheck':''%>" data-filter="<%=filter.key%>" <% if(filter.multiselect){ %> data-singleselect="false" <% } else { %> data-singleselect="true"<% } %>>
                        <div class="kuFilterHead <%=(filter.isCollapsed) ? 'kuExpand' : 'kuCollapse'%>">
                            <%=helper.translate(filter.label)%>
                        </div>
                        <div data-optionCount="<%= filter.options.length %>" class="kuFilterNames <%= (filter.options.length <= 5 ) ? 'kuFilterShowAll': '' %> <%=(filter.isCollapsed) ? 'kuFilterCollapse' : ''%>">
                            <ul>
                                <% helper.each(filter.options,function(key,filterOption){ %>
                                    <li <% if(filterOption.selected ==true) { %> class="kuSelected"<% } %>>
                                        <a
                                            target="_self" 
                                            href="#" 
                                            title="<%=helper.escapeHTML(filterOption.name)%>" 
                                            class="klevuFilterOption<% if(filterOption.selected ==true) { %> klevuFilterOptionActive<% } %>" 
                                            data-value="<%=helper.escapeHTML(filterOption.value)%>"
                                            data-name="<%=helper.escapeHTML(filterOption.name)%>"
                                        >
                                            <span class="kuFilterIcon"></span>
                                            <span class="kufacet-text">
                                                <div class="klevuFacetStars">
                                                    <div class="klevuFacetRating" style="width:<%=(20*Number(filterOption.name))%>%;"></div>
                                                </div>
                                            </span>
                                            <% if(filterOption.selected ==true) { %>
                                                <span class="kuFilterCancel">X</span>
                                            <% } else { %>
                                                <span class="kuFilterTotal"><%=filterOption.count%></span>
                                            <% } %>
                                        </a>
                                    </li>
                                    
                                <%  }); %>
                            </ul>
                        </div>
                    </div>
                <% } else { %>
                    <!-- Other Facets -->
                <% } %>
            <% }); %>
        </div>
    <% } %>
</script>

<script type="template/klevu" id="customKlevuLandingTemplateSortBy">
    <div class="sort-by-row">
        <div class="kuDropdown kuDropSortBy" role="listbox">
            <div class="kuDropdownLabel">{l s='Sort by' mod='mncklevu'}: <% if ((typeof klevu_pageCategory !== 'undefined') && klevu_pageCategory && ((typeof klevu_pageManufacturer === 'undefined') || !klevu_pageManufacturer)) { %><%=helper.getSortByCustom(dataLocal)%><% } else { %><%=helper.getSortBy(dataLocal)%><% } %></div>
            <div class="kuDropdownOptions">
                <div class="kuDropOption kuSort" data-value="RELEVANCE" role="option"><%=mncklevu.sortByOptions.RELEVANCE%></div>
                <div class="kuDropOption kuSort" data-value="PRICE_ASC" role="option"><%=mncklevu.sortByOptions.PRICE_ASC%></div>
                <div class="kuDropOption kuSort" data-value="PRICE_DESC" role="option"><%=mncklevu.sortByOptions.PRICE_DESC%></div>
                <div class="kuDropOption kuSort" data-value="NAME_ASC" role="option"><%=mncklevu.sortByOptions.NAME_ASC%></div>
                <div class="kuDropOption kuSort" data-value="NAME_DESC" role="option"><%=mncklevu.sortByOptions.NAME_DESC%></div>
                <div class="kuDropOption kuSort" data-value="NEW_ARRIVAL_DESC" role="option"><%=mncklevu.sortByOptions.NEW_ARRIVAL_DESC%></div>
            </div>
        </div>
        <div class="filter-button__open">
            <button class="btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="25.496" height="22.664" viewBox="140 694.668 25.496 22.664">
                    <path d="M148.367 694.668a68.145 68.145 0 0 0 .264 0c.522 0 1.035-.001 1.492.121a3.541 3.541 0 0 1 2.504 2.504c.123.457.122.97.121 1.493v.132h11.332a1.416 1.416 0 1 1 0 2.833h-11.332v.132c.001.522.002 1.035-.12 1.493a3.54 3.54 0 0 1-2.505 2.503c-.457.123-.97.122-1.492.121h-.264c-.522.001-1.036.002-1.493-.12a3.54 3.54 0 0 1-2.504-2.504c-.122-.458-.122-.971-.12-1.493v-.132h-2.834a1.416 1.416 0 0 1 0-2.833h2.833v-.132c0-.522-.001-1.036.121-1.493a3.541 3.541 0 0 1 2.504-2.504c.457-.122.97-.122 1.493-.12Zm-.578 2.841c-.152.007-.187.018-.182.017a.708.708 0 0 0-.5.5c-.002.005-.011.049-.017.182-.007.158-.008.368-.008.71v2.833c0 .342 0 .551.008.71.007.151.018.187.016.181a.708.708 0 0 0 .501.501c-.005-.001.03.01.182.017.158.007.368.007.71.007.342 0 .551 0 .71-.007.151-.007.187-.018.181-.017a.708.708 0 0 0 .501-.5c-.001.005.01-.03.017-.182.007-.159.007-.368.007-.71v-2.833c0-.342 0-.552-.007-.71-.007-.151-.018-.187-.017-.182m-2.102-.517c.158-.007.368-.008.71-.008l-.71.008Zm.71-.008c.342 0 .551 0 .71.008l-.71-.008Zm.71.008c.133.006.176.015.181.017l-.181-.017Zm.182.017Zm7.475 8.474h.263c.523 0 1.036-.002 1.493.12a3.541 3.541 0 0 1 2.504 2.505c.123.457.122.97.121 1.493v.132h2.833a1.416 1.416 0 1 1 0 2.832h-2.833v.132c0 .523.002 1.036-.12 1.493a3.54 3.54 0 0 1-2.505 2.504c-.457.123-.97.122-1.493.121h-.263c-.523 0-1.036.002-1.493-.12a3.54 3.54 0 0 1-2.504-2.505c-.123-.457-.122-.97-.121-1.493v-.132h-11.332a1.416 1.416 0 1 1 0-2.832h11.332v-.132c0-.523-.002-1.036.12-1.493a3.541 3.541 0 0 1 2.505-2.504c.457-.123.97-.122 1.493-.121Zm-.579 2.84c-.15.008-.186.019-.181.017a.709.709 0 0 0-.5.501c0-.005-.01.03-.017.181-.008.159-.008.368-.008.71v2.833c0 .343 0 .552.008.71.007.152.018.187.016.182a.708.708 0 0 0 .501.5c-.005 0 .03.01.181.017.159.008.368.008.71.008.343 0 .552 0 .71-.008.152-.007.187-.018.182-.016a.708.708 0 0 0 .5-.501c0 .005.01-.03.017-.181.008-.159.008-.368.008-.71v-2.833c0-.343 0-.552-.008-.71-.006-.152-.018-.187-.016-.182a.709.709 0 0 0-.5-.5c.004 0-.031-.01-.182-.017a17.328 17.328 0 0 0-.71-.008c-.343 0-.552 0-.71.008Z" fill="#0f1729" fill-rule="evenodd" data-name="filter-edit-svgrepo-com"></path>
                </svg>
                {l s='Filter' d='Shop.Theme.Actions'}
            </button>
        </div>
    </div>
</script>

<script type="template/klevu" id="customKuTemplateLandingResultsViewSwitch">
    <div class="kuLandingResultsViewSwitchContainer"></div>
</script>

<script type="template/klevu" id="customKlevuLandingTemplateProductBlock">
    <% 
        var updatedProductName = dataLocal.name;
        if(klevu.search.modules.kmcInputs.base.getSkuOnPageEnableValue()) {
            if(klevu.dom.helpers.cleanUpSku(dataLocal.sku)) {
                updatedProductName += klevu.dom.helpers.cleanUpSku(dataLocal.sku);
            }
        }
        var productNameAr = '';
        var productSticker = '';
        if (typeof dataLocal.additionalDataToReturn !== 'undefined') {
            //console.log('additionalDataToReturn: ' + dataLocal.additionalDataToReturn);
            var additionalData = JSON.parse(dataLocal.additionalDataToReturn);
            productNameAr = additionalData.mncklevu.name_ar;
            productSticker = additionalData.mncklevu.stickers;
        }
    %>
    <pre><%= JSON.stringify(dataLocal, null, 2) %></pre>
     
    <li ku-product-block class="klevuProduct" data-id="<%=dataLocal.id%>">
        <!-- <div class="kuProdWrap<% if (dataLocal.inStock && dataLocal.inStock != 'yes') { %> kuProdOutOfStock<% } %>"> -->
        <div class="kuProdWrap">
            <header ku-block data-block-id="ku_landing_result_item_header">
                <%=helper.render('landingProductBadge', scope, data, dataLocal) %>
            </header>
            <% var desc = [dataLocal.summaryAttribute,dataLocal.packageText,dataLocal.summaryDescription].filter(function(el) { return el; }); desc = desc.join(" "); %>
            <main ku-block data-block-id="ku_landing_result_item_info">
                <div class="kuProdTop">
                    <div class="klevuImgWrap">
                        <a data-id="<%=dataLocal.id%>" href="<%=dataLocal.url%>" class="klevuProductClick kuTrackRecentView">
                            <img src="<%=dataLocal.image%>" origin="<%=dataLocal.image%>" onerror="klevu.dom.helpers.cleanUpProductImage(this)" alt="<%=updatedProductName%>" class="kuProdImg">
                            <%=helper.render('landingImageRollover', scope, data, dataLocal) %>
                        </a>                        
                    </div>
                    <%=productSticker%>
                    <!-- <div class="kuQuickView">
                        <button data-id="<%=dataLocal.id%>" class="kuBtn kuBtnLight kuQuickViewBtn" role="button" tabindex="0" area-label="">Quick view</button>
                    </div> -->
                </div>
            </main>
            <footer ku-block="" data-block-id="ku_landing_result_item_footer">
                <div class="kuProdBottom">
                    <!-- <% if (dataLocal.inStock && dataLocal.inStock != 'yes') { %>
                        <%=helper.render('landingProductStock', scope, data, dataLocal) %>              
                    <% } %> -->
                    <div class="kuNames">
                        <div class="kuName"><a data-id="<%=dataLocal.id%>" href="<%=dataLocal.url%>" class="klevuProductClick kuTrackRecentView" title="<%= updatedProductName %>"><%= updatedProductName %></a></div>
                        <div class="kuName kuNameAr"><a data-id="<%=dataLocal.id%>" href="<%=dataLocal.url%>" class="klevuProductClick kuTrackRecentView" title="<%= productNameAr %>"><%= productNameAr %></a></div>
                    </div>
                    <% if(klevu.search.modules.kmcInputs.base.getShowPrices()) { %>
                        <div class="kuPrice">
                            <%
                                var kuTotalVariants = klevu.dom.helpers.cleanUpPriceValue(dataLocal.totalVariants);
                                var kuStartPrice = klevu.dom.helpers.cleanUpPriceValue(dataLocal.startPrice,dataLocal.currency);
                                var kuSalePrice = klevu.dom.helpers.cleanUpPriceValue(dataLocal.salePrice,dataLocal.currency);
                                var kuPrice = klevu.dom.helpers.cleanUpPriceValue(dataLocal.price,dataLocal.currency);
                            %>
                            <% if(!Number.isNaN(kuTotalVariants) && !Number.isNaN(kuStartPrice)) { %>
                                <div class="kuSalePrice kuStartPrice kuClippedOne">
                                    <span class="klevuQuickPriceGreyText">{l s='Starting at' mod='mncklevu'}</span>
                                    <span><%=helper.processCurrency(dataLocal.currency,parseFloat(dataLocal.startPrice))%></span>                                
                                </div>
                            <% } else if(!Number.isNaN(kuSalePrice) && !Number.isNaN(kuPrice) && (kuPrice > kuSalePrice)){ %>
                                <span class="kuOrigPrice kuClippedOne">
                                    <%= helper.processCurrency(dataLocal.currency,parseFloat(dataLocal.price)) %>
                                </span>
                                <span class="kuSalePrice kuSpecialPrice kuClippedOne">
                                    <%=helper.processCurrency(dataLocal.currency,parseFloat(dataLocal.salePrice))%>
                                </span>
                            <% } else if(!Number.isNaN(kuSalePrice)) { %>
                                <span class="kuSalePrice kuSpecialPrice">
                                    <%= helper.processCurrency(dataLocal.currency,parseFloat(dataLocal.salePrice)) %>
                                </span>
                            <% } else if(!Number.isNaN(kuPrice)) { %>
                                <span class="kuSalePrice">
                                    <%= helper.processCurrency(dataLocal.currency,parseFloat(dataLocal.price)) %>
                                </span>
                            <% } %>
                            <%=helper.render('searchResultProductVATLabel', scope, data, dataLocal) %>
                        </div>
                    <% } %>
                </div>
                <div class="kuProdAdditional">
                    <div class="kuProdAdditionalData">
                        <% if(desc && desc.length) { %>
                            <div class="kuDesc kuClippedTwo"> <%=desc%> </div>
                        <% } %>
                        <%=helper.render('landingProductSwatch',scope,data,dataLocal) %>
                        <%=helper.render('klevuLandingProductRating',scope,data,dataLocal) %>
                        <% var isAddToCartEnabled = klevu.search.modules.kmcInputs.base.getAddToCartEnableValue(); %>
                        <% if(isAddToCartEnabled) { %>
                            <%=helper.render('landingPageProductAddToCart',scope,data,dataLocal) %> 
                        <% } %>
                    </div>
                </div>
            </footer>             
        </div>
    </li>
</script>

<script type="template/klevu" id="customSearchResultProductVATLabel">
	<%
        var caption = klevu.search.modules.kmcInputs.base.getVatCaption();
        var vatCaption = caption;
    %>
    <div class="kuCaptionVat kuClippedOne"><%= vatCaption %></div>
</script>

<script type="template/klevu" id="customLandingSearchResultProductStock">
    <%
        var outOfStockCaption = klevu.search.modules.kmcInputs.base.getOutOfStockCaptionValue();
        var productStockStatus = outOfStockCaption;
    %>
    <div class="kuCaptionStockOut">
        <div><%= productStockStatus %></div>
    </div>
</script>

<script type="template/klevu" id="customLandingPageProductAddToCart">
    <% if (typeof dataLocal.additionalDataToReturn !== 'undefined') { %>
        <% var additionalData = JSON.parse(dataLocal.additionalDataToReturn); %>
        <div class="kuAddtocart" data-id="<%=dataLocal.id%>">
            <a target="_self" href="<%=dataLocal.url%>" class="ajax_add_to_cart_button hover_fly_btn btn-spin kuBtn kuBtnDark<% if (dataLocal.inStock == 'no') { %> kuBtnDisabled<% } %>" rel="nofollow" role="button" tabindex="0" area-label="{l s='Add to cart' mod='mncklevu'}" data-id-product="<%=additionalData.mncklevu.id_product%>" data-id-product-attribute="<%=additionalData.mncklevu.id_product_attribute%>" data-minimal-quantity="<%=additionalData.mncklevu.minimal_quantity%>">
                <div class="hover_fly_btn_inner">
                    <% if (dataLocal.inStock != 'no') { %>
                        <i class="fto-glyph icon_btn"></i>
                        <span>{l s='Add to cart' mod='mncklevu'}</span>
                    <% } else { %>
                        <span><%=klevu.search.modules.kmcInputs.base.getOutOfStockCaptionValue() %></span>
                    <% } %>
                </div>
            </a>
        </div>
    <% } %>
</script>