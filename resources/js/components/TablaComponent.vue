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
    show-select
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

    <template v-slot:item.node.images.edges[0].node.src="{value}">
      
      <img class="pt-2" width="50px" :src="value" alt="">

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
        

        page: 1,
        expanded: [],
        productHeaders: [
          { title: 'IMG', key: 'node.images.edges[0].node.src' },
          {
            title: 'Producto',
            align: 'start',
            sortable: false,
            key: 'node.title',
          },
          { title: 'STATUS', key: 'node.status' },
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



    computed: {
      pageCount () {
        return Math.ceil(this.productos.length / 2000)
      },
    },    


    methods: {
      async getProducts( { page, itemsPerPage, sortBy } ){

        // console.log(page)

        let prevNext=(page>this.currentPage?'after':(page<this.currentPage?'before':'current') );

        this.currentPage=page;

        try {
          this.loading=true;
          let response = await axios.get('/productos',{
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

          console.log( this.productos )
          

        } catch (error) {
          console.error(error);
        }        

      }
    }


  }
</script>