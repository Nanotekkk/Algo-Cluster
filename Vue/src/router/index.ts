import { createRouter, createWebHistory } from 'vue-router'

import { defineAsyncComponent } from 'vue'
import { createApp } from 'vue'
import App from '@/App.vue'
import '@/assets/main.css'  

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
  path: '/group-manager',
  name: 'group-manager',
  component: () => import('@/views/GroupManager.vue'),
},
    
  ],
})

export default router
