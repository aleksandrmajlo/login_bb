/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// ******* alert 
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
Vue.use(VueSweetalert2);
// ******* alert end

// миксин
import GlobalMixin from './mixin/mixin'
Vue.mixin(GlobalMixin);

// Lang
import i18n from './lang/i18n'


// вход в админку
Vue.component('LoginForm', require('./components/LoginForm.vue'));
// Vue.component('my-vuetable', require('./components/MyVuetable.vue'));

const app = new Vue({
    el: '#app',
    i18n,
});