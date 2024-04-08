<template>
    <v-col cols="12" md="7" class="mx-auto mb-2">
        <v-card class="rounded elevation-5">
            <v-form fast-fail @submit.prevent="productoB2B" ref="formProduct">
                <v-card-title class="text-center">
                    <v-icon
                        icon="mdi mdi-archive-plus"
                        :color="alerta.tipo"
                    ></v-icon>
                    <v-chip variant="text" size="x-large" :color="alerta.tipo">
                        {{ msj }}
                    </v-chip>
                </v-card-title>

                <v-text-field
                    variant="outlined"
                    class="px-3 pb-2"
                    v-model="producto"
                    :rules="productoRules"
                    label="Producto"
                    :color="alerta.tipo"
                ></v-text-field>

                <template v-if="showTable">
                    <v-container class="pa-0">
                        <v-row justify="center" class="text-caption text-center">
                            <v-col cols="1">
                                <img
                                    height="40px"
                                    :src="respuesta.product.image.src"
                                    alt=""
                                />
                            </v-col>
                            <v-col cols="2">
                                {{ respuesta.product.title }}
                            </v-col>
                            <v-col cols="2">
                                <v-chip
                                    class="mt-1"
                                    size="small"
                                    variant="flat"
                                    :color="
                                        respuesta.product.status == 'active'
                                            ? 'light-green-darken-1'
                                            : 'secondary'
                                    "
                                    >{{ respuesta.product.status }}</v-chip
                                >
                            </v-col>
                            <v-col class="mt-2" cols="2">
                                {{
                                    respuesta.product.variants.length
                                }}
                                variantes
                            </v-col>
                        </v-row>
                    </v-container>
                </template>

                <v-btn
                    variant="tonal"
                    class="mx-auto"
                    color="purple"
                    type="submit"
                    block
                    >ENVIAR A B2B</v-btn
                >
            </v-form>
        </v-card>
    </v-col>

    <v-overlay
        :model-value="alerta.overlay"
        class="align-center justify-center"
    >
        <v-progress-circular
            color="black"
            size="64"
            indeterminate
        ></v-progress-circular>
    </v-overlay>
</template>

<script>
export default {
    props: {
        token: String,
    },
    data: () => ({
        msj: "Crear Producto en B2B",
        alerta: {
            active: true,
            tipo: "grey",
            overlay: false,
        },
        respuesta: [],
        showTable: false,
        producto: "",
        productoRules: [
            (value) => {
                if (value?.length > 3) return true;

                return "El nombre del producto debe tener al menos 3 letras";
            },
        ],
    }),
    methods: {
        async productoB2B() {
            const { valid } = await this.$refs.formProduct.validate();

            if (valid) {
                this.alerta.overlay = true;
                this.showTable = false;

                try {
                    let response = await axios.post("/crear-producto", {
                        _token: this.token,
                        producto: this.producto,
                    });

                    // console.log( response );

                    this.respuesta = response.data.response;
                    this.showTable = true;

                    this.msj = response.data.msj;
                    this.alerta.tipo = response.data.tipo;
                } catch (error) {
                    // console.log( error.response )
                    this.msj = error.response.data.msj;
                    this.alerta.tipo = error.response.data.tipo;
                }

                this.alerta.active = true;
                this.alerta.overlay = false;
            }
        },
    },
};
</script>
