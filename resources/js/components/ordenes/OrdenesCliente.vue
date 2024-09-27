<template>
    <div class="card">
        <DataView :value="productos" :layout="layout" paginator :rows="2">
            <template #header>
                <div class="flex justify-content-end">
                    <Button label="Ver Orden" link @click="viewOrden" v-if="orden.detalleOrden.length > 0"/>
                    <DataViewLayoutOptions v-model="layout" />
                </div>
            </template>

            <template #list="slotProps">
                <div class="grid grid-nogutter">
                    <div v-for="(item, index) in slotProps.items" :key="index" class="col-12">
                        <div class="flex flex-column sm:flex-row sm:align-items-center p-4 gap-3" :class="{ 'border-top-1 surface-border': index !== 0 }">
                            <div class="md:w-10rem relative">
                                <!--<img class="block xl:block mx-auto border-round w-full" :src="`https://primefaces.org/cdn/primevue/images/product/${item.image}`" :alt="item.name" />-->
                                <img class="block xl:block mx-auto border-round w-full" src="images/productos/samsungA34.jpeg" :alt="item.name" />
                                <Tag :value="item.stock" class="absolute" style="left: 4px; top: 4px"></Tag>
                            </div>
                            <Carousel :value="item.imagenes" :numVisible="1" :numScroll="1" orientation="vertical" verticalViewPortHeight="330px" contentClass="flex align-items-center">
                <template #item="slotProps">
                    <div class="border-1 surface-border border-round m-2  p-3">
                        <div class="mb-3">
                            <div class="relative mx-auto">
                                <img :src="'/images/productos/' + slotProps.data.nombre" :alt="slotProps.data.nombre" class="w-full border-round" />
                                <Tag :value="slotProps.data.stock" class="absolute" style="left:5px; top: 5px"/>
                            </div>
                        </div>
                    </div>
                </template>
            </Carousel>
                            <div class="flex flex-column md:flex-row justify-content-between md:align-items-center flex-1 gap-4">
                                <div class="flex flex-row md:flex-column justify-content-between align-items-start gap-2">
                                    <div>
                                        <span class="font-medium text-secondary text-sm">{{ item.categoria.nombre }}</span>
                                        <div class="text-lg font-medium text-900 mt-2">{{ item.nombre }}</div>
                                    </div>
                                    <div class="surface-100 p-1" style="border-radius: 30px">
                                        <div class="surface-0 flex align-items-center gap-2 justify-content-center py-1 px-2" style="border-radius: 30px; box-shadow: 0px 1px 2px 0px rgba(0, 0, 0, 0.04), 0px 1px 2px 0px rgba(0, 0, 0, 0.06)">
                                            <!--Muestra la informacion del producto-->
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-column md:align-items-end gap-5">
                                    <span class="text-xl font-semibold text-900">${{ item.precio }}</span>
                                    <div class="flex flex-row-reverse md:flex-row gap-2">
                                        <div v-if="user !=null">
                                            <!--Selecciona la cantidad-->
                                            <InputNumber v-model="item.cantidad" inputId="horizontal-buttons" showButtons buttonLayout="horizontal" :step="1" :min="1">
                                                <template #incrementbuttonicon>
                                                    <span class="pi pi-plus" />
                                                </template>
                                                <template #decrementbuttonicon>
                                                    <span class="pi pi-minus" />
                                                </template>
                                            </InputNumber>
                                        </div>
                                        <Button icon="pi pi-shopping-cart" label="Agregar a Orden" :disabled="item.stock === 0" class="flex-auto md:flex-initial white-space-nowrap" @click="addToOrden(item)"></Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template #grid="slotProps">
                <div class="grid grid-nogutter">
                    <div v-for="(item, index) in slotProps.items" :key="index" class="col-12 sm:col-6 md:col-4 xl:col-6 p-2">
                        <div class="p-4 border-1 surface-border surface-card border-round flex flex-column">
                            <div class="surface-50 flex justify-content-center border-round p-3">
                                <div class="relative mx-auto">
                                    <!--<img class="border-round w-full" :src="`https://primefaces.org/cdn/primevue/images/product/${item.image}`" :alt="item.name" style="max-width: 300px"/>-->
                                    <img class="block xl:block mx-auto border-round w-full" src="images/productos/samsungA34.jpeg" :alt="item.name" />
                                    <Carousel :value="item.imagenes" :numVisible="1" :numScroll="1" orientation="vertical" verticalViewPortHeight="330px" contentClass="flex align-items-center">
                                        <template #item="slotProps">
                                        <div class="border-1 surface-border border-round m-2  p-3">
                                            <div class="mb-3">
                                            <div class="relative mx-auto">
                                         <img :src="'/images/productos/' + slotProps.data.nombre" :alt="slotProps.data.nombre" class="w-full border-round" />
                                         <Tag :value="slotProps.data.stock" class="absolute" style="left:5px; top: 5px"/>
                                            </div>
                                            </div>
                                        </div>
                                         </template>
                                      </Carousel>
                                    <Tag :value="item.stock" class="absolute" style="left: 4px; top: 4px"></Tag>
                                </div>
                            </div>
                            <div class="pt-4">
                                <div class="flex flex-row justify-content-between align-items-start gap-2">
                                    <div>
                                        <span class="font-medium text-secondary text-sm">{{ item.categoria.nombre }}</span>
                                        <div class="text-lg font-medium text-900 mt-1">{{ item.nombre }}</div>
                                    </div>
                                    <div class="surface-100 p-1" style="border-radius: 30px">
                                        <div class="surface-0 flex align-items-center gap-2 justify-content-center py-1 px-2" style="border-radius: 30px; box-shadow: 0px 1px 2px 0px rgba(0, 0, 0, 0.04), 0px 1px 2px 0px rgba(0, 0, 0, 0.06)">
                                        
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-column gap-4 mt-4">
                                    <span class="text-2xl font-semibold text-900">${{ item.precio }}</span>
                                    <div v-if="user !=null">
                                        <label>Cantidad</label>
                                        <InputNumber v-model="item.cantidad" inputId="horizontal-buttons" showButtons buttonLayout="horizontal" :step="1" :min="1">
                                              <template #incrementbuttonicon>
                                                <span class="pi pi-plus" />
                                            </template>
                                            <template #decrementbuttonicon>
                                                <span class="pi pi-minus" />
                                            </template>
                                        </InputNumber>
                                    </div>
                                    <div class="flex gap-2" v-if="user !=null">
                                        <Button icon="pi pi-shopping-cart" label="Agregar a Orden" :disabled="item.stock === 0" class="flex-auto white-space-nowrap" @click="addToOrder(item)"></Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </DataView>
        <!--Modal para ver la orden y hacerlo efectivo-->
        <Dialog v-model:visible="mostrarOrdenDialog" :style="{width: '650px'}" header="Detalle de Orden" :modal="true" class="p-fluid">
            <div class="row">
                <div class="col">
                    <label>Fecha Orden: {{ orden.fecha }}</label>
                </div>
            </div>
            <div class="row">
                <label>Cliente: {{ user.name }}</label>
            </div>
            <div class="row">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <td><b>Producto</b></td>
                            <td><b>Marca</b></td>
                            <td><b>Cantidad</b></td>
                            <td><b>Precio</b></td>
                            <td><b>Subtotal</b></td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in orden.detalleOrden" :key="item.id">
                            <td>{{ item.producto.nombre }}, {{ item.producto.descripcion }}, modelo: {{ item.producto.modelo }}</td>
                            <td>{{ item.producto.marca.nombre }}</td>
                            <td>{{ item.cantidad }}</td>
                            <td>${{ item.producto.precio }}</td>
                            <td>${{ item.cantidad * item.producto.precio }}</td>
                            <td>
                                <Button icon="pi pi-trash" outlined rounded severity="danger" @click="deleteItem(item)" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4">Total de la Orden</td>
                            <td><b>${{ total }}</b></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <template #footer>
                <Button label="Confirmar Orden" icon="pi pi-check" text @click="saveOrden" v-if="orden.detalleOrden.length > 0"/>
            </template>
        </Dialog>
    </div>
