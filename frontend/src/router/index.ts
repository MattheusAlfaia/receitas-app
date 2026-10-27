import { createRouter, createWebHistory } from 'vue-router'
import LayoutAutenticacao from '../layouts/LayoutAutenticacao.vue'
import LayoutPadrao from '../layouts/LayoutPadrao.vue'
import InicioView from '../views/HomeView.vue'
import { useAuthStore } from '../stores/autenticacao'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      component: LayoutPadrao,
      meta: { requiresAuth: true, title: 'Receita App' },
      children: [
        {
          path: '',
          name: 'home',
          component: InicioView,
        },
      ],
    },
    {
      path: '/login',
      component: LayoutAutenticacao,
      meta: { guestOnly: true, title: 'Receita App' },
      children: [
        {
          path: '',
          name: 'login',
          component: () => import('../views/LoginView.vue'),
        },
      ],
    },
    {
      path: '/register',
      component: LayoutAutenticacao,
      meta: { guestOnly: true, title: 'Receita App' },
      children: [
        {
          path: '',
          name: 'register',
          component: () => import('../views/CadastroView.vue'),
        },
      ],
    },
    {
      path: '/receita',
      component: LayoutPadrao,
      meta: { requiresAuth: true, title: 'Receita App' },
      children: [
        {
          path: 'nova',
          name: 'new-recipe',
          component: () => import('../views/NovaReceitaView.vue'),
        },
        {
          path: 'editar/:id',
          name: 'edit-recipe',
          component: () => import('../views/NovaReceitaView.vue'),
        }
      ],
    }
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  await auth.inicializar()

  if (to.meta.requiresAuth && !auth.estaAutenticado) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.guestOnly && auth.estaAutenticado) {
    return { name: 'home' }
  }
})

export default router
