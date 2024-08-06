<template>
    <v-app id="inspire">
  
      <v-main>
        <v-container class="fill-height" fluid>
          <v-row align="center" justify="center">
            
            
            <v-col cols="4" class="mx-auto" >
                <v-card class="pa-10"
                prepend-icon="mdi-login"
                title="ADAMINA">
                <v-form method="POST" action="/login" >
                    <slot></slot>

                    <v-text-field
                        type="email"
                        name="email"
                        label="Correo electronico"
                    ></v-text-field>

                    <v-text-field
                        type="password"
                        name="password"
                        label="Contraseña"
                    ></v-text-field>

                    <v-btn        
                        class="mt-2"
                        text="Acceder"
                        type="submit"
                        block
                    ></v-btn>
                </v-form>

            </v-card>

            </v-col>


          </v-row>
        </v-container>
      </v-main>
    </v-app>
  </template>
  
  