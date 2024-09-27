<template>
    <div>
        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <Button label="Nuevo" icon="pi pi-plus" severity="contrast" class="mr-2" @click="openNew" />
                </template>
                    <template #center>
                        <a href="/reportes/productos" target="_blank" class="btn btn-primary">Exportar a PDF</a>
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
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown" :rowsPerPageOptions="[2,10,25]"
                currentPageReportTemplate="Mostrar {first} de {last} de {totalRecords} productos" size="small" tableStyle="min-width:50rem">

                <Column field="nombre" header="Nombre" sortable ></Column>              
                <Column field="descripcion" header="Descripcion"  ></Column>            
                <Column field="marca.nombre" header="Marca"  ></Column>                     
                <Column field="precio" header="Precio" sortable></Column>       
                <Column field="categoria.nombre" header="Categoria" sortable></Column>    
                <Column :exportable="false">
                    <template #body="slotProps">
                        <Button icon="pi pi-image" outlined rounded class="mr-2" severity="info" @click="viewImage(slotProps.data)" />
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editProducto(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="deleteProducto(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Modal para guardar o actualizar un producto -->
        <Dialog v-model:visible="productoDialog" :style="{width: '550px'}" header="Registrar productos" :modal="true" class="p-fluid">
            <div class="field">
                <label for="nombre">Nombre</label>
                <InputText id="nombre" v-model="producto.nombre" required="true" autofocus :class="{'p-invalid': submitted && !producto.nombre}" />
                <small class="p-error" v-if="submitted && !producto.nombre">Nombre es requerido.</small>
            </div>
            <div class="field">
                <label for="descripcion">Descripción</label>
                <Textarea id="descripcion" v-model="producto.descripcion" required="true" rows="2" cols="20" />
                <small class="p-error" v-if="submitted && !producto.descripcion">Descripción es requerida.</small>
            </div>
            <div class="formgrid grid row">
                <div class="field col">
                    <label for="precio">Precio</label>
                    <InputNumber id="precio" v-model="producto.precio" mode="currency" currency="USD" locale="en-US":class="{'p-invalid': submitted && !producto.precio}" />
                    <small class="p-error" v-if="submitted && !producto.precio">Precio es requerido.</small>
                </div>
            </div>
            <div class="formgrid grid row">
                <div class="field col">
                    <label for="marca">Menú</label>
                    <div class="fiel col">
                        <Dropdown2 v-model="producto.marca_id" :options="marcas" optionLabel="nombre" optionValue="id" placeholder="Selecione un menú" class="w-full md:w-14rem" />
                        <small class="p-error" v-if="submitted && !producto.marca_id">Menú es requerido.</small>
                    </div>
                </div>
                <div class="field col">
                    <label for="Categorias">Categorías</label>
                    <div class="card flex justify-content-center">
                        <Dropdown2 v-model="producto.categoria_id" :options="categorias" optionLabel="nombre" optionValue="id" placeholder="Selecione una categoría" class="w-full md:w-14rem" />
                        <small class="p-error" v-if="submitted && !producto.categoria_id">Categoria es requerida.</small>
                    </div>
                </div>
                <div class="field">
                    <label for="imagenes">Imágenes</label>
                    <input type="file" class="form-control" multiple accept="image/*" @change="getImages"/>
                </div>
            </div>
            <template #footer>
                <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog"/>
                <Button label="Guardar" icon="pi pi-check" text @click="saveOrUpdate" />
            </template>
        </Dialog>
	</div>
</template>

<script>
    import { ref, onMounted } from 'vue';
    import { FilterMatchMode } from 'primevue/api';
    import { useToast } from 'primevue/usetoast';
    export default {
        data(){
            return{
                // Variables para gestionar la lógica
                productos:[],
                producto: {
                    id: null,
                    nombre: '',
                    descripcion:'',
                    precio: 0,
                    stock: 0,
                    estado: '',
                    modelo: '',
                    marca_id: null,
                    categoria_id: null,
                 
                },
                marcas:[],
                caetogorias: [],
                editedProducto: -1,
                search: '',
                submitted: false,
                productoDialog: ref(false),
                mostrarImageDialog:ref(false),
                imagenes: []
            }
        },

        computed:{
            formTitle(){
                return this.producto.id == null ? 'Agregar producto' : 'Actualizar producto';
            },
            btnTitle(){
               return this.producto.id == null ? 'Guardar' : 'Actualizar';
            }
        },

        methods: {
            async fetchCategorias(){
                await this.axios.get(`/api/categorias`)
                .then(response => {
                    //console.log(response.data);
                    this.categorias = response.data;
                }).catch(error => {
                    console.log(error);
                })
            },
            async fetchProductos(){
                await this.axios.get(`/api/productos`)
                .then(response => {
                    console.log(response.data);
                    this.productos = response.data;
                }).catch(error => {
                    console.log(error);
                })
            },

            async fetchMarcas(){
                await this.axios.get(`/api/marcas`)
                .then(response => {
                    this.marcas = response.data;
                }).catch(error => {
                    console.log(error);
                })
            },

            openNew(){
                this.producto = {};
                this.submitted = false;
                this.productoDialog = true;
            },

            hideDialog(){
                this.productoDialog = false;
                this.submitted = false;
            },

            editProducto(producto){
                this.producto = {...producto};
                this.productoDialog = true;
                this.editedProducto = this.productos.findIndex(producto => producto.id === cat.id);
            },
            createFormData(){
                this.producto.estado = 'D';
                let formData = new FormData();
                formData.append("nombre", this.producto.nombre);
                formData.append("descripcion",this.producto.descripcion);
                formData.append("precio",this.producto.precio);
                formData.append("marca_id",this.producto.marca_id);
                formData.append("categoria_id",this.producto.categoria_id);

                //verificando si hay imagenes dentro del producto

                
                if(this.imagenes != null){
                    for(let i=0; i<this.imagenes.length; i++){
                        formData.append("imagenes[]", this.imagenes[i]);
                    }
                }
                return formData;
            },
            getImages(event){
                let files = event.target.files;
                this.imagenes = files;
            },
            // Metodo para guardar o actualizar un producto
            async saveOrUpdate() {
                let me = this;
                me.submitted = true;
                if (me.producto.nombre.trim()) {
                    let accion = me.producto.id == null ? 'add' : 'upd';
                    const headers = {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                };
                if (accion == 'add') {
                    //Peticion para guardar una categoria
                    await this.axios.post(`/api/productos`, me.createFormData(), headers)
                    .then(response => {
                        console.log("REGRESO")
                        console.log(response.data)
                        if (response.status == 201) {
                            me.verificarAccion(response.data.data, response.status, accion, response.data.message);
                        }
                }).catch(error => {
                 console.log(error);
                if (error.response.status == 409) {
                    this.$swal.fire({
                    title: 'Precaución...',
                    text: error.response.data.message,
                    icon: 'warning'
                });
          }
        });
    } else {
      //Peticion para actualizar un producto
      await this.axios.put(`/api/productos/${me.producto.id}`, this.producto)
        .then(response => {
          if (response.status == 201) {
            me.verificarAccion(response.data.data, response.status, accion, response.data.message);
          }
        }).catch(errors => {
            this.$swal.fire({
              title: 'Precaución...',
              text: errors,
              icon: 'warning'
            });
        });
    }
    me.productoDialog = false;
    me.producto = {};
  }
}, //////////////////////////////////////////////////////////////

            async deleteProducto(producto){
                let me = this;
                this.$swal.fire({
                    title: '¿Seguro/a de eliminar este registro?',
                    text: 'No podrás revertir la acción!!',
                    icon: 'question',
                    showCancelButton:true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Si',
                    cancelButtonText: 'No',
                }).then((result) => {
                    if (result.value) {
                        me.editProducto = me.producto.indexOf(producto);
                        this.axios.delete(`/api/producto/${producto.id}`)
                            .then(response =>{
                                me.verificarAccion(null, response.status,"del", response.data.message);
                            }).catch(errors => {
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
                    case 'add':
                        // Agregamos al principio del arreglo productos el nuevo producto
                        me.productos.unshift(producto);
                        Toast.fire({
                            icon: 'success',
                            'title': message
                        });
                        break;
                    case 'upd': //el assing sirve para mover la posicion del nuevo dato (actualizar)
                        Object.assign(me.productos[me.editProducto], producto);
                        Toast.fire({
                            icon: 'success',
                            'title': message
                        });
                        break;
                    case 'del':
                        if (statusCode == 205) {//el splice  hace que se quite el 
                            me.productos.splice(me.editProducto, 1);
                            Toast.fire({
                                icon: 'success',
                                'title': 'Categoria eliminada...!'
                            });
                        } else {
                            this.$swal.fire({
                                icon: 'error',
                                text: 'No se puede eliminar, ya existen ordenes registradas de este producto'
                            });
                        }
                        break;
                }
            }
        },
        mounted() {
            this.fetchProductos();
            this.fetchMarcas();
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