<template>
    <v-app-bar 
      scroll-behavior="fade-image collapse"

    >
      <v-app-bar-nav-icon @click="drawer = !drawer">
        <v-icon icon="mdi mdi-menu"></v-icon>
      </v-app-bar-nav-icon>

      <v-app-bar-title>{{marca}}</v-app-bar-title>


      
  <div class="text-center">
    <v-menu
      v-model="menu"
      :close-on-content-click="false"      
    >
      <template v-slot:activator="{ props }">
        <v-btn
          variant="outlined"
          class="mr-5"
          color="purple"
          v-bind="props"
          icon="mdi mdi-account-circle"
        >          
        </v-btn>
      </template>

      <v-card min-width="300">
        <v-list>
          <v-list-item            
            subtitle="Admin"
            :title="username"          
          >

          <template v-slot:prepend>
        <v-avatar color="surface-light" size="32">🎯</v-avatar>
      </template>

            <template v-slot:append>
              <v-btn
                :class="fav ? 'text-red' : ''"
                icon="mdi-heart"
                variant="text"
                @click="fav = !fav"
              ></v-btn>
            </template>
          </v-list-item>
        </v-list>

        <v-divider></v-divider>

<!--
        <v-list>
          <v-list-item>
            <v-switch
              v-model="message"
              color="purple"
              label="AA EE II OO UU"
              hide-details
            ></v-switch>
          </v-list-item>

          <v-list-item>
            <v-switch
              v-model="hints"
              color="purple"
              label="11 22 33 44 55"
              hide-details
            ></v-switch>
          </v-list-item>
        </v-list>
        -->

        <v-card-actions>
          <v-spacer></v-spacer>


          <v-btn
            variant="outlined"
            color="primary"            
            @click="menu = false"            
            onclick="event.preventDefault();
                    document.getElementById('logout-form').submit();"
            >                                      
            CERRAR SESION

            <form id="logout-form" action="/logout" method="POST" class="d-none">              
              <slot></slot>
            </form>

          </v-btn>
        </v-card-actions>
      </v-card>
    </v-menu>
  </div>

      

    </v-app-bar>

    <v-navigation-drawer
      v-model="drawer"
      temporary      
      rail   
    >
    
    <v-list density="compact" nav>
          <a href="/"><v-list-item prepend-icon="mdi-folder" title="My Files" value="myfiles"></v-list-item></a>
          <a href="#"><v-list-item prepend-icon="mdi-account-multiple" title="Shared with me" value="shared"></v-list-item></a>
          <v-list-item prepend-icon="mdi-star" title="Starred" value="starred"></v-list-item>
    </v-list> 


    </v-navigation-drawer>
</template>

<script setup>
  import { ref } from 'vue'

  const drawer = ref(null)
</script>

<script>
  export default {
    props: {
      username: String,
      marca: String
    },
    data: () => ({ drawer: null,
      fav: true,
      menu: false,
      message: false,
      hints: true, }),
  }
</script>
