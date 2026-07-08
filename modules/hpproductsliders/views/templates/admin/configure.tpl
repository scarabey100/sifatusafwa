<script>
    let productsliderssecret = '{$secret}';
    let hproductslidersajax = '{$hproductslidersajax}';
</script>
<style>
    #hpproductsliders_form #productListTable {
        border-collapse: collapse;
        width: 100%;
    }

    #hpproductsliders_form #productListTable th, #hpproductsliders_form #productListTable td {
        border: 1px solid #ccc;
        padding: 10px;
    }


    #hpproductsliders_form #productListTable .ui-sortable-helper {
        background: #f5f5f5;
    }

    #hpproductsliders_form #productListTable .drag-handle {
        cursor: grab;
        width: 40px;
        text-align: center;
    }

    #hpproductsliders_form #productListTable td.delete {
        width: 40px;
        text-align: center;
        cursor: pointer;
    }

</style>
</head>
<body>

    <table id="productListTable">
        <thead>
            <tr>
                <th></th>
                <th>Item</th>
                <th></th>
            </tr>
        </thead>

        <tbody id="sortable">
            {foreach $products as $product}
                <tr data-id="{$product.id_product}">
                    <td class="drag-handle">☰</td>
                    <td>{$product.name}</td>
                    <td class="delete"><i class="material-icons">delete</i></td>
                </tr>
            {/foreach}
        </tbody>
    </table>
