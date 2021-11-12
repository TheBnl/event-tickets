import Vue from 'vue';
import BootstrapVue from 'bootstrap-vue'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'
import Checkin from './App/Checkin.vue';


{
    'use strict';
    Vue.use(BootstrapVue);
    
    const checkin = new Vue({
        el: '#checkin',
        render: h => h(Checkin),
    });
}
