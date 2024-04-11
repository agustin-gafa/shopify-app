<template>
  <v-app id="inspire">

    <menu-component :marca="marca" :username="username">
      <slot name="token"></slot>
    </menu-component>

    <v-main class="">     
      <v-container>
        <v-row>
            <!--  -->

           <slot name="contenido"></slot>           
                     


        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
  export default {
    props: {
      marca: String,
      username: String      
    }
  }
</script>