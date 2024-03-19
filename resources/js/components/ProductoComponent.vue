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

            <DataTable ref="dt" :value="productos" v-model="search" dataKey="id"
                :paginator="true" :rows="10" :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown" 
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Mostrar {first} de {last} de {totalRecords} productos" size='small' tableStyle="min-width: 50rem">
                <Column field="nombre" header="Nombre" sortable></Column>
                <Column field="descripcion" header="Descripcion"></Column>
                <Column field="modelo" header="Modelo"></Column>
                <Column field="marca.nombre" header="Marca"></Column>
                <Column field="precio" header="Precio">
                    <template #body="slotProps">
                        {{ formatCurrency(slotProps.data.precio) }}
                    </template>
                </Column>
                <Column field="stock" header="Stock"></Column>
                <Column field="categoria.nombre" header="Categoria"></Column>
                <Column :exportable="false">
                    <template #body="slotProps">
                        <Button icon="pi pi-images" outlined rounded class="mr-2" severity="info" @click="viewImages(slotProps.data)" />
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editProducto(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="deleteProducto(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
        <!-- FOrmulario modal para guardar o actualizar un producto -->
        <Dialog v-model:visible="productoDialog" :style="{ width: '550px' }" header="Registro de Productos" :modal="true" class="p-fluid">
            <div class="field">
                <label for="nombre">Nombre</label>
                <InputText id="nombre" v-model.trim="producto.nombre" required="true" autofocus :class="{'p-invalid': submitted && !producto.nombre }" />
                <small class="p-error" v-if="submitted && !producto.nombre">Nombre es requerdo.</small>
            </div>
            <br>
            <div class="field">
                <label for="descripcion">Descripción</label>
                <Textarea id="descripcion" v-model.trim="producto.descripcion" required="true" rows="2" cols="20"/>
                <small class="p-error" v-if="submitted && !producto.descripcion">Descripción es requerida.</small>
            </div>
            <br>
            <div class="formgrid grid row">
                <div class="field col">
                <label for="precio">Precio</label>
                <InputNumber id="precio" v-model="producto.precio" mode="currency" currency="USD" locale="en-US"
                :class="{'p-invalid': submitted && !producto.precio }" />
                <small class="p-error" v-if="submitted && !producto.precio">Precio es requerido.</small>
                </div>

                <div class="field col">
                <label for="stock">Stock</label>
                <InputNumber id="stock" v-model="producto.stock" integerOnly :class="{'p-invalid': submitted && !producto.stock }" />
                <small class="p-error" v-if="submitted && !producto.stock">Stock es requerdo.</small>
                </div>

                <div class="field">
                <label for="modelo">Modelo</label>
                <InputText id="modelo" v-model.trim="producto.modelo" />
                <small class="p-error" v-if="submitted && !producto.modelo">Nombre es requerdo.</small>
                </div>
            </div>
            <br>
            <div class="formgrid grid row">
                <div class="field col">
                    <Dropdown v-model="producto.marca_id" :options="marcas" optionLabel="nombre" optionValue="id" placeholder="Seleccion una marca" class="w-full md:w-14rem" />
                    <small class="p-error" v-if="submitted && !producto.marca_id">Seleccione una Marca.</small>
                </div>
                <br><br>
                <div class="field col">
                    <Dropdown v-model="producto.categoria_id" :options="categorias" optionLabel="nombre" optionValue="id" placeholder="Seleccion una categoria" class="w-full md:w-14rem" />
                    <small class="p-error" v-if="submitted && !producto.categoria_id">Seleccione una categoria.</small>
                </div>
            </div>
            <br>
            <div class="field">
                <label for="imagenes">Imágenes</label>
                <input type="file" class="form-control" multiple accept="image/* " id="fileI    nput" @change="getImages"/>
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Guardar" icon="pi pi-check" text @click="saveOrUpdate" />
            </template>
        </Dialog> <!-- fin del modal para guardar o actualizar producto-->

        <!-- modal para mostrar imagenes-->
        <Dialog v-model:visible="mostrarImagesDialog" :style="{ width: '550px' }" header="Imagenes de producto" :modal="true" class="p-fluid">
            <Carousel :value="imagenes" :numVisible="1" :numScroll="1" orientation="vertical" verticalViewPortHeight="330px" contentClass="flex align-items-center">
            <template #item="slotProps">
                <div class="relative mx-auto">
                    <img :src="'/images/productos/' + slotProps.data.nombre" :alt="slotProps.data.nombre" class="w-full border-round" />
                </div>
            </template>
            </Carousel>
        </Dialog>
    </div>
</template>

<script>
    import { ref, onMounted } from 'vue';
    import { FilterMatchMode } from 'primevue/api';
    import Dropdown from 'primevue/dropdown';
    export default {
        data() {
            return {
                productos:[],
                producto:{
                    id: null,
                    nombre:"",
                    descripcion:"",
                    precio:0,
                    stock:0,
                    estado:"",
                    modelo:"",
                    marca_id:null,
                    categoria_id:null
                },
                marcas:[],
                categorias: [],
                editedProducto: -1, //almacenar un indice de una categoria dentro del arreglo
                search: '',
                submitted: false,
                productoDialog: ref(false),
                mostrarImagesDialog: ref(false),
                imagenes:[]
            }
        },
        computed: {
            formTitle(){
                this.producto.id == null ? "Agregar Producto" : "Actualizar Producto";
            },
            btnTitle(){
                this.producto.id == null ? "Guardar" : "Actualizar";

            }
        },
        methods: {
            async fetchProductos() {
                await this.axios.get(`/api/productos`)
                .then(response => {
                    this.productos = response.data;
                })
                .catch(error => {
                    console.log(error);
                })
            },
            async fetchCategorias() {
                await this.axios.get(`/api/categorias`)
                .then(response => {
                    this.categorias = response.data;
                })
                .catch(error => {
                    console.log(error);
                })
            },
            async fetchMarcas() {
                await this.axios.get(`/api/marcas`)
                .then(response => {
                    this.marcas = response.data;
                })
                .catch(error => {
                    console.log(error);
                })
            },
            openNew() {
                this.producto = {};
                this.submitted = false;
                this.productoDialog= true;
            },

            hideDialog() {
                this.productoDialog = false;
                this.submitted = false;
            },

            //metodo para cargar el dialogo con la categoria a editar
            editProducto(product){
                this.producto = {...product};
                this.productoDialog = true;
                this.editedProducto = this.productos.indexOf(product);
            },

            createFormData(){
                this.producto.estado = 'D';
                let formData = new FormData();
                formData.append("nombre", this.producto.nombre);
                formData.append("descripcion", this.producto.descripcion);
                formData.append("precio", this.producto.precio);
                formData.append("stock", this.producto.stock);
                formData.append("estado", this.producto.estado);
                formData.append("modelo", this.producto.modelo);
                formData.append("marca_id", this.producto.marca_id);
                formData.append("categoria_id", this.producto.categoria_id);
                //verificando si hay imagenes asociadas al producto
                if (this.imagenes != null) {
                    for (let i = 0; i < this.imagenes.length; i++) {
                        formData.append("imagenes[]", this.imagenes[i]);
                    }
                }
                return formData;
            },
            getImages(event){
                let files = event.target.files;
                this.imagenes = files;
            },
            viewImages(producto){
                this.imagenes = producto.imagenes;
                this.mostrarImagesDialog = true
            },
            async saveOrUpdate(){
                let me = this;
                me.submitted = true;
                if (me.producto.nombre.trim()) {
                    let accion = me.producto.id == null ? "add" : "upd";
                    const headers = {
                        headers:{
                            'Content-Type': 'multipart/form-data'
                        }
                    }
                    if (accion == "add") {
                        //insertar producto
                        await this.axios.post(`/api/productos`, this.createFormData(), headers)
                        .then(response => {
                            if (response.status == 201) {
                                me.verificarAccion(response.data.data,response.status, accion, response.data.message);
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
                        //actualizar producto
                        await this.axios.put(`/api/productos/${me.producto.id}`, this.createFormData(), headers)
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
                    me.productoDialog = false;
                    me.producto = {};
                }
            },
            async deleteProducto(producto){
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
                            me.editedProducto = me.productos.indexOf(producto);
                            this.axios.delete(`/api/productos/${producto.id}`)
                                .then(response => {
                                    me.verificarAccion(null,response.status, "del", response.data.message);
                                }).catch(errors =>{
                                    console.log(errors);
                                })
                        }
                })
            },
            verificarAccion(producto, statusCode, accion, message){
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
                        me.productos.unshift(producto.original);
                        Toast.fire({
                            icon: 'success',
                            'title': message
                        });
                        break;
                    case "upd":
                        Object.assign(me.productos[me.editedProducto],producto);
                        Toast.fire({
                            icon: 'success',
                            'title': message
                        });
                        break;
                    case "del":
                        if (statusCode == 205) {
                            me.productos.splice(this.editedProducto, 1);
                            Toast.fire({
                                icon: 'success',
                                'title': 'Registro Eliminado ...!'
                            });
                        }else{
                            this.$swal.fire({
                                icon: 'error',
                                text: 'No se puede eliminar, ya existen registros de este producto'
                            });
                        }
                        break;
                 }
            }
        },
        mounted() {
            this.fetchMarcas();
            this.fetchCategorias();
            this.fetchProductos();
        }
    }
    const filters = ref({
        'global': {value: null, matchMode: FilterMatchMode.CONTAINS},
    });
    const formatCurrency = (value) => {
    if(value)
        return value.toLocaleString('en-US', {style: 'currency', currency: 'USD'});
    return;
};
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