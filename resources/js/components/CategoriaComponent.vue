<template>
    <div>
        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <Button label="Nuevo" icon="pi pi-plus" severity="success" class="mr-2" @click="openNew" />
                </template>
                <template #end>
                    <IconField iconPosition="left">
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="filters['global'].value" placeholder="Buscar..." />
                        </IconField>
                </template>
            </Toolbar>

            <DataTable ref="dt" :value="categorias" v-model="search" dataKey="id"
                :paginator="true" :rows="10" :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown" :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Mostrar {first} de {last} de {totalRecords} categorias" size='small' tableStyle="min-width: 50rem">
                <Column field="nombre" header="Categoria    " sortable></Column>
                <Column :exportable="false">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editCategoria(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="deleteCategoria(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="categoriaDialog" :style="{ width: '450px' }" header="Registro de Categorias" :modal="true" class="p-fluid">
            <div class="field">
                <label for="nombre">Nombre</label>
                <InputText id="nombre" v-model.trim="categoria.nombre" required="true" autofocus :class="{'p-invalid': submitted && !categoria.nombre }" />
                <small class="p-error" v-if="submitted && !categoria.nombre">Nombre es requerdo.</small>
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Guardar" icon="pi pi-check" text @click="saveOrUpdate" />
            </template>
        </Dialog>
    </div>
</template>

<script>
    import { ref, onMounted } from 'vue';
    import { FilterMatchMode } from 'primevue/api';
    export default {
        data() {
            return {
                categorias: [],
                categoria: {
                    id: null,
                    nombre: ""
                },
                editedCategoria: -1, //almacenar un indice de una categoria dentro del arreglo
                categoriaErrors: {
                    nombre: false
                },
                search: '',
                submitted: false,
                categoriaDialog: ref(false)
            }
        },
        computed: {
            formTitle(){
                this.categoria.id == null ? "Agregar Categoria" : "Actualizar Categoria";
            },
            btnTitle(){
                this.categoria.id == null ? "Guardar" : "Actualizar";

            }
        },
        methods: {
            async fetchCategorias() {
                await this.axios.get(`/api/categorias`)
                .then(response => {
                    this.categorias = response.data;
                })
                .catch(error => {
                    console.log(error);
                })
            },
            openNew() {
                this.categoria = {};
                this.submitted = false;
                this.categoriaDialog = true;
            },

            hideDialog() {
                this.categoriaDialog = false;
                this.submitted = false;
            },

            //metodo para cargar el dialogo con la categoria a editar
            editCategoria(cat){
                this.categoria = {...cat};
                this.categoriaDialog = true;
                this.editedCategoria = this.categorias.indexOf(cat);
            },

            async saveOrUpdate(){
                let me = this;
                me.submitted = true;
                if (me.categoria.nombre.trim()) {
                    let accion = me.categoria.id == null ? "add" : "upd";
                    if (accion == "add") {
                        //insertar categoria
                        await this.axios.post('/api/categorias', me.categoria)
                        .then(response => {
                            if (response.status == 201) {
                                me.verificarAccion(response.data.data,
                                response.status, accion, response.data.message);
                            }
                        })
                        .catch(errors => {
                            console.log(errors);
                            if (errors.response.status == 409) {
                                this.$swal.fire({
                                    title: 'Precaución...!',
                                    'text': errors.response.data.message,
                                    icon: 'warning'
                                });
                            }
                        })
                    }else{
                        //actualizar categoria
                        await this.axios.put(`/api/categorias/${me.categoria.id}`, me.categoria)
                        .then(response =>{
                            if (response.status == 202) {
                                me.verificarAccion(response.data.data, response.status, accion, response.data.message);
                            }
                        })
                        .catch(errors =>{
                            console.log(errors);
                            if (errors.response.status == 409) {
                                this.$swal.fire({
                                    title: "Precaución...!",
                                    text: errors.response.data.message,
                                    icon: "warning"
                                });
                            }
                        })
                    }
                    me.categoriaDialog = false;
                    me.categoria = {};
                }
            },
            async deleteCategoria(categoria){
                let me = this;
                this.$swal.fire({
                    title: 'Seguro/a de eliminar este registro?',
                    text: "No podrás revertir la acción",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Si',
                    cancelButtonText: 'No',
                    }).then((result) => {
                        if (result.value) {
                            me.editedCategoria = me.categorias.indexOf(categoria);
                            this.axios.delete(`/api/categorias/${categoria.id}`)
                                .then(response => {
                                    me.verificarAccion(null,response.status, "del", response.data.message);
                                }).catch(errors =>{
                                    console.log(errors);
                                })
                        }
                })
            },
            verificarAccion(categoria, statusCode, accion, message){
                let me = this;
                const Toast = this.$swal.mixin({
                    toast: true,
                    position: 'top-right',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
                 switch (accion) {
                    case "add":
                        //agregamos al principio del arreglo marcas, la nueva marca
                        me.categorias.unshift(categoria);
                        Toast.fire({
                            icon: 'success',
                            'title': message
                        });
                        break;
                    case "upd":
                        Object.assign(me.categorias[me.editedCategoria],categoria);
                        Toast.fire({
                            icon: 'success',
                            'title': message
                        });
                        break;
                    case "del":
                        if (statusCode == 205) {
                            me.categorias.splice(this.editedCategoria, 1);
                            Toast.fire({
                                icon: 'success',
                                'title': 'Registro Eliminado ...!'
                            });
                        }else{
                            this.$swal.fire({
                                icon: 'error',
                                text: 'No se puede eliminar, ya existen autos registrados de esta categoria'
                            });
                        }
                        break;
                 }
            }
        },
        mounted() {
            this.fetchCategorias();
        }
    }
    const filters = ref({
        'global': {value: null, matchMode: FilterMatchMode.CONTAINS},
    });
</script>

<script setup>
// import { ref, onMounted } from 'vue';
// import { FilterMatchMode } from 'primevue/api';
// import { useToast } from 'primevue/usetoast';
// import { ProductService } from '@/service/ProductService';

// onMounted(() => {
//     ProductService.getProducts().then((data) => (products.value = data));
// });

// const toast = useToast();
// const dt = ref();
// const products = ref();
// const productDialog = ref(false);
// const deleteProductDialog = ref(false);
// const deleteProductsDialog = ref(false);
// const product = ref({});
// const selectedProducts = ref();
// const filters = ref({
//     'global': {value: null, matchMode: FilterMatchMode.CONTAINS},
// });
// const submitted = ref(false);
// const statuses = ref([
//     {label: 'INSTOCK', value: 'instock'},
//     {label: 'LOWSTOCK', value: 'lowstock'},
//     {label: 'OUTOFSTOCK', value: 'outofstock'}
// ]);

// const formatCurrency = (value) => {
//     if(value)
//         return value.toLocaleString('en-US', {style: 'currency', currency: 'USD'});
//     return;
// };
// const openNew = () => {
//     product.value = {};
//     submitted.value = false;
//     productDialog.value = true;
// };
// const hideDialog = () => {
//     productDialog.value = false;
//     submitted.value = false;
// };
// const saveProduct = () => {
//     submitted.value = true;

//     if (product.value.name.trim()) {
//         if (product.value.id) {
//             product.value.inventoryStatus = product.value.inventoryStatus.value ? product.value.inventoryStatus.value : product.value.inventoryStatus;
//             products.value[findIndexById(product.value.id)] = product.value;
//             toast.add({severity:'success', summary: 'Successful', detail: 'Product Updated', life: 3000});
//         }
//         else {
//             product.value.id = createId();
//             product.value.code = createId();
//             product.value.image = 'product-placeholder.svg';
//             product.value.inventoryStatus = product.value.inventoryStatus ? product.value.inventoryStatus.value : 'INSTOCK';
//             products.value.push(product.value);
//             toast.add({severity:'success', summary: 'Successful', detail: 'Product Created', life: 3000});
//         }

//         productDialog.value = false;
//         product.value = {};
//     }
// };
// const editProduct = (prod) => {
//     product.value = {...prod};
//     productDialog.value = true;
// };
// const confirmDeleteProduct = (prod) => {
//     product.value = prod;
//     deleteProductDialog.value = true;
// };
// const deleteProduct = () => {
//     products.value = products.value.filter(val => val.id !== product.value.id);
//     deleteProductDialog.value = false;
//     product.value = {};
//     toast.add({severity:'success', summary: 'Successful', detail: 'Product Deleted', life: 3000});
// };
// const findIndexById = (id) => {
//     let index = -1;
//     for (let i = 0; i < products.value.length; i++) {
//         if (products.value[i].id === id) {
//             index = i;
//             break;
//         }
//     }

//     return index;
// };
// const createId = () => {
//     let id = '';
//     var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
//     for ( var i = 0; i < 5; i++ ) {
//         id += chars.charAt(Math.floor(Math.random() * chars.length));
//     }
//     return id;
// }
// const exportCSV = () => {
//     dt.value.exportCSV();
// };
// const confirmDeleteSelected = () => {
//     deleteProductsDialog.value = true;
// };
// const deleteSelectedProducts = () => {
//     products.value = products.value.filter(val => !selectedProducts.value.includes(val));
//     deleteProductsDialog.value = false;
//     selectedProducts.value = null;
//     toast.add({severity:'success', summary: 'Successful', detail: 'Products Deleted', life: 3000});
// };

// const getStatusLabel = (status) => {
//     switch (status) {
//         case 'INSTOCK':
//             return 'success';

//         case 'LOWSTOCK':
//             return 'warning';

//         case 'OUTOFSTOCK':
//             return 'danger';

//         default:
//             return null;
//     }
// };
</script>