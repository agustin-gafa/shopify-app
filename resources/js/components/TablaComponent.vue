<template>
  <v-data-table-server
    v-model:expanded="expanded"
    :headers="productHeaders"
    :items="productos"
    items-length="2000"
    item-value="node.title"
    show-expand
    @update:options="getProducts"
    :loading="loading"
  >
    <template v-slot:top>
      <v-card
        title="PRODUCTOS"
        flat
      >
        <template v-slot:text>
          <v-text-field        
            label="Buscar"
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            hide-details
            single-line
          ></v-text-field>
        </template>
      </v-card>
    </template>

    <template v-slot:item.node.tags="{value}">
      
      <template v-for="(tag, index) in value">
        <v-chip class="ma-1" size="small">
          {{tag}}
        </v-chip>
      </template>

    </template>

    <template v-slot:expanded-row="{ columns, item }">
      <tr>
        <td :colspan="columns.length">
          <pre>{{ item.node.variants }}</pre>
        </td>
      </tr>
    </template>
  </v-data-table-server>
</template>

<script>
  export default {
    created() {
      this.getProducts( {page:this.currentPage} );
    },
    data () {
      return {
        productos:[],
        paginacion:[],
        loading: true,
        cursor:"",
        currentPage:1,
        

        expanded: [],
        productHeaders: [
          {
            title: 'Producto',
            align: 'start',
            sortable: false,
            key: 'node.title',
          },
          { title: 'STATUS', key: 'node.status' },
          { title: 'TAGS', key: 'node.tags' },
          { title: 'TOTAL INVENTARIO', key: 'node.totalInventory' },
          { title: '# Variantes', key: 'node.totalVariants' },                  
          { title: '', key: 'data-table-expand' },
        ],
        desserts: [
          {
            name: 'Frozen Yogurt',
            calories: 159,
            fat: 6.0,
            carbs: 24,
            protein: 4.0,
            iron: 1,
          },
         
        ],
      }
    },

    methods: {
      async getProducts( { page, itemsPerPage, sortBy } ){

        // console.log(page)

        let prevNext=(page>this.currentPage?'after':(page<this.currentPage?'before':'current') );

        this.currentPage=page;

        try {
          this.loading=true;
          let response = await axios.get('http://shopify-app.test/productos',{
            params: {
              cursor: this.cursor,
              prevNext: prevNext
            }
          });
          this.productos=response.data.data.products.edges;
          this.cursor=response.data.data.products.pageInfo.startCursor;
          // this.cursor=response.data.data.products.pageInfo.endCursor
          // this.paginacion=response.data.data.products.pageInfo;
          this.loading=false;

          // console.log( response.data.data.products.pageInfo )
          

        } catch (error) {
          console.error(error);
        }        

      }
    }


  }
</script>