</template>

<script>
    import { ref, onMounted } from 'vue';
    export default {
        //con esto se manda a llamar al cliente a la interfsz
        props:['user'],
        data(){
            return{
                // Variables para gestionar la lógica
                productos:[],
                orden:{
                    correlativo:"",
                    fecha:new Date(Date.now()-new Date().getTimezoneOffset()*60000), //
                    fecha_despacho:null,
                    estado:"R",
                    monto:new  Number("0").toFixed(2),
                    user: null,
                    detalleOrden:[]
                },
                editedOrden: -1,
                search: '',
                submitted:false,
                mostrarOrdenDialog:false,
                layout: ref('grid')
            }
        },
        computed:{
            total(){
                var totalOrder = 0;
                this.orden.detalleOrden.forEach(element => { 
                    totalOrder += (element.producto.precio * element.cantidad); //El "+=" aumenta un elemento, pero conservando el que tiene
                });
                this.orden.monto = totalOrder;
                return totalOrder;
            }
        },
        methods: {
            async fetchProductos(){
                await this.axios.get(`/api/productos`)
                .then(response => {
                    console.log(response.data);
                    this.productos = response.data;
                }).catch(error => {
                    console.log(error);
                })
            },
            addToOrder(item){
                //console.log(item);
                if(!item.cantidad){
                    this.$swal.fire({
                        title:'cantidad vacía',
                        text:'Digite una cantidad para agregar a la orden',
                        icon:'warning'
                    })
                    return;
                }
                this.orden.detalleOrden.push({
                    id:null,
                    cantidad: item.cantidad,
                    precio: item.precio,
                    producto: item
                });
                const Toast = this.$swal.mixin({
                    toast: true,
                    position: 'top-right',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
                Toast.fire({
                    icon:'success',
                    title:'Producto agregado a la orden...'
                })
            },
            hideDialog(){
                this.mostrarOrdenDialog = false;
                this.submitted = false;
            },
            viewOrden(){
                this.mostrarOrdenDialog = true;
                this.submitted = true;
            },
            deleteItem(item){
                //se obtiene el indice del item que se eliminara
                let index = this.orden.detalleOrden.indexOf(item);
                //elimina el item del arreglo
                this.orden.detalleOrden.splice(index,1)
            },
            async saveOrden(){
                if(this.orden.detalleOrden.length > 0){
                    //se setean los datos faltantes de la orden
                    this.orden.user = this.user;
                    var f = new Date();
                    this.orden.fecha = f.getFullYear() + "-" + f.getMonth() + "-" + f.getDate();
                    await this.axios.post(`api/ordenes`, this.orden)
                    .then(response => {
                        if(response.status == 201){
                            this.$swal.fire("success", `Su orden ha sido registrada con No. ${response.data.data.correlativo} pronto nos comunicaremos con usted`);
                            this.orden.detalleOrden = [];
                            this.mostrarOrdenDialog = false;
                        }
                    }).catch(err => {
                        console.log(err);
                    })
                }else{
                    this.$swal.fire("warning","Agregue al menos un producto a la orden por favor");
                }
            }
        },
        mounted() {
            this.fetchProductos();
            console.log(this.user);
        }
        
    }
</script>