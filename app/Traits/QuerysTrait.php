<?php

namespace App\Traits;

trait QuerysTrait {

    public function queryGetProducts()
    {
        return <<<GRAPHQL
        query(\$first: Int!, \$after: String, \$before: String, \$query: String!) {
            products(first: \$first, after: \$after, before: \$before, query: \$query, sortKey: UPDATED_AT, reverse: true) {
                pageInfo {
                    hasNextPage
                    hasPreviousPage
                    startCursor
                    endCursor
                }
                edges {
                    node {
                        id
                        handle
                        title
                        descriptionHtml
                        tags
                        totalVariants
                        totalInventory                    
                        vendor
                        status   
                        onlineStoreUrl                     
                        images(first: 10) {
                            edges {
                                node {
                                    # id
                                    src
                                }
                            }
                        }                        
                        metafields(first: 10) {
                            edges {
                                node {
                                    id                                    
                                    key
                                    value
                                    type
                                    namespace                                    
                                }
                            }
                        }      
                        updatedAt                  
                        variants(first: 20) {
                            edges {
                                node {
                                    id
                                    title
                                    price
                                    sku
                                    inventoryQuantity
                                    inventoryItem{
                                        # id                              
                                        inventoryLevels(first: 10) {                             
                                            edges {
                                                node {
                                                    # id                                    
                                                    location {
                                                        id
                                                        name
                                                    }                                              
                                                    quantities(names: "available") {
                                                        quantity
                                                    }
                                                }
                                            }
                                        }

                                    }                                    
                                }
                            }
                        }
                    }
                }
            }
        }
        GRAPHQL;
    }


    public function queryGetProductsPrev()
    {
        return <<<GRAPHQL
        query(\$first: Int!, \$after: String, \$query: String!) {
            products(first: \$first, after: \$after, query: \$query, sortKey: UPDATED_AT,reverse: true ) {
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
                        onlineStoreUrl
                        images(first: 10) {
                            edges {
                                node {
                                    id
                                    src
                                }
                            }
                        }                        
                        # metafields(first: 10) {
                        #     edges {
                        #         node {
                        #             id
                        #             namespace
                        #             key
                        #             value
                        #         }
                        #     }
                        # }
                        updatedAt
                        variants(first: 20) {
                            edges {
                                node {
                                    id
                                    title
                                    price
                                    sku
                                    inventoryQuantity
                                    inventoryItem{
                                        id                              
                                        inventoryLevels(first: 10) {                             
                                            edges {
                                                node {
                                                    id                                    
                                                    location {
                                                        id
                                                        name
                                                    }                                              
                                                    quantities(names: "available") {
                                                        quantity
                                                    }
                                                }
                                            }
                                        }

                                    }
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
                            inventoryItem{
                                id                              
                                inventoryLevels(first: 100) {                             
                                    edges {
                                        node {
                                            id                                    
                                            location {
                                                id
                                                name
                                            }                                              
                                            quantities(names: "available") {
                                                quantity
                                            }
                                        }
                                    }
                                }

                            }
                            product {
                                id
                                title
                                totalInventory
                            }
                        }
                    }
                }
            }
        GRAPHQL;
    }

    
}