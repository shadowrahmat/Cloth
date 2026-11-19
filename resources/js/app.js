import '../css/app.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import reveal from './directives/reveal';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
app.directive('reveal', reveal);

app.mount('#app');
