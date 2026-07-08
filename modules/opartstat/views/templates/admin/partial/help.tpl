{**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <sav@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 *}
<div id="helpContainer" class="panel panel-default">
    <div class="panel-heading">
        <h2>{l s='Help' mod='opartstat'}</h2>
    </div>
    <h2>{l s='Frequently asked question' mod='opartstat'}</h2>
    <div class="questionContainer">
        <h3>{l s='Why trafic data are limited?' mod='opartstat'}</h3>
        <p>
            {l s='Trafic data are limited to avoid saturating your database ' mod='opartstat'}<br />
            {l s='You can increase the space allocated to the traffic data by changing the value of the "Maximum number of visits stored in the database" setting in the module configuration.' mod='opartstat'}<br />
            {l s='But note that a too big value can reduce the performance of your store.' mod='opartstat'}<br />
            {l s='If you want to significantly increase the size of traffic data you can [1]subscribe to Op\'art stat Premium.[/1]' tags=["<a href='{$adminOpartStatSubscriptionSellsPageUrl}'>"]
            mod='opartstat'}
        </p>
    </div>
    <div class="questionContainer">
        <h3>{l s='The module does not display any data why?' mod='opartstat'}</h3>
        <p>
            {l s='By default the module use the invoice date.' mod='opartstat'}<br />
            {l s='If your prestashop do not generate any invoice or if the invoice date is not set in the database, the module can\'t find any data' mod='opartstat'}<br />
            {l s='In this case, go to the module configuration and check the "Use order creation date instead of invoice" setting ' mod='opartstat'}
        </p>
    </div>
    <div class="questionContainer">
        <h3>{l s='The module does not seem to take into account all the commands, why?' mod='opartstat'}</h3>
        <p>
            {l s='Verify that you have checked all the right order statu in the module configuration' mod='opartstat'}<br />
            {l s='Here is an example of common status for valid orders : Delivered, Shipped, Payment accepted' mod='opartstat'}<br />
            {l s='Do the same for the refunded orders and the incoming orders' mod='opartstat'}<br />
            {l s='Note that the "partial refund" status has be check for the valid order, you\'ll find more explanation below' mod='opartstat'}
        </p>
    </div>
    <div class="questionContainer">
        <h3>{l s='Why the "partial refund" status must be checked for valid orders?' mod='opartstat'}</h3>
        <p>
            {l s='Because an order with some refunded product is still a valid order' mod='opartstat'}<br />
            {l s='Partially refunded orders must be considered valid to be correctly taken into account in the various calculations' mod='opartstat'}<br />
            {l s='But don\'t worry the refunded products will be added to your total refund and not added into your total revenues or profits' mod='opartstat'}<br />
        </p>
    </div>
    <div class="questionContainer">
        <h3>{l s='Why do i have some value in refunded metric but 0 refunded orders?' mod='opartstat'}</h3>
        <p>
            {l s='Because refunded metric combine the partial and fully refunded orders while number of refund use only fully refunded order' mod='opartstat'}<br />
        </p>
    </div>
    <div class="questionContainer">
        <h3>{l s='How to verify that the data provided by the module are correct?' mod='opartstat'}</h3>
        <p>
            {l s='The module has been tested on many store so we are pretty confident that the data are accurate' mod='opartstat'}<br />
            {l s='But if you want to be sure that the module provide correct data, here is an example to check global revenues:' mod='opartstat'}<br />
        <ol>
            <li>{l s='First of all choose a short period of time to limit the number of data you will have to verify.' mod='opartstat'}
            </li>
            <li>{l s='Watch the global revenu provided by the module for this period' mod='opartstat'}</li>
            <li>{l s='Go to your order admin page and filter your orders using the same period of time' mod='opartstat'}
            </li>
            <li>{l s='Open each orders' mod='opartstat'}</li>
            <li>{l s='For each order open each product it contains' mod='opartstat'}</li>
            <li>{l s='Multiply the quantité of each product by it\'s price without taxe' mod='opartstat'}</li>
            <li>{l s='Add all values obtained' mod='opartstat'}</li>
            <li>{l s='The total should be the same that the value provided by the module' mod='opartstat'}</li>
        </ol>
        </p>
    </div>
    <div class="questionContainer">
        <h3>{l s='How are sales attributed to traffic sources, campaigns, etc.?' mod='opartstat'}</h3>
        <p>
            {l s='The module uses the "first click" method to attribute a conversion. This method attributes the conversion to the first visit made within a given time frame (default is 72 hours) before the order was placed.' mod='opartstat'}<br />
            {l s='If, within the 72 hours preceding their order, a user discovers your site for the first time via Google, Google will be attributed the conversion, even if, in the meantime, they returned to your site through other sources (such as Facebook, etc.).' mod='opartstat'}<br />
            {l s='You can modify the time frame for attributing a conversion in the module\'s configuration by going to the "General Settings" tab / "Conversion Attribution Duration (in hours)".' mod='opartstat'}<br />
        </p>
    </div>
    <div class="questionContainer">
        <h3>{l s='How are sales attributed to traffic sources, campaigns, etc.?' mod='opartstat'}</h3>
        <p>
            {l s='The module uses the "first click" method to attribute a conversion. This method attributes the conversion to the first visit made within a given time frame (default is 72 hours) before the order was placed.' mod='opartstat'}<br />
            {l s='If, within the 72 hours preceding their order, a user discovers your site for the first time via Google, Google will be attributed the conversion, even if, in the meantime, they returned to your site through other sources (such as Facebook, etc.).' mod='opartstat'}<br />
            {l s='You can modify the time frame for attributing a conversion in the module\'s configuration by going to the "General Settings" tab / "Conversion Attribution Duration (in hours)".' mod='opartstat'}<br />
        </p>
    </div>
    <!--<div class="questionContainer">
        <h3>{l s='I just signed up for Op’art Stat Premium, but I have no Google Ads data. Why?' mod='opartstat'}</h3>
        <p>
            {l s='That\'s normal 😉' mod='opartstat'}<br />
            {l s='Google Ads imposes two limitations regarding data retrieval:' mod='opartstat'}<br />
        </p>
        <ol>
            <li>{l s='Only 90 days of history can be retrieved at a time.' mod='opartstat'}</li>
            <li>{l s='The number of data points that can be retrieved per call is limited.' mod='opartstat'}</li>
        </ol>
        <p>
            {l s='Op’art Stat will therefore retrieve a batch of data every 10 minutes until it has retrieved all of the last 90 days.' mod='opartstat'}<br />
            {l s='Then, every night at 3:00 AM, Op’art Stat will retrieve the data from the previous day.' mod='opartstat'}<br />
            {l s='So, by tomorrow, the module should have retrieved the 90 days of history and will be able to add each new day.' mod='opartstat'}<br />
            {l s='This way, you will be able to view the last 90 days of your Google Ads data, and each following day will add an additional day, resulting in 91, 92, 93 days, and so on.' mod='opartstat'}<br />
        </p>
    </div>
    <div class="questionContainer">
    <h3>{l s='The revenue and conversion data do not match those indicated in my Google Ads account. Why?' mod='opartstat'}</h3>
    <p>
        {l s='Op’art Stat does not use the conversions provided by Google Ads; it uses its own calculation method for two reasons.' mod='opartstat'}<br />
        <br />
        {l s='Firstly, Google has every interest in telling you that it generates a lot of revenue so that you continue to invest.' mod='opartstat'}<br />
        <br />
        {l s='Secondly,  the conversions indicated by Google Ads are necessarily higher than they should be because Google Ads does not know your visit history (unlike Op’art Stat).' mod='opartstat'}<br />
        {l s='So, if it sees an order linked to a click on one of its ads, it will inevitably attribute the conversion to itself.' mod='opartstat'}<br />
        <br />
        {l s='Let\'s take an example:' mod='opartstat'}<br />
        &nbsp; &nbsp; {l s='➡ On Monday, Louis discovers your site via Pinterest.' mod='opartstat'}<br />
        &nbsp; &nbsp; {l s='➡ On Tuesday, he clicks on a Google Ads ad.' mod='opartstat'}<br />
        &nbsp; &nbsp; {l s='➡ On Wednesday, he places an order.' mod='opartstat'}<br />
        <br />
        {l s='It was Pinterest that made your site known, so the conversion should be attributed to Pinterest. 🏆' mod='opartstat'}<br />
        <br />
        {l s='But Google Ads does not know that there was a visit from Pinterest.' mod='opartstat'}<br />
        {l s='For Google, the first visit is the one generated by Google Ads, so it will attribute the sale to itself.' mod='opartstat'}<br />
        <br />
        {l s='This is why the module attributes conversions itself, and all the resulting values are thus much more objective.' mod='opartstat'}<br />
    </p>
</div>-->
</div>