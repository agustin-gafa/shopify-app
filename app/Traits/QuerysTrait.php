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

    public function queryCreateProduct(){
        return  <<<GRAPHQL
                    mutation createProductWithVariants(\$input: ProductInput!) {
                        productCreate(input: \$input) {
                            product {
                                id
                                title    
                                descriptionHtml                            
                                options{
                                    name                                    
                                }
                                variants {
                                    edges {
                                        node {
                                            id
                                            title
                                            price
                                            sku    
                                            selectedOptions{
                                                name
                                                value
                                            }                                    
                                        }
                                    }
                                }
                            }
                            userErrors {
                                field
                                message
                            }
                        }
                    }
                GRAPHQL;

    }

    public function queryFindProductSKU(){
        return  <<<GRAPHQL
                query FindProductBySKU(\$sku: String!) {
                        products(query: "sku:\$sku") {
                        edges {
                        node {
                            id
                            title
                            descriptionHtml
                            tags
                            totalVariants
                            totalInventory
                            status
                            createdAt
                            updatedAt
                            publishedAt
                            onlineStoreUrl
                            images(first: 10) {
                            edges {
                                node {
                                id
                                src
                                }
                            }
                            }
                            metafields(first: 10) {
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

    
}