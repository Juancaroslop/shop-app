<template>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-4 col-sm-12">
                                <h5 class="float-start">Listado de Marcas de Prodcutos</h5>
                            </div>
                            <div class="input-group rounded col-md-4 col-sm-12">
                                <input type="search" v-model="search" class="form-control rounded" placeholder="Search" aria-label="Search" aria-describedby="search-addon" />
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <button @click="showDialog" class="btn btn-success btn-sm float-end">Nuevo</button>
                            </div>
                        </div>

                    </div>
                    <div class="card-body">
                        <table class="table bordered">
                            <thead>
                                <tr>
                                    <th scope="col">Marca</th>
                                    <th scope="col">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in items" v-bind:key="item.id">
                                    <td>{{ item.nombre }}</td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" @click="showDialogEditar(item)">Editar</button>
                                        &nbsp;
                                        <button type="button" class="btn btn-danger btn-sm" @click=" eliminar(item)">Eliminar</button>
                                    </td>
                                </tr>
                            </tbody>

                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="marcaModal" tabindex="-1" aria-labelledby="marcaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title fs-5" id="marcaModalLabel">{{ formTitle }}</h3>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <!-- definiendo cuerpo del formulario modal -->
            <div class="row">
                <div class="form-group col-12">
                    <label for="nombre">Nombre</label>
                    <input type="text" class="form-control" v-model="marca.nombre">
                    <span class="text-danger" v-show="marcaErrors.nombre">Nombre es requerido</span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" @click="saveOrUpdate()">{{ btnTitle }}</button>
        </div>
        </div>
    </div>
    </div>
</template>

<script>
    export default {
        data(){
            return{
                marcas: [],
                marca:{
                    id: null,
                    nombre:""
                },
                editedMarca: -1,
                marcaErrors:{
                    nombre:false
                },
                search:''
            }
        },

        computed:{
            formTitle(){
                return this.marca.id == null ? "Agregar Marca" : "Actualizar Marca";
            },
            btnTitle(){
                return this.marca.id == null ? "Guardar" : "Actualizar";
            },

            items(){
                return this.marcas.filter(item =>{
                    return item.nombre.toLowerCase().includes(this.search.toLowerCase());
                })
            }
        },
        methods:{
            async fetchMarcas(){
                await this.axios.get(`/api/marcas`)
                .then(response => {
                    this.marcas = response.data;
                })
            },

            showDialog(){
                this.marca = {
                    id: null,
                    nombre: ""
                };
                this.marcaErrors = {
                    nombre: false
                };
                $('#marcaModal').modal('show');
            },

            showDialogEditar(marca){
                let me = this;
                $('#marcaModal').modal('show');
                me.editedMarca = me.marcas.indexOf(marca);
                me.marca = Object.assign({},marca);
            },
            hideDialog(){
                let me = this;
                setTimeout(() => {
                    me.marca = {
                        id: null,
                        nombre: ""
                    }
                },300);
                $('#marcaModal').modal('hide');
            },
            async saveOrUpdate(){
                let me = this;
                me.marca.nombre == '' ? me.marcaErrors.nombre = true : me.marcaErrors.nombre = false;
                if (me.marca.nombre) {
                    let accion = me.marca.id == null ? "add" : "upd";
                    if (accion == "add") {
                        //peticion para guardar una marca
                        if (this.existMarca(me.marca)) {
                            this.$swal.fire("Registro duplicado","Ya existe una marca registrada con este nombre en la base de datos",'warning');
                            return;
                        }
                        await this.axios.post('/api/marcas', me.marca)
                        .then(response => {
                            //console.log(response.data);
                            if (response.status == 201) {
                                me.verificarAccion(response.data.data, response.status,accion, response.data.message);
                                me.hideDialog();
                            }
                        }).catch(errors => {
                            console.log(errors);
                        })
                    }else{
                        //peticion para actualizar una marca
                        await this.axios.put(`/api/marcas/${me.marca.id}`, me.marca)
                        .then(response => {
                            if (response.status == 202) {
                                me.verificarAccion(response.data.data, response.status,accion, response.data.message);
                                me.hideDialog();
                            }
                        }).catch(errors => {
                            console.log(errors);
                        })
                    }
                }
            },
            async eliminar(marca){
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
                            me.editedMarca = me.marcas.indexOf(marca);
                            this.axios.delete(`/api/marcas/${marca.id}`)
                                .then(response => {
                                    me.verificarAccion(null,response.status, "del", response.data.message);
                                }).catch(errors =>{
                                    console.log(errors);
                                })
                        }
                })
            },
            //metodo para verificar si existe un objeto dentro del arreglo
            existMarca(marca){
                let me = this;
                return me.marcas.some( obj => obj.nombre === marca.nombre);
            },

            verificarAccion(marca, statusCode, accion, message){
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
                        me.marcas.unshift(marca);
                        Toast.fire({
                            icon: 'success',
                            'title': message
                        });
                        break;
                    case "upd":
                        Object.assign(me.marcas[me.editedMarca],marca);
                        Toast.fire({
                            icon: 'success',
                            'title': message
                        });
                        break;
                    case "del":
                        if (statusCode == 205) {
                            me.marcas.splice(this.editedMarca, 1);
                            Toast.fire({
                                icon: 'success',
                                'title': 'Registro Eliminado ...!'
                            });
                        }else{
                            this.$swal.fire({
                                icon: 'error',
                                text: 'No se puede eliminar, ya existen autos registrados de esta marca'
                            });
                        }
                        break;
                 }
            }
        },
        mounted() {
            this.fetchMarcas();
        }
    }
</script>