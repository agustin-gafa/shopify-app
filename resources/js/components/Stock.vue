<template>
  <v-col cols="8" class="mx-auto">
    <v-card class="rounded-xl" color="white">
    <v-form fast-fail @submit.prevent="productoB2B" ref="formStock">          

      <template v-if="alerta.active">
        
        <v-alert          
          density="compact"                    
          :type="alerta.tipo"
          class="text-center"
        >
        <div class="text-h6 mb-1"> {{ msj }} </div>
        </v-alert>
      
      </template>

      <v-text-field
        variant="outlined"
        class="pa-3 mt-3"
        v-model="sku"
        :rules="skuRules"
        label="SKU variante"
      ></v-text-field>

      
      <v-btn variant="tonal" class="mx-auto" color="info" type="submit" block >SYNC STOCK</v-btn>    


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
        tipo: "info",
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
              this.alerta.tipo="success";


            } catch (error) {
              // console.log( error.response )
              this.msj=error.response.data.msj;
              this.alerta.tipo="warning";
            }
            
            this.alerta.active=true;
            this.alerta.overlay=false;
        }

      }

    }
  }
</script>