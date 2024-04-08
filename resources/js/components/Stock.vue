<template>
  <v-col cols="12" md="7" class="mx-auto mt-2">
    <v-card class="rounded elevation-5">
    <v-form fast-fail @submit.prevent="productoB2B" ref="formStock">          

      <v-card-title class="text-center">
        <v-icon icon="mdi mdi-calculator-variant" :color="alerta.tipo"></v-icon>
        <v-chip variant="text" size="x-large" :color="alerta.tipo">
          {{ msj }}      
        </v-chip>

      </v-card-title>

      <v-text-field
        variant="outlined"
        class="px-3 pb-2"
        v-model="sku"
        :rules="skuRules"
        label="SKU variante"
        :color="alerta.tipo"
      ></v-text-field>

      
      <v-btn variant="tonal" class="mx-auto" color="success" type="submit" block >SYNC STOCK</v-btn>    


    </v-form>
  </v-card>
  </v-col>

    <v-overlay
      :model-value="alerta.overlay"
      class="align-center justify-center"
    >
      <v-progress-circular
        color="primary"
        size="64"
        indeterminate
      ></v-progress-circular>
    </v-overlay>

</template>

<script>
  export default {
    props: {
      token: String      
    },    
    data: () => ({
      msj:'Verificar Stock',
      alerta: {
        active:true,
        tipo: "grey",
        overlay: false
      },
      sku: '',
      skuRules: [
        value => {
          if (value?.length > 3) return true

          return 'El SKU debe tener al menos 3 letras'
        },
      ],
    }),
    methods: {

      async productoB2B(){

        const { valid } = await this.$refs.formStock.validate();

        if(valid){

            this.alerta.overlay=true

            try {
              
              let response = await axios.post('/stock',{            
                _token:document.querySelector('meta[name="csrf-token"]').getAttribute('content'),//this.token,  
                sku: this.sku,                          
              });

              // console.log( response );

              this.msj=response.data.msj;          
              this.alerta.tipo=response.data.tipo;


            } catch (error) {
              // console.log( error.response )
              this.msj=error.response.data.msj;
              this.alerta.tipo=error.response.data.tipo;
            }
            
            this.alerta.active=true;
            this.alerta.overlay=false;
        }

      }

    }
  }
</script>