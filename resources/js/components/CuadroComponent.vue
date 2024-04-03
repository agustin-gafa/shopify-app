<template>
  <v-app id="inspire">

    <menu-component :marca="marca" :username="username">
      <slot name="token"></slot>
    </menu-component>

    <v-main class="bg-rkn">
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

<style scoped>
  .bg-rkn{background:#f1f1f1;}
</style>