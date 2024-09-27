<template>
    <div>
        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <!--Colocar radio button para seleccionar por estado de cliente-->
                    <!--<div class="form-check form-check-inline">
                    <input class="form-check-input" v-model="estado" type="radio" name="orden.estado" id="inlineRadio1" value="R" checked>
                    <label class="form-check-label" for="inlineRadio1">Ordenes Recibidas</label>
                    </div>
                    <div class="form-check form-check-inline">
                    <input class="form-check-input" v-model="estado" type="radio" name="orden.estado" id="inlineRadio2" value="D">
                    <label class="form-check-label" for="inlineRadio2">Ordenes Disponibles</label>
                    </div>
                    <div class="form-check form-check-inline">
                    <input class="form-check-input" v-model="estado" type="radio" name="orden.estado" id="inlineRadio3" value="A">
                    <label class="form-check-label" for="inlineRadio3">Ordenes Anuladas</label>
                    </div>-->
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

            <DataTable ref="dt" :value="filteredOrdenes" v-model="search" dataKey="id"
                :paginator="true" :rows="10" :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown" 
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Mostrar {first} de {last} de {totalRecords} ordenes" size='small' tableStyle="min-width: 50rem">
                <Column field="correlativo" header="Orden No" sortable></Column>
                <Column field="fecha" header="Fecha Orden"></Column>
                <Column field="fecha_despacho" header="Fecha Despacho"></Column>
                <Column field="monto" header="Estado">
                    <template #body="slotProps">
                        {{ formatCurrency(slotProps.data.monto) }}
                    </template>
                </Column>
                <Column :exportable="false">
                    <template #body="slotProps">
                        <Button icon="pi pi-list" outlined rounded class="mr-2" severity="info" @click="viewDetalle(slotProps.data)" v-tooltip="{ value: 'Ver Detalle', showDelay: 100, hideDelay: 300 }" />
                        <Button icon="pi pi-check" outlined rounded class="mr-2" @click="changeOrden(slotProps.data, 'D')" v-if="slotProps.data.estado == 'R'" v-tooltip="{ value: 'Despachar Orden', showDelay: 100, hideDelay: 300 }" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger" @click="changeOrden(slotProps.data, 'A')" v-if="slotProps.data.estado == 'R'" v-tooltip="{ value: 'Anular Orden', showDelay: 100, hideDelay: 300 }" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- modal para mostrar detalle de la orden-->
        <Dialog v-model:visible="mostrarDetalleDialog" :style="{ width: '550px' }" header="Imagenes de producto" :modal="true" class="p-fluid">
            <div class="formgrid grid row">
                <div class="field col">
                <label>Orden: {{ orden.correlativo }}</label>
                </div>
                <div class="field col">
                <label>Fecha: {{ orden.fecha }}</label>
                </div>
            </div>
            <div class="field">
                <label>Cliente: {{ orden.cliente.name }}</label>
            </div>
            <div class="row">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Marcas</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>SubTotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in orden.detalleOrdenes" :key="item.id">
                            <td>{{ item.producto.nombre }}, {{ item.producto.descripcion }}, Modelo: {{ item.producto.modelo }}</td>
                            <td>{{ item.producto.marca.nombre }}</td>
                            <td>{{ item.cantidad }}</td>
                            <td>${{ item.precio }}</td>
                            <td>${{ item.cantidad * item.precio }}</td>
                        </tr>
                        <tr>
                            <td colspan="4">Total Orden</td>
                            <td>${{ orden.monto }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <template #footer>
                <Button label="Cancel" icon="pi pi-times" text @click="hideDialog" />
            </template>
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
                ordenes:[],
                orden: null,
                editedOrden: -1,
                search: '',
                submitted: false,
                mostrarDetalleDialog: ref(false),
                estado:'R'
            }
        },
        computed: {
            filteredOrdenes(){
                if (this.estado === 'R') {
                    return this.ordenes.filter(orden => orden.estado === 'R'); //Retirados
                }else if (this.estado === 'D') {
                    return this.ordenes.filter(orden => orden.estado === 'D'); //Disponibles
                }else if (this.estado === 'A') {
                    return this.ordenes.filter(orden => orden.estado === 'A'); //Anulados
                }
                return this.estado;
            }
        },
        methods: {
            async fetchOrdenes() {
                await this.axios.get(`/api/ordenes`)
                .then(response => {
                    console.log(response.data)
                    this.ordenes = response.data;
                })
                .catch(error => {
                    console.log(error);
                })
            },
            viewDetalle(orden){
                this.orden = {...orden};
                this.mostrarDetalleDialog = true;
            },
            changeOrden(orden, estado){
                let textEstado = estado == 'D' ? 'Despachar' : 'Anular';
                this.$swal.fire({
                    title: `Seguro/a de ${textEstado} la orden No: ${orden.correlativo}?`,
                    text: "No podrás revertir la acción",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Si',
                    cancelButtonText: 'No',
                    }).then((result) => {
                        if (result.value) {
                            //me.editedProducto = me.productos.indexOf(producto);
                            //definiendo constante para crear msj tipo toast
                            const Toast = this.$swal.mixin({
                                toast: true,
                                position: 'top-right',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true
                            });

                            this.orden = {...orden};
                            this.orden.estado = estado;
                            this.editedOrden = this.ordenes.indexOf(orden);
                            this.axios.put(`/api/ordenes/${orden.id}`, this.orden)
                                .then(response => {
                                    //me.verificarAccion(null,response.status, "del", response.data.message);
                                    let estadoText = response.data.data.estado == 'D' ? "Despachado" : "Anulada"
                                    if (response.status == 202) {
                                        Object.assign(this.ordenes[this.editedOrden],response.data.data);
                                        Toast.fire({
                                            icon: 'success',
                                            'title': `responde.data.message a: ${estadoText}`
                                        });
                                    }
                                }).catch(errors =>{
                                    console.log(errors);
                                })
                        }
                })
            },
            hideDialog() {
                this.mostrarDetalleDialog = false;
                this.submitted = false;
            },
            getImages(event){
                let files = event.target.files;
                this.imagenes = files;
            },
        },
        mounted() {
            this.fetchOrdenes();
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