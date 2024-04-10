<?php

namespace App\Traits;

trait MutationTrait {

    public function mutaVariantStock(){

        return <<<GRAPHQL
            mutation UpdateVariantInventory(\$id: ID!, \$inventoryQuantity: Int!) {
                productVariantUpdate(input: {id: \$id, inventoryQuantity: \$inventoryQuantity}) {
                    productVariant {
                        id                        
                        inventoryQuantity
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL;
          
    }

    public function mutaCreateProduct(){
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

    
}