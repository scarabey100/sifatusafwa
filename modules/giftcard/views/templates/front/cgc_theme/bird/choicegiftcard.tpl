{*
 *
 * GIFT CARD
 *
 * @category pricing_promotion
 * @author EIRL Timactive De Véra
 * @copyright TIMACTIVE 2013
 * @version 1.0.0
 *
 *************************************
 **            GIFT CARD			 *              
 **             V 1.0.0              *
 *************************************
 * +
 * + Languages: EN, FR
 * + PS version: 1.5
 *
 *}
{* Variable used in js*}
<section id="choicegiftcard" class="section" data-link-controller="{$linkcgc|escape:'htmlall':'UTF-8'}">
	<div class="section__wrp container__fluid">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding: 0;">
			{* visible when ajax call*}
			<div class="ui-loader-background"> </div>{*** for what ? ***}
			{capture name=path}{l s='Gift card' mod='giftcard'}{/capture}
			
			{***$front_content nofilter***}{** CONTENT HTML / SETTING MODULE **} {***need to ask***}
			<header class="headernew">
				<div class="headernew__wrp">
					<div class="headernew__txt">
						<h2 class="headernew__title">
							Gift Card
						</h2>
						<div class="headernew__desc">Indulge your loved once in a few clicks! Anniversary, Valentine's Day, Wedding, Christmas. <br />
						Send pernolized gift card by e-mail to the address of your choice.<br />
						The amount is then availabe as a voucher on our entire site.
						</div>
					</div>
					<div class="headernew__img">
						<img src="/modules/giftcard/views/img/gifts.png" alt="gift">
					</div>
				</div>
			</header>

			<div class="section__content">
				<div class="progress">
					<h2 class="progress__title">Select delivery mode</h2>
					<div class="form-progress">
						<div class="progress-back"></div>
						<progress class="form-progress-bar" aria-labelledby="form-progress-completion"></progress>
						
						<div class="form-progress-indicator one active"></div>
						<div class="form-progress-indicator two"></div>
						<div class="form-progress-indicator three"></div>
						
						<p id="form-progress-completion" class="js-form-progress-completion sr-only" aria-live="polite">0% complete</p>
					</div>
				</div>
				<div class="bottom"></div>
				<div class="animation-container" style="height: 400px;">
					{if !isset($templates) || count($templates) == 0}
						<p class="warning">
									{l s='No model available' mod='giftcard'}
						</p>
					{elseif !isset($cards) || $cards|@count == 0}
						<p class="warning">
									{l s='No card available' mod='giftcard'}
						</p>
					{else}
				       <form class="form" id="formgiftcard" action="{$linkcgc|escape:'quotes':'UTF-8'}" method="POST">
				        {*** STEP 1 RECEPTION MODE ***}
				        <section  id= "gc-step-receptmode" class="form-step js-current-step js-current-frame sstep-1" data-gcstep-enable="1" data-gcstep-valid="0" data-step="1">
				        <h3 class="step-title h3">
						    <i class="material-icons done">&#xE876;</i>
						    <span class="step-number">1</span>
						    <span class="step-title-1">{l s='Select the reception mode' mod='giftcard'}</span>
						    <span class="step-edit text-muted"><i class="material-icons edit">mode_edit</i> edit</span>
				  		</h3>
				  		<div class="gc-section-content gc-section-content-first">
					    <div class="gc-receptmode-options">
					    {if $virtual_cards_available}
					     <div class="gc-receptmode-option gc-receptmode-option-nonmail clearfix">

				            <input
				              class="ps-shown-by-js"
				              id="receptmode_printathome"
				              name="receptmode"
				              type="radio"
				              value="0"
				            >
				            <label for="receptmode_printathome">
								<span class="form-step__wrp">
									<span class="form-step__ico">
										<svg version="1.1" id="Capa_3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
											 viewBox="0 0 482.5 482.5" style="enable-background:new 0 0 482.5 482.5;" xml:space="preserve">
										<g>
											<g>
												<path d="M399.25,98.9h-12.4V71.3c0-39.3-32-71.3-71.3-71.3h-149.7c-39.3,0-71.3,32-71.3,71.3v27.6h-11.3
													c-39.3,0-71.3,32-71.3,71.3v115c0,39.3,32,71.3,71.3,71.3h11.2v90.4c0,19.6,16,35.6,35.6,35.6h221.1c19.6,0,35.6-16,35.6-35.6
													v-90.4h12.5c39.3,0,71.3-32,71.3-71.3v-115C470.55,130.9,438.55,98.9,399.25,98.9z M121.45,71.3c0-24.4,19.9-44.3,44.3-44.3h149.6
													c24.4,0,44.3,19.9,44.3,44.3v27.6h-238.2V71.3z M359.75,447.1c0,4.7-3.9,8.6-8.6,8.6h-221.1c-4.7,0-8.6-3.9-8.6-8.6V298h238.3
													V447.1z M443.55,285.3c0,24.4-19.9,44.3-44.3,44.3h-12.4V298h17.8c7.5,0,13.5-6,13.5-13.5s-6-13.5-13.5-13.5h-330
													c-7.5,0-13.5,6-13.5,13.5s6,13.5,13.5,13.5h19.9v31.6h-11.3c-24.4,0-44.3-19.9-44.3-44.3v-115c0-24.4,19.9-44.3,44.3-44.3h316
													c24.4,0,44.3,19.9,44.3,44.3V285.3z"/>
												<path d="M154.15,364.4h171.9c7.5,0,13.5-6,13.5-13.5s-6-13.5-13.5-13.5h-171.9c-7.5,0-13.5,6-13.5,13.5S146.75,364.4,154.15,364.4
													z"/>
												<path d="M327.15,392.6h-172c-7.5,0-13.5,6-13.5,13.5s6,13.5,13.5,13.5h171.9c7.5,0,13.5-6,13.5-13.5S334.55,392.6,327.15,392.6z"
													/>
												<path d="M398.95,151.9h-27.4c-7.5,0-13.5,6-13.5,13.5s6,13.5,13.5,13.5h27.4c7.5,0,13.5-6,13.5-13.5S406.45,151.9,398.95,151.9z"
													/>
											</g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										</svg>
									</span>
									<span class="form-step__txt">
										 <span>{l s='Print at home' mod='giftcard'}</span>
									</span>
								</span>
				            </label>
				           </div>
				           <div class="gc-receptmode-option gc-receptmode-option-mail clearfix">
				            <div style="display: flex; flex-direction: column; width: 100%;">

				            <input
				              class="ps-shown-by-js"
				              id="receptmode_mail"
				              name="receptmode"
				              type="radio"
				              value="1"
				            >
				            <label for="receptmode_mail">
								<span class="form-step__wrp">
									<span class="form-step__ico">
										<svg version="1.1" id="Capa_2" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
											 viewBox="0 0 483.3 483.3" style="enable-background:new 0 0 483.3 483.3;" xml:space="preserve">
										<g>
											<g>
												<path d="M424.3,57.75H59.1c-32.6,0-59.1,26.5-59.1,59.1v249.6c0,32.6,26.5,59.1,59.1,59.1h365.1c32.6,0,59.1-26.5,59.1-59.1
													v-249.5C483.4,84.35,456.9,57.75,424.3,57.75z M456.4,366.45c0,17.7-14.4,32.1-32.1,32.1H59.1c-17.7,0-32.1-14.4-32.1-32.1v-249.5
													c0-17.7,14.4-32.1,32.1-32.1h365.1c17.7,0,32.1,14.4,32.1,32.1v249.5H456.4z"/>
												<path d="M304.8,238.55l118.2-106c5.5-5,6-13.5,1-19.1c-5-5.5-13.5-6-19.1-1l-163,146.3l-31.8-28.4c-0.1-0.1-0.2-0.2-0.2-0.3
													c-0.7-0.7-1.4-1.3-2.2-1.9L78.3,112.35c-5.6-5-14.1-4.5-19.1,1.1c-5,5.6-4.5,14.1,1.1,19.1l119.6,106.9L60.8,350.95
													c-5.4,5.1-5.7,13.6-0.6,19.1c2.7,2.8,6.3,4.3,9.9,4.3c3.3,0,6.6-1.2,9.2-3.6l120.9-113.1l32.8,29.3c2.6,2.3,5.8,3.4,9,3.4
													c3.2,0,6.5-1.2,9-3.5l33.7-30.2l120.2,114.2c2.6,2.5,6,3.7,9.3,3.7c3.6,0,7.1-1.4,9.8-4.2c5.1-5.4,4.9-14-0.5-19.1L304.8,238.55z"
													/>
											</g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										</svg>

									</span>
									<span class="form-step__txt">
										<span>{l s='Send by e-mail' mod='giftcard'}</span>
									</span>
								</span>

				            </label>
								  <div class="form-step__boxin">
									<div class="row form-step__inputs">
										<div class="form-step__arrw"></div>
										<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
											<input name="mailto" class="form-step__input email" type="text" placeholder="{l s='To : Email' mod='giftcard'}" style="width: 100%;">
										</div>
										<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
										  <div class="form-step__rflex">
											<div class="form-step__preinput">select day</div>
											{* Not delete is use in translat tools
											{l s='January' mod='giftcard'}
											{l s='February' mod='giftcard'}
											{l s='March' mod='giftcard'}
											{l s='April' mod='giftcard'}
											{l s='May' mod='giftcard'}
											{l s='June' mod='giftcard'}
											{l s='July' mod='giftcard'}
											{l s='August' mod='giftcard'}
											{l s='September' mod='giftcard'}
											{l s='October' mod='giftcard'}
											{l s='November' mod='giftcard'}
											{l s='December' mod='giftcard'}
											*}
											<select name="days" id="days" class="form-step__input form-step__input-mini">
												<option value="">-</option>
												{foreach from=$days item=v}
													<option value="{$v}" {if ($sl_day == $v)}selected="selected"{/if}>{$v}&nbsp;&nbsp;</option>
												{/foreach}
											</select>
											<select id="months" name="months" class="form-step__input form-step__input-mini">
												<option value="">-</option>
												{foreach from=$months key=k item=v}
													<option value="{$k}" {if ($sl_month == $k)}selected="selected"{/if}>{l s=$v mod='giftcard'}&nbsp;</option>
												{/foreach}
											</select>
											<select id="years" name="years" class="form-step__input form-step__input-mini">
												<option value="">-</option>
												{foreach from=$years item=v}
													<option value="{$v}" {if ($sl_year == $v)}selected="selected"{/if}>{$v}&nbsp;&nbsp;</option>
												{/foreach}
											</select>
										  </div>
										</div>
									</div>
								  </div>
				            </div>
				            
					       
				           </div>
					     {/if}
					     {if $physical_cards_available}
					     	<div class="gc-receptmode-option gc-receptmode-option-nonmail clearfix">

				           <input
				             class="ps-shown-by-js"
				             id="receptmode_post"
				             name="receptmode"
				             type="radio"
				             value="2"
				             {if !$virtual_cards_available}checked{/if}
				           >

				            <label for="receptmode_post">
								<span class="form-step__wrp">
									<span class="form-step__ico">
										<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
											 viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve">
										<g>
											<path d="M0,8.5v43h60v-43H0z M58,49.5H2v-39h56V49.5z"/>
											<path d="M53,15.5H43v10h10V15.5z M51,23.5h-6v-6h6V23.5z"/>
											<path d="M8,21.5h20c0.552,0,1-0.447,1-1s-0.448-1-1-1H8c-0.552,0-1,0.447-1,1S7.448,21.5,8,21.5z"/>
											<path d="M8,26.5h20c0.552,0,1-0.447,1-1s-0.448-1-1-1H8c-0.552,0-1,0.447-1,1S7.448,26.5,8,26.5z"/>
											<path d="M8,16.5h8c0.552,0,1-0.447,1-1s-0.448-1-1-1H8c-0.552,0-1,0.447-1,1S7.448,16.5,8,16.5z"/>
											<path d="M23,39.5c0,3.309,2.691,6,6,6s6-2.691,6-6s-2.691-6-6-6S23,36.191,23,39.5z M33,39.5c0,2.206-1.794,4-4,4s-4-1.794-4-4
												s1.794-4,4-4S33,37.294,33,39.5z"/>
											<path d="M37.77,37.139c0.683-0.824,1.778-0.824,2.461,0C40.958,38.017,41.942,38.5,43,38.5s2.042-0.483,2.77-1.361
												c0.683-0.824,1.778-0.824,2.461,0C48.958,38.017,49.942,38.5,51,38.5s2.042-0.483,2.77-1.361c0.353-0.426,0.294-1.056-0.131-1.408
												c-0.424-0.353-1.054-0.294-1.408,0.131c-0.683,0.824-1.778,0.824-2.461,0C49.042,34.983,48.058,34.5,47,34.5
												s-2.042,0.483-2.77,1.361c-0.683,0.824-1.778,0.824-2.461,0C41.042,34.983,40.058,34.5,39,34.5s-2.042,0.483-2.77,1.361
												c-0.353,0.426-0.294,1.056,0.131,1.408C36.787,37.623,37.417,37.564,37.77,37.139z"/>
											<path d="M37.77,43.139c0.683-0.824,1.778-0.824,2.461,0C40.958,44.017,41.942,44.5,43,44.5s2.042-0.483,2.77-1.361
												c0.683-0.824,1.778-0.824,2.461,0C48.958,44.017,49.942,44.5,51,44.5s2.042-0.483,2.77-1.361c0.353-0.426,0.294-1.056-0.131-1.408
												c-0.424-0.353-1.054-0.294-1.408,0.131c-0.683,0.824-1.778,0.824-2.461,0C49.042,40.983,48.058,40.5,47,40.5
												s-2.042,0.483-2.77,1.361c-0.683,0.824-1.778,0.824-2.461,0C41.042,40.983,40.058,40.5,39,40.5s-2.042,0.483-2.77,1.361
												c-0.353,0.426-0.294,1.056,0.131,1.408C36.787,43.623,37.417,43.564,37.77,43.139z"/>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										<g>
										</g>
										</svg>

									</span>
									<span class="form-step__txt">
										<span>{l s='Send by post' mod='giftcard'}</span>
									</span>
								</span>
				            </label>
				           </div>
					     {/if}
					</div>
					<div class="clearfix">

					</div>
					<div class="clearfix">
						<div class="buttons">
								<!--<button type="button" class="btn btn-alt js-reset">Reset</button>-->
							
							<button class="btn btn-nxt" type="button" data-stp="1" disabled="disabled" data-rel-gcstep="gc-step-template">
				              {l s='Continue' mod='giftcard'}
				          </button>
						</div>
				    </div>
					
						
					
					</div>
					</section>
					{*** END STEP 1 RECEPTION MODE ***}
					{*** STEP 2 BLOCK SELECT TEMPLATE ***}
				    <section  id= "gc-step-template" class="form-step js-current-step waiting hidden sstep-2" data-gcstep-enable="0" data-gcstep-valid="0" data-step="2">
				    <h3 class="step-title h3">
					  <i class="material-icons done">&#xE876;</i>
					  <span class="step-number">2</span>
					  <span class="step-title-2">{l s='Select a template' mod='giftcard'}</span>
					  <span class="step-edit text-muted"><i class="material-icons edit">mode_edit</i> edit</span>
				 	</h3>
				 	<div class="gc-section-content">
					<div id="templates_block" class="clear">
						<ul  class="gctabs clearfix">
							<li><a id="tab_template_all"  href="javascript:;" class="selected tab_template" data-tab="block_templates_all">{l s='All' mod='giftcard'}&nbsp;(<span class="ta-gc-number">{$templates|@count}</span>)</a></li>										
							{if isset($gc_tags) && $gc_tags && $gc_tags|@count > 0}
								{foreach from=$gc_tags item=tag} 
									{if (isset($templatesGroupTag[$tag.id_gift_card_tag]) && $templatesGroupTag[$tag.id_gift_card_tag]|@count > 0)}
										<li>
											<a id="tab_template_tag{$tag.id_gift_card_tag|intval}" href="javascript:;"  data-tab="block_templates_in_tags{$tag.id_gift_card_tag|intval}"  class="tab_template">{$tag.name|escape:'htmlall':'UTF-8'}&nbsp;(<span class="ta-gc-number">{$templatesGroupTag[$tag.id_gift_card_tag]|@count}</span>)</a>
										</li>
									{/if}
								{/foreach}
							{/if}
						</ul>
						<div>
							{******* TAB CONTENT ALL TEMPLATES ******}
							<div id="block_templates_all" class="gctab_content selected">
								{if isset($templates) && count($templates) > 0}
								<div class="jcarousel-wrapper" id="jcarouselcardtemplates-all">
					                <div class="jcarousel" >
					                    <ul>
					                    	
												{foreach from=$templates item=template name=thumbnails}
							                        <li class="template_item template_item{$template.id_gift_card_template|intval} {if isset($template_default) && $template_default->id==$template.id_gift_card_template}selected{/if}"  data-physicaluse="{$template.physicaluse|intval}" data-virtualuse="{$template.virtualuse|intval}">
							                        		<a href="javascript:;"  class="link_template" data-srel="link_template{$template.id_gift_card_template|intval}">

							                        			
							                        			<img src="{$link->getMediaLink("`$giftcard_templates_dir``$template.id_gift_card_template`/`$template.id_gift_card_template`-front{if $template.issvg}-{$id_lang|intval}{/if}.jpg")|escape:'quotes':'UTF-8'}" alt="
							                        			{$template.name|escape:'htmlall':'UTF-8'}" title="{$template.name|escape:'htmlall':'UTF-8'}">
							                        			
							                        			
							                        		</a>
							                        		
							                        		<a href="{$link->getMediaLink("`$giftcard_templates_dir``$template.id_gift_card_template`/`$template.id_gift_card_template`-thickbox{if $template.issvg}-{$id_lang|intval}{/if}")|escape:'quotes':'UTF-8'}.jpg" data-fancybox-group="other-views" class="thickbox-giftcard shown" title="{$template.name|escape:'html':'UTF-8'}">	
							                        			<span class="zoom_link">{l s='View larger' mod='giftcard'}</span>
							                        		</a>
							                        		<span class="check"></span>
							                        </li>
							                	{/foreach}
						            	</ul>
					        		</div>
					       			<a href="#" class="jcarousel-control-prev">&lsaquo;</a>
					        		<a href="#" class="jcarousel-control-next">&rsaquo;</a>
					        		<div class="jcarousel-pagination-container">
										<p class="jcarousel-pagination"></p>
									</div>
					            </div>
				            	{/if}
							</div>
							{******* END TAB CONTENT ALL TEMPLATES ******}
							{******* TAB CONTENT BY FILTER TAG ******}
							{if isset($gc_tags) && $gc_tags && $gc_tags|@count > 0}
								{foreach from=$gc_tags item=tag} 
									<div id="block_templates_in_tags{$tag.id_gift_card_tag|intval}" class="rte gctab_content">
									{if isset($templatesGroupTag[$tag.id_gift_card_tag]) && $templatesGroupTag[$tag.id_gift_card_tag]|@count > 0}
									<div class="jcarousel-wrapper" id="jcarouselcardtemplates-tag{$tag.id_gift_card_tag|intval}">
						                <div class="jcarousel" >
						                <ul>
						                			{foreach from=$templatesGroupTag[$tag.id_gift_card_tag] item=template}
							                        <li class="template_item template_item{$template.id_gift_card_template|intval} {if isset($template_default) && $template_default->id==$template.id_gift_card_template}selected{/if}"  data-physicaluse="{$template.physicaluse|intval}" data-virtualuse="{$template.virtualuse|intval}">

							                        		<a href="javascript:;"  class="link_template" data-srel="link_template{$template.id_gift_card_template|intval}">
							                        		
							                        		<img src="{$link->getMediaLink("`$giftcard_templates_dir``$template.id_gift_card_template`/`$template.id_gift_card_template`-front{if $template.issvg}-{$id_lang}{/if}.jpg")|escape:'quotes':'UTF-8'}" alt="
							                        			{$template.name|escape:'htmlall':'UTF-8'}" title="{$template.name|escape:'htmlall':'UTF-8'}">
							                        		</a>
							                        		
							                        		<a href="{$link->getMediaLink("`$giftcard_templates_dir``$template.id_gift_card_template`/`$template.id_gift_card_template`-thickbox{if $template.issvg}-{$id_lang|intval}{/if}")|escape:'quotes':'UTF-8'}.jpg" data-fancybox-group="other-views" class="thickbox-giftcard shown" title="{$template.name|escape:'html':'UTF-8'}">	
							                        			<span class="zoom_link">{l s='View larger' mod='giftcard'}</span>
							                        		</a>
							                        		<span class="check"></span>
							                        	
							                        </li>

							                		{/foreach}
								        </ul>
						        		</div>
						        		<a href="#" class="jcarousel-control-prev">&lsaquo;</a>
					        			<a href="#" class="jcarousel-control-next">&rsaquo;</a>
										<div class="jcarousel-pagination-container">
					        				<p class="jcarousel-pagination"></p>
										</div>
						        	</div>
									{/if}
									</div>
								{/foreach}
							{/if}
							{******* END TAB CONTENT BY FILTER TAG ******}
						</div>
					</div>
					<div class="clearfix">

						<div class="buttons">
				          <button class="btn btn-pre" data-stp="2" type="button" >
				              {l s='Pre' mod='giftcard'}
				          </button>
				          <button class="btn btn-nxt" data-stp="2" type="button"  disabled="disabled" data-rel-gcstep="gc-step-information">
				              {l s='Continue' mod='giftcard'}
				          </button>
						</div>
				    </div>
					</div>
					</section>
					{*** END STEP 2 BLOCK SELECT TEMPLATE ***}
					{*** STEP 3 GIFT CARD INFORMATION ***}
					<section  id= "gc-step-information" class="form-step js-current-step waiting hidden sstep-3" data-gcstep-enable="0" data-gcstep-valid="0" data-step="3">
					
				    <h3 class="step-title h3">
					  <i class="material-icons done">&#xE876;</i>
					  <span class="step-number">3</span>
					  <span class="step-title-3">{l s='Gift card information' mod='giftcard'}</span>
					  <span class="step-edit text-muted"><i class="material-icons edit">mode_edit</i> edit</span>
				 	</h3>
				 	<div class="gc-section-content">
					<input type="hidden" name="action" value="" />
					<input type="hidden" name="id_lang" value="{$id_lang|intval}" />
					<input type="hidden" name="token" value="{$token|escape:'html':'UTF-8'}" />
					<input type="hidden" name="id_gift_card_template" id="id_gift_card_template" value="{$template_default->id|intval}"/>
					<p> {l s='Amount' mod='giftcard'}&nbsp;
						<select name="id_product" id="ta_gc_products" style="border: none; background: #f1f1f1; box-sizing: border-box; background: #f1f1f1; border-radius: 16px;  padding: 12px 23px;  font-size: 1rem; color: #a2a2a2;">
						{foreach from=$cards item=carditem name=foo}
				           	<option value="{$carditem.id_product|intval}" {if ((!isset($card)&&$carditem.isdefaultgiftcard) || (isset($card) && $card->id==$carditem.id_product))}selected{/if}>{$carditem.price_dp}
				           	</option>
				           {/foreach}
				       	</select>
						</p>
						<div class="row">
						  <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
						  
							<p class="from">
					       		 <input name="from" type="text" class="form-step__input form-step__lastinput input input_user_from" placeholder="{l s='From : your lastname' mod='giftcard'}"  />
					     	 </p>
					      </div>
					      <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
					      	<p class="name">
					       		 <input name="lastname" type="text" class="form-step__input form-step__lastinput input input_user_to" placeholder="{l s='To : Lastname' mod='giftcard'}"  />
					     	</p>
					      </div>
					    </div>
					    <div class="row">
					    	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						      <div class="text form-step__itext">
						        <textarea name="message" class="form-step__input form-step__lastinput input textarea_comment"  placeholder="{l s='Indicate your message' mod='giftcard'}" onkeyup="countChar(this)"></textarea>
						        <div id="remaining" class="remaining characters">(<span id="charNum">200</span>&nbsp;{l s='remaining characters' mod='giftcard'})</div>
						      </div>
						    </div>
					    </div>
						<div class="row">
							<div class="col-sm-12">
					          <button class="btn btn-pre" data-stp="3" type="button" >
					              {l s='Pre' mod='giftcard'}
					          </button>
							</div>
						</div>
				      {*** BUTTON SUBMIT ***}
				      <div class="row ta-gc-submit">
				      	<div class="col-sm-6">
				      	   <button class="btn btn-lst pull-xs-left" type="button"  disabled="disabled"  data-ta-action="preview">
				              {l s='Preview' mod='giftcard'}
				          </button>
				        </div>
				        <div class="col-sm-6">
				          <button class="btn btn-lst btn-primary pull-xs-right" type="button"  disabled="disabled"  data-ta-action="add_to_cart">
				              <i class="material-icons shopping-cart">&#xE547;</i>
				              {l s='Add to cart' mod='giftcard'}
				          </button>
				        </div>
				    </div>
					{*** END BUTTON SUBMIT ***}
					</div>
					</section>
					{*** END STEP 3 GIFT CARD INFORMATION ***}
					<br/>
					{* is use for display ajax messages errors, sucess*}
					<div class="messages"></div>
					
					</form>
					{/if}
				</div>
			</div>
		</div>
	</div>
</section>
