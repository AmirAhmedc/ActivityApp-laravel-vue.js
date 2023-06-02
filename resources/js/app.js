import '../sass/app.scss';
import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './components/App.vue'
import UploadImage from './components/UploadImage.vue'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', component: UploadImage },
    ],
})

const app = createApp(App)


app.use(router)

app.mount('#app')