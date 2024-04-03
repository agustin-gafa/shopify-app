<?php

namespace App\Traits;

trait QuerysTrait {

    public function queryGetProducts()
    {
        return <<<GRAPHQL
        query(\$first: Int!, \$after: String, \$before: String, \$query: String!) {
            products(first: \$first, after: \$after, before: \$before, query: \$query) {
                pageInfo {
                    hasNextPage
                    hasPreviousPage
                    startCursor
                    endCursor
                }
                edges {
                    node {
                        id
                        title
                        descriptionHtml
                        tags
                        totalVariants
                        totalInventory
                        vendor
                        status
                        images(first: 250) { # Obtener las primeras 5 imágenes del producto
                            edges {
                                node {
                                    id
                                    src
                                }
                            }
                        }                        
                        metafields(first: 250) { # Obtener los primeros 5 metafields del producto
                            edges {
                                node {
                                    id
                                    namespace
                                    key
                                    value
                                }
                            }
                        }
                        variants(first: 250) {
                            edges {
                                node {
                                    id
                                    title
                                    price
                                    sku
                                    inventoryQuantity
                                }
                            }
                        }
                    }
                }
            }
        }
        GRAPHQL;
    }

    public function queryFindVariantSKU(){
        return  <<<GRAPHQL
            query(\$first: Int!, \$after: String, \$before: String, \$query: String!) {
                productVariants(first: \$first, after: \$after, before: \$before, query: \$query) {
                    pageInfo {
                    hasNextPage
                    hasPreviousPage
                    startCursor
                    endCursor
                    }
                    edges {
                    node {
                        id
                        title
                        price
                        sku
                        inventoryQuantity
                        inventoryItem {
                            id
                        }
                        product {
                            id
                            title
                        }
                    }
                    }
                }
            }
        GRAPHQL;
    }

    
}