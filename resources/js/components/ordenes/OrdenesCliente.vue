<template>
    <div class="card">
        <DataView :value="productos" :layout="layout" paginator :rows="6">
            <template #header>
                <div class="flex justify-content-end">
                    <DataViewLayoutOptions v-model="layout" />
                </div>
            </template>

            <template #list="slotProps">
                <div class="grid grid-nogutter">
                    <div v-for="(item, index) in slotProps.items" :key="index" class="col-12">
                        <div class="flex flex-column sm:flex-row sm:align-items-center p-4 gap-3" :class="{ 'border-top-1 surface-border': index !== 0 }">
                            <div class="md:w-10rem relative">
                                <!-- <img class="block xl:block mx-auto border-round w-full" :src="`https://primefaces.org/cdn/primevue/images/product/${item.image}`" :alt="item.name" /> -->
                                <img class="block xl:block mx-auto border-round w-full" :src="`/images/productos/1710044525_Samsung-A14-1.jpg`" :alt="item.name" style="max-width: 300px" />
                                <Tag :value="item.stock" class="absolute" style="left: 4px; top: 4px"></Tag>
                            </div>
                            <div class="flex flex-column md:flex-row justify-content-between md:align-items-center flex-1 gap-4">
                                <div class="flex flex-row md:flex-column justify-content-between align-items-start gap-2">
                                    <div>
                                        <span class="font-medium text-secondary text-sm">{{ item.categoria.nombre }}</span>
                                        <div class="text-lg font-medium text-900 mt-2">{{ item.nombre }}</div>
                                    </div>
                                </div>
                                <div class="flex flex-column md:align-items-end gap-5">
                                    <span class="text-xl font-semibold text-900">${{ item.precio }}</span>
                                    <div class="flex flex-row-reverse md:flex-row gap-2">
                                    <div>
                                        <label>Cantidad</label>
                                        <InputNumber v-model="item.cantidad" inputId="horizontal-buttons" showButtons buttonLayout="horizontal" :step="1" :min="1" >
                                            <template #incrementbuttonicon>
                                                <span class="pi pi-plus" />
                                            </template>
                                            <template #decrementbuttonicon>
                                                <span class="pi pi-minus" />
                                            </template>
                                        </InputNumber>
                                    </div>
                                        <Button icon="pi pi-shopping-cart" label="Agregar a Orden" :disabled="item.stock === 0 " class="flex-auto md:flex-initial white-space-nowrap"></Button>
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
                                    <img class="border-round w-full" :src="`/images/productos/1710044525_Samsung-A14-1.jpg`" :alt="item.nombre" style="max-width: 300px"/>
                                    <Tag :value="item.stock" class="absolute" style="left: 4px; top: 4px"></Tag>
                                </div>
                            </div>
                            <div class="pt-4">
                                <div class="flex flex-row justify-content-between align-items-start gap-2">
                                    <div>
                                        <span class="font-medium text-secondary text-sm">{{ item.categoria.nombre }}</span>
                                        <div class="text-lg font-medium text-900 mt-1">{{ item.nombre }}</div>
                                    </div>
                                </div>
                                <div class="flex flex-column gap-4 mt-4">
                                    <span class="text-2xl font-semibold text-900">${{ item.precio }}</span>
                                    <div class="flex flex-column">
                                        <label>Cantidad</label>
                                        <InputNumber v-model="item.cantidad" inputId="horizontal-buttons" showButtons buttonLayout="horizontal" :step="1" :min="1" >
                                            <template #incrementbuttonicon>
                                                <span class="pi pi-plus" />
                                            </template>
                                            <template #decrementbuttonicon>
                                                <span class="pi pi-minus" />
                                            </template>
                                        </InputNumber>
                                    </div>
                                    <div class="flex gap-2">
                                        <Button icon="pi pi-shopping-cart" label="Agregar a Orden" :disabled="item.stock === 0 " class="flex-auto white-space-nowrap"></Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </DataView>
    </div>
</template>

<script>
    import { ref, onMounted } from 'vue';

    export default {
        props:['user'],
        data() {
            return {
                productos:[],
                orden:{
                    correlativo:"",
                    fecha: new Date(Date.now() - new Date().getTimezoneOffset()*60000),
                    fecha_despacho:null,
                    estado:'R',
                    monto: new Number("0").toFixed(2),
                    user:null,
                    detalleOrden:[]
                },
                editedOrden: -1,
                search: '',
                submitted: false,
                layout: ref('grid')
            }
        },
        computed: {
            total(){
                var totalOrden = 0;
                this.orden.detalleOrden.forEach(element =>{
                    totalOrden += (element.producto.precio * element.cantidad)
                });
                this.orden.monto = totalOrden;
                return totalOrden;
            }
        },
        methods: {
            async fetchProductos() {
                await this.axios.get(`/api/productos`)
                .then(response => {
                    console.log(response.data);
                    this.productos = response.data;
                })
                .catch(error => {
                    console.log(error);
                })
            }
        },
        mounted() {
            this.fetchProductos();
            console.log(this.user)
        }
    }
</script>