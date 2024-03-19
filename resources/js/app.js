/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
import { createApp } from 'vue';
import axios from 'axios';

//importaciones de sweetalert 2
import VueSweetalert2 from 'vue-sweetalert2';

/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */

const app = createApp({});

import ExampleComponent from './components/ExampleComponent.vue';
app.component('example-component', ExampleComponent);
import CategoriaComponent from './components/CategoriaComponent.vue';
app.component('categoria-component', CategoriaComponent);
import MarcaComponent from './components/MarcaComponent.vue';
app.component('marca-component', MarcaComponent);
import ProductoComponent from './components/ProductoComponent.vue';
app.component('producto-component', ProductoComponent);
import OrdenesComponent from './components/ordenes/OrdenesComponent.vue';
app.component('ordenes-component', OrdenesComponent);
import OrdenesCliente from './components/ordenes/OrdenesCliente.vue';
app.component('ordenes-cliente', OrdenesCliente);

// importaciones de primeVue
import PrimeVue from 'primevue/config';
import Button from 'primevue/button';
import AutoComplete from 'primevue/autocomplete';
import Calendar from 'primevue/calendar';
import Checkbox from 'primevue/checkbox';
import ColorPicker from 'primevue/colorpicker';
import Dropdown from 'primevue/dropdown';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputMask from 'primevue/inputmask';
import InputNumber from 'primevue/inputnumber';
import InputSwitch from 'primevue/inputswitch';
import InputText from 'primevue/inputtext';
import MultiSelect from 'primevue/multiselect';
import Password from 'primevue/password';
import RadioButton from 'primevue/radiobutton';
import Textarea from 'primevue/textarea';
import SplitButton from 'primevue/splitbutton';
import Toolbar from 'primevue/toolbar';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import ColumnGroup from 'primevue/columngroup';     // optional
import Row from 'primevue/row';                     // optional
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import ToastService from 'primevue/toastservice';
import FileUpload from 'primevue/fileupload';
import Carousel from 'primevue/carousel';
import Tooltip from 'primevue/tooltip';
import DataView from 'primevue/dataview';
import DataViewLayoutOptions from 'primevue/dataviewlayoutoptions'   // optional
import Tag from 'primevue/tag';

//definiendo varibles globales
app.config.globalProperties.axios = axios;

app.use(VueSweetalert2);
app.use(PrimeVue);
app.use(ToastService);
app.directive('tooltip', Tooltip);

// agregamos los componentes de PrimeVue
app.component('Button', Button);
app.component('AutoComplete', AutoComplete);
app.component('Calendar', Calendar);
app.component('Checkbox', Checkbox);
app.component('ColorPicker', ColorPicker);
app.component('Dropdown2', Dropdown);
app.component('IconField', IconField);
app.component('InputIcon', InputIcon);
app.component('InputMask', InputMask);
app.component('InputNumber', InputNumber);
app.component('InputSwitch', InputSwitch);
app.component('InputText', InputText);
app.component('MultiSelect', MultiSelect);
app.component('Password', Password);
app.component('RadioButton', RadioButton);
app.component('Textarea', Textarea);
app.component('SplitButton', SplitButton);
app.component('DataTable', DataTable);
app.component('Column', Column);
app.component('ColumnGroup', ColumnGroup);      //opcional
app.component('Row', Row);                      //opcional
app.component('Toolbar', Toolbar);
app.component('Dialog', Dialog);
app.component('Toast', Toast);
app.component('FileUpload', FileUpload);
app.component('Carousel', Carousel);
app.component('DataView', DataView);
app.component('DataViewLayoutOptions', DataViewLayoutOptions);
app.component('Tag', Tag);
app.mount('#app');
