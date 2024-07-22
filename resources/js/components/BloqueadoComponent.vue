<template>
    <v-container>
        <v-row>
            <v-col cols="12" md="6">
                <v-sheet class="mx-auto">
                    <v-form fast-fail @submit.prevent>
                        <v-text-field
                            v-model="firstName"
                            :rules="firstNameRules"
                            label="Producto"
                        ></v-text-field>

                        <v-btn class="mt-2" type="submit" block>Submit</v-btn>
                    </v-form>
                </v-sheet>
            </v-col>

            <v-col cols="12" md="6"> </v-col>
        </v-row>
    </v-container>
</template>

<script>
export default {
    data: () => ({
        firstName: "",
        firstNameRules: [
            (value) => {
                if (value?.length > 3) return true;

                return "First name must be at least 3 characters.";
            },
        ],
        lastName: "123",
        lastNameRules: [
            (value) => {
                if (/[^0-9]/.test(value)) return true;

                return "Last name can not contain digits.";
            },
        ],
    }),
};
</script>
