import '../css/app.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import reveal from './directives/reveal';
import can from './directives/can';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.directive('reveal', reveal);
app.directive('can', can);

app.mount('#app');
